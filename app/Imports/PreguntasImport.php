<?php

namespace App\Imports;

use App\Models\AreaPregunta;
use App\Models\Pregunta;
use App\Models\PreguntaOpcion;
use Illuminate\Support\Collection as SupportCollection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

/**
 * Columnas esperadas (mismo orden que PreguntasExport): ID, Área, Pregunta,
 * Opción A, Opción B, Opción C, Opción D, Respuesta Correcta (A-D), Justificación.
 * El ID es opcional: si coincide con una pregunta existente la actualiza
 * (reemplazando todas sus opciones), si no (o viene vacío) crea una pregunta
 * nueva. La Opción D puede ir vacía (mínimo 3 opciones). El área se busca por
 * nombre (sin distinguir mayúsculas) y se crea sola si no existe todavía.
 */
class PreguntasImport implements ToCollection, WithStartRow
{
    private const LETRAS = ['A', 'B', 'C', 'D'];

    public int $creadas = 0;
    public int $actualizadas = 0;
    /** @var array<int, string> */
    public array $errores = [];

    private array $areasCache = [];

    public function startRow(): int
    {
        return 2;
    }

    public function collection(SupportCollection $rows)
    {
        foreach ($rows as $i => $row) {
            $numeroFila = $i + 2; // +2: startRow(2) + índice base 0

            if ($row->filter(fn ($v) => trim((string) $v) !== '')->isEmpty()) {
                continue; // fila vacía, se ignora en silencio
            }

            $id = trim((string) ($row[0] ?? ''));
            $areaNombre = trim((string) ($row[1] ?? ''));
            $pregunta = trim((string) ($row[2] ?? ''));
            $opcionesPorLetra = [];
            foreach (self::LETRAS as $idx => $letra) {
                $texto = trim((string) ($row[3 + $idx] ?? ''));
                if ($texto !== '') {
                    $opcionesPorLetra[$letra] = $texto;
                }
            }
            $letraCorrecta = strtoupper(trim((string) ($row[7] ?? '')));
            $justificacion = trim((string) ($row[8] ?? ''));

            if ($areaNombre === '' || $pregunta === '') {
                $this->errores[] = "Fila {$numeroFila}: faltan la pregunta o el área.";
                continue;
            }
            if (count($opcionesPorLetra) < 3) {
                $this->errores[] = "Fila {$numeroFila}: se necesitan al menos 3 opciones (A, B y C).";
                continue;
            }
            if (!isset($opcionesPorLetra[$letraCorrecta])) {
                $this->errores[] = "Fila {$numeroFila}: la 'Respuesta Correcta' ({$letraCorrecta}) no coincide con ninguna opción llena.";
                continue;
            }

            $idArea = $this->resolverArea($areaNombre);

            $datos = [
                'id_area' => $idArea,
                'pregunta' => $pregunta,
                'justificacion' => $justificacion !== '' ? $justificacion : null,
            ];

            $existente = $id !== '' && is_numeric($id) ? Pregunta::find((int) $id) : null;

            if ($existente) {
                $existente->update($datos);
                $existente->opciones()->delete();
                $this->guardarOpciones($existente->id, $opcionesPorLetra, $letraCorrecta);
                $this->actualizadas++;
            } else {
                $nueva = Pregunta::create($datos);
                $this->guardarOpciones($nueva->id, $opcionesPorLetra, $letraCorrecta);
                $this->creadas++;
            }
        }
    }

    private function guardarOpciones(int $preguntaId, array $opcionesPorLetra, string $letraCorrecta): void
    {
        $orden = 1;
        foreach (self::LETRAS as $letra) {
            if (!isset($opcionesPorLetra[$letra])) continue;
            PreguntaOpcion::create([
                'pregunta_id' => $preguntaId,
                'texto' => $opcionesPorLetra[$letra],
                'es_correcta' => $letra === $letraCorrecta,
                'orden' => $orden++,
            ]);
        }
    }

    private function resolverArea(string $nombre): int
    {
        $clave = mb_strtolower($nombre);

        if (isset($this->areasCache[$clave])) {
            return $this->areasCache[$clave];
        }

        $area = AreaPregunta::whereRaw('LOWER(nombre) = ?', [$clave])->first()
            ?? AreaPregunta::create(['nombre' => $nombre]);

        return $this->areasCache[$clave] = $area->id;
    }
}
