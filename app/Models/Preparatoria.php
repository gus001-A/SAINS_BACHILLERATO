<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Preparatoria extends Model
{
    protected $table = 'preparatorias';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'estado', 'municipio', 'localidad', 'ambito', 'tipo',
        'servicio', 'clave', 'turno', 'centro_educativo', 'direccion'
    ];
}