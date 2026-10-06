<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecursoAdicional extends Model
{
    protected $table = 'recursos_adicionales';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'materia_nombre', 'tema', 'descripcion', 'link'
    ];
}