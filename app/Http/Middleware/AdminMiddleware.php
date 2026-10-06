<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'Debes iniciar sesión primero');
        }

        if (Auth::user()->rol !== 'Administrador' && Auth::user()->rol !== 'admin') {
            return redirect()->route('home')->with('error', 'No tienes permisos de administrador');
        }

        return $next($request);
    }
}