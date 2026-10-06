<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExamenGenerado extends Model
{
    use HasFactory;

    protected $table = 'Examen_generado';
    protected $primaryKey = 'id';
    
    public $timestamps = true;
    
    protected $fillable = [
        'tipo_examen',
        'numero_preguntas',
        'tiempo',
        'fecha_creacion',
    ];
    
    protected $casts = [
        'tiempo' => 'integer',
        'numero_preguntas' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Accessor para formato de fecha
    public function getFechaFormateadaAttribute()
    {
        return $this->created_at ? $this->created_at->format('d/m/Y H:i') : '—';
    }
    
    // RELACIÓN CON APOYOS (CORREGIDA)
    public function apoyos()
    {
        return $this->hasMany(ApoyoPregunta::class, 'examen', 'id');
    }
    
    // RELACIÓN DIRECTA CON PREGUNTAS A TRAVÉS DE APOYOS
    public function preguntas()
    {
        return $this->belongsToMany(Pregunta::class, 'apoyo_preguntas', 'examen', 'pregunta');
    }
}