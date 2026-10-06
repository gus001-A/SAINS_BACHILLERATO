<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class ResetPasswordController extends Controller
{
    public function showResetForm($token)
    {
        return \Inertia\Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => request()->get('email'),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:usuario,correo',
            'password' => 'required|min:6|confirmed',
            'token' => 'required'
        ], [
            'email.exists' => 'No encontramos una cuenta con este correo electrónico.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.'
        ]);

        try {
            // Verificar token
            $resetRecord = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->where('token', $request->token)
                ->first();

            if (!$resetRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token inválido o expirado. Solicita un nuevo enlace de recuperación.'
                ], 400);
            }

            // Verificar expiración (60 minutos)
            $createdAt = Carbon::parse($resetRecord->created_at);
            if (abs($createdAt->diffInMinutes(now())) > 60) {
                DB::table('password_reset_tokens')->where('email', $request->email)->delete();
                return response()->json([
                    'success' => false,
                    'message' => 'El enlace ha expirado. Por favor, solicita uno nuevo.'
                ], 400);
            }

            // Actualizar contraseña
            $user = User::where('correo', $request->email)->first();
            $user->contraseña = Hash::make($request->password);
            $user->save();

            // Eliminar token usado
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return response()->json([
                'success' => true,
                'message' => '¡Contraseña actualizada correctamente! Ahora puedes iniciar sesión.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al resetear contraseña: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al actualizar la contraseña. Intenta de nuevo.'
            ], 500);
        }
    }
}