@echo off
title SIGEJUB - Servidor
echo ============================================
echo  SIGEJUB - Sistema de Jubilaciones
echo ============================================
echo.

:: Verificar que PHP existe
where php >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo [ERROR] No se encuentra PHP. Asegurate de que PHP esta instalado y en PATH.
    pause
    exit /b 1
)

:: Leer puerto desde .env (default 8080)
set PORT=8080
for /f "tokens=2 delims==" %%a in ('findstr /b "APP_PORT" .env 2^>nul') do set PORT=%%a
if "%PORT%"=="" set PORT=8080

:: Verificar si el servidor ya esta corriendo (Apache o artisan)
netstat -an | find ":%PORT%" >nul 2>&1
if %ERRORLEVEL% equ 0 (
    echo [OK] Servidor disponible en http://localhost:%PORT%/
    start http://localhost:%PORT%/
    exit /b 0
)

:: Verificar que existe .env
if not exist ".env" (
    echo [SETUP] Creando archivo .env...
    copy .env.example .env >nul
    php artisan key:generate --force
    echo [SETUP] Hecho. Revisa y configura tu base de datos en .env
)

:: Iniciar servidor (fallback: php artisan serve si Apache no esta configurado)
echo [INICIANDO] Servidor en http://localhost:%PORT%
start http://localhost:%PORT%
php artisan serve --port=%PORT%

pause
