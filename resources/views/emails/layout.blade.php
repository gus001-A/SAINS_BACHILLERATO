@php
    /**
     * Layout base para correos transaccionales de SAINS Bachillerato · ISSFAM.
     * Diseño a prueba de clientes de correo: tablas, estilos en línea, sin flex/grid,
     * sin @keyframes ni backdrop-filter. Colores institucionales (azul marino + dorado).
     *
     * Slots esperados desde las vistas hijas:
     *   @section('badge')      texto de la etiqueta superior
     *   @section('titulo')     título grande del encabezado
     *   @section('subtitulo')  frase bajo el título
     *   @section('accent')     'indigo' (default) | 'green' | 'red' | 'amber'
     *   @section('content')    cuerpo del correo
     */
    $accent = trim($__env->yieldContent('accent', 'indigo'));
    $accents = [
        'indigo' => ['#1851ad', '#214784', '#eef3f9', '#1b3a6c'],
        'green'  => ['#16a34a', '#15803d', '#ecfdf5', '#166534'],
        'red'    => ['#dc2626', '#b91c1c', '#fef2f2', '#991b1b'],
        'amber'  => ['#d97706', '#b45309', '#fffbeb', '#92400e'],
    ];
    [$c1, $c2, $cBg, $cDark] = $accents[$accent] ?? $accents['indigo'];
@endphp
<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('titulo', 'SAINS Bachillerato')</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; -webkit-font-smoothing:antialiased; font-family:'Segoe UI', -apple-system, BlinkMacSystemFont, Helvetica, Arial, sans-serif; color:#1e293b;">
    <span style="display:none; font-size:1px; color:#f1f5f9;">@yield('preheader', 'Actualización de tu cuenta de SAINS Bachillerato')</span>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;">
        <tr>
            <td align="center" style="padding:32px 12px;">

                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:18px; overflow:hidden; box-shadow:0 12px 32px -12px rgba(15,23,42,0.18);">

                    <!-- Encabezado -->
                    <tr>
                        <td style="background-color:{{ $c1 }}; background-image:linear-gradient(135deg, {{ $c1 }} 0%, {{ $c2 }} 100%); padding:38px 40px 34px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="left">
                                        <span style="display:inline-block; background-color:rgba(255,255,255,0.18); color:#ffffff; font-size:11px; font-weight:700; letter-spacing:1px; text-transform:uppercase; padding:6px 14px; border-radius:999px;">@yield('badge', 'SAINS')</span>
                                        <div style="font-size:26px; font-weight:800; color:#ffffff; margin-top:18px; letter-spacing:-0.02em; line-height:1.2;">@yield('titulo', 'SAINS')</div>
                                        <div style="font-size:14px; color:rgba(255,255,255,0.9); margin-top:6px;">@yield('subtitulo')</div>
                                    </td>
                                    <td align="right" valign="top" width="140">
                                        <div style="width:128px; background-color:#ffffff; border-radius:14px; text-align:center; padding:8px 0;">
                                            <img src="{{ asset('images/bachillerato-nacional-sm.png') }}" width="108" alt="Bachillerato Nacional SAINS"
                                                 style="display:inline-block; width:108px; height:auto; border:0;">
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Cuerpo -->
                    <tr>
                        <td style="padding:36px 40px 12px;">
                            <p style="margin:0 0 18px; font-size:16px; color:#0f172a;">
                                Hola <strong>{{ $estudiante->nombre ?? '' }} {{ $estudiante->paterno ?? '' }}</strong>,
                            </p>
                            @yield('content')
                        </td>
                    </tr>

                    <!-- Ayuda -->
                    <tr>
                        <td style="padding:8px 40px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:12px;">
                                <tr>
                                    <td style="padding:16px 20px; font-size:13px; color:#475569;">
                                        ¿Necesitas ayuda? Escríbenos a
                                        <a href="mailto:sains.bachillerato@gmail.com" style="color:{{ $c1 }}; font-weight:600; text-decoration:none;">sains.bachillerato@gmail.com</a>
                                        o por WhatsApp al
                                        <a href="https://wa.me/527771886018" style="color:{{ $c1 }}; font-weight:600; text-decoration:none;">777 188 6018</a>
                                        y
                                        <a href="https://wa.me/527772505603" style="color:{{ $c1 }}; font-weight:600; text-decoration:none;">777 250 5603</a>.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Pie -->
                    <tr>
                        <td style="background-color:#0e2d66; padding:26px 40px; text-align:center; border-top:4px solid #f5b301;">
                            <div style="font-size:16px; font-weight:800; color:#ffffff; letter-spacing:1px;">SAINS Bachillerato · ISSFAM</div>
                            <div style="font-size:11px; color:rgba(255,255,255,0.62); margin-top:8px; line-height:1.7;">
                                Bachillerato Tecnológico con Formación Profesional para personal y beneficiarios de la Guardia Nacional<br>
                                © {{ date('Y') }} SAINS · Instituto de Seguridad Social para las Fuerzas Armadas Mexicanas<br>
                                Este es un correo automático, por favor no respondas a este mensaje.
                            </div>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>
