<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Solicitudes</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; }
        h1 { text-align: center; font-size: 16px; margin-bottom: 5px; }
        .subtitle { text-align: center; color: #666; margin-bottom: 20px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1e3a8a; color: white; padding: 8px 6px; text-align: left; font-size: 9px; }
        td { padding: 6px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) { background: #f8fafc; }
        .folio { font-weight: bold; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 8px; font-weight: bold; }
        .pendiente { background: #fff3cd; color: #856404; }
        .revision { background: #cce5ff; color: #004085; }
        .aprobado { background: #d4edda; color: #155724; }
        .rechazado { background: #f8d7da; color: #721c24; }
        .footer { text-align: center; color: #94a3b8; font-size: 8px; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>SIGEJUB - Sistema de Gestión de Jubilaciones</h1>
    <p class="subtitle">Reporte de Solicitudes - Generado {{ now()->format('d/m/Y H:i') }}</p>

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
