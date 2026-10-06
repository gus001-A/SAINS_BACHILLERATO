@extends('emails.layout')

@section('accent', 'amber')
@section('preheader', 'El estado de tu pago cambió. Aquí tienes los detalles.')
@section('badge', 'Actualización de pago')
@section('titulo', 'Actualizamos el estado de tu pago')
@section('subtitulo', 'Hubo un cambio en la validación de tu pago')

@section('content')
    <p style="margin:0 0 22px; font-size:15px; line-height:1.6; color:#475569;">
        Te informamos que el estado de tu pago cambió a
        <strong style="text-transform:capitalize;">{{ $nuevo_estado }}</strong>.
        Si esto afecta tu acceso al Curso Premium, puedes regularizarlo en cualquier momento.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; margin-bottom:26px;">
        <tr>
            <td style="padding:20px 22px;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#94a3b8; margin-bottom:12px;">Detalles del cambio</div>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; color:#1e293b;">
                    <tr>
                        <td style="padding:6px 0; color:#64748b;">Referencia</td>
                        <td style="padding:6px 0; text-align:right; font-weight:600;">{{ $pago->referencia_pago ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; color:#64748b;">Nuevo estado</td>
                        <td style="padding:6px 0; text-align:right; font-weight:600; text-transform:capitalize;">{{ $nuevo_estado }}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; color:#64748b;">Fecha del cambio</td>
                        <td style="padding:6px 0; text-align:right; font-weight:600;">{{ $fecha_cambio }}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; color:#64748b;">Realizado por</td>
                        <td style="padding:6px 0; text-align:right; font-weight:600;">{{ $admin_nombre }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:6px 0 20px;">
                <a href="{{ route('estudiante.checkout') }}"
                   style="display:inline-block; background-color:#d97706; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700; padding:15px 38px; border-radius:12px;">
                    Regularizar mi pago
                </a>
            </td>
        </tr>
    </table>
@endsection
