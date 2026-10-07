<?php

namespace App\Imports;

use App\Models\AreaPregunta;
use App\Models\ExamenGenerado;
use App\Models\Pregunta;
use App\Models\PreguntaOpcion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Importación de preguntas desde Excel / CSV en dos pasos:
 *   1) analizar()  → lee el archivo y clasifica cada fila (nueva, actualiza, duplicada, error)
 *                    SIN guardar nada, para mostrar una vista previa.
 *   2) guardar()   → guarda las filas válidas (y opcionalmente las agrega a un examen).
 *
 * Las columnas se reconocen por su ENCABEZADO (sin importar orden, mayúsculas ni acentos):
 *   ID · Área · Pregunta · Opción A … Opción E · Respuesta correcta · Justificación
 * Si el archivo no trae encabezados reconocibles se usa el orden de la descarga
 * (ID, Área, Pregunta, A, B, C, D, Correcta, Justificación).
 *
 * La respuesta correcta puede escribirse como letra (A-E), número (1-5) o el
 * texto exacto de la opción. El ID es opcional: si coincide con una pregunta
 * existente se actualiza; si no, se crea. Si el área no existe se crea sola.
 */
class PreguntasImport
{
    public const LETRAS = ['A', 'B', 'C', 'D', 'E'];

    /** Sinónimos aceptados para cada columna (ya normalizados). */
    private const ENCABEZADOS = [
        'id' => ['id', 'clave pregunta', 'id pregunta'],
        'area' => ['area', 'materia', 'tema', 'area tematica', 'asignatura'],
        'pregunta' => ['pregunta', 'reactivo', 'enunciado', 'texto'],
        'A' => ['opcion a', 'a', 'inciso a', 'respuesta a'],
        'B' => ['opcion b', 'b', 'inciso b', 'respuesta b'],
        'C' => ['opcion c', 'c', 'inciso c', 'respuesta c'],
        'D' => ['opcion d', 'd', 'inciso d', 'respuesta d'],
        'E' => ['opcion e', 'e', 'inciso e', 'respuesta e'],
        'correcta' => ['respuesta correcta', 'correcta', 'respuesta', 'clave', 'respuesta correcta a d', 'respuesta correcta a e'],
        'justificacion' => ['justificacion', 'explicacion', 'retroalimentacion', 'solucion'],
    ];

    private const POSICIONAL = ['id' => 0, 'area' => 1, 'pregunta' => 2, 'A' => 3, 'B' => 4, 'C' => 5, 'D' => 6, 'correcta' => 7, 'justificacion' => 8];

    /**
     * @return array{filas: array<int, array>, resumen: array<string,int>, columnas: array<string,string>}
     */
    public function analizar(string $ruta, string $disco = 'local', ?string $tipo = null): array
    {
        $hojas = Excel::toArray(new class {}, $ruta, $disco, $tipo);
        $filas = $hojas[0] ?? [];

        [$mapa, $inicio, $columnas] = $this->mapearColumnas($filas[0] ?? []);

        // Lo que ya existe en la BD, para detectar duplicados e IDs válidos.
        $existentes = Pregunta::query()->get(['id', 'id_area', 'pregunta'])
            ->mapWithKeys(fn ($p) => [$p->id_area . '|' . $this->clave($p->pregunta) => $p->id]);
        $idsExistentes = Pregunta::pluck('id')->flip();
        $areas = AreaPregunta::all(['id', 'nombre'])->mapWithKeys(fn ($a) => [$this->clave($a->nombre) => $a]);

        $resultado = [];
        $vistasEnArchivo = [];

        foreach (array_slice($filas, $inicio) as $i => $fila) {
            $numero = $i + $inicio + 1;
            $celda = fn ($k) => isset($mapa[$k]) ? trim((string) ($fila[$mapa[$k]] ?? '')) : '';

            if (collect($fila)->filter(fn ($v) => trim((string) $v) !== '')->isEmpty()) {
                continue;
            }

            $opciones = [];
            foreach (self::LETRAS as $letra) {
                if (($t = $celda($letra)) !== '') {
                    $opciones[$letra] = $t;
                }
            }

            $f = [
                'fila' => $numero,
                'id' => $celda('id'),
                'area' => $celda('area'),
                'pregunta' => $celda('pregunta'),
                'opciones' => $opciones,
                'correcta' => null,
                'justificacion' => $celda('justificacion') ?: null,
                'estado' => 'nueva',
                'motivo' => null,
            ];

            $errores = [];
            if ($f['pregunta'] === '') $errores[] = 'falta el texto de la pregunta';
            if ($f['area'] === '') $errores[] = 'falta el área';
            if (count($opciones) < 2) $errores[] = 'necesita al menos 2 opciones';
            $f['correcta'] = $this->resolverCorrecta($celda('correcta'), $opciones);
            if (count($opciones) >= 2 && !$f['correcta']) {
                $errores[] = $celda('correcta') === ''
                    ? 'falta la respuesta correcta'
                    : "la respuesta correcta «{$celda('correcta')}» no coincide con ninguna opción";
            }

            if ($errores) {
                $f['estado'] = 'error';
                $f['motivo'] = Str::ucfirst(implode('; ', $errores)) . '.';
                $resultado[] = $f;
                continue;
            }

            $area = $areas[$this->clave($f['area'])] ?? null;
            $f['area_nueva'] = !$area;
            $claveTexto = $this->clave($f['pregunta']);
            $claveArchivo = $this->clave($f['area']) . '|' . $claveTexto;

            if ($f['id'] !== '' && ctype_digit($f['id']) && isset($idsExistentes[(int) $f['id']])) {
                $f['estado'] = 'actualiza';
            } elseif (isset($vistasEnArchivo[$claveArchivo])) {
                $f['estado'] = 'duplicada';
                $f['motivo'] = "Repetida en el archivo (igual que la fila {$vistasEnArchivo[$claveArchivo]}).";
            } elseif ($area && isset($existentes[$area->id . '|' . $claveTexto])) {
                $f['estado'] = 'duplicada';
                $f['motivo'] = "Ya existe en el banco (pregunta #{$existentes[$area->id . '|' . $claveTexto]}).";
            }
            $vistasEnArchivo[$claveArchivo] ??= $numero;

            $resultado[] = $f;
        }

        $resumen = ['total' => count($resultado)];
        foreach (['nueva', 'actualiza', 'duplicada', 'error'] as $estado) {
            $resumen[$estado] = collect($resultado)->where('estado', $estado)->count();
        }
        $resumen['areas_nuevas'] = collect($resultado)->where('area_nueva', true)->pluck('area')
            ->map(fn ($a) => $this->clave($a))->unique()->count();

        return ['filas' => $resultado, 'resumen' => $resumen, 'columnas' => $columnas];
    }

    /**
     * Guarda las filas válidas.
     *
     * @return array{creadas:int, actualizadas:int, omitidas:int, errores:int, examen:?string}
     */
    public function guardar(array $analisis, bool $omitirDuplicadas = true, ?int $examenId = null): array
    {
        $creadas = $actualizadas = $omitidas = 0;
        $ids = [];
        $areasCache = [];

        DB::transaction(function () use ($analisis, $omitirDuplicadas, &$creadas, &$actualizadas, &$omitidas, &$ids, &$areasCache) {
            foreach ($analisis['filas'] as $f) {
                if ($f['estado'] === 'error') {
                    continue;
                }
                if ($f['estado'] === 'duplicada' && $omitirDuplicadas) {
                    $omitidas++;
                    continue;
                }

                $clave = $this->clave($f['area']);
                $areasCache[$clave] ??= (AreaPregunta::whereRaw('LOWER(nombre) = ?', [mb_strtolower($f['area'])])->first()
                    ?? AreaPregunta::create(['nombre' => $f['area']]))->id;

                $datos = [
                    'id_area' => $areasCache[$clave],
                    'pregunta' => $f['pregunta'],
                    'justificacion' => $f['justificacion'],
                ];

                if ($f['estado'] === 'actualiza') {
                    $pregunta = Pregunta::find((int) $f['id']);
                    $pregunta->update($datos);
                    $pregunta->opciones()->delete();
                    $actualizadas++;
                } else {
                    $pregunta = Pregunta::create($datos);
                    $creadas++;
                }

                $orden = 1;
                foreach ($f['opciones'] as $letra => $texto) {
                    PreguntaOpcion::create([
                        'pregunta_id' => $pregunta->id,
                        'texto' => $texto,
                        'es_correcta' => $letra === $f['correcta'],
                        'orden' => $orden++,
                    ]);
                }
                $ids[] = $pregunta->id;
            }
        });

        $nombreExamen = null;
        if ($examenId && $ids && ($examen = ExamenGenerado::find($examenId))) {
            $examen->preguntas()->syncWithoutDetaching($ids);
            $examen->update(['numero_preguntas' => $examen->preguntas()->count()]);
            $nombreExamen = "{$examen->tipo_examen} #{$examen->id}";
        }

        return [
            'creadas' => $creadas,
            'actualizadas' => $actualizadas,
            'omitidas' => $omitidas,
            'errores' => $analisis['resumen']['error'] ?? 0,
            'examen' => $nombreExamen,
        ];
    }

    /** Detecta las columnas por encabezado; si no hay encabezados reconocibles, usa el orden fijo. */
    private function mapearColumnas(array $encabezados): array
    {
        $mapa = [];
        foreach ($encabezados as $idx => $texto) {
            $n = $this->clave((string) $texto);
            $n = trim(preg_replace('/\b(a|de|la|el)\s*-\s*[a-e]\b|\(.*?\)/u', '', $n)); // "respuesta correcta (a-d)" → "respuesta correcta"
            foreach (self::ENCABEZADOS as $campo => $sinonimos) {
                if (!isset($mapa[$campo]) && in_array($n, $sinonimos, true)) {
                    $mapa[$campo] = $idx;
                    break;
                }
            }
        }

        $reconocido = isset($mapa['pregunta']) && (isset($mapa['A']) || isset($mapa['correcta']));
        if (!$reconocido) {
            // Sin encabezados: la primera fila ya es una pregunta.
            $pareceEncabezado = str_contains($this->clave(implode(' ', $encabezados)), 'pregunta');

            return [self::POSICIONAL, $pareceEncabezado ? 1 : 0, ['modo' => 'orden fijo de columnas']];
        }

        $columnas = collect($mapa)->map(fn ($i, $campo) => trim((string) $encabezados[$i]))->all();

        return [$mapa, 1, $columnas];
    }

    private function resolverCorrecta(string $valor, array $opciones): ?string
    {
        $v = trim($valor);
        if ($v === '') {
            return null;
        }
        $mayus = strtoupper(rtrim($v, ').'));
        if (in_array($mayus, self::LETRAS, true) && isset($opciones[$mayus])) {
            return $mayus;
        }
        if (ctype_digit($v) && isset(self::LETRAS[(int) $v - 1], $opciones[self::LETRAS[(int) $v - 1]])) {
            return self::LETRAS[(int) $v - 1];
        }
        foreach ($opciones as $letra => $texto) {
            if ($this->clave($texto) === $this->clave($v)) {
                return $letra;
            }
        }

        return null;
    }

    /** Texto normalizado para comparar: minúsculas, sin acentos ni espacios repetidos. */
    private function clave(string $texto): string
    {
        $t = mb_strtolower(trim($texto));
        $t = strtr($t, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);

        return preg_replace('/\s+/u', ' ', preg_replace('/[^\p{L}\p{N}\s()\-]/u', ' ', $t));
    }
}
