<!DOCTYPE html>
<html>
<head>
    <title>Registro SIGEJUB</title>
    <link rel="icon" href="{{ asset('img/descarga (1).png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('img/imagen_2026-05-19_065531142.ico') }}" type="image/x-icon">
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

    <form method="POST" action="/register" onsubmit="mostrarCargando('Creando cuenta...')">
        @csrf

        <div class="form-grid">

            <div class="input-group full-width">
                <label>CORREO ELECTRÓNICO</label>
                <input type="email" name="email" placeholder="correo@ejemplo.com" value="{{ old('email') }}" required>
            </div>

            <div class="input-group">
                <label>NOMBRE</label>
                <input type="text" name="name" placeholder="Nombre" value="{{ old('name') }}" required>
            </div>

            <div class="input-group">
                <label>APELLIDO</label>
                <input type="text" name="surname" placeholder="Apellido" value="{{ old('surname') }}" required>
            </div>

            <div class="input-group">
                <label>FECHA DE NACIMIENTO</label>
                <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required>
            </div>

            <div class="input-group">
                <label>TELÉFONO</label>
                <input type="tel" name="phone" placeholder="0412-0000000" value="{{ old('phone') }}" required>
            </div>

            <div class="input-group">
                <label>CONTRASEÑA</label>
                <input type="password" name="password" placeholder="Mínimo 6 caracteres" required>
            </div>

            <div class="input-group">
                <label>CONFIRMAR CONTRASEÑA</label>
                <input type="password" name="password_confirmation" placeholder="Repita la contraseña" required>
            </div>

            <div class="input-group full-width">
                <label>ROL DEL USUARIO</label>
                <select name="role" required>
                    <option value="" disabled selected>Seleccione un rol...</option>
                    <option value="analista">Analista</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div class="full-width">
                <button type="submit">Registrar</button>
            </div>

            <p class="full-width" style="margin: 0; text-align: center;">
                ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
            </p>

        </div>

    </form>

   

</div>
<script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
    @include('partials.toast')
    @if(session('success'))
        <script>mostrarToast('{{ session('success') }}', 'success');</script>
    @endif
    @if($errors->any())
        <script>mostrarToast('{{ $errors->first() }}', 'error');</script>
    @endif
</body>
</html>
