<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionTimeout
{
    /**
     * Tiempo máximo de inactividad en minutos
     */
    protected $timeout = 30;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Si el usuario no está autenticado, continuar
        if (!Auth::check()) {
            return $next($request);
        }

        // Verificar si existe la última actividad
        $lastActivity = $request->session()->get('last_activity');
        
        if ($lastActivity !== null) {
            $inactiveTime = abs(now()->diffInMinutes($lastActivity));
            
            // Si superó el tiempo de inactividad
            if ($inactiveTime >= $this->timeout) {
                // Limpiar la sesión
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Redirigir con mensaje
                return redirect()->route('home')->with('error', 'Tu sesión ha expirado por inactividad');
            }
        }
        
        // Actualizar la última actividad
        $request->session()->put('last_activity', now());
        
        return $next($request);
    }
}