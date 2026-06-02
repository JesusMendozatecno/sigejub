<!DOCTYPE html>
<html>
<head>
    <title>Registro SIGEJUB</title>
    <link rel="icon" href="{{ asset('img/descarga (1).png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('img/imagen_2026-05-19_065531142.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome/css/all.min.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

<div class="register-container">
    <form method="POST" action="/register" onsubmit="return validarFormulario(event)">
        @csrf

        <a href="{{ url('/') }}" class="btn-back" title="Volver al inicio">
            <i class="fas fa-arrow-left"></i>
        </a>

        <h2 style="margin:0 0 16px;text-align:center;">Registro de Usuario</h2>

        <div class="form-grid">

            <div class="input-group full-width">
                <label>CORREO ELECTRÓNICO</label>
                <input type="email" name="email" id="regEmail" placeholder="correo@ejemplo.com" value="{{ old('email') }}" required oninput="validarEmail()">
                <span class="validation-msg" id="emailMsg"></span>
            </div>

            <div class="input-group">
                <label>NOMBRE</label>
                <input type="text" name="name" id="regName" placeholder="Nombre" value="{{ old('name') }}" required oninput="capitalizar(this)">
            </div>

            <div class="input-group">
                <label>APELLIDO</label>
                <input type="text" name="surname" id="regSurname" placeholder="Apellido" value="{{ old('surname') }}" required oninput="capitalizar(this)">
            </div>

            <div class="input-group">
                <label>FECHA DE NACIMIENTO</label>
                <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required>
            </div>

            <div class="input-group">
                <label>TELÉFONO</label>
                <div class="phone-wrapper">
                    <select name="phone_country_code" id="countryCode" class="country-code-select">
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
                    <input type="tel" name="phone" id="regPhone" placeholder="412-0000000" value="{{ old('phone') }}" required oninput="this.value=this.value.replace(/[^0-9-]/g,'')">
                </div>
            </div>

            <div class="input-group">
                <label>CONTRASEÑA</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="regPassword" placeholder="Mín. 8 caracteres, mayúscula, minúscula y número" required oninput="medirFortaleza()">
                    <button type="button" class="toggle-password" onclick="togglePassword('regPassword', this)" tabindex="-1">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength" id="passwordStrength">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
                <span class="validation-msg" id="passwordMsg"></span>
            </div>

            <div class="input-group">
                <label>CONFIRMAR CONTRASEÑA</label>
                <div class="password-wrapper">
                    <input type="password" name="password_confirmation" id="regPasswordConfirm" placeholder="Repita la contraseña" required oninput="validarConfirmacion()">
                    <button type="button" class="toggle-password" onclick="togglePassword('regPasswordConfirm', this)" tabindex="-1">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <span class="validation-msg" id="confirmMsg"></span>
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
                <button type="submit" id="regBtn">Registrar</button>
            </div>

            <p class="full-width" style="margin: 0; text-align: center;">
                ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
            </p>

        </div>

    </form>

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
        msg.className = 'validation-msg valid-msg';
        input.classList.add('valid');
        input.classList.remove('invalid');
    } else {
        msg.textContent = '✗ Formato de correo inválido';
        msg.className = 'validation-msg error-msg';
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
        { min: 0, color: '#e2e8f0', ancho: '0%', texto: '' },
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
    msg.className = 'validation-msg ' + (fuerza < 2 ? 'error-msg' : fuerza < 3 ? '' : 'valid-msg');

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
        msg.className = 'validation-msg valid-msg';
    } else {
        msg.textContent = '✗ No coinciden';
        msg.className = 'validation-msg error-msg';
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
@include('partials.toast')
@if(session('success'))
    <script>mostrarToast('{{ session('success') }}', 'success');</script>
@endif
@if($errors->any())
    <script>mostrarToast('{{ $errors->first() }}', 'error');</script>
@endif
</body>
</html>
