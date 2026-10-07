<?php

namespace App\Console\Commands;

use App\Models\CodigoPostal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Importa el Catálogo Nacional de Códigos Postales de Correos de México.
 *
 * Archivo: https://www.correosdemexico.gob.mx/SSLServicios/ConsultaCP/CodigoPostal_Exportar.aspx
 * (formato "TXT", todos los estados). Acepta el .zip descargado o el CPdescarga.txt.
 * Con --sql genera además el script para producción (phpMyAdmin no corre artisan).
 */
class ImportarCodigosPostales extends Command
{
    protected $signature = 'cp:importar
        {archivo=database/data/sepomex-CPdescargatxt.zip : .zip o .txt de SEPOMEX}
        {--sql= : Ruta donde escribir también el script SQL (.sql o .sql.gz)}';

    protected $description = 'Carga el catálogo de códigos postales de SEPOMEX en la tabla codigos_postales';

    public function handle(): int
    {
        $ruta = base_path($this->argument('archivo'));
        if (!is_file($ruta)) {
            $ruta = $this->argument('archivo');
        }
        if (!is_file($ruta)) {
            $this->error("No existe el archivo: {$this->argument('archivo')}");
            return self::FAILURE;
        }

        $texto = $this->leerTexto($ruta);
        $lineas = preg_split('/\r\n|\n|\r/', $texto);

        // Línea 1 = aviso legal, línea 2 = encabezados.
        $filas = [];
        foreach (array_slice($lineas, 2) as $linea) {
            $c = explode('|', $linea);
            if (count($c) < 6 || !preg_match('/^\d{5}$/', $c[0])) {
                continue;
            }
            $filas[] = [
                'cp' => $c[0],
                'colonia' => mb_substr(trim($c[1]), 0, 120),
                'tipo_asentamiento' => mb_substr(trim($c[2]), 0, 40) ?: null,
                'municipio' => mb_substr(trim($c[3]), 0, 120),
                'estado' => CodigoPostal::normalizarEstado(trim($c[4])),
                'ciudad' => mb_substr(trim($c[5]), 0, 120) ?: null,
            ];
        }

        if (!$filas) {
            $this->error('El archivo no tiene registros con el formato esperado.');
            return self::FAILURE;
        }

        $this->info('Registros leídos: ' . number_format(count($filas)));

        DB::transaction(function () use ($filas) {
            CodigoPostal::query()->delete();
            $barra = $this->output->createProgressBar(count($filas));
            foreach (array_chunk($filas, 2000) as $bloque) {
                CodigoPostal::insert($bloque);
                $barra->advance(count($bloque));
            }
            $barra->finish();
            $this->newLine();
        });

        $this->info('Códigos postales distintos: ' . number_format(CodigoPostal::distinct('cp')->count('cp')));

        if ($destino = $this->option('sql')) {
            $this->escribirSql($filas, base_path($destino));
            $this->info("Script SQL escrito en {$destino}");
        }

        return self::SUCCESS;
    }

    private function leerTexto(string $ruta): string
    {
        if (str_ends_with(strtolower($ruta), '.zip')) {
            $zip = new \ZipArchive();
            if ($zip->open($ruta) !== true) {
                throw new \RuntimeException('No se pudo abrir el .zip');
            }
            $contenido = $zip->getFromIndex(0);
            $zip->close();
        } else {
            $contenido = file_get_contents($ruta);
        }

        // SEPOMEX entrega el archivo en Latin-1 (Windows-1252).
        return mb_check_encoding($contenido, 'UTF-8') ? $contenido : mb_convert_encoding($contenido, 'UTF-8', 'Windows-1252');
    }

    private function escribirSql(array $filas, string $destino): void
    {
        $gz = str_ends_with($destino, '.gz');
        $fh = $gz ? gzopen($destino, 'w9') : fopen($destino, 'w');
        $escribe = fn ($s) => $gz ? gzwrite($fh, $s) : fwrite($fh, $s);
        $q = fn ($v) => $v === null ? 'NULL' : "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], $v) . "'";

        $escribe("-- Catálogo Nacional de Códigos Postales (SEPOMEX), generado con `php artisan cp:importar --sql`.\n"
            . "-- Correr UNA vez en producción. Reemplaza el contenido completo de la tabla.\n"
            . "SET NAMES utf8mb4;\n\n"
            . "CREATE TABLE IF NOT EXISTS `codigos_postales` (\n"
            . "    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,\n"
            . "    `cp` CHAR(5) NOT NULL,\n"
            . "    `colonia` VARCHAR(120) NOT NULL,\n"
            . "    `tipo_asentamiento` VARCHAR(40) NULL,\n"
            . "    `municipio` VARCHAR(120) NOT NULL,\n"
            . "    `estado` VARCHAR(60) NOT NULL,\n"
            . "    `ciudad` VARCHAR(120) NULL,\n"
            . "    PRIMARY KEY (`id`),\n"
            . "    KEY `codigos_postales_cp_index` (`cp`)\n"
            . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n"
            . "TRUNCATE TABLE `codigos_postales`;\n\n");

        foreach (array_chunk($filas, 1000) as $bloque) {
            $valores = array_map(fn ($f) => '(' . implode(',', [
                $q($f['cp']), $q($f['colonia']), $q($f['tipo_asentamiento']),
                $q($f['municipio']), $q($f['estado']), $q($f['ciudad']),
            ]) . ')', $bloque);
            $escribe("INSERT INTO `codigos_postales` (`cp`,`colonia`,`tipo_asentamiento`,`municipio`,`estado`,`ciudad`) VALUES\n"
                . implode(",\n", $valores) . ";\n");
        }

        $escribe("\nINSERT INTO `migrations` (`migration`, `batch`)\n"
            . "SELECT '2026_10_07_100000_create_codigos_postales_table', COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`\n"
            . "WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_07_100000_create_codigos_postales_table');\n");

        $gz ? gzclose($fh) : fclose($fh);
    }
}
