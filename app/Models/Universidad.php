<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Universidad extends Model
{
    protected $table = 'universidades';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'estado', 'municipio', 'localidad', 'carrera_id',
        'duracion', 'tipo', 'clave', 'direccion'
    ];

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id', 'id');
    }
}