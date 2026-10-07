<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PreguntasExport;
use App\Http\Controllers\Controller;
use App\Imports\PreguntasImport;
use App\Models\Pregunta;
use App\Models\PreguntaOpcion;
use App\Models\AreaPregunta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class PreguntaController extends Controller
{
    // ==================== MÉTODOS PARA PREGUNTAS ====================

    // Listar todas las preguntas (gestión) CON FILTROS Y ORDENAMIENTO
    public function indexPreguntas(Request $request)
    {
        $query = Pregunta::with(['area', 'opciones']);

        // Filtro por búsqueda (pregunta)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('pregunta', 'LIKE', "%{$search}%");
        }

        // Filtro por respuesta correcta
        if ($request->filled('respuesta_correcta')) {
            $texto = $request->respuesta_correcta;
            $query->whereHas('opciones', function ($q) use ($texto) {
                $q->where('es_correcta', true)->where('texto', 'LIKE', "%{$texto}%");
            });
        }

        // Filtro por área
        if ($request->filled('id_area')) {
            $query->where('id_area', $request->id_area);
        }

        // Filtro por justificación (si tiene o no)
        if ($request->filled('has_justificacion')) {
            if ($request->has_justificacion == 'si') {
                $query->whereNotNull('justificacion');
            } elseif ($request->has_justificacion == 'no') {
                $query->whereNull('justificacion');
            }
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');

        // Permitir ordenamiento solo por columnas válidas
        $allowedSorts = ['id', 'pregunta', 'id_area', 'justificacion'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('id', 'desc');
        }

        $preguntas = $query->paginate(10)->withQueryString();

        $preguntas->getCollection()->transform(fn ($p) => [
            'id' => $p->id,
            'id_area' => $p->id_area,
            'area' => $p->area?->nombre,
            'pregunta' => $p->pregunta,
            'opciones' => $p->opciones->map(fn ($o) => [
                'id' => $o->id,
                'texto' => $o->texto,
                'es_correcta' => $o->es_correcta,
            ])->values(),
            'respuesta_correcta' => $p->opciones->firstWhere('es_correcta', true)?->texto,
            'justificacion' => $p->justificacion,
        ]);

        return \Inertia\Inertia::render('Admin/Preguntas/Index', [
            'preguntas' => $preguntas,
            'areas' => AreaPregunta::orderBy('nombre')->get(['id', 'nombre']),
            'examenes' => \App\Models\ExamenGenerado::withCount('preguntas')->orderByDesc('id')->get(['id', 'tipo_examen'])
                ->map(fn ($e) => ['value' => $e->id, 'label' => "{$e->tipo_examen} #{$e->id} · {$e->preguntas_count} preguntas"]),
            'stats' => [
                'total' => Pregunta::count(),
                'areas' => AreaPregunta::count(),
                'conJustificacion' => Pregunta::whereNotNull('justificacion')->count(),
                'sinJustificacion' => Pregunta::whereNull('justificacion')->count(),
            ],
            'filters' => [
                'search' => $request->search,
                'respuesta_correcta' => $request->respuesta_correcta,
                'id_area' => $request->id_area ? (int) $request->id_area : null,
                'has_justificacion' => $request->has_justificacion,
            ],
        ]);
    }

    public function createPregunta()
    {
        return redirect()->route('admin.preguntas.index');
    }

    private function reglasOpciones(): array
    {
        return [
            'opciones' => 'required|array|min:3|max:4',
            'opciones.*.texto' => 'required|string|max:255',
            'opciones.*.es_correcta' => 'required|boolean',
        ];
    }

    // Verifica que exactamente una opción esté marcada como correcta.
    private function validarUnaCorrecta(Request $request, $validator): void
    {
        $validator->after(function ($validator) use ($request) {
            $opciones = $request->input('opciones', []);
            $correctas = collect($opciones)->filter(fn ($o) => (bool) ($o['es_correcta'] ?? false))->count();
            if ($correctas !== 1) {
                $validator->errors()->add('opciones', 'Debes marcar exactamente una opción como correcta.');
            }
        });
    }

    // Reemplaza las opciones de una pregunta con las recibidas del formulario.
    private function guardarOpciones(Pregunta $pregunta, array $opciones): void
    {
        $pregunta->opciones()->delete();
        foreach (array_values($opciones) as $i => $op) {
            PreguntaOpcion::create([
                'pregunta_id' => $pregunta->id,
                'texto' => $op['texto'],
                'es_correcta' => (bool) $op['es_correcta'],
                'orden' => $i + 1,
            ]);
        }
    }

    // Guardar nueva pregunta
    public function storePregunta(Request $request)
    {
        $validator = \Validator::make($request->all(), array_merge([
            'id_area' => 'required|exists:area_preguntas,id',
            'pregunta' => 'required|string',
            'justificacion' => 'nullable|string',
        ], $this->reglasOpciones()));
        $this->validarUnaCorrecta($request, $validator);
        $validator->validate();

        try {
            DB::transaction(function () use ($request) {
                $pregunta = Pregunta::create($request->only('id_area', 'pregunta', 'justificacion'));
                $this->guardarOpciones($pregunta, $request->input('opciones'));
            });
            return redirect()->route('admin.preguntas.index')
                ->with('success', 'Pregunta creada correctamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la pregunta: ' . $e->getMessage());
        }
    }

    // Mostrar una pregunta específica (API para modal)
    public function showPregunta($id)
    {
        try {
            $pregunta = Pregunta::with(['area', 'opciones'])->findOrFail($id);

            // Siempre devolver JSON para peticiones AJAX
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'id' => $pregunta->id,
                    'pregunta' => $pregunta->pregunta,
                    'opciones' => $pregunta->opciones->map(fn ($o) => [
                        'id' => $o->id,
                        'texto' => $o->texto,
                        'es_correcta' => $o->es_correcta,
                    ]),
                    'justificacion' => $pregunta->justificacion,
                    'area' => $pregunta->area ? [
                        'id' => $pregunta->area->id,
                        'nombre' => $pregunta->area->nombre
                    ] : null
                ]);
            }

            return redirect()->route('admin.preguntas.index');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pregunta no encontrada'
                ], 404);
            }
            abort(404);
        }
    }

    public function editPregunta($id)
    {
        return redirect()->route('admin.preguntas.index');
    }

    // Actualizar pregunta
    public function updatePregunta(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), array_merge([
            'id_area' => 'required|exists:area_preguntas,id',
            'pregunta' => 'required|string',
            'justificacion' => 'nullable|string',
        ], $this->reglasOpciones()));
        $this->validarUnaCorrecta($request, $validator);
        $validator->validate();

        try {
            $pregunta = Pregunta::findOrFail($id);
            DB::transaction(function () use ($request, $pregunta) {
                $pregunta->update($request->only('id_area', 'pregunta', 'justificacion'));
                $this->guardarOpciones($pregunta, $request->input('opciones'));
            });
            return redirect()->route('admin.preguntas.index')
                ->with('success', 'Pregunta actualizada correctamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la pregunta: ' . $e->getMessage());
        }
    }

    // Eliminar pregunta
    public function destroyPregunta($id)
    {
        try {
            $pregunta = Pregunta::findOrFail($id);

            // Verificar si tiene apoyos asociados
            if($pregunta->apoyos()->count() > 0) {
                return redirect()->route('admin.preguntas.index')
                    ->with('error', 'No se puede eliminar la pregunta porque tiene apoyos asociados');
            }

            $pregunta->delete(); // las opciones se eliminan en cascada (FK onDelete cascade)
            return redirect()->route('admin.preguntas.index')
                ->with('success', 'Pregunta eliminada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('admin.preguntas.index')
                ->with('error', 'Error al eliminar la pregunta: ' . $e->getMessage());
        }
    }

    // ==================== EXCEL / CSV: EXPORTAR / IMPORTAR ====================

    // Descargar todas las preguntas en Excel (también sirve como plantilla para cargar)
    public function exportarExcel()
    {
        return Excel::download(new PreguntasExport(), 'preguntas-' . now()->format('Y-m-d') . '.xlsx', ExcelFormat::XLSX);
    }

    // Descargar todas las preguntas en CSV (también sirve como plantilla para cargar)
    public function exportarCsv()
    {
        return Excel::download(new PreguntasExport(), 'preguntas-' . now()->format('Y-m-d') . '.csv', ExcelFormat::CSV);
    }

    // Plantilla vacía con ejemplos + hoja de instrucciones
    public function plantilla()
    {
        return Excel::download(new \App\Exports\PlantillaPreguntasExport(), 'plantilla-preguntas.xlsx', ExcelFormat::XLSX);
    }

    /**
     * Paso 1: analiza el archivo SIN guardar y devuelve la vista previa (JSON).
     * El archivo se guarda temporalmente y se identifica con un token para el paso 2.
     */
    public function analizarImportacion(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|max:10240|mimes:xlsx,xls,csv,txt',
        ], ['archivo.max' => 'El archivo no puede pesar más de 10 MB.']);

        // Limpia vistas previas que nunca se confirmaron (más de un día).
        $disco = \Illuminate\Support\Facades\Storage::disk('local');
        foreach ($disco->files('importaciones') as $viejo) {
            if ($disco->lastModified($viejo) < now()->subDay()->getTimestamp()) {
                $disco->delete($viejo);
            }
        }

        $archivo = $request->file('archivo');
        $ext = strtolower($archivo->getClientOriginalExtension()) ?: 'xlsx';
        $token = (string) \Illuminate\Support\Str::uuid();
        $ruta = $archivo->storeAs('importaciones', "{$token}.{$ext}", 'local');

        try {
            $analisis = (new PreguntasImport())->analizar($ruta, 'local', $this->tipoExcel($ext));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($ruta);
            Log::error('Error al analizar preguntas: ' . $e->getMessage());

            return response()->json(['message' => 'No se pudo leer el archivo. Revisa que sea un Excel o CSV válido.'], 422);
        }

        if ($analisis['resumen']['total'] === 0) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($ruta);

            return response()->json(['message' => 'El archivo no tiene preguntas.'], 422);
        }

        return response()->json([
            'token' => "{$token}.{$ext}",
            'nombre' => $archivo->getClientOriginalName(),
            'resumen' => $analisis['resumen'],
            'columnas' => $analisis['columnas'],
            // Para no mandar archivos enormes al navegador, la vista previa muestra hasta 1000 filas.
            'filas' => collect($analisis['filas'])->take(1000)->map(fn ($f) => [
                'fila' => $f['fila'],
                'estado' => $f['estado'],
                'motivo' => $f['motivo'],
                'area' => $f['area'],
                'area_nueva' => $f['area_nueva'] ?? false,
                'pregunta' => $f['pregunta'],
                'opciones' => count($f['opciones']),
                'correcta' => $f['correcta'] ? ($f['opciones'][$f['correcta']] ?? null) : null,
                'letra' => $f['correcta'],
            ])->values(),
        ]);
    }

    /** Paso 2: guarda las filas válidas del archivo analizado. */
    public function importar(Request $request)
    {
        $datos = $request->validate([
            'token' => ['required', 'string', 'regex:/^[0-9a-f\-]{36}\.(xlsx|xls|csv|txt)$/'],
            'omitir_duplicadas' => ['boolean'],
            'examen_id' => ['nullable', 'exists:examen_generado,id'],
        ]);

        $ruta = 'importaciones/' . $datos['token'];
        $disco = \Illuminate\Support\Facades\Storage::disk('local');
        if (!$disco->exists($ruta)) {
            return redirect()->route('admin.preguntas.index')
                ->with('error', 'La vista previa expiró. Vuelve a subir el archivo.');
        }

        try {
            $import = new PreguntasImport();
            $ext = pathinfo($ruta, PATHINFO_EXTENSION);
            $analisis = $import->analizar($ruta, 'local', $this->tipoExcel($ext));
            $r = $import->guardar($analisis, $request->boolean('omitir_duplicadas', true), $datos['examen_id'] ?? null);
        } catch (\Throwable $e) {
            Log::error('Error al importar preguntas: ' . $e->getMessage());

            return redirect()->route('admin.preguntas.index')
                ->with('error', 'Error al importar el archivo: ' . $e->getMessage());
        } finally {
            $disco->delete($ruta);
        }

        $partes = array_filter([
            $r['creadas'] ? "{$r['creadas']} creada(s)" : null,
            $r['actualizadas'] ? "{$r['actualizadas']} actualizada(s)" : null,
            $r['omitidas'] ? "{$r['omitidas']} duplicada(s) omitida(s)" : null,
            $r['errores'] ? "{$r['errores']} fila(s) con error sin importar" : null,
        ]);
        $mensaje = 'Importación completa: ' . ($partes ? implode(' · ', $partes) : 'no hubo cambios') . '.';
        if ($r['examen']) {
            $mensaje .= " Se agregaron al examen {$r['examen']}.";
        }

        return redirect()->route('admin.preguntas.index')->with($r['errores'] ? 'warning' : 'success', $mensaje);
    }

    private function tipoExcel(string $ext): string
    {
        return match (strtolower($ext)) {
            'csv', 'txt' => ExcelFormat::CSV,
            'xls' => ExcelFormat::XLS,
            default => ExcelFormat::XLSX,
        };
    }

    // ==================== MÉTODOS ADICIONALES ÚTILES ====================

    // Obtener preguntas por área (para API o filtros)
    public function getPreguntasByArea($areaId)
    {
        $preguntas = Pregunta::where('id_area', $areaId)->with(['area', 'opciones'])->get();
        return response()->json($preguntas);
    }

    // Obtener una pregunta específica con su justificación (para API)
    public function getPreguntaConJustificacion($id)
    {
        $pregunta = Pregunta::with(['area', 'opciones'])->findOrFail($id);
        $correcta = $pregunta->opciones->firstWhere('es_correcta', true);

        return response()->json([
            'id' => $pregunta->id,
            'pregunta' => $pregunta->pregunta,
            'respuesta_correcta' => $correcta?->texto,
            'justificacion' => $pregunta->justificacion ?? "La respuesta correcta es: {$correcta?->texto}",
            'opciones' => $pregunta->opciones->map(fn ($o) => ['id' => $o->id, 'texto' => $o->texto, 'es_correcta' => $o->es_correcta]),
            'area' => $pregunta->area->nombre ?? 'Sin área'
        ]);
    }

    // Método para exportar preguntas con justificación (útil para reportes)
    public function exportarPreguntasConJustificacion()
    {
        $preguntas = Pregunta::with(['area', 'opciones'])
            ->whereNotNull('justificacion')
            ->get();

        return response()->json($preguntas);
    }

    // Método para actualizar solo la justificación de una pregunta
    public function updateJustificacion(Request $request, $id)
    {
        $request->validate([
            'justificacion' => 'nullable|string'
        ]);

        try {
            $pregunta = Pregunta::findOrFail($id);
            $pregunta->justificacion = $request->justificacion;
            $pregunta->save();

            return response()->json([
                'success' => true,
                'message' => 'Justificación actualizada correctamente',
                'justificacion' => $pregunta->justificacion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la justificación: ' . $e->getMessage()
            ], 500);
        }
    }
}
