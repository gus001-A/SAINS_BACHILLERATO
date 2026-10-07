<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Estudiante;
use App\Models\Preparatoria;
use App\Models\Universidad;
use App\Models\Cupon;
use App\Models\Pago;
use App\Models\TiempoEstudio;
use App\Models\ExamenRealizado;
use App\Models\ProgresoVideo;
use App\Models\Video;
use App\Models\DocumentoEstudiante;
use App\Models\Notificacion;
use App\Exports\EstudiantesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;


class EstudianteController extends Controller
{

    /**
     * Opciones compartidas para los formularios de estudiante.
     */
    private function opcionesFormulario(): array
    {
        return [
            'cupones' => Cupon::where('estatus', 'activo')->where('usado', false)->orderBy('codigo')
                ->get(['id', 'codigo', 'tipo_descuento', 'valor_descuento'])
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'label' => $c->tipo_descuento === 'porcentaje'
                        ? "{$c->codigo} — {$c->valor_descuento}%"
                        : "{$c->codigo} — \${$c->valor_descuento}",
                ]),
            ...\App\Support\DatosIssfam::catalogos(),
        ];
    }

    public function create()
    {
        return \Inertia\Inertia::render('Admin/Estudiantes/Create', [
            'opciones' => $this->opcionesFormulario(),
        ]);
    }

    /**
     * Guarda un nuevo estudiante en el sistema
     */
    public function store(Request $request)
    {
        $edadMinima = now()->subYears(15)->format('Y-m-d');

        \App\Support\DatosIssfam::normalizar($request);
        $request->validate(\App\Support\DatosIssfam::reglas(false) + [
            'email' => 'required|email|unique:usuario,correo',
            'password' => 'required|min:6|confirmed',
            'nombre' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúñÑÁÉÍÓÚ\s]+$/',
            'paterno' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúñÑÁÉÍÓÚ\s]+$/',
            'materno' => 'nullable|string|max:100|regex:/^[a-zA-ZáéíóúñÑÁÉÍÓÚ\s]+$/',
            'fecha_nacimiento' => "required|date|before_or_equal:{$edadMinima}|after:1920-01-01",
            'sexo' => 'required|in:M,F',
            'telefono' => 'required|regex:/^[0-9]{10}$/',
            'telefono_casa' => 'nullable|regex:/^[0-9]{7,10}$/',
            'plan_activo' => 'boolean',
            'cupon_id' => 'nullable|exists:cupones,id',
        ], [
            'email.unique' => 'Este correo electrónico ya está registrado',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'Ingresa un correo electrónico válido',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.regex' => 'El nombre solo puede contener letras',
            'paterno.required' => 'El apellido paterno es obligatorio',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria',
            'fecha_nacimiento.before_or_equal' => 'El estudiante debe tener al menos 15 años.',
            'sexo.required' => 'Debes seleccionar el sexo',
            'telefono.required' => 'El teléfono es obligatorio',
            'telefono.regex' => 'El teléfono debe tener 10 dígitos (sin espacios ni guiones)',
            'telefono_casa.regex' => 'El teléfono de casa debe tener entre 7 y 10 dígitos',
        ] + \App\Support\DatosIssfam::mensajes(), \App\Support\DatosIssfam::atributos());

        try {
            DB::beginTransaction();

            $cupon = null;
            $codigoCupon = null;

            if ($request->cupon_id) {
                $cupon = Cupon::where('id', $request->cupon_id)
                    ->where('estatus', 'activo')
                    ->where('usado', false)
                    ->first();

                if (!$cupon) {
                    throw new \Exception('El cupón seleccionado no es válido o ya fue utilizado');
                }

                $codigoCupon = $cupon->codigo;
            }

            $user = User::create([
                'correo' => $request->email,
                'contraseña' => Hash::make($request->password),
                'rol' => 'estudiante'
            ]);

            $cuponCubreTodo = $cupon && $cupon->cubreTodo();

            $estudiante = Estudiante::create([
                'nombre' => $request->nombre,
                'paterno' => $request->paterno,
                'materno' => $request->materno,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'sexo' => $request->sexo,
                'telefono' => $request->telefono,
                'telefono_casa' => $request->telefono_casa,
                'plan_activo' => $request->boolean('plan_activo') || $cuponCubreTodo,
                'cupon' => $codigoCupon,
                'fecha_inscripcion' => now(),
                'usuario' => $user->id,
                ...$request->only(\App\Support\DatosIssfam::CAMPOS),
            ]);

            if ($cupon) {
                $cupon->update([
                    'usado' => true,
                    'usuario_uso' => $user->id,
                    'fecha_uso' => now()
                ]);
            }

            if ($cuponCubreTodo) {
                Pago::create([
                    'alumno_pago' => $estudiante->id,
                    'tipo_pago' => 'cupon',
                    'monto_pago' => 0,
                    'estatus' => 'completado',
                    'referencia_pago' => Pago::referenciaPreferida('CUPON-' . $cupon->codigo, 'CUPON'),
                    'fecha_pago' => now(),
                    'nota_usuario' => "Plan Premium activado con el cupón {$cupon->codigo} (100%).",
                ]);
            }

            DB::commit();

            $mensaje = 'Estudiante registrado exitosamente. Correo: ' . $user->correo;
            if ($cupon) {
                $mensaje .= $cuponCubreTodo
                    ? " - Cupón {$cupon->codigo} (100%): Premium activado."
                    : ' - Cupón aplicado: ' . $cupon->codigo;
            }

            return redirect()->route('admin.estudiantes.index')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar estudiante: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocurrió un error al registrar el estudiante: ' . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $telefono = $request->get('telefono');
        $sexo = $request->get('sexo');
        $plan_activo = $request->get('plan_activo');

        $carreraId = $request->get('carrera_id');

        $estudiantes = User::where('rol', 'estudiante')
            ->with('estudiante.carrera:id,nombre')
            // Agrupado: sin el where(fn) el orWhereHas se saltaba el filtro de rol.
            ->when($search, function($query, $search) {
                return $query->where(fn ($w) => $w->where('correo', 'LIKE', "%{$search}%")
                    ->orWhereHas('estudiante', function($q) use ($search) {
                        $q->where('nombre', 'LIKE', "%{$search}%")
                          ->orWhere('paterno', 'LIKE', "%{$search}%")
                          ->orWhere('materno', 'LIKE', "%{$search}%")
                          ->orWhere('telefono', 'LIKE', "%{$search}%")
                          ->orWhere('curp', 'LIKE', "%{$search}%")
                          ->orWhere('cupon', 'LIKE', "%{$search}%");
                    }));
            })
            ->when($carreraId, fn ($query) => $query->whereHas('estudiante', fn ($q) => $q->where('carrera_id', $carreraId)))
            ->when($telefono, function($query, $telefono) {
                return $query->whereHas('estudiante', function($q) use ($telefono) {
                    $q->where('telefono', 'LIKE', "%{$telefono}%")
                      ->orWhere('telefono_casa', 'LIKE', "%{$telefono}%");
                });
            })
            ->when($sexo, function($query, $sexo) {
                return $query->whereHas('estudiante', function($q) use ($sexo) {
                    $q->where('sexo', $sexo);
                });
            })
            ->when($plan_activo !== null && $plan_activo !== '', function($query) use ($plan_activo) {
                return $query->whereHas('estudiante', function($q) use ($plan_activo) {
                    $q->where('plan_activo', $plan_activo);
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(15);

        $totalEstudiantes = User::where('rol', 'estudiante')->count();

        $activos = User::where('rol', 'estudiante')
            ->whereHas('estudiante', function($q) {
                $q->where('plan_activo', true);
            })->count();

        $inactivos = User::where('rol', 'estudiante')
            ->whereHas('estudiante', function($q) {
                $q->where('plan_activo', false);
            })->count();

        $conCupon = User::where('rol', 'estudiante')
            ->whereHas('estudiante', function($q) {
                $q->whereNotNull('cupon')
                  ->where('cupon', '!=', '')
                  ->where('cupon', '!=', 'null');
            })->count();

        $totalDocsRequeridos = count(DocumentoEstudiante::TIPOS);
        $idsEstudiantes = $estudiantes->getCollection()->pluck('estudiante.id')->filter()->values();
        $docsAprobadosPorEstudiante = DocumentoEstudiante::whereIn('estudiante_id', $idsEstudiantes)
            ->where('estatus', DocumentoEstudiante::ESTATUS_APROBADO)
            ->selectRaw('estudiante_id, count(*) as total')
            ->groupBy('estudiante_id')
            ->pluck('total', 'estudiante_id');

        $estudiantes->getCollection()->transform(function ($u) use ($docsAprobadosPorEstudiante, $totalDocsRequeridos) {
            $e = $u->estudiante;
            return [
                'id' => $u->id,
                'correo' => $u->correo,
                'nombre_completo' => $e ? trim("{$e->nombre} {$e->paterno} {$e->materno}") : $u->correo,
                'telefono' => $e?->telefono,
                'sexo' => $e?->sexo,
                'plan_activo' => (bool) ($e?->plan_activo),
                'cupon' => $e?->cupon,
                'documentos_aprobados' => $e ? ($docsAprobadosPorEstudiante[$e->id] ?? 0) : 0,
                'documentos_requeridos' => $totalDocsRequeridos,
                'sin_perfil' => $e === null,
                'carrera' => $e?->carrera?->nombre,
                'fecha_registro' => optional($e?->fecha_inscripcion)->format('Y-m-d'),
            ];
        });

        return \Inertia\Inertia::render('Admin/Estudiantes/Index', [
            'estudiantes' => $estudiantes,
            'stats' => [
                'total' => $totalEstudiantes,
                'activos' => $activos,
                'inactivos' => $inactivos,
                'conCupon' => $conCupon,
            ],
            'filters' => [
                'search' => $search,
                'telefono' => $telefono,
                'sexo' => $sexo,
                'plan_activo' => $plan_activo === null || $plan_activo === '' ? null : (int) $plan_activo,
                'carrera_id' => $carreraId,
            ],
            'carreras' => \App\Models\CarreraBachillerato::opciones(),
        ]);
    }

    /**
     * Exporta en Excel los datos de todos los estudiantes: contacto y el
     * estatus (aprobado/rechazado/pendiente/no subido) de sus 4 documentos,
     * con el motivo cuando fueron rechazados.
     */
    public function exportarExcel()
    {
        return Excel::download(new EstudiantesExport(), 'estudiantes-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function show($id)
    {
        $user = User::with(['estudiante.escuelaProcedencia', 'estudiante.universidadInteres', 'estudiante.pagos'])
            ->findOrFail($id);

        if (!$user->estudiante) {
            // Se registró pero nunca completó su perfil: se muestra una ficha
            // mínima para que el administrador pueda eliminar la cuenta.
            return \Inertia\Inertia::render('Admin/Estudiantes/Incompleto', [
                'usuario' => [
                    'id' => $user->id,
                    'correo' => $user->correo,
                    'con_google' => !empty($user->google_id),
                ],
            ]);
        }

        $estudiante = $user->estudiante;
        $usuario = $user;

        $estadisticasTiempo = TiempoEstudio::getEstadisticasCompletas($estudiante->id);

        $tiempoTotalHoras = $estadisticasTiempo['total_horas'] ?? 0;
        $tiempoTotalMinutos = $estadisticasTiempo['total_minutos'] ?? 0;
        $totalSesiones = $estadisticasTiempo['total_sesiones'] ?? 0;
        $diasActivos = $estadisticasTiempo['dias_estudiados'] ?? 0;

        $ultimaActividad = '—';
        $fechaUltimaActividad = null;

        $ultimoTiempo = TiempoEstudio::where('estudiante_id', $estudiante->id)
            ->whereNotNull('ultima_actividad')
            ->orderBy('ultima_actividad', 'desc')
            ->first();

        if ($ultimoTiempo && $ultimoTiempo->ultima_actividad) {
            $fechaUltimaActividad = $ultimoTiempo->ultima_actividad;
            $ultimaActividad = $ultimoTiempo->ultima_actividad->diffForHumans();
        }

        if ($ultimaActividad === '—') {
            $ultimoExamen = ExamenRealizado::where('estudiante', $estudiante->id)
                ->whereNotNull('fecha_fin')
                ->orderBy('fecha_fin', 'desc')
                ->first();

            if ($ultimoExamen && $ultimoExamen->fecha_fin) {
                $fechaUltimaActividad = Carbon::parse($ultimoExamen->fecha_fin);
                $ultimaActividad = $fechaUltimaActividad->diffForHumans();
            }
        }

        if ($ultimaActividad === '—') {
            $ultimoVideo = ProgresoVideo::where('estudiante_id', $estudiante->id)
                ->whereNotNull('fecha_visto')
                ->orderBy('fecha_visto', 'desc')
                ->first();

            if ($ultimoVideo && $ultimoVideo->fecha_visto) {
                $fechaUltimaActividad = Carbon::parse($ultimoVideo->fecha_visto);
                $ultimaActividad = $fechaUltimaActividad->diffForHumans();
            }
        }

        if ($ultimaActividad === '—' && $estudiante->fecha_inscripcion) {
            $fechaUltimaActividad = Carbon::parse($estudiante->fecha_inscripcion);
            $ultimaActividad = 'Desde ' . $fechaUltimaActividad->format('d/m/Y');
        }

        $estudioDiario = TiempoEstudio::where('estudiante_id', $estudiante->id)
            ->where('fecha', '>=', Carbon::now()->subDays(7))
            ->orderBy('fecha', 'asc')
            ->get()
            ->map(function($item) {
                $minutos = (int)($item->minutos_estudiados ?? 0);
                $horas = $minutos > 0 ? round($minutos / 60, 1) : 0;

                $fecha = Carbon::parse($item->fecha);
                $diaEspanol = '';

                try {
                    Carbon::setLocale('es');
                    $diaEspanol = ucfirst($fecha->isoFormat('dddd'));
                } catch (\Exception $e) {
                    $diasMap = [
                        'Monday' => 'Lunes',
                        'Tuesday' => 'Martes',
                        'Wednesday' => 'Miércoles',
                        'Thursday' => 'Jueves',
                        'Friday' => 'Viernes',
                        'Saturday' => 'Sábado',
                        'Sunday' => 'Domingo',
                        'Mon' => 'Lunes',
                        'Tue' => 'Martes',
                        'Wed' => 'Miércoles',
                        'Thu' => 'Jueves',
                        'Fri' => 'Viernes',
                        'Sat' => 'Sábado',
                        'Sun' => 'Domingo'
                    ];
                    $diaIngles = $fecha->format('l');
                    $diaEspanol = $diasMap[$diaIngles] ?? $fecha->format('D');
                }

                return (object)[
                    'dia' => $diaEspanol,
                    'horas_estudiadas' => $horas,
                    'minutos_estudiados' => $minutos,
                    'segundos' => (int)($item->segundos_estudiados ?? 0)
                ];
            });

        if ($estudioDiario->isEmpty()) {
            $estudioDiario = collect();
            for ($i = 6; $i >= 0; $i--) {
                $fecha = Carbon::now()->subDays($i);
                Carbon::setLocale('es');
                $diaEspanol = ucfirst($fecha->isoFormat('dddd'));

                $estudioDiario->push((object)[
                    'dia' => $diaEspanol,
                    'horas_estudiadas' => 0,
                    'minutos_estudiados' => 0,
                    'segundos' => 0
                ]);
            }
        }

        $examenes = ExamenRealizado::where('estudiante', $estudiante->id)
            ->with('examenGenerado')
            ->orderBy('fecha_fin', 'desc')
            ->orderBy('hora_fin', 'desc')
            ->get()
            ->map(function($examenRealizado) {
                $examenGen = $examenRealizado->examenGenerado;
                $tipoExamen = $examenGen->tipo_examen ?? 'general';
                $nombreReferencia = '';

                switch (strtolower($tipoExamen)) {
                    case 'materia':
                        $nombreReferencia = $examenGen->materia->nombre ?? 'Materia';
                        $badgeColor = 'primary';
                        $badgeIcon = 'fa-book';
                        $tipoTexto = 'Por Materia';
                        break;

                    case 'curso':
                        $nombreReferencia = $examenGen->curso->nombre ?? 'Curso';
                        $badgeColor = 'success';
                        $badgeIcon = 'fa-graduation-cap';
                        $tipoTexto = 'Por Curso';
                        break;

                    case 'simulacion':
                    case 'simulación':
                        $nombreReferencia = $examenGen->titulo ?? 'Simulación';
                        $badgeColor = 'danger';
                        $badgeIcon = 'fa-flask';
                        $tipoTexto = 'Simulación';
                        break;

                    default:
                        $nombreReferencia = $examenGen->titulo ?? 'Examen';
                        $badgeColor = 'secondary';
                        $badgeIcon = 'fa-puzzle-piece';
                        $tipoTexto = ucfirst($tipoExamen);
                        break;
                }

                $examenRealizado->tipo_examen = $tipoExamen;
                $examenRealizado->nombre_referencia = $nombreReferencia;
                $examenRealizado->badge_color = $badgeColor;
                $examenRealizado->badge_icon = $badgeIcon;
                $examenRealizado->tipo_texto = $tipoTexto;
                $examenRealizado->examen_nombre = $examenGen->titulo ?? 'Examen';

                return $examenRealizado;
            });

        $examenesPorTipo = [
            'simulacion' => $examenes->filter(function($e) {
                return in_array(strtolower($e->tipo_examen), ['simulacion', 'simulación']);
            })->count(),
            'materia' => $examenes->filter(function($e) {
                return strtolower($e->tipo_examen) == 'materia';
            })->count(),
            'curso' => $examenes->filter(function($e) {
                return strtolower($e->tipo_examen) == 'curso';
            })->count(),
        ];

        $promedioCalificaciones = $examenes->isNotEmpty() ? round($examenes->avg('calificacion')) : 0;

        $promedioPorTipo = [
            'simulacion' => $examenes->filter(function($e) {
                return in_array(strtolower($e->tipo_examen), ['simulacion', 'simulación']);
            })->isNotEmpty() ? round($examenes->filter(function($e) {
                return in_array(strtolower($e->tipo_examen), ['simulacion', 'simulación']);
            })->avg('calificacion')) : 0,

            'materia' => $examenes->filter(function($e) {
                return strtolower($e->tipo_examen) == 'materia';
            })->isNotEmpty() ? round($examenes->filter(function($e) {
                return strtolower($e->tipo_examen) == 'materia';
            })->avg('calificacion')) : 0,

            'curso' => $examenes->filter(function($e) {
                return strtolower($e->tipo_examen) == 'curso';
            })->isNotEmpty() ? round($examenes->filter(function($e) {
                return strtolower($e->tipo_examen) == 'curso';
            })->avg('calificacion')) : 0,
        ];

        $totalVideos = Video::count();
        $progresosVideos = ProgresoVideo::where('estudiante_id', $estudiante->id)->get();
        $vistosCompletos = $progresosVideos->where('completado', true)->count();
        $videosEnProgreso = $progresosVideos->where('completado', false)->count();
        $porcentajeProgreso = $totalVideos > 0 ? round(($vistosCompletos / $totalVideos) * 100) : 0;

        $ultimosVideos = ProgresoVideo::where('estudiante_id', $estudiante->id)
            ->with('video')
            ->whereNotNull('fecha_visto')
            ->orderBy('fecha_visto', 'desc')
            ->limit(5)
            ->get();

        $videosIdsVistos = $progresosVideos->pluck('video_id')->toArray();
        $videosFaltantes = Video::whereNotIn('id', $videosIdsVistos)
            ->orderBy('materia')
            ->orderBy('tema')
            ->get();

        return \Inertia\Inertia::render('Admin/Estudiantes/Show', [
            'estudiante' => [
                'id' => $user->id,
                'correo' => $user->correo,
                'nombre_completo' => trim("{$estudiante->nombre} {$estudiante->paterno} {$estudiante->materno}"),
                'telefono' => $estudiante->telefono,
                'telefono_casa' => $estudiante->telefono_casa,
                'sexo' => $estudiante->sexo,
                'fecha_nacimiento' => optional($estudiante->fecha_nacimiento)->format('Y-m-d'),
                'fecha_inscripcion' => optional($estudiante->fecha_inscripcion)->format('Y-m-d'),
                'plan_activo' => (bool) $estudiante->plan_activo,
                'cupon' => $estudiante->cupon,
                'carrera' => $estudiante->carrera?->nombre,
                'curp' => $estudiante->curp,
                'domicilio' => collect([$estudiante->calle_numero, $estudiante->colonia,
                    $estudiante->codigo_postal ? 'C.P. ' . $estudiante->codigo_postal : null,
                    $estudiante->municipio, $estudiante->entidad_federativa])->filter()->implode(', ') ?: null,
                'ediciones_datos' => $estudiante->candadoEdiciones(),
                'escuela' => $estudiante->escuelaProcedencia?->centro_educativo,
                'universidad' => $estudiante->universidadInteres?->clave,
                'foto_url' => $estudiante->foto ? \App\Support\ArchivoUrl::foto($estudiante) : null,
                'certificado_generado' => (bool) $estudiante->certificado_path,
                'certificado_generado_en' => optional($estudiante->certificado_generado_en)->format('d/m/Y H:i'),
            ],
            'stats' => [
                'tiempo_horas' => $tiempoTotalHoras,
                'tiempo_minutos' => $tiempoTotalMinutos,
                'total_sesiones' => $totalSesiones,
                'dias_activos' => $diasActivos,
                'ultima_actividad' => $ultimaActividad,
                'promedio_calificaciones' => $promedioCalificaciones,
                'total_examenes' => $examenes->count(),
                'total_videos' => $totalVideos,
                'videos_completos' => $vistosCompletos,
                'videos_en_progreso' => $videosEnProgreso,
                'porcentaje_progreso' => $porcentajeProgreso,
            ],
            'estudioDiario' => collect($estudioDiario)->map(fn ($d) => [
                'dia' => is_object($d) ? $d->dia : $d['dia'],
                'horas' => is_object($d) ? $d->horas_estudiadas : $d['horas_estudiadas'],
            ])->values(),
            'examenes' => collect($examenes)->take(50)->map(fn ($e) => [
                'id' => $e->id ?? null,
                'tipo' => $e->tipo_examen ?? 'Simulador',
                'calificacion' => round($e->calificacion ?? 0, 1),
                ...$this->aciertosExamen($e),
                'intento' => $e->intento,
                'fecha' => isset($e->fecha_fin) && $e->fecha_fin ? Carbon::parse($e->fecha_fin)->format('d/m/Y') : null,
            ])->values(),
        ]);
    }

    /** Aciertos / total de un examen realizado, leídos del JSON de respuestas. */
    private function aciertosExamen($examen): array
    {
        $raw = $examen->respuestas;
        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }
        $lista = is_array($raw) ? ($raw['respuestas'] ?? $raw) : [];
        $lista = array_values(array_filter($lista, 'is_array'));

        $aciertos = collect($lista)->filter(fn ($r) => ($r['estatus'] ?? null) === 'correcta'
            || (($r['correcta'] ?? false) === true))->count();

        return [
            'aciertos' => $aciertos,
            'total_preguntas' => count($lista),
            'corregido' => !empty($raw['ediciones'] ?? null),
        ];
    }

    /**
     * Obtiene los datos de un estudiante en formato JSON para el modal
     */
    public function getEstudianteJson($id)
    {
        $user = User::with(['estudiante.escuelaProcedencia', 'estudiante.universidadInteres'])
            ->findOrFail($id);

        if (!$user->estudiante) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado'
            ], 404);
        }

        $estudiante = $user->estudiante;

        $data = [
            'success' => true,
            'data' => [
                'id' => $estudiante->id,
                'nombre' => $estudiante->nombre,
                'paterno' => $estudiante->paterno,
                'materno' => $estudiante->materno,
                'fecha_nacimiento' => $estudiante->fecha_nacimiento,
                'sexo' => $estudiante->sexo,
                'telefono' => $estudiante->telefono,
                'telefono_casa' => $estudiante->telefono_casa,
                'plan_activo' => $estudiante->plan_activo,
                'cupon' => $estudiante->cupon,
                'fecha_inscripcion' => $estudiante->fecha_inscripcion,
                'user' => [
                    'correo' => $user->correo,
                    'rol' => $user->rol
                ],
                'escuela_procedencia' => $estudiante->escuelaProcedencia ? [
                    'id' => $estudiante->escuelaProcedencia->id,
                    'estado' => $estudiante->escuelaProcedencia->estado,
                    'municipio' => $estudiante->escuelaProcedencia->municipio,
                    'localidad' => $estudiante->escuelaProcedencia->localidad,
                    'centro_educativo' => $estudiante->escuelaProcedencia->centro_educativo,
                    'clave' => $estudiante->escuelaProcedencia->clave,
                    'tipo' => $estudiante->escuelaProcedencia->tipo,
                    'servicio' => $estudiante->escuelaProcedencia->servicio,
                    'turno' => $estudiante->escuelaProcedencia->turno,
                    'ambito' => $estudiante->escuelaProcedencia->ambito,
                    'direccion' => $estudiante->escuelaProcedencia->direccion,
                ] : null,
                'universidad_interes' => $estudiante->universidadInteres ? [
                    'id' => $estudiante->universidadInteres->id,
                    'clave' => $estudiante->universidadInteres->clave,
                    'direccion' => $estudiante->universidadInteres->direccion,
                    'tipo' => $estudiante->universidadInteres->tipo,
                    'duracion' => $estudiante->universidadInteres->duracion,
                    'estado' => $estudiante->universidadInteres->estado,
                    'municipio' => $estudiante->universidadInteres->municipio,
                    'localidad' => $estudiante->universidadInteres->localidad,
                ] : null,
            ]
        ];

        return response()->json($data);
    }

    /**
     * Muestra el formulario para editar un estudiante
     */
    public function edit($id)
    {
        $user = User::with(['estudiante.escuelaProcedencia', 'estudiante.universidadInteres'])->findOrFail($id);

        if (!$user->estudiante) {
            return redirect()->route('admin.estudiantes.show', $user->id)
                ->with('error', 'Este usuario no completó su registro; no hay datos de estudiante que editar.');
        }

        $estudiante = $user->estudiante;

        $estudianteData = new \stdClass();

        $estudianteData->id = $user->id;
        $estudianteData->correo = $user->correo;
        $estudianteData->rol = $user->rol;

        $estudianteData->nombre = $estudiante->nombre ?? '';
        $estudianteData->paterno = $estudiante->paterno ?? '';
        $estudianteData->materno = $estudiante->materno ?? '';
        $estudianteData->fecha_nacimiento = $estudiante->fecha_nacimiento ?? '';
        $estudianteData->sexo = $estudiante->sexo ?? '';
        $estudianteData->telefono = $estudiante->telefono ?? '';
        $estudianteData->telefono_casa = $estudiante->telefono_casa ?? '';
        $estudianteData->escuela_procedencia = $estudiante->escuela_procedencia ?? '';
        $estudianteData->universidad_interes = $estudiante->universidad_interes ?? '';
        $estudianteData->plan_activo = $estudiante->plan_activo ?? false;
        $estudianteData->cupon = $estudiante->cupon ?? '';

        if ($estudiante->escuelaProcedencia) {
            $estudianteData->escuela_procedencia_nombre = $estudiante->escuelaProcedencia->centro_educativo ?? '';
            $estudianteData->escuela_procedencia_estado = $estudiante->escuelaProcedencia->estado ?? '';
            $estudianteData->escuela_procedencia_municipio = $estudiante->escuelaProcedencia->municipio ?? '';
            $estudianteData->escuela_procedencia_localidad = $estudiante->escuelaProcedencia->localidad ?? '';
        } else {
            $estudianteData->escuela_procedencia_nombre = '';
            $estudianteData->escuela_procedencia_estado = '';
            $estudianteData->escuela_procedencia_municipio = '';
            $estudianteData->escuela_procedencia_localidad = '';
        }

        if ($estudiante->universidadInteres) {
            $estudianteData->universidad_interes_nombre = $estudiante->universidadInteres->clave . ' - ' . ($estudiante->universidadInteres->direccion ?? '');
            $estudianteData->universidad_interes_estado = $estudiante->universidadInteres->estado ?? '';
            $estudianteData->universidad_interes_municipio = $estudiante->universidadInteres->municipio ?? '';
            $estudianteData->universidad_interes_localidad = $estudiante->universidadInteres->localidad ?? '';
        } else {
            $estudianteData->universidad_interes_nombre = '';
            $estudianteData->universidad_interes_estado = '';
            $estudianteData->universidad_interes_municipio = '';
            $estudianteData->universidad_interes_localidad = '';
        }

        $cuponesDisponibles = Cupon::where('estatus', 'activo')
            ->where('usado', false)
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'tipo_descuento', 'valor_descuento']);

        $estadosPrepa = Preparatoria::select('estado')->distinct()->orderBy('estado')->pluck('estado');
        $estadosUniversidad = Universidad::select('estado')->distinct()->orderBy('estado')->pluck('estado');

        $preparatorias = Preparatoria::orderBy('centro_educativo')->get();
        $universidades = Universidad::orderBy('clave')->get();

        return \Inertia\Inertia::render('Admin/Estudiantes/Edit', [
            'estudiante' => [
                'id' => $user->id,
                'correo' => $user->correo,
                'nombre' => $estudianteData->nombre,
                'paterno' => $estudianteData->paterno,
                'materno' => $estudianteData->materno,
                'fecha_nacimiento' => $estudiante->fecha_nacimiento ? $estudiante->fecha_nacimiento->format('Y-m-d') : null,
                'sexo' => $estudianteData->sexo ?: null,
                'telefono' => $estudianteData->telefono,
                'telefono_casa' => $estudianteData->telefono_casa,
                'escuela_procedencia' => $estudiante->escuela_procedencia,
                'universidad_interes' => $estudiante->universidad_interes,
                'plan_activo' => (bool) $estudiante->plan_activo,
                'cupon' => $estudianteData->cupon,
                ...\App\Support\DatosIssfam::valores($estudiante),
                'candado' => $estudiante->candadoEdiciones(),
            ],
            'opciones' => $this->opcionesFormulario(),
        ]);
    }

    /**
     * Actualiza los datos de un estudiante
     */
    public function update(Request $request, $id)
    {
        $user = User::with('estudiante')->findOrFail($id);
        $estudiante = $user->estudiante;

        if (!$estudiante) {
            return redirect()->route('admin.estudiantes.index')
                ->with('error', 'Estudiante no encontrado');
        }

        $edadMinima = now()->subYears(15)->format('Y-m-d');

        \App\Support\DatosIssfam::normalizar($request);
        $request->validate(\App\Support\DatosIssfam::reglas(false, $estudiante->id) + [
            'reiniciar_candado' => 'boolean',
            'nombre' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúñÑÁÉÍÓÚ\s]+$/',
            'paterno' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúñÑÁÉÍÓÚ\s]+$/',
            'materno' => 'nullable|string|max:100|regex:/^[a-zA-ZáéíóúñÑÁÉÍÓÚ\s]+$/',
            'fecha_nacimiento' => "required|date|before_or_equal:{$edadMinima}",
            'sexo' => 'required|in:M,F',
            'telefono' => 'required|regex:/^[0-9]{10}$/',
            'telefono_casa' => 'nullable|regex:/^[0-9]{7,10}$/',
            'plan_activo' => 'boolean',
            'cupon_id' => 'nullable|exists:cupones,id',
            'email' => 'required|email|unique:usuario,correo,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
        ], [
            'fecha_nacimiento.before_or_equal' => 'El estudiante debe tener al menos 15 años.',
        ] + \App\Support\DatosIssfam::mensajes(), \App\Support\DatosIssfam::atributos());

        try {
            DB::beginTransaction();

            $nuevoCupon = null;
            $codigoCupon = $estudiante->cupon;

            if ($request->cupon_id) {
                $nuevoCupon = Cupon::where('id', $request->cupon_id)
                    ->where('estatus', 'activo')
                    ->where('usado', false)
                    ->first();

                if (!$nuevoCupon) {
                    throw new \Exception('El cupón seleccionado no es válido o ya fue utilizado');
                }

                $codigoCupon = $nuevoCupon->codigo;

                $nuevoCupon->update([
                    'usado' => true,
                    'usuario_uso' => $user->id,
                    'fecha_uso' => now()
                ]);
            } elseif ($request->cupon_id === '' && $estudiante->cupon) {
                $codigoCupon = null;
            }

            $userData = ['correo' => $request->email];
            if ($request->filled('password')) {
                $userData['contraseña'] = Hash::make($request->password);
            }
            $user->update($userData);

            $estudiante->update([
                'nombre' => $request->nombre,
                'paterno' => $request->paterno,
                'materno' => $request->materno,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'sexo' => $request->sexo,
                'telefono' => $request->telefono,
                'telefono_casa' => $request->telefono_casa,
                'plan_activo' => $request->boolean('plan_activo'),
                'cupon' => $codigoCupon,
                ...$request->only(\App\Support\DatosIssfam::CAMPOS),
                // Lo que edita el administrador no cuenta para el candado del alumno;
                // además puede devolverle sus cambios si los agotó.
                ...($request->boolean('reiniciar_candado')
                    ? ['ediciones_total' => 0, 'ediciones_dia' => 0, 'ediciones_fecha' => null]
                    : []),
            ]);

            DB::commit();

            $mensaje = 'Estudiante actualizado exitosamente';
            if ($nuevoCupon) {
                $mensaje .= ' - Cupón aplicado: ' . $nuevoCupon->codigo;
            }

            return redirect()->route('admin.estudiantes.index')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar estudiante: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocurrió un error al actualizar el estudiante: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un estudiante
     */
    public function destroy($id)
    {
        try {
            $user = User::with('estudiante')->findOrFail($id);

            if ($user->rol !== 'estudiante') {
                return redirect()->route('admin.estudiantes.index')
                    ->with('error', 'Este usuario no es un estudiante.');
            }

            $estudiante = $user->estudiante;
            $archivos = [];

            DB::beginTransaction();

            if ($estudiante) {
                $nombre = trim($estudiante->nombre . ' ' . $estudiante->paterno) ?: $user->correo;

                $archivos = DocumentoEstudiante::delEstudiante($estudiante->id)->pluck('archivo')
                    ->push($estudiante->certificado_path, $estudiante->foto)
                    ->filter()->all();

                // Se borra explícitamente lo que cuelga del estudiante en vez de
                // confiar en el ON DELETE CASCADE: en producción la BD se importó
                // de un dump y no todas las llaves foráneas lo tienen.
                $this->borrarSiExiste('documento_estudiante', 'estudiante_id', $estudiante->id);
                $this->borrarSiExiste('examen_realizado', 'estudiante', $estudiante->id);
                $this->borrarSiExiste('progreso_videos', 'estudiante_id', $estudiante->id);
                $this->borrarSiExiste('tiempo_estudio', 'estudiante_id', $estudiante->id);
                $this->borrarSiExiste('pagos', 'alumno_pago', $estudiante->id);
                $estudiante->delete();
            } else {
                // Usuario que nunca completó su registro (sin perfil de estudiante).
                $nombre = $user->correo;
            }

            $this->borrarSiExiste('notificaciones', 'id_usuario', $user->id);
            $this->borrarSiExiste('interacciones_call_center', 'id_estudiante', $user->id);
            $this->borrarSiExiste('sessions', 'user_id', $user->id);
            if (\Illuminate\Support\Facades\Schema::hasColumn('cupones', 'usuario_uso')) {
                DB::table('cupones')->where('usuario_uso', $user->id)->update(['usuario_uso' => null]);
            }

            $user->delete();

            DB::commit();

            // Los archivos se borran hasta que la BD confirmó, para no perderlos
            // si la transacción se revierte.
            Storage::disk('public')->delete($archivos);

            return redirect()->route('admin.estudiantes.index')
                ->with('success', $estudiante ? "Estudiante {$nombre} eliminado exitosamente" : "Usuario {$nombre} eliminado exitosamente");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar estudiante: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Ocurrió un error al eliminar el estudiante: ' . $e->getMessage());
        }
    }

    private function borrarSiExiste(string $tabla, string $columna, $valor): void
    {
        if (\Illuminate\Support\Facades\Schema::hasTable($tabla)) {
            DB::table($tabla)->where($columna, $valor)->delete();
        }
    }

    /**
     * Restablece la contraseña de un estudiante
     */
    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'new_password' => 'required|min:6|confirmed',
        ]);

        try {
            $user = User::findOrFail($id);

            $user->update([
                'contraseña' => Hash::make($request->new_password)
            ]);

            return redirect()->route('admin.estudiantes.show', $id)
                ->with('success', 'Contraseña restablecida exitosamente');

        } catch (\Exception $e) {
            Log::error('Error al restablecer contraseña: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Ocurrió un error al restablecer la contraseña');
        }
    }

    // ========== MÉTODOS PARA FILTROS ANIDADOS (AJAX) ==========

    public function getMunicipiosPrepa(Request $request)
    {
        $municipios = Preparatoria::where('estado', $request->estado)
            ->select('municipio')
            ->distinct()
            ->orderBy('municipio')
            ->pluck('municipio');

        return response()->json($municipios);
    }

    public function getLocalidadesPrepa(Request $request)
    {
        $localidades = Preparatoria::where('estado', $request->estado)
            ->where('municipio', $request->municipio)
            ->select('localidad')
            ->distinct()
            ->orderBy('localidad')
            ->pluck('localidad');

        return response()->json($localidades);
    }

    public function getPreparatorias(Request $request)
    {
        $query = Preparatoria::where('estado', $request->estado)
            ->where('municipio', $request->municipio);

        if ($request->has('localidad') && $request->localidad && $request->localidad !== 'todas') {
            $query->where('localidad', $request->localidad);
        }

        $preparatorias = $query->orderBy('centro_educativo')
            ->get(['id', 'centro_educativo', 'clave', 'turno']);

        return response()->json($preparatorias);
    }

    // ========== MÉTODOS PARA UNIVERSIDADES ==========

    public function getMunicipiosUniversidad(Request $request)
    {
        $municipios = Universidad::where('estado', $request->estado)
            ->select('municipio')
            ->distinct()
            ->orderBy('municipio')
            ->pluck('municipio');

        return response()->json($municipios);
    }

    public function getLocalidadesUniversidad(Request $request)
    {
        $localidades = Universidad::where('estado', $request->estado)
            ->where('municipio', $request->municipio)
            ->select('localidad')
            ->distinct()
            ->orderBy('localidad')
            ->pluck('localidad');

        return response()->json($localidades);
    }

    public function getUniversidades(Request $request)
    {
        $query = Universidad::where('estado', $request->estado)
            ->where('municipio', $request->municipio);

        if ($request->has('localidad') && $request->localidad && $request->localidad !== 'todas') {
            $query->where('localidad', $request->localidad);
        }

        $universidades = $query->orderBy('clave')
            ->get(['id', 'clave', 'direccion', 'tipo', 'duracion']);

        $universidades = $universidades->map(function($uni) {
            $uni->nombre_completo = $uni->clave . ' - ' . $uni->direccion;
            if ($uni->tipo) {
                $uni->nombre_completo .= ' (' . $uni->tipo . ')';
            }
            return $uni;
        });

        return response()->json($universidades);
    }

    // ========== MÉTODOS PARA CUPONES ==========

    public function getCuponesDisponibles()
    {
        $cupones = Cupon::where('estatus', 'activo')
            ->where('usado', false)
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'tipo_descuento', 'valor_descuento']);

        return response()->json($cupones);
    }

    public function validarCupon(Request $request)
    {
        $request->validate([
            'cupon_id' => 'required|exists:cupones,id'
        ]);

        $cupon = Cupon::find($request->cupon_id);

        if (!$cupon || $cupon->estatus !== 'activo' || $cupon->usado) {
            return response()->json([
                'valido' => false,
                'mensaje' => 'El cupón no es válido o ya ha sido utilizado'
            ]);
        }

        $descuentoTexto = $cupon->tipo_descuento == 'porcentaje'
            ? "{$cupon->valor_descuento}% de descuento"
            : "$" . number_format($cupon->valor_descuento, 2) . " de descuento";

        return response()->json([
            'valido' => true,
            'mensaje' => "¡Cupón válido! {$descuentoTexto}"
        ]);
    }

    /**
     * Genera un reporte PDF con todos los detalles del estudiante
     */
    public function generarReportePDF($id)
    {
        // dompdf consume bastante memoria con historiales largos (exámenes,
        // videos); el límite por defecto de PHP (128M) no siempre alcanza.
        ini_set('memory_limit', '512M');

        $user = User::with(['estudiante.escuelaProcedencia', 'estudiante.universidadInteres', 'estudiante.pagos'])
            ->findOrFail($id);

        if (!$user->estudiante) {
            return redirect()->route('admin.estudiantes.index')
                ->with('error', 'Estudiante no encontrado');
        }

        $estudiante = $user->estudiante;
        $usuario = $user;

        $estadisticasTiempo = TiempoEstudio::getEstadisticasCompletas($estudiante->id);

        $tiempoTotalHoras = $estadisticasTiempo['total_horas'] ?? 0;
        $tiempoTotalMinutos = $estadisticasTiempo['total_minutos'] ?? 0;
        $totalSesiones = $estadisticasTiempo['total_sesiones'] ?? 0;
        $diasActivos = $estadisticasTiempo['dias_estudiados'] ?? 0;

        $ultimoRegistro = TiempoEstudio::where('estudiante_id', $estudiante->id)
            ->whereNotNull('ultima_actividad')
            ->orderBy('ultima_actividad', 'desc')
            ->first();
        $ultimaActividad = $ultimoRegistro ? $ultimoRegistro->ultima_actividad->format('d/m/Y H:i') : '—';

        $estudioDiario = TiempoEstudio::where('estudiante_id', $estudiante->id)
            ->where('fecha', '>=', Carbon::now()->subDays(7))
            ->orderBy('fecha', 'asc')
            ->get()
            ->map(function($item) {
                $minutos = (int)($item->minutos_estudiados ?? 0);
                $horas = $minutos > 0 ? round($minutos / 60, 1) : 0;

                return (object)[
                    'dia' => Carbon::parse($item->fecha)->format('D'),
                    'fecha' => Carbon::parse($item->fecha)->format('d/m/Y'),
                    'horas_estudiadas' => $horas,
                    'minutos_estudiados' => $minutos,
                    'segundos' => (int)($item->segundos_estudiados ?? 0)
                ];
            });

        if ($estudioDiario->isEmpty()) {
            $estudioDiario = collect();
            for ($i = 6; $i >= 0; $i--) {
                $fecha = Carbon::now()->subDays($i);
                $estudioDiario->push((object)[
                    'dia' => $fecha->format('D'),
                    'fecha' => $fecha->format('d/m/Y'),
                    'horas_estudiadas' => 0,
                    'minutos_estudiados' => 0,
                    'segundos' => 0
                ]);
            }
        }

        $examenes = ExamenRealizado::where('estudiante', $estudiante->id)
            ->with('examenGenerado')
            ->orderBy('fecha_fin', 'desc')
            ->get()
            ->map(function($examenRealizado) {
                $examenGen = $examenRealizado->examenGenerado;
                $tipoExamen = $examenGen->tipo_examen ?? 'general';
                $nombreReferencia = '';

                switch (strtolower($tipoExamen)) {
                    case 'materia':
                        $nombreReferencia = $examenGen->materia->nombre ?? 'Materia';
                        $tipoTexto = 'Por Materia';
                        break;
                    case 'curso':
                        $nombreReferencia = $examenGen->curso->nombre ?? 'Curso';
                        $tipoTexto = 'Por Curso';
                        break;
                    case 'simulacion':
                    case 'simulación':
                        $nombreReferencia = $examenGen->titulo ?? 'Simulación';
                        $tipoTexto = 'Simulación';
                        break;
                    default:
                        $nombreReferencia = $examenGen->titulo ?? 'Examen';
                        $tipoTexto = ucfirst($tipoExamen);
                        break;
                }

                $examenRealizado->tipo_examen = $tipoExamen;
                $examenRealizado->nombre_referencia = $nombreReferencia;
                $examenRealizado->tipo_texto = $tipoTexto;
                $examenRealizado->examen_nombre = $examenGen->titulo ?? 'Examen';
                $examenRealizado->fecha_completa = $examenRealizado->fecha_fin
                    ? Carbon::parse($examenRealizado->fecha_fin)->format('d/m/Y')
                    : '—';

                return $examenRealizado;
            });

        $examenesPorTipo = [
            'simulacion' => $examenes->filter(function($e) {
                return in_array(strtolower($e->tipo_examen), ['simulacion', 'simulación']);
            })->count(),
            'materia' => $examenes->filter(function($e) {
                return strtolower($e->tipo_examen) == 'materia';
            })->count(),
            'curso' => $examenes->filter(function($e) {
                return strtolower($e->tipo_examen) == 'curso';
            })->count(),
        ];

        $promedioCalificaciones = $examenes->isNotEmpty() ? round($examenes->avg('calificacion'), 1) : 0;

        $promedioPorTipo = [
            'simulacion' => $examenes->filter(function($e) {
                return in_array(strtolower($e->tipo_examen), ['simulacion', 'simulación']);
            })->isNotEmpty() ? round($examenes->filter(function($e) {
                return in_array(strtolower($e->tipo_examen), ['simulacion', 'simulación']);
            })->avg('calificacion'), 1) : 0,
            'materia' => $examenes->filter(function($e) {
                return strtolower($e->tipo_examen) == 'materia';
            })->isNotEmpty() ? round($examenes->filter(function($e) {
                return strtolower($e->tipo_examen) == 'materia';
            })->avg('calificacion'), 1) : 0,
            'curso' => $examenes->filter(function($e) {
                return strtolower($e->tipo_examen) == 'curso';
            })->isNotEmpty() ? round($examenes->filter(function($e) {
                return strtolower($e->tipo_examen) == 'curso';
            })->avg('calificacion'), 1) : 0,
        ];

        $totalVideos = Video::count();
        $progresosVideos = ProgresoVideo::where('estudiante_id', $estudiante->id)->get();
        $vistosCompletos = $progresosVideos->where('completado', true)->count();
        $videosEnProgreso = $progresosVideos->where('completado', false)->count();
        $porcentajeProgreso = $totalVideos > 0 ? round(($vistosCompletos / $totalVideos) * 100) : 0;

        $ultimosVideos = ProgresoVideo::where('estudiante_id', $estudiante->id)
            ->with('video')
            ->whereNotNull('fecha_visto')
            ->orderBy('fecha_visto', 'desc')
            ->limit(5)
            ->get();

        $data = compact(
            'estudiante',
            'usuario',
            'tiempoTotalHoras',
            'tiempoTotalMinutos',
            'estudioDiario',
            'totalSesiones',
            'ultimaActividad',
            'diasActivos',
            'examenes',
            'examenesPorTipo',
            'promedioPorTipo',
            'promedioCalificaciones',
            'totalVideos',
            'vistosCompletos',
            'videosEnProgreso',
            'porcentajeProgreso',
            'ultimosVideos'
        );

        $pdf = \PDF::loadView('administrador.estudiantes.reporte-pdf', $data);
        $pdf->setPaper('a4', 'portrait');

        $nombreArchivo = 'reporte_estudiante_' . $estudiante->id . '_' . date('Y-m-d') . '.pdf';
        return $pdf->download($nombreArchivo);
    }

    /**
     * Exporta un único PDF con el estatus de documentos del estudiante:
     * primera hoja con sus datos + la tabla de estatus (INE, CURP, acta de
     * nacimiento, certificado), seguida de los PDF que haya subido.
     */
    public function exportarDocumentosPDF($id)
    {
        // Fusionar varios PDF (portada + anexos) puede pesar bastante en memoria.
        ini_set('memory_limit', '512M');

        $user = User::with('estudiante')->findOrFail($id);

        if (!$user->estudiante) {
            return redirect()->route('admin.estudiantes.index')
                ->with('error', 'Estudiante no encontrado');
        }

        $estudiante = $user->estudiante;
        $usuario = $user;

        $documentosPorTipo = DocumentoEstudiante::delEstudiante($estudiante->id)
            ->get()
            ->keyBy('tipo');

        // Orden solicitado: INE, CURP, acta de nacimiento, certificado.
        $ordenTipos = [
            DocumentoEstudiante::TIPO_INE,
            DocumentoEstudiante::TIPO_CURP,
            DocumentoEstudiante::TIPO_ACTA_NACIMIENTO,
            DocumentoEstudiante::TIPO_CERTIFICADO_SECUNDARIA,
        ];

        $estatusTextos = [
            DocumentoEstudiante::ESTATUS_APROBADO  => 'Aceptado',
            DocumentoEstudiante::ESTATUS_RECHAZADO => 'Rechazado',
            DocumentoEstudiante::ESTATUS_PENDIENTE => 'Pendiente de revisión',
            'no_subido' => 'No subido',
        ];

        $filasDocumentos = [];
        $anexos = []; // rutas absolutas de los PDF que se anexarán después de la portada

        foreach ($ordenTipos as $tipo) {
            $doc = $documentosPorTipo->get($tipo);
            $estatus = $doc->estatus ?? 'no_subido';

            $filasDocumentos[] = [
                'label'         => DocumentoEstudiante::TIPOS_LABELS[$tipo] ?? $tipo,
                'estatus'       => $estatus,
                'estatus_texto' => $estatusTextos[$estatus] ?? ucfirst($estatus),
                'motivo'        => $estatus === DocumentoEstudiante::ESTATUS_RECHAZADO ? $doc->observaciones : null,
                'archivo'       => $doc->nombre_original ?? null,
            ];

            if ($doc && $doc->archivo) {
                $rutaAbsoluta = Storage::disk('public')->path($doc->archivo);
                if (file_exists($rutaAbsoluta)) {
                    $anexos[] = $rutaAbsoluta;
                }
            }
        }

        $totalAnexos = count($anexos);

        $pdf = \PDF::loadView('administrador.estudiantes.documentos-pdf', compact(
            'estudiante', 'usuario', 'filasDocumentos', 'totalAnexos'
        ));
        $pdf->setPaper('a4', 'portrait');
        $portadaBytes = $pdf->output();

        $nombreArchivo = 'documentos_estudiante_' . $estudiante->id . '_' . date('Y-m-d') . '.pdf';

        // Sin anexos: la portada sola ya es el PDF final, no hace falta fusionar.
        if ($totalAnexos === 0) {
            return response($portadaBytes, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            ]);
        }

        $rutaPortadaTemp = tempnam(sys_get_temp_dir(), 'sains_portada_') . '.pdf';
        file_put_contents($rutaPortadaTemp, $portadaBytes);

        try {
            $merged = $this->fusionarPdfs(array_merge([$rutaPortadaTemp], $anexos));
        } finally {
            @unlink($rutaPortadaTemp);
        }

        return response($merged, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
        ]);
    }

    /**
     * Fusiona varios archivos PDF (en el orden dado) en uno solo y devuelve
     * su contenido binario. Usa FPDI, que solo importa páginas de PDF sin
     * cifrar (justo lo que genera dompdf y lo que suben los estudiantes).
     */
    private function fusionarPdfs(array $rutasPdf): string
    {
        $pdf = new \setasign\Fpdi\Fpdi();

        foreach ($rutasPdf as $ruta) {
            $totalPaginas = $pdf->setSourceFile($ruta);
            for ($pagina = 1; $pagina <= $totalPaginas; $pagina++) {
                $plantillaId = $pdf->importPage($pagina);
                $tamano = $pdf->getTemplateSize($plantillaId);
                $pdf->AddPage($tamano['orientation'], [$tamano['width'], $tamano['height']]);
                $pdf->useTemplate($plantillaId);
            }
        }

        return $pdf->Output('S');
    }

    /**
     * Genera el certificado de finalización del estudiante, lo envía por
     * correo (adjunto en PDF) y crea una notificación dentro del sistema.
     * No se descarga para el administrador: solo se le avisa al estudiante.
     */
    public function generarCertificado($id)
    {
        ini_set('memory_limit', '512M');

        $user = User::with('estudiante')->findOrFail($id);

        if (!$user->estudiante) {
            return response()->json(['ok' => false, 'mensaje' => 'Estudiante no encontrado'], 404);
        }

        $estudiante = $user->estudiante;

        if (!$user->correo) {
            return response()->json(['ok' => false, 'mensaje' => 'El estudiante no tiene un correo registrado.'], 422);
        }

        $fecha = now()->translatedFormat('d \d\e F \d\e Y');

        $pdf = \PDF::loadView('administrador.estudiantes.certificado', compact('estudiante', 'fecha'));
        $pdf->setPaper('a4', 'landscape');
        $pdfBytes = $pdf->output();

        $nombreCompleto = trim($estudiante->nombre . ' ' . $estudiante->paterno);
        $nombreArchivo = 'certificado_' . $estudiante->id . '_' . date('Y-m-d') . '.pdf';

        // Se guarda en el sistema para poder descargarlo después, sin depender del correo.
        $rutaAlmacenamiento = 'certificados/' . $estudiante->id . '/certificado.pdf';
        Storage::disk('public')->put($rutaAlmacenamiento, $pdfBytes);
        $estudiante->update([
            'certificado_path' => $rutaAlmacenamiento,
            'certificado_generado_en' => now(),
        ]);

        try {
            $htmlCorreo = view('emails.certificado-generado', compact('estudiante'))->render();

            Mail::html($htmlCorreo, function ($message) use ($user, $nombreCompleto, $pdfBytes, $nombreArchivo) {
                $message->to($user->correo, $nombreCompleto)
                    ->subject('🎓 Tu certificado de finalización | SAINS Bachillerato')
                    ->from(config('mail.from.address', 'sains.bachillerato@gmail.com'), 'SAINS Bachillerato · ISSFAM')
                    ->attachData($pdfBytes, $nombreArchivo, ['mime' => 'application/pdf']);
            });
        } catch (\Exception $e) {
            Log::error('❌ [Certificado] Error al enviar correo: ' . $e->getMessage());
            return response()->json(['ok' => false, 'mensaje' => 'No se pudo enviar el correo con el certificado.'], 500);
        }

        Notificacion::enviar($user->id, [
            'tipo'    => 'certificado_generado',
            'titulo'  => '¡Tu certificado de finalización ya está listo!',
            'mensaje' => 'Te lo enviamos por correo electrónico.',
            'url'     => route('estudiante.perfil'),
            'icono'   => 'check',
            'color'   => 'green',
        ]);

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Certificado generado y enviado por correo a ' . $user->correo . '.',
        ]);
    }

    /**
     * Descarga el certificado ya generado (lado administrador).
     */
    public function descargarCertificado($id)
    {
        $estudiante = $this->resolverEstudiante($id);

        if (!$estudiante->certificado_path || !Storage::disk('public')->exists($estudiante->certificado_path)) {
            abort(404, 'Este estudiante aún no tiene un certificado generado.');
        }

        $nombreArchivo = 'certificado_' . $estudiante->id . '.pdf';

        return response()->file(Storage::disk('public')->path($estudiante->certificado_path), [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
        ]);
    }

    /* =========================================================
       DOCUMENTOS DEL ESTUDIANTE (ADMIN)
       ========================================================= */

    /**
     * Resuelve el estudiante a partir del ID de User.
     */
    private function resolverEstudiante($id): Estudiante
    {
        $user = User::with('estudiante')->findOrFail($id);

        if (!$user->estudiante) {
            abort(404, 'Este usuario no es un estudiante válido');
        }

        return $user->estudiante;
    }

    /**
     * Lista todos los documentos del estudiante.
     */
    public function documentosIndex($id)
    {
        $estudiante = $this->resolverEstudiante($id);

        $documentos = DocumentoEstudiante::delEstudiante($estudiante->id)
            ->orderBy('tipo')
            ->get();

        return response()->json([
            'ok'         => true,
            'documentos' => $documentos,
            'tipos'      => DocumentoEstudiante::TIPOS_LABELS,
        ]);
    }

    /**
     * Aprueba un documento del estudiante y envía correo + notificación.
     */
    public function aprobarDocumento(Request $request, $id, $documentoId)
    {
        $estudiante = $this->resolverEstudiante($id);

        $documento = DocumentoEstudiante::delEstudiante($estudiante->id)
            ->findOrFail($documentoId);

        $documento->update([
            'estatus'       => DocumentoEstudiante::ESTATUS_APROBADO,
            'observaciones' => $request->input('observaciones'),
        ]);

        // Correo al estudiante
        $this->enviarCorreoDocumentoAprobado($estudiante, $documento);

        // Notificación interna
        $this->notificarEstudianteDocumento($estudiante, $documento, 'aprobado');

        return response()->json([
            'ok'        => true,
            'mensaje'   => 'Documento aprobado correctamente.',
            'documento' => $documento->fresh(),
        ]);
    }

    /**
     * Rechaza un documento del estudiante y envía correo + notificación.
     */
    public function rechazarDocumento(Request $request, $id, $documentoId)
    {
        $estudiante = $this->resolverEstudiante($id);

        $request->validate([
            'observaciones' => 'required|string|max:500',
        ], [
            'observaciones.required' => 'Debes indicar el motivo del rechazo.',
            'observaciones.max'      => 'El motivo no debe superar los 500 caracteres.',
        ]);

        $documento = DocumentoEstudiante::delEstudiante($estudiante->id)
            ->findOrFail($documentoId);

        $documento->update([
            'estatus'       => DocumentoEstudiante::ESTATUS_RECHAZADO,
            'observaciones' => $request->input('observaciones'),
        ]);

        // Correo al estudiante
        $this->enviarCorreoDocumentoRechazado($estudiante, $documento, $request->input('observaciones'));

        // Notificación interna
        $this->notificarEstudianteDocumento($estudiante, $documento, 'rechazado', $request->input('observaciones'));

        return response()->json([
            'ok'        => true,
            'mensaje'   => 'Documento rechazado. El estudiante podrá volver a subirlo.',
            'documento' => $documento->fresh(),
        ]);
    }

    /**
     * Sirve el PDF inline para verlo en el modal.
     */
    public function verDocumento($id, $documentoId)
    {
        return $this->servirDocumento($id, $documentoId, 'inline');
    }

    /**
     * Fuerza la descarga del PDF.
     */
    public function descargarDocumento($id, $documentoId)
    {
        return $this->servirDocumento($id, $documentoId, 'attachment');
    }

    /**
     * Helper para servir el PDF con la disposición indicada.
     */
    private function servirDocumento($id, $documentoId, string $disposicion = 'inline')
    {
        $estudiante = $this->resolverEstudiante($id);

        $documento = DocumentoEstudiante::delEstudiante($estudiante->id)
            ->findOrFail($documentoId);

        $rutaAbsoluta = Storage::disk('public')->path($documento->archivo);

        if (!file_exists($rutaAbsoluta)) {
            abort(404, 'El archivo ya no está disponible.');
        }

        $nombre = $documento->nombre_original ?: basename($documento->archivo);

        return response()->file($rutaAbsoluta, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => $disposicion . '; filename="' . $nombre . '"',
            'X-Frame-Options'     => 'SAMEORIGIN',
        ]);
    }

    /* =========================================================
       CORREOS Y NOTIFICACIONES DE DOCUMENTOS
       ========================================================= */

    /**
     * Envía el correo al estudiante cuando un documento es aprobado.
     * Sigue el mismo patrón que PagoController (Mail::html + view()->render()).
     */
    private function enviarCorreoDocumentoAprobado($estudiante, $documento)
    {
        try {
            if (is_numeric($estudiante)) {
                $estudiante = Estudiante::find($estudiante);
                if (!$estudiante) {
                    Log::error('❌ [Documento] Estudiante no encontrado con ID: ' . $estudiante);
                    return false;
                }
            }

            if (is_numeric($documento)) {
                $documento = DocumentoEstudiante::find($documento);
                if (!$documento) {
                    Log::error('❌ [Documento] Documento no encontrado con ID: ' . $documento);
                    return false;
                }
            }

            $usuario = User::find($estudiante->usuario);
            if (!$usuario || !$usuario->correo) {
                Log::warning('⚠️ [Documento] Estudiante sin correo registrado: ' . $estudiante->id);
                return false;
            }

            $correoEstudiante = $usuario->correo;
            $nombreCompleto = trim($estudiante->nombre . ' ' . $estudiante->paterno);

            $tipoLabel = DocumentoEstudiante::TIPOS_LABELS[$documento->tipo] ?? 'Documento';

            $datos = [
                'estudiante'        => $estudiante,
                'documento'         => $documento,
                'tipoLabel'         => $tipoLabel,
                'fecha_aprobacion'  => now()->format('d/m/Y H:i:s'),
                'admin_nombre'      => auth()->user()->name ?? 'Administrador SAINS',
            ];

            $htmlContent = view('emails.documento-aprobado', $datos)->render();

            Mail::html($htmlContent, function ($message) use ($correoEstudiante, $nombreCompleto, $tipoLabel) {
                $message->to($correoEstudiante, $nombreCompleto)
                        ->subject("✅ Tu {$tipoLabel} fue aprobado | SAINS Bachillerato")
                        ->from(config('mail.from.address', 'sains.bachillerato@gmail.com'), 'SAINS Bachillerato · ISSFAM');
            });

            Log::info('📧 [Documento] Correo de APROBACIÓN enviado a: ' . $correoEstudiante . ' (' . $tipoLabel . ')');
            return true;

        } catch (\Exception $e) {
            Log::error('❌ [Documento] Error al enviar correo de aprobación: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envía el correo al estudiante cuando un documento es rechazado.
     */
    private function enviarCorreoDocumentoRechazado($estudiante, $documento, $motivo)
    {
        try {
            if (is_numeric($estudiante)) {
                $estudiante = Estudiante::find($estudiante);
                if (!$estudiante) {
                    Log::error('❌ [Documento] Estudiante no encontrado con ID: ' . $estudiante);
                    return false;
                }
            }

            if (is_numeric($documento)) {
                $documento = DocumentoEstudiante::find($documento);
                if (!$documento) {
                    Log::error('❌ [Documento] Documento no encontrado con ID: ' . $documento);
                    return false;
                }
            }

            $usuario = User::find($estudiante->usuario);
            if (!$usuario || !$usuario->correo) {
                Log::warning('⚠️ [Documento] Estudiante sin correo registrado: ' . $estudiante->id);
                return false;
            }

            $correoEstudiante = $usuario->correo;
            $nombreCompleto = trim($estudiante->nombre . ' ' . $estudiante->paterno);

            $tipoLabel = DocumentoEstudiante::TIPOS_LABELS[$documento->tipo] ?? 'Documento';

            $datos = [
                'estudiante'    => $estudiante,
                'documento'     => $documento,
                'tipoLabel'     => $tipoLabel,
                'motivo'        => $motivo ?? 'No se especificó un motivo',
                'fecha_rechazo' => now()->format('d/m/Y H:i:s'),
            ];

            $htmlContent = view('emails.documento-rechazado', $datos)->render();

            Mail::html($htmlContent, function ($message) use ($correoEstudiante, $nombreCompleto, $tipoLabel) {
                $message->to($correoEstudiante, $nombreCompleto)
                        ->subject("⚠️ Tu {$tipoLabel} necesita una corrección | SAINS Bachillerato")
                        ->from(config('mail.from.address', 'sains.bachillerato@gmail.com'), 'SAINS Bachillerato · ISSFAM');
            });

            Log::info('📧 [Documento] Correo de RECHAZO enviado a: ' . $correoEstudiante . ' (' . $tipoLabel . ')');
            return true;

        } catch (\Exception $e) {
            Log::error('❌ [Documento] Error al enviar correo de rechazo: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notificación in-app al estudiante sobre el cambio de estado del documento.
     */
    private function notificarEstudianteDocumento($estudiante, $documento, string $nuevoEstado, ?string $motivo = null): void
    {
        if (!$estudiante || !$estudiante->usuario) {
            return;
        }

        $tipoLabel = DocumentoEstudiante::TIPOS_LABELS[$documento->tipo] ?? 'Documento';

        try {
            if ($nuevoEstado === 'aprobado') {
                Notificacion::enviar($estudiante->usuario, [
                    'tipo'    => 'documento_aprobado',
                    'titulo'  => "¡Tu {$tipoLabel} fue aprobado!",
                    'mensaje' => 'Un requisito más de tu expediente está completo.',
                    'url'     => route('estudiante.perfil'),
                    'icono'   => 'check',
                    'color'   => 'green',
                ]);
            } elseif ($nuevoEstado === 'rechazado') {
                Notificacion::enviar($estudiante->usuario, [
                    'tipo'    => 'documento_rechazado',
                    'titulo'  => "Tu {$tipoLabel} necesita una corrección",
                    'mensaje' => $motivo ? "Motivo: {$motivo}" : 'Revisa tu documento y vuelve a subirlo.',
                    'url'     => route('estudiante.perfil'),
                    'icono'   => 'close',
                    'color'   => 'red',
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('⚠️ [Documento] No se pudo crear la notificación: ' . $e->getMessage());
        }
    }
}