<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApoyoPregunta extends Model
{
    protected $table = 'apoyo_preguntas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['examen', 'pregunta'];

    // Relación con ExamenGenerado
    public function examenGenerado()
    {
        return $this->belongsTo(ExamenGenerado::class, 'examen', 'id');
    }
    
    // Relación con Pregunta
    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class, 'pregunta', 'id');
    }

    
}