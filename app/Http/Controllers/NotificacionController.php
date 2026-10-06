<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\DocumentoEstudiante;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificacionController extends Controller
{
    /* =========================================================
       VISTAS Y FEED
       ========================================================= */

    /**
     * Página completa de notificaciones (estudiante o admin).
     */
    public function index()
    {
        $user = Auth::user();
        $esAdmin = $user->isAdmin();

        $notificaciones = Notificacion::where('id_usuario', $user->id)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map->paraVista();

        return \Inertia\Inertia::render(
            $esAdmin ? 'Admin/Notificaciones' : 'Estudiante/Notificaciones',
            [
                'notificaciones' => $notificaciones,
                'noLeidas' => $notificaciones->where('leida', false)->count(),
            ]
        );
    }

    /**
     * Feed en JSON para la campana (últimas 12 + conteo de no leídas).
     */
    public function feed()
    {
        $user = Auth::user();

        return response()->json([
            'items' => Notificacion::where('id_usuario', $user->id)
                ->orderByDesc('created_at')
                ->limit(12)
                ->get()
                ->map->paraVista(),
            'no_leidas' => Notificacion::where('id_usuario', $user->id)->noLeidas()->count(),
        ]);
    }

    public function marcarLeida($id)
    {
        Notificacion::where('id_usuario', Auth::id())
            ->where('id', $id)
            ->whereNull('leida_at')
            ->update(['leida_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function marcarTodas()
    {
        Notificacion::where('id_usuario', Auth::id())
            ->whereNull('leida_at')
            ->update(['leida_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function eliminar($id)
    {
        Notificacion::where('id_usuario', Auth::id())->where('id', $id)->delete();

        return response()->json(['ok' => true]);
    }

    public function eliminarLeidas()
    {
        $n = Notificacion::where('id_usuario', Auth::id())
            ->whereNotNull('leida_at')
            ->delete();

        return response()->json(['ok' => true, 'eliminadas' => $n]);
    }

    /* =========================================================
       HELPERS PÚBLICOS (llamables desde otros controladores)
       ========================================================= */

    /**
     * Notifica al estudiante cuando sube (o reemplaza) un documento.
     * También notifica a TODOS los administradores para que lo revisen.
     *
     * @param  Estudiante|int  $estudiante
     * @param  DocumentoEstudiante|int  $documento
     * @param  bool  $esReemplazo  true si reemplazó uno existente
     */
    public static function notificarDocumentoSubido($estudiante, $documento, bool $esReemplazo = false): void
    {
        try {
            $estudiante  = self::resolverEstudiante($estudiante);
            $documento   = self::resolverDocumento($documento);

            if (!$estudiante || !$documento) {
                return;
            }

            $tipoLabel = DocumentoEstudiante::TIPOS_LABELS[$documento->tipo] ?? 'Documento';
            $nombreEstudiante = trim("{$estudiante->nombre} {$estudiante->paterno} {$estudiante->materno}");

            /* ---------- 1) Al estudiante ---------- */
            if ($estudiante->usuario) {
                Notificacion::enviar($estudiante->usuario, [
                    'tipo'    => 'documento_subido',
                    'titulo'  => $esReemplazo
                        ? "Reemplazaste tu {$tipoLabel}"
                        : "Subiste tu {$tipoLabel}",
                    'mensaje' => 'Tu documento quedó en revisión. Te avisaremos cuando sea aprobado o si necesita correcciones.',
                    'url'     => route('estudiante.perfil'),
                    'icono'   => 'upload',
                    'color'   => 'blue',
                ]);
            }

            /* ---------- 2) A los administradores ---------- */
            $admins = User::where('rol', 'admin')->orWhere('rol', 'administrador')->get();

            foreach ($admins as $admin) {
                Notificacion::enviar($admin->id, [
                    'tipo'    => 'documento_por_revisar',
                    'titulo'  => "{$tipoLabel} por revisar",
                    'mensaje' => "{$nombreEstudiante} subió su {$tipoLabel}. Revísalo y apruébalo o recházalo.",
                    'url'     => route('admin.estudiantes.show', $estudiante->usuario),
                    'icono'   => 'file-check',
                    'color'   => 'amber',
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('⚠️ [Notificación] No se pudo notificar documento subido: ' . $e->getMessage());
        }
    }

    /**
     * Notifica al estudiante que su documento fue aprobado.
     *
     * @param  Estudiante|int  $estudiante
     * @param  DocumentoEstudiante|int  $documento
     */
    public static function notificarDocumentoAprobado($estudiante, $documento): void
    {
        try {
            $estudiante = self::resolverEstudiante($estudiante);
            $documento  = self::resolverDocumento($documento);

            if (!$estudiante || !$documento || !$estudiante->usuario) {
                return;
            }

            $tipoLabel = DocumentoEstudiante::TIPOS_LABELS[$documento->tipo] ?? 'Documento';

            Notificacion::enviar($estudiante->usuario, [
                'tipo'    => 'documento_aprobado',
                'titulo'  => "¡Tu {$tipoLabel} fue aprobado!",
                'mensaje' => 'Un requisito más de tu expediente está completo. ¡Sigue así!',
                'url'     => route('estudiante.perfil'),
                'icono'   => 'check',
                'color'   => 'green',
            ]);
        } catch (\Exception $e) {
            Log::warning('⚠️ [Notificación] No se pudo notificar documento aprobado: ' . $e->getMessage());
        }
    }

    /**
     * Notifica al estudiante que su documento fue rechazado.
     *
     * @param  Estudiante|int  $estudiante
     * @param  DocumentoEstudiante|int  $documento
     * @param  string|null  $motivo
     */
    public static function notificarDocumentoRechazado($estudiante, $documento, ?string $motivo = null): void
    {
        try {
            $estudiante = self::resolverEstudiante($estudiante);
            $documento  = self::resolverDocumento($documento);

            if (!$estudiante || !$documento || !$estudiante->usuario) {
                return;
            }

            $tipoLabel = DocumentoEstudiante::TIPOS_LABELS[$documento->tipo] ?? 'Documento';
            $motivo    = $motivo ?: ($documento->observaciones ?? null);

            Notificacion::enviar($estudiante->usuario, [
                'tipo'    => 'documento_rechazado',
                'titulo'  => "Tu {$tipoLabel} necesita una corrección",
                'mensaje' => $motivo
                    ? "Motivo: {$motivo}. Corrígelo y vuelve a subirlo."
                    : 'Revisa tu documento y vuelve a subirlo desde tu perfil.',
                'url'     => route('estudiante.perfil'),
                'icono'   => 'close',
                'color'   => 'red',
            ]);
        } catch (\Exception $e) {
            Log::warning('⚠️ [Notificación] No se pudo notificar documento rechazado: ' . $e->getMessage());
        }
    }

    /**
     * Notifica al estudiante que su documento fue puesto en revisión de nuevo
     * (por ejemplo, cuando lo reemplaza después de un rechazo).
     */
    public static function notificarDocumentoEnRevision($estudiante, $documento): void
    {
        try {
            $estudiante = self::resolverEstudiante($estudiante);
            $documento  = self::resolverDocumento($documento);

            if (!$estudiante || !$documento || !$estudiante->usuario) {
                return;
            }

            $tipoLabel = DocumentoEstudiante::TIPOS_LABELS[$documento->tipo] ?? 'Documento';

            Notificacion::enviar($estudiante->usuario, [
                'tipo'    => 'documento_en_revision',
                'titulo'  => "Tu {$tipoLabel} está en revisión",
                'mensaje' => 'Nuestro equipo lo revisará en las próximas horas. Te avisaremos por aquí.',
                'url'     => route('estudiante.perfil'),
                'icono'   => 'clock',
                'color'   => 'amber',
            ]);
        } catch (\Exception $e) {
            Log::warning('⚠️ [Notificación] No se pudo notificar documento en revisión: ' . $e->getMessage());
        }
    }

    /* =========================================================
       HELPERS PRIVADOS
       ========================================================= */

    /**
     * Acepta un modelo Estudiante o un ID y devuelve el modelo.
     */
    private static function resolverEstudiante($estudiante): ?Estudiante
    {
        if ($estudiante instanceof Estudiante) {
            return $estudiante;
        }
        if (is_numeric($estudiante)) {
            return Estudiante::find($estudiante);
        }
        return null;
    }

    /**
     * Acepta un modelo DocumentoEstudiante o un ID y devuelve el modelo.
     */
    private static function resolverDocumento($documento): ?DocumentoEstudiante
    {
        if ($documento instanceof DocumentoEstudiante) {
            return $documento;
        }
        if (is_numeric($documento)) {
            return DocumentoEstudiante::find($documento);
        }
        return null;
    }
}