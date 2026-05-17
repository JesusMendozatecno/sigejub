<!DOCTYPE html>
<html>
<head>
    <title>Registro SIGEJUB</title>

    <!-- CSS exclusivo del registro -->
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>
<body>

<!-- Contenedor del registro -->
<div class="register-container">

    
        <!-- Botón Volver -->
        <a href="{{ url('/') }}" class="btn-back" title="Volver al inicio">
            <i data-lucide="arrow-left"></i>
        </a>

    <h2>Registro de Usuario</h2>

    <!-- Formulario de registro -->
    <form method="POST" action="/register">
        @csrf

        <!-- Nombre -->
        <input type="text" name="name" placeholder="Nombre">

        <!-- Email -->
        <input type="email" name="email" placeholder="Email">

        <!-- Contraseña -->
        <input type="password" name="password" placeholder="Contraseña">

        <!-- Rol del usuario -->
        <select name="role">
            <option value="analista">Analista</option>
            <option value="admin">Admin</option>
        </select>

        <!-- Botón -->
        <button>Registrar</button>
         <!-- Enlace a registro -->
        <p style="margin-top: 10px;">
            ¿Deseas Iniciar Seccion?
            <a href="{{ route('login') }}">Inicio</a>
        </p>

    </form>

   

</div>
<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
</body>
</html>
