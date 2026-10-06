<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamenGenerado;
use App\Models\Pregunta;
use App\Models\AreaPregunta;
use App\Models\ApoyoPregunta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExamenGeneradoController extends Controller
{
    public function index(Request $request)
    {
        $query = ExamenGenerado::query();
        
        // 🔍 Filtro por búsqueda (ID o tipo de examen)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('tipo_examen', 'like', "%{$search}%");
            });
        }
        
        // 🔍 Filtro por tipo de examen
        if ($request->filled('tipo')) {
            $query->where('tipo_examen', $request->tipo);
        }
        
        // 🔍 Filtro por rango de preguntas
        if ($request->filled('rango')) {
            $rango = $request->rango;
            switch ($rango) {
                case '0-20':
                    $query->whereBetween('numero_preguntas', [0, 20]);
                    break;
                case '21-50':
                    $query->whereBetween('numero_preguntas', [21, 50]);
                    break;
                case '51-100':
                    $query->whereBetween('numero_preguntas', [51, 100]);
                    break;
                case '100+':
                    $query->where('numero_preguntas', '>', 100);
                    break;
            }
        }
        
        // 🆕 ORDENAMIENTO POR COLUMNAS (dinámico)
        $ordenCampo = $request->get('orden_campo', 'id');
        $ordenDireccion = $request->get('orden_direccion', 'desc');
        
        // Mapeo de campos permitidos para ordenamiento
        $ordenPermitido = [
            'id' => 'id',
            'tipo_examen' => 'tipo_examen',
            'numero_preguntas' => 'numero_preguntas',
            'tiempo' => 'tiempo',
            'created_at' => 'created_at'
        ];
        
        // Aplicar ordenamiento por columna si existe en el mapa
        if (array_key_exists($ordenCampo, $ordenPermitido)) {
            $query->orderBy($ordenPermitido[$ordenCampo], $ordenDireccion);
        } else {
            // Fallback: ordenar por ID descendente
            $query->orderBy('id', 'desc');
        }
        
        $examenes = $query->paginate(15)->appends($request->except('page'));
        
        // 📊 ESTADÍSTICAS PARA LAS TARJETAS
        $totalExamenes = ExamenGenerado::count();
        
        // Contadores por tipo de examen
        $examenesMateria = ExamenGenerado::where('tipo_examen', 'Materia')->count();
        $examenesSimulacion = ExamenGenerado::where('tipo_examen', 'Simulación')->count();
        $examenesGeneral = ExamenGenerado::where('tipo_examen', 'Curso')->count();
        
        // Estadísticas adicionales
        $totalPreguntasAsignadas = ApoyoPregunta::count();
        $promedioPreguntas = round(ExamenGenerado::avg('numero_preguntas') ?? 0, 1);
        $promedioTiempo = round(ExamenGenerado::avg('tiempo') ?? 0, 1);
        
        // Para el modal de generación automática
        $tipos_examen = ['Materia', 'Curso', 'Simulación'];
        $areas = AreaPregunta::orderBy('nombre')->get();
        
        $examenes->getCollection()->transform(fn ($e) => [
            'id' => $e->id,
            'tipo_examen' => $e->tipo_examen,
            'numero_preguntas' => $e->numero_preguntas,
            'tiempo' => $e->tiempo,
            'created_at' => optional($e->created_at)->format('Y-m-d H:i'),
        ]);

        // Tipos reales presentes en la BD (para que el filtro siempre coincida)
        $tiposExamen = ExamenGenerado::select('tipo_examen')
            ->whereNotNull('tipo_examen')->where('tipo_examen', '!=', '')
            ->distinct()->orderBy('tipo_examen')->pluck('tipo_examen');

        return \Inertia\Inertia::render('Admin/Examenes/Index', [
            'examenes' => $examenes,
            'areas' => AreaPregunta::orderBy('nombre')->get(['id', 'nombre']),
            'tiposExamen' => $tiposExamen,
            'stats' => [
                'total' => $totalExamenes,
                'materia' => $examenesMateria,
                'simulacion' => $examenesSimulacion,
                'curso' => $examenesGeneral,
                'preguntasAsignadas' => $totalPreguntasAsignadas,
                'promedioPreguntas' => $promedioPreguntas,
            ],
            'filters' => [
                'search' => $request->search,
                'tipo' => $request->tipo,
                'rango' => $request->rango,
            ],
        ]);
    }

    private function preguntasParaSelector()
    {
        return Pregunta::with('area')->orderBy('id_area')->get()->map(fn ($p) => [
            'id' => $p->id,
            'pregunta' => $p->pregunta,
            'area' => $p->area?->nombre ?? 'Sin área',
            'id_area' => $p->id_area,
        ]);
    }

    public function create()
    {
        return \Inertia\Inertia::render('Admin/Examenes/Create', [
            'preguntas' => $this->preguntasParaSelector(),
            'areas' => AreaPregunta::orderBy('nombre')->get(['id', 'nombre']),
            'tiposExamen' => ['Materia', 'Curso', 'Simulación'],
        ]);
    }

    // Guardar nuevo examen
    public function store(Request $request)
    {
        $request->validate([
            'numero_preguntas' => 'required|integer|min:1|max:200',
            'tiempo' => 'required|integer|min:1|max:180',
            'tipo_examen' => 'required|string|max:50',
            'completar_aleatorio' => 'boolean',
            'preguntas_seleccionadas' => 'array',
            'preguntas_seleccionadas.*' => 'exists:preguntas,id',
        ]);

        try {
            DB::beginTransaction();

            $preguntas = $this->armarPreguntas($request);
            if ($preguntas instanceof \Illuminate\Http\RedirectResponse) {
                DB::rollBack();
                return $preguntas;
            }

            $examen = ExamenGenerado::create([
                'numero_preguntas' => count($preguntas),
                'tiempo' => $request->tiempo,
                'tipo_examen' => $request->tipo_examen,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            foreach ($preguntas as $pregunta_id) {
                ApoyoPregunta::create(['examen' => $examen->id, 'pregunta' => $pregunta_id]);
            }

            DB::commit();

            return redirect()->route('admin.examenes.index')
                ->with('success', $this->mensajeArmado($examen->id, $request, count($preguntas), 'creado'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear examen: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el examen: ' . $e->getMessage());
        }
    }

    /**
     * Combina las preguntas elegidas manualmente con un relleno aleatorio del banco.
     * Devuelve un array de IDs, o un RedirectResponse con el error de validación.
     */
    private function armarPreguntas(Request $request)
    {
        $objetivo = (int) $request->numero_preguntas;
        $manuales = collect($request->input('preguntas_seleccionadas', []))
            ->map(fn ($id) => (int) $id)->unique()->values();
        $completar = $request->boolean('completar_aleatorio');

        if ($manuales->count() > $objetivo) {
            return redirect()->back()->withInput()->with('error',
                "Seleccionaste {$manuales->count()} preguntas pero el examen es de {$objetivo}. Reduce la selección o sube el total.");
        }

        if (!$completar) {
            if ($manuales->count() !== $objetivo) {
                return redirect()->back()->withInput()->with('error',
                    "Debes seleccionar exactamente {$objetivo} preguntas o activar el relleno aleatorio.");
            }
            return $manuales->all();
        }

        $faltan = $objetivo - $manuales->count();
        if ($faltan > 0) {
            $aleatorias = Pregunta::whereNotIn('id', $manuales->all())
                ->inRandomOrder()->limit($faltan)->pluck('id');

            if ($aleatorias->count() < $faltan) {
                return redirect()->back()->withInput()->with('error',
                    "No hay suficientes preguntas en el banco para el relleno aleatorio (faltan {$faltan}, disponibles {$aleatorias->count()}).");
            }
            $manuales = $manuales->merge($aleatorias);
        }

        return $manuales->values()->all();
    }

    private function mensajeArmado($id, Request $request, int $total, string $verbo): string
    {
        $manuales = count($request->input('preguntas_seleccionadas', []));
        $aleatorias = $total - $manuales;
        $detalle = ($request->boolean('completar_aleatorio') && $aleatorias > 0)
            ? " ({$manuales} elegidas + {$aleatorias} aleatorias)"
            : '';
        return "Examen #{$id} {$verbo} correctamente con {$total} preguntas{$detalle}";
    }

    // Mostrar un examen específico - VERSIÓN CORREGIDA
    public function show($id){
        try {
            // Primero, obtener el examen básico
            $examen = ExamenGenerado::findOrFail($id);
            
            // Obtener las preguntas del examen (con sus opciones) vía la relación del modelo
            $preguntasDelExamen = $examen->preguntas()->with(['area', 'opciones'])->get();

            // Asignar las preguntas al examen como una colección
            $examen->preguntas_lista = $preguntasDelExamen;

            // Calcular preguntas por área
            $preguntasPorArea = [];
            foreach($preguntasDelExamen as $pregunta) {
                $areaNombre = $pregunta->area?->nombre ?? 'Sin área';
                $preguntasPorArea[$areaNombre] = ($preguntasPorArea[$areaNombre] ?? 0) + 1;
            }

            return \Inertia\Inertia::render('Admin/Examenes/Show', [
                'examen' => [
                    'id' => $examen->id,
                    'tipo_examen' => $examen->tipo_examen,
                    'numero_preguntas' => $examen->numero_preguntas,
                    'tiempo' => $examen->tiempo,
                ],
                'preguntas' => $preguntasDelExamen->map(fn ($p) => [
                    'id' => $p->id,
                    'texto' => $p->pregunta,
                    'respuesta_correcta' => $p->opciones->firstWhere('es_correcta', true)?->texto,
                    'opciones' => $p->opciones->map(fn ($o) => ['texto' => $o->texto, 'es_correcta' => $o->es_correcta])->values(),
                    'area' => $p->area?->nombre ?? 'Sin área',
                ])->values(),
                'porArea' => collect($preguntasPorArea)->map(fn ($total, $area) => ['area' => $area, 'total' => $total])->values(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en show de examen: ' . $e->getMessage());
            return redirect()->route('admin.examenes.index')
                ->with('error', 'Error al cargar el examen: ' . $e->getMessage());
        }
    }

    // Mostrar formulario de edición de examen
    public function edit($id)
    {
        \Log::info('=== EDIT EXAMEN ===');
        \Log::info('ID recibido: ' . $id);
        \Log::info('Tipo de ID: ' . gettype($id));
        
        try {
            // Buscar el examen manualmente
            $examen = ExamenGenerado::find($id);
            
            if (!$examen) {
                \Log::error('Examen no encontrado con ID: ' . $id);
                return redirect()->route('admin.examenes.index')
                    ->with('error', "El examen #{$id} no existe");
            }
            
            \Log::info('Examen encontrado: ' . $examen->id . ' - ' . $examen->tipo_examen);
            
            // Obtener las preguntas seleccionadas del examen
            $preguntasSeleccionadas = DB::table('apoyo_preguntas')
                ->where('examen', $examen->id)
                ->pluck('pregunta')
                ->toArray();
            
            \Log::info('Preguntas seleccionadas: ' . json_encode($preguntasSeleccionadas));
            
            // Obtener todas las áreas y preguntas
            $areas = AreaPregunta::with('preguntas')->get();
            $preguntas = Pregunta::with('area')->get();
            
            // Tipos de examen disponibles
            $tipos_examen = [
                'Materia' => 'Materia',
                'Curso' => 'Curso',
                'Simulación' => 'Simulación'
            ];
            
            return \Inertia\Inertia::render('Admin/Examenes/Edit', [
                'examen' => [
                    'id' => $examen->id,
                    'tipo_examen' => $examen->tipo_examen,
                    'numero_preguntas' => $examen->numero_preguntas,
                    'tiempo' => $examen->tiempo,
                    'preguntas_seleccionadas' => array_map('intval', $preguntasSeleccionadas),
                ],
                'preguntas' => $this->preguntasParaSelector(),
                'areas' => AreaPregunta::orderBy('nombre')->get(['id', 'nombre']),
                'tiposExamen' => ['Materia', 'Curso', 'Simulación'],
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en edit de examen: ' . $e->getMessage());
            return redirect()->route('admin.examenes.index')
                ->with('error', 'Error al cargar el examen para editar: ' . $e->getMessage());
        }
    }

// Actualizar examen - CORREGIDO
public function update(Request $request, $id)
{
    \Log::info('=== UPDATE EXAMEN ===');
    \Log::info('ID: ' . $id);
    \Log::info('Request data: ' . json_encode($request->all()));
    
    $request->validate([
        'numero_preguntas' => 'required|integer|min:1|max:200',
        'tiempo' => 'required|integer|min:1|max:180',
        'tipo_examen' => 'required|string|max:50',
        'completar_aleatorio' => 'boolean',
        'preguntas_seleccionadas' => 'array',
        'preguntas_seleccionadas.*' => 'exists:preguntas,id',
    ]);

    try {
        DB::beginTransaction();

        $examen = ExamenGenerado::findOrFail($id);

        $preguntas = $this->armarPreguntas($request);
        if ($preguntas instanceof \Illuminate\Http\RedirectResponse) {
            DB::rollBack();
            return $preguntas;
        }

        $examen->update([
            'numero_preguntas' => count($preguntas),
            'tiempo' => $request->tiempo,
            'tipo_examen' => $request->tipo_examen,
            'updated_at' => Carbon::now(),
        ]);

        ApoyoPregunta::where('examen', $examen->id)->delete();
        foreach ($preguntas as $pregunta_id) {
            ApoyoPregunta::create(['examen' => $examen->id, 'pregunta' => $pregunta_id]);
        }

        DB::commit();

        return redirect()->route('admin.examenes.index')
            ->with('success', $this->mensajeArmado($examen->id, $request, count($preguntas), 'actualizado'));

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        DB::rollBack();
        \Log::error('Examen no encontrado para actualizar: ' . $id);
        return redirect()->route('admin.examenes.index')
            ->with('error', 'El examen que intentas actualizar no existe');
            
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error al actualizar examen: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return redirect()->back()
            ->withInput()
            ->with('error', 'Error al actualizar el examen: ' . $e->getMessage());
    }
}

    // Eliminar examen
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $examen = ExamenGenerado::findOrFail($id);
            
            // Eliminar las preguntas asociadas
            ApoyoPregunta::where('examen', $examen->id)->delete();
            
            $examenId = $examen->id;
            $examen->delete();
            
            DB::commit();
            
            return redirect()->route('admin.examenes.index')
                ->with('success', "Examen #{$examenId} eliminado correctamente");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar examen: ' . $e->getMessage());
            return redirect()->route('admin.examenes.index')
                ->with('error', 'Error al eliminar el examen: ' . $e->getMessage());
        }
    }

    // ==================== MÉTODOS ADICIONALES ====================
    
    // Generar examen automático con preguntas aleatorias por área
    public function generarAutomatico(Request $request)
    {
        $request->validate([
            'numero_preguntas' => 'required|integer|min:1|max:200',
            'tiempo' => 'required|integer|min:1|max:180',
            'tipo_examen' => 'required|string|max:50',
            'areas' => 'nullable|array',
            'areas.*' => 'exists:area_preguntas,id',
        ]);

        try {
            DB::beginTransaction();
            
            // Construir query de preguntas disponibles
            $query = Pregunta::query();
            
            if ($request->has('areas') && !empty($request->areas)) {
                $query->whereIn('id_area', $request->areas);
            }
            
            // Obtener preguntas aleatorias
            $preguntasDisponibles = $query->count();
            
            if ($preguntasDisponibles < $request->numero_preguntas) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "No hay suficientes preguntas disponibles. Disponibles: {$preguntasDisponibles}, Requeridas: {$request->numero_preguntas}");
            }
            
            $preguntasSeleccionadas = $query->inRandomOrder()
                ->limit($request->numero_preguntas)
                ->pluck('id')
                ->toArray();
            
            // Crear el examen
            $examen = ExamenGenerado::create([
                'numero_preguntas' => $request->numero_preguntas,
                'tiempo' => $request->tiempo,
                'tipo_examen' => $request->tipo_examen,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            // Asignar las preguntas
            foreach ($preguntasSeleccionadas as $pregunta_id) {
                ApoyoPregunta::create([
                    'examen' => $examen->id,
                    'pregunta' => $pregunta_id,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('admin.examenes.index')
                ->with('success', "Examen #{$examen->id} generado automáticamente con {$request->numero_preguntas} preguntas");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al generar examen automático: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al generar el examen: ' . $e->getMessage());
        }
    }
    
    // Duplicar examen existente
    public function duplicar($id){
        try {
            DB::beginTransaction();
            
            $examenOriginal = ExamenGenerado::with('apoyos')->findOrFail($id);
            
            // Crear copia del examen
            $nuevoExamen = ExamenGenerado::create([
                'numero_preguntas' => $examenOriginal->numero_preguntas,
                'tiempo' => $examenOriginal->tiempo,
                'tipo_examen' => $examenOriginal->tipo_examen . ' (Copia)',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            // Copiar las preguntas
            foreach ($examenOriginal->apoyos as $apoyo) {
                ApoyoPregunta::create([
                    'examen' => $nuevoExamen->id,
                    'pregunta' => $apoyo->pregunta,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('admin.examenes.edit', $nuevoExamen->id)
                ->with('success', "Examen duplicado correctamente. Ahora puedes editarlo");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al duplicar examen: ' . $e->getMessage());
            return redirect()->route('admin.examenes.index')
                ->with('error', 'Error al duplicar el examen: ' . $e->getMessage());
        }
    }
    
    // Obtener preguntas por área para selección dinámica (API)
    public function getPreguntasByArea($areaId)
    {
        $preguntas = Pregunta::where('id_area', $areaId)
            ->select('id', 'pregunta')
            ->orderBy('pregunta')
            ->get();
        
        return response()->json($preguntas);
    }
    
    // Obtener todas las preguntas para selección (API)
    public function getTodasPreguntas(Request $request)
    {
        $query = Pregunta::with('area')
            ->select('id', 'pregunta', 'id_area');
        
        if ($request->has('area_id') && $request->area_id != '') {
            $query->where('id_area', $request->area_id);
        }
        
        $preguntas = $query->orderBy('pregunta')->get();
        
        return response()->json($preguntas);
    }
    
    // Dashboard de exámenes
    public function dashboard()
    {
        $totalExamenes = ExamenGenerado::count();
        $totalPreguntasAsignadas = ApoyoPregunta::count();
        $promedioPreguntas = ExamenGenerado::avg('numero_preguntas') ?? 0;
        $promedioTiempo = ExamenGenerado::avg('tiempo') ?? 0;
        
        // Exámenes por tipo
        $examenesPorTipo = ExamenGenerado::selectRaw('tipo_examen, COUNT(*) as total')
            ->groupBy('tipo_examen')
            ->get();
        
        // Distribución de preguntas
        $distribucionPreguntas = ExamenGenerado::selectRaw('numero_preguntas, COUNT(*) as total')
            ->groupBy('numero_preguntas')
            ->orderBy('numero_preguntas')
            ->limit(10)
            ->get();
        
        // Últimos exámenes creados
        $ultimosExamenes = ExamenGenerado::withCount('apoyos')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return \Inertia\Inertia::render('Admin/Examenes/Dashboard', [
            'stats' => [
                'total' => $totalExamenes,
                'preguntasAsignadas' => $totalPreguntasAsignadas,
                'promedioPreguntas' => round($promedioPreguntas, 1),
                'promedioTiempo' => round($promedioTiempo, 1),
            ],
            'porTipo' => $examenesPorTipo->map(fn ($r) => ['tipo' => $r->tipo_examen, 'total' => $r->total]),
            'ultimosExamenes' => $ultimosExamenes->map(fn ($e) => [
                'id' => $e->id,
                'tipo_examen' => $e->tipo_examen,
                'numero_preguntas' => $e->numero_preguntas,
                'preguntas_reales' => $e->apoyos_count,
                'tiempo' => $e->tiempo,
                'created_at' => optional($e->created_at)->format('d/m/Y'),
            ]),
        ]);
    }
}