<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Estatus de documentos - {{ $estudiante->nombre }} {{ $estudiante->paterno }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { margin: 0; }

        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            color: #1e293b;
            font-size: 10.5px;
            line-height: 1.45;
        }

        .wrap { padding: 32px 34px 40px; }

        /* ===== Encabezado ===== */
        .brandbar {
            background: #4f46e5;
            color: #ffffff;
            padding: 22px 34px;
        }
        .brandbar table { width: 100%; }
        .brandbar .logo { width: 44px; height: auto; vertical-align: middle; }
        .brandbar h1 {
            font-size: 20px; font-weight: 700; letter-spacing: 1px;
            display: inline-block; vertical-align: middle; margin-left: 12px;
        }
        .brandbar .sub { font-size: 10px; color: #c7d2fe; margin-top: 3px; }
        .brandbar .meta { text-align: right; font-size: 9px; color: #c7d2fe; line-height: 1.7; }
        .brandbar .meta strong { color: #ffffff; display: block; font-size: 11px; }

        /* ===== Ficha del estudiante ===== */
        .profile {
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 10px;
            padding: 16px 18px;
            margin-bottom: 20px;
        }
        .profile .name { font-size: 16px; font-weight: 700; color: #312e81; }
        .profile .email { font-size: 10px; color: #6366f1; margin: 3px 0 9px; }

        /* ===== Secciones ===== */
        .section { margin-bottom: 22px; page-break-inside: avoid; }
        .section-title {
            font-size: 11px;
            font-weight: 700;
            color: #312e81;
            letter-spacing: 0.5px;
            padding: 0 0 6px 10px;
            border-left: 3px solid #4f46e5;
            margin-bottom: 10px;
        }

        /* ===== Tablas de datos ===== */
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data th {
            background: #f1f5f9;
            padding: 7px 11px;
            text-align: left;
            font-size: 9.5px;
            font-weight: 700;
            color: #334155;
            border: 1px solid #e2e8f0;
            width: 26%;
        }
        table.data td {
            padding: 7px 11px;
            border: 1px solid #e2e8f0;
            font-size: 9.5px;
            color: #475569;
        }

        /* ===== Tabla de documentos ===== */
        table.docs { width: 100%; border-collapse: collapse; }
        table.docs th {
            background: #4f46e5;
            color: #ffffff;
            padding: 8px 10px;
            text-align: left;
            font-size: 8.5px;
            font-weight: 700;
            letter-spacing: 0.4px;
        }
        table.docs td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9.5px;
            color: #475569;
            vertical-align: top;
        }
        table.docs tr:nth-child(even) td { background: #f8fafc; }

        .chip {
            display: inline-block;
            padding: 3px 11px;
            border-radius: 999px;
            font-size: 8.5px;
            font-weight: 700;
            letter-spacing: 0.4px;
            white-space: nowrap;
        }
        .chip-aprobado  { background: #dcfce7; color: #166534; }
        .chip-rechazado { background: #fee2e2; color: #991b1b; }
        .chip-pendiente { background: #fef3c7; color: #92400e; }
        .chip-no_subido { background: #f1f5f9; color: #64748b; }

        .muted { color: #94a3b8; }

        .footer {
            margin-top: 26px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <div class="brandbar">
        <table>
            <tr>
                <td>
                    <img src="{{ public_path('images/logo-sm.png') }}" class="logo" alt="SAINS">
                    <h1>SAINS BACHILLERATO</h1>
                    <div class="sub">ESTUDIA LA PREPARATORIA EN LINEA CON NOSOTROS</div>
                </td>
                <td class="meta">
                    <strong>ESTATUS DE DOCUMENTOS</strong>
                    GENERADO: {{ date('d/m/Y H:i') }}<br>
                    DOCUMENTO CONFIDENCIAL
                </td>
            </tr>
        </table>
    </div>

    <div class="wrap">

        <!-- Ficha -->
        <div class="profile">
            <div class="name">{{ Str::upper($estudiante->nombre . ' ' . $estudiante->paterno . ' ' . $estudiante->materno) }}</div>
            <div class="email">{{ Str::upper($usuario->correo) }}</div>
        </div>

        <!-- Datos de contacto -->
        <div class="section">
            <div class="section-title">DATOS DE CONTACTO</div>
            <table class="data">
                <tr>
                    <th>CORREO</th>
                    <td>{{ Str::upper($usuario->correo) }}</td>
                    <th>TELÉFONO</th>
                    <td>{{ $estudiante->telefono ?? $estudiante->telefono_casa ?? '—' }}</td>
                </tr>
            </table>
        </div>

        <!-- Estatus de documentos -->
        <div class="section">
            <div class="section-title">ESTATUS DE DOCUMENTOS</div>
            <table class="docs">
                <thead>
                    <tr>
                        <th style="width: 22%;">DOCUMENTO</th>
                        <th style="width: 14%;">ESTATUS</th>
                        <th style="width: 34%;">MOTIVO</th>
                        <th style="width: 30%;">ARCHIVO</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($filasDocumentos as $fila)
                        <tr>
                            <td><strong>{{ Str::upper($fila['label']) }}</strong></td>
                            <td>
                                <span class="chip chip-{{ $fila['estatus'] }}">{{ Str::upper($fila['estatus_texto']) }}</span>
                            </td>
                            <td>{{ Str::upper((string) $fila['motivo']) }}</td>
                            <td>{{ $fila['archivo'] ? Str::upper($fila['archivo']) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($totalAnexos > 0)
            <p class="muted">A CONTINUACIÓN SE INCLUYEN LOS {{ $totalAnexos }} DOCUMENTO(S) QUE EL ESTUDIANTE HA SUBIDO.</p>
        @else
            <p class="muted">EL ESTUDIANTE AÚN NO HA SUBIDO NINGÚN DOCUMENTO.</p>
        @endif

        <div class="footer">
            SAINS · PREPARACIÓN PARA EL INGRESO A LA UNIVERSIDAD<br>
            REPORTE GENERADO AUTOMÁTICAMENTE · DOCUMENTO CONFIDENCIAL
        </div>

    </div>
</body>
</html>
