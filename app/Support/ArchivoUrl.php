<?php

namespace App\Support;

/**
 * URLs de archivos privados (comprobantes de pago y fotos de perfil) servidos por
 * Laravel en lugar de /storage. Así funcionan aunque el servidor no tenga el
 * symlink public/storage (Hostinger por FTP) y solo los ve quien tiene permiso.
 * Son relativas para no depender de APP_URL.
 */
class ArchivoUrl
{
    public static function comprobante($pago): ?string
    {
        return $pago?->comprobante
            ? route('archivos.comprobante', ['pago' => $pago->id, 'v' => self::version($pago->comprobante)], false)
            : null;
    }

    public static function foto($estudiante): ?string
    {
        return $estudiante?->foto
            ? route('archivos.foto', ['estudiante' => $estudiante->id, 'v' => self::version($estudiante->foto)], false)
            : null;
    }

    /** Cambia cuando cambia el archivo, para que el navegador no muestre la foto vieja. */
    private static function version(string $ruta): string
    {
        return substr(md5($ruta), 0, 8);
    }
}
