<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarreraBachillerato;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Catálogo de carreras del Bachillerato Tecnológico (ISSFAM).
 * El alta/edición se hace en un modal del índice.
 */
class CarreraBachilleratoController extends Controller
{
    /** Íconos de Font Awesome que puede elegir el administrador. */
    public const ICONOS = [
        'fa-laptop-code', 'fa-laptop', 'fa-code', 'fa-people-group', 'fa-users', 'fa-briefcase',
        'fa-chart-column', 'fa-chart-line', 'fa-calculator', 'fa-scale-balanced', 'fa-gears',
        'fa-screwdriver-wrench', 'fa-bolt', 'fa-stethoscope', 'fa-utensils', 'fa-truck',
        'fa-building', 'fa-shield-halved', 'fa-language', 'fa-palette', 'fa-graduation-cap',
    ];

    public function index(Request $request)
    {
        $carreras = CarreraBachillerato::query()
            ->withCount(['guias', 'estudiantes'])
            ->when($request->filled('search'), fn ($q) => $q->where('nombre', 'like', '%' . $request->search . '%'))
            ->orderBy('id')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'descripcion' => $c->descripcion,
                'icono' => $c->icono,
                'guias_count' => $c->guias_count,
                'estudiantes_count' => $c->estudiantes_count,
            ]);

        return \Inertia\Inertia::render('Admin/Carreras/Index', [
            'carreras' => $carreras,
            'stats' => [
                'total' => CarreraBachillerato::count(),
                'guias' => \App\Models\Guia::count(),
                'estudiantes' => \App\Models\Estudiante::whereNotNull('carrera_id')->count(),
            ],
            'iconos' => self::ICONOS,
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request)
    {
        CarreraBachillerato::create($this->validar($request));

        return back()->with('success', 'Carrera creada.');
    }

    public function update(Request $request, CarreraBachillerato $carrera)
    {
        $carrera->update($this->validar($request, $carrera));

        return back()->with('success', 'Carrera actualizada.');
    }

    public function destroy(CarreraBachillerato $carrera)
    {
        if ($carrera->estudiantes()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay estudiantes inscritos en esta carrera. Desactívala para ocultarla.');
        }

        $carrera->guias()->get()->each(fn ($g) => app(GuiaController::class)->borrarArchivo($g));
        $carrera->delete();

        return back()->with('success', 'Carrera eliminada.');
    }

    private function validar(Request $request, ?CarreraBachillerato $carrera = null): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120', Rule::unique('carreras_bachillerato', 'nombre')->ignore($carrera?->id)],
            'descripcion' => ['nullable', 'string', 'max:600'],
            'icono' => ['nullable', 'string', Rule::in(self::ICONOS)],
        ], [], [
            'nombre' => 'nombre', 'descripcion' => 'descripción', 'icono' => 'ícono',
        ]);
        return $datos;
    }
}
