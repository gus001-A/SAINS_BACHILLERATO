<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Administrador extends Model
{
    protected $table = 'administradores';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'usuario_id',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'telefono',
        'sexo'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relación con el usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }
    
    // Accesor para nombre completo
    public function getNombreCompletoAttribute()
    {
        $nombreCompleto = $this->nombre . ' ' . $this->apellido_paterno;
        if ($this->apellido_materno) {
            $nombreCompleto .= ' ' . $this->apellido_materno;
        }
        return $nombreCompleto;
    }
}