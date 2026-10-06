<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TiempoEstudio extends Model
{
    protected $table = 'tiempo_estudio';
    
    public $timestamps = true;
    
    protected $fillable = [
        'estudiante_id',
        'fecha',
        'segundos_estudiados',
        'minutos_estudiados',
        'horas_estudiadas',
        'sesiones',
        'ultima_actividad'
    ];

    protected $casts = [
        'fecha' => 'date',
        'ultima_actividad' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relación con estudiante (usando la tabla 'estudiante')
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id', 'id');
    }

    public static function agregarTiempo($estudianteId, $segundos)
    {
        try {
            $hoy = Carbon::now()->toDateString();
            
            $tiempoEstudio = self::firstOrCreate([
                'estudiante_id' => $estudianteId,
                'fecha' => $hoy
            ]);
            
            // Incrementar segundos
            $tiempoEstudio->segundos_estudiados += $segundos;
            
            // Calcular minutos y horas
            $tiempoEstudio->minutos_estudiados = floor($tiempoEstudio->segundos_estudiados / 60);
            $tiempoEstudio->horas_estudiadas = floor($tiempoEstudio->segundos_estudiados / 3600);
            
            // Incrementar sesiones si pasaron más de 5 minutos desde la última actividad
            if ($tiempoEstudio->ultima_actividad) {
                $diferencia = abs(Carbon::now()->diffInMinutes($tiempoEstudio->ultima_actividad));
                if ($diferencia > 5) {
                    $tiempoEstudio->sesiones = ($tiempoEstudio->sesiones ?? 0) + 1;
                }
            } else {
                $tiempoEstudio->sesiones = ($tiempoEstudio->sesiones ?? 0) + 1;
            }
            
            $tiempoEstudio->ultima_actividad = Carbon::now();
            $tiempoEstudio->save();
            
            return $tiempoEstudio;
            
        } catch (\Exception $e) {
            \Log::error('Error en agregarTiempo: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function getTiempoHoy($estudianteId)
    {
        $hoy = Carbon::now()->toDateString();
        $tiempo = self::where('estudiante_id', $estudianteId)
                     ->where('fecha', $hoy)
                     ->first();
        
        return [
            'segundos' => $tiempo ? $tiempo->segundos_estudiados : 0,
            'minutos' => $tiempo ? $tiempo->minutos_estudiados : 0,
            'horas' => $tiempo ? round($tiempo->minutos_estudiados / 60, 1) : 0,
            'sesiones' => $tiempo ? $tiempo->sesiones : 0
        ];
    }
    
    // Nuevo método para obtener estadísticas completas
    public static function getEstadisticasCompletas($estudianteId)
    {
        $registros = self::where('estudiante_id', $estudianteId)
            ->orderBy('fecha', 'desc')
            ->get();
        
        $totalSegundos = $registros->sum('segundos_estudiados');
        $totalSesiones = $registros->sum('sesiones');
        $diasEstudiados = $registros->count();
        
        // Calcular racha actual
        $rachaActual = 0;
        $fechaEsperada = Carbon::now()->toDateString();
        
        foreach ($registros as $registro) {
            if ($registro->fecha == $fechaEsperada && $registro->minutos_estudiados > 0) {
                $rachaActual++;
                $fechaEsperada = Carbon::parse($fechaEsperada)->subDay()->toDateString();
            } else {
                break;
            }
        }
        
        return [
            'total_segundos' => $totalSegundos,
            'total_minutos' => round($totalSegundos / 60),
            'total_horas' => round($totalSegundos / 3600, 1),
            'total_sesiones' => $totalSesiones,
            'dias_estudiados' => $diasEstudiados,
            'racha_actual' => $rachaActual,
            'promedio_diario' => $diasEstudiados > 0 ? round(($totalSegundos / 60) / $diasEstudiados, 1) : 0,
        ];
    }
}