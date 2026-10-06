<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Estudiante; // Importar modelo Estudiante
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Buscar por google_id o por correo
            $user = User::where('google_id', $googleUser->getId())
                        ->orWhere('correo', $googleUser->getEmail())
                        ->first();

            if ($user) {
                if (!$user->google_id) {
                    $user->google_id = $googleUser->getId();
                    $user->save();
                }
            } else {
                $user = User::create([
                    'correo'     => $googleUser->getEmail(),
                    'google_id'  => $googleUser->getId(),
                    'contraseña' => bcrypt(uniqid()),
                    'rol'        => 'estudiante',
                ]);
            }

            Auth::login($user);

            // ⭐ Verificar rol del usuario
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            
            // ⭐ Para estudiantes: verificar si tiene plan activo
            if ($user->isEstudiante()) {
                $estudiante = Estudiante::where('usuario', $user->id)->first();
                $tienePlanActivo = $estudiante && $estudiante->plan_activo;
                
                // Si tiene plan activo, redirigir directamente a clases premium
                if ($tienePlanActivo) {
                    return redirect()->route('estudiante.clases-premium')
                        ->with('info', 'Bienvenido de vuelta. ¡Disfruta de tus clases premium!');
                }
                
                // Si no tiene plan activo, ir al dashboard normal
                return redirect()->route('estudiante.dashboard');
            }

            // Fallback por si algo sale mal
            return redirect('/');

        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Error al iniciar sesión: ' . $e->getMessage());
        }
    }
}