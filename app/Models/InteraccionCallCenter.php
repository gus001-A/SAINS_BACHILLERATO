<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InteraccionCallCenter extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'interacciones_call_center';

    protected $fillable = [
        'id_usuario_contacta',   // ID del administrador que contacta
        'id_estudiante',         // ID del estudiante contactado
        'fecha_contacto',        // Fecha del contacto
        'hora_contacto',         // Hora del contacto
        'nota',                  // Comentarios de la interacción
        'tipo_contacto',         // Ej: llamada, email, WhatsApp
        'estado_seguimiento',    // Ej: pendiente, en_proceso, finalizado
        'proximo_contacto',      // Fecha para próximo seguimiento
        'motivo_contacto',       // Motivo del contacto
        'resultado',             // Resultado de la interacción
    ];

    protected $casts = [
        'fecha_contacto' => 'date',
        'hora_contacto' => 'datetime:H:i:s',
        'proximo_contacto' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relación con el administrador que contacta
    public function administrador()
    {
        return $this->belongsTo(User::class, 'id_usuario_contacta');
    }

    // Relación con el estudiante contactado
    public function estudiante()
    {
        return $this->belongsTo(User::class, 'id_estudiante');
    }

    // Scope para filtrar por estado
    public function scopeEstado($query, $estado)
    {
        return $query->where('estado_seguimiento', $estado);
    }

    // Scope para filtrar por fechas
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_contacto', [$fechaInicio, $fechaFin]);
    }

    // Accessor para obtener nombre completo del administrador
    public function getNombreAdministradorAttribute()
    {
        if ($this->administrador && $this->administrador->administrador) {
            return $this->administrador->administrador->nombre . ' ' . 
                   $this->administrador->administrador->apellido_paterno;
        }
        return $this->administrador->name ?? 'N/A';
    }

    // Accessor para obtener nombre completo del estudiante
    public function getNombreEstudianteAttribute()
    {
        if ($this->estudiante && $this->estudiante->estudiante) {
            return $this->estudiante->estudiante->nombre . ' ' . 
                   $this->estudiante->estudiante->apellido_paterno;
        }
        return $this->estudiante->name ?? 'N/A';
    }

    // Accessor para obtener fecha y hora formateada
    public function getFechaHoraContactoAttribute()
    {
        return $this->fecha_contacto->format('d/m/Y') . ' ' . 
               ($this->hora_contacto ? date('H:i', strtotime($this->hora_contacto)) : '');
    }
}