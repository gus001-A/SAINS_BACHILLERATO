@extends('emails.layout')

@section('accent', 'indigo')
@section('preheader', 'Restablece tu contraseña con el enlace de este correo. Vence en 60 minutos.')
@section('badge', 'Seguridad de tu cuenta')
@section('titulo', 'Restablece tu contraseña')
@section('subtitulo', 'Recibimos una solicitud para cambiar tu contraseña')

@section('content')
    <p style="margin:0 0 18px; font-size:15px; line-height:1.65; color:#334155;">
        Alguien (esperamos que tú) pidió restablecer la contraseña de tu cuenta en
        <strong>SAINS Bachillerato · ISSFAM</strong>. Para crear una nueva, haz clic en el botón:
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:10px 0 22px;">
                <a href="{{ $resetLink }}"
                   style="display:inline-block; background-color:#1851ad; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700; padding:15px 38px; border-radius:12px;">
                    Crear nueva contraseña
                </a>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fffbeb; border:1px solid #fde68a; border-radius:12px;">
        <tr>
            <td style="padding:14px 18px; font-size:13px; color:#92400e; line-height:1.6;">
                <strong>El enlace vence en 60 minutos.</strong> Si no pediste este cambio, ignora este correo:
                tu contraseña actual sigue funcionando.
            </td>
        </tr>
    </table>

    <p style="margin:18px 0 6px; font-size:12px; color:#64748b;">
        ¿El botón no funciona? Copia y pega este enlace en tu navegador:
    </p>
    <p style="margin:0 0 18px; font-size:12px; word-break:break-all;">
        <a href="{{ $resetLink }}" style="color:#1851ad;">{{ $resetLink }}</a>
    </p>

    <p style="margin:0 0 6px; font-size:13px; font-weight:700; color:#0f172a;">Recomendaciones para tu nueva contraseña</p>
    <p style="margin:0 0 4px; font-size:13px; color:#475569; line-height:1.7;">
        &bull;&nbsp; Usa al menos 8 caracteres, combinando letras, números y símbolos.<br>
        &bull;&nbsp; No uses la misma contraseña que en otros sitios.<br>
        &bull;&nbsp; No la compartas con nadie; nuestro equipo nunca te la pedirá.
    </p>
@endsection
