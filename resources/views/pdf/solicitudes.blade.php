<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Solicitudes</title>
    <link rel="icon" href="{{ asset('img/descarga (1).png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('img/imagen_2026-05-19_065531142.ico') }}" type="image/x-icon">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #1e293b; padding: 30px; }
        .no-print { text-align: center; margin-bottom: 20px; }
        .no-print button { padding: 10px 24px; background: #1a365d; color: white; border: none; border-radius: 8px; font-size: 14px; cursor: pointer; }
        .no-print button:hover { background: #2563eb; }
        h1 { text-align: center; font-size: 18px; color: #0f172a; margin-bottom: 4px; }
        .subtitle { text-align: center; color: #64748b; margin-bottom: 24px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1e3a8a; color: white; padding: 10px 8px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) { background: #f8fafc; }
        .folio { font-weight: 700; color: #1e3a8a; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 9px; font-weight: 700; }
        .pendiente { background: #fef3c7; color: #92400e; }
        .revision { background: #dbeafe; color: #1e40af; }
        .aprobado { background: #d1fae5; color: #065f46; }
        .rechazado { background: #fee2e2; color: #991b1b; }
        .footer { text-align: center; color: #94a3b8; font-size: 9px; margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 16px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            th { background: #1e3a8a !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            tr:nth-child(even) { background: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">📄 Guardar / Imprimir PDF</button>
        <button onclick="window.close()" style="background:#64748b;margin-left:8px;">Cerrar</button>
    </div>

    <h1>SIGEJUB — Sistema de Gestión de Jubilaciones</h1>
    <p class="subtitle">Reporte de Solicitudes — Generado {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>FOLIO</th>
                <th>TRABAJADOR</th>
                <th>CÉDULA</th>
                <th>FECHA</th>
                <th>PERÍODO</th>
                <th>ESTATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($solicitudes as $s)
            <tr>
                <td class="folio">#SOL-{{ str_pad($s->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $s->trabajador?->nombres }} {{ $s->trabajador?->apellidos }}</td>
                <td>{{ $s->trabajador?->cedula ?? '—' }}</td>
                <td>{{ \Carbon\Carbon::parse($s->fecha_solicitud)->format('d/m/Y') }}</td>
                <td>{{ $s->periodo ?? '—' }}</td>
                <td><span class="badge {{ $s->estado }}">{{ ucfirst($s->estado) }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:20px; color:#94a3b8;">No hay solicitudes registradas</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">Total de solicitudes: {{ $solicitudes->count() }} | SIGEJUB v1.0</p>
</body>
</html>
