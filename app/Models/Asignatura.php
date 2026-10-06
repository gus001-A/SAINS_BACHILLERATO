<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asignatura extends Model
{
    protected $table = 'asignatura';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    // Relación polimórfica inversa desde Carrera
    public function carrerasComoMateria1()
    {
        return $this->hasMany(Carrera::class, 'id_asignatura_1', 'id');
    }

    public function carrerasComoMateria2()
    {
        return $this->hasMany(Carrera::class, 'id_asignatura_2', 'id');
    }

    public function carrerasComoMateria3()
    {
        return $this->hasMany(Carrera::class, 'id_asignatura_3', 'id');
    }

    public function clases()
    {
        return $this->hasMany(Clase::class, 'id_asignatura', 'id');
    }
}