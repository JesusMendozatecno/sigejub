{{-- user.blade.php - Página de perfil de usuario con pestañas: Mi Perfil, Seguridad (2FA, sesiones), Configuración (tema, idioma, color), Actividad y Administración. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil — SIGEJUB</title>
    <link rel="icon" href="{{ asset('img/logo-dark.svg') }}" type="image/svg+xml">
    <link rel="shortcut icon" href="{{ asset('img/logo-dark.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.min.css') }}?v={{ filemtime(public_path('css/dashboard/dashboard.min.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/modern-theme.css') }}?v={{ filemtime(public_path('css/dashboard/modern-theme.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome/css/all.min.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/cropperjs/cropper.min.css') }}">
    <script defer src="{{ asset('js/cropperjs/cropper.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/dashboard/profile.css') }}?v={{ filemtime(public_path('css/dashboard/profile.css')) }}">
    <style>:root { --accent: {{ $user->color_acento ?? '#1a365d' }}; }</style>
</head>
<body>
<div class="profile-wrapper">
    <div class="profile-header">
        <div class="flex-center gap-3">
            <a href="{{ route('dashboard') }}" class="btn-back" title="Volver al dashboard">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1>Mi Perfil</h1>
        </div>
        <span class="text-muted">Último acceso: {{ $user->ultimo_acceso ? $user->ultimo_acceso->diffForHumans() : 'Primer ingreso' }}</span>
    </div>

    <div class="profile-layout">
        <!-- Sidebar -->
        <div class="profile-sidebar">
            <div class="profile-card">
                <div class="avatar-wrap" onclick="document.getElementById('avatarInput').click()">
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
                <a href="{{ route('documentacion') }}" class="profile-nav-item" style="text-decoration:none;cursor:pointer;">
                    <i class="fas fa-book"></i> Documentación
                </a>
                <div class="profile-nav-divider"></div>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
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
                <h2 class="tab-title">Mi Perfil</h2>
                <p class="tab-subtitle">Información básica de tu cuenta</p>
                <form id="formProfile">
                    <div class="form-group">
                        <label>Rol</label>
                        <input type="text" class="form-input" value="{{ $user->rol === 'admin' ? 'Administrador' : ($user->rol === 'superadmin' ? 'Superadmin' : 'Usuario') }}" disabled>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" class="form-input" name="nombre" value="{{ $user->nombre }}" disabled oninput="this.value=this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g,'')">
                        </div>
                        <div class="form-group">
                            <label>Apellido</label>
                            <input type="text" class="form-input" name="apellido" value="{{ $user->apellido }}" disabled oninput="this.value=this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g,'')">
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Correo electrónico</label>
                            <input type="email" class="form-input" name="correo" value="{{ $user->correo }}" disabled>
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" class="form-input" name="telefono" value="{{ $user->telefono }}" disabled oninput="this.value=this.value.replace(/[^0-9+\-\s]/g,'')">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Fecha de nacimiento</label>
                        <input type="date" class="form-input" name="fecha_nacimiento" value="{{ $user->fecha_nacimiento ? \Carbon\Carbon::parse($user->fecha_nacimiento)->format('Y-m-d') : '' }}" disabled>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <button type="button" class="btn btn-primary" id="btnEditarPerfil">Editar Datos</button>
                        <button type="submit" class="btn btn-success" id="btnGuardarPerfil" style="display:none;">Guardar Datos</button>
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
                    <div class="theme-option {{ $user->tema === 'modern' ? 'selected' : '' }}" data-theme="modern" onclick="cambiarTema('modern')">
                        <i class="fas fa-brush fa-fw"></i>
                        <span>Moderno</span>
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
                        <input type="text" id="adminSearch" placeholder="Buscar usuario..." oninput="cargarAdminUsuarios()">
                        <select id="adminRoleFilter" class="form-input" style="width:auto;" onchange="cargarAdminUsuarios()">
                            <option value="">Todos los roles</option>
                            <option value="admin">Admin</option>
                            <option value="usuario">Usuario</option>
                            <option value="superadmin">Superadmin</option>
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
                                <option value="modern">Moderno</option>
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

<script>
window.SIGEJUB_USER_ID={{ $user->id }};window.SIGEJUB_USER_ROL='{{ $user->rol }}';
window.SIGEJUB_STORAGE_URL='{{ asset("storage") }}';
window.SIGEJUB_PROFILE_THEME='{{ $user->tema }}';
window.SIGEJUB_ACCENT_COLOR='{{ $user->color_acento ?? "#1a365d" }}';
</script>
<script defer src="{{ asset('js/profile.js') }}?v={{ filemtime(public_path('js/profile.js')) }}"></script>
</body>
</html>
