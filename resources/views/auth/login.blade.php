<!DOCTYPE html>
<html>
<head>
    <title>Login SIGEJUB</title>
    <link rel="icon" href="{{ asset('img/logo-dark.svg') }}" type="image/svg+xml">
    <link rel="shortcut icon" href="{{ asset('img/logo-dark.svg') }}" type="image/svg+xml">
    <!-- CSS exclusivo del login -->
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

        <!-- Formulario de autenticación -->
        <form method="POST" action="/login" onsubmit="mostrarCargando('Iniciando sesión...')">
            @csrf <!-- Seguridad Laravel (evita ataques CSRF) -->

            <!-- Campo email -->
            <div class="input-group">
                <label>Usuario</label>
                <input type="text" name="email" required>
            </div>

            <!-- Campo contraseña -->
            <div class="input-group">
                <label>Contraseña</label>
                <input type="password" name="password" required>
            </div>

            <!-- Botón de acceso -->
            <button class="btn">Entrar</button>
        </form>

        <!-- Enlace a registro -->
        <p style="margin-top: 10px;">
            ¿Deseas Registrarte?
            <a href="{{ route('register') }}">Regístrate aquí</a>
        </p>

    </div>
</div>
    @include('partials.toast')
    @if(session('success'))
        <script>mostrarToast('{{ session('success') }}', 'success');</script>
    @endif
    @if($errors->any())
        <script>mostrarToast('{{ $errors->first() }}', 'error');</script>
    @endif
</body>
</html>
