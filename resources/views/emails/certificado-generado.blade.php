@extends('emails.layout')

@section('accent', 'indigo')
@section('preheader', 'Tu certificado de finalización ya está listo. Lo encontrarás adjunto en este correo.')
@section('badge', 'Certificado de finalización')
@section('titulo', '¡Felicidades, tu certificado está listo!')
@section('subtitulo', 'Lo encontrarás adjunto en este correo')

@section('content')
    <p style="margin:0 0 22px; font-size:15px; line-height:1.6; color:#475569;">
        Hola <strong>{{ $estudiante->nombre ?? 'estudiante' }}</strong>, queremos felicitarte por
        concluir tu Bachillerato con SAINS. Adjuntamos
        tu <strong>certificado de finalización</strong> en formato PDF.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef2ff; border:1px solid #c7d2fe; border-radius:14px; margin-bottom:26px;">
        <tr>
            <td style="padding:20px 22px;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#4338ca; margin-bottom:8px;">Certificado de finalización</div>
                <div style="font-size:15px; color:#312e81; line-height:1.5; font-weight:600;">{{ $estudiante->nombre }} {{ $estudiante->paterno }} {{ $estudiante->materno }}</div>
                <div style="font-size:13px; color:#4338ca; opacity:.8; margin-top:4px;">Folio: SAINS-{{ str_pad($estudiante->id, 6, '0', STR_PAD_LEFT) }}-{{ date('Y') }}</div>
            </td>
        </tr>
    </table>

    <p style="margin:0; font-size:13px; line-height:1.6; color:#94a3b8;">
        Guarda este documento, es tu comprobante oficial de finalización del bachillerato.
        Si tienes dudas, respóndenos por los medios de contacto de abajo.
    </p>
@endsection
