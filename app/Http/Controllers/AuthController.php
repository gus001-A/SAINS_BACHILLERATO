<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Ruta a la que se redirige al estudiante tras autenticarse.
     */
    private function destinoEstudiante(User $user): string
    {
        $estudiante = Estudiante::where('usuario', $user->id)->first();

        if ($estudiante && $estudiante->plan_activo) {
            return route('estudiante.clases-premium');
        }

        return route('estudiante.dashboard');
    }

    private function guardarSesionLegacy(User $user): void
    {
        session([
            'MM_Username' => $user->correo,
            'MM_UserGroup' => $user->rol,
            'user_id' => $user->id,
            'user_nombre' => $user->nombre ?? $user->correo,
            'user_rol' => $user->rol,
        ]);
    }

    // Registro de estudiante - SOLO email y contraseña
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:usuario,correo',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'correo' => $request->email,
            'contraseña' => Hash::make($request->password),
            'rol' => 'estudiante',
        ]);

        \App\Models\Notificacion::enviar($user->id, [
            'tipo' => 'bienvenida',
            'titulo' => '¡Bienvenido a SAINS! 🎓',
            'mensaje' => 'Completa tu perfil y empieza a practicar con los simuladores gratuitos.',
            'url' => route('estudiante.completar-perfil'),
            'icono' => 'bell', 'color' => 'indigo',
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $this->guardarSesionLegacy($user);

        return redirect($this->destinoEstudiante($user));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('correo', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->contraseña)) {
            throw ValidationException::withMessages([
                'email' => 'El correo o la contraseña son incorrectos.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $this->guardarSesionLegacy($user);

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended($this->destinoEstudiante($user));
    }

    // Cerrar sesión
    public function logout(Request $request)
    {
        session()->forget(['MM_Username', 'MM_UserGroup', 'user_id', 'user_nombre', 'user_rol']);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Sesión cerrada correctamente');
    }
}
