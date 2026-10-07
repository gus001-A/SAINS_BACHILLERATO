<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Guia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Entrega el archivo de una guía a través de Laravel (igual que los documentos
 * del alumno) en vez de un enlace directo a /storage. Así funciona aunque el
 * servidor no tenga el symlink public/storage (Hostinger por FTP no puede
 * correr `php artisan storage:link`) y solo lo ve quien tiene permiso.
 */
class GuiaArchivoController extends Controller
{
    public function show(Request $request, Guia $guia)
    {
        $usuario = $request->user();

        if (!$usuario->isAdmin()) {
            $estudiante = Estudiante::where('usuario', $usuario->id)->first();
            $permitida = $estudiante
                && ($guia->carrera_id === null || (int) $guia->carrera_id === (int) $estudiante->carrera_id);
            abort_unless($permitida, 403, 'Esta guía no está disponible para tu carrera.');
        }

        $disco = Storage::disk('public');
        abort_if(!$guia->archivo || !$disco->exists($guia->archivo), 404, 'El archivo de esta guía ya no está disponible.');

        $ext = strtolower(pathinfo($guia->archivo, PATHINFO_EXTENSION));
        $nombre = Str::slug($guia->titulo) ?: 'guia';

        return $disco->response(
            $guia->archivo,
            "{$nombre}.{$ext}",
            ['X-Frame-Options' => 'SAMEORIGIN'],
            $request->boolean('descargar') ? 'attachment' : 'inline'
        );
    }
}
