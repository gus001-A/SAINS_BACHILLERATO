<?php

namespace App\Http\Controllers\Alumno;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Models\Universidad;
use App\Models\Carrera;
use App\Models\User;
use App\Models\Pago;
use App\Models\Cupon;
use App\Models\Notificacion;
use App\Models\Pregunta;
use App\Models\ExamenGenerado;
use App\Models\TiempoEstudio;
use App\Models\ProgresoVideo;
use App\Models\Video;
use App\Models\Asignatura;
use App\Models\Clase;
use App\Models\ExamenRealizado;
use App\Models\AreaPregunta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

class AlumnoController extends Controller
{
    // ✅ CONSTANTES
    const MAX_INTENTOS_BASICO = 3;
    const MIN_PREGUNTAS_BASICO = 3;
    const MAX_PREGUNTAS_BASICO = 5;
    const PRECIO_CURSO = 800;

    // ========== PANEL PRINCIPAL ==========
    
    public function dashboard()
    {
        $user = Auth::user();
        $estudiante = Estudiante::where('usuario', $user->id)->first();
        
        $perfilCompleto = $estudiante ? true : false;
        $tieneFoto = false;
        
        if (!$estudiante) {
            return redirect()->route('estudiante.completar-perfil')
                ->with('warning', 'Por favor completa tu perfil para continuar');
        }
        
        if ($estudiante && $estudiante->foto) {
            $tieneFoto = true;
        }

        $documentos = \App\Models\DocumentoEstudiante::delEstudiante($estudiante->id)->get();

        return \Inertia\Inertia::render('Estudiante/Dashboard', [
            'estudiante' => $estudiante,
            'perfilCompleto' => $perfilCompleto,
            'tieneFoto' => $tieneFoto,
            'documentos' => [
                'total_requeridos' => count(\App\Models\DocumentoEstudiante::TIPOS),
                'total_subidos' => $documentos->count(),
                'total_aprobados' => $documentos->where('estatus', \App\Models\DocumentoEstudiante::ESTATUS_APROBADO)->count(),
                'total_pendientes' => $documentos->where('estatus', \App\Models\DocumentoEstudiante::ESTATUS_PENDIENTE)->count(),
                'total_rechazados' => $documentos->where('estatus', \App\Models\DocumentoEstudiante::ESTATUS_RECHAZADO)->count(),
            ],
            'certificado' => [
                'generado' => (bool) $estudiante->certificado_path,
                'generado_en' => optional($estudiante->certificado_generado_en)->format('d/m/Y'),
            ],
        ]);
    }

    public function progreso()
    {
        // La página "Mi progreso" se retiró: su contenido vive ahora en el
        // dashboard y en "Mis exámenes". Se conserva la ruta por compatibilidad.
        return redirect()->route('estudiante.dashboard');
    }

    // ========== PERFIL Y FOTO ==========
    
    public function perfil()
    {
        $user = Auth::user();
        $estudiante = Estudiante::where('usuario', $user->id)->first();
        
        if (!$estudiante) {
            return redirect()->route('estudiante.dashboard')->with('warning', 'Por favor completa tu perfil primero');
        }
        
        $universidades = Universidad::with('carrera')->orderBy('clave')->get();

        return \Inertia\Inertia::render('Estudiante/Perfil', [
            'estudianteData' => [
                'id' => $estudiante->id,
                'nombre' => $estudiante->nombre,
                'paterno' => $estudiante->paterno,
                'materno' => $estudiante->materno,
                'telefono' => $estudiante->telefono,
                'telefono_casa' => $estudiante->telefono_casa,
                'fecha_nacimiento' => optional($estudiante->fecha_nacimiento)->format('Y-m-d'),
                'sexo' => $estudiante->sexo,
                'correo' => $user->correo,
                'cupon' => $estudiante->cupon,
                'plan_activo' => (bool) $estudiante->plan_activo,
                'universidad_interes' => $estudiante->universidad_interes,
                'foto_url' => $estudiante->foto ? Storage::url($estudiante->foto) : null,
                'fecha_inscripcion' => optional($estudiante->fecha_inscripcion)->format('Y-m-d'),
            ],
            'universidades' => $universidades->map(fn ($u) => [
                'id' => $u->id,
                'clave' => $u->clave,
                'direccion' => $u->direccion,
                'carrera' => $u->carrera->nombre ?? null,
            ])->values(),
        ]);
    }

    /**
     * Descarga el certificado de finalización ya generado por el administrador.
     */
    public function descargarCertificado()
    {
        $user = Auth::user();
        $estudiante = Estudiante::where('usuario', $user->id)->first();

        if (!$estudiante || !$estudiante->certificado_path || !Storage::disk('public')->exists($estudiante->certificado_path)) {
            abort(404, 'Aún no tienes un certificado generado.');
        }

        return response()->file(Storage::disk('public')->path($estudiante->certificado_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="certificado_' . $estudiante->id . '.pdf"',
        ]);
    }

    public function completarPerfilForm()
    {
        $user = Auth::user();
        $estudiante = Estudiante::where('usuario', $user->id)->first();
        
        if ($estudiante) {
            return redirect()->route('estudiante.dashboard')
                ->with('info', 'Ya tienes un perfil completado');
        }

        return \Inertia\Inertia::render('Estudiante/CompletarPerfil', [
            'correo' => $user->correo,
            'preparatorias' => \App\Models\Preparatoria::orderBy('centro_educativo')
                ->get(['id', 'centro_educativo', 'estado'])
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'label' => $p->centro_educativo . ($p->estado ? " ({$p->estado})" : ''),
                ])->values(),
            'universidades' => Universidad::orderBy('clave')
                ->get(['id', 'clave', 'direccion'])
                ->map(fn ($u) => [
                    'id' => $u->id,
                    'label' => trim("{$u->clave} - {$u->direccion}"),
                ])->values(),
        ]);
    }

    public function completarPerfil(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'paterno' => 'required|string|max:255',
            'materno' => 'nullable|string|max:255',
            'telefono' => 'required|regex:/^[0-9]{10}$/',
            'fecha_nacimiento' => 'required|date|before:today',
            'sexo' => 'required|in:M,F',
            'escuela_procedencia' => 'nullable|exists:preparatorias,id',
            'universidad_interes' => 'nullable|exists:universidades,id',
            'telefono_casa' => 'nullable|regex:/^[0-9]{7,10}$/',
            'cupon' => 'nullable|string|max:50',
        ], [
            'telefono.regex' => 'El teléfono debe tener 10 dígitos.',
            'telefono_casa.regex' => 'El teléfono de casa debe tener entre 7 y 10 dígitos.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
        ]);

        try {
            $user = Auth::user();
            
            $existePerfil = Estudiante::where('usuario', $user->id)->exists();
            if ($existePerfil) {
                return redirect()->route('estudiante.dashboard')
                    ->with('info', 'Ya tienes un perfil completado');
            }

            $estudiante = Estudiante::create([
                'nombre' => $request->nombre,
                'paterno' => $request->paterno,
                'materno' => $request->materno,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'sexo' => $request->sexo,
                'telefono' => $request->telefono,
                'telefono_casa' => $request->telefono_casa,
                'escuela_procedencia' => $request->escuela_procedencia,
                'cupon' => null,
                'fecha_inscripcion' => now(),
                'plan_activo' => false,
                'universidad_interes' => $request->universidad_interes,
                'foto' => null,
                'usuario' => $user->id
            ]);

            // Cupón que cubre el 100% → activa Premium automáticamente (sin pago).
            $cupon100 = null;
            if ($request->filled('cupon')) {
                $cupon = Cupon::where('codigo', strtoupper($request->cupon))->first();
                if ($cupon && $cupon->aplicable() && $cupon->cubreTodo()) {
                    $cupon100 = $cupon;
                    $this->activarPlanPorCupon($estudiante, $cupon);
                } elseif ($cupon && $cupon->aplicable()) {
                    // Cupón parcial: sólo se asocia para aplicarse al pagar.
                    $estudiante->update(['cupon' => $cupon->codigo]);
                    session(['cupon_aplicado' => $cupon->codigo]);
                }
            }

            Notificacion::enviar($user->id, [
                'tipo' => 'perfil_completo',
                'titulo' => 'Perfil completado',
                'mensaje' => $cupon100
                    ? '¡Tu cupón cubre el 100%! Tu acceso Premium ya está activo.'
                    : 'Ya puedes usar los simuladores y ver las clases gratuitas. Hazte Premium para desbloquear todo.',
                'url' => route('estudiante.clases-premium'),
                'icono' => 'check', 'color' => 'green',
            ]);
            Notificacion::enviarAdmins([
                'tipo' => 'estudiante_nuevo',
                'titulo' => 'Nuevo estudiante',
                'mensaje' => "{$estudiante->nombre_completo} completó su registro." . ($cupon100 ? " Activó Premium con el cupón {$cupon100->codigo} (100%)." : ''),
                'url' => route('admin.estudiantes.index'),
                'icono' => 'bell', 'color' => 'indigo',
            ]);

            return redirect()->route('estudiante.dashboard')
                ->with('success', $cupon100
                    ? '¡Perfil completado! Tu cupón cubre el 100%, ya tienes acceso Premium.'
                    : '¡Perfil completado exitosamente!');

        } catch (\Exception $e) {
            Log::error('Error al completar perfil: ' . $e->getMessage());
            return back()->with('error', 'Error al completar perfil: ' . $e->getMessage());
        }
    }

    /**
     * Activa el plan Premium de un estudiante gracias a un cupón que cubre el 100%.
     * Marca el cupón como usado y deja registro de un "pago" de $0 para el historial.
     */
    private function activarPlanPorCupon(Estudiante $estudiante, Cupon $cupon): void
    {
        DB::transaction(function () use ($estudiante, $cupon) {
            $cupon->update([
                'usado' => true,
                'usuario_uso' => $estudiante->usuario,
                'fecha_uso' => now(),
            ]);

            $estudiante->update([
                'cupon' => $cupon->codigo,
                'plan_activo' => true,
                'fecha_inscripcion' => now(),
            ]);

            Pago::create([
                'alumno_pago' => $estudiante->id,
                'tipo_pago' => 'cupon',
                'monto_pago' => 0,
                'estatus' => 'completado',
                'referencia_pago' => Pago::referenciaPreferida('CUPON-' . $cupon->codigo, 'CUPON'),
                'fecha_pago' => now(),
                'nota_usuario' => "Plan Premium activado con el cupón {$cupon->codigo} (100% de descuento).",
            ]);
        });

        session()->forget('cupon_aplicado');

        Notificacion::enviar($estudiante->usuario, [
            'tipo' => 'pago_aprobado',
            'titulo' => '¡Acceso Premium activado!',
            'mensaje' => "Tu cupón {$cupon->codigo} cubre el 100%. Ya tienes acceso completo al Curso Premium.",
            'url' => route('estudiante.clases-premium'),
            'icono' => 'check', 'color' => 'green',
        ]);
        Notificacion::enviarAdmins([
            'tipo' => 'pago_nuevo',
            'titulo' => 'Premium activado con cupón 100%',
            'mensaje' => "{$estudiante->nombre_completo} activó Premium con el cupón {$cupon->codigo}.",
            'url' => route('admin.estudiantes.index'),
            'icono' => 'dollar', 'color' => 'green',
        ]);
    }

    public function actualizarPerfil(Request $request)
    {
        try {
            $user = Auth::user();
            $estudiante = Estudiante::where('usuario', $user->id)->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perfil de estudiante no encontrado'
                ], 404);
            }
            
            $request->validate([
                'nombre' => 'required|string|max:255',
                'paterno' => 'nullable|string|max:255',
                'materno' => 'nullable|string|max:255',
                'telefono' => 'nullable|regex:/^[0-9]{10}$/',
                'fecha_nacimiento' => 'nullable|date|before:today',
                'sexo' => 'nullable|in:M,F',
                'correo' => 'required|email|max:255|unique:usuario,correo,' . $user->id . ',id'
            ], [
                'telefono.regex' => 'El teléfono debe tener 10 dígitos.',
                'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            ]);
            
            $estudiante->update([
                'nombre' => $request->nombre,
                'paterno' => $request->paterno,
                'materno' => $request->materno,
                'telefono' => $request->telefono,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'sexo' => $request->sexo,
            ]);
            
            if ($request->correo !== $user->correo) {
                $user->correo = $request->correo;
                $user->save();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado correctamente',
                'nuevo_correo' => $user->correo
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar perfil: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el perfil: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'password_actual' => 'required|string',
            'password_nueva' => 'required|string|min:6|confirmed'
        ]);

        try {
            $user = Auth::user();
            
            if (!\Hash::check($request->password_actual, $user->contraseña)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La contraseña actual es incorrecta'
                ], 400);
            }
            
            $user->contraseña = \Hash::make($request->password_nueva);
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Contraseña cambiada exitosamente'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al cambiar contraseña: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar la contraseña: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== FOTO DE PERFIL ==========
    
    public function subirFoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $user = Auth::user();
            $estudiante = Estudiante::where('usuario', $user->id)->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Primero completa tu perfil'
                ], 400);
            }
            
            if ($estudiante->foto && Storage::disk('public')->exists($estudiante->foto)) {
                Storage::disk('public')->delete($estudiante->foto);
            }
            
            $fotoPath = $request->file('foto')->store('fotos_perfil', 'public');
            
            $estudiante->foto = $fotoPath;
            $estudiante->save();
            
            return response()->json([
                'success' => true,
                'message' => '¡Foto de perfil actualizada exitosamente!',
                'foto_url' => Storage::url($fotoPath)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al subir foto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al subir la foto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getFoto()
    {
        $user = Auth::user();
        $estudiante = Estudiante::where('usuario', $user->id)->first();
        
        if (!$estudiante || !$estudiante->foto) {
            return response()->json([
                'success' => false,
                'message' => 'No hay foto de perfil',
                'tiene_foto' => false
            ]);
        }
        
        return response()->json([
            'success' => true,
            'tiene_foto' => true,
            'foto_url' => Storage::url($estudiante->foto),
            'foto_path' => $estudiante->foto
        ]);
    }

    public function eliminarFoto()
    {
        try {
            $user = Auth::user();
            $estudiante = Estudiante::where('usuario', $user->id)->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estudiante no encontrado'
                ], 404);
            }
            
            if ($estudiante->foto && Storage::disk('public')->exists($estudiante->foto)) {
                Storage::disk('public')->delete($estudiante->foto);
                $estudiante->foto = null;
                $estudiante->save();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Foto eliminada correctamente'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No hay foto para eliminar'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar foto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la foto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEstudiante()
    {
        $user = Auth::user();
        $estudiante = Estudiante::where('usuario', $user->id)->first();
        
        return response()->json([
            'success' => true,
            'estudiante' => $estudiante,
            'tiene_perfil' => $estudiante ? true : false,
            'tiene_foto' => $estudiante && $estudiante->foto ? true : false,
            'foto_url' => $estudiante && $estudiante->foto ? Storage::url($estudiante->foto) : null
        ]);
    }

    // ========== CLASES PREMIUM ==========
    
    public function clasesPremium()
    {
        $user = Auth::user();
        $estudiante = Estudiante::where('usuario', $user->id)->first();
        
        $asignaturas = Asignatura::whereHas('clases', function($query) {
            $query->whereNotNull('link')->where('link', '!=', '');
        })->with(['clases' => function($query) {
            $query->whereNotNull('link')->where('link', '!=', '')
                  ->orderBy('num_clase', 'asc');
        }])->orderBy('nombre')->get();

        // El video de cada clase se resuelve por "link" (igual que en
        // registrarProgresoVideo), no por clases.id_video: esa columna no se
        // usa en la práctica y siempre viene vacía. Algunos links tienen más
        // de un registro en "videos" (sin índice único), así que se agrupan
        // por link en vez de quedarnos con uno solo al azar.
        $enlaces = $asignaturas->flatMap(fn ($a) => $a->clases->pluck('link'))->filter()->unique()->values();
        $videosPorLink = Video::whereIn('link', $enlaces)->get(['id', 'link', 'duracion'])->groupBy('link');

        $vistasIds = [];
        if ($estudiante) {
            $vistasIds = ProgresoVideo::where('estudiante_id', $estudiante->id)
                ->where('completado', true)
                ->pluck('video_id')
                ->toArray();
        }
        
        $examenesMateria = ExamenGenerado::where('tipo_examen', 'Materia')
            ->orWhere('tipo_examen', 'materia')
            ->get();
        
        $examenesPorArea = [];
        
        foreach($asignaturas as $asignatura) {
            $area = AreaPregunta::where('nombre', $asignatura->nombre)->first();
            
            if ($area) {
                foreach($examenesMateria as $examen) {
                    $tienePreguntas = DB::table('apoyo_preguntas as ap')
                        ->join('preguntas as p', 'ap.pregunta', '=', 'p.id')
                        ->where('ap.examen', $examen->id)
                        ->where('p.id_area', $area->id)
                        ->exists();
                    
                    if ($tienePreguntas) {
                        $examenesPorArea[$asignatura->id] = $examen;
                        break;
                    }
                }
            }
        }
        
        $tieneAccesoPremium = $estudiante && $estudiante->plan_activo;

        $data = $asignaturas->map(function ($a) use ($vistasIds, $examenesPorArea, $videosPorLink) {
            $examen = $examenesPorArea[$a->id] ?? null;
            return [
                'id' => $a->id,
                'nombre' => $a->nombre,
                'examen' => $examen ? [
                    'id' => $examen->id,
                    'nombre' => $examen->nombre ?? $examen->tipo_examen ?? 'Examen de materia',
                ] : null,
                'clases' => $a->clases->values()->map(function ($c, $i) use ($vistasIds, $videosPorLink) {
                    $videos = $videosPorLink->get($c->link) ?? collect();
                    $idsVideo = $videos->pluck('id');
                    $duracion = $videos->first(fn ($v) => $v->duracion && $v->duracion !== '00:00:00');
                    return [
                        'id' => $c->id,
                        'num_clase' => $c->num_clase,
                        'orden' => $i + 1,
                        'nombre_clase' => $c->nombre_clase,
                        'link' => $c->link,
                        'url' => $c->url,
                        'gratis' => (bool) $c->gratis,
                        'duracion' => $duracion?->duracion,
                        'vista' => $idsVideo->intersect($vistasIds)->isNotEmpty(),
                    ];
                }),
            ];
        })->values();

        $examenFinal = ExamenGenerado::where('tipo_examen', 'Curso')
            ->orWhere('tipo_examen', 'curso')
            ->first();

        return \Inertia\Inertia::render('Estudiante/ClasesPremium', [
            'asignaturas' => $data,
            'tieneAccesoPremium' => (bool) $tieneAccesoPremium,
            'examenFinal' => $examenFinal ? [
                'id' => $examenFinal->id,
                'numero_preguntas' => $examenFinal->numero_preguntas,
                'tiempo' => $examenFinal->tiempo,
            ] : null,
        ]);
    }

    public function registrarProgresoVideo(Request $request)
    {
        try {
            $user = Auth::user();
            $estudiante = Estudiante::where('usuario', $user->id)->first();
            
            if (!$estudiante) {
                return response()->json(['success' => false, 'message' => 'Estudiante no encontrado']);
            }
            
            $claseId = $request->video_id;
            $ultimoSegundo = $request->ultimo_segundo ?? '00:00:00';
            $duracionVideo = intval($request->duracion_video ?? 0);
            $porcentajeVisto = intval($request->porcentaje_visto ?? 0);
            $esPrimerRegistro = $request->es_primer_registro ?? false;
            
            if (!$claseId) {
                return response()->json(['success' => false, 'message' => 'ID de clase no proporcionado']);
            }
            
            $clase = Clase::find($claseId);
            if (!$clase) {
                return response()->json(['success' => false, 'message' => 'Clase no encontrada']);
            }
            
            $video = Video::firstOrCreate(
                ['link' => $clase->link],
                [
                    'materia' => $clase->asignatura->nombre ?? 'General',
                    'tema' => $clase->nombre_clase,
                    'titulo' => $clase->nombre_clase,
                    'duracion' => $this->formatearSegundos($duracionVideo),
                    'plan' => true
                ]
            );
            
            if ($duracionVideo > 0 && $video->duracion == '00:00:00') {
                $video->duracion = $this->formatearSegundos($duracionVideo);
                $video->save();
            }
            
            $completado = ($porcentajeVisto >= 80);
            
            $progreso = ProgresoVideo::where('estudiante_id', $estudiante->id)
                ->where('video_id', $video->id)
                ->first();
            
            if ($progreso) {
                if ($esPrimerRegistro) {
                    $progreso->veces_visto = ($progreso->veces_visto ?? 0) + 1;
                }
                
                $progreso->ultimo_segundo = $ultimoSegundo;
                
                if ($completado && !$progreso->completado) {
                    $progreso->completado = true;
                }
                
                $progreso->save();
            } else {
                ProgresoVideo::create([
                    'estudiante_id' => $estudiante->id,
                    'video_id' => $video->id,
                    'fecha_visto' => now(),
                    'completado' => $completado,
                    'ultimo_segundo' => $ultimoSegundo,
                    'veces_visto' => 1
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Progreso registrado',
                'completado' => $completado
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en registrarProgresoVideo: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function formatearSegundos($segundos)
    {
        $horas = floor($segundos / 3600);
        $minutos = floor(($segundos % 3600) / 60);
        $segs = $segundos % 60;
        return sprintf("%02d:%02d:%02d", $horas, $minutos, $segs);
    }

    // ========== CHECKOUT Y PAGOS ==========
    
    public function checkout()
    {
        $estudiante = Estudiante::where('usuario', Auth::id())->first();
        
        if (!$estudiante) {
            return redirect()->route('estudiante.completar-perfil')->with('warning', 'Primero completa tu perfil');
        }
        
        if ($estudiante->plan_activo) {
            return redirect()->route('estudiante.clases-premium')->with('info', 'Ya tienes acceso al curso premium');
        }
        
        $pagoPendiente = Pago::where('alumno_pago', $estudiante->id)
            ->whereIn('estatus', ['pendiente', 'procesando', 'revisando'])
            ->first();
        
        if ($pagoPendiente) {
            return redirect()->route('estudiante.checkout-pendiente', $pagoPendiente->id)
                ->with('warning', 'Ya tienes un pago pendiente');
        }
        
        $precioOriginal = self::PRECIO_CURSO;
        $precioFinal = self::PRECIO_CURSO;
        $montoDescuento = 0;
        $porcentajeDescuento = null;
        $cuponAplicado = session('cupon_aplicado');
        
        if (empty($cuponAplicado) && !empty($estudiante->cupon)) {
            $cupon = Cupon::where('codigo', $estudiante->cupon)->first();
            if ($cupon && !$cupon->usado && $cupon->estatus === 'activo') {
                $expirado = $cupon->fecha_expiracion && Carbon::now()->greaterThan($cupon->fecha_expiracion);
                if (!$expirado) {
                    session(['cupon_aplicado' => $estudiante->cupon]);
                    $cuponAplicado = $estudiante->cupon;
                }
            }
        }
        
        if ($cuponAplicado) {
            $cupon = Cupon::where('codigo', $cuponAplicado)->first();
            if ($cupon && !$cupon->usado && $cupon->estatus === 'activo') {
                $expirado = $cupon->fecha_expiracion && Carbon::now()->greaterThan($cupon->fecha_expiracion);
                
                if (!$expirado) {
                    if ($cupon->tipo_descuento === 'porcentaje') {
                        $montoDescuento = ($precioOriginal * $cupon->valor_descuento) / 100;
                        $porcentajeDescuento = $cupon->valor_descuento;
                    } else {
                        $montoDescuento = $cupon->valor_descuento;
                    }
                    $precioFinal = max(0, $precioOriginal - $montoDescuento);
                } else {
                    session()->forget('cupon_aplicado');
                    $cuponAplicado = null;
                }
            } else {
                session()->forget('cupon_aplicado');
                $cuponAplicado = null;
            }
        }
        
        $precios = [
            'precio_original' => $precioOriginal,
            'precio_final' => round($precioFinal, 2),
            'monto_descuento' => round($montoDescuento, 2),
            'tiene_descuento' => $montoDescuento > 0,
            'cupon_aplicado' => $cuponAplicado,
            'porcentaje_descuento' => $porcentajeDescuento
        ];
        
        return \Inertia\Inertia::render('Estudiante/Checkout', [
            'estudianteData' => [
                'nombre_completo' => $estudiante->nombre_completo,
                'correo' => Auth::user()->correo,
                'telefono' => $estudiante->telefono,
            ],
            'precios' => $precios,
            'mpPublicKey' => config('mercadopago.public_key'),
        ]);
    }

    public function checkoutPendiente($pagoId = null)
    {
        $estudiante = Estudiante::where('usuario', Auth::id())->first();
        
        if (!$estudiante) {
            return redirect()->route('estudiante.completar-perfil')->with('warning', 'Primero completa tu perfil');
        }
        
        if ($estudiante->plan_activo) {
            return redirect()->route('estudiante.clases-premium')->with('info', 'Ya tienes acceso premium');
        }
        
        if ($pagoId) {
            $pagoPendiente = Pago::where('id', $pagoId)
                ->where('alumno_pago', $estudiante->id)
                ->whereNotIn('estatus', ['cancelado', 'completado', 'rechazado'])
                ->first();
        } else {
            $pagoPendiente = Pago::where('alumno_pago', $estudiante->id)
                ->whereNotIn('estatus', ['cancelado', 'completado', 'rechazado'])
                ->first();
        }
        
        if (!$pagoPendiente) {
            return redirect()->route('estudiante.checkout')->with('info', 'No tienes pagos pendientes');
        }
        
        return \Inertia\Inertia::render('Estudiante/CheckoutPendiente', [
            'pago' => $this->datosPago($pagoPendiente),
        ]);
    }

    public function procesarSolicitudPago(Request $request)
    {
        $request->validate([
            'metodo_pago' => 'required|in:transferencia,oxxo'
        ]);
        
        $estudiante = Estudiante::where('usuario', Auth::id())->first();
        
        if (!$estudiante) {
            return redirect()->back()->with('error', 'Primero completa tu perfil');
        }
        
        if ($estudiante->plan_activo) {
            return redirect()->route('estudiante.dashboard')->with('info', 'Ya tienes un plan activo');
        }
        
        $pagoExistente = Pago::where('alumno_pago', $estudiante->id)
            ->whereNotIn('estatus', ['cancelado', 'completado', 'rechazado'])
            ->first();
        
        if ($pagoExistente) {
            return redirect()->route('estudiante.checkout-pendiente', $pagoExistente->id)
                ->with('warning', 'Ya tienes una solicitud de pago pendiente');
        }
        
        $codigoCupon = session('cupon_aplicado');
        $precioOriginal = self::PRECIO_CURSO;
        $montoDescuento = 0;
        $precioFinal = $precioOriginal;
        $detalleCupon = null;
        
        if ($codigoCupon) {
            $cupon = Cupon::where('codigo', $codigoCupon)->first();
            if ($cupon && !$cupon->usado && $cupon->estatus === 'activo') {
                $expirado = $cupon->fecha_expiracion && Carbon::now()->greaterThan($cupon->fecha_expiracion);
                if (!$expirado) {
                    if ($cupon->tipo_descuento === 'porcentaje') {
                        $montoDescuento = ($precioOriginal * $cupon->valor_descuento) / 100;
                        $detalleCupon = "Cupón: $codigoCupon | Descuento del {$cupon->valor_descuento}% | Ahorro: $" . number_format($montoDescuento, 2) . " MXN";
                    } else {
                        $montoDescuento = min($cupon->valor_descuento, $precioOriginal);
                        $detalleCupon = "Cupón: $codigoCupon | Descuento de $" . number_format($cupon->valor_descuento, 2) . " MXN | Ahorro: $" . number_format($montoDescuento, 2) . " MXN";
                    }
                    $precioFinal = max(0, $precioOriginal - $montoDescuento);
                }
            }
        }
        
        try {
            DB::beginTransaction();
            
            $referencia = $this->generarReferenciaPago($request->metodo_pago, $estudiante->id);
            
            $notaCompleta = "=== SOLICITUD DE PAGO ===\n";
            $notaCompleta .= "Fecha: " . now()->format('d/m/Y H:i:s') . "\n";
            $notaCompleta .= "Monto original: $" . number_format($precioOriginal, 2) . " MXN\n";
            
            if ($detalleCupon) {
                $notaCompleta .= "--- DESCUENTO APLICADO ---\n";
                $notaCompleta .= $detalleCupon . "\n";
                $notaCompleta .= "Monto con descuento: $" . number_format($precioFinal, 2) . " MXN\n";
            } else {
                $notaCompleta .= "Sin cupón aplicado\n";
                $notaCompleta .= "Monto a pagar: $" . number_format($precioFinal, 2) . " MXN\n";
            }
            
            $notaCompleta .= "---\n";
            $notaCompleta .= "Método de pago: " . strtoupper($request->metodo_pago) . "\n";
            $notaCompleta .= "Referencia: $referencia\n";
            $notaCompleta .= "Estado: Pendiente de pago";
            
            $pago = Pago::create([
                'alumno_pago' => $estudiante->id,
                'tipo_pago' => $request->metodo_pago,
                'monto_pago' => round($precioFinal, 2),
                'estatus' => 'pendiente',
                'referencia_pago' => $referencia,
                'fecha_pago' => now(),
                'nota_usuario' => $notaCompleta,
            ]);
            
            if ($codigoCupon) {
                $cupon = Cupon::where('codigo', $codigoCupon)->first();
                if ($cupon && !$cupon->usado && $cupon->estatus === 'activo') {
                    $expirado = $cupon->fecha_expiracion && Carbon::now()->greaterThan($cupon->fecha_expiracion);
                    if (!$expirado) {
                        $cupon->update([
                            'usado' => true,
                            'usuario_uso' => $estudiante->usuario,
                            'fecha_uso' => now()
                        ]);
                        $estudiante->update(['cupon' => $codigoCupon]);
                    }
                }
            }
            
            DB::commit();
            session()->forget('cupon_aplicado');

            Notificacion::enviar($estudiante->usuario, [
                'tipo' => 'pago_solicitud',
                'titulo' => 'Solicitud de pago generada',
                'mensaje' => "Referencia {$referencia} por $" . number_format($precioFinal, 2) . " MXN. Descarga tu ficha y sube tu comprobante cuando pagues.",
                'url' => route('estudiante.ficha-pago', $pago->id),
                'icono' => 'file', 'color' => 'amber',
            ]);
            Notificacion::enviarAdmins([
                'tipo' => 'pago_nuevo',
                'titulo' => 'Nueva solicitud de pago',
                'mensaje' => "{$estudiante->nombre_completo} generó una ficha ({$request->metodo_pago}) por $" . number_format($precioFinal, 2) . " MXN.",
                'url' => route('admin.pagos.show', $pago->id),
                'icono' => 'dollar', 'color' => 'indigo',
            ]);

            return redirect()->route('estudiante.ficha-pago', $pago->id)
                ->with('success', 'Solicitud de pago registrada. Descarga tu ficha para realizar el pago.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar solicitud de pago: ' . $e->getMessage());
            return back()->with('error', 'Error al procesar la solicitud: ' . $e->getMessage());
        }
    }

    public function aplicarCupon(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:50'
        ]);
        
        $estudiante = Estudiante::where('usuario', Auth::id())->first();
        
        if (!empty($estudiante->cupon)) {
            return redirect()->back()->with('error','Ya tienes un cupón asociado a tu cuenta');
        }
        
        if (session('cupon_aplicado')) {
            return redirect()->back()->with('error','Ya tienes un cupón aplicado');
        }
        
        $codigo = strtoupper($request->codigo);
        $cupon = Cupon::where('codigo', $codigo)->first();
        
        if (!$cupon) {
            return redirect()->back()->with('error','El cupón no existe');
        }
        
        if ($cupon->usado) {
            return redirect()->back()->with('error','Este cupón ya ha sido utilizado');
        }
        
        if ($cupon->fecha_expiracion && Carbon::now()->greaterThan($cupon->fecha_expiracion)) {
            return redirect()->back()->with('error','Este cupón ha expirado');
        }
        
        if ($cupon->estatus !== 'activo') {
            return redirect()->back()->with('error','Este cupón no está activo');
        }

        // Cupón que cubre el 100% → activa Premium al instante, sin pago.
        if ($cupon->cubreTodo()) {
            if (!$estudiante) {
                return redirect()->route('estudiante.completar-perfil')
                    ->with('info', 'Completa tu perfil para activar tu cupón.');
            }
            if ($estudiante->plan_activo) {
                return redirect()->route('estudiante.dashboard')->with('info', 'Ya tienes el plan Premium activo.');
            }
            $this->activarPlanPorCupon($estudiante, $cupon);
            return redirect()->route('estudiante.clases-premium')
                ->with('success', '¡Tu cupón cubre el 100%! Ya tienes acceso Premium completo.');
        }

        session(['cupon_aplicado' => $codigo]);

        if ($cupon->tipo_descuento === 'porcentaje') {
            $mensaje = "¡Cupón aplicado! " . $cupon->valor_descuento . "% de descuento";
        } else {
            $mensaje = "¡Cupón aplicado! $" . number_format($cupon->valor_descuento, 2) . " de descuento";
        }

        return redirect()->back()->with('success', $mensaje);
    }

    public function eliminarCupon()
    {
        session()->forget('cupon_aplicado');
        return redirect()->route('estudiante.checkout')->with('success', 'Cupón eliminado correctamente');
    }

    public function cancelarPago($pagoId)
    {
        try {
            $estudiante = Estudiante::where('usuario', Auth::id())->first();
            
            if (!$estudiante) {
                return redirect()->route('estudiante.dashboard')->with('error', 'Estudiante no encontrado');
            }
            
            $pago = Pago::where('id', $pagoId)
                ->where('alumno_pago', $estudiante->id)
                ->where('estatus', 'pendiente')
                ->first();
            
            if (!$pago) {
                return redirect()->route('estudiante.checkout')->with('error', 'No se encontró un pago pendiente para cancelar');
            }
            
            $pago->estatus = 'cancelado';
            $pago->save();
            
            session()->forget('cupon_aplicado');
            
            return redirect()->route('estudiante.checkout')->with('success', 'Pago cancelado correctamente. Ya puedes generar uno nuevo.');
            
        } catch (\Exception $e) {
            Log::error('Error al cancelar pago: ' . $e->getMessage());
            return redirect()->route('estudiante.checkout')->with('error', 'Error al cancelar el pago: ' . $e->getMessage());
        }
    }

    public function mostrarFichaPago($pagoId)
    {
        $estudiante = Estudiante::where('usuario', Auth::id())->first();
        $pago = Pago::where('id', $pagoId)->where('alumno_pago', $estudiante->id)->firstOrFail();

        return \Inertia\Inertia::render('Estudiante/FichaPago', [
            'pago' => $this->datosPago($pago),
            'fichaUrl' => asset('images/FICHA_PAGO.jpeg'),
        ]);
    }

    public function descargarFichaPago($pagoId)
    {
        $estudiante = Estudiante::where('usuario', Auth::id())->first();
        $pago = Pago::where('id', $pagoId)->where('alumno_pago', $estudiante->id)->firstOrFail();
        
        $rutaImagen = public_path('images/FICHA_PAGO.jpeg');
        
        if (!file_exists($rutaImagen)) {
            return back()->with('error', 'La ficha de pago no está disponible');
        }
        
        return response()->download($rutaImagen, 'ficha_pago_' . $pago->referencia_pago . '.jpeg');
    }

    public function subirComprobantePago(Request $request)
    {
        $request->validate([
            'pago_id' => 'required|exists:pagos,id',
            'comprobante' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'nota_usuario' => 'nullable|string|max:500'
        ]);

        try {
            $estudiante = Estudiante::where('usuario', Auth::id())->first();
            $pago = Pago::where('id', $request->pago_id)->where('alumno_pago', $estudiante->id)->first();
            
            if (!$pago) {
                return redirect()->back()->with('error', 'Pago no encontrado');
            }
            
            $file = $request->file('comprobante');
            $nombreArchivo = 'comprobante_' . $pago->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $ruta = $file->storeAs('comprobantes', $nombreArchivo, 'public');
            
            $pago->update([
                'comprobante' => $ruta,
                'nota_usuario' => $request->nota_usuario,
                'estatus' => 'revisando'
            ]);

            Notificacion::enviar($estudiante->usuario, [
                'tipo' => 'comprobante_recibido',
                'titulo' => 'Comprobante recibido',
                'mensaje' => 'Tu pago está en revisión. Te avisaremos en cuanto sea aprobado (24–48 h hábiles).',
                'url' => route('estudiante.pago-exito', $pago->id),
                'icono' => 'clock', 'color' => 'blue',
            ]);
            Notificacion::enviarAdmins([
                'tipo' => 'comprobante_subido',
                'titulo' => 'Comprobante subido',
                'mensaje' => "{$estudiante->nombre_completo} subió su comprobante del pago #{$pago->id}. Pendiente de revisar.",
                'url' => route('admin.pagos.show', $pago->id),
                'icono' => 'paperclip', 'color' => 'violet',
            ]);

            return redirect()->route('estudiante.pago-exito', $pago->id)
                ->with('success', '¡Comprobante subido! Tu pago está en revisión.');
            
        } catch (\Exception $e) {
            Log::error('Error al subir comprobante: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al subir el comprobante');
        }
    }

    public function pagoExito($pagoId)
    {
        $estudiante = Estudiante::where('usuario', Auth::id())->first();
        $pago = Pago::where('id', $pagoId)->where('alumno_pago', $estudiante->id)->firstOrFail();

        return \Inertia\Inertia::render('Estudiante/PagoExito', [
            'pago' => $this->datosPago($pago),
        ]);
    }

    public function misPagos()
    {
        $estudiante = Estudiante::where('usuario', Auth::id())->first();
        
        if (!$estudiante) {
            return redirect()->route('estudiante.dashboard')->with('error', 'Primero completa tu perfil');
        }
        
        // Obtener el último pago del estudiante (o null si no existe)
        $pago = Pago::where('alumno_pago', $estudiante->id)
            ->orderBy('id', 'desc')
            ->first();  // Cambiado de get() a first()
        
        return \Inertia\Inertia::render('Estudiante/MisPagos', [
            'pago' => $pago ? $this->datosPago($pago) : null,
        ]);
    }

    private function generarReferenciaPago($metodo, $estudianteId)
    {
        $prefix = match($metodo) {
            'transferencia' => 'TRA',
            'oxxo' => 'OXX',
            default => 'PAG'
        };

        // Reintenta hasta obtener una referencia que no exista (la columna es UNIQUE).
        do {
            $ref = $prefix . date('Ymd')
                . str_pad((string) $estudianteId, 6, '0', STR_PAD_LEFT)
                . random_int(1000, 9999);
        } while (Pago::where('referencia_pago', $ref)->exists());

        return $ref;
    }

    /**
     * Arma el arreglo de datos de un pago para las páginas Inertia del estudiante.
     */
    private function datosPago(Pago $pago): array
    {
        return [
            'id' => $pago->id,
            'tipo_pago' => $pago->tipo_pago,
            'monto_pago' => (float) $pago->monto_pago,
            'estatus' => $pago->estatus,
            'referencia_pago' => $pago->referencia_pago,
            'comprobante_url' => $pago->comprobante ? Storage::url($pago->comprobante) : null,
            'nota_usuario' => $pago->nota_usuario,
            'fecha_pago' => optional($pago->fecha_pago)->toIso8601String(),
            'fecha_pago_formato' => optional($pago->fecha_pago)->format('d/m/Y H:i'),
        ];
    }

    /**
     * Datos base de un ExamenGenerado para las páginas de examen/simulador.
     */
    private function datosExamen($examen): array
    {
        return [
            'id' => $examen->id,
            'nombre' => $examen->nombre ?? $examen->tipo_examen ?? 'Examen',
            'tipo_examen' => $examen->tipo_examen,
            'numero_preguntas' => (int) ($examen->numero_preguntas ?? 0),
            'tiempo' => (int) ($examen->tiempo ?? 60),
        ];
    }

    /**
     * Formatea las preguntas para el componente QuizRunner: cada una con sus
     * opciones (3 a 4) ya barajadas. El "valor" enviado al navegador es el ID
     * de la opción (no revela cuál es la correcta, a diferencia del esquema
     * anterior que mandaba literalmente 'correcta'/'incorrecta1').
     */
    private function formatearPreguntasQuiz($preguntas): array
    {
        $coleccion = collect($preguntas)->values();
        $conOpciones = \App\Models\Pregunta::with('opciones')
            ->whereIn('id', $coleccion->pluck('id'))
            ->get()
            ->keyBy('id');
        $coleccion = $coleccion->map(fn ($p) => $conOpciones->get($p->id, $p));

        return $coleccion->map(function ($p) {
            $opciones = $p->opciones->map(fn ($o) => ['texto' => $o->texto, 'valor' => $o->id])->all();
            shuffle($opciones);

            return [
                'id' => $p->id,
                'pregunta' => $p->pregunta,
                'opciones' => $opciones,
            ];
        })->all();
    }

    // ==================== MERCADO PAGO (INTEGRACIÓN COMPLETA) ====================

    /**
     * Crear preferencia de pago con Mercado Pago
     */
    public function crearPreferenciaMercadoPago(Request $request)
    {
        try {
            Log::info('=== INICIANDO CREACIÓN DE PREFERENCIA MP ===');
            
            $estudiante = Estudiante::where('usuario', Auth::id())->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estudiante no encontrado'
                ], 404);
            }
            
            // Calcular precio final
            $precioOriginal = self::PRECIO_CURSO;
            $precioFinal = $precioOriginal;
            $cuponAplicado = session('cupon_aplicado');
            
            if ($cuponAplicado) {
                $cupon = Cupon::where('codigo', $cuponAplicado)->first();
                if ($cupon && !$cupon->usado && $cupon->estatus === 'activo') {
                    $expirado = $cupon->fecha_expiracion && Carbon::now()->greaterThan($cupon->fecha_expiracion);
                    if (!$expirado) {
                        if ($cupon->tipo_descuento === 'porcentaje') {
                            $precioFinal = $precioOriginal - (($precioOriginal * $cupon->valor_descuento) / 100);
                        } else {
                            $precioFinal = max(0, $precioOriginal - $cupon->valor_descuento);
                        }
                    }
                }
            }
            
            $accessToken = config('mercadopago.access_token');
            
            if (!$accessToken || $accessToken === '') {
                Log::error('Access Token no configurado');
                return response()->json([
                    'success' => false,
                    'message' => 'Token de Mercado Pago no configurado'
                ], 500);
            }
            
            // Obtener la URL base completa
            $baseUrl = url('/');
            
            // Construir URLs absolutas
            $successUrl = $baseUrl . route('estudiante.pago.mercadopago.success', [], false);
            $failureUrl = $baseUrl . route('estudiante.pago.mercadopago.failure', [], false);
            $pendingUrl = $baseUrl . route('estudiante.pago.mercadopago.pending', [], false);
            
            Log::info('URLs de retorno:', [
                'success' => $successUrl,
                'failure' => $failureUrl,
                'pending' => $pendingUrl
            ]);
            
            // Crear la preferencia
            $client = new \GuzzleHttp\Client();
            
            $data = [
                'items' => [
                    [
                        'id' => 'curso_sains_2026',
                        'title' => 'Curso Premium SAINS 2026',
                        'description' => 'Acceso completo al curso premium de preparación para examen de admisión',
                        'quantity' => 1,
                        'currency_id' => 'MXN',
                        'unit_price' => round($precioFinal, 2)
                    ]
                ],
                'payer' => [
                    'email' => Auth::user()->correo,
                    'name' => $estudiante->nombre ?? 'Estudiante',
                    'surname' => $estudiante->paterno ?? 'SAINS',
                ],
                'back_urls' => [
                    'success' => $successUrl,
                    'failure' => $failureUrl,
                    'pending' => $pendingUrl
                ],
                'auto_return' => 'approved',
                'notification_url' => $baseUrl . route('estudiante.pago.mercadopago.webhook', [], false),
                'payment_methods' => [
                    'excluded_payment_types' => [
                        ['id' => 'ticket'],
                        ['id' => 'atm']
                    ],
                    'installments' => 12
                ],
                'external_reference' => 'curso_' . $estudiante->id . '_' . time(),
                'metadata' => [
                    'estudiante_id' => $estudiante->id,
                    'cupon' => $cuponAplicado,
                    'monto' => round($precioFinal, 2),
                ],
            ];
            
            Log::info('Enviando a MP:', $data);
            
            $response = $client->post('https://api.mercadopago.com/checkout/preferences', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json'
                ],
                'json' => $data
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            Log::info('Respuesta MP:', $result);
            
            if (isset($result['id'])) {
                // Guardar en sesión
                session(['pago_en_proceso' => [
                    'estudiante_id' => $estudiante->id,
                    'monto' => round($precioFinal, 2),
                    'cupon' => $cuponAplicado,
                    'preference_id' => $result['id'],
                    'fecha_inicio' => now()->toDateTimeString()
                ]]);
                
                return response()->json([
                    'success' => true,
                    'preference_id' => $result['id'],
                    'init_point' => $result['init_point']
                ]);
            }
            
            $errorMsg = $result['message'] ?? 'Error desconocido';
            Log::error('Error MP:', $result);
            
            return response()->json([
                'success' => false,
                'message' => $errorMsg
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Error en crearPreferenciaMercadoPago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Retorno de éxito de Mercado Pago (lo abre el navegador del estudiante).
     * Verifica el pago contra la API y delega en el helper idempotente, de modo
     * que da igual si llega primero este retorno o el webhook.
     */
    public function pagoExitoMercadoPago(Request $request)
    {
        try {
            // MP puede mandar el id como payment_id o collection_id
            $paymentId = $request->get('payment_id') ?: $request->get('collection_id');

            Log::info('Retorno éxito MP:', $request->all());

            if (!$paymentId) {
                // Sin id de pago no podemos verificar; mándalo a su pago pendiente.
                return redirect()->route('estudiante.checkout-pendiente')
                    ->with('warning', 'No recibimos la confirmación del pago. Si ya pagaste, se activará en unos minutos.');
            }

            $accessToken = config('mercadopago.access_token');
            $response = Http::withToken($accessToken)
                ->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

            if (!$response->successful()) {
                return redirect()->route('estudiante.checkout-pendiente')
                    ->with('warning', 'Estamos verificando tu pago con Mercado Pago. Se activará en cuanto se confirme.');
            }

            $payment = $response->json();
            $status = $payment['status'] ?? 'unknown';

            if ($status === 'approved') {
                $pago = $this->procesarPagoAprobadoMercadoPago($payment);
                session()->forget(['pago_en_proceso', 'cupon_aplicado']);

                if ($pago) {
                    return redirect()->route('estudiante.pago-exito', $pago->id)
                        ->with('success', '¡Pago completado! Ya tienes acceso al Curso Premium.');
                }

                return redirect()->route('estudiante.mis-pagos')
                    ->with('success', '¡Pago aprobado! Tu plan se está activando.');
            }

            if (in_array($status, ['in_process', 'pending', 'authorized'])) {
                return redirect()->route('estudiante.checkout-pendiente')
                    ->with('info', 'Tu pago está siendo procesado por Mercado Pago. Recibirás una notificación cuando se apruebe.');
            }

            Log::warning('Pago MP no aprobado', ['status' => $status, 'payment_id' => $paymentId]);
            return redirect()->route('estudiante.checkout')
                ->with('error', 'El pago no se completó (estado: ' . $status . '). Puedes intentarlo de nuevo.');

        } catch (\Exception $e) {
            Log::error('Error en pagoExitoMercadoPago: ' . $e->getMessage());
            return redirect()->route('estudiante.checkout-pendiente')
                ->with('warning', 'Hubo un problema al confirmar el pago. Si ya pagaste, contáctanos con tu comprobante.');
        }
    }

    /**
     * Pago fallido - Mercado Pago
     */
    public function pagoFallidoMercadoPago(Request $request)
    {
        Log::info('Pago fallido MP:', $request->all());
        
        $errorMessage = $request->get('message', 'El pago no pudo ser procesado.');
        
        return redirect()->route('estudiante.checkout')
            ->with('error', 'Pago fallido: ' . $errorMessage . ' Por favor intenta nuevamente con otra tarjeta.');
    }

    /**
     * Pago pendiente - Mercado Pago
     */
    public function pagoPendienteMercadoPago(Request $request)
    {
        Log::info('Pago pendiente MP:', $request->all());

        $estudiante = Estudiante::where('usuario', Auth::id())->first();
        if ($estudiante) {
            Notificacion::enviar($estudiante->usuario, [
                'tipo' => 'pago_pendiente',
                'titulo' => 'Pago en proceso',
                'mensaje' => 'Mercado Pago está procesando tu pago. Te avisaremos en cuanto se apruebe.',
                'url' => route('estudiante.checkout-pendiente'),
                'icono' => 'clock', 'color' => 'amber',
            ]);
        }

        return redirect()->route('estudiante.checkout-pendiente')
            ->with('info', 'Tu pago está siendo procesado por Mercado Pago. Recibirás una notificación cuando se apruebe.');
    }

    /**
     * Activa el plan del estudiante a partir de un pago aprobado de Mercado Pago.
     * Es idempotente: si el pago ya fue registrado (por el redirect de éxito o por
     * un webhook anterior) no vuelve a crear el registro ni a re-activar el plan.
     *
     * @param  array  $payment  Objeto de pago devuelto por la API de Mercado Pago.
     * @return \App\Models\Pago|null
     */
    private function procesarPagoAprobadoMercadoPago(array $payment)
    {
        $paymentId = (string) ($payment['id'] ?? '');

        if ($paymentId === '') {
            Log::warning('procesarPagoAprobadoMercadoPago: payment sin id');
            return null;
        }

        // Idempotencia: ¿ya está registrado este pago?
        $pagoExistente = Pago::where('referencia_pago', $paymentId)->first();
        if ($pagoExistente) {
            return $pagoExistente;
        }

        // Resolver el estudiante desde external_reference: "curso_{id}_{timestamp}"
        $externalReference = $payment['external_reference'] ?? '';
        $estudianteId = null;
        if (preg_match('/curso_(\d+)_/', $externalReference, $m)) {
            $estudianteId = (int) $m[1];
        }

        $estudiante = $estudianteId ? Estudiante::find($estudianteId) : null;
        if (!$estudiante) {
            Log::error('procesarPagoAprobadoMercadoPago: estudiante no encontrado', [
                'external_reference' => $externalReference,
                'payment_id' => $paymentId,
            ]);
            return null;
        }

        $monto = $payment['transaction_amount'] ?? self::PRECIO_CURSO;

        return DB::transaction(function () use ($estudiante, $payment, $paymentId, $monto) {
            // Segunda comprobación dentro de la transacción para evitar carreras
            // entre el redirect de éxito y el webhook.
            $pago = Pago::where('referencia_pago', $paymentId)->lockForUpdate()->first();
            if ($pago) {
                return $pago;
            }

            $pago = Pago::create([
                'alumno_pago' => $estudiante->id,
                'tipo_pago' => 'mercadopago',
                'monto_pago' => round($monto, 2),
                'estatus' => 'completado',
                'referencia_pago' => $paymentId,
                'fecha_pago' => now(),
                'nota_usuario' => "Pago aprobado vía Mercado Pago\nID Transacción: {$paymentId}",
            ]);

            if (!$estudiante->plan_activo) {
                $estudiante->plan_activo = true;
                $estudiante->save();
            }

            $codigoCupon = $payment['metadata']['cupon'] ?? $estudiante->cupon;
            if ($codigoCupon) {
                $cupon = Cupon::where('codigo', $codigoCupon)->first();
                if ($cupon && !$cupon->usado) {
                    $cupon->update([
                        'usado' => true,
                        'usuario_uso' => $estudiante->usuario,
                        'fecha_uso' => now(),
                    ]);
                    if (empty($estudiante->cupon)) {
                        $estudiante->update(['cupon' => $codigoCupon]);
                    }
                }
            }

            Notificacion::enviar($estudiante->usuario, [
                'tipo' => 'pago_aprobado',
                'titulo' => '¡Pago aprobado con Mercado Pago!',
                'mensaje' => 'Tu acceso al Curso Premium SAINS ya está activo. ¡A estudiar!',
                'url' => route('estudiante.clases-premium'),
                'icono' => 'check', 'color' => 'green',
            ]);
            Notificacion::enviarAdmins([
                'tipo' => 'pago_mercadopago',
                'titulo' => 'Pago con Mercado Pago recibido',
                'mensaje' => "{$estudiante->nombre_completo} pagó $" . number_format($pago->monto_pago, 2) . " MXN con Mercado Pago. Plan activado automáticamente.",
                'url' => route('admin.pagos.show', $pago->id),
                'icono' => 'dollar', 'color' => 'green',
            ]);

            Log::info('procesarPagoAprobadoMercadoPago: plan activado', [
                'estudiante_id' => $estudiante->id,
                'pago_id' => $pago->id,
            ]);

            return $pago;
        });
    }

    /**
     * Webhook - Notificaciones de Mercado Pago
     */
    public function webhookMercadoPago(Request $request)
    {
        try {
            $data = $request->all();
            Log::info('Webhook MP recibido:', $data);
            
            // Procesar notificación de pago
            if (isset($data['type']) && $data['type'] == 'payment') {
                $payment_id = $data['data']['id'];
                
                // Consultar información del pago
                $accessToken = config('mercadopago.access_token');
                $response = Http::withToken($accessToken)
                    ->get("https://api.mercadopago.com/v1/payments/{$payment_id}");
                
                if ($response->successful()) {
                    $payment = $response->json();
                    Log::info('Webhook - Detalle del pago:', ['payment' => $payment]);
                    
                    // Aquí puedes actualizar el estado del pago en tu BD si es necesario
                    // Por ejemplo, si el pago está aprobado y por alguna razón no se procesó antes
                    if ($payment['status'] === 'approved') {
                        $this->procesarPagoAprobadoMercadoPago($payment);
                    }
                }
            }
            
            return response()->json(['status' => 'ok'], 200);
            
        } catch (\Exception $e) {
            Log::error('Error en webhook MP: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }

    // ========== ESTADÍSTICAS Y PROGRESO ==========
    
    public function getProgresoApi()
    {
        try {
            $user = Auth::user();
            $estudiante = Estudiante::where('usuario', $user->id)->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => true,
                    'total_examenes' => 0,
                    'lecciones_vistas' => 0,
                    'mejor_puntaje' => 0,
                    'horas_estudio' => 0,
                    'aprobados' => 0,
                    'reprobados' => 0,
                    'progreso' => 0,
                    'promedio' => 0
                ]);
            }
            
            $examenesRealizados = ExamenRealizado::where('estudiante', $estudiante->id)->get();
            $totalExamenes = $examenesRealizados->count();
            $mejorPuntaje = $examenesRealizados->max('calificacion') ?? 0;
            $aprobados = $examenesRealizados->where('calificacion', '>=', 70)->count();
            $reprobados = $examenesRealizados->where('calificacion', '<', 70)->count();
            $promedio = $totalExamenes > 0 ? round($examenesRealizados->avg('calificacion'), 1) : 0;
            $progreso = $totalExamenes > 0 ? min(100, round(($totalExamenes / 20) * 100)) : 0;
            
            $leccionesVistas = ProgresoVideo::where('estudiante_id', $estudiante->id)
                ->where('completado', true)
                ->count();
            
            $tiempoEstudio = TiempoEstudio::where('estudiante_id', $estudiante->id)->sum('segundos_estudiados');
            $horasEstudio = round($tiempoEstudio / 3600, 1);
            
            return response()->json([
                'success' => true,
                'total_examenes' => $totalExamenes,
                'lecciones_vistas' => $leccionesVistas,
                'mejor_puntaje' => $mejorPuntaje,
                'horas_estudio' => $horasEstudio,
                'aprobados' => $aprobados,
                'reprobados' => $reprobados,
                'progreso' => $progreso,
                'promedio' => $promedio
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en getProgresoApi: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }

    public function getEstadisticas()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }
            
            $estudiante = Estudiante::where('usuario', $user->id)->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => true,
                    'total_examenes' => 0,
                    'lecciones_vistas' => 0,
                    'mejor_puntaje' => 0,
                    'horas_estudio' => 0,
                    'aprobados' => 0,
                    'reprobados' => 0,
                    'progreso' => 0,
                    'promedio' => 0
                ]);
            }
            
            $examenesRealizados = ExamenRealizado::where('estudiante', $estudiante->id)->get();
            $totalExamenes = $examenesRealizados->count();
            $mejorPuntaje = $examenesRealizados->max('calificacion') ?? 0;
            $aprobados = $examenesRealizados->where('calificacion', '>=', 70)->count();
            $reprobados = $examenesRealizados->where('calificacion', '<', 70)->count();
            $promedio = $totalExamenes > 0 ? round($examenesRealizados->avg('calificacion'), 1) : 0;
            $progreso = $totalExamenes > 0 ? min(100, round(($totalExamenes / 20) * 100)) : 0;
            
            $leccionesVistas = ProgresoVideo::where('estudiante_id', $estudiante->id)
                ->where('completado', true)
                ->count();
            
            $tiempoEstudio = TiempoEstudio::where('estudiante_id', $estudiante->id)->sum('segundos_estudiados');
            $horasEstudio = round($tiempoEstudio / 3600, 1);
            
            return response()->json([
                'success' => true,
                'total_examenes' => $totalExamenes,
                'lecciones_vistas' => $leccionesVistas,
                'mejor_puntaje' => $mejorPuntaje,
                'horas_estudio' => $horasEstudio,
                'aprobados' => $aprobados,
                'reprobados' => $reprobados,
                'progreso' => $progreso,
                'promedio' => $promedio
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en getEstadisticas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }

    public function getUltimosExamenes()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'examenes' => []]);
            }
            
            $estudiante = Estudiante::where('usuario', $user->id)->first();
            
            if (!$estudiante) {
                return response()->json(['success' => true, 'examenes' => []]);
            }
            
            $examenes = ExamenRealizado::where('estudiante', $estudiante->id)
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get();
            
            $examenesFormateados = $examenes->map(function($examen) {
                $fecha = $examen->fecha_inicio;
                if (!$fecha || $fecha == '0000-00-00' || $fecha == '1970-01-01') {
                    $fecha = date('Y-m-d');
                }
                
                return [
                    'id' => $examen->id,
                    'fecha' => Carbon::parse($fecha)->format('d/m/Y'),
                    'calificacion' => round($examen->calificacion ?? 0, 1)
                ];
            });
            
            return response()->json([
                'success' => true,
                'examenes' => $examenesFormateados
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en getUltimosExamenes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'examenes' => []
            ], 500);
        }
    }

    public function getHistorialExamenes()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'examenes' => []]);
            }
            
            $estudiante = Estudiante::where('usuario', $user->id)->first();
            
            if (!$estudiante) {
                return response()->json(['success' => true, 'examenes' => []]);
            }
            
            $examenes = ExamenRealizado::where('estudiante', $estudiante->id)
                ->orderBy('id', 'desc')
                ->get();
            
            $examenesFormateados = $examenes->map(function($examen) {
                $examenGenerado = ExamenGenerado::find($examen->examen);
                $tipoExamen = $examenGenerado ? $examenGenerado->tipo_examen : 'Simulador';
                
                $fecha = $examen->fecha_inicio;
                $fechaFormateada = '';
                
                if ($fecha && $fecha !== '0000-00-00' && $fecha !== '1970-01-01') {
                    try {
                        $fechaFormateada = Carbon::parse($fecha)->format('d/m/Y');
                    } catch (\Exception $e) {
                        $fechaFormateada = date('d/m/Y');
                    }
                } else {
                    $fechaFormateada = date('d/m/Y');
                }
                
                return [
                    'id' => $examen->id,
                    'examen_id' => $examen->examen,
                    'fecha' => $fechaFormateada,
                    'calificacion' => round($examen->calificacion ?? 0, 2),
                    'intento' => $examen->intento ?? 1,
                    'tipo_examen' => $tipoExamen,
                    'aprobado' => ($examen->calificacion ?? 0) >= 70
                ];
            });
            
            return response()->json([
                'success' => true,
                'examenes' => $examenesFormateados
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en getHistorialExamenes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'examenes' => []
            ], 500);
        }
    }

    // ========== TIEMPO DE ESTUDIO ==========
    
    public function heartbeat(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }
            
            $estudiante = Estudiante::where('usuario', $user->id)->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estudiante no encontrado'
                ]);
            }
            
            $tiempo = TiempoEstudio::agregarTiempo($estudiante->id, 60);
            
            return response()->json([
                'success' => true,
                'message' => 'Actividad registrada',
                'plan_activo' => $estudiante->plan_activo,
                'tiempo_hoy' => [
                    'minutos' => $tiempo->minutos_estudiados ?? 0,
                    'horas' => $tiempo->horas_estudiadas ?? 0
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en heartbeat: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar actividad'
            ], 500);
        }
    }

    public function getTiempoEstudio()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }
            
            $estudiante = Estudiante::where('usuario', $user->id)->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => true,
                    'hoy' => ['segundos' => 0, 'minutos' => 0, 'horas' => 0],
                    'total' => ['segundos' => 0, 'minutos' => 0, 'horas' => 0]
                ]);
            }
            
            $hoy = TiempoEstudio::getTiempoHoy($estudiante->id);
            $totalSegundos = TiempoEstudio::where('estudiante_id', $estudiante->id)->sum('segundos_estudiados');
            
            return response()->json([
                'success' => true,
                'hoy' => [
                    'segundos' => $hoy['segundos'],
                    'minutos' => $hoy['minutos'],
                    'horas' => $hoy['horas']
                ],
                'total' => [
                    'segundos' => $totalSegundos,
                    'minutos' => round($totalSegundos / 60),
                    'horas' => round($totalSegundos / 3600, 1)
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en getTiempoEstudio: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tiempo de estudio'
            ], 500);
        }
    }

    // ========== EXÁMENES ==========
    
    public function simulador(Request $request = null, $examenId = null)
    {
        if (is_numeric($request) && $examenId === null) {
            $examenId = $request;
            $request = null;
        }
        
        $user = Auth::user();
        $estudiante = Estudiante::where('usuario', $user->id)->first();
        
        if (!$estudiante) {
            return redirect()->route('estudiante.completar-perfil')->with('warning', 'Primero completa tu perfil');
        }
        
        if (!$estudiante->plan_activo) {
            return $this->simuladorBasico($estudiante, $examenId);
        }
        
        return $this->simuladorPremium($estudiante, $examenId);
    }

    private function simuladorPremium($estudiante, $examenId = null)
    {
        $examenes = ExamenGenerado::where('tipo_examen', 'Simulación')
            ->orWhere('tipo_examen', 'simulacion')
            ->orWhere('tipo_examen', 'Simulador')
            ->orWhere('tipo_examen', 'simulador')
            ->get();
        
        if ($examenes->isEmpty()) {
            return redirect()->route('estudiante.dashboard')->with('error', 'No hay simuladores disponibles');
        }
        
        $examen = $examenId ? $examenes->find($examenId) : $examenes->first();
        
        if (!$examen) {
            return redirect()->route('estudiante.simulador')->with('error', 'Examen no encontrado');
        }
        
        $preguntas = $examen->preguntas()->get();
        
        if ($preguntas->isEmpty()) {
            return redirect()->route('estudiante.simulador')->with('error', 'El simulador no tiene preguntas configuradas');
        }
        
        $intentosRealizados = ExamenRealizado::where('estudiante', $estudiante->id)
            ->where('examen', $examen->id)
            ->count();

        return \Inertia\Inertia::render('Estudiante/Simulador', [
            'examen' => $this->datosExamen($examen),
            'preguntas' => $this->formatearPreguntasQuiz($preguntas),
            'intento' => $intentosRealizados + 1,
            'planActivo' => true,
            'intentosRestantes' => null,
            'maxPreguntas' => null,
            'examenes' => $examenes->map(fn ($e) => $this->datosExamen($e))->values(),
            'responderUrl' => route('estudiante.simulador.responder'),
        ]);
    }

    private function simuladorBasico($estudiante, $examenId = null)
    {
        $examenes = ExamenGenerado::where('tipo_examen', 'Simulación')
            ->orWhere('tipo_examen', 'simulacion')
            ->orWhere('tipo_examen', 'Simulador')
            ->orWhere('tipo_examen', 'simulador')
            ->get();
        
        if ($examenes->isEmpty()) {
            return redirect()->route('estudiante.dashboard')->with('error', 'No hay simuladores disponibles');
        }
        
        $examen = $examenId ? $examenes->find($examenId) : $examenes->first();
        
        if (!$examen) {
            return redirect()->route('estudiante.simulador')->with('error', 'Examen no encontrado');
        }
        
        $intentosRealizados = ExamenRealizado::where('estudiante', $estudiante->id)
            ->where('examen', $examen->id)
            ->count();
        
        $intentosRestantes = max(0, self::MAX_INTENTOS_BASICO - $intentosRealizados);
        
        if ($intentosRealizados >= self::MAX_INTENTOS_BASICO) {
            return \Inertia\Inertia::render('Estudiante/Simulador', [
                'examen' => $this->datosExamen($examen),
                'preguntas' => [],
                'intento' => $intentosRealizados,
                'planActivo' => false,
                'intentosRestantes' => 0,
                'maxPreguntas' => self::MAX_PREGUNTAS_BASICO,
                'maxIntentos' => self::MAX_INTENTOS_BASICO,
                'limiteAlcanzado' => true,
                'examenes' => $examenes->map(fn ($e) => $this->datosExamen($e))->values(),
                'responderUrl' => route('estudiante.simulador.responder'),
            ]);
        }

        $todasPreguntas = $examen->preguntas()->get();
        
        if ($todasPreguntas->isEmpty()) {
            return redirect()->route('estudiante.simulador')->with('error', 'El simulador no tiene preguntas configuradas');
        }
        
        $numPreguntas = rand(self::MIN_PREGUNTAS_BASICO, min(self::MAX_PREGUNTAS_BASICO, $todasPreguntas->count()));
        $preguntas = $todasPreguntas->random($numPreguntas);
        
        return \Inertia\Inertia::render('Estudiante/Simulador', [
            'examen' => $this->datosExamen($examen),
            'preguntas' => $this->formatearPreguntasQuiz($preguntas),
            'intento' => $intentosRealizados + 1,
            'planActivo' => false,
            'intentosRestantes' => $intentosRestantes,
            'maxPreguntas' => self::MAX_PREGUNTAS_BASICO,
            'maxIntentos' => self::MAX_INTENTOS_BASICO,
            'examenes' => $examenes->map(fn ($e) => $this->datosExamen($e))->values(),
            'responderUrl' => route('estudiante.simulador.responder'),
        ]);
    }

    public function cargarSimulador($id)
    {
        return $this->simulador(null, $id);
    }
    
    public function responderSimulador(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Usuario no autenticado'], 401);
            }
            
            $estudiante = Estudiante::where('usuario', $user->id)->first();
            
            if (!$estudiante) {
                return response()->json(['success' => false, 'message' => 'Primero completa tu perfil'], 400);
            }
            
            $examen_id = $request->examen_id;
            $respuestas = $request->respuestas;
            $tiempoUtilizadoSegundos = intval($request->tiempo_utilizado_segundos ?? 0);
            
            if (!$examen_id || !$respuestas || count($respuestas) == 0) {
                return response()->json(['success' => false, 'message' => 'Datos incompletos'], 400);
            }
            
            $examen = ExamenGenerado::find($examen_id);
            if (!$examen) {
                return response()->json(['success' => false, 'message' => 'Examen no encontrado'], 404);
            }
            
            $intentosRealizados = ExamenRealizado::where('estudiante', $estudiante->id)
                ->where('examen', $examen->id)
                ->count();
            
            if (!$estudiante->plan_activo) {
                if ($intentosRealizados >= self::MAX_INTENTOS_BASICO) {
                    return response()->json(['success' => false, 'message' => "Límite de " . self::MAX_INTENTOS_BASICO . " intentos alcanzado"], 403);
                }
                
                $preguntasRespondidas = count($respuestas);
                if ($preguntasRespondidas > self::MAX_PREGUNTAS_BASICO) {
                    return response()->json(['success' => false, 'message' => "Máximo " . self::MAX_PREGUNTAS_BASICO . " preguntas"], 403);
                }
            }
            
            if ($estudiante->plan_activo) {
                $preguntasExamen = $examen->preguntas()->get();
            } else {
                $preguntasIds = array_keys($respuestas);
                $preguntasExamen = $examen->preguntas()->whereIn('preguntas.id', $preguntasIds)->get();
            }
            
            $totalPreguntas = $preguntasExamen->count();
            $aciertos = 0;
            $respuestasGuardadas = [];
            
            foreach ($preguntasExamen as $pregunta) {
                $respuestaSeleccionada = $respuestas[$pregunta->id] ?? null;
                $opcionElegida = $this->resolverOpcionElegida($pregunta, $respuestaSeleccionada);
                $esCorrecta = (bool) ($opcionElegida?->es_correcta);

                if ($esCorrecta) $aciertos++;

                $textoRespuesta = $opcionElegida?->texto ?? 'No respondida';

                $respuestasGuardadas[] = [
                    'pregunta_id' => $pregunta->id,
                    'respuesta' => $textoRespuesta,
                    'estatus' => $esCorrecta ? 'correcta' : 'incorrecta'
                ];
            }
            
            $calificacion = $totalPreguntas > 0 ? round(($aciertos / $totalPreguntas) * 100) : 0;
            $intento = $intentosRealizados + 1;
            
            $horas = floor($tiempoUtilizadoSegundos / 3600);
            $minutos = floor(($tiempoUtilizadoSegundos % 3600) / 60);
            $segundos = $tiempoUtilizadoSegundos % 60;
            $tiempoFormateado = sprintf("%02d:%02d:%02d", $horas, $minutos, $segundos);
            
            $ahora = Carbon::now();
            $fechaActual = $ahora->toDateString();
            $horaFin = $ahora->toTimeString();
            $horaInicioCalculada = $ahora->copy()->subSeconds($tiempoUtilizadoSegundos);
            $horaInicioStr = $horaInicioCalculada->toTimeString();
            
            $examenRealizado = ExamenRealizado::create([
                'estudiante' => $estudiante->id,
                'examen' => $examen->id,
                'intento' => $intento,
                'calificacion' => $calificacion,
                'fecha_inicio' => $fechaActual,
                'hora_inicio' => $horaInicioStr,
                'fecha_fin' => $fechaActual,
                'hora_fin' => $horaFin,
                'tiempo' => $tiempoFormateado,
                'respuestas' => json_encode(['respuestas' => $respuestasGuardadas])
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Examen completado',
                'calificacion' => $calificacion,
                'aciertos' => $aciertos,
                'total' => $totalPreguntas,
                'intento' => $intento,
                'redirect' => route('estudiante.resultados', $examenRealizado->id)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en responderSimulador: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al procesar el examen'], 500);
        }
    }
    
    public function resultados($id)
    {
        $user = Auth::user();
        $estudiante = Estudiante::where('usuario', $user->id)->first();
        
        $examenRealizado = ExamenRealizado::where('id', $id)
            ->where('estudiante', $estudiante->id)
            ->with('examenGenerado')
            ->first();
        
        if (!$examenRealizado) {
            return redirect()->route('estudiante.dashboard')->with('error', 'Resultado no encontrado');
        }
        
        $respuestasData = json_decode($examenRealizado->respuestas, true);
        $respuestasLista = $respuestasData['respuestas'] ?? [];
        
        $respuestasMap = [];
        foreach ($respuestasLista as $respuestaItem) {
            $respuestasMap[$respuestaItem['pregunta_id']] = [
                'respuesta' => $respuestaItem['respuesta'],
                'estatus' => $respuestaItem['estatus']
            ];
        }
        
        $preguntasIds = array_keys($respuestasMap);
        $preguntas = Pregunta::with('opciones')->whereIn('id', $preguntasIds)->get()->keyBy('id');

        $preguntasConRespuestas = [];
        foreach ($respuestasMap as $preguntaId => $respuestaInfo) {
            $pregunta = $preguntas->get($preguntaId);

            $preguntasConRespuestas[] = (object)[
                'id' => $preguntaId,
                'texto' => $pregunta ? ($pregunta->pregunta ?? 'Pregunta sin texto') : 'Pregunta no encontrada',
                'respuesta_correcta' => $pregunta ? $pregunta->opciones->firstWhere('es_correcta', true)?->texto : null,
                'respuesta_usuario' => $respuestaInfo['respuesta'],
                'estatus' => $respuestaInfo['estatus'],
                'es_correcta' => $respuestaInfo['estatus'] === 'correcta',
                'justificacion' => $pregunta ? ($pregunta->justificacion ?? null) : null
            ];
        }
        
        $mejorCalificacion = ExamenRealizado::where('estudiante', $estudiante->id)
            ->where('examen', $examenRealizado->examen)
            ->max('calificacion') ?? 0;

        $intentosAnteriores = ExamenRealizado::where('estudiante', $estudiante->id)
            ->where('examen', $examenRealizado->examen)
            ->orderBy('intento')
            ->get(['id', 'intento', 'calificacion'])
            ->map(fn ($i) => [
                'id' => $i->id,
                'intento' => $i->intento,
                'calificacion' => round($i->calificacion ?? 0),
            ])->values();

        return \Inertia\Inertia::render('Estudiante/Resultados', [
            'examen' => [
                'id' => $examenRealizado->id,
                'calificacion' => round($examenRealizado->calificacion ?? 0),
                'intento' => $examenRealizado->intento ?? 1,
                'tiempo' => $examenRealizado->tiempo,
                'fecha_inicio' => $examenRealizado->fecha_inicio,
                'hora_inicio' => $examenRealizado->hora_inicio,
                'tipo_examen' => optional($examenRealizado->examenGenerado)->tipo_examen ?? 'Simulador',
                'nombre' => optional($examenRealizado->examenGenerado)->nombre,
            ],
            'mejorCalificacion' => round($mejorCalificacion),
            'intentos' => $intentosAnteriores,
            'preguntas' => collect($preguntasConRespuestas)->map(fn ($p) => [
                'id' => $p->id,
                'texto' => $p->texto,
                'respuesta_correcta' => $p->respuesta_correcta,
                'respuesta_usuario' => $p->respuesta_usuario,
                'es_correcta' => $p->es_correcta,
                'justificacion' => $p->justificacion,
            ])->values(),
        ]);
    }

    // ========== MÉTODOS AUXILIARES ==========
    
    /**
     * Resuelve la opción de respuesta que el estudiante eligió para una pregunta,
     * a partir del ID de opción enviado por el navegador. Verifica que esa opción
     * realmente pertenezca a la pregunta (evita que se mande el ID de una opción
     * de otra pregunta para forzar un acierto).
     */
    private function resolverOpcionElegida($pregunta, $idOpcionSeleccionada): ?\App\Models\PreguntaOpcion
    {
        if (!$idOpcionSeleccionada) {
            return null;
        }

        return \App\Models\PreguntaOpcion::where('id', $idOpcionSeleccionada)
            ->where('pregunta_id', $pregunta->id)
            ->first();
    }

    public function getRecomendacionesUniversidades(){
        try {
            $user = Auth::user();
            $estudiante = Estudiante::where('usuario', $user->id)->first();
            
            if (!$estudiante) {
                return response()->json(['success' => false, 'message' => 'Estudiante no encontrado']);
            }
            
            $examenesRealizados = ExamenRealizado::where('estudiante', $estudiante->id)->get();
            $promedio = $examenesRealizados->avg('calificacion') ?? 0;
            $mejorCalificacion = $examenesRealizados->max('calificacion') ?? 0;
            
            $universidadInteres = null;
            $carreraInteres = null;
            
            if ($estudiante->universidad_interes) {
                $universidadInteres = Universidad::with('carrera')->find($estudiante->universidad_interes);
                if ($universidadInteres && $universidadInteres->carrera) {
                    $carreraInteres = $universidadInteres->carrera;
                }
            }
            
            $progresoCarrera = null;
            if ($carreraInteres) {
                $calificacionMinima = $carreraInteres->calificacion_minima ?? 0;
                $diferenciaFaltante = $calificacionMinima > 0 ? max(0, $calificacionMinima - $promedio) : 0;
                $porcentajeProgreso = $calificacionMinima > 0 ? min(100, round(($promedio / $calificacionMinima) * 100)) : 100;
                
                $estado = $calificacionMinima == 0 ? 'sin_requisito' : ($promedio >= $calificacionMinima ? 'cumple' : ($promedio >= $calificacionMinima * 0.7 ? 'cerca' : 'lejos'));
                
                $progresoCarrera = [
                    'id' => $carreraInteres->id,
                    'nombre' => $carreraInteres->nombre,
                    'calificacion_minima' => $calificacionMinima,
                    'calificacion_minima_formateada' => ($calificacionMinima > 0) ? $calificacionMinima . '%' : 'No definido',
                    'estado' => $estado,
                    'diferencia_faltante' => $diferenciaFaltante,
                    'porcentaje_progreso' => $porcentajeProgreso
                ];
            }
            
            return response()->json([
                'success' => true,
                'promedio' => round($promedio, 1),
                'mejor_calificacion' => round($mejorCalificacion, 1),
                'total_examenes' => $examenesRealizados->count(),
                'tiene_universidad_interes' => !is_null($estudiante->universidad_interes),
                'carrera_interes' => $progresoCarrera
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en getRecomendacionesUniversidades: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener recomendaciones'], 500);
        }
    }

    // ========== MÉTODOS PARA EXÁMENES DE MATERIA ==========

    /**
     * Muestra el formulario del examen de materia
     */
    public function examenMateria($examenId)
    {
        $user = Auth::user();
        $estudiante = Estudiante::where('usuario', $user->id)->first();
        
        if (!$estudiante || !$estudiante->plan_activo) {
            return redirect()->route('estudiante.dashboard')->with('error', 'Necesitas un plan activo para acceder al examen');
        }
        
        $examen = ExamenGenerado::where('id', $examenId)
                            ->where(function($query) {
                                $query->where('tipo_examen', 'Materia')
                                      ->orWhere('tipo_examen', 'materia');
                            })
                            ->first();
        
        if (!$examen) {
            return redirect()->route('estudiante.clases-premium')->with('error', 'Examen de materia no encontrado');
        }
        
        $preguntas = $examen->preguntas()->get();
        
        if ($preguntas->isEmpty()) {
            $preguntas = DB::table('preguntas')
                ->join('apoyo_preguntas', 'preguntas.id', '=', 'apoyo_preguntas.pregunta')
                ->where('apoyo_preguntas.examen', $examen->id)
                ->select('preguntas.*')
                ->get();
        }
        
        if ($preguntas->isEmpty()) {
            return redirect()->route('estudiante.clases-premium')->with('error', 'Este examen no tiene preguntas configuradas');
        }
        
        $asignaturaNombre = 'Materia';
        if ($preguntas->isNotEmpty()) {
            $primeraPregunta = $preguntas->first();
            if (is_object($primeraPregunta) && isset($primeraPregunta->id_area)) {
                $area = AreaPregunta::find($primeraPregunta->id_area);
                if ($area) {
                    $asignaturaNombre = $area->nombre;
                }
            }
        }
        
        $intentosRealizados = ExamenRealizado::where('estudiante', $estudiante->id)
                                        ->where('examen', $examen->id)
                                        ->count();
        $intento = $intentosRealizados + 1;
        $mejorCalificacion = ExamenRealizado::where('estudiante', $estudiante->id)
                                        ->where('examen', $examen->id)
                                        ->max('calificacion');

        return \Inertia\Inertia::render('Estudiante/ExamenMateria', [
            'examen' => $this->datosExamen($examen),
            'preguntas' => $this->formatearPreguntasQuiz($preguntas),
            'intento' => $intento,
            'mejorCalificacion' => $mejorCalificacion !== null ? round($mejorCalificacion) : null,
            'asignaturaNombre' => $asignaturaNombre,
            'responderUrl' => route('estudiante.responder.examen.materia'),
        ]);
    }

    /**
     * Procesa las respuestas del examen de materia
     */
    public function responderExamenMateria(Request $request)
    {
        try {
            Log::info('========== INICIANDO RESPONDER EXAMEN MATERIA ==========');
            
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }
            
            $estudiante = Estudiante::where('usuario', $user->id)->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Primero completa tu perfil'
                ], 400);
            }
            
            if (!$estudiante->plan_activo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Necesitas un plan activo para realizar el examen'
                ], 403);
            }
            
            $examen_id = $request->examen_id;
            $respuestas = $request->respuestas;
            $tiempoUtilizadoSegundos = intval($request->tiempo_utilizado_segundos ?? 0);
            
            if (!$examen_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se especificó el examen'
                ], 400);
            }
            
            if (!$respuestas || count($respuestas) == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se recibieron respuestas'
                ], 400);
            }
            
            $examen = ExamenGenerado::find($examen_id);
            if (!$examen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Examen no encontrado'
                ], 404);
            }
            
            $preguntasExamen = $examen->preguntas()->get();
            if ($preguntasExamen->isEmpty()) {
                $preguntasExamen = DB::table('preguntas')
                    ->join('apoyo_preguntas', 'preguntas.id', '=', 'apoyo_preguntas.pregunta')
                    ->where('apoyo_preguntas.examen', $examen->id)
                    ->select('preguntas.*')
                    ->get();
            }
            
            $totalPreguntas = $preguntasExamen->count();
            $aciertos = 0;
            $respuestasGuardadas = [];
            
            foreach ($preguntasExamen as $pregunta) {
                $respuestaSeleccionada = $respuestas[$pregunta->id] ?? null;
                $opcionElegida = $this->resolverOpcionElegida($pregunta, $respuestaSeleccionada);
                $esCorrecta = (bool) ($opcionElegida?->es_correcta);

                if ($esCorrecta) {
                    $aciertos++;
                }

                $textoRespuesta = $opcionElegida?->texto ?? 'No respondida';

                $respuestasGuardadas[] = [
                    'pregunta_id' => $pregunta->id,
                    'respuesta' => $textoRespuesta,
                    'estatus' => $esCorrecta ? 'correcta' : 'incorrecta'
                ];
            }
            
            $calificacion = $totalPreguntas > 0 ? round(($aciertos / $totalPreguntas) * 100) : 0;
            
            $intento = ExamenRealizado::where('estudiante', $estudiante->id)
                                    ->where('examen', $examen->id)
                                    ->count() + 1;
            
            $horas = floor($tiempoUtilizadoSegundos / 3600);
            $minutos = floor(($tiempoUtilizadoSegundos % 3600) / 60);
            $segundos = $tiempoUtilizadoSegundos % 60;
            $tiempoFormateado = sprintf("%02d:%02d:%02d", $horas, $minutos, $segundos);
            
            $ahora = Carbon::now();
            $fechaActual = $ahora->toDateString();
            $horaFin = $ahora->toTimeString();
            $horaInicioCalculada = $ahora->copy()->subSeconds($tiempoUtilizadoSegundos);
            $horaInicioStr = $horaInicioCalculada->toTimeString();
            
            $examenRealizado = ExamenRealizado::create([
                'estudiante' => $estudiante->id,
                'examen' => $examen->id,
                'intento' => $intento,
                'calificacion' => $calificacion,
                'fecha_inicio' => $fechaActual,
                'hora_inicio' => $horaInicioStr,
                'fecha_fin' => $fechaActual,
                'hora_fin' => $horaFin,
                'tiempo' => $tiempoFormateado,
                'respuestas' => json_encode([
                    'respuestas' => $respuestasGuardadas
                ])
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Examen completado exitosamente',
                'calificacion' => $calificacion,
                'aciertos' => $aciertos,
                'total' => $totalPreguntas,
                'intento' => $intento,
                'tiempo_utilizado' => $tiempoFormateado,
                'redirect' => route('estudiante.resultados', $examenRealizado->id)
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ Error en responderExamenMateria: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el examen: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== MÉTODOS PARA EXÁMENES FINALES DEL CURSO ==========

    /**
     * Muestra el formulario del examen final del curso
     */
    public function examenCurso($examenId)
    {
        $user = Auth::user();
        $estudiante = Estudiante::where('usuario', $user->id)->first();
        
        if (!$estudiante || !$estudiante->plan_activo) {
            return redirect()->route('estudiante.dashboard')->with('error', 'Necesitas un plan activo para acceder al examen final');
        }
        
        $examen = ExamenGenerado::where('id', $examenId)
                            ->where(function($query) {
                                $query->where('tipo_examen', 'Curso')
                                      ->orWhere('tipo_examen', 'curso');
                            })
                            ->first();
        
        if (!$examen) {
            return redirect()->route('estudiante.clases-premium')->with('error', 'Examen final no encontrado');
        }
        
        $preguntas = $examen->preguntas()->get();
        
        if ($preguntas->isEmpty()) {
            $preguntas = DB::table('preguntas')
                ->join('apoyo_preguntas', 'preguntas.id', '=', 'apoyo_preguntas.pregunta')
                ->where('apoyo_preguntas.examen', $examen->id)
                ->select('preguntas.*')
                ->get();
        }
        
        if ($preguntas->isEmpty()) {
            return redirect()->route('estudiante.clases-premium')->with('error', 'Este examen no tiene preguntas configuradas');
        }
        
        $intentosRealizados = ExamenRealizado::where('estudiante', $estudiante->id)
                                        ->where('examen', $examen->id)
                                        ->count();
        $intento = $intentosRealizados + 1;
        $mejorCalificacion = ExamenRealizado::where('estudiante', $estudiante->id)
                                        ->where('examen', $examen->id)
                                        ->max('calificacion');

        return \Inertia\Inertia::render('Estudiante/ExamenCurso', [
            'examen' => $this->datosExamen($examen),
            'preguntas' => $this->formatearPreguntasQuiz($preguntas),
            'intento' => $intento,
            'mejorCalificacion' => $mejorCalificacion !== null ? round($mejorCalificacion) : null,
            'responderUrl' => route('estudiante.responder.examen-curso'),
        ]);
    }

    /**
     * Procesa las respuestas del examen final del curso
     */
    public function responderExamenCurso(Request $request)
    {
        try {
            Log::info('========== INICIANDO RESPONDER EXAMEN CURSO ==========');
            
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }
            
            $estudiante = Estudiante::where('usuario', $user->id)->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Primero completa tu perfil'
                ], 400);
            }
            
            if (!$estudiante->plan_activo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Necesitas un plan activo para realizar el examen final'
                ], 403);
            }
            
            $examen_id = $request->examen_id;
            $respuestas = $request->respuestas;
            $tiempoUtilizadoSegundos = intval($request->tiempo_utilizado_segundos ?? 0);
            
            if (!$examen_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se especificó el examen'
                ], 400);
            }
            
            if (!$respuestas || count($respuestas) == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se recibieron respuestas'
                ], 400);
            }
            
            $examen = ExamenGenerado::find($examen_id);
            if (!$examen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Examen no encontrado'
                ], 404);
            }
            
            $preguntasExamen = $examen->preguntas()->get();
            if ($preguntasExamen->isEmpty()) {
                $preguntasExamen = DB::table('preguntas')
                    ->join('apoyo_preguntas', 'preguntas.id', '=', 'apoyo_preguntas.pregunta')
                    ->where('apoyo_preguntas.examen', $examen->id)
                    ->select('preguntas.*')
                    ->get();
            }
            
            $totalPreguntas = $preguntasExamen->count();
            $aciertos = 0;
            $respuestasGuardadas = [];
            
            foreach ($preguntasExamen as $pregunta) {
                $respuestaSeleccionada = $respuestas[$pregunta->id] ?? null;
                $opcionElegida = $this->resolverOpcionElegida($pregunta, $respuestaSeleccionada);
                $esCorrecta = (bool) ($opcionElegida?->es_correcta);

                if ($esCorrecta) {
                    $aciertos++;
                }

                $textoRespuesta = $opcionElegida?->texto ?? 'No respondida';

                $respuestasGuardadas[] = [
                    'pregunta_id' => $pregunta->id,
                    'respuesta' => $textoRespuesta,
                    'estatus' => $esCorrecta ? 'correcta' : 'incorrecta'
                ];
            }
            
            $calificacion = $totalPreguntas > 0 ? round(($aciertos / $totalPreguntas) * 100) : 0;
            
            $intento = ExamenRealizado::where('estudiante', $estudiante->id)
                                    ->where('examen', $examen->id)
                                    ->count() + 1;
            
            $horas = floor($tiempoUtilizadoSegundos / 3600);
            $minutos = floor(($tiempoUtilizadoSegundos % 3600) / 60);
            $segundos = $tiempoUtilizadoSegundos % 60;
            $tiempoFormateado = sprintf("%02d:%02d:%02d", $horas, $minutos, $segundos);
            
            $ahora = Carbon::now();
            $fechaActual = $ahora->toDateString();
            $horaFin = $ahora->toTimeString();
            $horaInicioCalculada = $ahora->copy()->subSeconds($tiempoUtilizadoSegundos);
            $horaInicioStr = $horaInicioCalculada->toTimeString();
            
            $examenRealizado = ExamenRealizado::create([
                'estudiante' => $estudiante->id,
                'examen' => $examen->id,
                'intento' => $intento,
                'calificacion' => $calificacion,
                'fecha_inicio' => $fechaActual,
                'hora_inicio' => $horaInicioStr,
                'fecha_fin' => $fechaActual,
                'hora_fin' => $horaFin,
                'tiempo' => $tiempoFormateado,
                'respuestas' => json_encode([
                    'respuestas' => $respuestasGuardadas
                ])
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Examen final completado exitosamente',
                'calificacion' => $calificacion,
                'aciertos' => $aciertos,
                'total' => $totalPreguntas,
                'intento' => $intento,
                'tiempo_utilizado' => $tiempoFormateado,
                'redirect' => route('estudiante.resultados', $examenRealizado->id)
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ Error en responderExamenCurso: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el examen final: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener recursos adicionales de una clase
     */
    public function getRecursosClase($claseId)
    {
        try {
            $clase = Clase::with('recursos')->find($claseId);
            
            if (!$clase) {
                return response()->json([
                    'success' => false,
                    'message' => 'Clase no encontrada'
                ]);
            }
            
            $recursos = $clase->recursos->map(function($recurso) {
                return [
                    'id' => $recurso->id,
                    'titulo' => $recurso->titulo,
                    'tipo' => $recurso->tipo,
                    'url' => $recurso->url,
                    'descripcion' => $recurso->descripcion,
                    'tipo_nombre' => $recurso->tipo_nombre,
                    'icono' => $recurso->icono,
                    'color' => $recurso->color
                ];
            });
            
            return response()->json([
                'success' => true,
                'recursos' => $recursos
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en getRecursosClase: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener recursos'
            ]);
        }
    }

    /**
     * Validar un cupón de descuento (desde el panel del estudiante)
     */
    public function validarCupon(Request $request)
    {
        try {
            $request->validate([
                'cupon' => 'required|string|max:50'
            ]);
            
            $codigo = strtoupper(trim($request->cupon));
            
            $cupon = Cupon::where('codigo', $codigo)->first();
            
            if (!$cupon) {
                return response()->json([
                    'valid' => false,
                    'message' => 'El cupón no existe'
                ]);
            }
            
            if ($cupon->usado) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Este cupón ya ha sido utilizado'
                ]);
            }
            
            if ($cupon->estatus !== 'activo') {
                return response()->json([
                    'valid' => false,
                    'message' => 'Este cupón no está activo'
                ]);
            }
            
            if ($cupon->fecha_expiracion && Carbon::now()->greaterThan($cupon->fecha_expiracion)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Este cupón ha expirado'
                ]);
            }
            
            return response()->json([
                'valid' => true,
                'descuento' => $cupon->valor_descuento,
                'tipo' => $cupon->tipo_descuento,
                'message' => 'Cupón válido!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al validar cupón: ' . $e->getMessage());
            return response()->json([
                'valid' => false,
                'message' => 'Error al validar el cupón: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener universidades para el estudiante
     */
    public function getUniversidades()
    {
        $universidades = Universidad::with('carrera')->orderBy('clave')->get();
        return response()->json([
            'success' => true,
            'universidades' => $universidades
        ]);
    }
}