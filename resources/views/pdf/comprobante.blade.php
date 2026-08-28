<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Comprobante de Prestaciones</title>
    <style>
        @page { margin: 12mm 10mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 8.5px; color: #1e293b; line-height: 1.3; }
        .header { text-align: center; border-bottom: 2px solid #1a365d; padding-bottom: 6px; margin-bottom: 10px; }
        .header h1 { font-size: 13px; color: #1a365d; margin: 0 0 2px; text-transform: uppercase; }
        .header h2 { font-size: 10px; color: #2563eb; margin: 0; font-weight: 400; }
        .header p { font-size: 7px; color: #64748b; margin: 2px 0 0; }
        .info-grid { display: flex; gap: 10px; margin-bottom: 8px; align-items: flex-start; }
        .info-photo { width: 55px; min-width: 55px; }
        .info-photo img { width: 55px; height: 55px; border-radius: 4px; object-fit: cover; border: 1px solid #e2e8f0; }
        .info-photo-placeholder { width: 55px; height: 55px; border-radius: 4px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 18px; border: 1px solid #e2e8f0; }
        .info-text { flex: 1; }
        .info-text h3 { font-size: 11px; margin: 0 0 2px; color: #0f172a; }
        .info-text p { font-size: 7.5px; color: #475569; margin: 0; line-height: 1.5; }
        .info-text .label { color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin: 4px 0; }
        th, td { padding: 3px 5px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 8px; }
        th { background: #f8fafc; color: #475569; font-size: 7px; text-transform: uppercase; font-weight: 700; }
        .text-right { text-align: right; }
        .text-bold { font-weight: 700; }
        .total-row td { border-top: 2px solid #1a365d; font-weight: 700; font-size: 10px; color: #1a365d; padding-top: 4px; }
        .total-row td:last-child { font-size: 11px; }
        .two-col { display: flex; gap: 12px; }
        .two-col > div { flex: 1; }
        .section-title { font-size: 9px; font-weight: 700; color: #1a365d; text-transform: uppercase; border-bottom: 1px solid #cbd5e1; padding-bottom: 2px; margin-bottom: 4px; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 6.5px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 4px; }
        .badge { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 7px; font-weight: 700; }
        .badge-blue { background: #eff6ff; color: #1e40af; }
        .badge-green { background: #ecfdf5; color: #059669; }
        .exp-row { display: flex; gap: 8px; flex-wrap: wrap; }
        .exp-row span { font-size: 7.5px; color: #475569; white-space: nowrap; }
        .exp-row .label { color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sistema Integral de Gestión de Jubilaciones</h1>
        <h2>Comprobante de Cálculo de Prestaciones Sociales</h2>
        <p>Emitido: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info-grid">
        <div class="info-photo">
            @if ($foto_base64)
                <img src="{{ $foto_base64 }}">
            @else
                <div class="info-photo-placeholder">&#128100;</div>
            @endif
        </div>
        <div class="info-text">
            <h3>{{ $trabajador->nombres }} {{ $trabajador->apellidos }}</h3>
            <p>
                <span class="label">Cédula:</span> {{ $trabajador->cedula }}
                &nbsp;|&nbsp; <span class="label">Edad:</span> {{ $trabajador->edad }} años
                &nbsp;|&nbsp; <span class="label">Servicio:</span> {{ $trabajador->total_anos_servicio ?? 0 }} años
                &nbsp;|&nbsp; <span class="label">Tipo:</span> {{ $solicitud->tipo_jubilacion ?? '—' }}
            </p>
            <p>
                <span class="label">Cargo:</span> {{ $trabajador->cargo }}
                &nbsp;|&nbsp; <span class="label">Unidad:</span> {{ $trabajador->unidad_departamento }}
                &nbsp;|&nbsp; <span class="label">Ingreso:</span> {{ $trabajador->fecha_ingreso ? \Carbon\Carbon::parse($trabajador->fecha_ingreso)->format('d/m/Y') : '—' }}
            </p>
        </div>
    </div>

    <div class="two-col">
        <div>
            <div class="section-title">Detalle del Cálculo</div>
            <table>
                <thead>
                    <tr><th>Concepto</th><th class="text-right">Monto (Bs.)</th></tr>
                </thead>
                <tbody>
                    <tr><td>Sueldo Base Mensual</td><td class="text-right">{{ number_format($sueldo_base, 2, ',', '.') }}</td></tr>
                    @if (count($detalles) > 0)
                        <tr><td colspan="2" style="font-weight:700;color:#475569;padding-top:4px;">Primas Aplicables</td></tr>
                        @foreach ($detalles as $d)
                            @if (($d['monto'] ?? 0) > 0)
                                <tr><td style="padding-left:12px;">{{ $d['nombre'] ?? $d['codigo'] ?? '' }}</td><td class="text-right">{{ number_format($d['monto'], 2, ',', '.') }}</td></tr>
                            @endif
                        @endforeach
                    @endif
                    <tr><td><strong>Total Primas</strong></td><td class="text-right text-bold">{{ number_format($total_primas, 2, ',', '.') }}</td></tr>
                    <tr><td><strong>Sueldo Integral Mensual</strong></td><td class="text-right text-bold">{{ number_format($sueldo_integral, 2, ',', '.') }}</td></tr>
                    <tr><td>Porcentaje de Jubilación</td><td class="text-right">{{ number_format($porcentaje, 1, ',', '.') }}%</td></tr>
                    <tr><td>Años de Servicio</td><td class="text-right">{{ $trabajador->total_anos_servicio ?? 0 }}</td></tr>
                    <tr class="total-row"><td>MONTO TOTAL PRESTACIONES</td><td class="text-right">Bs. {{ number_format($total_prestaciones, 2, ',', '.') }}</td></tr>
                </tbody>
            </table>
        </div>
        <div>
            <div class="section-title">Datos del Expediente</div>
            <table>
                <tr><td>Fecha de Nacimiento</td><td>{{ $trabajador->fecha_nacimiento ? \Carbon\Carbon::parse($trabajador->fecha_nacimiento)->format('d/m/Y') : '—' }}</td></tr>
                <tr><td>Nivel de Instrucción</td><td>{{ $trabajador->nivel_instruccion ?? '—' }}</td></tr>
                <tr><td>Número de Hijos</td><td>{{ $trabajador->numero_hijos ?? 0 }}</td></tr>
                <tr><td>Hijos con Discapacidad</td><td>{{ $trabajador->hijos_discapacidad ?? 0 }}</td></tr>
                <tr><td>Actividad Universitaria</td><td>{{ $trabajador->actividad_universitaria ? 'Sí' : 'No' }}</td></tr>
                <tr><td>Prima Familiar (Antigüedad)</td><td>{{ $trabajador->porcentaje_antiguedad ?? 0 }}%</td></tr>
                <tr><td>Prima Profesionalización</td><td>{{ $trabajador->prima_profesionalizacion ? 'Bs. ' . number_format($trabajador->prima_profesionalizacion, 2, ',', '.') : '—' }}</td></tr>
                <tr><td>Cesta Ticket</td><td>{{ $trabajador->cesta_ticket ? 'Bs. ' . number_format($trabajador->cesta_ticket, 2, ',', '.') : '—' }}</td></tr>
            </table>
        </div>
    </div>

    <div class="footer">
        SIGEJUB - Sistema Integral de Gestión de Jubilaciones - Documento generado electrónicamente
    </div>
</body>
</html>
