<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamenRealizado;
use App\Models\Estudiante;
use App\Models\ExamenGenerado;
use App\Models\Pregunta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExamenRealizadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request){
        $query = ExamenRealizado::with(['estudianteRel', 'examenGenerado']);
        
        $query = $this->applyFiltersAndSorting($query, $request);
        
        $examenes = $query->paginate(15)->withQueryString();

        $examenes->getCollection()->transform(function ($e) {
            $est = $e->estudianteRel;
            $hora = $e->hora_inicio && $e->hora_inicio != '00:00:00'
                ? \Carbon\Carbon::parse($e->hora_inicio)->format('H:i')
                : null;
            return [
                'id' => $e->id,
                'estudiante' => $est ? trim("{$est->nombre} {$est->paterno} {$est->materno}") : 'Estudiante #' . $e->estudiante,
                'examen_id' => $e->examen,
                'tipo_examen' => $e->examenGenerado?->tipo_examen ?? 'Simulador',
                'calificacion' => $e->calificacion !== null ? round($e->calificacion, 1) : null,
                'intento' => $e->intento,
                'fecha' => $e->fecha_inicio
                    ? \Carbon\Carbon::parse($e->fecha_inicio)->format('d/m/Y') . ($hora ? " · {$hora}" : '')
                    : null,
                'tiempo' => $e->tiempo,
            ];
        });

        return \Inertia\Inertia::render('Admin/ExamenesRealizados/Index', [
            'examenes' => $examenes,
            'stats' => $this->getEstadisticas($request),
            'tiposExamen' => ExamenGenerado::select('tipo_examen')->distinct()->pluck('tipo_examen'),
            'filters' => [
                'estudiante' => $request->estudiante,
                'tipo_examen' => $request->tipo_examen,
                'calificacion' => $request->calificacion,
                'intento' => $request->intento,
            ],
        ]);
    }
    
    private function applyFiltersAndSorting($query, Request $request)
    {
        if ($request->filled('estudiante')) {
            $search = $request->estudiante;
            $query->whereHas('estudianteRel', function($q) use ($search) {
                $q->where('nombre', 'LIKE', '%' . $search . '%')
                  ->orWhere('paterno', 'LIKE', '%' . $search . '%')
                  ->orWhere('materno', 'LIKE', '%' . $search . '%');
            });
        }
        
        if ($request->filled('examen')) {
            $query->whereHas('examenGenerado', function($q) use ($request) {
                $q->where('titulo', 'LIKE', '%' . $request->examen . '%');
            });
        }
        
        if ($request->filled('tipo_examen')) {
            $query->whereHas('examenGenerado', function($q) use ($request) {
                $q->where('tipo_examen', $request->tipo_examen);
            });
        }
        
        if ($request->filled('calificacion')) {
            if ($request->calificacion == 'excelente') {
                $query->where('calificacion', '>=', 80);
            } elseif ($request->calificacion == 'aprobado') {
                $query->whereBetween('calificacion', [60, 79]);
            } elseif ($request->calificacion == 'reprobado') {
                $query->where('calificacion', '<', 60);
            }
        }
        
        if ($request->filled('intento')) {
            $query->where('intento', $request->intento);
        }
        
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_inicio', '>=', Carbon::parse($request->fecha_desde));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_inicio', '<=', Carbon::parse($request->fecha_hasta));
        }
        
        $ordenCampo = $request->get('orden_campo', 'fecha');
        $ordenDireccion = $request->get('orden_direccion', 'desc');
        
        if ($ordenCampo == 'estudiante') {
            $query->leftJoin('estudiante', 'examen_realizado.estudiante', '=', 'estudiante.id')
                  ->select('examen_realizado.*')
                  ->orderByRaw("CONCAT(estudiante.nombre, ' ', estudiante.paterno, ' ', COALESCE(estudiante.materno, '')) {$ordenDireccion}");
        } elseif ($ordenCampo == 'examen') {
            $query->leftJoin('Examen_generado', 'examen_realizado.examen', '=', 'Examen_generado.id')
                  ->select('examen_realizado.*')
                  ->orderBy('Examen_generado.titulo', $ordenDireccion);
        } elseif ($ordenCampo == 'tipo_examen') {
            $query->leftJoin('Examen_generado', 'examen_realizado.examen', '=', 'Examen_generado.id')
                  ->select('examen_realizado.*')
                  ->orderBy('Examen_generado.tipo_examen', $ordenDireccion);
        } elseif (in_array($ordenCampo, ['calificacion', 'intento', 'fecha_inicio', 'tiempo'])) {
            $query->orderBy($ordenCampo, $ordenDireccion);
        } else {
            $query->orderBy('fecha_inicio', 'desc')->orderBy('hora_inicio', 'desc');
        }
        
        return $query;
    }
    
    private function getEstadisticas(Request $request = null)
    {
        // Estadísticas SIN aplicar filtros (totales globales)
        $total = ExamenRealizado::count();
        $promedio = round(ExamenRealizado::avg('calificacion') ?? 0, 1);
        $excelentes = ExamenRealizado::where('calificacion', '>=', 80)->count();
        $aprobados = ExamenRealizado::whereBetween('calificacion', [60, 79])->count();
        $reprobados = ExamenRealizado::where('calificacion', '<', 60)->count();
        
        return [
            'total' => $total,
            'promedio' => $promedio,
            'excelentes' => $excelentes,
            'aprobados' => $aprobados,      // ✅ ESTO ES CLAVE
            'reprobados' => $reprobados,
        ];
    }
    
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $examen = ExamenRealizado::with(['estudianteRel', 'examenGenerado'])->findOrFail($id);
        
        // 🔥 CORRECCIÓN: Decodificar las respuestas correctamente
        $respuestasProcesadas = [];
        
        // Obtener las respuestas (pueden ser array o string JSON)
        $respuestasRaw = $examen->respuestas;
        
        // Si es string JSON, decodificarlo
        if (is_string($respuestasRaw)) {
            $respuestasRaw = json_decode($respuestasRaw, true);
        }
        
        // 🔥 IMPORTANTE: La estructura tiene una clave 'respuestas' que contiene el array
        $listaRespuestas = [];
        if (is_array($respuestasRaw) && isset($respuestasRaw['respuestas'])) {
            $listaRespuestas = $respuestasRaw['respuestas'];
        } elseif (is_array($respuestasRaw)) {
            // Si ya es el array directamente (por si acaso)
            $listaRespuestas = $respuestasRaw;
        }
        
        // Recolectar IDs de preguntas
        $preguntaIds = [];
        foreach ($listaRespuestas as $respuesta) {
            if (isset($respuesta['pregunta_id'])) {
                $preguntaIds[] = $respuesta['pregunta_id'];
            }
        }
        
        // Cargar todas las preguntas (con sus opciones) de una sola vez
        $preguntas = Pregunta::with('opciones')->whereIn('id', $preguntaIds)->get()->keyBy('id');
        
        // Procesar cada respuesta
        foreach ($listaRespuestas as $index => $respuesta) {
            $preguntaId = $respuesta['pregunta_id'] ?? null;
            $pregunta = $preguntaId ? ($preguntas[$preguntaId] ?? null) : null;
            
            // Determinar si es correcta (usa 'estatus' o 'correcta')
            $esCorrecta = false;
            if (isset($respuesta['estatus'])) {
                $esCorrecta = $respuesta['estatus'] === 'correcta';
            } elseif (isset($respuesta['correcta'])) {
                $esCorrecta = $respuesta['correcta'] === true || $respuesta['correcta'] === 'true';
            }
            
            $respuestasProcesadas[] = [
                'numero' => $index + 1,
                'pregunta' => $respuesta['pregunta'] ?? ($pregunta ? $pregunta->pregunta : 'Pregunta no disponible'),
                'respuesta' => $respuesta['respuesta'] ?? 'No respondida',
                'correcta' => $esCorrecta,
                'respuesta_correcta' => $respuesta['respuesta_correcta'] ?? ($pregunta ? $pregunta->opciones->firstWhere('es_correcta', true)?->texto : 'No disponible'),
                'justificacion' => $respuesta['justificacion'] ?? ($pregunta ? $pregunta->justificacion : null),
            ];
        }
        
        $totalPreguntas = count($respuestasProcesadas);
        $correctas = collect($respuestasProcesadas)->where('correcta', true)->count();
        $est = $examen->estudianteRel;

        return \Inertia\Inertia::render('Admin/ExamenesRealizados/Show', [
            'examen' => [
                'id' => $examen->id,
                'estudiante' => $est ? trim("{$est->nombre} {$est->paterno} {$est->materno}") : 'Estudiante #' . $examen->estudiante,
                'estudiante_id' => $examen->estudiante,
                'tipo_examen' => $examen->examenGenerado?->tipo_examen ?? 'Simulador',
                'calificacion' => $examen->calificacion !== null ? round($examen->calificacion, 1) : null,
                'intento' => $examen->intento,
                'fecha_inicio' => $examen->fecha_inicio ? \Carbon\Carbon::parse($examen->fecha_inicio)->format('d/m/Y') : null,
                'tiempo' => $examen->tiempo,
            ],
            'resumen' => [
                'total' => $totalPreguntas,
                'correctas' => $correctas,
                'incorrectas' => $totalPreguntas - $correctas,
            ],
            'respuestas' => $respuestasProcesadas,
        ]);
    }
    
    public function export(Request $request)
    {
        $query = ExamenRealizado::with(['estudianteRel', 'examenGenerado']);
        $query = $this->applyFiltersAndSorting($query, $request);
        
        $examenes = $query->get();
        
        $filename = 'examenes_realizados_' . Carbon::now()->format('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'w');
        
        fputcsv($handle, [
            'ID', 'Estudiante', 'Examen', 'Tipo', 'Calificación', 'Intento', 
            'Fecha Inicio', 'Fecha Fin', 'Tiempo', 'Fecha Creación'
        ]);
        
        foreach ($examenes as $examen) {
            fputcsv($handle, [
                $examen->id,
                $examen->estudianteRel?->nombre_completo ?? 'N/A',
                $examen->examenGenerado?->titulo ?? 'N/A',
                $examen->examenGenerado?->tipo_examen ?? 'N/A',
                $examen->calificacion ?? 0,
                $examen->intento ?? 1,
                $examen->fecha_inicio ? Carbon::parse($examen->fecha_inicio)->format('d/m/Y H:i') : 'N/A',
                $examen->fecha_fin ? Carbon::parse($examen->fecha_fin)->format('d/m/Y H:i') : 'N/A',
                $examen->tiempo ?? 'N/A',
                $examen->examenGenerado?->created_at?->format('d/m/Y') ?? 'N/A',
            ]);
        }
        
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        
        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    
    public function destroy($id)
    {
        try {
            $examen = ExamenRealizado::findOrFail($id);
            
            Log::info('Examen eliminado', [
                'id' => $examen->id,
                'estudiante' => $examen->estudiante,
                'examen' => $examen->examen,
                'ip' => request()->ip(),
                'user_id' => auth()->id() ?? 'unknown'
            ]);
            
            $examen->delete();
            
            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Examen eliminado correctamente']);
            }
            
            return redirect()->route('admin.examenes-realizados.index')
                ->with('success', 'Examen eliminado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al eliminar examen: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error al eliminar el examen'], 500);
            }
            
            return redirect()->route('admin.examenes-realizados.index')
                ->with('error', 'Error al eliminar el examen: ' . $e->getMessage());
        }
    }
    
    public function estadisticas(Request $request)
    {
        $stats = $this->getEstadisticas($request);
        return response()->json($stats);
    }
}