<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - SIGEJUB</title>
    <link rel="icon" href="<?php echo e(asset('img/logo-dark.svg')); ?>" type="image/svg+xml">
    <link rel="shortcut icon" href="<?php echo e(asset('img/logo-dark.svg')); ?>" type="image/svg+xml">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="<?php echo e(asset('css/fontawesome/css/all.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/login.css')); ?>">
    <script src="<?php echo e(asset('js/theme-welcome.js')); ?>" defer></script>
</head>
<body>
    <?php echo $__env->make('partials.loading-overlay', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('partials.toast', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="login-split">

        <div class="login-info">
            <div class="login-info-brand">
                <img src="<?php echo e(asset('img/logo-light.svg')); ?>" alt="" aria-hidden="true">
                <div class="login-info-brand-copy">
                    <strong>SIGEJUB</strong>
                    <span>Sistema Integral de Gestión de Jubilaciones</span>
                </div>
            </div>
            <div class="login-info-content">
                <div class="login-info-logo">
                    <img src="<?php echo e(asset('img/logo-light.svg')); ?>" alt="SIGEJUB">
                </div>
                <h1 class="login-info-title">Gestión de Jubilaciones</h1>
                <p class="login-info-subtitle">Restablece el acceso a tu cuenta de forma segura.</p>
            </div>
        </div>

        <div class="login-form-panel">
            <div class="login-form-wrapper">
                <a href="<?php echo e(url('/login')); ?>" class="login-back">
                    <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
                </a>

                <h2 class="login-form-title">¿Olvidaste tu contraseña?</h2>
                <p class="login-form-subtitle">Ingresa tu correo electrónico y te enviaremos un enlace para restablecerla.</p>

                <form method="POST" action="<?php echo e(route('password.email')); ?>">
                    <?php echo csrf_field(); ?>

                    <div class="login-input-group has-leading-icon">
                        <label for="forgotEmail">Correo Electrónico</label>
                        <div class="login-field-control"><span class="login-field-icon" aria-hidden="true"><i class="fas fa-envelope"></i></span><input class="login-input" type="email" name="correo" id="forgotEmail" placeholder="tu@correo.com" value="<?php echo e(old('correo')); ?>" required autofocus autocomplete="email"></div>
                    </div>

                    <button class="login-submit" type="submit">
                        <i class="fas fa-paper-plane"></i> Enviar enlace de recuperación
                    </button>
                </form>

                <p class="login-register-link">
                    ¿Recordaste tu contraseña? <a href="<?php echo e(route('login')); ?>">Inicia sesión</a>
                </p>
            </div>
        </div>

    </div>

    <?php if(session('status')): ?>
        <script>mostrarToast('<?php echo e(session('status')); ?>', 'success');</script>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <script>mostrarToast('<?php echo e($errors->first()); ?>', 'error');</script>
    <?php endif; ?>
</body>
</html><?php /**PATH C:\xampp\htdocs\Nueva carpeta (2)\sigejub 2\original\resources\views\auth\forgot-password.blade.php ENDPATH**/ ?>