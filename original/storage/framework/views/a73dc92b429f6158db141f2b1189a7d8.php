
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>SIGEJUB — Próximos Jubilados</title>
    <link rel="icon" href="<?php echo e(asset('img/descarga (1).png')); ?>" type="image/png">
    <link rel="stylesheet" href="<?php echo e(asset('css/fontawesome/css/all.min.css')); ?>">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #1e293b; padding: 30px; }
        .no-print { text-align: center; margin-bottom: 20px; }
        .no-print button { padding: 10px 24px; background: #1a365d; color: white; border: none; border-radius: 8px; font-size: 14px; cursor: pointer; }
        .no-print button:hover { background: #2563eb; }
        .header-logo { display: flex; align-items: center; justify-content: center; gap: 14px; margin-bottom: 6px; }
        .header-logo img { height: 44px; }
        h1 { text-align: center; font-size: 18px; color: #0f172a; margin-bottom: 4px; }
        .subtitle { text-align: center; color: #64748b; margin-bottom: 6px; font-size: 12px; }
        .rango { text-align: center; color: #1e3a8a; font-weight: 700; margin-bottom: 22px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1e3a8a; color: white; padding: 9px 8px; text-align: left; font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 8px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        tr:nth-child(even) { background: #f8fafc; }
        .text-center { text-align: center; }
        .badge-fecha { display: inline-block; background: #dcfce7; color: #166534; font-weight: 700; padding: 3px 9px; border-radius: 12px; font-size: 9.5px; }
        .badge-motivo { display: inline-block; background: #dbeafe; color: #1e40af; padding: 3px 9px; border-radius: 12px; font-size: 9px; font-weight: 600; }
        .footer { text-align: center; color: #94a3b8; font-size: 9px; margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 16px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            th { background: #1e3a8a !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .badge-fecha, .badge-motivo, tr:nth-child(even) { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            tr:nth-child(even) { background: #f8fafc !important; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()"><i class="fas fa-print"></i> Guardar / Imprimir PDF</button>
        <button onclick="window.close()" style="background:#64748b;margin-left:8px;">Cerrar</button>
    </div>

    <div class="header-logo">
        <img src="<?php echo e(asset('img/logo-light.svg')); ?>" alt="SIGEJUB">
    </div>
    <h1>SIGEJUB — Sistema Integral de Gestión de Jubilaciones</h1>
    <p class="subtitle">Reporte de Próximos Jubilados — Generado <?php echo e(now()->format('d/m/Y H:i')); ?></p>
    <p class="rango">Período consultado: del <?php echo e(\Carbon\Carbon::parse($desde)->format('d/m/Y')); ?> al <?php echo e(\Carbon\Carbon::parse($hasta)->format('d/m/Y')); ?></p>

    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>TRABAJADOR</th>
                <th>CÉDULA</th>
                <th>CARGO</th>
                <th>UNIDAD / DEPTO.</th>
                <th>EDAD</th>
                <th>AÑOS SERVICIO</th>
                <th>FECHA ESTIMADA</th>
                <th>MOTIVO</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $filas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td class="text-center"><?php echo e($i + 1); ?></td>
                <td><strong><?php echo e($f['trabajador']->nombres); ?> <?php echo e($f['trabajador']->apellidos); ?></strong></td>
                <td><?php echo e($f['trabajador']->cedula ?? '—'); ?></td>
                <td><?php echo e($f['trabajador']->cargo ?? '—'); ?></td>
                <td><?php echo e($f['trabajador']->unidad_departamento ?? '—'); ?></td>
                <td class="text-center"><?php echo e($f['edad'] ?? '—'); ?></td>
                <td class="text-center"><strong><?php echo e($f['anos_calculados']); ?></strong></td>
                <td><span class="badge-fecha"><?php echo e($f['fecha_estimada']->format('d/m/Y')); ?></span></td>
                <td><span class="badge-motivo"><?php echo e($f['motivo']); ?></span></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="9" style="text-align:center; padding:24px; color:#94a3b8;">No hay jubilaciones estimadas dentro del período consultado</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p class="footer">
        Total de trabajadores: <?php echo e($total); ?> · Criterio: primer requisito que ocurra primero (60 años de edad o 25 años de servicio) ·
        Años de servicio calculados con los antecedentes de servicio registrados | SIGEJUB
    </p>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Nueva carpeta (2)\sigejub 2\original\resources\views\reportes\proximos-jubilados.blade.php ENDPATH**/ ?>