<?php

namespace App\Http\Controllers;

use App\Models\CodigoPostal;
use Illuminate\Http\JsonResponse;

/** Autocompletado de domicilio: con el CP devuelve estado, municipio y colonias. */
class CodigoPostalController extends Controller
{
    public function show(string $cp): JsonResponse
    {
        if (!preg_match('/^\d{5}$/', $cp)) {
            return response()->json(['encontrado' => false, 'mensaje' => 'El código postal debe tener 5 dígitos.'], 422);
        }

        try {
            $datos = CodigoPostal::buscar($cp);
        } catch (\Throwable $e) {
            // Tabla aún sin cargar en el servidor: el alumno puede llenar a mano.
            $datos = null;
        }

        return $datos
            ? response()->json(['encontrado' => true] + $datos)
            : response()->json(['encontrado' => false, 'mensaje' => 'No encontramos ese código postal. Escribe tu domicilio a mano.']);
    }
}
