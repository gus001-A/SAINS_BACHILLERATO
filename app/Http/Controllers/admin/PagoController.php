<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Estudiante;
use App\Models\User;
use App\Models\Administrador;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PagoController extends Controller
{
    // ==================== MÉTODOS PRINCIPALES ====================
    
    // Listar todos los pagos con filtros
    public function index(Request $request)
    {
        $search = $request->get('search');
        $estudiante = $request->get('estudiante');
        $estatus = $request->get('estatus');
        $tipo_pago = $request->get('tipo_pago');
        $fecha_desde = $request->get('fecha_desde');
        $fecha_hasta = $request->get('fecha_hasta');
        $monto_min = $request->get('monto_min');
        $monto_max = $request->get('monto_max');
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        
        $pagos = Pago::with(['alumno', 'revisor.administrador'])
            ->when($search, function($query, $search) {
                return $query->where('referencia_pago', 'LIKE', "%{$search}%");
            })
            ->when($estudiante, function($query, $estudiante) {
                return $query->whereHas('alumno', function($sub) use ($estudiante) {
                    $sub->where('nombre', 'LIKE', "%{$estudiante}%")
                        ->orWhere('paterno', 'LIKE', "%{$estudiante}%")
                        ->orWhere('materno', 'LIKE', "%{$estudiante}%")
                        ->orWhereRaw("CONCAT(nombre, ' ', paterno, ' ', COALESCE(materno,'')) LIKE ?", ["%{$estudiante}%"]);
                });
            })
            ->when($estatus, function($query, $estatus) {
                return $query->where('estatus', $estatus);
            })
            ->when($tipo_pago, function($query, $tipo_pago) {
                return $query->where('tipo_pago', $tipo_pago);
            })
            ->when($fecha_desde, function($query, $fecha_desde) {
                return $query->whereDate('fecha_pago', '>=', $fecha_desde);
            })
            ->when($fecha_hasta, function($query, $fecha_hasta) {
                return $query->whereDate('fecha_pago', '<=', $fecha_hasta);
            })
            ->when($monto_min, function($query, $monto_min) {
                return $query->where('monto_pago', '>=', $monto_min);
            })
            ->when($monto_max, function($query, $monto_max) {
                return $query->where('monto_pago', '<=', $monto_max);
            })
            ->orderBy($sort, $order)
            ->paginate(15);
        
        $estados_pago = Pago::select('estatus')->distinct()->orderBy('estatus')->pluck('estatus');
        $tipos_pago = Pago::select('tipo_pago')->distinct()->orderBy('tipo_pago')->pluck('tipo_pago');
        
        $queryStats = Pago::query();
        if ($estatus) $queryStats->where('estatus', $estatus);
        if ($tipo_pago) $queryStats->where('tipo_pago', $tipo_pago);
        if ($fecha_desde) $queryStats->whereDate('fecha_pago', '>=', $fecha_desde);
        if ($fecha_hasta) $queryStats->whereDate('fecha_pago', '<=', $fecha_hasta);
        
        $totalPagos = $queryStats->sum('monto_pago');
        $pagosPendientes = (clone $queryStats)->whereIn('estatus', ['pendiente', 'revisando', 'procesando'])->count();
        $pagosAprobados = (clone $queryStats)->where('estatus', 'aprobado')->count();
        $pagosRechazados = (clone $queryStats)->where('estatus', 'rechazado')->count();
        $totalTransacciones = $queryStats->count();
        
        $pagos->getCollection()->transform(fn ($p) => [
            'id' => $p->id,
            'referencia' => $p->referencia_pago,
            'alumno' => $p->alumno ? trim("{$p->alumno->nombre} {$p->alumno->paterno} {$p->alumno->materno}") : null,
            'monto' => (float) $p->monto_pago,
            'tipo_pago' => $p->tipo_pago,
            'estatus' => $p->estatus,
            'fecha_pago' => optional($p->fecha_pago)->format('d/m/Y'),
            'hora_pago' => optional($p->fecha_pago)->format('H:i') !== '00:00'
                ? optional($p->fecha_pago)->format('H:i')
                : optional($p->created_at)->format('H:i'),
            'tiene_comprobante' => !empty($p->comprobante),
            'comprobante_url' => $p->comprobante ? Storage::url($p->comprobante) : null,
            'comprobante_es_pdf' => $p->comprobante ? str_ends_with(strtolower($p->comprobante), '.pdf') : false,
            'revisor' => $p->revisor?->administrador
                ? trim("{$p->revisor->administrador->nombre} {$p->revisor->administrador->apellido_paterno}")
                : null,
        ]);

        return \Inertia\Inertia::render('Admin/Pagos/Index', [
            'pagos' => $pagos,
            'estadosPago' => $estados_pago,
            'tiposPago' => $tipos_pago,
            'stats' => [
                'ingresos' => (float) $totalPagos,
                'pendientes' => $pagosPendientes,
                'aprobados' => $pagosAprobados,
                'rechazados' => $pagosRechazados,
                'transacciones' => $totalTransacciones,
            ],
            'filters' => [
                'search' => $search,
                'estudiante' => $estudiante,
                'estatus' => $estatus,
                'tipo_pago' => $tipo_pago,
            ],
        ]);
    }

    private function estudiantesParaSelect()
    {
        return Estudiante::with('usuario')
            ->orderBy('paterno')->orderBy('materno')->orderBy('nombre')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'label' => trim("{$e->nombre} {$e->paterno} {$e->materno}") . ' · ' . (optional($e->getRelation('usuario'))->correo ?? ''),
            ]);
    }

    public function create()
    {
        return \Inertia\Inertia::render('Admin/Pagos/Create', [
            'estudiantes' => $this->estudiantesParaSelect(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_pago' => 'required|string|max:50',
            'alumno_pago' => 'required|exists:estudiante,id',
            'fecha_pago' => 'required|date|before_or_equal:today',
            'referencia_pago' => 'nullable|string|max:100|unique:pagos,referencia_pago',
            'comprobante' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'nota_usuario' => 'nullable|string',
            'estatus' => 'required|in:pendiente,aprobado,rechazado,cancelado',
        ], [
            'fecha_pago.before_or_equal' => 'La fecha de pago no puede ser posterior a hoy.',
            'referencia_pago.unique' => 'Ya existe un pago con esa referencia. Revisa si el estudiante ya tiene una ficha pendiente.',
        ]);

        // El monto del curso premium es fijo; nunca se toma del formulario.
        $montoCurso = \App\Http\Controllers\Alumno\AlumnoController::PRECIO_CURSO;

        // La referencia es obligatoria y única en BD: si viene vacía, se genera.
        $referencia = trim((string) $request->referencia_pago) ?: Pago::referenciaUnica(
            str_contains(strtolower((string) $request->tipo_pago), 'oxxo') ? 'OXX' : 'TRA'
        );

        try {
            DB::beginTransaction();
            $estudiante = Estudiante::findOrFail($request->alumno_pago);

            $imagenPath = null;
            if ($request->hasFile('comprobante')) {
                $file = $request->file('comprobante');
                $nombreLimpio = $this->sanitizarNombre($estudiante->nombre . '_' . $estudiante->paterno);
                $fechaActual = date('Ymd_His');
                $extension = $file->getClientOriginalExtension();
                $nombreArchivo = $nombreLimpio . '_' . $fechaActual . '.' . $extension;
                $imagenPath = $file->storeAs('comprobantes', $nombreArchivo, 'public');
                if (!$imagenPath) throw new \Exception('No se pudo guardar el archivo');
            }
            
            $usuarioRevision = null;
            $fechaRevision = null;
            if ($request->estatus == 'aprobado' || $request->estatus == 'rechazado') {
                $usuarioRevision = auth()->id();
                $fechaRevision = now();
            }
            
            $pago = Pago::create([
                'tipo_pago' => $request->tipo_pago,
                'alumno_pago' => $request->alumno_pago,
                'fecha_pago' => $request->fecha_pago,
                'monto_pago' => $montoCurso,
                'estatus' => $request->estatus,
                'referencia_pago' => $referencia,
                'comprobante' => $imagenPath,
                'usuario_revision' => $usuarioRevision,
                'fecha_aprueba' => $fechaRevision,
                'nota_usuario' => $request->nota_usuario,
            ]);
            
            if ($request->estatus == 'aprobado') {
                $this->activarPlanEstudiante($estudiante, $pago);
                $this->enviarCorreoAprobacion($estudiante, $pago);
            }
            
            DB::commit();
            
            $mensaje = 'Pago registrado correctamente. ID: #' . $pago->id;
            if ($request->estatus == 'aprobado') {
                $mensaje .= ' ✅ Plan activado para el estudiante. Se ha enviado correo de notificación.';
            } elseif ($request->estatus == 'rechazado') {
                $mensaje .= ' ❌ Pago rechazado.';
            }
            
            return redirect()->route('admin.pagos.index')->with('success', $mensaje);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar pago: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error al registrar el pago: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $pago = Pago::with(['alumno.usuario', 'revisor.administrador'])->findOrFail($id);

        return \Inertia\Inertia::render('Admin/Pagos/Show', [
            'pago' => [
                'id' => $pago->id,
                'referencia' => $pago->referencia_pago,
                'monto' => (float) $pago->monto_pago,
                'tipo_pago' => $pago->tipo_pago,
                'estatus' => $pago->estatus,
                'fecha_pago' => optional($pago->fecha_pago)->format('Y-m-d H:i'),
                'fecha_aprueba' => optional($pago->fecha_aprueba)->format('Y-m-d H:i'),
                'nota_usuario' => $pago->nota_usuario,
                'comprobante_url' => $pago->comprobante ? Storage::url($pago->comprobante) : null,
                'alumno' => $pago->alumno ? [
                    'id' => $pago->alumno->id,
                    'nombre' => trim("{$pago->alumno->nombre} {$pago->alumno->paterno} {$pago->alumno->materno}"),
                    'correo' => optional($pago->alumno->getRelation('usuario'))->correo,
                    'plan_activo' => (bool) $pago->alumno->plan_activo,
                ] : null,
                'revisor' => $pago->revisor?->administrador
                    ? trim("{$pago->revisor->administrador->nombre} {$pago->revisor->administrador->apellido_paterno}")
                    : null,
            ],
        ]);
    }

    public function edit($id)
    {
        $pago = Pago::with('alumno')->findOrFail($id);

        return \Inertia\Inertia::render('Admin/Pagos/Edit', [
            'pago' => [
                'id' => $pago->id,
                'alumno_pago' => $pago->alumno_pago,
                'tipo_pago' => $pago->tipo_pago,
                'fecha_pago' => optional($pago->fecha_pago)->format('Y-m-d'),
                'monto_pago' => (float) $pago->monto_pago,
                'referencia_pago' => $pago->referencia_pago,
                'estatus' => $pago->estatus,
                'nota_usuario' => $pago->nota_usuario,
                'comprobante_url' => $pago->comprobante ? Storage::url($pago->comprobante) : null,
            ],
            'estudiantes' => $this->estudiantesParaSelect(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tipo_pago' => 'required|string|max:50',
            'alumno_pago' => 'required|exists:estudiante,id',
            'fecha_pago' => 'required|date|before_or_equal:today',
            'referencia_pago' => 'nullable|string|max:100|unique:pagos,referencia_pago,' . $id,
            'comprobante' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'nota_usuario' => 'nullable|string',
            'estatus' => 'required|in:pendiente,aprobado,rechazado,cancelado',
        ], [
            'fecha_pago.before_or_equal' => 'La fecha de pago no puede ser posterior a hoy.',
            'referencia_pago.unique' => 'Ya existe otro pago con esa referencia.',
        ]);

        try {
            DB::beginTransaction();
            $pago = Pago::findOrFail($id);
            $estadoAnterior = $pago->estatus;
            $estudiante = Estudiante::findOrFail($request->alumno_pago);

            $data = [
                'tipo_pago' => $request->tipo_pago,
                'alumno_pago' => $request->alumno_pago,
                'fecha_pago' => $request->fecha_pago,
                // La referencia no puede quedar vacía (columna NOT NULL + UNIQUE).
                'referencia_pago' => trim((string) $request->referencia_pago) ?: $pago->referencia_pago,
                'nota_usuario' => $request->nota_usuario,
                'estatus' => $request->estatus,
            ];
            
            if ($request->hasFile('comprobante')) {
                if ($pago->comprobante && Storage::disk('public')->exists($pago->comprobante)) {
                    Storage::disk('public')->delete($pago->comprobante);
                }
                $file = $request->file('comprobante');
                $nombreLimpio = $this->sanitizarNombre($estudiante->nombre . '_' . $estudiante->paterno);
                $fechaActual = date('Ymd_His');
                $extension = $file->getClientOriginalExtension();
                $nombreArchivo = $nombreLimpio . '_' . $fechaActual . '.' . $extension;
                $imagenPath = $file->storeAs('comprobantes', $nombreArchivo, 'public');
                if ($imagenPath) $data['comprobante'] = $imagenPath;
            }
            
            if ($request->has('eliminar_comprobante') && $request->eliminar_comprobante == '1') {
                if ($pago->comprobante && Storage::disk('public')->exists($pago->comprobante)) {
                    Storage::disk('public')->delete($pago->comprobante);
                }
                $data['comprobante'] = null;
            }
            
            $seActivoPlan = false;
            
            if ($request->estatus == 'aprobado' && $estadoAnterior != 'aprobado') {
                $data['usuario_revision'] = auth()->id();
                $data['fecha_aprueba'] = now();
                $seActivoPlan = true;
            } elseif ($request->estatus == 'rechazado' && $estadoAnterior != 'rechazado') {
                $data['usuario_revision'] = auth()->id();
                $data['fecha_aprueba'] = now();
            } elseif (($estadoAnterior == 'aprobado' || $estadoAnterior == 'rechazado') && 
                      ($request->estatus == 'pendiente' || $request->estatus == 'cancelado')) {
                $usuarioActual = auth()->user()->correo ?? auth()->user()->name ?? 'Usuario actual';
                $notaActual = $data['nota_usuario'] ?? '';
                $notaRevision = "[REVISIÓN REVERTIDA] El pago fue cambiado de {$estadoAnterior} a {$request->estatus} por {$usuarioActual} el " . now()->format('d/m/Y H:i');
                $data['nota_usuario'] = $notaActual ? $notaActual . "\n\n" . $notaRevision : $notaRevision;
                $data['usuario_revision'] = null;
                $data['fecha_aprueba'] = null;
            }
            
            $pago->update($data);
            
            if ($seActivoPlan) {
                $this->activarPlanEstudiante($estudiante, $pago);
                $this->enviarCorreoAprobacion($estudiante, $pago);
                $this->notificarEstudiantePago($estudiante, $pago, 'aprobado');
            } elseif ($request->estatus == 'rechazado' && $estadoAnterior != 'rechazado') {
                $this->notificarEstudiantePago($estudiante, $pago, 'rechazado', $request->nota_usuario);
            }

            if ($estadoAnterior == 'aprobado' && $request->estatus != 'aprobado') {
                $estudiante->update(['plan_activo' => false]);
                $this->enviarCorreoDesactivacion($estudiante, $pago, $request->estatus);
            }

            DB::commit();
            
            $usuarioActual = auth()->user()->correo ?? auth()->user()->name ?? 'Usuario';
            $mensaje = 'Pago actualizado correctamente';
            if ($seActivoPlan) {
                $mensaje = "✅ Pago APROBADO por: {$usuarioActual}. Plan activado y correo enviado al estudiante.";
            } elseif ($request->estatus == 'rechazado') {
                $mensaje = "❌ Pago RECHAZADO por: {$usuarioActual}";
            }
            
            return redirect()->route('admin.pagos.index')->with('success', $mensaje);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar pago: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error al actualizar el pago: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $pago = Pago::findOrFail($id);
            
            if ($pago->estatus == 'aprobado' || $pago->estatus == 'rechazado') {
                return redirect()->route('admin.pagos.index')->with('error', 'No se puede eliminar un pago que ya ha sido revisado');
            }
            
            if ($pago->comprobante && Storage::disk('public')->exists($pago->comprobante)) {
                Storage::disk('public')->delete($pago->comprobante);
            }
            
            $nombre = $pago->alumno ? ($pago->alumno->nombre . ' ' . $pago->alumno->paterno) : '#' . $pago->id;
            $pago->delete();
            DB::commit();
            
            return redirect()->route('admin.pagos.index')->with('success', "Pago de {$nombre} eliminado correctamente");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar pago: ' . $e->getMessage());
            return redirect()->route('admin.pagos.index')->with('error', 'Error al eliminar el pago: ' . $e->getMessage());
        }
    }

    // ==================== MÉTODOS PARA CORREOS (CORREGIDOS) ====================
    
    /**
     * Enviar correo de aprobación al estudiante - SIN ENCABEZADO LARAVEL
     */
    private function enviarCorreoAprobacion($estudiante, $pago)
    {
        try {
            if (is_numeric($estudiante)) {
                $estudiante = Estudiante::find($estudiante);
                if (!$estudiante) {
                    Log::error('❌ Estudiante no encontrado con ID: ' . $estudiante);
                    return false;
                }
            }
            
            if (is_numeric($pago)) {
                $pago = Pago::find($pago);
                if (!$pago) {
                    Log::error('❌ Pago no encontrado con ID: ' . $pago);
                    return false;
                }
            }
            
            $usuario = User::find($estudiante->usuario);
            if (!$usuario) {
                Log::error('❌ Usuario no encontrado para estudiante ID: ' . $estudiante->id);
                return false;
            }
            
            $correoEstudiante = $usuario->correo;
            if (!$correoEstudiante) {
                Log::warning('⚠️ Estudiante sin correo registrado: ' . $estudiante->id);
                return false;
            }
            
            $nombreCompleto = trim($estudiante->nombre . ' ' . $estudiante->paterno);
            
            $datos = [
                'estudiante' => $estudiante,
                'pago' => $pago,
                'fecha_aprobacion' => now()->format('d/m/Y H:i:s'),
                'admin_nombre' => auth()->user()->name ?? 'Administrador SAINS',
            ];
            
            // Renderizar la vista a HTML
            $htmlContent = view('emails.pago-aprobado', $datos)->render();
            
            // Enviar usando Mail::html() - Esto EVITA el encabezado "Laravel"
            Mail::html($htmlContent, function($message) use ($correoEstudiante, $nombreCompleto) {
                $message->to($correoEstudiante, $nombreCompleto)
                        ->subject('🎉 ¡Tu pago ha sido aprobado! | SAINS Educación')
                        ->from(config('mail.from.address', 'sains.ingreso@gmail.com'), 'SAINS Educación');
            });
            
            Log::info('📧 Correo de aprobación ENVIADO a: ' . $correoEstudiante);
            return true;
            
        } catch (\Exception $e) {
            Log::error('❌ Error al enviar correo de aprobación: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar correo de rechazo al estudiante - SIN ENCABEZADO LARAVEL
     */
    private function enviarCorreoRechazo($estudiante, $pago, $motivo)
    {
        try {
            if (is_numeric($estudiante)) {
                $estudiante = Estudiante::find($estudiante);
                if (!$estudiante) {
                    Log::error('❌ Estudiante no encontrado con ID: ' . $estudiante);
                    return false;
                }
            }
            
            if (is_numeric($pago)) {
                $pago = Pago::find($pago);
                if (!$pago) {
                    Log::error('❌ Pago no encontrado con ID: ' . $pago);
                    return false;
                }
            }
            
            $usuario = User::find($estudiante->usuario);
            if (!$usuario) {
                Log::error('❌ Usuario no encontrado para estudiante ID: ' . $estudiante->id);
                return false;
            }
            
            $correoEstudiante = $usuario->correo;
            if (!$correoEstudiante) {
                Log::warning('⚠️ Estudiante sin correo registrado: ' . $estudiante->id);
                return false;
            }
            
            $nombreCompleto = trim($estudiante->nombre . ' ' . $estudiante->paterno);
            
            $datos = [
                'estudiante' => $estudiante,
                'pago' => $pago,
                'motivo' => $motivo ?? 'No se especificó un motivo',
                'fecha_rechazo' => now()->format('d/m/Y H:i:s'),
            ];
            
            // Renderizar la vista a HTML
            $htmlContent = view('emails.pago-rechazado', $datos)->render();
            
            // Enviar usando Mail::html() - Esto EVITA el encabezado "Laravel"
            Mail::html($htmlContent, function($message) use ($correoEstudiante, $nombreCompleto) {
                $message->to($correoEstudiante, $nombreCompleto)
                        ->subject('❌ Tu pago fue rechazado | SAINS Educación')
                        ->from(config('mail.from.address', 'sains.ingreso@gmail.com'), 'SAINS Educación');
            });
            
            Log::info('📧 Correo de rechazo enviado a: ' . $correoEstudiante);
            return true;
            
        } catch (\Exception $e) {
            Log::error('❌ Error al enviar correo de rechazo: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar correo de desactivación al estudiante - SIN ENCABEZADO LARAVEL
     */
    private function enviarCorreoDesactivacion($estudiante, $pago, $nuevoEstado)
    {
        try {
            if (is_numeric($estudiante)) {
                $estudiante = Estudiante::find($estudiante);
                if (!$estudiante) {
                    Log::error('❌ Estudiante no encontrado con ID: ' . $estudiante);
                    return false;
                }
            }
            
            if (is_numeric($pago)) {
                $pago = Pago::find($pago);
                if (!$pago) {
                    Log::error('❌ Pago no encontrado con ID: ' . $pago);
                    return false;
                }
            }
            
            $usuario = User::find($estudiante->usuario);
            if (!$usuario) {
                Log::error('❌ Usuario no encontrado para estudiante ID: ' . $estudiante->id);
                return false;
            }
            
            $correoEstudiante = $usuario->correo;
            if (!$correoEstudiante) {
                return false;
            }
            
            $nombreCompleto = trim($estudiante->nombre . ' ' . $estudiante->paterno);
            
            $datos = [
                'estudiante' => $estudiante,
                'pago' => $pago,
                'nuevo_estado' => $nuevoEstado,
                'fecha_cambio' => now()->format('d/m/Y H:i:s'),
                'admin_nombre' => auth()->user()->name ?? 'Administrador SAINS',
            ];
            
            // Renderizar la vista a HTML
            $htmlContent = view('emails.pago-revertido', $datos)->render();
            
            // Enviar usando Mail::html() - Esto EVITA el encabezado "Laravel"
            Mail::html($htmlContent, function($message) use ($correoEstudiante, $nombreCompleto) {
                $message->to($correoEstudiante, $nombreCompleto)
                        ->subject('⚠️ Actualización de tu pago | SAINS Educación')
                        ->from(config('mail.from.address', 'sains.ingreso@gmail.com'), 'SAINS Educación');
            });
            
            Log::info('📧 Correo de desactivación enviado a: ' . $correoEstudiante);
            return true;
            
        } catch (\Exception $e) {
            Log::error('❌ Error al enviar correo de desactivación: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Activar el plan premium del estudiante
     */
    private function activarPlanEstudiante($estudiante, $pago)
    {
        try {
            if (is_numeric($estudiante)) {
                $estudiante = Estudiante::find($estudiante);
                if (!$estudiante) {
                    Log::error('❌ Estudiante no encontrado con ID: ' . $estudiante);
                    return false;
                }
            }
            
            $estudiante->update([
                'plan_activo' => true,
                'fecha_inscripcion' => now(),
            ]);
            
            Log::info('✅ Plan activado para estudiante ID: ' . $estudiante->id . ' - Pago ID: ' . ($pago->id ?? $pago));
            return true;
        } catch (\Exception $e) {
            Log::error('❌ Error al activar plan: ' . $e->getMessage());
            throw $e;
        }
    }

    // ==================== MÉTODOS PÚBLICOS DE APROBACIÓN ====================
    
    public function aprobar($id)
    {
        try {
            DB::beginTransaction();
            $pago = Pago::findOrFail($id);
            $estudiante = Estudiante::findOrFail($pago->alumno_pago);
            
            if ($pago->estatus == 'aprobado') {
                return redirect()->route('admin.pagos.index')->with('warning', 'El pago ya estaba aprobado');
            }
            
            $pago->update([
                'estatus' => 'aprobado',
                'usuario_revision' => auth()->id(),
                'fecha_aprueba' => now(),
            ]);
            
            $this->activarPlanEstudiante($estudiante, $pago);
            $this->enviarCorreoAprobacion($estudiante, $pago);
            $this->notificarEstudiantePago($estudiante, $pago, 'aprobado');

            DB::commit();
            return redirect()->route('admin.pagos.index')->with('success', '✅ Pago aprobado. Plan activado y correo enviado al estudiante.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al aprobar pago: ' . $e->getMessage());
            return redirect()->route('admin.pagos.index')->with('error', 'Error al aprobar el pago: ' . $e->getMessage());
        }
    }
    
    public function rechazar(Request $request, $id)
    {
        $request->validate([
            'motivo_rechazo' => 'nullable|string|max:500',
        ]);
        
        try {
            DB::beginTransaction();
            $pago = Pago::findOrFail($id);
            $estudiante = Estudiante::findOrFail($pago->alumno_pago);
            
            if ($estudiante->plan_activo) {
                $estudiante->update(['plan_activo' => false]);
                $this->enviarCorreoDesactivacion($estudiante, $pago, 'rechazado');
            }
            
            $pago->update([
                'estatus' => 'rechazado',
                'usuario_revision' => auth()->id(),
                'fecha_aprueba' => now(),
                'nota_usuario' => $request->motivo_rechazo ?: 'Pago rechazado por el administrador',
            ]);
            
            $this->enviarCorreoRechazo($estudiante, $pago, $request->motivo_rechazo);
            $this->notificarEstudiantePago($estudiante, $pago, 'rechazado', $request->motivo_rechazo);

            DB::commit();
            return redirect()->route('admin.pagos.index')->with('success', '❌ Pago rechazado. Se ha notificado al estudiante.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al rechazar pago: ' . $e->getMessage());
            return redirect()->route('admin.pagos.index')->with('error', 'Error al rechazar el pago: ' . $e->getMessage());
        }
    }
    
    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estatus' => 'required|in:pendiente,aprobado,rechazado,cancelado',
            'motivo_rechazo' => 'nullable|string|max:500',
        ]);
        
        try {
            DB::beginTransaction();
            $pago = Pago::findOrFail($id);
            $estadoAnterior = $pago->estatus;
            $estudiante = Estudiante::findOrFail($pago->alumno_pago);
            
            $data = ['estatus' => $request->estatus];
            
            if ($request->estatus == 'aprobado') {
                $data['usuario_revision'] = auth()->id();
                $data['fecha_aprueba'] = now();
                $this->activarPlanEstudiante($estudiante, $pago);
                $this->enviarCorreoAprobacion($estudiante, $pago);
                $this->notificarEstudiantePago($estudiante, $pago, 'aprobado');
            } elseif ($request->estatus == 'rechazado') {
                $data['usuario_revision'] = auth()->id();
                $data['fecha_aprueba'] = now();
                if ($request->motivo_rechazo) $data['nota_usuario'] = $request->motivo_rechazo;
                if ($estudiante->plan_activo) $estudiante->update(['plan_activo' => false]);
                $this->enviarCorreoRechazo($estudiante, $pago, $request->motivo_rechazo);
                $this->notificarEstudiantePago($estudiante, $pago, 'rechazado', $request->motivo_rechazo);
            } elseif (($estadoAnterior == 'aprobado' || $estadoAnterior == 'rechazado') &&
                      ($request->estatus == 'pendiente' || $request->estatus == 'cancelado')) {
                $usuarioActual = auth()->user()->correo ?? auth()->user()->name;
                $notaActual = $pago->nota_usuario ?? '';
                $notaRevision = "[REVISIÓN REVERTIDA] Estado cambiado de {$estadoAnterior} a {$request->estatus} por {$usuarioActual} el " . now()->format('d/m/Y H:i');
                $data['nota_usuario'] = $notaActual ? $notaActual . "\n\n" . $notaRevision : $notaRevision;
                $data['usuario_revision'] = null;
                $data['fecha_aprueba'] = null;
                if ($estadoAnterior == 'aprobado') {
                    $estudiante->update(['plan_activo' => false]);
                    $this->enviarCorreoDesactivacion($estudiante, $pago, $request->estatus);
                }
            }
            
            $pago->update($data);
            DB::commit();
            
            $usuarioActual = auth()->user()->correo ?? auth()->user()->name;
            $mensaje = "Estado cambiado de " . ucfirst($estadoAnterior) . " a " . ucfirst($request->estatus);
            if ($request->estatus == 'aprobado') {
                $mensaje = "✅ Pago APROBADO por: {$usuarioActual}. Plan activado y correo enviado.";
            } elseif ($request->estatus == 'rechazado') {
                $mensaje = "❌ Pago RECHAZADO por: {$usuarioActual}. Se ha notificado al estudiante.";
            }
            
            return redirect()->route('admin.pagos.index')->with('success', $mensaje);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cambiar estado del pago: ' . $e->getMessage());
            return redirect()->route('admin.pagos.index')->with('error', 'Error al cambiar el estado del pago: ' . $e->getMessage());
        }
    }

    // ==================== MÉTODOS ADICIONALES ====================
    
    public function dashboard()
    {
        $totalIngresos = Pago::where('estatus', 'aprobado')->sum('monto_pago');
        $totalPendientes = Pago::where('estatus', 'pendiente')->sum('monto_pago');
        $totalTransacciones = Pago::count();
        $totalAprobados = Pago::where('estatus', 'aprobado')->count();
        $totalRechazados = Pago::where('estatus', 'rechazado')->count();
        
        $pagosPorTipo = Pago::selectRaw('tipo_pago, COUNT(*) as total, SUM(monto_pago) as monto')
            ->where('estatus', 'aprobado')
            ->groupBy('tipo_pago')
            ->get();
        
        $pagosPorMes = Pago::selectRaw('DATE_FORMAT(fecha_pago, "%Y-%m") as mes, COUNT(*) as total, SUM(monto_pago) as monto')
            ->where('estatus', 'aprobado')
            ->whereYear('fecha_pago', '>=', date('Y') - 1)
            ->groupBy('mes')
            ->orderBy('mes', 'desc')
            ->limit(12)
            ->get();
        
        return \Inertia\Inertia::render('Admin/Pagos/Dashboard', [
            'stats' => [
                'ingresos' => (float) $totalIngresos,
                'pendientes_monto' => (float) $totalPendientes,
                'transacciones' => $totalTransacciones,
                'aprobados' => $totalAprobados,
                'rechazados' => $totalRechazados,
            ],
            'porTipo' => $pagosPorTipo->map(fn ($r) => [
                'tipo' => $r->tipo_pago,
                'total' => $r->total,
                'monto' => (float) $r->monto,
            ]),
            'porMes' => $pagosPorMes->map(fn ($r) => [
                'mes' => $r->mes,
                'total' => $r->total,
                'monto' => (float) $r->monto,
            ]),
            'ultimosPagos' => Pago::with('alumno')->orderBy('id', 'desc')->limit(10)->get()->map(fn ($p) => [
                'id' => $p->id,
                'alumno' => $p->alumno ? trim("{$p->alumno->nombre} {$p->alumno->paterno}") : null,
                'monto' => (float) $p->monto_pago,
                'estatus' => $p->estatus,
                'fecha' => optional($p->fecha_pago)->format('d/m/Y'),
            ]),
        ]);
    }
    
    public function exportar(Request $request)
    {
        $query = Pago::with(['alumno', 'revisor.administrador']);
        if ($request->has('estatus') && $request->estatus != '') $query->where('estatus', $request->estatus);
        if ($request->has('tipo_pago') && $request->tipo_pago != '') $query->where('tipo_pago', $request->tipo_pago);
        if ($request->has('fecha_desde') && $request->fecha_desde != '') $query->whereDate('fecha_pago', '>=', $request->fecha_desde);
        if ($request->has('fecha_hasta') && $request->fecha_hasta != '') $query->whereDate('fecha_pago', '<=', $request->fecha_hasta);
        
        $pagos = $query->orderBy('id', 'desc')->get();
        $filename = 'pagos_' . date('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'w+');
        
        fputcsv($handle, ['ID', 'Tipo Pago', 'Alumno', 'Correo', 'Monto', 'Estado', 'Referencia', 'Fecha Pago', 'Fecha Revisión', 'Revisado por', 'Plan Activado', 'Nota']);
        
        foreach ($pagos as $pago) {
            $revisorNombre = 'N/A';
            if ($pago->revisor && $pago->revisor->administrador) {
                $revisorNombre = $pago->revisor->administrador->nombre_completo;
            } elseif ($pago->revisor) {
                $revisorNombre = $pago->revisor->correo;
            }
            
            $correoEstudiante = $pago->alumno ? $pago->alumno->correo : 'N/A';
            $planActivado = ($pago->alumno && $pago->alumno->plan_activo) ? 'Sí' : 'No';
            
            fputcsv($handle, [
                $pago->id, $pago->tipo_pago,
                $pago->alumno ? ($pago->alumno->nombre . ' ' . $pago->alumno->paterno) : 'N/A',
                $correoEstudiante, '$' . number_format($pago->monto_pago, 2),
                $this->getEstadoTexto($pago->estatus), $pago->referencia_pago ?? 'N/A',
                $pago->fecha_pago ? date('d/m/Y', strtotime($pago->fecha_pago)) : 'N/A',
                $pago->fecha_aprueba ? date('d/m/Y H:i', strtotime($pago->fecha_aprueba)) : 'N/A',
                $revisorNombre, $planActivado, $pago->nota_usuario ?? 'N/A',
            ]);
        }
        
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        
        return response($content, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }
    
    public function getPagosByAlumno($alumnoId)
    {
        $pagos = Pago::where('alumno_pago', $alumnoId)->orderBy('id', 'desc')->get();
        return response()->json($pagos);
    }
    
    /**
     * Notifica al estudiante el resultado de la revisión de su pago.
     */
    private function notificarEstudiantePago($estudiante, $pago, string $nuevoEstado, ?string $motivo = null): void
    {
        if (! $estudiante) {
            return;
        }

        if ($nuevoEstado === 'aprobado') {
            Notificacion::enviar($estudiante->usuario, [
                'tipo' => 'pago_aprobado',
                'titulo' => '¡Tu pago fue aprobado!',
                'mensaje' => 'Ya tienes acceso completo al Curso Premium SAINS. ¡Bienvenido!',
                'url' => route('estudiante.clases-premium'),
                'icono' => 'check', 'color' => 'green',
            ]);
        } elseif ($nuevoEstado === 'rechazado') {
            Notificacion::enviar($estudiante->usuario, [
                'tipo' => 'pago_rechazado',
                'titulo' => 'Tu pago fue rechazado',
                'mensaje' => $motivo ? "Motivo: {$motivo}" : 'Revisa tu comprobante e inténtalo de nuevo, o contáctanos.',
                'url' => route('estudiante.mis-pagos'),
                'icono' => 'close', 'color' => 'red',
            ]);
        }
    }

    private function sanitizarNombre($nombre)
    {
        $nombre = strtolower($nombre);
        $nombre = strtr($nombre, 'áéíóúñ', 'aeioun');
        $nombre = preg_replace('/[^a-z0-9_-]/', '_', $nombre);
        $nombre = preg_replace('/_+/', '_', $nombre);
        return trim($nombre, '_');
    }
    
    private function getEstadoTexto($estado)
    {
        $estados = ['pendiente' => 'Pendiente', 'aprobado' => 'Aprobado', 'rechazado' => 'Rechazado', 'cancelado' => 'Cancelado'];
        return $estados[$estado] ?? $estado;
    }
}