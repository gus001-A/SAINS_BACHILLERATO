<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Carrera del Bachillerato Tecnológico (ISSFAM). No confundir con
 * `Carrera`, que son las carreras universitarias del simulador de admisión.
 */
class CarreraBachillerato extends Model
{
    protected $table = 'carreras_bachillerato';

    protected $fillable = ['nombre', 'descripcion', 'icono', 'orden', 'activa'];

    protected $casts = [
        'activa' => 'boolean',
        'orden' => 'integer',
    ];

    private const CACHE_OFERTA = 'carreras_bachillerato.oferta';

    private const OFERTA_POR_DEFECTO = [
        ['id' => 1, 'nombre' => 'Informática Administrativa', 'descripcion' => null, 'icono' => 'fa-laptop-code'],
        ['id' => 2, 'nombre' => 'Administración de Recursos Humanos', 'descripcion' => null, 'icono' => 'fa-people-group'],
        ['id' => 3, 'nombre' => 'Administración', 'descripcion' => null, 'icono' => 'fa-chart-column'],
        ['id' => 4, 'nombre' => 'Programador', 'descripcion' => null, 'icono' => 'fa-code'],
    ];

    protected static function booted(): void
    {
        $limpiar = fn () => Cache::forget(self::CACHE_OFERTA);
        static::saved($limpiar);
        static::deleted($limpiar);
    }

    /**
     * Carreras activas para el landing y el login (compartidas por Inertia).
     * Si la tabla aún no existe en producción, no tumba el sitio.
     */
    public static function oferta(): array
    {
        try {
            return Cache::remember(self::CACHE_OFERTA, 600, fn () => static::activas()
                ->get(['id', 'nombre', 'descripcion', 'icono'])
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'nombre' => $c->nombre,
                    'descripcion' => $c->descripcion,
                    'icono' => $c->icono ?: 'fa-graduation-cap',
                ])->all());
        } catch (\Throwable $e) {
            // La tabla aún no existe (falta correr el SQL en el servidor): oferta por defecto.
            return self::OFERTA_POR_DEFECTO;
        }
    }

    public function guias()
    {
        return $this->hasMany(Guia::class, 'carrera_id');
    }

    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class, 'carrera_id');
    }

    public function scopeActivas($query)
    {
        // Ya no hay carreras ocultas ni orden manual: todas, en el orden en que se registraron.
        return $query->orderBy('id');
    }

    /** Opciones para selects: [{value, label}]. */
    public static function opciones(): array
    {
        return static::activas()->get(['id', 'nombre', 'descripcion', 'icono'])
            ->map(fn ($c) => [
                'value' => $c->id,
                'label' => $c->nombre,
                'descripcion' => $c->descripcion,
                'icono' => $c->icono ?: 'fa-graduation-cap',
            ])
            ->all();
    }
}
