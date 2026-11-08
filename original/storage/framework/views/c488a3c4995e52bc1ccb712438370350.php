<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - SIGEJUB</title>
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
                <p class="login-info-subtitle">Define tu nueva contraseña para acceder al sistema.</p>
            </div>
        </div>

        <div class="login-form-panel">
            <div class="login-form-wrapper">
                <a href="<?php echo e(url('/login')); ?>" class="login-back">
                    <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
                </a>

                <h2 class="login-form-title">Nueva contraseña</h2>
                <p class="login-form-subtitle">Debe tener al menos 8 caracteres e incluir mayúsculas, minúsculas y números.</p>

                <form method="POST" action="<?php echo e(route('password.update')); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="token" value="<?php echo e($token); ?>">

                    <div class="login-input-group has-leading-icon">
                        <label for="resetEmail">Correo Electrónico</label>
                        <div class="login-field-control"><span class="login-field-icon" aria-hidden="true"><i class="fas fa-envelope"></i></span><input class="login-input" type="email" name="correo" id="resetEmail" value="<?php echo e($correo); ?>" required autocomplete="email"></div>
                    </div>

                    <div class="login-input-group">
                        <label for="resetPassword">Nueva Contraseña</label>
                        <div class="login-password-wrapper">
                            <input class="login-input" type="password" name="password" id="resetPassword" placeholder="Mín. 8 caracteres" required autocomplete="new-password">
                            <button type="button" class="login-toggle-password" id="toggleResetPassword">
                                <i class="fas fa-eye" id="toggleResetPasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="login-input-group">
                        <label for="resetPasswordConfirm">Confirmar Contraseña</label>
                        <div class="login-password-wrapper">
                            <input class="login-input" type="password" name="password_confirmation" id="resetPasswordConfirm" placeholder="Repite la contraseña" required autocomplete="new-password">
                            <button type="button" class="login-toggle-password" id="toggleResetConfirm">
                                <i class="fas fa-eye" id="toggleResetConfirmIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button class="login-submit" type="submit">
                        <i class="fas fa-key"></i> Restablecer contraseña
                    </button>
                </form>

                <p class="login-register-link">
                    ¿Ya tienes cuenta? <a href="<?php echo e(route('login')); ?>">Inicia sesión</a>
                </p>
            </div>
        </div>

    </div>

    <script>
    function togglePwd(inputId, iconId) {
        var input = document.getElementById(inputId);
        var icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
    document.getElementById('toggleResetPassword')?.addEventListener('click', function() {
        togglePwd('resetPassword', 'toggleResetPasswordIcon');
    });
    document.getElementById('toggleResetConfirm')?.addEventListener('click', function() {
        togglePwd('resetPasswordConfirm', 'toggleResetConfirmIcon');
    });

    document.getElementById('resetPasswordConfirm')?.addEventListener('input', function() {
        var pwd = document.getElementById('resetPassword').value;
        var confirm = this.value;
        if (confirm.length === 0) return;
        if (pwd !== confirm) {
            this.style.borderColor = '#dc2626';
        } else {
            this.style.borderColor = '#16a34a';
        }
    });
    </script>

    <?php if(session('status')): ?>
        <script>mostrarToast('<?php echo e(session('status')); ?>', 'success');</script>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <script>mostrarToast('<?php echo e($errors->first()); ?>', 'error');</script>
    <?php endif; ?>
</body>
</html><?php /**PATH C:\xampp\htdocs\Nueva carpeta (2)\sigejub 2\original\resources\views\auth\reset-password.blade.php ENDPATH**/ ?>