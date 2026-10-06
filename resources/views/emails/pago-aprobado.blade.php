@extends('emails.layout')

@section('accent', 'green')
@section('preheader', 'Tu pago fue aprobado y tu acceso Premium ya está activo.')
@section('badge', 'Pago aprobado')
@section('titulo', '¡Tu pago fue aprobado!')
@section('subtitulo', 'Tu acceso al Curso Premium ya está activo')

@section('content')
    <p style="margin:0 0 22px; font-size:15px; line-height:1.6; color:#475569;">
        Confirmamos que <strong>tu pago fue validado correctamente</strong>. Tu cuenta ya tiene acceso
        completo a las clases en video, el simulador de examen y el seguimiento de tu progreso.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; margin-bottom:26px;">
        <tr>
            <td style="padding:20px 22px;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#94a3b8; margin-bottom:12px;">Detalles de la transacción</div>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; color:#1e293b;">
                    <tr>
                        <td style="padding:6px 0; color:#64748b;">Referencia</td>
                        <td style="padding:6px 0; text-align:right; font-weight:600;">{{ $pago->referencia_pago ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; color:#64748b;">Monto</td>
                        <td style="padding:6px 0; text-align:right; font-weight:600;">${{ number_format($pago->monto_pago, 2) }} MXN</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; color:#64748b;">Fecha de aprobación</td>
                        <td style="padding:6px 0; text-align:right; font-weight:600;">{{ $fecha_aprobacion }}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; color:#64748b;">Validado por</td>
                        <td style="padding:6px 0; text-align:right; font-weight:600;">{{ $admin_nombre }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:8px;">
        <tr>
            <td align="center" style="padding:6px 0 22px;">
                <a href="{{ route('estudiante.clases-premium') }}"
                   style="display:inline-block; background-color:#16a34a; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700; padding:15px 38px; border-radius:12px;">
                    Ir a mis clases
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0; font-size:13px; line-height:1.6; color:#94a3b8;">
        Consejo: empieza por el simulador para conocer tu nivel y crea una rutina de estudio de 30 minutos al día.
    </p>
@endsection
