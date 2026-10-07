<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Entrega comprobantes de pago y fotos de perfil a través de Laravel (ver
 * App\Support\ArchivoUrl). Acceso: administradores, o el propio estudiante.
 */
class ArchivoPrivadoController extends Controller
{
    public function comprobante(Request $request, Pago $pago)
    {
        $this->autorizar($request, (int) $pago->alumno_pago);

        return $this->entregar($pago->comprobante, 'comprobante-pago-' . $pago->id, $request);
    }

    public function foto(Request $request, Estudiante $estudiante)
    {
        $this->autorizar($request, (int) $estudiante->id);

        return $this->entregar($estudiante->foto, 'foto-' . $estudiante->id, $request);
    }

    private function autorizar(Request $request, int $estudianteId): void
    {
        $usuario = $request->user();
        if ($usuario->isAdmin()) {
            return;
        }

        $propio = Estudiante::where('usuario', $usuario->id)->value('id');
        abort_unless($propio && (int) $propio === $estudianteId, 403, 'No tienes permiso para ver este archivo.');
    }

    private function entregar(?string $ruta, string $nombre, Request $request)
    {
        $disco = Storage::disk('public');
        abort_if(!$ruta || !$disco->exists($ruta), 404, 'El archivo ya no está disponible.');

        $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));

        return $disco->response(
            $ruta,
            "{$nombre}.{$ext}",
            ['X-Frame-Options' => 'SAMEORIGIN'],
            $request->boolean('descargar') ? 'attachment' : 'inline'
        );
    }
}
