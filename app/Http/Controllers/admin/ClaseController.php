<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clase;
use App\Models\Asignatura;
use App\Models\Video;
use App\Models\RecursoClase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator; 

class ClaseController extends Controller
{
    public function index(Request $request)
    {
        // Obtener todas las clases con relaciones
        $query = Clase::with('asignatura', 'recursos', 'video');
        
        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre_clase', 'like', "%{$search}%")
                  ->orWhereHas('asignatura', function($q2) use ($search) {
                      $q2->where('nombre', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filtro por materia
        if ($request->filled('asignatura_id')) {
            $query->where('id_asignatura', $request->asignatura_id);
        }

        // Filtro por acceso (gratis / premium)
        if ($request->filled('acceso')) {
            $query->where('gratis', $request->acceso === 'gratis');
        }

        // Filtro por recursos
        if ($request->filled('recurso')) {
            match ($request->recurso) {
                'video' => $query->whereNotNull('link')->where('link', '!=', ''),
                'material' => $query->whereNotNull('url')->where('url', '!=', ''),
                'sin' => $query
                    ->where(fn ($q) => $q->whereNull('link')->orWhere('link', ''))
                    ->where(fn ($q) => $q->whereNull('url')->orWhere('url', '')),
                default => null,
            };
        }
        
        // Obtener todas las clases (sin paginar aún)
        $clasesCollection = $query->get();
        
        // ORDENAMIENTO
        $ordenCampo = $request->get('orden_campo', 'asignatura');
        $ordenDireccion = $request->get('orden_direccion', 'asc');
        
        // Ordenar la colección según el campo seleccionado
        if ($ordenCampo == 'id') {
            $clasesCollection = $ordenDireccion == 'asc' 
                ? $clasesCollection->sortBy('id') 
                : $clasesCollection->sortByDesc('id');
        } 
        elseif ($ordenCampo == 'asignatura') {
            // Materia (asc/desc) y dentro de cada materia por número de clase
            $clasesCollection = $clasesCollection
                ->sortBy('num_clase')
                ->{$ordenDireccion == 'asc' ? 'sortBy' : 'sortByDesc'}(
                    fn ($item) => $item->asignatura ? $item->asignatura->nombre : ''
                );
        }
        elseif ($ordenCampo == 'num_clase') {
            $clasesCollection = $ordenDireccion == 'asc' 
                ? $clasesCollection->sortBy('num_clase') 
                : $clasesCollection->sortByDesc('num_clase');
        }
        elseif ($ordenCampo == 'nombre_clase') {
            $clasesCollection = $ordenDireccion == 'asc' 
                ? $clasesCollection->sortBy('nombre_clase') 
                : $clasesCollection->sortByDesc('nombre_clase');
        }
        elseif ($ordenCampo == 'video') {
            $clasesCollection = $ordenDireccion == 'asc' 
                ? $clasesCollection->sortBy(function($item) {
                    return $item->link ? 1 : 0;
                }) 
                : $clasesCollection->sortByDesc(function($item) {
                    return $item->link ? 1 : 0;
                });
        }
        elseif ($ordenCampo == 'material') {
            $clasesCollection = $ordenDireccion == 'asc' 
                ? $clasesCollection->sortBy(function($item) {
                    return $item->url ? 1 : 0;
                }) 
                : $clasesCollection->sortByDesc(function($item) {
                    return $item->url ? 1 : 0;
                });
        }
        
        // Paginar la colección manualmente
        $perPage = 15;
        $currentPage = $request->get('page', 1);
        $currentItems = $clasesCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $clases = new LengthAwarePaginator(
            $currentItems,
            $clasesCollection->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // Estadísticas
        $totalClases = Clase::count();
        $conVideo = Clase::whereNotNull('link')->where('link', '!=', '')->count();
        $conMaterial = Clase::whereNotNull('url')->where('url', '!=', '')->count();
        $gratuitas = Clase::where('gratis', true)->count();
        
        // Para selects
        $asignaturas = Asignatura::orderBy('nombre', 'asc')->get();

        // Videos del catálogo ("los que se suben") para elegir el video de la clase
        $videos = Video::orderBy('materia')->orderBy('tema')
            ->get(['id', 'materia', 'tema', 'titulo', 'link', 'duracion', 'plan'])
            ->map(fn ($v) => [
                'id' => $v->id,
                'materia' => $v->materia,
                'tema' => $v->tema,
                'titulo' => $v->titulo,
                'link' => $v->link,
                'duracion' => $v->duracion && $v->duracion !== '00:00:00' ? $v->duracion : null,
                'plan' => (bool) $v->plan,
            ]);

        $clases->getCollection()->transform(fn ($c) => [
            'id' => $c->id,
            'id_asignatura' => $c->id_asignatura,
            'id_video' => $c->id_video,
            'asignatura' => $c->asignatura?->nombre,
            'num_clase' => $c->num_clase,
            'nombre_clase' => $c->nombre_clase,
            'link' => $c->link ?: $c->video?->link,
            'url' => $c->url,
            'gratis' => (bool) $c->gratis,
            'recursos_count' => $c->recursos->count(),
            'recursos' => $c->recursos->map(fn ($r) => [
                'id' => $r->id,
                'titulo' => $r->titulo,
                'tipo' => $r->tipo,
                'url' => $r->url,
                'descripcion' => $r->descripcion,
            ])->values(),
            'video_titulo' => $c->video?->titulo,
            'video_duracion' => $c->video && $c->video->duracion && $c->video->duracion !== '00:00:00' ? $c->video->duracion : null,
        ]);

        return \Inertia\Inertia::render('Admin/Clases/Index', [
            'clases' => $clases,
            'asignaturas' => $asignaturas->map(fn ($a) => ['id' => $a->id, 'nombre' => $a->nombre]),
            'videos' => $videos,
            'tiposRecurso' => collect(RecursoClase::TIPOS)->map(fn ($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])->values(),
            'stats' => [
                'total' => $totalClases,
                'conVideo' => $conVideo,
                'conMaterial' => $conMaterial,
                'gratuitas' => $gratuitas,
            ],
            'filters' => [
                'search' => $request->search,
                'asignatura_id' => $request->asignatura_id ? (int) $request->asignatura_id : null,
                'recurso' => $request->recurso,
                'acceso' => $request->acceso,
            ],
        ]);
    }

    public function create()
    {
        return redirect()->route('admin.clases.index');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'id_asignatura' => 'required|exists:asignatura,id',
            'id_video' => 'nullable|exists:videos,id',
            'num_clase' => 'required|integer|min:1',
            'nombre_clase' => 'required|string|max:255',
            'link' => 'nullable|string|max:500',
            'url' => 'nullable|string|max:500',
            'gratis' => 'boolean',
            'recursos' => 'nullable|array',
            'recursos.*.titulo' => 'required_with:recursos.*.url|string|max:255',
            'recursos.*.tipo' => ['required_with:recursos.*.url', 'string', \Illuminate\Validation\Rule::in(array_keys(\App\Models\RecursoClase::TIPOS))],
            'recursos.*.url' => 'nullable|url|max:2000',
            'recursos.*.descripcion' => 'nullable|string|max:1000',
        ]);

        try {
            // Verificar duplicado de número de clase
            $existe = Clase::where('id_asignatura', $request->id_asignatura)
                           ->where('num_clase', $request->num_clase)
                           ->exists();
            
            if ($existe) {
                return redirect()->back()->with('error', 'Ya existe una clase con el número ' . $request->num_clase . ' para esta asignatura')->withInput();
            }
            
            // El video sale del catálogo: guardamos también su link por compatibilidad
            $video = $request->id_video ? Video::find($request->id_video) : null;

            $clase = Clase::create([
                'id_asignatura' => $request->id_asignatura,
                'id_video' => $video?->id,
                'num_clase' => $request->num_clase,
                'nombre_clase' => $request->nombre_clase,
                'link' => $video?->link ?: $request->link,
                'url' => $request->url,
                'gratis' => $request->boolean('gratis'),
            ]);
            
            // Guardar recursos adicionales
            if ($request->has('recursos')) {
                foreach ($request->recursos as $index => $recursoData) {
                    if (!empty($recursoData['titulo']) && !empty($recursoData['url'])) {
                        RecursoClase::create([
                            'id_clase' => $clase->id,
                            'titulo' => $recursoData['titulo'],
                            'tipo' => $recursoData['tipo'],
                            'url' => $recursoData['url'],
                            'descripcion' => $recursoData['descripcion'] ?? null,
                            'orden' => $index
                        ]);
                    }
                }
            }
            
            return redirect()->route('admin.clases.index')->with('success', 'Clase creada exitosamente con ' . ($request->recursos ? count(array_filter($request->recursos, function($r) { return !empty($r['titulo']); })) : 0) . ' recursos adicionales');
        } catch (\Exception $e) {
            Log::error('Error al crear clase: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al crear la clase: ' . $e->getMessage())->withInput();
        }
    }
    
    public function show($id)
    {
        try {
            $clase = Clase::with('asignatura', 'recursos')->findOrFail($id);
            $tiposRecursos = RecursoClase::TIPOS;
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $clase
                ]);
            }
            
            return redirect()->route('admin.clases.index');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al obtener los datos'
                ], 500);
            }
            return redirect()->route('admin.clases.index')->with('error', 'Clase no encontrada');
        }
    }

    public function edit($id)
    {
        return redirect()->route('admin.clases.index');
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_asignatura' => 'required|exists:asignatura,id',
            'id_video' => 'nullable|exists:videos,id',
            'num_clase' => 'required|integer|min:1',
            'nombre_clase' => 'required|string|max:255',
            'link' => 'nullable|string|max:500',
            'url' => 'nullable|string|max:500',
            'gratis' => 'boolean',
            'recursos' => 'nullable|array',
            'recursos.*.titulo' => 'required_with:recursos.*.url|string|max:255',
            'recursos.*.tipo' => ['required_with:recursos.*.url', 'string', \Illuminate\Validation\Rule::in(array_keys(\App\Models\RecursoClase::TIPOS))],
            'recursos.*.url' => 'nullable|url|max:2000',
            'recursos.*.descripcion' => 'nullable|string|max:1000',
            'recursos_eliminar' => 'nullable|array',
        ]);
        
        try {
            $clase = Clase::findOrFail($id);
            
            // Verificar duplicado excluyendo la clase actual
            $existe = Clase::where('id_asignatura', $request->id_asignatura)
                           ->where('num_clase', $request->num_clase)
                           ->where('id', '!=', $id)
                           ->exists();
            
            if ($existe) {
                return redirect()->back()->with('error', 'Ya existe una clase con el número ' . $request->num_clase . ' para esta asignatura')->withInput();
            }
            
            $video = $request->id_video ? Video::find($request->id_video) : null;

            $clase->update([
                'id_asignatura' => $request->id_asignatura,
                'id_video' => $video?->id,
                'num_clase' => $request->num_clase,
                'nombre_clase' => $request->nombre_clase,
                'link' => $video?->link ?: $request->link,
                'url' => $request->url,
                'gratis' => $request->boolean('gratis'),
            ]);
            
            // Eliminar recursos marcados para eliminar
            if ($request->has('recursos_eliminar')) {
                RecursoClase::whereIn('id', $request->recursos_eliminar)->delete();
            }
            
            // Actualizar o crear recursos existentes
            if ($request->has('recursos')) {
                foreach ($request->recursos as $index => $recursoData) {
                    if (!empty($recursoData['titulo']) && !empty($recursoData['url'])) {
                        if (isset($recursoData['id']) && $recursoData['id']) {
                            // Actualizar recurso existente
                            $recurso = RecursoClase::find($recursoData['id']);
                            if ($recurso && $recurso->id_clase == $clase->id) {
                                $recurso->update([
                                    'titulo' => $recursoData['titulo'],
                                    'tipo' => $recursoData['tipo'],
                                    'url' => $recursoData['url'],
                                    'descripcion' => $recursoData['descripcion'] ?? null,
                                    'orden' => $index
                                ]);
                            }
                        } else {
                            // Crear nuevo recurso
                            RecursoClase::create([
                                'id_clase' => $clase->id,
                                'titulo' => $recursoData['titulo'],
                                'tipo' => $recursoData['tipo'],
                                'url' => $recursoData['url'],
                                'descripcion' => $recursoData['descripcion'] ?? null,
                                'orden' => $index
                            ]);
                        }
                    }
                }
            }
            
            return redirect()->route('admin.clases.index')->with('success', 'Clase actualizada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error al actualizar clase: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar la clase: ' . $e->getMessage())->withInput();
        }
    }
    
    public function destroy($id)
    {
        try {
            $clase = Clase::findOrFail($id);
            // Los recursos se eliminarán automáticamente por cascade
            $clase->delete();
            
            return redirect()->route('admin.clases.index')->with('success', 'Clase eliminada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error al eliminar clase: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al eliminar la clase: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtener el siguiente número de clase disponible para una asignatura
     */
    public function getSiguienteNumero($asignaturaId)
    {
        try {
            $maxNumero = Clase::where('id_asignatura', $asignaturaId)->max('num_clase');
            $siguiente = $maxNumero ? $maxNumero + 1 : 1;
            
            return response()->json([
                'success' => true,
                'siguiente_numero' => $siguiente
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el número'
            ], 500);
        }
    }
    
    public function getVideosApi(Request $request)
    {
        try {
            $query = Video::query();
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('materia', 'like', '%' . $search . '%')
                      ->orWhere('tema', 'like', '%' . $search . '%')
                      ->orWhere('titulo', 'like', '%' . $search . '%');
                });
            }
            
            if ($request->filled('materia')) {
                $query->where('materia', 'like', '%' . $request->materia . '%');
            }
            
            $videos = $query->orderBy('materia')->orderBy('tema')->get(['id', 'materia', 'tema', 'titulo', 'link', 'duracion']);
            
            return response()->json([
                'success' => true,
                'data' => $videos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => []
            ]);
        }
    }
    
    public function getClasesByAsignaturaApi($asignaturaId)
    {
        try {
            $clases = Clase::where('id_asignatura', $asignaturaId)
                ->orderBy('num_clase', 'asc')
                ->get(['id', 'num_clase', 'nombre_clase']);
            
            return response()->json([
                'success' => true,
                'data' => $clases
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => []
            ]);
        }
    }
}