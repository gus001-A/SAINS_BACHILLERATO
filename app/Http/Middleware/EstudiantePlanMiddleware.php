<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EstudiantePlanMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'Debes iniciar sesión primero');
        }

        // Obtener el estudiante
        $user = Auth::user();
        $estudiante = $user->estudiante;

        // Si no es estudiante, redirigir
        if (!$estudiante) {
            return redirect()->route('home')->with('error', 'Acceso no autorizado');
        }

        // Verificar si tiene plan activo
        if (!$estudiante->plan_activo) {
            return redirect()->route('estudiante.dashboard')
                ->with('error', 'Necesitas adquirir el plan SAINS para acceder a esta sección');
        }

        return $next($request);
    }
}