<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'id_usuario', 'tipo', 'titulo', 'mensaje', 'url', 'icono', 'color', 'leida_at',
    ];

    protected $casts = [
        'leida_at' => 'datetime',
    ];

    public function scopeNoLeidas($q)
    {
        return $q->whereNull('leida_at');
    }

    /**
     * Crea una notificación para un usuario. Nunca lanza excepción:
     * si la tabla aún no existe o algo falla, sólo lo registra en el log.
     */
    public static function enviar($idUsuario, array $data): ?self
    {
        if (! $idUsuario || ! Schema::hasTable('notificaciones')) {
            return null;
        }

        try {
            return static::create([
                'id_usuario' => $idUsuario,
                'tipo'       => $data['tipo'] ?? 'info',
                'titulo'     => $data['titulo'] ?? 'Notificación',
                'mensaje'    => $data['mensaje'] ?? '',
                'url'        => $data['url'] ?? null,
                'icono'      => $data['icono'] ?? 'bell',
                'color'      => $data['color'] ?? 'indigo',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Notificacion::enviar falló: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Envía la misma notificación a todos los administradores.
     */
    public static function enviarAdmins(array $data): void
    {
        if (! Schema::hasTable('notificaciones')) {
            return;
        }

        User::whereIn('rol', ['admin', 'Administrador'])
            ->pluck('id')
            ->each(fn ($id) => static::enviar($id, $data));
    }

    public function paraVista(): array
    {
        return [
            'id'         => $this->id,
            'tipo'       => $this->tipo,
            'grupo_tipo' => $this->grupoTipo(),
            'titulo'     => $this->titulo,
            'mensaje'    => $this->mensaje,
            'url'        => $this->url,
            'icono'      => $this->icono,
            'color'      => $this->color,
            'leida'      => $this->leida_at !== null,
            'creada_iso' => optional($this->created_at)->toIso8601String(),
            'fecha'      => optional($this->created_at)->diffForHumans(),
            'fecha_completa' => optional($this->created_at)->format('d/m/Y H:i'),
            'grupo'      => $this->grupoFecha(),
        ];
    }

    /** Categoría para filtrar en la página de notificaciones (pagos | cuenta). */
    private function grupoTipo(): string
    {
        $t = (string) $this->tipo;
        if (str_contains($t, 'pago') || str_contains($t, 'comprobante') || str_contains($t, 'mercadopago')) {
            return 'pagos';
        }
        return 'cuenta';
    }

    /** Etiqueta de agrupación por fecha (Hoy / Ayer / Esta semana / dd/mm/aaaa). */
    private function grupoFecha(): string
    {
        $f = $this->created_at;
        if (! $f) {
            return 'Antes';
        }
        if ($f->isToday()) {
            return 'Hoy';
        }
        if ($f->isYesterday()) {
            return 'Ayer';
        }
        if ($f->greaterThanOrEqualTo(now()->subDays(7))) {
            return 'Esta semana';
        }
        return $f->format('d/m/Y');
    }
}
