<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil — SIGEJUB</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/components.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <style>
        :root { --accent: {{ $user->accent_color ?? '#1a365d' }}; }
        body { margin: 0; font-family: 'Inter', system-ui, -apple-system, sans-serif; background: #f1f5f9; min-height: 100vh; }
        .profile-wrapper { max-width: 1200px; margin: 0 auto; padding: 24px; }
        .profile-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .profile-header h1 { font-size: 1.5rem; color: #0f172a; font-weight: 700; margin: 0; }
        .btn-back { width: 40px; height: 40px; border-radius: 10px; border: 1px solid #e2e8f0; background: white; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #64748b; transition: all 0.2s; text-decoration: none; }
        .btn-back:hover { background: #f8fafc; color: #0f172a; border-color: #cbd5e1; }
        .profile-layout { display: grid; grid-template-columns: 300px 1fr; gap: 24px; align-items: start; }

        /* Sidebar */
        .profile-sidebar { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); overflow: hidden; }
        .profile-card { padding: 32px 24px; text-align: center; border-bottom: 1px solid #f1f5f9; }
        .avatar-wrap { width: 120px; height: 120px; border-radius: 50%; margin: 0 auto 16px; overflow: hidden; border: 4px solid #f1f5f9; position: relative; cursor: pointer; background: #f8fafc; display: flex; align-items: center; justify-content: center; }
        .avatar-wrap img { width: 100%; height: 100%; object-fit: cover; }
        .avatar-wrap .avatar-placeholder { color: #cbd5e1; }
        .avatar-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s; border-radius: 50%; color: white; font-size: 0.75rem; font-weight: 600; }
        .avatar-wrap:hover .avatar-overlay { opacity: 1; }
        .profile-name { font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 0; }
        .profile-email { font-size: 0.85rem; color: #64748b; margin: 4px 0 0; }
        .profile-role { display: inline-block; margin-top: 10px; padding: 4px 14px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .role-admin { background: #eef2ff; color: #4338ca; }
        .role-analista { background: #fef3c7; color: #b45309; }

        .profile-nav { padding: 12px; }
        .profile-nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px; color: #475569; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: all 0.15s; border: none; background: none; width: 100%; text-align: left; }
        .profile-nav-item:hover { background: #f8fafc; color: #0f172a; }
        .profile-nav-item.active { background: #f1f5f9; color: var(--accent); font-weight: 700; }
        .profile-nav-item i { width: 20px; text-align: center; flex-shrink: 0; }
        .profile-nav-divider { height: 1px; background: #f1f5f9; margin: 8px 12px; }

        /* Content */
        .profile-content { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); padding: 32px; min-height: 500px; }
        .profile-tab { display: none; animation: fadeIn 0.25s ease; }
        .profile-tab.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
        .tab-title { font-size: 1.2rem; font-weight: 700; color: #0f172a; margin: 0 0 4px; }
        .tab-subtitle { font-size: 0.85rem; color: #64748b; margin: 0 0 24px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.3px; }
        .form-input { width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 0.9rem; background: #f8fafc; transition: border-color 0.2s; box-sizing: border-box; }
        .form-input:focus { outline: none; border-color: var(--accent); background: white; box-shadow: 0 0 0 3px rgba(26,54,93,0.08); }
        .form-control { display: flex; flex-direction: column; gap: 4px; }
        .btn { padding: 10px 22px; border-radius: 10px; font-size: 0.85rem; font-weight: 600; cursor: pointer; border: none; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: var(--accent); color: white; }
        .btn-primary:hover { filter: brightness(1.1); }
        .btn-secondary { background: #f1f5f9; color: #475569; }
        .btn-secondary:hover { background: #e2e8f0; }
        .btn-danger { background: #fef2f2; color: #dc2626; }
        .btn-danger:hover { background: #fee2e2; }
        .btn-sm { padding: 6px 14px; font-size: 0.78rem; }
        .btn-outline-danger { border: 1px solid #fecaca; color: #dc2626; background: white; }
        .btn-outline-danger:hover { background: #fef2f2; }
        .flex { display: flex; }
        .flex-center { display: flex; align-items: center; }
        .flex-between { display: flex; justify-content: space-between; align-items: center; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 12px; }
        .gap-4 { gap: 16px; }
        .mt-3 { margin-top: 12px; }
        .mt-4 { margin-top: 16px; }
        .mt-6 { margin-top: 24px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 16px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .text-center { text-align: center; }
        .text-muted { color: #64748b; font-size: 0.85rem; }

        /* Theme/Color picker */
        .color-presets { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 8px; }
        .color-preset { width: 36px; height: 36px; border-radius: 50%; cursor: pointer; border: 3px solid transparent; transition: all 0.2s; }
        .color-preset:hover { transform: scale(1.1); }
        .color-preset.selected { border-color: #0f172a; box-shadow: 0 0 0 2px white, 0 0 0 4px #0f172a; }
        .theme-toggle { display: flex; gap: 8px; }
        .theme-option { flex: 1; padding: 14px; border-radius: 12px; border: 2px solid #e2e8f0; cursor: pointer; text-align: center; transition: all 0.2s; background: white; }
        .theme-option:hover { border-color: #cbd5e1; }
        .theme-option.selected { border-color: var(--accent); background: #f8fafc; }
        .theme-option i { font-size: 1.4rem; display: block; margin-bottom: 6px; }
        .theme-option span { font-size: 0.8rem; font-weight: 600; color: #475569; }

        /* Sessions */
        .session-item { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border-radius: 10px; background: #f8fafc; margin-bottom: 8px; }
        .session-item.current { background: #f0fdf4; border: 1px solid #bbf7d0; }
        .session-device { display: flex; align-items: center; gap: 12px; }
        .session-device i { color: #64748b; }
        .session-info p { margin: 0; font-size: 0.85rem; font-weight: 600; color: #0f172a; }
        .session-info span { font-size: 0.75rem; color: #94a3b8; }
        .session-badge { font-size: 0.7rem; padding: 2px 10px; border-radius: 12px; font-weight: 600; }
        .session-badge.current { background: #bbf7d0; color: #166534; }

        /* Activity */
        .activity-item { display: flex; gap: 14px; padding: 12px 0; border-bottom: 1px solid #f8fafc; }
        .activity-item:last-child { border-bottom: none; }
        .activity-icon { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.85rem; }
        .activity-icon.green { background: #f0fdf4; color: #16a34a; }
        .activity-icon.blue { background: #eff6ff; color: #2563eb; }
        .activity-icon.orange { background: #fff7ed; color: #ea580c; }
        .activity-icon.red { background: #fef2f2; color: #dc2626; }
        .activity-icon.purple { background: #f5f3ff; color: #7c3aed; }
        .activity-text { flex: 1; }
        .activity-text p { margin: 0; font-size: 0.85rem; color: #334155; }
        .activity-text span { font-size: 0.73rem; color: #94a3b8; }

        /* Stats cards */
        .stat-card-mini { background: #f8fafc; border-radius: 12px; padding: 16px; text-align: center; }
        .stat-card-mini h4 { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; }
        .stat-card-mini p { font-size: 0.72rem; color: #64748b; margin: 4px 0 0; text-transform: uppercase; font-weight: 600; letter-spacing: 0.3px; }

        /* Cropper modal */
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 9999; display: none; align-items: center; justify-content: center; }
        .modal-overlay.show { display: flex; }
        .modal-box { background: white; border-radius: 16px; padding: 24px; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 60px rgba(0,0,0,0.2); }
        .modal-box h3 { margin: 0 0 16px; font-size: 1.1rem; color: #0f172a; }
        .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 16px; }
        #cropContainer { max-width: 100%; max-height: 400px; }
        #cropContainer img { max-width: 100%; }

        /* Form toggle */
        .toggle-wrap { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; }
        .toggle-label { font-size: 0.9rem; font-weight: 500; color: #0f172a; }
        .toggle-desc { font-size: 0.78rem; color: #64748b; }
        .toggle-switch { width: 44px; height: 24px; background: #cbd5e1; border-radius: 12px; cursor: pointer; position: relative; transition: background 0.2s; flex-shrink: 0; }
        .toggle-switch.active { background: var(--accent); }
        .toggle-switch::after { content: ''; position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; border-radius: 50%; background: white; transition: transform 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
        .toggle-switch.active::after { transform: translateX(20px); }

        /* Users table (admin) */
        .admin-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        .admin-table th { text-align: left; padding: 12px 10px; color: #64748b; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #f1f5f9; }
        .admin-table td { padding: 10px; border-bottom: 1px solid #f8fafc; color: #334155; }
        .admin-table tr:hover td { background: #f8fafc; }
        .search-bar { display: flex; gap: 10px; margin-bottom: 16px; }
        .search-bar input { flex: 1; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 0.85rem; }

        /* Dark mode */ 
        body.dark-mode { background: #0f172a; }
        body.dark-mode .profile-sidebar, body.dark-mode .profile-content { background: #1e293b; border-color: #334155; }
        body.dark-mode .profile-header h1 { color: #f1f5f9; }
        body.dark-mode .profile-name { color: #f1f5f9; }
        body.dark-mode .form-input { background: #334155; border-color: #475569; color: #e2e8f0; }
        body.dark-mode .form-input:focus { background: #334155; }
        body.dark-mode .stat-card-mini { background: #334155; }
        body.dark-mode .session-item { background: #334155; }
        body.dark-mode .admin-table td { color: #cbd5e1; }
        body.dark-mode .btn-back { background: #1e293b; border-color: #334155; color: #94a3b8; }
        body.dark-mode .tab-title { color: #f1f5f9; }
        body.dark-mode .profile-nav-item { color: #94a3b8; }
        body.dark-mode .profile-nav-item:hover { background: #334155; color: #e2e8f0; }
        body.dark-mode .profile-nav-item.active { background: #334155; color: #60a5fa; }
        body.dark-mode .profile-nav-divider { background: #334155; }
    </style>
</head>
<body>
<div class="profile-wrapper">
    <div class="profile-header">
        <div class="flex-center gap-3">
            <a href="{{ route('dashboard') }}" class="btn-back" title="Volver al dashboard">
                <i data-lucide="arrow-left" size="20"></i>
            </a>
            <h1>Mi Perfil</h1>
        </div>
        <span class="text-muted">Último acceso: {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Primer ingreso' }}</span>
    </div>

    <div class="profile-layout">
        <!-- Sidebar -->
        <div class="profile-sidebar">
            <div class="profile-card">
                <div class="avatar-wrap" onclick="document.getElementById('avatarInput').click()">
                    @if($user->avatar)
                        <img id="profileAvatarImg" src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar">
                    @else
                        <i data-lucide="user" size="48" class="avatar-placeholder" id="profileAvatarPlaceholder"></i>
                    @endif
                    <div class="avatar-overlay"><i data-lucide="camera" size="18"></i></div>
                </div>
                <input type="file" id="avatarInput" hidden accept="image/*" onchange="onAvatarSelect(event)">
                <h2 class="profile-name">{{ $user->name }}</h2>
                <p class="profile-email">{{ $user->email }}</p>
                <span class="profile-role {{ $user->role === 'admin' ? 'role-admin' : 'role-analista' }}">
                    {{ $user->role === 'admin' ? 'Administrador' : 'Analista' }}
                </span>
            </div>
            <div class="profile-nav">
                <button class="profile-nav-item active" data-tab="tab-perfil">
                    <i data-lucide="user" size="18"></i> Mi Perfil
                </button>
                <button class="profile-nav-item" data-tab="tab-seguridad">
                    <i data-lucide="shield" size="18"></i> Seguridad
                </button>
                <button class="profile-nav-item" data-tab="tab-configuracion">
                    <i data-lucide="settings" size="18"></i> Configuración
                </button>
                <button class="profile-nav-item" data-tab="tab-actividad">
                    <i data-lucide="activity" size="18"></i> Actividad
                </button>
                @if($user->role === 'admin')
                <div class="profile-nav-divider"></div>
                <button class="profile-nav-item" data-tab="tab-admin">
                    <i data-lucide="shield" size="18"></i> Administración
                </button>
                @endif
                <div class="profile-nav-divider"></div>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="profile-nav-item" style="color:#dc2626;">
                        <i data-lucide="log-out" size="18"></i> Cerrar sesión
                    </button>
                </form>
            </div>
        </div>

        <!-- Content -->
        <div class="profile-content">
            <!-- TAB: MI PERFIL -->
            <div class="profile-tab active" id="tab-perfil">
                <h2 class="tab-title">Mi Perfil</h2>
                <p class="tab-subtitle">Información básica de tu cuenta</p>
                <form id="formProfile">
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Nombre completo</label>
                            <input type="text" class="form-input" name="name" value="{{ $user->name }}" required>
                        </div>
                        <div class="form-group">
                            <label>Correo electrónico</label>
                            <input type="email" class="form-input" name="email" value="{{ $user->email }}" required>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                        @if($user->avatar)
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarAvatar()">Eliminar foto</button>
                        @endif
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
                    <div class="toggle-switch {{ $user->two_factor_enabled ? 'active' : '' }}" id="toggle2FA" onclick="toggleFA()"></div>
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
                    <div class="theme-option {{ $user->theme === 'light' ? 'selected' : '' }}" data-theme="light" onclick="cambiarTema('light')">
                        <i data-lucide="sun" size="22"></i>
                        <span>Claro</span>
                    </div>
                    <div class="theme-option {{ $user->theme === 'dark' ? 'selected' : '' }}" data-theme="dark" onclick="cambiarTema('dark')">
                        <i data-lucide="moon" size="22"></i>
                        <span>Oscuro</span>
                    </div>
                </div>

                <h4 style="font-size:0.9rem;color:#0f172a;margin:0 0 12px;">Idioma</h4>
                <div style="max-width:200px;">
                    <select class="form-input" id="selectLanguage" onchange="cambiarIdioma(this.value)">
                        <option value="es" {{ $user->language === 'es' ? 'selected' : '' }}>Español</option>
                        <option value="en" {{ $user->language === 'en' ? 'selected' : '' }}>English</option>
                    </select>
                </div>

                <h4 style="font-size:0.9rem;color:#0f172a;margin:16px 0 12px;">Color principal</h4>
                <div class="color-presets" id="colorPresets">
                    @php $colors = ['#1a365d','#2563eb','#7c3aed','#db2777','#dc2626','#ea580c','#ca8a04','#16a34a','#0d9488']; @endphp
                    @foreach($colors as $c)
                    <div class="color-preset {{ $user->accent_color === $c ? 'selected' : '' }}" style="background:{{ $c }};" data-color="{{ $c }}" onclick="cambiarColor('{{ $c }}')"></div>
                    @endforeach
                </div>

                <hr style="border:0;border-top:1px solid #f1f5f9;margin:24px 0;">

                <h4 style="font-size:0.9rem;color:#0f172a;margin:0 0 12px;">Notificaciones</h4>
                <div class="toggle-wrap">
                    <div><div class="toggle-label">Notificaciones por correo</div><div class="toggle-desc">Recibe alertas importantes en tu email</div></div>
                    <select class="form-input" style="width:auto;padding:6px 10px;" id="notifEmail" onchange="guardarNotificaciones()">
                        <option value="all" {{ $user->notification_email === 'all' ? 'selected' : '' }}>Todas</option>
                        <option value="important" {{ $user->notification_email === 'important' ? 'selected' : '' }}>Solo importantes</option>
                        <option value="none" {{ $user->notification_email === 'none' ? 'selected' : '' }}>Ninguna</option>
                    </select>
                </div>
                <div class="toggle-wrap">
                    <div><div class="toggle-label">Notificaciones en sistema</div><div class="toggle-desc">Alertas dentro de la plataforma</div></div>
                    <select class="form-input" style="width:auto;padding:6px 10px;" id="notifSystem" onchange="guardarNotificaciones()">
                        <option value="all" {{ $user->notification_system === 'all' ? 'selected' : '' }}>Todas</option>
                        <option value="important" {{ $user->notification_system === 'important' ? 'selected' : '' }}>Solo importantes</option>
                        <option value="none" {{ $user->notification_system === 'none' ? 'selected' : '' }}>Ninguna</option>
                    </select>
                </div>
                <div class="toggle-wrap">
                    <div><div class="toggle-label">Perfil público</div><div class="toggle-desc">Permitir que otros usuarios vean tu perfil</div></div>
                    <div class="toggle-switch {{ $user->profile_public ? 'active' : '' }}" id="togglePrivacy" onclick="togglePrivacidad()"></div>
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
            </div>

            <!-- TAB: ADMINISTRACIÓN (solo admin) -->
            @if($user->role === 'admin')
            <div class="profile-tab" id="tab-admin">
                <h2 class="tab-title">Administración</h2>
                <p class="tab-subtitle">Gestión de usuarios y configuración global del sistema</p>

                <div style="display:flex;gap:12px;margin-bottom:16px;">
                    <button class="btn btn-secondary btn-sm active admin-tab-btn" data-admin-tab="admin-usuarios" onclick="cambiarAdminTab('admin-usuarios')">
                        <i data-lucide="users" size="16"></i> Usuarios
                    </button>
                    <button class="btn btn-secondary btn-sm admin-tab-btn" data-admin-tab="admin-actividad" onclick="cambiarAdminTab('admin-actividad')">
                        <i data-lucide="activity" size="16"></i> Actividad global
                    </button>
                    <button class="btn btn-secondary btn-sm admin-tab-btn" data-admin-tab="admin-config" onclick="cambiarAdminTab('admin-config')">
                        <i data-lucide="globe" size="16"></i> Config. global
                    </button>
                </div>

                <!-- Sub-tab: Usuarios -->
                <div class="admin-subtab" id="admin-usuarios">
                    <div class="search-bar">
                        <input type="text" id="adminSearch" placeholder="Buscar usuario..." oninput="cargarAdminUsuarios()">
                        <select id="adminRoleFilter" class="form-input" style="width:auto;" onchange="cargarAdminUsuarios()">
                            <option value="">Todos los roles</option>
                            <option value="admin">Admin</option>
                            <option value="analista">Analista</option>
                        </select>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="admin-table">
                            <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Registro</th><th>Acción</th></tr></thead>
                            <tbody id="adminUsersBody"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Sub-tab: Actividad global -->
                <div class="admin-subtab" id="admin-actividad" style="display:none;">
                    <div id="adminActivityList">
                        <p class="text-muted text-center" style="padding:20px;">Cargando actividad global...</p>
                    </div>
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
    <div class="modal-box">
        <h3>Ajustar foto de perfil</h3>
        <p style="font-size:0.85rem;color:#64748b;margin:0 0 16px;">Arrastra para ajustar la imagen al área deseada</p>
        <div id="cropContainer"></div>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="cerrarCrop()">Cancelar</button>
            <button class="btn btn-primary" onclick="confirmarCrop()">Guardar foto</button>
        </div>
    </div>
</div>

<script>
lucide.createIcons();

// === TAB NAVIGATION ===
document.querySelectorAll('.profile-nav-item[data-tab]').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.profile-nav-item').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
        if (btn.dataset.tab === 'tab-actividad') cargarActividad();
        if (btn.dataset.tab === 'tab-seguridad') cargarSesiones();
        if (btn.dataset.tab === 'tab-admin') { cargarAdminUsuarios(); }
    });
});

// === CRONOMETRO ACTIVIDAD ===
let _inicioCargaStats = false;

// === TOAST (quick) ===
function mostrarToast(msg, tipo) {
    const exists = document.querySelector('.toast-float');
    if (exists) exists.remove();
    const colors = { success: '#16a34a', error: '#dc2626', info: '#2563eb' };
    const div = document.createElement('div');
    div.className = 'toast-float';
    div.style.cssText = 'position:fixed;bottom:24px;right:24px;padding:14px 22px;border-radius:12px;color:white;font-weight:600;font-size:0.85rem;box-shadow:0 8px 30px rgba(0,0,0,0.15);z-index:99999;animation:slideUp 0.3s ease;max-width:400px;';
    div.style.background = colors[tipo] || colors.info;
    div.textContent = msg;
    document.body.appendChild(div);
    setTimeout(() => { div.style.opacity = '0'; div.style.transition = 'opacity 0.3s'; setTimeout(() => div.remove(), 300); }, 3000);
}
const styleToast = document.createElement('style');
styleToast.textContent = '@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }';
document.head.appendChild(styleToast);

// === CSRF ===
function csrfToken() { return document.querySelector('meta[name="csrf-token"]').content; }

function api(url, opts = {}) {
    return fetch(url, {
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json', ...(opts.headers || {}) },
        ...opts,
    });
}

// === TAB: PERFIL ===
document.getElementById('formProfile').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('_method', 'PUT');
    try {
        const r = await api('/perfil/actualizar', { method: 'POST', body: fd });
        const d = await r.json();
        if (!r.ok) { mostrarToast(d.message || 'Error al guardar', 'error'); return; }
        mostrarToast(d.message, 'success');
        document.querySelector('.profile-name').textContent = fd.get('name');
    } catch (err) { mostrarToast('Error de conexión', 'error'); }
});

// === AVATAR CROPPER ===
let cropper = null;
let avatarFile = null;

function onAvatarSelect(e) {
    const file = e.target.files[0];
    if (!file) return;
    avatarFile = file;
    const reader = new FileReader();
    reader.onload = (ev) => {
        const container = document.getElementById('cropContainer');
        container.innerHTML = '<img id="cropImage" src="' + ev.target.result + '">';
        document.getElementById('cropModal').classList.add('show');
        if (cropper) cropper.destroy();
        setTimeout(() => {
            cropper = new Cropper(document.getElementById('cropImage'), {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                cropBoxResizable: true,
                cropBoxMovable: true,
            });
        }, 200);
    };
    reader.readAsDataURL(file);
    e.target.value = '';
}

function cerrarCrop() {
    document.getElementById('cropModal').classList.remove('show');
    if (cropper) { cropper.destroy(); cropper = null; }
}

async function confirmarCrop() {
    if (!cropper) return;
    const canvas = cropper.getCroppedCanvas({ width: 400, height: 400 });
    canvas.toBlob(async (blob) => {
        const fd = new FormData();
        fd.append('avatar', blob, 'avatar.jpg');
        try {
            const r = await api('/perfil/avatar', { method: 'POST', body: fd });
            const d = await r.json();
            if (!r.ok) { mostrarToast(d.message || 'Error al subir', 'error'); return; }
            document.getElementById('profileAvatarImg').src = d.avatar + '?t=' + Date.now();
            mostrarToast(d.message, 'success');
            cerrarCrop();
        } catch (err) { mostrarToast('Error de conexión', 'error'); }
    }, 'image/jpeg', 0.9);
}

async function eliminarAvatar() {
    if (!confirm('¿Eliminar foto de perfil?')) return;
    try {
        const r = await api('/perfil/avatar', { method: 'DELETE' });
        const d = await r.json();
        if (!r.ok) { mostrarToast(d.message, 'error'); return; }
        const img = document.getElementById('profileAvatarImg');
        img.style.display = 'none';
        img.src = '';
        location.reload();
    } catch (err) { mostrarToast('Error', 'error'); }
}

// === TAB: SEGURIDAD ===
document.getElementById('formPassword').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('_method', 'PUT');
    try {
        const r = await api('/perfil/password', { method: 'POST', body: fd });
        const d = await r.json();
        if (!r.ok) { mostrarToast(d.message || 'Error', 'error'); return; }
        mostrarToast(d.message, 'success');
        e.target.reset();
    } catch (err) { mostrarToast('Error de conexión', 'error'); }
});

// 2FA
async function toggleFA() {
    const tog = document.getElementById('toggle2FA');
    const nuevo = !tog.classList.contains('active');
    try {
        const r = await api('/perfil/2fa', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ enabled: nuevo }),
        });
        const d = await r.json();
        if (!r.ok) { mostrarToast(d.message, 'error'); return; }
        tog.classList.toggle('active', d.enabled);
        mostrarToast(d.message, 'success');
    } catch (err) { mostrarToast('Error', 'error'); }
}

// Sessions
async function cargarSesiones() {
    const el = document.getElementById('sessionList');
    try {
        const r = await api('/perfil/sesiones');
        const sessions = await r.json();
        if (!sessions.length) { el.innerHTML = '<p class="text-muted text-center" style="padding:20px;">Sin sesiones activas</p>'; return; }
        el.innerHTML = sessions.map(s => `
            <div class="session-item ${s.is_current ? 'current' : ''}">
                <div class="session-device">
                    <i data-lucide="${s.is_current ? 'smartphone' : 'laptop'}" size="20"></i>
                    <div class="session-info">
                        <p>${s.user_agent ? s.user_agent.substring(0, 60) + '...' : 'Dispositivo desconocido'} ${s.is_current ? '<span class="session-badge current">Actual</span>' : ''}</p>
                        <span>${s.ip_address} — ${s.last_activity_humans || 'desconocido'}</span>
                    </div>
                </div>
                ${!s.is_current ? `<button class="btn btn-danger btn-sm" onclick="eliminarSesion('${s.id}')">Cerrar</button>` : ''}
            </div>
        `).join('');
        lucide.createIcons();
    } catch (err) { el.innerHTML = '<p class="text-muted text-center" style="padding:20px;">Error al cargar sesiones</p>'; }
}

async function eliminarSesion(id) {
    try {
        const r = await api('/perfil/sesiones/' + id, { method: 'DELETE' });
        const d = await r.json();
        if (!r.ok) { mostrarToast(d.message, 'error'); return; }
        mostrarToast(d.message, 'success');
        cargarSesiones();
    } catch (err) { mostrarToast('Error', 'error'); }
}

async function cerrarOtrasSesiones() {
    if (!confirm('¿Cerrar sesión en todos los otros dispositivos?')) return;
    try {
        const r = await api('/perfil/sesiones/cerrar-otras', { method: 'POST' });
        const d = await r.json();
        if (!r.ok) { mostrarToast(d.message, 'error'); return; }
        mostrarToast(d.message, 'success');
        cargarSesiones();
    } catch (err) { mostrarToast('Error', 'error'); }
}

// === TAB: CONFIGURACIÓN ===
async function cambiarTema(tema) {
    document.querySelectorAll('.theme-option').forEach(el => el.classList.remove('selected'));
    document.querySelector(`.theme-option[data-theme="${tema}"]`).classList.add('selected');
    document.body.classList.toggle('dark-mode', tema === 'dark');
    try {
        await api('/perfil/configuracion', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ theme: tema }),
        });
    } catch (err) {}
}

async function cambiarIdioma(lang) {
    try {
        await api('/perfil/configuracion', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ language: lang }),
        });
        mostrarToast('Idioma actualizado', 'success');
    } catch (err) {}
}

async function cambiarColor(color) {
    document.querySelectorAll('.color-preset').forEach(el => el.classList.remove('selected'));
    document.querySelector(`.color-preset[data-color="${color}"]`).classList.add('selected');
    document.documentElement.style.setProperty('--accent', color);
    try {
        await api('/perfil/configuracion', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ accent_color: color }),
        });
        mostrarToast('Color actualizado', 'success');
    } catch (err) {}
}

async function guardarNotificaciones() {
    try {
        await api('/perfil/notificaciones', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({
                notification_email: document.getElementById('notifEmail').value,
                notification_system: document.getElementById('notifSystem').value,
            }),
        });
    } catch (err) {}
}

async function togglePrivacidad() {
    const tog = document.getElementById('togglePrivacy');
    const nuevo = !tog.classList.contains('active');
    tog.classList.toggle('active', nuevo);
    try {
        await api('/perfil/notificaciones', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ profile_public: nuevo }),
        });
        mostrarToast(nuevo ? 'Perfil público' : 'Perfil privado', 'success');
    } catch (err) { tog.classList.toggle('active', !nuevo); }
}

// === TAB: ACTIVIDAD ===
async function cargarActividad() {
    const statsGrid = document.getElementById('statsGrid');
    const list = document.getElementById('activityList');

    try {
        const [rStats, rAct] = await Promise.all([
            api('/perfil/estadisticas'),
            api('/perfil/actividad'),
        ]);
        const stats = await rStats.json();
        const acts = await rAct.json();

        document.getElementById('statTrabajadores').textContent = stats.total_trabajadores_capturados || 0;
        document.getElementById('statSolicitudes').textContent = stats.total_solicitudes_gestionadas || 0;
        document.getElementById('statExpedientes').textContent = stats.total_expedientes_movidos || 0;
        document.getElementById('statMiembro').textContent = stats.miembro_desde || '—';
        document.getElementById('statUltimoAcceso').textContent = stats.ultimo_acceso || '—';
        document.getElementById('statUltimaIP').textContent = stats.ultima_ip || '—';

        if (!acts.length) {
            list.innerHTML = '<p class="text-muted text-center" style="padding:20px;">No hay actividad registrada</p>';
            return;
        }
        list.innerHTML = acts.map(a => {
            const icons = { created: { trabajador: ['users','green'], solicitud: ['file-text','blue'], usuario: ['user-plus','purple'], documento: ['file','blue'], notificacion: ['bell','purple'] }, updated: { trabajador: ['edit-3','orange'], solicitud: ['edit','orange'], usuario: ['edit','orange'] }, deleted: { trabajador: ['trash-2','red'], solicitud: ['x-circle','red'] } };
            const fallback = ['circle','blue'];
            const [icon, color] = (icons[a.action] && icons[a.action][a.subject_type]) || fallback;
            const fecha = new Date(a.created_at).toLocaleDateString('es-ES', { day:'numeric', month:'short', hour:'2-digit', minute:'2-digit' });
            return `<div class="activity-item"><div class="activity-icon ${color}"><i data-lucide="${icon}" size="16"></i></div><div class="activity-text"><p>${a.description || a.action + ' ' + a.subject_type}</p><span>${fecha}</span></div></div>`;
        }).join('');
        lucide.createIcons();
    } catch (err) {
        list.innerHTML = '<p class="text-muted text-center" style="padding:20px;">Error al cargar actividad</p>';
    }
}

// === TAB: ADMIN ===
let adminTabActual = 'admin-usuarios';

function cambiarAdminTab(tab) {
    adminTabActual = tab;
    document.querySelectorAll('.admin-subtab').forEach(t => t.style.display = 'none');
    document.querySelectorAll('.admin-tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(tab).style.display = 'block';
    document.querySelector(`.admin-tab-btn[data-admin-tab="${tab}"]`).classList.add('active');
    if (tab === 'admin-usuarios') cargarAdminUsuarios();
    if (tab === 'admin-actividad') cargarAdminActividad();
}

async function cargarAdminUsuarios() {
    const search = document.getElementById('adminSearch').value;
    const role = document.getElementById('adminRoleFilter').value;
    const tbody = document.getElementById('adminUsersBody');
    try {
        const r = await api(`/perfil/admin/usuarios?search=${encodeURIComponent(search)}&role=${role}`);
        const data = await r.json();
        if (!data.data || !data.data.length) { tbody.innerHTML = '<tr><td colspan="5" class="text-muted text-center" style="padding:20px;">Sin usuarios</td></tr>'; return; }
        tbody.innerHTML = data.data.map(u => `
            <tr>
                <td><div style="display:flex;align-items:center;gap:8px;"><div style="width:28px;height:28px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:700;color:#1e3a8a;overflow:hidden;">${u.avatar ? '<img src="'+'{{ asset("storage/") }}/'+u.avatar+'" style="width:100%;height:100%;object-fit:cover;">' : u.name.charAt(0).toUpperCase()}</div>${u.name}</div></td>
                <td>${u.email}</td>
                <td><select class="form-input" style="width:auto;padding:4px 8px;font-size:0.75rem;" onchange="cambiarRolUsuario(${u.id}, this.value)"><option value="analista" ${u.role==='analista'?'selected':''}>Analista</option><option value="admin" ${u.role==='admin'?'selected':''}>Admin</option></select></td>
                <td style="font-size:0.75rem;color:#94a3b8;">${new Date(u.created_at).toLocaleDateString('es-ES')}</td>
                <td><button class="btn btn-danger btn-sm" onclick="eliminarUsuario(${u.id})" ${u.id === {{ $user->id }} ? 'disabled style="opacity:0.4;"' : ''}>Eliminar</button></td>
            </tr>
        `).join('');
    } catch (err) { tbody.innerHTML = '<tr><td colspan="5" class="text-muted text-center" style="padding:20px;">Error al cargar</td></tr>'; }
}

async function cambiarRolUsuario(id, role) {
    try {
        const r = await api('/perfil/admin/usuarios/' + id, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ role }),
        });
        const d = await r.json();
        if (!r.ok) { mostrarToast(d.message, 'error'); return; }
        mostrarToast(d.message, 'success');
    } catch (err) { mostrarToast('Error', 'error'); }
}

async function eliminarUsuario(id) {
    if (!confirm('¿Eliminar este usuario permanentemente?')) return;
    try {
        const r = await api('/perfil/admin/usuarios/' + id, { method: 'DELETE' });
        const d = await r.json();
        if (!r.ok) { mostrarToast(d.message, 'error'); return; }
        mostrarToast(d.message, 'success');
        cargarAdminUsuarios();
    } catch (err) { mostrarToast('Error', 'error'); }
}

async function cargarAdminActividad() {
    const el = document.getElementById('adminActivityList');
    try {
        const r = await api('/perfil/admin/actividad');
        const acts = await r.json();
        if (!acts.length) { el.innerHTML = '<p class="text-muted text-center" style="padding:20px;">Sin actividad global</p>'; return; }
        el.innerHTML = acts.map(a => {
            const fecha = new Date(a.created_at).toLocaleDateString('es-ES', { day:'numeric', month:'short', hour:'2-digit', minute:'2-digit' });
            const usuario = a.user ? a.user.name : 'Sistema';
            return `<div class="activity-item"><div class="activity-text"><p>${a.description}</p><span>${usuario} — ${fecha}</span></div></div>`;
        }).join('');
    } catch (err) { el.innerHTML = '<p class="text-muted text-center" style="padding:20px;">Error al cargar</p>'; }
}

document.getElementById('formGlobalConfig')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const data = { app_name: fd.get('app_name'), default_theme: fd.get('default_theme'), maintenance_mode: fd.has('maintenance_mode') };
    try {
        const r = await api('/perfil/admin/config-global', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify(data),
        });
        const d = await r.json();
        if (!r.ok) { mostrarToast(d.message, 'error'); return; }
        mostrarToast(d.message, 'success');
    } catch (err) { mostrarToast('Error', 'error'); }
});

// === DARK MODE INIT ===
if ('{{ $user->theme }}' === 'dark') document.body.classList.add('dark-mode');

// Cargar actividad en inicial si es visible
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('tab-actividad').classList.contains('active')) cargarActividad();
});
</script>
</body>
</html>
