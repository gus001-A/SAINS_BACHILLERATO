<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    protected $table = 'estudiante';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre', 'paterno', 'materno', 'fecha_nacimiento', 'sexo',
        'telefono', 'telefono_casa', 'escuela_procedencia', 'cupon',
        'fecha_inscripcion', 'plan_activo', 'universidad_interes', 'foto', 'usuario',
        'certificado_path', 'certificado_generado_en',
        'curp', 'calle_numero', 'colonia', 'codigo_postal', 'municipio', 'entidad_federativa',
        'carrera_id', 'ediciones_total', 'ediciones_dia', 'ediciones_fecha',
    ];

    /** Candado de datos personales: cambios permitidos al estudiante. */
    public const EDICIONES_POR_DIA = 2;
    public const EDICIONES_TOTALES = 5;

    protected $casts = [
        'plan_activo' => 'boolean',
        'fecha_nacimiento' => 'date',
        'fecha_inscripcion' => 'date',
        'certificado_generado_en' => 'datetime',
        'ediciones_fecha' => 'date',
        'ediciones_total' => 'integer',
        'ediciones_dia' => 'integer',
    ];

    public function getNombreCompletoAttribute()
    {
        $nombreCompleto = $this->nombre . ' ' . $this->paterno;
        if ($this->materno) {
            $nombreCompleto .= ' ' . $this->materno;
        }
        return $nombreCompleto;
    }

    public function getCorreoAttribute()
    {
        // "usuario" también es el nombre de la columna FK (int), por eso hay que
        // resolver la relación explícitamente en lugar de leer $this->usuario.
        return $this->usuario()->first()?->correo;
    }

    public function escuelaProcedencia()
    {
        return $this->belongsTo(Preparatoria::class, 'escuela_procedencia', 'id');
    }

    public function universidadInteres()
    {
        return $this->belongsTo(Universidad::class, 'universidad_interes', 'id');
    }

    public function progresoVideos()
    {
        return $this->hasMany(ProgresoVideo::class, 'estudiante_id', 'id');
    }

    public function examenesRealizados()
    {
        return $this->hasMany(ExamenRealizado::class, 'estudiante', 'id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'alumno_pago', 'id');
    }

    public function cuponUsado()
    {
        return $this->belongsTo(Cupon::class, 'cupon', 'codigo');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario', 'id');
    }

    public function tiempoEstudio()
    {
        return $this->hasMany(TiempoEstudio::class, 'estudiante_id', 'id');
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoEstudiante::class, 'estudiante_id', 'id');
    }

    public function carrera()
    {
        return $this->belongsTo(CarreraBachillerato::class, 'carrera_id');
    }

    /** Ediciones que lleva hoy (el contador diario se reinicia al cambiar de día). */
    public function edicionesHoy(): int
    {
        return $this->ediciones_fecha && $this->ediciones_fecha->isToday() ? (int) $this->ediciones_dia : 0;
    }

    /**
     * Estado del candado de datos para mostrarlo en el perfil.
     *
     * @return array{total:int, hoy:int, restantes_total:int, restantes_hoy:int, bloqueado:bool, motivo:?string}
     */
    public function candadoEdiciones(): array
    {
        $total = (int) $this->ediciones_total;
        $hoy = $this->edicionesHoy();
        $restTotal = max(0, self::EDICIONES_TOTALES - $total);
        $restHoy = min($restTotal, max(0, self::EDICIONES_POR_DIA - $hoy));

        $motivo = null;
        if ($restTotal === 0) {
            $motivo = 'Ya usaste los ' . self::EDICIONES_TOTALES . ' cambios permitidos. Tus datos quedaron bloqueados; si necesitas corregir algo, contacta a soporte.';
        } elseif ($restHoy === 0) {
            $motivo = 'Ya hiciste ' . self::EDICIONES_POR_DIA . ' cambios hoy. Podrás volver a editar tus datos mañana.';
        }

        return [
            'total' => $total,
            'hoy' => $hoy,
            'max_total' => self::EDICIONES_TOTALES,
            'max_dia' => self::EDICIONES_POR_DIA,
            'restantes_total' => $restTotal,
            'restantes_hoy' => $restHoy,
            'bloqueado' => $motivo !== null,
            'motivo' => $motivo,
        ];
    }

    /** Registra un cambio de datos hecho por el propio estudiante. */
    public function registrarEdicion(): void
    {
        $this->ediciones_dia = $this->edicionesHoy() + 1;
        $this->ediciones_fecha = now()->toDateString();
        $this->ediciones_total = (int) $this->ediciones_total + 1;
    }

    public function getTiempoEstudioHoyAttribute()
    {
        return \App\Models\TiempoEstudio::getTiempoHoy($this->id);
    }
}