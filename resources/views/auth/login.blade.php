<!DOCTYPE html>
<html>
<head>
    <title>Login SIGEJUB</title>

    <!-- CSS exclusivo del login -->
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

<!-- Contenedor principal del login -->
<div class="login-container">

    <!-- Tarjeta del formulario -->
    <div class="card">

        <!-- Botón Volver -->
        <a href="{{ url('/') }}" class="btn-back" title="Volver al inicio">
            <i data-lucide="arrow-left"></i>
        </a>

        <!-- Título del sistema -->
        <h2>Gestión Jubilaciones</h2>

        <!-- Subtítulo -->
        <p>Accede a tu cuenta</p>

        <!-- Formulario de autenticación -->
        <form method="POST" action="/login">
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
<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
</body>
</html>
