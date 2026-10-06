<?php


namespace App\Http\Controllers\Alumno;

use App\Http\Controllers\Controller;
use App\Models\DocumentoEstudiante;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentoEstudianteController extends Controller
{
    /**
     * Reglas de validación comunes para el PDF.
     */
    private const REGLAS_PDF = [
        'required',
        'file',
        'mimes:pdf',
        'mimetypes:application/pdf',
        'max:5120', // 5 MB máximo
    ];

    /**
     * Lista los documentos del estudiante autenticado.
     */
    public function index()
    {
        $estudiante = $this->estudianteAutenticado();

        $documentos = DocumentoEstudiante::delEstudiante($estudiante->id)
            ->orderBy('tipo')
            ->get();

        return response()->json([
            'ok'         => true,
            'documentos' => $documentos,
            'tipos'      => DocumentoEstudiante::TIPOS_LABELS,
        ]);
    }

    /**
     * Sube (o reemplaza) un documento PDF.
     * Acepta uno o varios archivos según el tipo recibido.
     */
    public function store(Request $request)
    {
        $estudiante = $this->estudianteAutenticado();

        $data = $request->validate([
            'tipo'    => ['required', 'string', Rule::in(DocumentoEstudiante::TIPOS)],
            'archivo' => self::REGLAS_PDF,
        ], [
            'tipo.in'          => 'El tipo de documento no es válido.',
            'archivo.required' => 'Debes adjuntar un archivo PDF.',
            'archivo.mimes'    => 'El archivo debe ser un PDF.',
            'archivo.mimetypes'=> 'El archivo debe ser un PDF válido.',
            'archivo.max'      => 'El PDF no debe pesar más de 5 MB.',
        ]);

        $file = $request->file('archivo');

        // Guardamos en storage/app/public/documentos/{estudiante_id}/
        $carpeta = 'documentos/' . $estudiante->id;
        $ruta    = $file->store($carpeta, 'public');

        // Si ya existía un documento de ese tipo, lo reemplazamos
        $existente = DocumentoEstudiante::delEstudiante($estudiante->id)
            ->where('tipo', $data['tipo'])
            ->first();

        if ($existente) {
            // Borra el PDF anterior
            if ($existente->archivo && Storage::disk('public')->exists($existente->archivo)) {
                Storage::disk('public')->delete($existente->archivo);
            }

            $existente->update([
                'archivo'         => $ruta,
                'nombre_original' => $file->getClientOriginalName(),
                'mime_type'       => $file->getMimeType(),
                'peso'            => $file->getSize(),
                'estatus'         => DocumentoEstudiante::ESTATUS_PENDIENTE,
                'observaciones'   => null,
            ]);

            $documento = $existente;
        } else {
            $documento = DocumentoEstudiante::create([
                'estudiante_id'   => $estudiante->id,
                'tipo'            => $data['tipo'],
                'archivo'         => $ruta,
                'nombre_original' => $file->getClientOriginalName(),
                'mime_type'       => $file->getMimeType(),
                'peso'            => $file->getSize(),
                'estatus'         => DocumentoEstudiante::ESTATUS_PENDIENTE,
            ]);
        }

        return response()->json([
            'ok'        => true,
            'mensaje'   => 'Documento subido correctamente.',
            'documento' => $documento,
        ], 201);
    }

    public function show(int $id)
    {
        $estudiante = $this->estudianteAutenticado();

        $documento = DocumentoEstudiante::delEstudiante($estudiante->id)
            ->findOrFail($id);

        $rutaAbsoluta = Storage::disk('public')->path($documento->archivo);

        if (!file_exists($rutaAbsoluta)) {
            abort(404, 'El archivo ya no está disponible.');
        }

        return response()->file($rutaAbsoluta, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . ($documento->nombre_original ?: basename($documento->archivo)) . '"',
            'X-Frame-Options'     => 'SAMEORIGIN',
        ]);
    }

    /**
     * Elimina un documento (solo si está pendiente o rechazado).
     */
    public function destroy(int $id)
    {
        $estudiante = $this->estudianteAutenticado();

        $documento = DocumentoEstudiante::delEstudiante($estudiante->id)
            ->findOrFail($id);

        if ($documento->estatus === DocumentoEstudiante::ESTATUS_APROBADO) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'No puedes eliminar un documento ya aprobado.',
            ], 403);
        }

        if ($documento->archivo && Storage::disk('public')->exists($documento->archivo)) {
            Storage::disk('public')->delete($documento->archivo);
        }

        $documento->delete();

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Documento eliminado.',
        ]);
    }

    /**
     * Devuelve el estudiante asociado al usuario autenticado.
     */
    private function estudianteAutenticado(): Estudiante
    {
        $estudiante = Estudiante::where('usuario', Auth::id())->first();

        abort_if(!$estudiante, 403, 'No se encontró un estudiante asociado a tu usuario.');

        return $estudiante;
    }
}