<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgresoVideo extends Model
{
    protected $table = 'progreso_videos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'estudiante_id', 
        'video_id', 
        'fecha_visto', 
        'completado',
        'ultimo_segundo', 
        'veces_visto'
    ];

    protected $casts = [
        'completado' => 'boolean',
        'fecha_visto' => 'datetime',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id', 'id');
    }

    public function video()
    {
        return $this->belongsTo(Video::class, 'video_id', 'id');
    }
}