<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clase extends Model
{
    protected $table = 'clases';
    public $timestamps = true;
    
    protected $fillable = [
        'id_asignatura',
        'id_video', // Video del catálogo (los que se suben en "Videos")
        'num_clase',
        'nombre_clase',
        'link',   // Video principal (copia del link del video, para compatibilidad)
        'url',    // Recurso adicional (opcional, se mantiene por compatibilidad)
        'gratis', // true = clase de muestra abierta, false = requiere plan premium
    ];

    protected $casts = [
        'gratis' => 'boolean',
    ];

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura');
    }

    public function video()
    {
        return $this->belongsTo(Video::class, 'id_video');
    }
    
    // Relación con recursos adicionales
    public function recursos()
    {
        return $this->hasMany(RecursoClase::class, 'id_clase')->orderBy('orden', 'asc');
    }
    
    // Método para obtener recursos por tipo
    public function recursosPorTipo($tipo)
    {
        return $this->recursos()->where('tipo', $tipo)->get();
    }
    
    // Método para obtener solo videos (sin el principal)
    public function recursosVideos()
    {
        return $this->recursos()->whereIn('tipo', ['video_youtube', 'video_vimeo', 'video_drive'])->get();
    }
    
    // Método para obtener solo documentos
    public function recursosDocumentos()
    {
        return $this->recursos()->whereIn('tipo', ['pdf', 'documento', 'presentacion'])->get();
    }
}