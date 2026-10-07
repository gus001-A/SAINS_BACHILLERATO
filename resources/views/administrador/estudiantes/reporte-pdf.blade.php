<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Estudiante - {{ $estudiante->nombre }} {{ $estudiante->paterno }}</title>
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
            background: #1851ad;
            color: #ffffff;
            padding: 22px 34px;
        }
        .brandbar table { width: 100%; }
        .brandbar .logo { width: 96px; height: auto; vertical-align: middle; }
        .brandbar h1 {
            font-size: 20px; font-weight: 700; letter-spacing: 1px;
            display: inline-block; vertical-align: middle; margin-left: 12px;
        }
        .brandbar .sub { font-size: 10px; color: #c5d5e9; margin-top: 3px; }
        .brandbar .meta { text-align: right; font-size: 9px; color: #c5d5e9; line-height: 1.7; }
        .brandbar .meta strong { color: #ffffff; display: block; font-size: 11px; }

        /* ===== Ficha del estudiante ===== */
        .profile {
            background: #eef3f9;
            border: 1px solid #c5d5e9;
            border-radius: 10px;
            padding: 16px 18px;
            margin-bottom: 20px;
        }
        .profile .name { font-size: 16px; font-weight: 700; color: #17325d; }
        .profile .email { font-size: 10px; color: #1664db; margin: 3px 0 9px; }
        .chip {
            display: inline-block;
            padding: 3px 11px;
            border-radius: 999px;
            font-size: 8.5px;
            font-weight: 700;
            letter-spacing: 0.4px;
            margin-right: 5px;
        }
        .chip-on { background: #dcfce7; color: #166534; }
        .chip-off { background: #fee2e2; color: #991b1b; }
        .chip-cupon { background: #fef3c7; color: #92400e; font-family: 'DejaVu Sans Mono', monospace; }

        /* ===== Tarjetas de estadísticas ===== */
        .stats { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin: 0 -8px 22px; }
        .stats td {
            width: 33.33%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-top: 3px solid #1851ad;
            border-radius: 8px;
            padding: 12px 8px;
            text-align: center;
        }
        .stats .num { font-size: 17px; font-weight: 800; color: #0f172a; display: block; }
        .stats .lbl {
            font-size: 8px; color: #64748b; margin-top: 4px;
            letter-spacing: 0.5px; display: block;
        }
        .stats td.c-green { border-top-color: #16a34a; }
        .stats td.c-amber { border-top-color: #d97706; }
        .stats td.c-blue { border-top-color: #0ea5e9; }

        /* ===== Secciones ===== */
        .section { margin-bottom: 22px; page-break-inside: avoid; }
        .section-title {
            font-size: 11px;
            font-weight: 700;
            color: #17325d;
            letter-spacing: 0.5px;
            padding: 0 0 6px 10px;
            border-left: 3px solid #1851ad;
            margin-bottom: 10px;
        }

        /* ===== Tablas de datos ===== */
        table.data { width: 100%; border-collapse: collapse; }
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

        table.grid { width: 100%; border-collapse: collapse; }
        table.grid th {
            background: #1851ad;
            color: #ffffff;
            padding: 7px 10px;
            text-align: left;
            font-size: 8.5px;
            font-weight: 700;
            letter-spacing: 0.4px;
        }
        table.grid td {
            padding: 7px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9.5px;
            color: #475569;
        }
        table.grid tr:nth-child(even) td { background: #f8fafc; }

        .metrics { width: 100%; border-collapse: separate; border-spacing: 8px 0; }
        .metrics td {
            width: 33.33%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 10px;
            text-align: center;
        }
        .metrics .num { font-size: 16px; font-weight: 800; color: #0f172a; display: block; }
        .metrics .lbl { font-size: 8.5px; color: #64748b; margin: 3px 0; display: block; }
        .bar { background: #e2e8f0; height: 5px; border-radius: 3px; margin: 6px 0 3px; }
        .bar > span { display: block; height: 5px; border-radius: 3px; background: #1851ad; }

        .score-high { color: #16a34a; font-weight: 700; }
        .score-low { color: #dc2626; font-weight: 700; }

        .footer {
            margin-top: 26px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            page-break-inside: avoid;
        }
        .page-break { page-break-before: always; }
        .text-center { text-align: center; }
    </style>
</head>
<body>

    <div class="brandbar">
        <table>
            <tr>
                <td>
                    <img src="{{ public_path('images/bachillerato-nacional-sm.png') }}" class="logo" alt="Bachillerato Nacional SAINS">
                    <h1>SAINS BACHILLERATO</h1>
                    <div class="sub">ESTUDIA LA PREPARATORIA EN LINEA CON NOSOTROS</div>
                </td>
                <td class="meta">
                    <strong>REPORTE DE ESTUDIANTE</strong>
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
            @if($estudiante->plan_activo)
                <span class="chip chip-on">PLAN ACTIVO</span>
            @else
                <span class="chip chip-off">PLAN INACTIVO</span>
            @endif
            @if($estudiante->cupon)
                <span class="chip chip-cupon">CUPÓN: {{ Str::upper($estudiante->cupon) }}</span>
            @endif
        </div>

        <!-- Estadísticas principales -->
        <table class="stats">
            <tr>
                <td class="c-blue"><span class="num">{{ $vistosCompletos ?? 0 }}/{{ $totalVideos ?? 0 }}</span><span class="lbl">VIDEOS VISTOS</span></td>
                <td class="c-green"><span class="num">{{ number_format($promedioCalificaciones ?? 0) }}%</span><span class="lbl">PROMEDIO GENERAL</span></td>
                <td class="c-amber"><span class="num">{{ number_format($diasActivos ?? 0) }}</span><span class="lbl">DÍAS ACTIVOS</span></td>
            </tr>
        </table>

        <!-- Información personal -->
        <div class="section">
            <div class="section-title">INFORMACIÓN PERSONAL</div>
            <table class="data">
                <tr>
                    <th>NOMBRE COMPLETO</th>
                    <td colspan="3">{{ Str::upper($estudiante->nombre . ' ' . $estudiante->paterno . ' ' . $estudiante->materno) }}</td>
                </tr>
                <tr>
                    <th>FECHA DE NACIMIENTO</th>
                    <td>{{ \Carbon\Carbon::parse($estudiante->fecha_nacimiento)->format('d/m/Y') }} ({{ \Carbon\Carbon::parse($estudiante->fecha_nacimiento)->age }} AÑOS)</td>
                    <th>SEXO</th>
                    <td>{{ $estudiante->sexo == 'M' ? 'MASCULINO' : 'FEMENINO' }}</td>
                </tr>
                <tr>
                    <th>TELÉFONO CELULAR</th>
                    <td>{{ $estudiante->telefono ?? '—' }}</td>
                    <th>TELÉFONO CASA</th>
                    <td>{{ $estudiante->telefono_casa ?? '—' }}</td>
                </tr>
                <tr>
                    <th>SESIONES TOTALES</th>
                    <td>{{ number_format($totalSesiones ?? 0) }}</td>
                    <th>ÚLTIMA ACTIVIDAD</th>
                    <td>{{ $ultimaActividad ?? '—' }}</td>
                </tr>
            </table>
        </div>

        <!-- Rendimiento por tipo -->
        <div class="section">
            <div class="section-title">RENDIMIENTO POR TIPO DE EXAMEN</div>
            <table class="metrics">
                <tr>
                    <td>
                        <span class="num">{{ $examenesPorTipo['simulacion'] ?? 0 }}</span>
                        <span class="lbl">SIMULACIONES</span>
                        <div class="bar"><span style="width: {{ min(100, $promedioPorTipo['simulacion'] ?? 0) }}%;"></span></div>
                        <span class="lbl">PROM. {{ number_format($promedioPorTipo['simulacion'] ?? 0, 0) }}%</span>
                    </td>
                    <td>
                        <span class="num">{{ $examenesPorTipo['materia'] ?? 0 }}</span>
                        <span class="lbl">POR MATERIA</span>
                        <div class="bar"><span style="width: {{ min(100, $promedioPorTipo['materia'] ?? 0) }}%;"></span></div>
                        <span class="lbl">PROM. {{ number_format($promedioPorTipo['materia'] ?? 0, 0) }}%</span>
                    </td>
                    <td>
                        <span class="num">{{ $examenesPorTipo['curso'] ?? 0 }}</span>
                        <span class="lbl">POR CURSO</span>
                        <div class="bar"><span style="width: {{ min(100, $promedioPorTipo['curso'] ?? 0) }}%;"></span></div>
                        <span class="lbl">PROM. {{ number_format($promedioPorTipo['curso'] ?? 0, 0) }}%</span>
                    </td>
                </tr>
            </table>
        </div>

        @if($examenes->isNotEmpty())
        <div class="page-break"></div>
        <div class="section">
            <div class="section-title">HISTORIAL DE EXÁMENES</div>
            <table class="grid">
                <thead>
                    <tr><th>EXAMEN</th><th>TIPO</th><th>FECHA</th><th>CALIFICACIÓN</th><th>INTENTO</th></tr>
                </thead>
                <tbody>
                    @foreach($examenes->take(15) as $examen)
                        @php $score = floatval($examen->calificacion ?? 0); @endphp
                        <tr>
                            <td>{{ Str::upper(Str::limit($examen->examen_nombre ?? 'Examen', 40)) }}</td>
                            <td>{{ Str::upper($examen->tipo_texto ?? 'General') }}</td>
                            <td>{{ $examen->fecha_completa ?? '—' }}</td>
                            <td class="{{ $score >= 70 ? 'score-high' : 'score-low' }}">{{ number_format($score, 0) }}%</td>
                            <td>{{ $examen->intento ?? 1 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if($examenes->count() > 15)
                <div class="text-center" style="font-size: 8px; color: #94a3b8; margin-top: 6px;">MOSTRANDO 15 DE {{ $examenes->count() }} EXÁMENES</div>
            @endif
        </div>
        @endif

        @if($ultimosVideos->isNotEmpty())
        <div class="page-break"></div>
        <div class="section">
            <div class="section-title">PROGRESO EN VIDEOS</div>
            <table class="metrics" style="margin-bottom: 14px;">
                <tr>
                    <td><span class="num">{{ $vistosCompletos ?? 0 }}</span><span class="lbl">COMPLETADOS</span></td>
                    <td><span class="num">{{ $videosEnProgreso ?? 0 }}</span><span class="lbl">EN PROGRESO</span></td>
                    <td><span class="num">{{ number_format($porcentajeProgreso ?? 0) }}%</span><span class="lbl">PROGRESO TOTAL</span></td>
                </tr>
            </table>
            <div class="bar" style="height: 7px; margin-bottom: 16px;">
                <span style="width: {{ min(100, $porcentajeProgreso ?? 0) }}%; height: 7px; background: #16a34a;"></span>
            </div>

            <div class="section-title" style="margin-bottom: 8px;">ÚLTIMOS VIDEOS VISTOS</div>
            <table class="grid">
                <thead>
                    <tr><th>VIDEO</th><th>FECHA</th><th>ESTADO</th></tr>
                </thead>
                <tbody>
                    @foreach($ultimosVideos as $progreso)
                    <tr>
                        <td>{{ Str::upper(Str::limit($progreso->video->titulo ?? 'Video', 50)) }}</td>
                        <td>{{ $progreso->fecha_visto ? \Carbon\Carbon::parse($progreso->fecha_visto)->format('d/m/Y') : '—' }}</td>
                        <td>{{ $progreso->completado ? 'COMPLETADO' : 'EN PROGRESO' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div class="footer">
            SAINS · PREPARACIÓN PARA EL INGRESO A LA UNIVERSIDAD<br>
            REPORTE GENERADO AUTOMÁTICAMENTE · DOCUMENTO CONFIDENCIAL
        </div>

    </div>
</body>
</html>
