@extends('emails.layout')

@section('accent', 'green')
@section('preheader', 'Uno de tus documentos fue aprobado. ¡Ya puedes continuar!')
@section('badge', 'Documento aprobado')
@section('titulo', '¡Tu documento fue aprobado!')
@section('subtitulo', 'Uno de los requisitos de tu expediente está completo')

@section('content')
    <p style="margin:0 0 22px; font-size:15px; line-height:1.6; color:#475569;">
        Hola <strong>{{ $estudiante->nombre ?? 'estudiante' }}</strong>, revisamos tu
        <strong>{{ $tipoLabel }}</strong> y <strong>todo está en orden</strong>. Ya forma parte
        de tu expediente oficial. ¡Gracias por completar este paso!
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0fdf4; border:1px solid #bbf7d0; border-radius:14px; margin-bottom:24px;">
        <tr>
            <td style="padding:20px 22px;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#15803d; margin-bottom:8px;">Documento aprobado</div>
                <div style="font-size:15px; color:#166534; line-height:1.5; font-weight:600;">{{ $tipoLabel }}</div>
                @if($documento->nombre_original)
                    <div style="font-size:13px; color:#166534; opacity:.8; margin-top:4px;">{{ $documento->nombre_original }}</div>
                @endif
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; margin-bottom:26px;">
        <tr>
            <td style="padding:20px 22px;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#94a3b8; margin-bottom:12px;">¿Qué sigue?</div>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; color:#334155; line-height:1.5;">
                    <tr><td style="padding:5px 0;">&bull;&nbsp; Continúa subiendo tus documentos pendientes.</td></tr>
                    <tr><td style="padding:5px 0;">&bull;&nbsp; Revisa tu expediente desde tu perfil cuando quieras.</td></tr>
                    <tr><td style="padding:5px 0;">&bull;&nbsp; Si ya completaste todos, ¡estás listo para continuar!</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:6px 0 20px;">
                <a href="{{ route('estudiante.perfil') }}"
                   style="display:inline-block; background-color:#16a34a; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700; padding:15px 38px; border-radius:12px;">
                    Ver mi expediente
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0; font-size:13px; line-height:1.6; color:#94a3b8;">
        Si tienes dudas, respóndenos por los medios de contacto de abajo. ¡Estamos para ayudarte!
    </p>
@endsection