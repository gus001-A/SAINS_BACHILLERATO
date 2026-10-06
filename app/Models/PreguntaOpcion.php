<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreguntaOpcion extends Model
{
    protected $table = 'pregunta_opciones';
    public $timestamps = false;

    protected $fillable = ['pregunta_id', 'texto', 'es_correcta', 'orden'];

    protected $casts = [
        'es_correcta' => 'boolean',
    ];

    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class, 'pregunta_id', 'id');
    }
}
