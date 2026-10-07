<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarreraBachillerato;
use App\Models\Guia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Guías de estudio por carrera. Cada guía es un archivo (PDF, Word, imagen…)
 * o un enlace externo; el estudiante solo ve las de su carrera.
 */
class GuiaController extends Controller
{
    public function index(Request $request)
    {
        $guias = Guia::with('carrera:id,nombre')
            ->when($request->filled('search'), fn ($q) => $q->where('titulo', 'like', '%' . $request->search . '%'))
            ->when($request->carrera_id === 'tronco', fn ($q) => $q->whereNull('carrera_id'))
            ->when($request->filled('carrera_id') && $request->carrera_id !== 'tronco',
                fn ($q) => $q->where('carrera_id', $request->carrera_id))
            // Tronco común primero, luego por carrera.
            ->orderBy('id')
            ->paginate(15)->withQueryString()
            ->through(fn ($g) => [
                'id' => $g->id,
                'carrera_id' => $g->carrera_id,
                'carrera' => $g->carrera?->nombre ?? 'Tronco común',
                'tronco_comun' => $g->es_tronco_comun,
                'titulo' => $g->titulo,
                'descripcion' => $g->descripcion,
                'enlace' => $g->enlace,
                'archivo_url' => $g->archivo_url,
                'descarga_url' => $g->descarga_url,
                'archivo_nombre' => $g->archivo ? basename($g->archivo) : null,
                'tipo' => $g->tipo,
            ]);

        return \Inertia\Inertia::render('Admin/Guias/Index', [
            'guias' => $guias,
            'carreras' => CarreraBachillerato::orderBy('id')->get(['id', 'nombre'])
                ->map(fn ($c) => ['value' => $c->id, 'label' => $c->nombre]),
            'stats' => [
                'total' => Guia::count(),
                'tronco' => Guia::whereNull('carrera_id')->count(),
                'conArchivo' => Guia::whereNotNull('archivo')->count(),
                'carrerasSinGuia' => CarreraBachillerato::doesntHave('guias')->count(),
            ],
            'filters' => $request->only('search', 'carrera_id'),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request, true);
        $datos['archivo'] = $request->file('archivo')?->store('guias', 'public');
        Guia::create($datos);

        return back()->with('success', 'Guía creada.');
    }

    /** POST con _method=PUT para poder mandar archivo (multipart). */
    public function update(Request $request, Guia $guia)
    {
        $datos = $this->validar($request, false, $guia);

        if ($request->hasFile('archivo')) {
            $this->borrarArchivo($guia);
            $datos['archivo'] = $request->file('archivo')->store('guias', 'public');
        } elseif ($request->boolean('quitar_archivo')) {
            $this->borrarArchivo($guia);
            $datos['archivo'] = null;
        }

        if (empty($datos['archivo'] ?? $guia->archivo) && empty($datos['enlace'])) {
            return back()->withErrors(['archivo' => 'La guía necesita un archivo o un enlace.']);
        }

        $guia->update($datos);

        return back()->with('success', 'Guía actualizada.');
    }

    public function destroy(Guia $guia)
    {
        $this->borrarArchivo($guia);
        $guia->delete();

        return back()->with('success', 'Guía eliminada.');
    }

    public function borrarArchivo(Guia $guia): void
    {
        if ($guia->archivo) {
            Storage::disk('public')->delete($guia->archivo);
        }
    }

    private function validar(Request $request, bool $nueva, ?Guia $guia = null): array
    {
        $datos = $request->validate([
            // Vacío = tronco común (para todas las carreras).
            'carrera_id' => ['nullable', 'exists:carreras_bachillerato,id'],
            'titulo' => ['required', 'string', 'max:180'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'enlace' => ['nullable', 'url', 'max:500', $nueva ? 'required_without:archivo' : 'nullable'],
            'archivo' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,webp'],
        ], [
            'enlace.required_without' => 'Sube un archivo o escribe un enlace.',
            'archivo.max' => 'El archivo no puede pesar más de 20 MB.',
        ], [
            'carrera_id' => 'carrera', 'titulo' => 'título', 'descripcion' => 'descripción',
            'enlace' => 'enlace', 'archivo' => 'archivo',
        ]);
        unset($datos['archivo']);
        $datos['carrera_id'] = $datos['carrera_id'] ?? null;

        return $datos;
    }
}
