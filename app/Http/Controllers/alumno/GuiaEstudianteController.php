<?php

namespace App\Http\Controllers\Alumno;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Models\Guia;
use Illuminate\Support\Facades\Auth;

/** Guías de estudio de la carrera del estudiante (ISSFAM). */
class GuiaEstudianteController extends Controller
{
    public function index()
    {
        $estudiante = Estudiante::with('carrera')->where('usuario', Auth::id())->first();

        if (!$estudiante) {
            return redirect()->route('estudiante.completar-perfil')->with('warning', 'Primero completa tu perfil');
        }

        // Tronco común (todos) + las de su carrera; sin carrera solo ve el tronco común.
        $guias = Guia::paraCarrera($estudiante->carrera_id)->orderBy('id')->get()
                ->map(fn ($g) => [
                    'id' => $g->id,
                    'titulo' => $g->titulo,
                    'descripcion' => $g->descripcion,
                    'tipo' => $g->tipo,
                    'url' => $g->archivo_url ?? $g->enlace,
                    'descarga_url' => $g->descarga_url,
                    'archivo_nombre' => $g->archivo ? basename($g->archivo) : null,
                    'es_archivo' => (bool) $g->archivo,
                    'tronco_comun' => $g->es_tronco_comun,
                ]);

        return \Inertia\Inertia::render('Estudiante/Guias', [
            'carrera' => $estudiante->carrera ? [
                'id' => $estudiante->carrera->id,
                'nombre' => $estudiante->carrera->nombre,
                'descripcion' => $estudiante->carrera->descripcion,
                'icono' => $estudiante->carrera->icono ?: 'fa-graduation-cap',
            ] : null,
            'guias' => $guias->values(),
        ]);
    }
}
