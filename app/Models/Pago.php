<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'tipo_pago', 'alumno_pago', 'fecha_pago', 'monto_pago',
        'estatus', 'referencia_pago', 'comprobante', 'usuario_revision', 'fecha_aprueba', 'nota_usuario'
    ];

    protected $casts = [
        'fecha_pago' => 'datetime',
        'fecha_aprueba' => 'datetime',
    ];

    /**
     * Genera una referencia de pago única.
     * La columna `referencia_pago` es UNIQUE y NOT NULL, así que nunca debe
     * quedar vacía ni repetida.
     */
    public static function referenciaUnica(string $prefix = 'PAG'): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Za-z]/', '', $prefix)) ?: 'PAG';

        do {
            $ref = $prefix . now()->format('Ymd')
                . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        } while (static::where('referencia_pago', $ref)->exists());

        return $ref;
    }

    /**
     * Devuelve `$preferida` si es válida y libre; si no, genera una única.
     */
    public static function referenciaPreferida(?string $preferida, string $prefixFallback = 'PAG'): string
    {
        $preferida = trim((string) $preferida);

        if ($preferida !== '' && ! static::where('referencia_pago', $preferida)->exists()) {
            return $preferida;
        }

        return static::referenciaUnica($prefixFallback);
    }

    // 👇 CORREGIR: La relación debe ser belongsTo, no hasOne
    public function alumno()
    {
        return $this->belongsTo(Estudiante::class, 'alumno_pago', 'id');
    }

    public function revisor()
    {
        return $this->belongsTo(User::class, 'usuario_revision', 'id');
    }
    
    // Accessor para obtener el nombre del alumno
    public function getNombreAlumnoAttribute()
    {
        if ($this->relationLoaded('alumno') && $this->alumno) {
            return $this->alumno->nombre_completo;
        }
        return 'ID: ' . $this->alumno_pago;
    }
    
    // Accessor para obtener el correo del alumno
    public function getCorreoAlumnoAttribute()
    {
        if ($this->relationLoaded('alumno') && $this->alumno && $this->alumno->usuario) {
            return $this->alumno->usuario->correo;
        }
        return null;
    }
    
    // Accessor para obtener el nombre del revisor
    public function getNombreRevisorAttribute()
    {
        if (!$this->usuario_revision) {
            return null;
        }
        
        if ($this->relationLoaded('revisor') && $this->revisor) {
            if ($this->revisor->administrador) {
                return $this->revisor->administrador->nombre_completo;
            }
            return $this->revisor->correo;
        }
        return 'ID: ' . $this->usuario_revision;
    }
}