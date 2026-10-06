<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $table = 'videos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'materia', 'tema', 'titulo', 'link', 'duracion', 'plan'
    ];

    protected $casts = [
        'plan' => 'boolean',
    ];

    public function progresos()
    {
        return $this->hasMany(ProgresoVideo::class, 'video_id', 'id');
    }
}