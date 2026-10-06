<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    protected $table = 'estudiante';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre', 'paterno', 'materno', 'fecha_nacimiento', 'sexo',
        'telefono', 'telefono_casa', 'escuela_procedencia', 'cupon',
        'fecha_inscripcion', 'plan_activo', 'universidad_interes', 'foto', 'usuario',
        'certificado_path', 'certificado_generado_en',
    ];

    protected $casts = [
        'plan_activo' => 'boolean',
        'fecha_nacimiento' => 'date',
        'fecha_inscripcion' => 'date',
        'certificado_generado_en' => 'datetime',
    ];

    public function getNombreCompletoAttribute()
    {
        $nombreCompleto = $this->nombre . ' ' . $this->paterno;
        if ($this->materno) {
            $nombreCompleto .= ' ' . $this->materno;
        }
        return $nombreCompleto;
    }

    public function getCorreoAttribute()
    {
        // "usuario" también es el nombre de la columna FK (int), por eso hay que
        // resolver la relación explícitamente en lugar de leer $this->usuario.
        return $this->usuario()->first()?->correo;
    }

    public function escuelaProcedencia()
    {
        return $this->belongsTo(Preparatoria::class, 'escuela_procedencia', 'id');
    }

    public function universidadInteres()
    {
        return $this->belongsTo(Universidad::class, 'universidad_interes', 'id');
    }

    public function progresoVideos()
    {
        return $this->hasMany(ProgresoVideo::class, 'estudiante_id', 'id');
    }

    public function examenesRealizados()
    {
        return $this->hasMany(ExamenRealizado::class, 'estudiante', 'id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'alumno_pago', 'id');
    }

    public function cuponUsado()
    {
        return $this->belongsTo(Cupon::class, 'cupon', 'codigo');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario', 'id');
    }

    public function tiempoEstudio()
    {
        return $this->hasMany(TiempoEstudio::class, 'estudiante_id', 'id');
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoEstudiante::class, 'estudiante_id', 'id');
    }

    public function getTiempoEstudioHoyAttribute()
    {
        return \App\Models\TiempoEstudio::getTiempoHoy($this->id);
    }
}