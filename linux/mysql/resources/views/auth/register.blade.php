<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - SIGEJUB</title>
    <link rel="icon" href="{{ asset('img/logo-dark.svg') }}" type="image/svg+xml">
    <link rel="shortcut icon" href="{{ asset('img/logo-dark.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('css/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
    <script src="{{ asset('js/theme-welcome.js') }}" defer></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    @include('partials.loading-overlay')
    @include('partials.toast')

    <div class="register-split">

        <div class="register-info">
            <div class="register-info-content">
                <div class="register-info-logo">
                    <img src="{{ asset('img/logo-light.svg') }}" alt="SIGEJUB">
                </div>
                <h1 class="register-info-title">Únete a SIGEJUB</h1>
                <p class="register-info-subtitle">Crea tu cuenta y comienza a gestionar jubilaciones de forma integral.</p>
                <div class="register-info-features">
                    <div class="register-info-feature">
                        <div class="register-info-feature-icon"><i class="fas fa-shield-halved"></i></div>
                        <div class="register-info-feature-text">
                            <strong>Seguridad</strong>
                            Protección de datos con protocolos avanzados
                        </div>
                    </div>
                    <div class="register-info-feature">
                        <div class="register-info-feature-icon"><i class="fas fa-bolt"></i></div>
                        <div class="register-info-feature-text">
                            <strong>Rápido y Eficiente</strong>
                            Procesos automatizados en tiempo real
                        </div>
                    </div>
                    <div class="register-info-feature">
                        <div class="register-info-feature-icon"><i class="fas fa-headset"></i></div>
                        <div class="register-info-feature-text">
                            <strong>Soporte</strong>
                            Asistencia técnica disponible para ti
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="register-form-panel">
            <div class="register-form-wrapper">
                <a href="{{ url('/') }}" class="register-back">
                    <i class="fas fa-arrow-left"></i> Volver al inicio
                </a>

                <h2 class="register-form-title">Crear Cuenta</h2>
                <p class="register-form-subtitle">Completa tus datos para registrarte en el sistema</p>

                <form method="POST" action="/register" onsubmit="return validarFormulario(event)">
                    @csrf

                    <div class="register-section">
                        <div class="register-section-title"><i class="fas fa-user"></i> Información Personal</div>
                        <div class="register-form-grid">

                            <div class="register-input-group reg-full-width">
                                <label for="regEmail">Correo Electrónico</label>
                                <input class="register-input" type="email" name="correo" id="regEmail" placeholder="correo@ejemplo.com" value="{{ old('correo') }}" required oninput="validarEmail()">
                                <span class="register-validation" id="emailMsg"></span>
                            </div>

                            <div class="register-input-group">
                                <label for="regName">Nombre</label>
                                <input class="register-input" type="text" name="nombre" id="regName" placeholder="Nombre" value="{{ old('nombre') }}" required oninput="capitalizar(this);this.value=this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g,'')" onkeypress="if(!/[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/.test(event.key))event.preventDefault()">
                            </div>

                            <div class="register-input-group">
                                <label for="regSurname">Apellido</label>
                                <input class="register-input" type="text" name="apellido" id="regSurname" placeholder="Apellido" value="{{ old('apellido') }}" required oninput="capitalizar(this);this.value=this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g,'')" onkeypress="if(!/[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/.test(event.key))event.preventDefault()">
                            </div>

                            <div class="register-input-group">
                                <label for="regBirthdate">Fecha de Nacimiento</label>
                                <input class="register-input" type="date" name="fecha_nacimiento" id="regBirthdate" value="{{ old('fecha_nacimiento') }}" required>
                            </div>

                            <div class="register-input-group">
                                <label for="regPhone">Teléfono</label>
                                <div class="register-phone-wrapper">
                                    <select name="phone_country_code" id="countryCode" class="register-input register-country-select">
                                        <option value="+58">+58 (VE)</option>
                                        <option value="+56">+56 (CL)</option>
                                        <option value="+54">+54 (AR)</option>
                                        <option value="+57">+57 (CO)</option>
                                        <option value="+52">+52 (MX)</option>
                                        <option value="+51">+51 (PE)</option>
                                        <option value="+593">+593 (EC)</option>
                                        <option value="+591">+591 (BO)</option>
                                        <option value="+55">+55 (BR)</option>
                                        <option value="+598">+598 (UY)</option>
                                        <option value="+595">+595 (PY)</option>
                                        <option value="+507">+507 (PA)</option>
                                        <option value="+506">+506 (CR)</option>
                                        <option value="+502">+502 (GT)</option>
                                        <option value="+504">+504 (HN)</option>
                                        <option value="+503">+503 (SV)</option>
                                        <option value="+505">+505 (NI)</option>
                                        <option value="+1">+1 (US)</option>
                                        <option value="+34">+34 (ES)</option>
                                    </select>
                                    <input class="register-input" type="tel" name="telefono" id="regPhone" placeholder="412-0000000" value="{{ old('telefono') }}" required oninput="this.value=this.value.replace(/[^0-9-]/g,'')">
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="register-section">
                        <div class="register-section-title"><i class="fas fa-lock"></i> Información de Acceso</div>
                        <div class="register-form-grid">

                            <div class="register-input-group">
                                <label for="regPassword">Contraseña</label>
                                <div class="register-password-wrapper">
                                    <input class="register-input" type="password" name="password" id="regPassword" placeholder="Mín. 8 caracteres" required oninput="medirFortaleza()">
                                    <button type="button" class="register-toggle-password" onclick="togglePassword('regPassword', this)" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="register-password-strength" id="passwordStrength">
                                    <div class="register-strength-bar" id="strengthBar"></div>
                                </div>
                                <span class="register-validation" id="passwordMsg"></span>
                            </div>

                            <div class="register-input-group">
                                <label for="regPasswordConfirm">Confirmar Contraseña</label>
                                <div class="register-password-wrapper">
                                    <input class="register-input" type="password" name="password_confirmation" id="regPasswordConfirm" placeholder="Repita la contraseña" required oninput="validarConfirmacion()">
                                    <button type="button" class="register-toggle-password" onclick="togglePassword('regPasswordConfirm', this)" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <span class="register-validation" id="confirmMsg"></span>
                            </div>

                        </div>
                    </div>

                    <button class="register-submit" type="submit" id="regBtn">
                        <i class="fas fa-user-plus"></i> Registrar
                    </button>

                    <p class="register-login-link">
                        ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
                    </p>

                </form>
            </div>
        </div>

    </div>

    <script>
    function togglePassword(inputId, btn) {
        var input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
        } else {
            input.type = 'password';
            btn.innerHTML = '<i class="fas fa-eye"></i>';
        }
    }

    function capitalizar(input) {
        input.value = input.value.replace(/\b\w/g, function(c) { return c.toUpperCase(); });
    }

    function validarEmail() {
        var input = document.getElementById('regEmail');
        var msg = document.getElementById('emailMsg');
        var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (input.value.length === 0) {
            msg.textContent = '';
            input.classList.remove('valid', 'invalid');
            return;
        }
        if (regex.test(input.value)) {
            msg.textContent = '✓ Correo válido';
            msg.className = 'register-validation valid';
            input.classList.add('valid');
            input.classList.remove('invalid');
        } else {
            msg.textContent = '✗ Formato de correo inválido';
            msg.className = 'register-validation error';
            input.classList.add('invalid');
            input.classList.remove('valid');
        }
    }

    function medirFortaleza() {
        var pwd = document.getElementById('regPassword').value;
        var bar = document.getElementById('strengthBar');
        var msg = document.getElementById('passwordMsg');
        var fuerza = 0;

        if (pwd.length >= 6) fuerza += 1;
        if (pwd.length >= 10) fuerza += 1;
        if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) fuerza += 1;
        if (/\d/.test(pwd)) fuerza += 1;
        if (/[^a-zA-Z0-9]/.test(pwd)) fuerza += 1;

        var niveles = [
            { min: 0, color: 'var(--reg-border)', ancho: '0%', texto: '' },
            { min: 1, color: '#ef4444', ancho: '25%', texto: 'Muy poco segura' },
            { min: 2, color: '#f59e0b', ancho: '50%', texto: 'Poco segura' },
            { min: 3, color: '#3b82f6', ancho: '75%', texto: 'Segura' },
            { min: 4, color: '#22c55e', ancho: '100%', texto: 'Muy segura' },
        ];

        var nivel = niveles[0];
        for (var i = niveles.length - 1; i >= 0; i--) {
            if (fuerza >= niveles[i].min) { nivel = niveles[i]; break; }
        }

        bar.style.width = nivel.ancho;
        bar.style.background = nivel.color;
        msg.textContent = nivel.texto;
        msg.className = 'register-validation ' + (fuerza < 2 ? 'error' : fuerza < 3 ? '' : 'valid');

        validarConfirmacion();
    }

    function validarConfirmacion() {
        var pwd = document.getElementById('regPassword').value;
        var confirm = document.getElementById('regPasswordConfirm').value;
        var msg = document.getElementById('confirmMsg');

        if (confirm.length === 0) {
            msg.textContent = '';
            return;
        }
        if (pwd === confirm) {
            msg.textContent = '✓ Coinciden';
            msg.className = 'register-validation valid';
        } else {
            msg.textContent = '✗ No coinciden';
            msg.className = 'register-validation error';
        }
    }

    function validarFormulario(e) {
        var pwd = document.getElementById('regPassword').value;
        var confirm = document.getElementById('regPasswordConfirm').value;

        if (pwd.length < 8) {
            e.preventDefault();
            mostrarToast('La contraseña debe tener al menos 8 caracteres', 'error');
            return false;
        }
        if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(pwd)) {
            e.preventDefault();
            mostrarToast('La contraseña debe incluir mayúsculas, minúsculas y números', 'error');
            return false;
        }
        if (pwd !== confirm) {
            e.preventDefault();
            mostrarToast('Las contraseñas no coinciden', 'error');
            return false;
        }

        mostrarCargando('Creando cuenta...');
        return true;
    }
    </script>

    @if(session('success'))
        <script>mostrarToast('{{ session('success') }}', 'success');</script>
    @endif
    @if($errors->any())
        <script>mostrarToast('{{ $errors->first() }}', 'error');</script>
    @endif
</body>
</html>