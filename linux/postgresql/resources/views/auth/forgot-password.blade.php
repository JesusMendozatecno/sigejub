<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - SIGEJUB</title>
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
                <p class="login-info-subtitle">Restablece el acceso a tu cuenta de forma segura.</p>
            </div>
        </div>

        <div class="login-form-panel">
            <div class="login-form-wrapper">
                <a href="{{ url('/login') }}" class="login-back">
                    <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
                </a>

                <h2 class="login-form-title">¿Olvidaste tu contraseña?</h2>
                <p class="login-form-subtitle">Ingresa tu correo electrónico y te enviaremos un enlace para restablecerla.</p>

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="login-input-group">
                        <label for="forgotEmail">Correo Electrónico</label>
                        <input class="login-input" type="email" name="correo" id="forgotEmail" placeholder="tu@correo.com" value="{{ old('correo') }}" required autofocus autocomplete="email">
                    </div>

                    <button class="login-submit" type="submit">
                        <i class="fas fa-paper-plane"></i> Enviar enlace de recuperación
                    </button>
                </form>

                <p class="login-register-link">
                    ¿Recordaste tu contraseña? <a href="{{ route('login') }}">Inicia sesión</a>
                </p>
            </div>
        </div>

    </div>

    @if(session('status'))
        <script>mostrarToast('{{ session('status') }}', 'success');</script>
    @endif
    @if($errors->any())
        <script>mostrarToast('{{ $errors->first() }}', 'error');</script>
    @endif
</body>
</html>