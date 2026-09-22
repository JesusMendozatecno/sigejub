<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIGEJUB</title>
    <link rel="icon" href="{{ asset('img/logo-dark.svg') }}" type="image/svg+xml">
    <link rel="shortcut icon" href="{{ asset('img/logo-dark.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('css/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <script src="{{ asset('js/theme-welcome.js') }}" defer></script>
</head>
<body>
    @include('partials.loading-overlay')
    @include('partials.toast')

    <div class="login-split">

        <div class="login-info">
            <div class="login-info-content">
                <div class="login-info-logo">
                    <img src="{{ asset('img/logo-light.svg') }}" alt="SIGEJUB">
                </div>
                <h1 class="login-info-title">Gestión de Jubilaciones</h1>
                <p class="login-info-subtitle">Administre de forma integral el proceso jubilatorio de su institución.</p>
                <div class="login-info-features">
                    <div class="login-info-feature">
                        <div class="login-info-feature-icon"><i class="fas fa-users-gear"></i></div>
                        <div class="login-info-feature-text">
                            <strong>Trabajadores</strong>
                            Gestión completa de expedientes y historial laboral
                        </div>
                    </div>
                    <div class="login-info-feature">
                        <div class="login-info-feature-icon"><i class="fas fa-file-invoice"></i></div>
                        <div class="login-info-feature-text">
                            <strong>Solicitudes</strong>
                            Control de solicitudes con flujos de aprobación
                        </div>
                    </div>
                    <div class="login-info-feature">
                        <div class="login-info-feature-icon"><i class="fas fa-chart-bar"></i></div>
                        <div class="login-info-feature-text">
                            <strong>Reportes</strong>
                            Informes analíticos para la toma de decisiones
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="login-form-panel">
            <div class="login-form-wrapper">
                <a href="{{ url('/') }}" class="login-back">
                    <i class="fas fa-arrow-left"></i> Volver al inicio
                </a>

                <h2 class="login-form-title">Bienvenido nuevamente</h2>
                <p class="login-form-subtitle">Ingresa tus credenciales para acceder al sistema</p>

                <form id="loginForm">
                    @csrf

                    <div class="login-input-group">
                        <label for="loginEmail">Correo Electrónico</label>
                        <input class="login-input" type="text" name="correo" id="loginEmail" placeholder="tu@correo.com" required autocomplete="username">
                    </div>

                    <div class="login-input-group">
                        <label for="loginPassword">Contraseña</label>
                        <div class="login-password-wrapper">
                            <input class="login-input" type="password" name="password" id="loginPassword" placeholder="Ingresa tu contraseña" required>
                            <button type="button" class="login-toggle-password" id="togglePassword">
                                <i class="fas fa-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="login-remember">
                        <label>
                            <input type="checkbox" name="remember"> Recordarme
                        </label>
                        <a href="{{ route('password.request') }}" class="login-forgot">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button class="login-submit" id="loginBtn" type="submit">
                        <i class="fas fa-right-to-bracket"></i> Entrar
                    </button>
                </form>

                <p class="login-register-link">
                    ¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate aquí</a>
                </p>
            </div>
        </div>

    </div>

    <script>
    var emailRecordado = localStorage.getItem('sigejub_email_recordado');
    var chkRemember = document.querySelector('#loginForm input[name="remember"]');
    var inputEmail = document.getElementById('loginEmail');

    // Recupera el correo de la última persona que ingresó con "Recordarme".
    if (emailRecordado && inputEmail) {
        inputEmail.value = emailRecordado;
        if (chkRemember) chkRemember.checked = true;
    }

    document.getElementById('togglePassword')?.addEventListener('click', function() {
        var input = document.getElementById('loginPassword');
        var icon = document.getElementById('togglePasswordIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });

    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        // Si "Recordarme" está marcado, guarda el correo para el próximo acceso.
        if (inputEmail && chkRemember) {
            if (chkRemember.checked && inputEmail.value) {
                localStorage.setItem('sigejub_email_recordado', inputEmail.value.trim());
            } else {
                localStorage.removeItem('sigejub_email_recordado');
            }
        }
        mostrarCargando('Verificando credenciales...');
        var btn = document.getElementById('loginBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Entrando...';
        try {
            var resp = await fetch('/login', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                },
                body: new URLSearchParams(new FormData(this))
            });
            var data = await resp.json();
            if (!resp.ok) throw data;
            document.getElementById('loadingText').textContent = 'Cargando el sistema...';
            var dashResp = await fetch('/dashboard');
            var dashHtml = await dashResp.text();
            document.open();
            document.write(dashHtml);
            document.close();
            history.pushState(null, '', data.redirect || '/dashboard');
        } catch (err) {
            ocultarCargando();
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-right-to-bracket"></i> Entrar';
            var mensaje = err.message || err.error || 'Credenciales incorrectas';
            mostrarToast(mensaje, 'error');
        }
    });
    </script>

    @if(session('success'))
        <script>mostrarToast('{{ session('success') }}', 'success');</script>
    @endif
    @if($errors->any())
        <script>mostrarToast('{{ $errors->first() }}', 'error');</script>
    @endif
    <script>sessionStorage.removeItem('sigejub_dashboard_cargado');localStorage.removeItem('sigejub_active_section');</script>
</body>
</html>