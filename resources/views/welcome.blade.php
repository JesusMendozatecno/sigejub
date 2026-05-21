<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido | SIGEJUB</title>
    <link rel="icon" href="{{ asset('img/descarga (1).png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('img/imagen_2026-05-19_065531142.ico') }}" type="image/x-icon">
    <!-- Vinculamos al CSS unificado de presentación -->
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome/css/all.min.css') }}">
</head>
<body>

    <!-- NAVBAR: Estilo oscuro institucional -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo-brand">
                <div class="logo-box">🏛️</div>
                <div class="logo-text">
                    <span class="main-title">SIGEJUB</span>
                    <span class="sub-title">GESTIÓN INTEGRAL</span>
                </div>
            </div>
            <ul class="nav-links">
                <li><a href="#">Inicio</a></li>
                <li><a href="#">Noticias</a></li>
                <li><a href="#">Contacto</a></li>
                <li><a href="{{ route('login') }}" class="btn-nav-login">Ingresar</a></li>
            </ul>
        </div>
    </nav>

    <!-- SECCIÓN PRINCIPAL (HERO) -->
    <main class="hero-section">
        <div class="hero-content">
            <!-- Logo central con el estilo de tu proyecto -->
            <div class="hero-visual">
                <div class="hero-logo-large">🏛️</div>
                <h1 class="hero-title">SIGEJUB 1.0</h1>
                <p class="hero-description">Sistema de Registro de Inventario y Soporte Técnico</p>
                
                <div class="hero-actions">
                    <a href="{{ route('login') }}" class="btn-main">Acceder al Sistema</a>
                    <a href="{{ route('register') }}" class="btn-secondary">Solicitar Registro</a>
                </div>
            </div>
        </div>
    </main>

    <footer class="welcome-footer">
        <p>© 2026 INSTITUCIÓN EDUCATIVA - COORDINACIÓN DE TECNOLOGÍA</p>
    </footer>

    @include('partials.toast')
</body>
</html>