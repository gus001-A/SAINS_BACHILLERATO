<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InteraccionCallCenter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InteraccionCallCenterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $estado = $request->get('estado');
            $busqueda = $request->get('busqueda');

            // Consulta base de interacciones (con filtros) para AGRUPAR por estudiante.
            $base = InteraccionCallCenter::with(['administrador.administrador', 'estudiante.estudiante'])
                ->when($estado, fn ($q) => $q->where('estado_seguimiento', $estado))
                ->when($busqueda, function ($query) use ($busqueda) {
                    $query->where(function ($q) use ($busqueda) {
                        $q->whereHas('estudiante', function ($subq) use ($busqueda) {
                            $subq->where('correo', 'like', "%{$busqueda}%")
                                ->orWhereHas('estudiante', function ($estQuery) use ($busqueda) {
                                    $estQuery->where('nombre', 'like', "%{$busqueda}%")
                                        ->orWhere('paterno', 'like', "%{$busqueda}%")
                                        ->orWhere('materno', 'like', "%{$busqueda}%");
                                });
                        })
                        ->orWhere('motivo_contacto', 'like', "%{$busqueda}%")
                        ->orWhere('resultado', 'like', "%{$busqueda}%")
                        ->orWhere('nota', 'like', "%{$busqueda}%");
                    });
                });

            // IDs de estudiante que tienen interacciones (ya filtradas), paginados.
            $studentIdsPage = (clone $base)->without(['administrador', 'estudiante'])
                ->select('id_estudiante')
                ->selectRaw('MAX(fecha_contacto) as ultima_fecha')
                ->groupBy('id_estudiante')
                ->orderByDesc('ultima_fecha')
                ->paginate(12);

            $ids = collect($studentIdsPage->items())->pluck('id_estudiante');

            // Todas las interacciones (filtradas) de esos estudiantes.
            $interacciones = (clone $base)
                ->whereIn('id_estudiante', $ids)
                ->orderBy('fecha_contacto', 'desc')
                ->orderBy('hora_contacto', 'desc')
                ->get();

            $nombreEst = function ($i) {
                $e = optional($i->estudiante)->estudiante;
                return $e
                    ? trim("{$e->nombre} {$e->paterno} {$e->materno}")
                    : (optional($i->estudiante)->correo ?? 'Estudiante #' . $i->id_estudiante);
            };
            $nombreAdmin = function ($i) {
                $a = optional($i->administrador)->administrador;
                return $a
                    ? trim("{$a->nombre} {$a->apellido_paterno} {$a->apellido_materno}")
                    : (optional($i->administrador)->correo ?? 'N/A');
            };

            $shape = fn ($i) => [
                'id' => $i->id,
                'id_estudiante' => $i->id_estudiante,
                'admin' => $nombreAdmin($i),
                'fecha_contacto' => $i->fecha_contacto ? \Carbon\Carbon::parse($i->fecha_contacto)->format('Y-m-d') : null,
                'hora_contacto' => optional($i->hora_contacto)->format('H:i'),
                'tipo_contacto' => $i->tipo_contacto,
                'estado_seguimiento' => $i->estado_seguimiento,
                'motivo_contacto' => $i->motivo_contacto,
                'nota' => $i->nota,
                'resultado' => $i->resultado,
                'proximo_contacto' => $i->proximo_contacto ? \Carbon\Carbon::parse($i->proximo_contacto)->format('Y-m-d H:i') : null,
            ];

            // Agrupar por estudiante, respetando el orden de la página.
            $porEstudiante = $interacciones->groupBy('id_estudiante');
            $grupos = $ids->map(function ($sid) use ($porEstudiante, $shape, $nombreEst) {
                $items = $porEstudiante->get($sid, collect());
                $primera = $items->first();
                return [
                    'id_estudiante' => $sid,
                    'estudiante' => $primera ? $nombreEst($primera) : 'Estudiante #' . $sid,
                    'correo' => $primera ? optional($primera->estudiante)->correo : null,
                    'total' => $items->count(),
                    'pendientes' => $items->where('estado_seguimiento', 'pendiente')->count(),
                    'abiertas' => $items->where('estado_seguimiento', '!=', 'finalizado')->count(),
                    'estado_ultima' => optional($items->first())->estado_seguimiento,
                    'ultima_fecha' => optional($items->first()->fecha_contacto ?? null)
                        ? \Carbon\Carbon::parse($items->first()->fecha_contacto)->format('Y-m-d')
                        : null,
                    'interacciones' => $items->map($shape)->values(),
                ];
            })->values();

            $estudiantes = User::whereHas('estudiante')->with('estudiante')->get()->map(fn ($u) => [
                'id' => $u->id,
                'label' => optional($u->getRelation('estudiante'))
                    ? trim("{$u->getRelation('estudiante')->nombre} {$u->getRelation('estudiante')->paterno} {$u->getRelation('estudiante')->materno}") . " · {$u->correo}"
                    : $u->correo,
            ])->values();

            return \Inertia\Inertia::render('Admin/CallCenter/Index', [
                'grupos' => $grupos,
                'pagination' => [
                    'current_page' => $studentIdsPage->currentPage(),
                    'per_page' => $studentIdsPage->perPage(),
                    'total' => $studentIdsPage->total(),
                ],
                'estudiantes' => $estudiantes,
                'stats' => [
                    'total' => InteraccionCallCenter::count(),
                    'pendientes' => InteraccionCallCenter::where('estado_seguimiento', 'pendiente')->count(),
                    'enProceso' => InteraccionCallCenter::where('estado_seguimiento', 'en_proceso')->count(),
                    'finalizados' => InteraccionCallCenter::where('estado_seguimiento', 'finalizado')->count(),
                    'estudiantesContactados' => InteraccionCallCenter::distinct('id_estudiante')->count('id_estudiante'),
                ],
                'filters' => [
                    'busqueda' => $busqueda,
                    'estado' => $estado,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error en index de interacciones: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')
                ->with('error', 'Error al cargar las interacciones: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            // Obtener estudiantes con plan inactivo o sin plan
            $estudiantes = User::whereHas('estudiante', function($query) {
                $query->where('plan_activo', '!=', true)
                    ->orWhereNull('plan_activo');
            })->with('estudiante')->get();
            
            // Procesar nombres completos de estudiantes para el select
            foreach ($estudiantes as $estudiante) {
                if ($estudiante->estudiante) {
                    $estudiante->nombre_completo = trim(
                        ($estudiante->estudiante->nombre ?? '') . ' ' . 
                        ($estudiante->estudiante->paterno ?? '') . ' ' . 
                        ($estudiante->estudiante->materno ?? '')
                    );
                } else {
                    $estudiante->nombre_completo = $estudiante->name ?? 'N/A';
                }
            }
            
            $estadosSeguimiento = [
                'pendiente' => 'Pendiente',
                'en_proceso' => 'En Proceso',
                'finalizado' => 'Finalizado'
            ];
            
            $tiposContacto = [
                'llamada' => 'Llamada',
                'email' => 'Email',
                'whatsapp' => 'WhatsApp'
            ];
            
            return redirect()->route('admin.callcenter.index');

        } catch (\Exception $e) {
            Log::error('Error en create de interacciones: ' . $e->getMessage());
            return redirect()->route('admin.callcenter.index');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_estudiante' => 'required|exists:usuario,id',
            'fecha_contacto' => 'required|date|before_or_equal:today',
            'hora_contacto' => 'nullable',
            'tipo_contacto' => 'required|in:llamada,email,whatsapp',
            'estado_seguimiento' => 'required|in:pendiente,en_proceso,finalizado',
            'motivo_contacto' => 'required|string',
            'nota' => 'nullable|string',
            'resultado' => 'nullable|string',
            'proximo_contacto' => 'nullable|date',
        ], [
            'fecha_contacto.before_or_equal' => 'La fecha de contacto no puede ser posterior a hoy.',
        ]);

        try {
            DB::beginTransaction();

            $interaccion = InteraccionCallCenter::create([
                'id_usuario_contacta' => Auth::id(),
                'id_estudiante' => $request->id_estudiante,
                'fecha_contacto' => $request->fecha_contacto,
                'hora_contacto' => $request->hora_contacto ?: now()->format('H:i'),
                'tipo_contacto' => $request->tipo_contacto,
                'estado_seguimiento' => $request->estado_seguimiento,
                'motivo_contacto' => $request->motivo_contacto,
                'nota' => $request->nota ?: '',
                'resultado' => $request->resultado ?: 'no_contesto',
                'proximo_contacto' => $request->proximo_contacto,
            ]);
            
            DB::commit();
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Interacción registrada exitosamente',
                    'data' => $interaccion
                ]);
            }
            
            return redirect()->route('admin.callcenter.index')
                ->with('success', 'Interacción registrada exitosamente.');
                
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            $errorMessage = $e->getMessage();
            
            // Mensaje específico para error de campo muy largo
            if (str_contains($errorMessage, 'Data too long for column')) {
                $errorMessage = 'El texto ingresado es demasiado largo. Por favor, reduce la longitud del motivo, nota o resultado.';
            }
            
            Log::error('Error en store de interacción: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error al registrar la interacción: ' . $errorMessage)
                ->withInput();
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en store de interacción: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error al registrar la interacción: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $interaccion = InteraccionCallCenter::with([
                'administrador.administrador', 
                'estudiante.estudiante'
            ])->findOrFail($id);
            
            // Procesar nombres completos
            if ($interaccion->administrador && $interaccion->administrador->administrador) {
                $interaccion->admin_nombre_completo = trim(
                    ($interaccion->administrador->administrador->nombre ?? '') . ' ' . 
                    ($interaccion->administrador->administrador->apellido_paterno ?? '') . ' ' . 
                    ($interaccion->administrador->administrador->apellido_materno ?? '')
                );
            } else {
                $interaccion->admin_nombre_completo = $interaccion->administrador->name ?? 'N/A';
            }
            
            if ($interaccion->estudiante && $interaccion->estudiante->estudiante) {
                $interaccion->est_nombre_completo = trim(
                    ($interaccion->estudiante->estudiante->nombre ?? '') . ' ' . 
                    ($interaccion->estudiante->estudiante->paterno ?? '') . ' ' . 
                    ($interaccion->estudiante->estudiante->materno ?? '')
                );
            } else {
                $interaccion->est_nombre_completo = $interaccion->estudiante->name ?? 'N/A';
            }
            
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $interaccion
                ]);
            }
            
            return redirect()->route('admin.callcenter.index');
            
        } catch (\Exception $e) {
            Log::error('Error en show de interacción: ' . $e->getMessage());
            
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cargar la interacción: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('admin.callcenter.index')
                ->with('error', 'Error al cargar la interacción: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $interaccion = InteraccionCallCenter::findOrFail($id);
            $estudiantes = User::whereHas('estudiante')->with('estudiante')->get();
            
            // Procesar nombres completos de estudiantes
            foreach ($estudiantes as $estudiante) {
                if ($estudiante->estudiante) {
                    $estudiante->nombre_completo = trim(
                        ($estudiante->estudiante->nombre ?? '') . ' ' . 
                        ($estudiante->estudiante->paterno ?? '') . ' ' . 
                        ($estudiante->estudiante->materno ?? '')
                    );
                } else {
                    $estudiante->nombre_completo = $estudiante->name ?? 'N/A';
                }
            }
            
            $estadosSeguimiento = [
                'pendiente' => 'Pendiente',
                'en_proceso' => 'En Proceso',
                'finalizado' => 'Finalizado'
            ];
            
            $tiposContacto = [
                'llamada' => 'Llamada',
                'email' => 'Email',
                'whatsapp' => 'WhatsApp'
            ];
            
            return redirect()->route('admin.callcenter.index');

        } catch (\Exception $e) {
            Log::error('Error en edit de interacción: ' . $e->getMessage());
            return redirect()->route('admin.callcenter.index');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_estudiante' => 'required|exists:usuario,id',
            'fecha_contacto' => 'required|date|before_or_equal:today',
            'hora_contacto' => 'nullable',
            'tipo_contacto' => 'required|in:llamada,email,whatsapp',
            'estado_seguimiento' => 'required|in:pendiente,en_proceso,finalizado',
            'motivo_contacto' => 'required|string',
            'nota' => 'nullable|string',
            'resultado' => 'nullable|string',
            'proximo_contacto' => 'nullable|date',
        ], [
            'fecha_contacto.before_or_equal' => 'La fecha de contacto no puede ser posterior a hoy.',
        ]);

        try {
            DB::beginTransaction();

            $interaccion = InteraccionCallCenter::findOrFail($id);
            $interaccion->update([
                'id_estudiante' => $request->id_estudiante,
                'fecha_contacto' => $request->fecha_contacto,
                'hora_contacto' => $request->hora_contacto ?: $interaccion->hora_contacto,
                'tipo_contacto' => $request->tipo_contacto,
                'estado_seguimiento' => $request->estado_seguimiento,
                'motivo_contacto' => $request->motivo_contacto,
                'nota' => $request->nota ?: '',
                'resultado' => $request->resultado ?: 'no_contesto',
                'proximo_contacto' => $request->proximo_contacto,
            ]);
            
            DB::commit();
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Interacción actualizada exitosamente',
                    'data' => $interaccion
                ]);
            }
            
            return redirect()->route('admin.callcenter.index')
                ->with('success', 'Interacción actualizada exitosamente.');
                
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            $errorMessage = $e->getMessage();
            
            if (str_contains($errorMessage, 'Data too long for column')) {
                $errorMessage = 'El texto ingresado es demasiado largo. Por favor, reduce la longitud del motivo, nota o resultado.';
            }
            
            Log::error('Error en update de interacción: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error al actualizar la interacción: ' . $errorMessage)
                ->withInput();
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en update de interacción: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error al actualizar la interacción: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $interaccion = InteraccionCallCenter::findOrFail($id);
            $interaccion->delete();
            
            DB::commit();
            
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Interacción eliminada exitosamente'
                ]);
            }
            
            return redirect()->route('admin.callcenter.index')
                ->with('success', 'Interacción eliminada exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en destroy de interacción: ' . $e->getMessage());
            
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('admin.callcenter.index')
                ->with('error', 'Error al eliminar la interacción: ' . $e->getMessage());
        }
    }
    
    /**
     * Cambiar el estado de seguimiento de una interacción
     */
    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,en_proceso,finalizado'
        ]);
        
        try {
            $interaccion = InteraccionCallCenter::findOrFail($id);
            $interaccion->estado_seguimiento = $request->estado;
            $interaccion->save();
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Estado actualizado exitosamente',
                    'estado' => $interaccion->estado_seguimiento
                ]);
            }
            
            return redirect()->back()->with('success', 'Estado actualizado exitosamente.');
            
        } catch (\Exception $e) {
            Log::error('Error en cambiarEstado: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar estado: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error al actualizar el estado.');
        }
    }
    
    /**
     * Exportar interacciones a Excel/CSV
     */
    public function exportar(Request $request)
    {
        try {
            $query = InteraccionCallCenter::with(['administrador.administrador', 'estudiante.estudiante']);
            
            // Aplicar filtros
            if ($request->estado) {
                $query->where('estado_seguimiento', $request->estado);
            }
            
            if ($request->fecha_inicio && $request->fecha_fin) {
                $query->whereBetween('fecha_contacto', [$request->fecha_inicio, $request->fecha_fin]);
            }
            
            $interacciones = $query->orderBy('fecha_contacto', 'desc')->get();
            
            // Preparar datos para exportar
            $data = [];
            foreach ($interacciones as $interaccion) {
                // Nombre del administrador
                if ($interaccion->administrador && $interaccion->administrador->administrador) {
                    $nombreAdmin = trim(
                        ($interaccion->administrador->administrador->nombre ?? '') . ' ' . 
                        ($interaccion->administrador->administrador->apellido_paterno ?? '')
                    );
                } else {
                    $nombreAdmin = $interaccion->administrador->name ?? 'N/A';
                }
                
                // Nombre del estudiante
                if ($interaccion->estudiante && $interaccion->estudiante->estudiante) {
                    $nombreEstudiante = trim(
                        ($interaccion->estudiante->estudiante->nombre ?? '') . ' ' . 
                        ($interaccion->estudiante->estudiante->paterno ?? '')
                    );
                } else {
                    $nombreEstudiante = $interaccion->estudiante->name ?? 'N/A';
                }
                
                $data[] = [
                    'ID' => $interaccion->id,
                    'Administrador' => $nombreAdmin,
                    'Estudiante' => $nombreEstudiante,
                    'Fecha' => $interaccion->fecha_contacto,
                    'Hora' => $interaccion->hora_contacto,
                    'Tipo' => ucfirst($interaccion->tipo_contacto ?? 'N/A'),
                    'Estado' => ucfirst(str_replace('_', ' ', $interaccion->estado_seguimiento ?? 'N/A')),
                    'Motivo' => $interaccion->motivo_contacto ?? 'N/A',
                    'Nota' => $interaccion->nota ?? 'N/A',
                    'Resultado' => $interaccion->resultado ?? 'N/A',
                    'Próximo Contacto' => $interaccion->proximo_contacto ?? 'N/A',
                    'Fecha Registro' => $interaccion->created_at ?? 'N/A',
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => count($data),
                'message' => 'Exportación generada exitosamente'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en exportar: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar: ' . $e->getMessage()
            ], 500);
        }
    }
}