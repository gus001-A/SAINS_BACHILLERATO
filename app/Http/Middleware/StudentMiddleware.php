<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->rol === 'estudiante') {
            return $next($request);
        }
        
        return redirect()->route('home')->with('error', 'Acceso no autorizado');
    }
}