<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\Asignatura;
use App\Models\ProgresoVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $plan = $request->get('plan');
        $materia = $request->get('materia');
        $tema = $request->get('tema');
        
        // Obtener parámetros de ordenamiento
        $ordenCampo = $request->get('orden_campo', 'id');
        $ordenDireccion = $request->get('orden_direccion', 'desc');
        
        // Validar que el campo de ordenamiento sea válido
        $camposPermitidos = ['id', 'titulo', 'materia', 'duracion', 'plan', 'created_at'];
        if (!in_array($ordenCampo, $camposPermitidos)) {
            $ordenCampo = 'id';
        }
        
        // Validar dirección de ordenamiento
        $ordenDireccion = in_array($ordenDireccion, ['asc', 'desc']) ? $ordenDireccion : 'desc';
        
        $videos = Video::when($search, function($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('titulo', 'like', "%{$search}%")
                        ->orWhere('materia', 'like', "%{$search}%")
                        ->orWhere('tema', 'like', "%{$search}%");
                });
            })
            ->when($materia, fn ($q, $materia) => $q->where('materia', $materia))
            ->when($tema, fn ($q, $tema) => $q->where('tema', 'like', "%{$tema}%"))
            ->when($plan !== null && $plan !== '', function($query) use ($plan) {
                return $query->where('plan', $plan);
            })
            ->orderBy($ordenCampo, $ordenDireccion)
            ->paginate(10);

        // Mantener los parámetros de ordenamiento en la paginación
        $videos->appends([
            'orden_campo' => $ordenCampo,
            'orden_direccion' => $ordenDireccion,
            'search' => $search,
            'materia' => $materia,
            'tema' => $tema,
            'plan' => $plan
        ]);
        
        // Estadísticas
        $totalVideos = Video::count();
        $conProgresos = Video::has('progresos')->count();
        $premium = Video::where('plan', true)->count();
        $publicos = Video::where('plan', false)->count();

        return \Inertia\Inertia::render('Admin/Videos/Index', [
            'videos' => $videos,
            'materias' => Asignatura::orderBy('nombre')->pluck('nombre'),
            'stats' => [
                'total' => $totalVideos,
                'conProgresos' => $conProgresos,
                'premium' => $premium,
                'publicos' => $publicos,
            ],
            'filters' => [
                'search' => $search,
                'materia' => $materia,
                'tema' => $tema,
                'plan' => $plan === null || $plan === '' ? null : (int) $plan,
            ],
        ]);
    }

    /**
     * Intenta obtener la duración de un video a partir de su enlace.
     * Soporta Vimeo (oEmbed) y YouTube (lengthSeconds del HTML).
     */
    public function obtenerDuracion(Request $request)
    {
        $link = trim((string) $request->get('link'));

        if ($link === '') {
            return response()->json(['success' => false, 'message' => 'Falta el enlace del video'], 422);
        }

        try {
            $segundos = null;

            if (str_contains($link, 'vimeo.com')) {
                // withoutVerifying: entornos WAMP locales no traen bundle CA para cURL
                $res = Http::withoutVerifying()->timeout(8)->get('https://vimeo.com/api/oembed.json', ['url' => $link]);
                if ($res->ok()) {
                    $segundos = (int) $res->json('duration');
                }
            } elseif (str_contains($link, 'youtube.com') || str_contains($link, 'youtu.be')) {
                $id = null;
                if (preg_match('/[?&]v=([A-Za-z0-9_\-]{6,})/', $link, $m)) {
                    $id = $m[1];
                } elseif (preg_match('#youtu\.be/([A-Za-z0-9_\-]{6,})#', $link, $m)) {
                    $id = $m[1];
                }
                if ($id) {
                    $html = Http::withoutVerifying()->timeout(8)->get("https://www.youtube.com/watch?v={$id}")->body();
                    if (preg_match('/"lengthSeconds":"(\d+)"/', $html, $m)) {
                        $segundos = (int) $m[1];
                    }
                }
            }

            if (!$segundos || $segundos <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo obtener la duración automáticamente para este enlace.',
                ], 200);
            }

            $duracion = sprintf('%02d:%02d:%02d', intdiv($segundos, 3600), intdiv($segundos % 3600, 60), $segundos % 60);

            return response()->json(['success' => true, 'segundos' => $segundos, 'duracion' => $duracion]);

        } catch (\Exception $e) {
            Log::warning('obtenerDuracion falló: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al consultar el proveedor de video.'], 200);
        }
    }

    public function create()
    {
        return redirect()->route('admin.videos.index');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'materia' => 'required|string|max:255',
            'tema' => 'required|string|max:255',
            'titulo' => 'required|string|max:255',
            'link' => 'required|url|max:500',
            'duracion' => 'nullable|string|max:50',
            'plan' => 'boolean'
        ]);
        
        try {
            Video::create([
                'materia' => $request->materia,
                'tema' => $request->tema,
                'titulo' => $request->titulo,
                'link' => $request->link,
                'duracion' => $request->duracion ?: '00:00:00',
                'plan' => $request->boolean('plan') ? 1 : 0,
            ]);

            return redirect()->route('admin.videos.index')
                ->with('success', 'Video creado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear el video: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function show($id)
    {
        $video = Video::with(['progresos.estudiante'])->findOrFail($id);
        
        // Calcular porcentaje de completado
        $totalProgresos = $video->progresos()->count();
        $completados = $video->progresos()->where('completado', true)->count();
        $porcentajeCompletado = $totalProgresos > 0 ? round(($completados / $totalProgresos) * 100) : 0;
        
        // Últimos progresos
        $ultimosProgresos = $video->progresos()
            ->with('estudiante')
            ->orderBy('fecha_visto', 'desc')
            ->limit(10)
            ->get();
        
        // Agregar porcentaje a cada progreso
        foreach ($ultimosProgresos as $progreso) {
            // Calcular porcentaje basado en duración estimada del video
            $duracionSegundos = $this->convertirDuracionASegundos($video->duracion);
            
            // Asegurar que ultimo_segundo sea numérico
            $ultimoSegundo = is_numeric($progreso->ultimo_segundo) ? (float)$progreso->ultimo_segundo : 0;
            
            if ($duracionSegundos > 0 && $ultimoSegundo > 0) {
                $progreso->porcentaje = round(($ultimoSegundo / $duracionSegundos) * 100);
                $progreso->porcentaje = min(100, max(0, $progreso->porcentaje));
            } else {
                $progreso->porcentaje = $progreso->completado ? 100 : 0;
            }
        }
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $video->id,
                    'materia' => $video->materia,
                    'tema' => $video->tema,
                    'titulo' => $video->titulo,
                    'link' => $video->link,
                    'duracion' => $video->duracion,
                    'plan' => $video->plan,
                    'progresos_count' => $totalProgresos,
                    'porcentaje_completado' => $porcentajeCompletado
                ]
            ]);
        }
        
        return redirect()->route('admin.videos.index');
    }
    
    /**
     * Convertir duración formato HH:MM:SS a segundos
     */
    private function convertirDuracionASegundos($duracion)
    {
        if (!$duracion || !is_string($duracion)) {
            return 3600; // Valor por defecto
        }
        
        $parts = explode(':', $duracion);
        
        if (count($parts) == 3) {
            // HH:MM:SS
            $hours = is_numeric($parts[0]) ? (int)$parts[0] : 0;
            $minutes = is_numeric($parts[1]) ? (int)$parts[1] : 0;
            $seconds = is_numeric($parts[2]) ? (int)$parts[2] : 0;
            return ($hours * 3600) + ($minutes * 60) + $seconds;
        } elseif (count($parts) == 2) {
            // MM:SS
            $minutes = is_numeric($parts[0]) ? (int)$parts[0] : 0;
            $seconds = is_numeric($parts[1]) ? (int)$parts[1] : 0;
            return ($minutes * 60) + $seconds;
        }
        
        // Si el formato es solo un número (segundos)
        if (is_numeric($duracion)) {
            return (int)$duracion;
        }
        
        return 3600; // Valor por defecto si no se puede parsear
    }
    
    public function edit($id)
    {
        return redirect()->route('admin.videos.index');
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'materia' => 'required|string|max:255',
            'tema' => 'required|string|max:255',
            'titulo' => 'required|string|max:255',
            'link' => 'required|url|max:500',
            'duracion' => 'nullable|string|max:50',
            'plan' => 'boolean'
        ]);
        
        try {
            $video = Video::findOrFail($id);
            $video->update([
                'materia' => $request->materia,
                'tema' => $request->tema,
                'titulo' => $request->titulo,
                'link' => $request->link,
                'duracion' => $request->duracion ?: '00:00:00',
                'plan' => $request->boolean('plan') ? 1 : 0,
            ]);
            
            return redirect()->route('admin.videos.index')
                ->with('success', 'Video actualizado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar el video: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function destroy($id)
    {
        try {
            $video = Video::findOrFail($id);
            
            if ($video->progresos()->count() > 0) {
                return redirect()->route('admin.videos.index')
                    ->with('error', 'No se puede eliminar el video porque tiene progresos asociados.');
            }
            
            $video->delete();
            
            return redirect()->route('admin.videos.index')
                ->with('success', 'Video eliminado exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('admin.videos.index')
                ->with('error', 'Error al eliminar el video: ' . $e->getMessage());
        }
    }
}