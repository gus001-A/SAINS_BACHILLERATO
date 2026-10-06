<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecursoClase extends Model
{
    protected $table = 'recursos_clase';
    public $timestamps = true;
    
    protected $fillable = [
        'id_clase',
        'titulo',
        'tipo',
        'url',
        'descripcion',
        'orden'
    ];
    
    protected $casts = [
        'orden' => 'integer'
    ];
    
    // Tipos de recursos permitidos
    const TIPOS = [
        'pdf' => '📄 PDF',
        'video_youtube' => '🎬 Video YouTube',
        'video_vimeo' => '🎬 Video Vimeo',
        'video_drive' => '☁️ Video Drive',
        'presentacion' => '📊 Presentación (PowerPoint/Canva)',
        'documento' => '📝 Documento',
        'podcast' => '🎙️ Podcast',
        'imagen' => '🖼️ Imagen',
        'enlace' => '🔗 Enlace externo',
        'otros' => '📁 Otros'
    ];
    
    // Iconos por tipo
    const ICONOS = [
        'pdf' => 'fa-file-pdf',
        'video_youtube' => 'fa-youtube',
        'video_vimeo' => 'fa-vimeo',
        'video_drive' => 'fa-google-drive',
        'presentacion' => 'fa-chalkboard',
        'documento' => 'fa-file-alt',
        'podcast' => 'fa-podcast',
        'imagen' => 'fa-image',
        'enlace' => 'fa-link',
        'otros' => 'fa-file'
    ];
    
    // Colores por tipo
    const COLORES = [
        'pdf' => '#ef4444',
        'video_youtube' => '#ff0000',
        'video_vimeo' => '#1ab7ea',
        'video_drive' => '#0f9d58',
        'presentacion' => '#f59e0b',
        'documento' => '#3b82f6',
        'podcast' => '#8b5cf6',
        'imagen' => '#10b981',
        'enlace' => '#6366f1',
        'otros' => '#6b7280'
    ];
    
    public function clase()
    {
        return $this->belongsTo(Clase::class, 'id_clase');
    }
    
    public function getTipoNombreAttribute()
    {
        return self::TIPOS[$this->tipo] ?? self::TIPOS['otros'];
    }
    
    public function getIconoAttribute()
    {
        return self::ICONOS[$this->tipo] ?? self::ICONOS['otros'];
    }
    
    public function getColorAttribute()
    {
        return self::COLORES[$this->tipo] ?? self::COLORES['otros'];
    }
    
    // Método para obtener el embed de YouTube
    public function getEmbedUrlAttribute()
    {
        if ($this->tipo === 'video_youtube') {
            preg_match('/(?:youtube\\.com\\/(?:[^\\/]+\\/.+\\/|(?:v|e(?:mbed)?)\\/|.*[?&]v=)|youtu\\.be\\/)([^"&?\\s]{11})/', $this->url, $match);
            return isset($match[1]) ? "https://www.youtube.com/embed/{$match[1]}" : null;
        }
        if ($this->tipo === 'video_vimeo') {
            preg_match('/vimeo\\.com\\/(?:.*#at=(\\d+)|(\\d+))/', $this->url, $match);
            $vimeoId = $match[1] ?? $match[2] ?? null;
            return $vimeoId ? "https://player.vimeo.com/video/{$vimeoId}" : null;
        }
        return null;
    }
    
    // Método para obtener el preview (si es imagen o PDF)
    public function getPreviewAttribute()
    {
        if ($this->tipo === 'imagen') {
            return $this->url;
        }
        if ($this->tipo === 'pdf') {
            return asset('images/pdf-icon.png');
        }
        return null;
    }
}