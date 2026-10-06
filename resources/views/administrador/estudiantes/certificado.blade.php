<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Certificado - {{ $estudiante->nombre }} {{ $estudiante->paterno }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { margin: 0; }

        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            color: #1e293b;
        }

        .wrap {
            display: table;
            width: 100%;
            height: 190mm;
            padding: 24px;
        }
        .wrap-cell {
            display: table-cell;
            vertical-align: middle;
        }

        .border {
            border: 3px solid #4f46e5;
            padding: 3px;
        }
        .border-inner {
            border: 1px solid #c7d2fe;
            padding: 46px 60px;
            text-align: center;
        }

        .logo { width: 56px; height: auto; margin-bottom: 10px; }

        .kicker {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #4f46e5;
            margin-bottom: 18px;
        }

        .title {
            font-size: 30px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #312e81;
            margin-bottom: 26px;
        }

        .lead {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 10px;
        }

        .nombre {
            font-size: 26px;
            font-weight: 800;
            color: #1e293b;
            padding: 8px 0 14px;
            margin-bottom: 14px;
            border-bottom: 2px solid #e2e8f0;
            display: inline-block;
            min-width: 60%;
        }

        .texto {
            font-size: 13px;
            line-height: 1.8;
            color: #334155;
            max-width: 560px;
            margin: 0 auto 30px;
        }

        .firmas {
            width: 100%;
            margin-top: 50px;
        }
        .firmas td {
            width: 50%;
            text-align: center;
            font-size: 11px;
            color: #475569;
            vertical-align: top;
        }
        .firma-linea {
            border-top: 1px solid #94a3b8;
            width: 220px;
            margin: 0 auto 6px;
            padding-top: 8px;
        }

        .meta {
            margin-top: 40px;
            font-size: 9.5px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="wrap-cell">
            <div class="border">
                <div class="border-inner">
                    <img src="{{ public_path('images/logo-sm.png') }}" class="logo" alt="SAINS">
                    <div class="kicker">SAINS &middot; BACHILLERATO</div>
                    <div class="title">CERTIFICADO DE FINALIZACIÓN DE BACHILLERATO</div>

                    <div class="lead">SE OTORGA EL PRESENTE CERTIFICADO A</div>
                    <div class="nombre">{{ Str::upper($estudiante->nombre . ' ' . $estudiante->paterno . ' ' . $estudiante->materno) }}</div>

                    <div class="texto">
                        POR HABER CONCLUIDO Y APROBADO SATISFACTORIAMENTE SUS ESTUDIOS DE
                        BACHILLERATO (PREPARATORIA) IMPARTIDOS POR SAINS, DEMOSTRANDO DEDICACIÓN
                        Y COMPROMISO A LO LARGO DE SU PROCESO DE ESTUDIO.
                    </div>

                    <table class="firmas">
                        <tr>
                            <td>
                                <div class="firma-linea">DIRECCIÓN ACADÉMICA</div>
                            </td>
                            <td>
                                <div class="firma-linea">SAINS</div>
                            </td>
                        </tr>
                    </table>

                    <div class="meta">
                        FOLIO: SAINS-{{ str_pad($estudiante->id, 6, '0', STR_PAD_LEFT) }}-{{ date('Y') }}
                        &nbsp;&middot;&nbsp; EXPEDIDO EL {{ Str::upper($fecha) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
