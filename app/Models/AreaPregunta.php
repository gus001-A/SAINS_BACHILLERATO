<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaPregunta extends Model
{
    protected $table = 'area_preguntas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function preguntas()
    {
        return $this->hasMany(Pregunta::class, 'id_area', 'id');
    }
}