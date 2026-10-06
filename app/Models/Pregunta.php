<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pregunta extends Model
{
    protected $table = 'preguntas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_area',
        'pregunta',
        'justificacion'
    ];

    // Relación con áreas
    public function area()
    {
        return $this->belongsTo(AreaPregunta::class, 'id_area', 'id');
    }

    // Relación con apoyos
    public function apoyos()
    {
        return $this->hasMany(ApoyoPregunta::class, 'pregunta', 'id');
    }

    public function examenesGenerados()
    {
        return $this->belongsToMany(ExamenGenerado::class, 'apoyo_preguntas', 'pregunta', 'examen');
    }

    // Opciones de respuesta (3 a 4 por pregunta, una marcada como correcta)
    public function opciones()
    {
        return $this->hasMany(PreguntaOpcion::class, 'pregunta_id', 'id')->orderBy('orden');
    }

    public function opcionCorrecta()
    {
        return $this->hasOne(PreguntaOpcion::class, 'pregunta_id', 'id')->where('es_correcta', true);
    }

    // Método para obtener la justificación formateada (opcional pero útil)
    public function getJustificacionFormateadaAttribute()
    {
        if (empty($this->justificacion)) {
            $correcta = $this->relationLoaded('opciones')
                ? $this->opciones->firstWhere('es_correcta', true)
                : $this->opcionCorrecta;
            return "La respuesta correcta es: " . ($correcta?->texto ?? 'no disponible');
        }
        return $this->justificacion;
    }
}