<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:usuario,correo'
        ], [
            'email.exists' => 'No encontramos una cuenta con este correo electrónico.'
        ]);

        $token = Str::random(60);
        
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => Carbon::now()]
        );

        try {
            // Obtener el usuario y su nombre
            $user = User::where('correo', $request->email)->first();
            $userName = $this->getUserName($user);
            
            $this->sendResetEmail($request->email, $token, $userName);
            
            return response()->json([
                'success' => true,
                'message' => 'Te hemos enviado un enlace para restablecer tu contraseña.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al enviar correo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el correo: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getUserName($user)
    {
        if (!$user) return 'usuario';
        
        // Si es administrador
        if ($user->administrador) {
            return $user->administrador->nombre_completo;
        }
        
        // Si es estudiante
        if ($user->estudiante) {
            return $user->estudiante->nombre_completo;
        }
        
        return $user->correo;
    }

    private function sendResetEmail($email, $token, $userName)
    {
        $resetLink = url('/reset-password/' . $token . '?email=' . urlencode($email));
        
        $subject = "Restablece tu contraseña | SAINS Bachillerato";
        
        $htmlContent = $this->getEmailHtml($resetLink, $userName);
        
        Mail::html($htmlContent, function ($message) use ($email, $subject) {
            $message->to($email)
                    ->subject($subject)
                    ->from(config('mail.from.address'), 'SAINS Bachillerato · ISSFAM');
        });
    }
    
    private function getEmailHtml($resetLink, $userName)
    {
        // Misma plantilla que el resto de correos (tablas + estilos en línea).
        return view('emails.recuperar-contrasena', [
            'resetLink' => $resetLink,
            'estudiante' => (object) ['nombre' => $userName, 'paterno' => ''],
        ])->render();
    }
}