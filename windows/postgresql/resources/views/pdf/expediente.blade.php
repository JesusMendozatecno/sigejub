<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Expediente de {{ $trabajador->nombres }} {{ $trabajador->apellidos }}</title>
    <style>
        @page { margin: 22mm 18mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; }
        h1 { color: #1a365d; font-size: 18px; margin: 0 0 4px; }
        h2 { color: #1a365d; font-size: 13px; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px; margin: 18px 0 8px; }
        p { margin: 4px 0; }
        .header { border-bottom: 2px solid #1a365d; padding-bottom: 10px; }
        .photo { float: right; width: 95px; height: 115px; object-fit: cover; border: 1px solid #cbd5e1; padding: 3px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; text-align: left; vertical-align: top; }
        th { width: 32%; color: #475569; background: #f8fafc; }
        .footer { margin-top: 22px; color: #64748b; font-size: 8px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        @if ($foto_base64)
            <img class="photo" src="{{ $foto_base64 }}" alt="Foto del trabajador">
        @endif
        <h1>Expediente del trabajador</h1>
        <p>SIGEJUB · Folio de expediente #{{ $expediente->id }}</p>
        <p>Generado: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <h2>Datos personales</h2>
    <table>
        <tr><th>Nombre completo</th><td>{{ $trabajador->nombres }} {{ $trabajador->apellidos }}</td></tr>
        <tr><th>Cédula</th><td>{{ $trabajador->cedula ?: '—' }}</td></tr>
        <tr><th>Género</th><td>{{ $trabajador->genero ?: '—' }}</td></tr>
        <tr><th>Fecha de nacimiento</th><td>{{ $trabajador->fecha_nacimiento ?: '—' }}</td></tr>
        <tr><th>Edad</th><td>{{ $trabajador->edad === null ? '—' : $trabajador->edad . ' años' }}</td></tr>
        <tr><th>Número de hijos</th><td>{{ $trabajador->numero_hijos ?? '—' }}</td></tr>
    </table>

    <h2>Datos laborales</h2>
    <table>
        <tr><th>Código de empleado</th><td>{{ $trabajador->codigo_empleado ?: '—' }}</td></tr>
        <tr><th>Cargo</th><td>{{ $trabajador->cargo ?: '—' }}</td></tr>
        <tr><th>Unidad / departamento</th><td>{{ $trabajador->unidad_departamento ?: '—' }}</td></tr>
        <tr><th>Fecha de ingreso</th><td>{{ $trabajador->fecha_ingreso ?: '—' }}</td></tr>
        <tr><th>Años de servicio</th><td>{{ $trabajador->total_anos_servicio ?? '—' }}</td></tr>
        <tr><th>Nivel de instrucción</th><td>{{ $trabajador->nivel_instruccion ?: '—' }}</td></tr>
        <tr><th>Tipo de nómina</th><td>{{ $trabajador->tipo_nomina ?: '—' }}</td></tr>
        <tr><th>Tipo de jubilación</th><td>{{ $solicitud->tipo_jubilacion ?? '—' }}</td></tr>
    </table>

    <h2>Estado del expediente</h2>
    <table>
        <tr><th>Progreso documental</th><td>{{ $expediente->estado_global }}%</td></tr>
        <tr><th>Fecha de creación</th><td>{{ $expediente->created_at ? $expediente->created_at->format('d/m/Y') : '—' }}</td></tr>
        <tr><th>Solicitud vinculada</th><td>{{ $solicitud ? '#' . $solicitud->id . ' · ' . ($solicitud->tipo_jubilacion ?: '—') . ' · ' . ucfirst($solicitud->estado) : 'Sin solicitud vinculada' }}</td></tr>
        <tr><th>Notas administrativas</th><td>{{ $expediente->notas_admin ?: '—' }}</td></tr>
    </table>

    <h2>Documentos registrados</h2>
    <table>
        <tr><th>Documento</th><th>Estado</th></tr>
        @forelse ($expediente->documentos as $documento)
            <tr><td>{{ $documento->nombre }}</td><td>{{ ucfirst(str_replace('_', ' ', $documento->estado)) }}</td></tr>
        @empty
            <tr><td colspan="2">No hay documentos registrados.</td></tr>
        @endforelse
    </table>

    <div class="footer">SIGEJUB · Documento generado electrónicamente</div>
</body>
</html>
