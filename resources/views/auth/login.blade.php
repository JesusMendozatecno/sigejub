{{-- login.blade.php - Template de la página de inicio de sesión con formulario AJAX, loading overlay y sonidos. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SIGEJUB</title>
    <link rel="icon" href="{{ asset('img/descarga (1).png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('img/imagen_2026-05-19_065531142.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome/css/all.min.css') }}">
</head>
<body>

<!-- Contenedor principal del login -->
<div class="login-container">

    <!-- Tarjeta del formulario -->
    <div class="card">

        <!-- Botón Volver -->
        <a href="{{ url('/') }}" class="btn-back" title="Volver al inicio">
            <i class="fas fa-arrow-left"></i>
        </a>

        <!-- Título del sistema -->
        <h2>Gestión Jubilaciones</h2>

        <!-- Subtítulo -->
        <p>Accede a tu cuenta</p>

        <!-- Formulario de autenticación (AJAX + loading + sonidos) -->
        <form id="loginForm">
            @csrf

            <!-- Campo email -->
            <div class="input-group">
                <label>Usuario</label>
                <input type="text" name="correo" id="loginEmail" required>
            </div>

            <!-- Campo contraseña con ojo toggle -->
            <div class="input-group" style="position:relative;">
                <label>Contraseña</label>
                <input type="password" name="password" id="loginPassword" required style="padding-right:40px;">
                <button type="button" id="togglePassword" style="position:absolute;right:10px;bottom:10px;background:none;border:none;color:#94a3b8;cursor:pointer;font-size:1.1rem;padding:4px;">
                    <i class="fas fa-eye" id="togglePasswordIcon"></i>
                </button>
            </div>

            <script>
            document.getElementById('togglePassword')?.addEventListener('click', function() {
                var input = document.getElementById('loginPassword');
                var icon = document.getElementById('togglePasswordIcon');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
            </script>

            <!-- Botón de acceso -->
            <button class="btn" id="loginBtn" type="submit">Entrar</button>
        </form>

        <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            mostrarCargando('Verificando credenciales...');
            var btn = document.getElementById('loginBtn');
            btn.disabled = true;
            btn.textContent = 'Entrando...';
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
                // Obtiene el HTML completo del dashboard
                var dashResp = await fetch('/dashboard');
                var dashHtml = await dashResp.text();
                // Reemplaza la página completa sin recargar (el overlay sigue visible)
                document.open();
                document.write(dashHtml);
                document.close();
                history.pushState(null, '', data.redirect || '/dashboard');
            } catch (err) {
                ocultarCargando();
                btn.disabled = false;
                btn.textContent = 'Entrar';
                var mensaje = err.message || err.error || 'Credenciales incorrectas';
                mostrarToast(mensaje, 'error');
            }
        });
        </script>

        <!-- Enlace a registro -->
        <p style="margin-top: 10px;">
            ¿Deseas Registrarte?
            <a href="{{ route('register') }}">Regístrate aquí</a>
        </p>

    </div>
</div>
    @include('partials.loading-overlay')
    @include('partials.toast')
    @if(session('success'))
        <script>mostrarToast('{{ session('success') }}', 'success');</script>
    @endif
    @if($errors->any())
        <script>mostrarToast('{{ $errors->first() }}', 'error');</script>
    @endif
    <script>sessionStorage.removeItem('sigejub_dashboard_cargado');localStorage.removeItem('sigejub_active_section');</script>
</body>
</html>
