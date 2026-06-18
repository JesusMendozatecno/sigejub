
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Caja Negra — Reporte de Auditoría</title>
    <link rel="icon" href="<?php echo e(asset('img/descarga (1).png')); ?>" type="image/png">
    <link rel="shortcut icon" href="<?php echo e(asset('img/imagen_2026-05-19_065531142.ico')); ?>" type="image/x-icon">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',Arial,sans-serif; font-size:9px; color:#1e293b; padding:20px; }
        .no-print { text-align:center; margin-bottom:16px; }
        .no-print button { padding:8px 20px; background:#1a365d; color:white; border:none; border-radius:6px; font-size:13px; cursor:pointer; margin:0 4px; }
        h1 { text-align:center; font-size:16px; color:#0f172a; margin-bottom:2px; }
        .subtitle { text-align:center; color:#64748b; margin-bottom:16px; font-size:10px; }
        table { width:100%; border-collapse:collapse; }
        th { background:#1e3a8a; color:white; padding:6px 5px; text-align:left; font-size:8px; text-transform:uppercase; letter-spacing:0.3px; }
        td { padding:5px; border-bottom:1px solid #e2e8f0; font-size:8px; }
        tr:nth-child(even) { background:#f8fafc; }
        .footer { text-align:center; color:#94a3b8; font-size:7px; margin-top:16px; border-top:1px solid #e2e8f0; padding-top:12px; }
        .badge { display:inline-block;padding:1px 6px;border-radius:8px;font-size:7px;font-weight:700; }
        .badge-created { background:#d1fae5;color:#065f46; }
        .badge-updated { background:#dbeafe;color:#1e40af; }
        .badge-deleted { background:#fee2e2;color:#991b1b; }
        @media print {
            .no-print { display:none !important; }
            body { padding:0; }
            th { background:#1e3a8a !important; color:white !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
            tr:nth-child(even) { background:#f8fafc !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
            .badge { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">📄 Guardar / Imprimir PDF</button>
        <button onclick="window.close()" style="background:#64748b;">Cerrar</button>
    </div>
    <h1>SIGEJUB — Caja Negra / Auditoría</h1>
    <p class="subtitle">Reporte generado <?php echo e(now()->format('d/m/Y H:i')); ?> — Total: <?php echo e($activities->count()); ?> registros</p>

    <table>
        <thead>
            <tr>
                <th>Fecha/Hora</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Tipo</th>
                <th>Descripción</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td style="white-space:nowrap;"><?php echo e($a->created_at->format('d/m/Y H:i')); ?></td>
                <td><?php echo e($a->user?->nombre ?? 'Sistema'); ?></td>
                <td><span class="badge badge-<?php echo e($a->accion); ?>"><?php echo e(ucfirst($a->accion)); ?></span></td>
                <td><?php echo e($a->tipo_entidad ?? '—'); ?></td>
                <td><?php echo e(Str::limit($a->descripcion, 80)); ?></td>
                <td style="font-family:monospace;"><?php echo e($a->direccion_ip ?? '—'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="6" style="text-align:center;padding:20px;color:#94a3b8;">Sin registros</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <p class="footer">SIGEJUB v1.0 — Documento generado por <?php echo e(auth()->user()?->nombre ?? 'Sistema'); ?></p>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\sigejub 2\resources\views\pdf\caja-negra.blade.php ENDPATH**/ ?>