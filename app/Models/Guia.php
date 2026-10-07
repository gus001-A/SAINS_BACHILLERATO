<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Guia extends Model
{
    protected $table = 'guias';

    protected $fillable = ['carrera_id', 'titulo', 'descripcion', 'archivo', 'enlace', 'orden', 'activa'];

    protected $casts = [
        'activa' => 'boolean',
        'orden' => 'integer',
    ];

    public function carrera()
    {
        return $this->belongsTo(CarreraBachillerato::class, 'carrera_id');
    }

    /** Sin carrera = guía de tronco común (la ven todos los estudiantes). */
    public function getEsTroncoComunAttribute(): bool
    {
        return $this->carrera_id === null;
    }

    /** Guías que le tocan a un estudiante: tronco común + las de su carrera. */
    public function scopeParaCarrera($query, ?int $carreraId)
    {
        return $query->where(fn ($q) => $q->whereNull('carrera_id')
            ->when($carreraId, fn ($q) => $q->orWhere('carrera_id', $carreraId)));
    }

    /**
     * URL para ver el archivo (relativa: no depende de APP_URL ni del symlink public/storage).
     */
    public function getArchivoUrlAttribute(): ?string
    {
        return $this->archivo ? route('guias.archivo', $this, false) : null;
    }

    /** URL que fuerza la descarga del archivo. */
    public function getDescargaUrlAttribute(): ?string
    {
        return $this->archivo ? route('guias.archivo', ['guia' => $this, 'descargar' => 1], false) : null;
    }

    /** Tipo para el ícono/preview en el frontend: pdf, imagen, documento o enlace. */
    public function getTipoAttribute(): string
    {
        if (!$this->archivo) {
            return 'enlace';
        }
        $ext = strtolower(pathinfo($this->archivo, PATHINFO_EXTENSION));

        return match (true) {
            $ext === 'pdf' => 'pdf',
            in_array($ext, ['jpg', 'jpeg', 'png', 'webp']) => 'imagen',
            default => 'documento',
        };
    }
}
