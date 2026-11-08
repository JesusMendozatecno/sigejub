
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil — SIGEJUB</title>
    <link rel="icon" href="<?php echo e(asset('img/logo-dark.svg')); ?>" type="image/svg+xml">
    <link rel="shortcut icon" href="<?php echo e(asset('img/logo-dark.svg')); ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?php echo e(asset('css/dashboard/dashboard.min.css')); ?>?v=<?php echo e(filemtime(public_path('css/dashboard/dashboard.min.css'))); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/fontawesome/css/all.min.css')); ?>">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/cropperjs/cropper.min.css')); ?>">
    <script defer src="<?php echo e(asset('js/cropperjs/cropper.min.js')); ?>"></script>
    <style>:root {
        --accent: <?php echo e($user->color_acento ?? '#1a365d'); ?>;
        /* Color del usuario para la pantalla de carga (independiente del tema). */
        --user-accent: <?php echo e($user->color_acento ?? '#087cf0'); ?>;
    }</style>

    <style id="profile-visual-redesign">
        /* Rediseño visual local de Mi Perfil; no modifica la lógica ni los controles existentes. */
        body:has(.profile-wrapper) {
            min-height: 100vh;
            height: auto;
            overflow-x: hidden;
            overflow-y: auto;
            position: relative;
            isolation: isolate;
            background:
                radial-gradient(ellipse at 8% 8%, rgba(96,165,250,.17) 0%, rgba(96,165,250,0) 31%),
                radial-gradient(ellipse at 96% 88%, rgba(56,189,248,.13) 0%, rgba(56,189,248,0) 29%),
                radial-gradient(circle, rgba(59,130,246,.13) 1.1px, transparent 1.5px) 0 0 / 22px 22px,
                linear-gradient(135deg, #f8fbff 0%, #edf4fc 52%, #f7faff 100%);
            color: #172b4d;
        }
        body:has(.profile-wrapper)::before,
        body:has(.profile-wrapper)::after {
            content: "";
            position: fixed;
            z-index: -1;
            pointer-events: none;
            border-radius: 50%;
        }
        body:has(.profile-wrapper)::before {
            width: 340px; height: 340px;
            top: -190px; right: -105px;
            border: 1px solid rgba(59,130,246,.14);
            box-shadow: 0 0 0 28px rgba(59,130,246,.025), 0 0 0 58px rgba(59,130,246,.02);
        }
        body:has(.profile-wrapper)::after {
            width: 270px; height: 270px;
            left: -155px; bottom: -155px;
            border: 1px solid rgba(14,165,233,.17);
            box-shadow: 0 0 0 24px rgba(14,165,233,.035), 0 0 0 50px rgba(14,165,233,.025);
        }
        .profile-wrapper { position: relative; z-index: 1; width: min(1280px, 100%); max-width: 1280px; margin: 0 auto; padding: 34px 28px 42px; }
        .profile-header { justify-content: space-between; align-items: center; gap: 18px; margin-bottom: 28px; }
        .profile-header .flex-center { align-items: center; gap: 16px !important; }
        .profile-heading-copy h1 { color: #142d52; font-size: clamp(1.65rem, 2.4vw, 2rem); letter-spacing: -.035em; font-weight: 800; line-height: 1.2; }
        .profile-page-subtitle { margin-top: 7px; color: #71819a; font-size: .94rem; }
        .btn-back { width: 46px; height: 46px; border-radius: 50%; border: 1px solid #e2eaf5; background: #fff; color: #2563eb; box-shadow: 0 5px 16px rgba(28,61,108,.09); }
        .btn-back:hover { background: #eff6ff; color: #1d4ed8; transform: translateY(-1px); }
        .profile-last-access { font-size: .8rem; color: #8190a7 !important; }
        .profile-layout { grid-template-columns: minmax(265px, 310px) minmax(0, 1fr); gap: 24px; align-items: start; }
        .profile-sidebar, .profile-content { border: 1px solid rgba(219,230,244,.85); border-radius: 22px; background: #fff; box-shadow: 0 14px 38px rgba(30,64,115,.075); overflow: hidden; }
        .profile-card { position: relative; padding: 30px 22px 27px; color: #fff; background: linear-gradient(135deg, var(--accent, #2563eb) 0%, color-mix(in srgb, var(--accent, #2563eb) 82%, #fff) 48%, color-mix(in srgb, var(--accent, #2563eb) 62%, #fff) 100%); border: 0; }
        .profile-card:before { content: ""; position: absolute; width: 190px; height: 190px; border: 1px solid rgba(255,255,255,.14); border-radius: 50%; right: -90px; top: -105px; pointer-events: none; }
        .profile-card:after { content: ""; position: absolute; width: 135px; height: 135px; border: 1px solid rgba(255,255,255,.12); border-radius: 50%; left: -82px; bottom: -75px; pointer-events: none; }
        .profile-card > * { position: relative; z-index: 1; }
        .avatar-wrap { width: 112px; height: 112px; margin-bottom: 17px; border: 4px solid rgba(255,255,255,.95); box-shadow: 0 0 0 5px rgba(255,255,255,.16), 0 10px 25px rgba(12,42,100,.22); background: color-mix(in srgb, var(--accent, #2563eb) 78%, #000 22%); }
        .avatar-wrap .avatar-placeholder { color: rgba(255,255,255,.92); }
        .avatar-overlay { background: rgba(15,42,92,.48); }
        .avatar-actions-btns { flex-wrap: wrap; }
        .profile-card .avatar-actions-btns .btn-primary { background: rgba(255,255,255,.17) !important; border: 1px solid rgba(255,255,255,.45) !important; color: #fff !important; }
        .profile-card .avatar-actions-btns .btn-danger { box-shadow: 0 3px 10px rgba(127,29,29,.14); }
        .profile-name { color: #fff; font-size: 1.18rem; font-weight: 800; overflow-wrap: anywhere; }
        .profile-email { color: rgba(255,255,255,.78); font-size: .84rem; overflow-wrap: anywhere; }
        .profile-role { display: inline-flex; align-items: center; margin-top: 8px; padding: 6px 12px; border-radius: 999px; background: #fff0f3; color: #be185d; font-size: .72rem; font-weight: 800; border: 1px solid rgba(255,255,255,.6); }
        .profile-nav { padding: 16px 14px; }
        .profile-nav-item { gap: 12px; min-height: 46px; padding: 12px 15px; border-radius: 13px; color: #64748b; font-size: .9rem; font-weight: 600; }
        .profile-nav-item i { width: 20px; font-size: 1rem; }
        .profile-nav-item:hover { background: #f3f7ff; color: #2563eb; }
        .profile-nav-item.active { background: #eaf2ff; color: #2563eb; box-shadow: inset 3px 0 0 #3b82f6; }
        .profile-nav-item[style*="color:#dc2626"] { color: #dc2626 !important; }
        .profile-nav-item[style*="color:#dc2626"]:hover { background: #fff1f2; color: #b91c1c !important; }
        .profile-nav-divider { background: #e9eff7; margin: 10px 10px; }
        .profile-content { padding: 34px 36px; min-width: 0; min-height: 520px; }
        .profile-section-heading { display: flex; align-items: center; gap: 14px; margin-bottom: 28px; }
        .profile-section-icon { display: inline-flex; flex: 0 0 48px; width: 48px; height: 48px; align-items: center; justify-content: center; border-radius: 15px; background: linear-gradient(135deg,#2563eb,#38a0f5); color: #fff; font-size: 1.12rem; box-shadow: 0 7px 15px rgba(37,99,235,.2); }
        .profile-section-heading .tab-title { margin: 0 0 4px; color: #172f54; font-size: 1.35rem; font-weight: 800; letter-spacing: -.02em; }
        .profile-section-heading .tab-subtitle { margin: 0; color: #8492a8; font-size: .88rem; }
        #formProfile .form-group { margin-bottom: 22px; }
        #formProfile .form-group label { display: flex; align-items: center; gap: 8px; margin-bottom: 9px; color: #4d607b; font-size: .79rem; font-weight: 750; text-transform: none; letter-spacing: .01em; }
        #formProfile .field-label-icon { width: 15px; color: #4285ee; font-size: .85rem; }
        #formProfile .form-input { min-height: 47px; padding: 12px 15px; border: 1px solid #e1e9f4; border-radius: 12px; background: #f6f9fd; color: #253a59; font-size: .91rem; box-shadow: inset 0 1px 2px rgba(25,55,95,.015); }
        #formProfile .form-input:focus { border-color: #77a8fb; background: #fff; box-shadow: 0 0 0 4px rgba(59,130,246,.1); }
        #formProfile .form-input:disabled { opacity: 1; color: #344865; -webkit-text-fill-color: #344865; cursor: default; }
        #formProfile .form-group:first-child { position: relative; }
        #formProfile .form-group:first-child:after { content: "\f078"; font-family: "Font Awesome 6 Free"; font-weight: 900; position: absolute; right: 16px; bottom: 16px; color: #6c8fca; font-size: .75rem; pointer-events: none; }
        #formProfile .form-group:first-child .form-input { padding-right: 42px; }
        #formProfile .flex.gap-2.mt-3 { display: flex; flex-direction: column; gap: 10px; margin-top: 28px; }
        #formProfile .profile-edit-button, #formProfile .profile-save-button, #formProfile #btnCancelarEditar { width: 100%; min-height: 49px; justify-content: center; border-radius: 12px; font-size: .92rem; font-weight: 750; }
        #formProfile .profile-edit-button { border: 0; color: #fff; background: linear-gradient(100deg,#2563eb 0%,#3b82f6 55%,#1687ef 100%); box-shadow: 0 8px 18px rgba(37,99,235,.22); }
        #formProfile .profile-edit-button:hover { filter: brightness(1.04); transform: translateY(-1px); box-shadow: 0 11px 22px rgba(37,99,235,.27); }
        #formProfile .profile-save-button { border: 0; background: linear-gradient(100deg,#059669,#10b981); color: #fff; }
        #formProfile #btnCancelarEditar { background: #f1f5f9; color: #475569; border: 1px solid #dbe4ef; }
        @media (max-width: 900px) {
            .profile-wrapper { padding: 25px 18px 34px; }
            .profile-layout { grid-template-columns: minmax(230px, 280px) minmax(0,1fr); gap: 16px; }
            .profile-content { padding: 27px 24px; }
        }
        @media (max-width: 700px) {
            .profile-wrapper { padding: 20px 14px 28px; }
            .profile-header { align-items: flex-start; }
            .profile-last-access { display: none; }
            .profile-layout { grid-template-columns: 1fr; }
            .profile-card { padding: 25px 18px; }
            .profile-nav { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 6px; }
            .profile-nav-divider { display: none; }
            .profile-nav form { min-width: 0; }
            .profile-nav-item { padding: 11px 10px; font-size: .82rem; }
            .profile-content { min-height: 0; padding: 24px 18px; }
            .profile-section-heading { margin-bottom: 24px; }
            #formProfile .grid-2 { grid-template-columns: 1fr; }
        }
        @media (max-width: 420px) {
            .profile-heading-copy h1 { font-size: 1.45rem; }
            .profile-page-subtitle { max-width: 250px; font-size: .82rem; }
            .profile-section-icon { flex-basis: 42px; width: 42px; height: 42px; }
            .profile-nav { grid-template-columns: 1fr; }
        }
    </style>

</head>
<body>
<?php echo $__env->make('partials.loading-overlay', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script>document.getElementById('loading-overlay').classList.add('active');</script>
<div class="profile-wrapper">
    <div class="profile-header">
        <div class="flex-center gap-3">
            <a href="<?php echo e(route('dashboard')); ?>" class="btn-back" title="Volver al dashboard">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="profile-heading-copy">
                <h1>Mi Perfil</h1>
                <p class="profile-page-subtitle">Gestiona tu información personal y de acceso al sistema</p>
            </div>
        </div>
        <span class="text-muted profile-last-access">Último acceso: <?php echo e($user->ultimo_acceso ? $user->ultimo_acceso->diffForHumans() : 'Primer ingreso'); ?></span>
    </div>

    <div class="profile-layout">
        <!-- Sidebar -->
        <div class="profile-sidebar">
            <div class="profile-card">
                <div class="avatar-wrap" onclick="document.getElementById('avatarInput').click()">
                    <?php if($user->avatar): ?>
                        <img id="profileAvatarImg" src="<?php echo e(asset('storage/' . $user->avatar)); ?>" alt="Avatar">
                    <?php else: ?>
                        <i class="fas fa-user fa-3x avatar-placeholder" id="profileAvatarPlaceholder"></i>
                    <?php endif; ?>
                    <div class="avatar-overlay"><i class="fas fa-camera"></i></div>
                </div>
                <input type="file" id="avatarInput" hidden accept="image/*" onchange="onAvatarSelect(event)">
                <div class="avatar-actions-btns" style="display:flex;gap:8px;justify-content:center;margin:12px 0 16px;">
                    <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('avatarInput').click()" title="Subir nueva foto de perfil" style="display:inline-flex;align-items:center;gap:6px;font-size:0.8rem;padding:6px 14px;border-radius:8px;">
                        <i class="fas fa-camera"></i> <span>Cambiar imagen</span>
                    </button>
                    <?php if($user->avatar): ?>
                    <button type="button" class="btn btn-sm btn-danger" onclick="eliminarAvatar()" title="Eliminar foto actual" style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;padding:0;border-radius:8px;background:#ef4444;border-color:#ef4444;color:#fff;">
                        <i class="fas fa-trash-can"></i>
                    </button>
                    <?php endif; ?>
                </div>
                <h2 class="profile-name"><?php echo e($user->nombre); ?></h2>
                <p class="profile-email"><?php echo e($user->correo); ?></p>
                <span class="profile-role <?php echo e($user->rol === 'superadmin' ? 'role-superadmin' : ($user->rol === 'admin' ? 'role-admin' : 'role-analista')); ?>">
                    <?php echo e($user->rol === 'admin' ? 'Administrador' : ($user->rol === 'superadmin' ? 'Superadmin' : 'Usuario')); ?>

                </span>
            </div>
            <div class="profile-nav">
                <button class="profile-nav-item active" data-tab="tab-perfil">
                    <i class="fas fa-user"></i> Mi Perfil
                </button>
                <button class="profile-nav-item" data-tab="tab-seguridad">
                    <i class="fas fa-shield"></i> Seguridad
                </button>
                <button class="profile-nav-item" data-tab="tab-configuracion">
                    <i class="fas fa-gear"></i> Configuración
                </button>
                <button class="profile-nav-item" data-tab="tab-actividad">
                    <i class="fas fa-wave-square"></i> Actividad
                </button>
                <?php if(in_array($user->rol, ['admin', 'superadmin'])): ?>
                <div class="profile-nav-divider"></div>
                <button class="profile-nav-item" data-tab="tab-admin">
                    <i class="fas fa-shield"></i> Administración
                </button>
                <?php endif; ?>
                <div class="profile-nav-divider"></div>
                <?php if(in_array($user->rol, ['admin', 'superadmin'])): ?>
                <a href="<?php echo e(route('documentacion')); ?>" class="profile-nav-item" style="text-decoration:none;cursor:pointer;">
                    <i class="fas fa-book"></i> Documentación
                </a>
                <div class="profile-nav-divider"></div>
                <?php endif; ?>
                <form method="POST" action="<?php echo e(route('logout')); ?>" style="margin:0;">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="profile-nav-item" style="color:#dc2626;">
                        <i class="fas fa-right-from-bracket"></i> Cerrar sesión
                    </button>
                </form>
            </div>
        </div>

        <!-- Content -->
        <div class="profile-content">
            <!-- TAB: MI PERFIL -->
            <div class="profile-tab active" id="tab-perfil">
                <div class="profile-section-heading">
                    <span class="profile-section-icon"><i class="fas fa-user"></i></span>
                    <div>
                        <h2 class="tab-title">Mi Perfil</h2>
                        <p class="tab-subtitle">Información básica de tu cuenta</p>
                    </div>
                </div>
                <form id="formProfile">
                    <div class="form-group">
                        <label><i class="fas fa-user-tag field-label-icon"></i> Rol</label>
                        <input type="text" class="form-input" value="<?php echo e($user->rol === 'admin' ? 'Administrador' : ($user->rol === 'superadmin' ? 'Superadmin' : 'Usuario')); ?>" disabled>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label><i class="fas fa-user field-label-icon"></i> Nombre</label>
                            <input type="text" class="form-input" name="nombre" value="<?php echo e($user->nombre); ?>" disabled oninput="this.value=this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g,'')">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-user field-label-icon"></i> Apellido</label>
                            <input type="text" class="form-input" name="apellido" value="<?php echo e($user->apellido); ?>" disabled oninput="this.value=this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g,'')">
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label><i class="fas fa-envelope field-label-icon"></i> Correo electrónico</label>
                            <input type="email" class="form-input" name="correo" value="<?php echo e($user->correo); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-phone field-label-icon"></i> Teléfono</label>
                            <input type="text" class="form-input" name="telefono" value="<?php echo e($user->telefono); ?>" disabled oninput="this.value=this.value.replace(/[^0-9+\-\s]/g,'')">
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar-days field-label-icon"></i> Fecha de nacimiento</label>
                        <input type="date" class="form-input" name="fecha_nacimiento" value="<?php echo e($user->fecha_nacimiento ? \Carbon\Carbon::parse($user->fecha_nacimiento)->format('Y-m-d') : ''); ?>" disabled>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <button type="button" class="btn btn-primary profile-edit-button" id="btnEditarPerfil"><i class="fas fa-pen-to-square"></i> Editar Datos</button>
                        <button type="submit" class="btn btn-success profile-save-button" id="btnGuardarPerfil" style="display:none;"><i class="fas fa-floppy-disk"></i> Guardar Datos</button>
                        <button type="button" class="btn btn-secondary" id="btnCancelarEditar" style="display:none;">Cancelar</button>
                    </div>
                </form>
            </div>

            <!-- TAB: SEGURIDAD -->
            <div class="profile-tab" id="tab-seguridad">
                <h2 class="tab-title">Seguridad</h2>
                <p class="tab-subtitle">Protege tu cuenta con medidas de seguridad avanzadas</p>

                <h4 style="font-size:0.9rem;color:#0f172a;margin:0 0 12px;">Cambiar contraseña</h4>
                <form id="formPassword" style="max-width:420px;">
                    <div class="form-group">
                        <label>Contraseña actual</label>
                        <input type="password" class="form-input" name="current_password" required>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Nueva contraseña</label>
                            <input type="password" class="form-input" name="password" minlength="8" required>
                        </div>
                        <div class="form-group">
                            <label>Confirmar contraseña</label>
                            <input type="password" class="form-input" name="password_confirmation" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Actualizar contraseña</button>
                </form>

                <hr style="border:0;border-top:1px solid #f1f5f9;margin:24px 0;">

                <div class="flex-between">
                    <div>
                        <h4 style="font-size:0.9rem;color:#0f172a;margin:0;">Verificación en dos pasos (2FA)</h4>
                        <p style="margin:4px 0 0;font-size:0.78rem;color:#64748b;">Añade una capa extra de seguridad a tu cuenta</p>
                    </div>
                    <div class="toggle-switch <?php echo e($user->verificacion_dos_pasos ? 'active' : ''); ?>" id="toggle2FA" onclick="toggleFA()"></div>
                </div>

                <hr style="border:0;border-top:1px solid #f1f5f9;margin:24px 0;">

                <div class="flex-between mb-4">
                    <div>
                        <h4 style="font-size:0.9rem;color:#0f172a;margin:0;">Sesiones activas</h4>
                        <p style="margin:4px 0 0;font-size:0.78rem;color:#64748b;">Dispositivos donde has iniciado sesión</p>
                    </div>
                    <button class="btn btn-secondary btn-sm" onclick="cerrarOtrasSesiones()">Cerrar otras sesiones</button>
                </div>
                <div id="sessionList">Cargando...</div>
            </div>

            <!-- TAB: CONFIGURACIÓN -->
            <div class="profile-tab" id="tab-configuracion">
                <h2 class="tab-title">Configuración del Sistema</h2>
                <p class="tab-subtitle">Personaliza la apariencia y preferencias del sistema</p>

                <h4 style="font-size:0.9rem;color:#0f172a;margin:0 0 12px;">Tema</h4>
                <div class="theme-toggle mb-4">
                    <div class="theme-option <?php echo e($user->tema === 'light' ? 'selected' : ''); ?>" data-theme="light" onclick="cambiarTema('light')">
                        <i class="fas fa-sun fa-fw"></i>
                        <span>Claro</span>
                    </div>
                    <div class="theme-option <?php echo e($user->tema === 'dark' ? 'selected' : ''); ?>" data-theme="dark" onclick="cambiarTema('dark')">
                        <i class="fas fa-moon fa-fw"></i>
                        <span>Oscuro</span>
                    </div>
                </div>

                <h4 style="font-size:0.9rem;color:#0f172a;margin:0 0 12px;">Idioma</h4>
                <div style="max-width:200px;">
                    <select class="form-input" id="selectLanguage" onchange="cambiarIdioma(this.value)">
                        <option value="es" <?php echo e($user->idioma === 'es' ? 'selected' : ''); ?>>Español</option>
                        <option value="en" <?php echo e($user->idioma === 'en' ? 'selected' : ''); ?>>English</option>
                    </select>
                </div>

                <h4 style="font-size:0.9rem;color:#0f172a;margin:16px 0 12px;">Color principal</h4>
                <div class="color-presets" id="colorPresets">
                    <?php $colors = ['#1a365d','#2563eb','#7c3aed','#db2777','#dc2626','#ea580c','#ca8a04','#16a34a','#0d9488']; ?>
                    <?php $__currentLoopData = $colors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="color-preset <?php echo e($user->color_acento === $c ? 'selected' : ''); ?>" style="background:<?php echo e($c); ?>;" data-color="<?php echo e($c); ?>" onclick="cambiarColor('<?php echo e($c); ?>')"></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <hr style="border:0;border-top:1px solid #f1f5f9;margin:24px 0;">

                <h4 style="font-size:0.9rem;color:#0f172a;margin:0 0 12px;">Notificaciones</h4>
                <div class="toggle-wrap">
                    <div><div class="toggle-label">Notificaciones por correo</div><div class="toggle-desc">Recibe alertas importantes en tu email</div></div>
                    <select class="form-input" style="width:auto;padding:6px 10px;" id="notifEmail" onchange="guardarNotificaciones()">
                        <option value="all" <?php echo e($user->notificacion_correo === 'all' ? 'selected' : ''); ?>>Todas</option>
                        <option value="important" <?php echo e($user->notificacion_correo === 'important' ? 'selected' : ''); ?>>Solo importantes</option>
                        <option value="none" <?php echo e($user->notificacion_correo === 'none' ? 'selected' : ''); ?>>Ninguna</option>
                    </select>
                </div>
                <div class="toggle-wrap">
                    <div><div class="toggle-label">Notificaciones en sistema</div><div class="toggle-desc">Alertas dentro de la plataforma</div></div>
                    <select class="form-input" style="width:auto;padding:6px 10px;" id="notifSystem" onchange="guardarNotificaciones()">
                        <option value="all" <?php echo e($user->notificacion_sistema === 'all' ? 'selected' : ''); ?>>Todas</option>
                        <option value="important" <?php echo e($user->notificacion_sistema === 'important' ? 'selected' : ''); ?>>Solo importantes</option>
                        <option value="none" <?php echo e($user->notificacion_sistema === 'none' ? 'selected' : ''); ?>>Ninguna</option>
                    </select>
                </div>
                <div class="toggle-wrap">
                    <div><div class="toggle-label">Perfil público</div><div class="toggle-desc">Permitir que otros usuarios vean tu perfil</div></div>
                    <div class="toggle-switch <?php echo e($user->perfil_publico ? 'active' : ''); ?>" id="togglePrivacy" onclick="togglePrivacidad()"></div>
                </div>
            </div>

            <!-- TAB: ACTIVIDAD -->
            <div class="profile-tab" id="tab-actividad">
                <h2 class="tab-title">Mi Actividad</h2>
                <p class="tab-subtitle">Historial de tus acciones en el sistema</p>

                <div class="grid-3 mb-4" id="statsGrid">
                    <div class="stat-card-mini"><h4 id="statTrabajadores">0</h4><p>Trabajadores capturados</p></div>
                    <div class="stat-card-mini"><h4 id="statSolicitudes">0</h4><p>Solicitudes gestionadas</p></div>
                    <div class="stat-card-mini"><h4 id="statExpedientes">0</h4><p>Documentos procesados</p></div>
                </div>
                <div class="flex gap-4 text-muted" style="font-size:0.8rem;padding:0 0 16px;">
                    <span>🕐 Miembro desde: <strong id="statMiembro">—</strong></span>
                    <span>📱 Último acceso: <strong id="statUltimoAcceso">—</strong></span>
                    <span>🌐 IP: <strong id="statUltimaIP">—</strong></span>
                </div>
                <div id="activityList">
                    <p class="text-muted text-center" style="padding:20px;">Cargando actividad...</p>
                </div>
                <div class="activity-pagination" id="activityPagination"></div>
            </div>

            <!-- TAB: ADMINISTRACIÓN (solo admin/superadmin) -->
            <?php if(in_array($user->rol, ['admin', 'superadmin'])): ?>
            <div class="profile-tab" id="tab-admin">
                <h2 class="tab-title">Administración</h2>
                <p class="tab-subtitle">Gestión de usuarios y configuración global del sistema</p>

                <div style="display:flex;gap:12px;margin-bottom:16px;">
                    <button class="btn btn-secondary btn-sm active admin-tab-btn" data-admin-tab="admin-usuarios" onclick="cambiarAdminTab('admin-usuarios')">
                        <i class="fas fa-users"></i> Usuarios
                    </button>
                    <button class="btn btn-secondary btn-sm admin-tab-btn" data-admin-tab="admin-actividad" onclick="cambiarAdminTab('admin-actividad')">
                        <i class="fas fa-wave-square"></i> Actividad global
                    </button>
                    <button class="btn btn-secondary btn-sm admin-tab-btn" data-admin-tab="admin-config" onclick="cambiarAdminTab('admin-config')">
                        <i class="fas fa-globe"></i> Config. global
                    </button>
                </div>

                <!-- Sub-tab: Usuarios -->
                <div class="admin-subtab" id="admin-usuarios">
                    <div class="search-bar">
                        <input type="text" id="adminSearch" placeholder="Buscar usuario..." oninput="cargarAdminUsuarios(1)">
                        <select id="adminRoleFilter" class="form-input" style="width:auto;" onchange="cargarAdminUsuarios(1)">
                            <option value="">Todos los roles</option>
                            <option value="admin">Admin</option>
                            <option value="usuario">Usuario</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                    <div class="admin-table-wrapper" style="overflow-x:auto; overflow-y:auto; max-height:calc(100vh - 320px); -webkit-overflow-scrolling: touch;">
                        <table class="admin-table">
                            <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Registro</th><th>Acción</th></tr></thead>
                            <tbody id="adminUsersBody"></tbody>
                        </table>
                    </div>
                    <div class="activity-pagination" id="adminUsersPagination"></div>
                </div>

                <!-- Sub-tab: Actividad global -->
                <div class="admin-subtab" id="admin-actividad" style="display:none;">
                    <div id="adminActivityList">
                        <p class="text-muted text-center" style="padding:20px;">Cargando actividad global...</p>
                    </div>
                    <div class="activity-pagination" id="adminActivityPagination"></div>
                </div>

                <!-- Sub-tab: Config global -->
                <div class="admin-subtab" id="admin-config" style="display:none;">
                    <form id="formGlobalConfig">
                        <div class="form-group">
                            <label>Nombre del sistema</label>
                            <input type="text" class="form-input" name="app_name" value="SIGEJUB" placeholder="SIGEJUB">
                        </div>
                        <div class="form-group">
                            <label>Tema por defecto</label>
                            <select class="form-input" name="default_theme" style="max-width:200px;">
                                <option value="light">Claro</option>
                                <option value="dark">Oscuro</option>
                            </select>
                        </div>
                        <div class="toggle-wrap">
                            <div><div class="toggle-label">Modo mantenimiento</div><div class="toggle-desc">Deshabilitar acceso al sistema</div></div>
                            <input type="checkbox" name="maintenance_mode" value="1">
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Guardar configuración global</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Cropper Modal -->
<div class="modal-overlay" id="cropModal">
    <div class="modal-box" style="display:flex;flex-direction:column;max-height:90vh;max-width:520px;padding:24px;">
        <div style="flex-shrink:0;">
            <h3 style="margin:0 0 6px;color:var(--text-primary);"><i class="fas fa-crop-simple"></i> Ajustar foto de perfil</h3>
            <p style="font-size:0.85rem;color:var(--text-muted);margin:0 0 16px;">Arrastra para encuadrar y recortar la imagen al área deseada</p>
        </div>
        <div id="cropContainer" style="max-height:340px;min-height:220px;overflow:hidden;background:#0f172a;border-radius:10px;margin-bottom:16px;"></div>
        <div class="modal-actions" style="margin-top:auto;flex-shrink:0;padding-top:14px;border-top:1px solid var(--border);display:flex;gap:12px;justify-content:flex-end;">
            <button type="button" class="btn btn-secondary" onclick="cerrarCrop()" style="padding:10px 18px;border-radius:8px;">
                <i class="fas fa-xmark"></i> Cancelar
            </button>
            <button type="button" class="btn btn-primary" id="btnConfirmarCrop" onclick="confirmarCrop()" style="padding:10px 20px;border-radius:8px;display:inline-flex;align-items:center;gap:8px;font-weight:600;background:var(--accent);border-color:var(--accent);color:#fff;">
                <i class="fas fa-floppy-disk"></i> Guardar foto
            </button>
        </div>
    </div>
</div>

<!-- Modal Enviar Notificación -->
<div class="modal-overlay" id="modalEnviarNotificacion">
    <div class="modal-box" style="max-width:480px;">
        <h3><i class="fas fa-bell"></i> Enviar mensaje</h3>
        <form id="formEnviarNotificacion">
            <input type="hidden" name="user_id" id="notifUserId">
            <div class="form-group">
                <label>Para:</label>
                <p style="margin:4px 0 8px;font-weight:600;font-size:0.95rem;color:#0f172a;" id="notifUserName">—</p>
            </div>
            <div class="form-group">
                <label>Título</label>
                <input type="text" class="form-input" name="titulo" required placeholder="Ej: Actualización importante">
            </div>
            <div class="form-group">
                <label>Mensaje</label>
                <textarea class="form-input" name="mensaje" required placeholder="Escribe tu mensaje..." rows="4" style="resize:vertical;"></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalNotificacion()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Enviar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Confirmar Eliminación de Usuario -->
<div class="modal-overlay" id="modalEliminarUsuario">
    <div class="modal-box" style="max-width:400px;text-align:center;">
        <div style="width:56px;height:56px;border-radius:50%;background:var(--error-light);color:var(--error);display:flex;align-items:center;justify-content:center;font-size:1.4rem;margin:0 auto 16px;"><i class="fas fa-triangle-exclamation"></i></div>
        <h3 style="color:var(--text-primary);">Eliminar usuario</h3>
        <p style="font-size:0.9rem;color:var(--text-secondary);margin:0 0 4px;">¿Desea eliminar a <strong id="deleteUserName" style="color:var(--text-primary);">—</strong>?</p>
        <p style="font-size:0.8rem;color:var(--text-muted);margin:0 0 18px;">Esta acción es permanente y no se puede deshacer.</p>
        <div class="modal-actions" style="justify-content:center;">
            <button class="btn btn-secondary" onclick="cerrarModalEliminarUsuario()"><i class="fas fa-xmark"></i> No</button>
            <button class="btn btn-danger" onclick="confirmarEliminarUsuario()"><i class="fas fa-trash-can"></i> Sí, eliminar</button>
        </div>
    </div>
</div>

<script>
window.SIGEJUB_USER_ID=<?php echo e($user->id); ?>;window.SIGEJUB_USER_ROL='<?php echo e($user->rol); ?>';
window.SIGEJUB_STORAGE_URL='<?php echo e(asset("storage")); ?>';
window.SIGEJUB_PROFILE_THEME='<?php echo e($user->tema); ?>';
window.SIGEJUB_ACCENT_COLOR='<?php echo e($user->color_acento ?? "#1a365d"); ?>';
</script>

<script defer src="<?php echo e(asset('js/dashboard/modales.js')); ?>?v=<?php echo e(filemtime(public_path('js/dashboard/modales.js'))); ?>"></script>
<script defer src="<?php echo e(asset('js/profile.js')); ?>?v=<?php echo e(filemtime(public_path('js/profile.js'))); ?>"></script>


<script>
(function() {
    var overlay = document.getElementById('loading-overlay');
    if (overlay) {
        window.addEventListener('load', function() {
            setTimeout(function() { overlay.classList.remove('active'); }, 350);
        });
    }
    function mostrarCarga(texto) {
        if (!overlay) return;
        var t = document.getElementById('loadingText');
        if (t && texto) t.textContent = texto;
        overlay.classList.add('active');
    }
    // Salir del perfil hacia el dashboard u otras páginas con pantalla de carga.
    document.addEventListener('click', function(e) {
        var a = e.target.closest('a[href]');
        if (!a) return;
        var href = a.getAttribute('href');
        if (!href || href.charAt(0) === '#' || a.target === '_blank' || a.hasAttribute('download')) return;
        if (a.dataset.noLoading === 'true') return;
        mostrarCarga(a.dataset.loading || 'Cargando...');
    }, true);
})();
</script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Nueva carpeta (2)\sigejub 2\original\resources\views\usuarios\user.blade.php ENDPATH**/ ?>