<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    protected $table = 'carreras';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre', 
        'tronco_id', 
        'id_asignatura_1', 
        'id_asignatura_2', 
        'id_asignatura_3',
        'calificacion_minima'  // Nuevo campo agregado
    ];

    protected $casts = [
        'calificacion_minima' => 'decimal:2',  // Para manejar decimales como 85.50
    ];

    // Relación con Tronco
    public function tronco()
    {
        return $this->belongsTo(Tronco::class, 'tronco_id', 'id');
    }

    // Relación con Asignatura 1
    public function asignatura1()
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura_1', 'id');
    }

    // Relación con Asignatura 2
    public function asignatura2()
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura_2', 'id');
    }

    // Relación con Asignatura 3
    public function asignatura3()
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura_3', 'id');
    }

    // Relación con Universidades
    public function universidades()
    {
        return $this->hasMany(Universidad::class, 'carrera_id', 'id');
    }

    /**
     * Verifica si una calificación cumple con el mínimo requerido
     * 
     * @param float $calificacion
     * @return bool
     */
    public function cumpleCalificacionMinima($calificacion)
    {
        if (is_null($this->calificacion_minima)) {
            return true; // Si no hay mínimo definido, siempre cumple
        }
        return floatval($calificacion) >= floatval($this->calificacion_minima);
    }

    /**
     * Obtiene la calificación mínima formateada
     * 
     * @return string
     */
    public function getCalificacionMinimaFormateadaAttribute()
    {
        if (is_null($this->calificacion_minima)) {
            return 'No definida';
        }
        return $this->calificacion_minima . '%';
    }

    /**
     * Obtiene el estado de la calificación mínima (texto y clase CSS)
     * 
     * @param float $calificacion
     * @return array
     */
    public function getEstadoCalificacion($calificacion)
    {
        if (is_null($this->calificacion_minima)) {
            return [
                'texto' => 'Sin requisito',
                'clase' => 'secondary'
            ];
        }

        $cumple = $this->cumpleCalificacionMinima($calificacion);
        
        return [
            'texto' => $cumple ? 'Cumple requisito' : 'No cumple requisito',
            'clase' => $cumple ? 'success' : 'danger'
        ];
    }

    /**
     * Scope para filtrar carreras por calificación mínima
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float $calificacion
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccesiblesPorCalificacion($query, $calificacion)
    {
        return $query->where(function($q) use ($calificacion) {
            $q->whereNull('calificacion_minima')
              ->orWhere('calificacion_minima', '<=', $calificacion);
        });
    }
}