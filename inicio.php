<?php
/**
 * SIGEJUB - Auto-launcher web
 * 
 * Accede via: http://localhost/sigejub%202/inicio.php
 * 
 * Sirve el proyecto Laravel directamente a traves de Apache (XAMPP)
 * en el puerto definido por APP_PORT (.env).
 * No necesita "php artisan serve".
 * 
 * Para otros proyectos Laravel:
 * - Copia este archivo a la raiz del otro proyecto
 * - Agrega un VirtualHost en httpd-vhosts.conf con su puerto
 * - Cambia APP_PORT en su .env (ej: 8082, 8083, ...)
 * - Accede via: http://localhost/OTRO_PROYECTO/inicio.php
 */

// ─── Configuracion ──────────────────────────────────────────
$projectDir = __DIR__;
$envFile = $projectDir . DIRECTORY_SEPARATOR . '.env';
$port = '8081';

// Leer puerto desde .env
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (str_starts_with($line, 'APP_PORT=')) {
            $val = trim(substr($line, strpos($line, '=') + 1));
            if (is_numeric($val)) {
                $port = $val;
            }
            break;
        }
    }
}

// ─── Redirigir a la raiz (Apache sirve directamente) ──────
// La raiz '/' redirige a /login o /dashboard segun la sesion
$rootUrl = 'http://localhost:' . $port . '/';

$fp = @fsockopen('127.0.0.1', (int)$port, $errno, $errstr, 1);
if ($fp) {
    fclose($fp);
    header('Location: ' . $rootUrl);
    exit;
}

// Fallback: intentar con php artisan serve
$artisan = '"' . $projectDir . '\artisan"';
$cmd = 'php ' . $artisan . ' serve --port=' . $port;

if (class_exists('COM', false)) {
    try {
        $WshShell = new COM("WScript.Shell");
        $WshShell->Run($cmd, 0, false);
    } catch (Throwable $e) {
        if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
            pclose(popen('start /B "" ' . $cmd, 'r'));
        } else {
            exec('nohup php ' . $artisan . ' serve --port=' . $port . ' > /dev/null 2>&1 &');
        }
    }
} else {
    if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
        pclose(popen('start /B "" ' . $cmd, 'r'));
    } else {
        exec('nohup php ' . $artisan . ' serve --port=' . $port . ' > /dev/null 2>&1 &');
    }
}

// ─── Pagina "Iniciando..." con auto-redirect ────────────────
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Iniciando SIGEJUB...</title>
    <meta http-equiv="refresh" content="5;url=http://localhost:<?= htmlspecialchars($port, ENT_QUOTES, 'UTF-8') ?>/">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            text-align: center;
            padding: 60px 20px;
            background: #0f172a;
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .logo {
            width: 72px; height: 72px;
            background: #1e293b;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .logo svg { width: 40px; height: 40px; fill: #3b82f6; }
        h1 { font-size: 1.6rem; font-weight: 700; margin-bottom: 6px; }
        .sub { color: #94a3b8; margin-bottom: 4px; font-size: 0.9rem; }
        .port { color: #64748b; font-size: 0.8rem; margin-bottom: 20px; }
        .spinner {
            width: 40px; height: 40px;
            border: 4px solid #1e293b;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .btn {
            display: inline-block;
            margin-top: 16px;
            padding: 10px 28px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn:hover { background: #1d4ed8; }
        .note { color: #475569; font-size: 0.75rem; margin-top: 32px; max-width: 400px; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="logo">
        <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
    </div>
    <h1>Iniciando SIGEJUB</h1>
    <p class="sub">El servidor se está iniciando...</p>
    <p class="port">Puerto <?= htmlspecialchars($port, ENT_QUOTES, 'UTF-8') ?></p>
    <div class="spinner"></div>
    <p class="sub">Serás redirigido automáticamente en unos segundos.</p>
    <a class="btn" href="http://localhost:<?= htmlspecialchars($port, ENT_QUOTES, 'UTF-8') ?>/">Ir al sistema ahora</a>
    <p class="note">
        Si el sistema no carga, espera unos segundos y haz clic en el botón de arriba.
        Verifica que el puerto <?= htmlspecialchars($port, ENT_QUOTES, 'UTF-8') ?> no esté ocupado por otro proyecto.
    </p>
</body>
</html>
