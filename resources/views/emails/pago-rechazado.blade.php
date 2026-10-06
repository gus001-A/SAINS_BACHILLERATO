@extends('emails.layout')

@section('accent', 'red')
@section('preheader', 'No pudimos validar tu comprobante. Puedes corregirlo y volver a enviarlo.')
@section('badge', 'Pago rechazado')
@section('titulo', 'Tu pago necesita una corrección')
@section('subtitulo', 'No pudimos validar el comprobante que enviaste')

@section('content')
    <p style="margin:0 0 22px; font-size:15px; line-height:1.6; color:#475569;">
        Revisamos tu pago y <strong>no pudimos aprobarlo todavía</strong>. No te preocupes: puedes
        corregir el detalle y volver a enviar tu comprobante. Tu lugar sigue disponible.
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
                    <tr><td style="padding:5px 0;">&bull;&nbsp; El comprobante se vea completo y legible.</td></tr>
                    <tr><td style="padding:5px 0;">&bull;&nbsp; El monto sea de <strong>$800.00 MXN</strong>.</td></tr>
                    <tr><td style="padding:5px 0;">&bull;&nbsp; La referencia coincida con la de tu ficha.</td></tr>
                    <tr><td style="padding:5px 0;">&bull;&nbsp; El archivo sea JPG, PNG o PDF (máx. 5 MB).</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:6px 0 20px;">
                <a href="{{ route('estudiante.mis-pagos') }}"
                   style="display:inline-block; background-color:#dc2626; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700; padding:15px 38px; border-radius:12px;">
                    Corregir y reenviar comprobante
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0; font-size:13px; line-height:1.6; color:#94a3b8;">
        Puedes reintentarlo las veces que necesites. Si crees que es un error, respóndenos por los medios de contacto de abajo.
    </p>
@endsection
