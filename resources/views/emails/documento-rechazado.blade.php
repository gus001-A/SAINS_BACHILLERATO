@extends('emails.layout')

@section('accent', 'red')
@section('preheader', 'No pudimos validar tu documento. Puedes corregirlo y volver a subirlo.')
@section('badge', 'Documento rechazado')
@section('titulo', 'Tu documento necesita una corrección')
@section('subtitulo', 'No pudimos validar el archivo que enviaste')

@section('content')
    <p style="margin:0 0 22px; font-size:15px; line-height:1.6; color:#475569;">
        Hola <strong>{{ $estudiante->nombre ?? 'estudiante' }}</strong>, revisamos tu
        <strong>{{ $tipoLabel }}</strong> y <strong>necesita una corrección</strong>. No te preocupes:
        puedes volver a subirlo desde tu perfil. Tu lugar sigue disponible.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fef2f2; border:1px solid #fecaca; border-radius:14px; margin-bottom:24px;">
        <tr>
            <td style="padding:20px 22px;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#b91c1c; margin-bottom:8px;">Motivo del rechazo</div>
                <div style="font-size:15px; color:#7f1d1d; line-height:1.5;">{{ $motivo }}</div>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; margin-bottom:26px;">
        <tr>
            <td style="padding:20px 22px;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#94a3b8; margin-bottom:12px;">Antes de reenviar, revisa que</div>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; color:#334155; line-height:1.5;">
                    <tr><td style="padding:5px 0;">&bull;&nbsp; El archivo se vea completo y legible.</td></tr>
                    <tr><td style="padding:5px 0;">&bull;&nbsp; Sea el documento correcto ({{ $tipoLabel }}).</td></tr>
                    <tr><td style="padding:5px 0;">&bull;&nbsp; El formato sea PDF (máx. 5 MB).</td></tr>
                    <tr><td style="padding:5px 0;">&bull;&nbsp; Toda la información esté visible.</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:6px 0 20px;">
                <a href="{{ route('estudiante.perfil') }}"
                   style="display:inline-block; background-color:#dc2626; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700; padding:15px 38px; border-radius:12px;">
                    Corregir y reenviar documento
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0; font-size:13px; line-height:1.6; color:#94a3b8;">
        Puedes reintentarlo las veces que necesites. Si crees que es un error, respóndenos por los medios de contacto de abajo.
    </p>
@endsection