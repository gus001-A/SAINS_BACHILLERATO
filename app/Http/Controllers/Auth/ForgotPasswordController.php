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
        
        $subject = "Recupera tu contraseña - SAINS";
        
        $htmlContent = $this->getEmailHtml($resetLink, $userName);
        
        Mail::html($htmlContent, function ($message) use ($email, $subject) {
            $message->to($email)
                    ->subject($subject)
                    ->from(config('mail.from.address'), 'SAINS');
        });
    }
    
    private function getEmailHtml($resetLink, $userName)
    {
        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Recuperar Contraseña - SAINS</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    margin: 0;
                    padding: 50px 30px;
                    line-height: 1.5;
                    min-height: 100vh;
                }
                
                .container {
                    max-width: 680px;
                    margin: 0 auto;
                }
                
                /* Tarjeta principal - MÁS ANCHA */
                .card {
                    background: #ffffff;
                    border-radius: 32px;
                    overflow: hidden;
                    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2), 0 10px 25px rgba(0, 0, 0, 0.1);
                    transition: transform 0.3s ease;
                }
                
                /* Header con gradiente y patrón */
                .card-header {
                    background: linear-gradient(135deg, #1e1b4b 0%, #4f46e5 50%, #7c3aed 100%);
                    padding: 56px 40px;
                    text-align: center;
                    position: relative;
                    overflow: hidden;
                }
                
                .card-header::before {
                    content: '';
                    position: absolute;
                    top: -20%;
                    right: -10%;
                    width: 300px;
                    height: 300px;
                    background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
                    border-radius: 50%;
                }
                
                .card-header::after {
                    content: '';
                    position: absolute;
                    bottom: -20%;
                    left: -10%;
                    width: 250px;
                    height: 250px;
                    background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
                    border-radius: 50%;
                }
                
                .logo-icon {
                    font-size: 48px;
                    margin-bottom: 20px;
                    position: relative;
                    z-index: 2;
                }
                
                .card-header h1 {
                    color: white;
                    font-size: 32px;
                    font-weight: 800;
                    margin: 0 0 12px 0;
                    letter-spacing: -0.5px;
                    position: relative;
                    z-index: 2;
                }
                
                .card-header p {
                    color: rgba(255, 255, 255, 0.9);
                    font-size: 16px;
                    margin: 0;
                    position: relative;
                    z-index: 2;
                }
                
                /* Cuerpo */
                .card-body {
                    padding: 56px 48px;
                }
                
                .greeting {
                    font-size: 26px;
                    font-weight: 800;
                    color: #1e293b;
                    margin-bottom: 24px;
                    letter-spacing: -0.3px;
                }
                
                .greeting-name {
                    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
                    -webkit-background-clip: text;
                    background-clip: text;
                    color: transparent;
                }
                
                .message {
                    color: #475569;
                    font-size: 16px;
                    line-height: 1.7;
                    margin-bottom: 20px;
                }
                
                .highlight {
                    color: #4f46e5;
                    font-weight: 700;
                    background: #eef2ff;
                    padding: 2px 8px;
                    border-radius: 8px;
                    display: inline-block;
                }
                
                /* Botón elegante */
                .button-wrapper {
                    text-align: center;
                    margin: 45px 0 40px;
                }
                
                .reset-button {
                    display: inline-block;
                    padding: 16px 48px;
                    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
                    color: white !important;
                    text-decoration: none;
                    border-radius: 60px;
                    font-weight: 700;
                    font-size: 16px;
                    letter-spacing: 0.5px;
                    box-shadow: 0 8px 20px rgba(79, 70, 229, 0.35);
                    transition: transform 0.2s ease, box-shadow 0.2s ease;
                    border: none;
                }
                
                /* Enlace */
                .token-box {
                    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                    border-radius: 20px;
                    padding: 24px 28px;
                    margin: 35px 0;
                    border: 1px solid #e2e8f0;
                }
                
                .token-label {
                    font-size: 11px;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 1.5px;
                    color: #64748b;
                    margin-bottom: 14px;
                    text-align: center;
                }
                
                .token-link {
                    font-size: 13px;
                    color: #4f46e5;
                    font-family: 'Courier New', 'SF Mono', monospace;
                    word-break: break-all;
                    background: white;
                    padding: 14px 18px;
                    border-radius: 14px;
                    border: 1px solid #e2e8f0;
                    text-align: center;
                }
                
                .token-link a {
                    color: #4f46e5;
                    text-decoration: none;
                    word-break: break-all;
                }
                
                /* Información importante - Diseño con tarjeta */
                .info-box {
                    background: #fffbeb;
                    border-radius: 20px;
                    padding: 28px;
                    margin: 35px 0;
                    border: 1px solid #fde047;
                }
                
                .info-title {
                    font-weight: 800;
                    color: #92400e;
                    font-size: 15px;
                    margin-bottom: 18px;
                    letter-spacing: 0.3px;
                    padding-bottom: 10px;
                    border-bottom: 2px solid #fde047;
                }
                
                .info-list {
                    list-style: none;
                    padding: 0;
                    margin: 0;
                }
                
                .info-list li {
                    color: #78350f;
                    font-size: 14px;
                    margin-bottom: 14px;
                    padding-left: 24px;
                    position: relative;
                    line-height: 1.5;
                }
                
                .info-list li:before {
                    content: '•';
                    position: absolute;
                    left: 6px;
                    color: #f59e0b;
                    font-weight: bold;
                    font-size: 18px;
                }
                
                .info-list li:last-child {
                    margin-bottom: 0;
                }
                
                /* Recomendaciones de seguridad - Diseño mejorado */
                .security-box {
                    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
                    border-radius: 20px;
                    padding: 28px;
                    margin: 35px 0;
                    border: 1px solid #86efac;
                }
                
                .security-title {
                    font-weight: 800;
                    color: #166534;
                    font-size: 15px;
                    margin-bottom: 18px;
                    letter-spacing: 0.3px;
                    padding-bottom: 10px;
                    border-bottom: 2px solid #86efac;
                }
                
                .security-grid {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 12px;
                }
                
                .security-item {
                    color: #14532d;
                    font-size: 13px;
                    padding: 8px 12px;
                    padding-left: 28px;
                    position: relative;
                    background: rgba(255,255,255,0.6);
                    border-radius: 12px;
                }
                
                .security-item:before {
                    content: '✓';
                    position: absolute;
                    left: 10px;
                    color: #22c55e;
                    font-weight: bold;
                    font-size: 14px;
                }
                
                /* Firma */
                .signature {
                    margin-top: 45px;
                    padding-top: 35px;
                    border-top: 2px solid #eef2ff;
                    text-align: center;
                }
                
                .signature-text {
                    color: #475569;
                    font-size: 15px;
                    margin-bottom: 10px;
                }
                
                .signature-team {
                    font-weight: 800;
                    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
                    -webkit-background-clip: text;
                    background-clip: text;
                    color: transparent;
                    font-size: 18px;
                    margin-top: 12px;
                }
                
                /* Footer */
                .card-footer {
                    background: #fafcff;
                    padding: 36px 48px;
                    text-align: center;
                    border-top: 1px solid #eef2ff;
                }
                
                hr {
                    border: none;
                    border-top: 1px solid #eef2ff;
                    margin: 24px 0;
                }
                
                .copyright {
                    color: #94a3b8;
                    font-size: 12px;
                    margin-top: 10px;
                }
                
                .copyright:first-of-type {
                    margin-top: 0;
                }
                
                /* Animación */
                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateY(30px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                
                .card {
                    animation: fadeInUp 0.6s ease;
                }
                
                /* Responsive */
                @media (max-width: 680px) {
                    body {
                        padding: 30px 20px;
                    }
                    
                    .card-body {
                        padding: 40px 28px;
                    }
                    
                    .card-footer {
                        padding: 28px 28px;
                    }
                    
                    .card-header {
                        padding: 40px 28px;
                    }
                    
                    .card-header h1 {
                        font-size: 26px;
                    }
                    
                    .reset-button {
                        padding: 14px 36px;
                        font-size: 15px;
                    }
                    
                    .greeting {
                        font-size: 22px;
                    }
                    
                    .security-grid {
                        grid-template-columns: 1fr;
                    }
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='card'>
                    <!-- Header -->
                    <div class='card-header'>
                        <div class='logo-icon'>🔐</div>
                        <h1>Recupera tu acceso</h1>
                        <p>Seguridad y confianza para tu preparación</p>
                    </div>
                    
                    <!-- Body -->
                    <div class='card-body'>
                        <div class='greeting'>
                            Hola, <span class='greeting-name'>" . htmlspecialchars($userName) . "</span>
                        </div>
                        
                        <div class='message'>
                            Recibimos una solicitud para restablecer la contraseña de tu cuenta en 
                            <span class='highlight'>SAINS</span>.
                        </div>
                        
                        <div class='message'>
                            Si realizaste esta solicitud, haz clic en el botón para crear una nueva contraseña:
                        </div>
                        
                        <!-- Botón -->
                        <div class='button-wrapper'>
                            <a href='{$resetLink}' class='reset-button'>
                                Restablecer contraseña
                            </a>
                        </div>
                        
                        <!-- Enlace -->
                        <div class='token-box'>
                            <div class='token-label'>
                                Enlace de recuperación
                            </div>
                            <div class='token-link'>
                                <a href='{$resetLink}'>{$resetLink}</a>
                            </div>
                        </div>
                        
                        <!-- Información importante -->
                        <div class='info-box'>
                            <div class='info-title'>
                                Información importante
                            </div>
                            <ul class='info-list'>
                                <li>Este enlace expirará en 60 minutos por seguridad</li>
                                <li>Si no solicitaste este cambio, ignora este mensaje</li>
                                <li>Tu contraseña actual no cambiará hasta que confirmes</li>
                            </ul>
                        </div>
                        
                        <!-- Recomendaciones de seguridad -->
                        <div class='security-box'>
                            <div class='security-title'>
                                Recomendaciones de seguridad
                            </div>
                            <div class='security-grid'>
                                <div class='security-item'>Usa una contraseña única</div>
                                <div class='security-item'>Combina mayúsculas, números y símbolos</div>
                                <div class='security-item'>Mínimo 8 caracteres</div>
                                <div class='security-item'>No compartas tu contraseña</div>
                                <div class='security-item'>Activa verificación en dos pasos</div>
                                <div class='security-item'>Cambia tu contraseña cada 3 meses</div>
                                <div class='security-item'>Evita información personal</div>
                                <div class='security-item'>No guardes en navegadores públicos</div>
                            </div>
                        </div>
                        
                        <!-- Firma -->
                        <div class='signature'>
                            <div class='signature-text'>
                                Sigue preparándote para ingresar a la universidad de tus sueños.
                            </div>
                            <div class='signature-text'>
                                Atentamente,
                            </div>
                            <div class='signature-team'>
                                El equipo de SAINS
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class='card-footer'>
                        <hr>
                        <div class='copyright'>
                            © 2025 SAINS. Todos los derechos reservados.
                        </div>
                        <div class='copyright'>
                            Curso de ingreso a la universidad | Convocatoria 2026
                        </div>
                        <div class='copyright'>
                            Este es un correo automático, por favor no responder.
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}