<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NewsletterController extends Controller
{
    /**
     * Procesa la suscripción al newsletter
     */
    public function subscribe(Request $request)
    {
        // Validar que el email sea válido
        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            
            Log::info('Nueva suscripción newsletter: ' . $request->email);
            
            return response()->json([
                'success' => true,
                'message' => '¡Gracias por suscribirte! Revisa tu correo para más información.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en newsletter: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Hubo un error, por favor intenta de nuevo.'
            ], 500);
        }
    }
}