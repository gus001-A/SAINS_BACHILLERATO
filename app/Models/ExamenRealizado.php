<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamenRealizado extends Model
{
    protected $table = 'examen_realizado';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'estudiante', 'fecha_inicio', 'hora_inicio', 'fecha_fin',
        'hora_fin', 'tiempo', 'calificacion', 'examen', 'intento', 'respuestas'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'respuestas' => 'array'
    ];

    public function estudianteRel()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante', 'id');
    }

    public function examenGenerado()
    {
        return $this->belongsTo(ExamenGenerado::class, 'examen', 'id');
    }

    // Agrega esto a tu modelo ExamenRealizado
    public function getEstudianteNombreAttribute()
    {
        if ($this->relationLoaded('estudianteRel') && $this->estudianteRel) {
            return trim(($this->estudianteRel->nombre ?? '') . ' ' . ($this->estudianteRel->paterno ?? ''));
        }
        
        $estudiante = Estudiante::find($this->estudiante);
        return $estudiante ? trim(($estudiante->nombre ?? '') . ' ' . ($estudiante->paterno ?? '')) : 'Estudiante #' . $this->estudiante;
    }

    public function getExamenTituloAttribute()
    {
        if ($this->relationLoaded('examenGenerado') && $this->examenGenerado) {
            return $this->examenGenerado->titulo ?? 'Examen #' . $this->examen;
        }
        
        $examen = ExamenGenerado::find($this->examen);
        return $examen ? ($examen->titulo ?? 'Examen #' . $this->examen) : 'Examen #' . $this->examen;
    }
}