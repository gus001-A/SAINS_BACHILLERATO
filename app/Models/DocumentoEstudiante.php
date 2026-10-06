<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoEstudiante extends Model
{
    protected $table = 'documento_estudiante';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'estudiante_id',
        'tipo',
        'archivo',
        'nombre_original',
        'mime_type',
        'peso',
        'estatus',
        'observaciones',
    ];

    protected $casts = [
        'peso'       => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* ------------------------------------------------------------------ */
    /*  Constantes de tipos de documento                                  */
    /* ------------------------------------------------------------------ */
    const TIPO_ACTA_NACIMIENTO       = 'acta_nacimiento';
    const TIPO_CURP                  = 'curp';
    const TIPO_CERTIFICADO_SECUNDARIA = 'certificado_secundaria';
    const TIPO_INE                   = 'ine';

    const TIPOS = [
        self::TIPO_ACTA_NACIMIENTO,
        self::TIPO_CURP,
        self::TIPO_CERTIFICADO_SECUNDARIA,
        self::TIPO_INE,
    ];

    const TIPOS_LABELS = [
        self::TIPO_ACTA_NACIMIENTO        => 'Acta de nacimiento',
        self::TIPO_CURP                   => 'CURP',
        self::TIPO_CERTIFICADO_SECUNDARIA => 'Certificado de secundaria',
        self::TIPO_INE                    => 'INE',
    ];

    /* ------------------------------------------------------------------ */
    /*  Constantes de estatus                                             */
    /* ------------------------------------------------------------------ */
    const ESTATUS_PENDIENTE = 'pendiente';
    const ESTATUS_APROBADO  = 'aprobado';
    const ESTATUS_RECHAZADO = 'rechazado';

    /* ------------------------------------------------------------------ */
    /*  Relaciones                                                        */
    /* ------------------------------------------------------------------ */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id', 'id');
    }

    /* ------------------------------------------------------------------ */
    /*  Accessors                                                         */
    /* ------------------------------------------------------------------ */
    public function getTipoLabelAttribute(): string
    {
        return self::TIPOS_LABELS[$this->tipo] ?? $this->tipo;
    }

    public function getPesoFormateadoAttribute(): string
    {
        $bytes = (int) $this->peso;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }

    public function getEsPdfAttribute(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                            */
    /* ------------------------------------------------------------------ */
    public function scopeDelEstudiante($query, int $estudianteId)
    {
        return $query->where('estudiante_id', $estudianteId);
    }

    public function scopePendientes($query)
    {
        return $query->where('estatus', self::ESTATUS_PENDIENTE);
    }
}