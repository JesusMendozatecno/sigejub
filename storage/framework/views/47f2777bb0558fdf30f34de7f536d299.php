<?php
    $types = [
        'feature' => ['Mejora', '#'],
        'fix' => ['Corrección', '#'],
        'security' => ['Seguridad', '#'],
        'improvement' => ['Optimización', '#'],
        'style' => ['Diseño', '#'],
        'docs' => ['Documentación', '#'],
        'change' => ['Cambio', '#'],
    ];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentación — SIGEJUB</title>
    <link rel="icon" href="<?php echo e(asset('img/descarga (1).png')); ?>" type="image/png">
    <link rel="stylesheet" href="<?php echo e(asset('css/dashboard/dashboard.min.css')); ?>?v=<?php echo e(filemtime(public_path('css/dashboard/dashboard.min.css'))); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/fontawesome/css/all.min.css')); ?>">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <style>
        :root { --accent: <?php echo e(auth()->user()->color_acento ?? '#1a365d'); ?>; }
        body { margin: 0; font-family: 'Inter', system-ui, sans-serif; background: #f1f5f9; min-height: 100vh; }
        .doc-wrapper { max-width: 1000px; margin: 0 auto; padding: 24px; }
        .doc-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .doc-header h1 { font-size: 1.5rem; color: #0f172a; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 10px; }
        .btn-back { width: 40px; height: 40px; border-radius: 10px; border: 1px solid #e2e8f0; background: white; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #64748b; transition: all 0.2s; text-decoration: none; flex-shrink: 0; }
        .btn-back:hover { background: #f8fafc; color: #0f172a; border-color: #cbd5e1; }
        .doc-empty { background: white; border-radius: 16px; padding: 60px 40px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.04); }
        .doc-empty i { font-size: 3rem; color: #cbd5e1; margin-bottom: 16px; }
        .doc-empty h2 { font-size: 1.1rem; color: #64748b; margin: 0 0 8px; font-weight: 600; }
        .doc-empty p { font-size: 0.85rem; color: #94a3b8; margin: 0; }
        .doc-day { margin-bottom: 24px; }
        .doc-day-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding: 0 4px; }
        .doc-day-header h2 { font-size: 1rem; color: #0f172a; font-weight: 700; margin: 0; }
        .doc-day-header span { font-size: 0.78rem; color: #94a3b8; background: #e2e8f0; padding: 2px 10px; border-radius: 12px; font-weight: 600; }
        .doc-card { background: white; border-radius: 12px; padding: 16px 20px; margin-bottom: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); display: flex; gap: 14px; align-items: flex-start; border-left: 4px solid #e2e8f0; }
        .doc-card.feature { border-left-color: #22c55e; }
        .doc-card.fix { border-left-color: #f59e0b; }
        .doc-card.security { border-left-color: #ef4444; }
        .doc-card.improvement { border-left-color: #3b82f6; }
        .doc-card.style { border-left-color: #a855f7; }
        .doc-card.docs { border-left-color: #64748b; }
        .doc-card.change { border-left-color: #94a3b8; }
        .doc-card .doc-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.85rem; }
        .doc-card.feature .doc-icon { background: #f0fdf4; color: #16a34a; }
        .doc-card.fix .doc-icon { background: #fff7ed; color: #d97706; }
        .doc-card.security .doc-icon { background: #fef2f2; color: #dc2626; }
        .doc-card.improvement .doc-icon { background: #eff6ff; color: #2563eb; }
        .doc-card.style .doc-icon { background: #f5f3ff; color: #7c3aed; }
        .doc-card.docs .doc-icon { background: #f1f5f9; color: #64748b; }
        .doc-card .doc-body { flex: 1; min-width: 0; }
        .doc-card .doc-body h3 { font-size: 0.92rem; font-weight: 600; color: #0f172a; margin: 0 0 4px; }
        .doc-card .doc-body p { font-size: 0.82rem; color: #64748b; margin: 0 0 8px; line-height: 1.4; }
        .doc-card .doc-meta { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; font-size: 0.72rem; color: #94a3b8; }
        .doc-card .doc-meta .author { font-weight: 600; color: #64748b; }
        .doc-card .doc-meta .badge { padding: 2px 8px; border-radius: 6px; font-weight: 600; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.3px; }
        .doc-card .doc-meta .badge-section { background: #f1f5f9; color: #475569; }
        .doc-card .doc-meta .hash { font-family: monospace; color: #94a3b8; }
        .doc-card .doc-desc { margin-top: 8px; padding: 10px 12px; background: #f8fafc; border-radius: 8px; font-size: 0.8rem; color: #475569; line-height: 1.5; white-space: pre-wrap; display: none; }
        .doc-card .doc-desc.show { display: block; }
        .doc-card .toggle-desc { background: none; border: none; color: #3b82f6; cursor: pointer; font-size: 0.75rem; font-weight: 600; padding: 0; }
        .doc-card .toggle-desc:hover { text-decoration: underline; }

        body.dark-mode { background: #0f172a; }
        body.dark-mode .doc-header h1 { color: #f1f5f9; }
        body.dark-mode .btn-back { background: #1e293b; border-color: #334155; color: #94a3b8; }
        body.dark-mode .doc-card { background: #1e293b; }
        body.dark-mode .doc-card .doc-body h3 { color: #f1f5f9; }
        body.dark-mode .doc-card .doc-body p { color: #94a3b8; }
        body.dark-mode .doc-card .doc-meta { color: #64748b; }
        body.dark-mode .doc-card .doc-meta .author { color: #94a3b8; }
        body.dark-mode .doc-card .doc-desc { background: #334155; color: #cbd5e1; }
        body.dark-mode .doc-day-header h2 { color: #f1f5f9; }
        body.dark-mode .doc-empty { background: #1e293b; }
        body.dark-mode .doc-empty h2 { color: #94a3b8; }
        body.dark-mode .doc-empty i { color: #475569; }
    </style>
</head>
<body class="<?php echo e(auth()->user()->tema === 'dark' ? 'dark-mode' : ''); ?>">
<div class="doc-wrapper">
    <div class="doc-header">
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="<?php echo e(route('dashboard')); ?>" class="btn-back" title="Volver al dashboard">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1><i class="fas fa-book" style="color:var(--accent);"></i> Documentación del Proyecto</h1>
        </div>
        <button class="btn btn-primary" style="padding: 10px 20px;border-radius: 10px;font-size: 0.85rem;font-weight: 600;cursor: pointer;border: none;background: var(--accent);color: white;display: flex;align-items: center;gap: 8px;" onclick="generarChangelog()">
            <i class="fas fa-sync"></i> Sincronizar cambios
        </button>
    </div>

    <?php if($grouped->isEmpty()): ?>
        <div class="doc-empty">
            <i class="fas fa-book-open"></i>
            <h2>Aún no hay cambios registrados</h2>
            <p>Haz clic en "Sincronizar cambios" para generar el changelog desde git.</p>
        </div>
    <?php else: ?>
        <?php $__currentLoopData = $grouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $logs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="doc-day">
                <div class="doc-day-header">
                    <h2><?php echo e(\Carbon\Carbon::parse($date)->locale('es')->isoFormat('D [de] MMMM [del] YYYY')); ?></h2>
                    <span><?php echo e($logs->count()); ?> <?php echo e($logs->count() === 1 ? 'cambio' : 'cambios'); ?></span>
                </div>
                <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $typeInfo = $types[$log->tipo] ?? ['Cambio', 'change'];
                        $icons = ['feature'=>'fa-star','fix'=>'fa-wrench','security'=>'fa-shield','improvement'=>'fa-arrow-trend-up','style'=>'fa-palette','docs'=>'fa-book','change'=>'fa-circle'];
                        $icon = $icons[$log->tipo] ?? 'fa-circle';
                    ?>
                    <div class="doc-card <?php echo e($log->tipo); ?>">
                        <div class="doc-icon"><i class="fas <?php echo e($icon); ?>"></i></div>
                        <div class="doc-body">
                            <h3><?php echo e($log->mensaje_commit); ?></h3>
                            <?php if($log->descripcion): ?>
                                <p><button class="toggle-desc" onclick="this.nextElementSibling.classList.toggle('show');this.textContent=this.nextElementSibling.classList.contains('show')?'Ocultar detalle':'Ver detalle'">Ver detalle</button></p>
                                <div class="doc-desc"><?php echo e($log->descripcion); ?></div>
                            <?php endif; ?>
                            <div class="doc-meta">
                                <span class="author"><i class="fas fa-user"></i> <?php echo e($log->nombre_autor); ?></span>
                                <?php if($log->seccion): ?><span class="badge badge-section"><?php echo e(ucfirst($log->seccion)); ?></span><?php endif; ?>
                                <span class="badge" style="background:<?php echo e(['feature'=>'#dcfce7','fix'=>'#fef3c7','security'=>'#fee2e2','improvement'=>'#dbeafe','style'=>'#ede9fe','docs'=>'#f1f5f9','change'=>'#f8fafc'][$log->tipo] ?? '#f1f5f9'); ?>;color:<?php echo e(['feature'=>'#16a34a','fix'=>'#d97706','security'=>'#dc2626','improvement'=>'#2563eb','style'=>'#7c3aed','docs'=>'#64748b','change'=>'#64748b'][$log->tipo] ?? '#64748b'); ?>"><?php echo e($typeInfo[0]); ?></span>
                                <span class="hash"><?php echo e(substr($log->hash_commit, 0, 8)); ?></span>
                                <span><?php echo e($log->created_at->locale('es')->diffForHumans()); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
</div>

<script>
window.addEventListener('DOMContentLoaded', function() {
    if ('<?php echo e(auth()->user()->tema); ?>' === 'dark') document.body.classList.add('dark-mode');
});

async function generarChangelog() {
    const btn = document.querySelector('.btn-primary');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sincronizando...';
    try {
        const resp = await fetch('/documentacion/generar', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const data = await resp.json();
        location.reload();
    } catch (e) {
        alert('Error al sincronizar: ' + e.message);
        btn.innerHTML = '<i class="fas fa-sync"></i> Sincronizar cambios';
        btn.disabled = false;
    }
}
</script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\sigejub 2\resources\views/usuarios/documentacion.blade.php ENDPATH**/ ?>