<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tronco extends Model
{
    protected $table = 'tronco';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function carreras()
    {
        return $this->hasMany(Carrera::class, 'tronco_id', 'id');
    }
}