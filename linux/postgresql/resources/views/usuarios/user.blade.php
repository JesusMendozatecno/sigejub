{{-- user.blade.php - Página de perfil de usuario con pestañas: Mi Perfil, Seguridad (2FA, sesiones), Configuración (tema, idioma, color), Actividad y Administración. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil — SIGEJUB</title>
    <link rel="icon" href="{{ asset('img/logo-dark.svg') }}" type="image/svg+xml">
    <link rel="shortcut icon" href="{{ asset('img/logo-dark.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.min.css') }}?v={{ substr(md5_file(public_path('css/dashboard/dashboard.min.css')), 0, 10) }}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome/css/all.min.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/cropperjs/cropper.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/modal-layout.css') }}?v={{ substr(md5_file(public_path('css/dashboard/modal-layout.css')), 0, 10) }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/table-layout.css') }}?v={{ substr(md5_file(public_path('css/dashboard/table-layout.css')), 0, 10) }}">
    <script defer src="{{ asset('js/cropperjs/cropper.min.js') }}"></script>
    <style>:root {
        --accent: {{ $user->color_acento ?? '#1a365d' }};
        --accent-dark: color-mix(in srgb, var(--accent) 72%, #000 28%);
        --accent-soft: color-mix(in srgb, var(--accent) 12%, transparent);
        /* Color del usuario para la pantalla de carga (independiente del tema). */
        --user-accent: {{ $user->color_acento ?? '#087cf0' }};
    }
</style>

<style id="avatar-crop-circle">
    /* Recorte circular del avatar */
        #cropContainer .cropper-bg { background-image: none; background: #0f172a; }
        #cropModal .cropper-view-box { border-radius: 50%; outline: none; box-shadow: 0 0 0 2px rgba(255,255,255,.9); }
        #cropModal .cropper-face { border-radius: 50%; background-color: transparent; }
        #cropModal .cropper-line,
        #cropModal .cropper-point,
        #cropModal .cropper-dashed,
        #cropModal .cropper-center { display: none; }
        #cropModal .cropper-modal { background: #0b1220; opacity: .62; }
</style>

    <style id="profile-visual-redesign">
        /* Rediseño visual de Mi Perfil: solo presentación, sin tocar la lógica. */
        html:has(.profile-wrapper) { background: color-mix(in srgb, var(--accent, #1a365d) 6%, #f7fafd); }

        body:has(.profile-wrapper) {
            min-height: 100vh;
            height: auto;
            overflow-x: hidden;
            overflow-y: auto;
            background-color: color-mix(in srgb, var(--accent, #1a365d) 6%, #f7fafd);
            background-image:
                radial-gradient(ellipse at 6% 4%, color-mix(in srgb, var(--accent, #1a365d) 18%, transparent) 0%, transparent 36%),
                radial-gradient(ellipse at 97% 94%, color-mix(in srgb, var(--accent, #1a365d) 14%, transparent) 0%, transparent 34%),
                radial-gradient(circle, color-mix(in srgb, var(--accent, #1a365d) 18%, transparent) 1.1px, transparent 1.6px),
                linear-gradient(140deg, #f8fbff 0%, color-mix(in srgb, var(--accent, #1a365d) 9%, #eaf2fc) 55%, #f6faff 100%);
            background-size: auto, auto, 22px 22px, cover;
            background-attachment: fixed;
            color: #172b4d;
        }
        body:has(.profile-wrapper)::before,
        body:has(.profile-wrapper)::after {
            content: "";
            position: fixed;
            z-index: 0;
            pointer-events: none;
            border-radius: 50%;
        }
        body:has(.profile-wrapper)::before {
            width: 360px; height: 360px;
            top: -190px; right: -120px;
            border: 1px solid color-mix(in srgb, var(--accent, #1a365d) 16%, transparent);
            box-shadow: 0 0 0 30px color-mix(in srgb, var(--accent, #1a365d) 3%, transparent), 0 0 0 62px color-mix(in srgb, var(--accent, #1a365d) 2%, transparent);
        }
        body:has(.profile-wrapper)::after {
            width: 280px; height: 280px;
            left: -160px; bottom: -160px;
            border: 1px solid color-mix(in srgb, var(--accent, #1a365d) 18%, transparent);
            box-shadow: 0 0 0 26px color-mix(in srgb, var(--accent, #1a365d) 4%, transparent), 0 0 0 54px color-mix(in srgb, var(--accent, #1a365d) 3%, transparent);
        }

        .profile-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1440px;
            min-height: 100vh;
            margin: 0 auto;
            padding: 30px clamp(18px, 3vw, 46px) 46px;
            box-sizing: border-box;
        }

        .profile-layout {
            display: grid;
            grid-template-columns: minmax(268px, 318px) minmax(0, 1fr);
            gap: 26px;
            align-items: stretch;
        }

        .profile-sidebar,
        .profile-content {
            border: 1px solid rgba(213,226,242,.9);
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 16px 42px rgba(30,64,115,.08);
            overflow: hidden;
        }
        .profile-sidebar { display: flex; flex-direction: column; }

        .profile-card {
            position: relative;
            padding: 32px 22px 26px;
            color: #fff;
            text-align: center;
            background: linear-gradient(135deg, var(--accent, #2563eb) 0%, color-mix(in srgb, var(--accent, #2563eb) 82%, #fff) 48%, color-mix(in srgb, var(--accent, #2563eb) 62%, #fff) 100%);
            border: 0;
        }
        .profile-card:before { content: ""; position: absolute; width: 190px; height: 190px; border: 1px solid rgba(255,255,255,.14); border-radius: 50%; right: -90px; top: -105px; pointer-events: none; }
        .profile-card:after { content: ""; position: absolute; width: 135px; height: 135px; border: 1px solid rgba(255,255,255,.12); border-radius: 50%; left: -82px; bottom: -75px; pointer-events: none; }
        .profile-card > * { position: relative; z-index: 1; }
        .avatar-wrap { width: 112px; height: 112px; margin: 0 auto 17px; border: 4px solid rgba(255,255,255,.95); box-shadow: 0 0 0 5px rgba(255,255,255,.16), 0 10px 25px rgba(12,42,100,.22); background: color-mix(in srgb, var(--accent, #2563eb) 78%, #000 22%); }
        .avatar-wrap .avatar-placeholder { color: rgba(255,255,255,.92); }
        .profile-name { color: #fff; font-size: 1.18rem; font-weight: 800; overflow-wrap: anywhere; margin: 0; }
        .profile-email { color: rgba(255,255,255,.8); font-size: .84rem; overflow-wrap: anywhere; margin: 4px 0 0; }
        .profile-role { display: inline-flex; align-items: center; margin-top: 10px; padding: 6px 12px; border-radius: 999px; background: #fff0f3; color: #be185d; font-size: .72rem; font-weight: 800; border: 1px solid rgba(255,255,255,.6); }
        .profile-last-access-line { display: flex; align-items: center; justify-content: center; gap: 6px; margin: 13px 0 0; padding-top: 12px; border-top: 1px solid rgba(255,255,255,.22); color: rgba(255,255,255,.82); font-size: .72rem; font-weight: 600; }
        .profile-last-access-line i { font-size: .72rem; }

        .profile-nav { display: flex; flex-direction: column; flex: 1 1 auto; padding: 16px 14px 18px; }
        .profile-nav-item { display: flex; align-items: center; gap: 12px; width: 100%; min-height: 46px; padding: 12px 15px; border-radius: 13px; color: #64748b; font-size: .9rem; font-weight: 600; text-align: left; border: 0; background: transparent; cursor: pointer; transition: background .16s ease, color .16s ease; }
        .profile-nav-item i { width: 20px; font-size: 1rem; text-align: center; }
        .profile-nav-item:hover { background: color-mix(in srgb, var(--accent, #1a365d) 7%, #fff); color: var(--accent, #1a365d); }
        .profile-nav-item.active { background: color-mix(in srgb, var(--accent, #1a365d) 13%, #fff); color: var(--accent, #1a365d); box-shadow: inset 3px 0 0 var(--accent, #1a365d); }
        .profile-nav-divider { height: 1px; background: #e9eff7; margin: 10px 10px; }
        .profile-back-action { margin-top: auto; justify-content: center; gap: 9px; color: var(--accent, #1a365d) !important; background: color-mix(in srgb, var(--accent, #1a365d) 7%, #fff); border: 1px solid color-mix(in srgb, var(--accent, #1a365d) 22%, #fff); font-weight: 750; text-decoration: none; }
        .profile-back-action:hover { background: color-mix(in srgb, var(--accent, #1a365d) 13%, #fff); color: var(--accent-dark, #1a365d) !important; border-color: color-mix(in srgb, var(--accent, #1a365d) 34%, #fff); }

        .profile-content { padding: 38px clamp(22px, 3vw, 42px); min-width: 0; min-height: 560px; }
        .profile-section-heading { display: flex; align-items: center; gap: 14px; margin-bottom: 28px; }
        .profile-section-icon { display: inline-flex; flex: 0 0 48px; width: 48px; height: 48px; align-items: center; justify-content: center; border-radius: 15px; background: linear-gradient(135deg, var(--accent, #1a365d), color-mix(in srgb, var(--accent, #1a365d) 58%, #fff)); color: #fff; font-size: 1.12rem; box-shadow: 0 7px 15px color-mix(in srgb, var(--accent, #1a365d) 28%, transparent); }
        .profile-section-heading .tab-title { margin: 0 0 4px; color: #172f54; font-size: 1.35rem; font-weight: 800; letter-spacing: -.02em; }
        .profile-section-heading .tab-subtitle { margin: 0; color: #8492a8; font-size: .88rem; }
        #formProfile .form-group { margin-bottom: 22px; }
        #formProfile .form-group label { display: flex; align-items: center; gap: 8px; margin-bottom: 9px; color: #4d607b; font-size: .79rem; font-weight: 750; text-transform: none; letter-spacing: .01em; }
        #formProfile .field-label-icon { width: 15px; color: var(--accent, #1a365d); font-size: .85rem; }
        #formProfile .form-input { min-height: 47px; padding: 12px 15px; border: 1px solid #e1e9f4; border-radius: 12px; background: #f6f9fd; color: #253a59; font-size: .91rem; box-shadow: inset 0 1px 2px rgba(25,55,95,.015); }
        #formProfile .form-input:focus { border-color: color-mix(in srgb, var(--accent, #1a365d) 45%, #fff); background: #fff; box-shadow: 0 0 0 4px color-mix(in srgb, var(--accent, #1a365d) 16%, transparent); }
        #formProfile .form-input:disabled { opacity: 1; color: #344865; -webkit-text-fill-color: #344865; cursor: default; }
        #formProfile .form-group:first-child { position: relative; }
        #formProfile .form-group:first-child:after { content: "\f078"; font-family: "Font Awesome 6 Free"; font-weight: 900; position: absolute; right: 16px; bottom: 16px; color: color-mix(in srgb, var(--accent, #1a365d) 48%, #94a3b8); font-size: .75rem; pointer-events: none; }
        #formProfile .form-group:first-child .form-input { padding-right: 42px; }
        #formProfile .flex.gap-2.mt-3 { display: flex; flex-direction: column; gap: 10px; margin-top: 28px; }
        #formProfile .profile-edit-button, #formProfile .profile-save-button, #formProfile #btnCancelarEditar { width: 100%; min-height: 49px; justify-content: center; border-radius: 12px; font-size: .92rem; font-weight: 750; }
        #formProfile .profile-edit-button { border: 0; color: #fff; background: linear-gradient(100deg, var(--accent, #1a365d) 0%, color-mix(in srgb, var(--accent, #1a365d) 78%, #fff) 55%, color-mix(in srgb, var(--accent, #1a365d) 90%, #fff) 100%); box-shadow: 0 8px 18px color-mix(in srgb, var(--accent, #1a365d) 26%, transparent); }
        #formProfile .profile-edit-button:hover { filter: brightness(1.04); transform: translateY(-1px); box-shadow: 0 11px 22px color-mix(in srgb, var(--accent, #1a365d) 32%, transparent); }
        #formProfile .profile-save-button { border: 0; background: linear-gradient(100deg,#059669,#10b981); color: #fff; }
        #formProfile #btnCancelarEditar { background: #f1f5f9; color: #475569; border: 1px solid #dbe4ef; }

        @media (max-width: 1024px) {
            .profile-layout { grid-template-columns: minmax(240px, 290px) minmax(0, 1fr); gap: 18px; }
            .profile-content { padding: 30px 26px; }
        }
        @media (max-width: 860px) {
            .profile-wrapper { padding: 22px 16px 34px; }
            .profile-layout { grid-template-columns: 1fr; align-items: start; }
            .profile-content { min-height: 0; }
            .profile-back-action { margin-top: 8px; }
        }
        @media (max-width: 700px) {
            .profile-card { padding: 26px 18px; }
            .profile-nav { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 6px; }
            .profile-nav-divider { display: none; }
            .profile-back-action { grid-column: 1 / -1; margin-top: 4px; }
            .profile-nav-item { padding: 11px 10px; font-size: .82rem; }
            .profile-section-heading { margin-bottom: 24px; }
            #formProfile .grid-2 { grid-template-columns: 1fr; }
        }
        @media (max-width: 420px) {
            .profile-section-icon { flex-basis: 42px; width: 42px; height: 42px; }
            .profile-nav { grid-template-columns: 1fr; }
        }
    </style>

<link rel="stylesheet" href="{{ asset('css/scrollbars.css') }}?v={{ substr(md5_file(public_path('css/scrollbars.css')), 0, 10) }}">
</head>
<body>
@include('partials.loading-overlay')
<script>document.getElementById('loading-overlay').classList.add('active');</script>
<div class="profile-wrapper">
    <div class="profile-layout">
        <!-- Sidebar -->
        <div class="profile-sidebar">
            <div class="profile-card">
                <div class="avatar-wrap" onclick="document.getElementById('avatarInput').click()" title="Cambiar foto de perfil">
                    @if($user->avatar)
                        <img id="profileAvatarImg" src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar">
                    @else
                        <i class="fas fa-user fa-3x avatar-placeholder" id="profileAvatarPlaceholder"></i>
                    @endif
                    <div class="avatar-overlay"><i class="fas fa-camera"></i></div>

                </div>

                <input type="file" id="avatarInput" hidden accept="image/*" onchange="onAvatarSelect(event)">
                <h2 class="profile-name">{{ $user->nombre }}</h2>
                <p class="profile-email">{{ $user->correo }}</p>
                <span class="profile-role {{ $user->rol === 'superadmin' ? 'role-superadmin' : ($user->rol === 'admin' ? 'role-admin' : 'role-analista') }}">
                    {{ $user->rol === 'admin' ? 'Administrador' : ($user->rol === 'superadmin' ? 'Superadmin' : 'Usuario') }}
                </span>
                <p class="profile-last-access-line"><i class="fas fa-clock-rotate-left"></i> Último acceso: {{ $user->ultimo_acceso ? $user->ultimo_acceso->diffForHumans() : 'Primer ingreso' }}</p>
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
                @if(in_array($user->rol, ['admin', 'superadmin']))
                <div class="profile-nav-divider"></div>
                <button class="profile-nav-item" data-tab="tab-admin">
                    <i class="fas fa-shield"></i> Administración
                </button>
                @endif
                <div class="profile-nav-divider"></div>
                @if(in_array($user->rol, ['admin', 'superadmin']))
                <a href="{{ route('documentacion') }}" class="profile-nav-item" style="text-decoration:none;cursor:pointer;">
                    <i class="fas fa-book"></i> Documentación
                </a>
                <div class="profile-nav-divider"></div>
                @endif
                <a href="{{ route('dashboard') }}" class="profile-nav-item profile-back-action">
                    <i class="fas fa-arrow-left"></i> Volver al inicio
                </a>
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
                        <input type="text" class="form-input" value="{{ $user->rol === 'admin' ? 'Administrador' : ($user->rol === 'superadmin' ? 'Superadmin' : 'Usuario') }}" disabled>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label><i class="fas fa-user field-label-icon"></i> Nombre</label>
                            <input type="text" class="form-input" name="nombre" value="{{ $user->nombre }}" disabled oninput="this.value=this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g,'')">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-user field-label-icon"></i> Apellido</label>
                            <input type="text" class="form-input" name="apellido" value="{{ $user->apellido }}" disabled oninput="this.value=this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g,'')">
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label><i class="fas fa-envelope field-label-icon"></i> Correo electrónico</label>
                            <input type="email" class="form-input" name="correo" value="{{ $user->correo }}" disabled>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-phone field-label-icon"></i> Teléfono</label>
                            <input type="text" class="form-input" name="telefono" value="{{ $user->telefono }}" disabled oninput="this.value=this.value.replace(/[^0-9+\-\s]/g,'')">
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar-days field-label-icon"></i> Fecha de nacimiento</label>
                        <input type="date" class="form-input" name="fecha_nacimiento" value="{{ $user->fecha_nacimiento ? \Carbon\Carbon::parse($user->fecha_nacimiento)->format('Y-m-d') : '' }}" disabled>
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
                    <div class="toggle-switch {{ $user->verificacion_dos_pasos ? 'active' : '' }}" id="toggle2FA" onclick="toggleFA()"></div>
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
                    <div class="theme-option {{ $user->tema === 'light' ? 'selected' : '' }}" data-theme="light" onclick="cambiarTema('light')">
                        <i class="fas fa-sun fa-fw"></i>
                        <span>Claro</span>
                    </div>
                    <div class="theme-option {{ $user->tema === 'dark' ? 'selected' : '' }}" data-theme="dark" onclick="cambiarTema('dark')">
                        <i class="fas fa-moon fa-fw"></i>
                        <span>Oscuro</span>
                    </div>
                </div>

                <h4 style="font-size:0.9rem;color:#0f172a;margin:0 0 12px;">Idioma</h4>
                <div style="max-width:200px;">
                    <select class="form-input" id="selectLanguage" onchange="cambiarIdioma(this.value)">
                        <option value="es" {{ $user->idioma === 'es' ? 'selected' : '' }}>Español</option>
                        <option value="en" {{ $user->idioma === 'en' ? 'selected' : '' }}>English</option>
                    </select>
                </div>

                <h4 style="font-size:0.9rem;color:#0f172a;margin:16px 0 12px;">Color principal</h4>
                <div class="color-presets" id="colorPresets">
                    @php $colors = ['#1a365d','#2563eb','#7c3aed','#db2777','#dc2626','#ea580c','#ca8a04','#16a34a','#0d9488']; @endphp
                    @foreach($colors as $c)
                    <div class="color-preset {{ $user->color_acento === $c ? 'selected' : '' }}" style="background:{{ $c }};" data-color="{{ $c }}" onclick="cambiarColor('{{ $c }}')"></div>
                    @endforeach
                </div>

                <hr style="border:0;border-top:1px solid #f1f5f9;margin:24px 0;">

                <h4 style="font-size:0.9rem;color:#0f172a;margin:0 0 12px;">Notificaciones</h4>
                <div class="toggle-wrap">
                    <div><div class="toggle-label">Notificaciones por correo</div><div class="toggle-desc">Recibe alertas importantes en tu email</div></div>
                    <select class="form-input" style="width:auto;padding:6px 10px;" id="notifEmail" onchange="guardarNotificaciones()">
                        <option value="all" {{ $user->notificacion_correo === 'all' ? 'selected' : '' }}>Todas</option>
                        <option value="important" {{ $user->notificacion_correo === 'important' ? 'selected' : '' }}>Solo importantes</option>
                        <option value="none" {{ $user->notificacion_correo === 'none' ? 'selected' : '' }}>Ninguna</option>
                    </select>
                </div>
                <div class="toggle-wrap">
                    <div><div class="toggle-label">Notificaciones en sistema</div><div class="toggle-desc">Alertas dentro de la plataforma</div></div>
                    <select class="form-input" style="width:auto;padding:6px 10px;" id="notifSystem" onchange="guardarNotificaciones()">
                        <option value="all" {{ $user->notificacion_sistema === 'all' ? 'selected' : '' }}>Todas</option>
                        <option value="important" {{ $user->notificacion_sistema === 'important' ? 'selected' : '' }}>Solo importantes</option>
                        <option value="none" {{ $user->notificacion_sistema === 'none' ? 'selected' : '' }}>Ninguna</option>
                    </select>
                </div>
                <div class="toggle-wrap">
                    <div><div class="toggle-label">Perfil público</div><div class="toggle-desc">Permitir que otros usuarios vean tu perfil</div></div>
                    <div class="toggle-switch {{ $user->perfil_publico ? 'active' : '' }}" id="togglePrivacy" onclick="togglePrivacidad()"></div>
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
            @if(in_array($user->rol, ['admin', 'superadmin']))
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
            @endif
        </div>
    </div>
</div>

<!-- Cropper Modal -->
<div class="modal-overlay" id="cropModal">
    <div class="modal-box" style="display:flex;flex-direction:column;max-height:90vh;max-width:520px;padding:24px;">
        <div style="flex-shrink:0;">
            <h3 style="margin:0 0 6px;color:var(--text-primary);"><i class="fas fa-crop-simple"></i> Ajustar foto de perfil</h3>
            <p style="font-size:0.85rem;color:var(--text-muted);margin:0 0 16px;">Arrastra la foto para centrarla dentro del círculo y usa la rueda del mouse para acercar o alejar</p>
        </div>
        <div id="cropContainer" style="height:340px;max-height:340px;overflow:hidden;background:#0f172a;border-radius:14px;margin-bottom:16px;"></div>
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
window.SIGEJUB_USER_ID={{ $user->id }};window.SIGEJUB_USER_ROL='{{ $user->rol }}';
window.SIGEJUB_STORAGE_URL='{{ asset("storage") }}';
window.SIGEJUB_PROFILE_THEME='{{ $user->tema }}';
window.SIGEJUB_ACCENT_COLOR='{{ $user->color_acento ?? "#1a365d" }}';
</script>
{{-- Gestor central de ventanas modales: solo cierran con su botón (clic fuera y ESC desactivados) --}}
<script defer src="{{ asset('js/dashboard/modales.js') }}?v={{ substr(md5_file(public_path('js/dashboard/modales.js')), 0, 10) }}"></script>
<script defer src="{{ asset('js/profile.js') }}?v={{ substr(md5_file(public_path('js/profile.js')), 0, 10) }}"></script>

{{-- Pantalla de carga: se oculta al terminar de cargar y se muestra al salir hacia otra página. --}}
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
