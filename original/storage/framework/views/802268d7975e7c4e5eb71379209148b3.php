
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>SIGEJUB — Relación de Cargos y Tiempo de Servicio</title>
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
        h1 { text-align: center; font-size: 17px; color: #0f172a; margin-bottom: 4px; }
        .subtitle { text-align: center; color: #64748b; margin-bottom: 20px; font-size: 12px; }
        .ficha { border: 1px solid #cbd5e1; border-radius: 10px; padding: 14px 16px; margin-bottom: 18px; background: #f8fafc; }
        .ficha-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px 18px; }
        .ficha-item span { display: block; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
        .ficha-item strong { font-size: 12px; color: #0f172a; }
        .section-title { font-size: 12px; font-weight: 700; color: #1e3a8a; text-transform: uppercase; letter-spacing: 0.6px; margin: 16px 0 8px; display: flex; align-items: center; gap: 7px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1e3a8a; color: white; padding: 9px 8px; text-align: left; font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 8px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        tr:nth-child(even) { background: #f8fafc; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge-actual { display: inline-block; background: #dcfce7; color: #166534; font-weight: 700; padding: 3px 9px; border-radius: 12px; font-size: 9px; }
        .badge-externo { display: inline-block; background: #f1f5f9; color: #475569; font-weight: 600; padding: 3px 9px; border-radius: 12px; font-size: 9px; }
        .total-row td { border-top: 2px solid #1a365d; font-weight: 700; color: #1a365d; }
        .totales { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 14px; }
        .total-box { border: 1px solid #cbd5e1; border-radius: 10px; padding: 10px 14px; text-align: center; background: #f8fafc; }
        .total-box span { display: block; font-size: 9px; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; }
        .total-box strong { font-size: 16px; color: #1e3a8a; }
        .firma { display: flex; justify-content: space-between; gap: 40px; margin-top: 54px; }
        .firma div { flex: 1; text-align: center; border-top: 1px solid #334155; padding-top: 6px; font-size: 10px; color: #475569; }
        .footer { text-align: center; color: #94a3b8; font-size: 9px; margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 16px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            th { background: #1e3a8a !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .badge-actual, .badge-externo, tr:nth-child(even), .ficha, .total-box { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
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
    <p class="subtitle">Relación de Cargos y Tiempo de Servicio — Generado <?php echo e(now()->format('d/m/Y H:i')); ?></p>

    <div class="ficha">
        <div class="ficha-grid">
            <div class="ficha-item"><span>Trabajador</span><strong><?php echo e($trabajador->nombres); ?> <?php echo e($trabajador->apellidos); ?></strong></div>
            <div class="ficha-item"><span>Cédula</span><strong><?php echo e($trabajador->cedula ?? '—'); ?></strong></div>
            <div class="ficha-item"><span>Grado</span><strong><?php echo e($trabajador->grado_nivel ?? '—'); ?></strong></div>
            <div class="ficha-item"><span>Cargo actual</span><strong><?php echo e($trabajador->cargo ?? '—'); ?></strong></div>
            <div class="ficha-item"><span>Unidad / Depto.</span><strong><?php echo e($trabajador->unidad_departamento ?? '—'); ?></strong></div>
            <div class="ficha-item"><span>Condición laboral</span><strong><?php echo e($condicion ?? $trabajador->tipo_contrato ?? '—'); ?></strong></div>
            <div class="ficha-item"><span>Dedicación</span><strong><?php echo e($trabajador->dedicacion ?? '—'); ?></strong></div>
            <div class="ficha-item"><span>Fecha de ingreso</span><strong><?php echo e($trabajador->fecha_ingreso ? \Carbon\Carbon::parse($trabajador->fecha_ingreso)->format('d/m/Y') : '—'); ?></strong></div>
            <div class="ficha-item"><span>Fecha de nacimiento</span><strong><?php echo e($trabajador->fecha_nacimiento ? \Carbon\Carbon::parse($trabajador->fecha_nacimiento)->format('d/m/Y') : '—'); ?></strong></div>
        </div>
    </div>

    <div class="section-title"><i class="fas fa-clock-rotate-left"></i> Períodos de Servicio Registrados</div>
    <table>
        <thead>
            <tr>
                <th>DESDE</th>
                <th>HASTA</th>
                <th>AÑOS</th>
                <th>INSTITUCIÓN / ENTIDAD</th>
                <th>CARGO</th>
                <th>CONDICIÓN</th>
                <th>DEDICACIÓN</th>
                <th>TIPO</th>
                <th>DOCS.</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $periodos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($p['fecha_inicio'] ? \Carbon\Carbon::parse($p['fecha_inicio'])->format('d/m/Y') : '—'); ?></td>
                <td><?php echo e($p['fecha_fin'] ? \Carbon\Carbon::parse($p['fecha_fin'])->format('d/m/Y') : ($p['vigente'] ? 'Presente' : '—')); ?></td>
                <td class="text-center"><strong><?php echo e(number_format($p['anios'], 1, ',', '.')); ?></strong></td>
                <td><strong><?php echo e($p['institucion']); ?></strong></td>
                <td><?php echo e($p['cargo'] ?? '—'); ?></td>
                <td><?php echo e($p['condicion_laboral'] ?? '—'); ?></td>
                <td><?php echo e($p['dedicacion'] ?? '—'); ?></td>
                <td><?php echo $p['es_actual'] ? '<span class="badge-actual">INSTITUCIÓN ACTUAL</span>' : '<span class="badge-externo">EXTERNO</span>'; ?></td>
                <td class="text-center"><?php echo e($p['documentos']); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="9" style="text-align:center; padding:24px; color:#94a3b8;">No hay períodos de servicio registrados en antecedentes</td>
            </tr>
            <?php endif; ?>
            <?php if($totalPeriodos > 0): ?>
            <tr class="total-row">
                <td colspan="2">TOTAL DE PERÍODOS REGISTRADOS</td>
                <td class="text-center"><?php echo e($totalPeriodos); ?></td>
                <td colspan="6"></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="totales">
        <div class="total-box">
            <span>Años acreditados en ficha</span>
            <strong><?php echo e($anosAcreditados); ?></strong>
        </div>
        <div class="total-box">
            <span>Tiempo total calculado</span>
            <strong><?php echo e($anosCalculados); ?> años</strong>
        </div>
        <div class="total-box">
            <span>Antecedentes con documentos</span>
            <strong><?php echo e($periodos->where('documentos', '>', 0)->count()); ?> / <?php echo e($totalPeriodos); ?></strong>
        </div>
    </div>

    <div class="firma">
        <div>Elaborado por<br>SIGEJUB</div>
        <div>Revisado por<br>Coordinación de Jubilaciones</div>
        <div>Autorizado por<br>Rectoría</div>
    </div>

    <p class="footer">
        El tiempo total calculado considera los períodos registrados en antecedentes de servicio y nunca es menor a los años acreditados en la ficha | SIGEJUB
    </p>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Nueva carpeta (2)\sigejub 2\original\resources\views\reportes\relacion-cargos.blade.php ENDPATH**/ ?>