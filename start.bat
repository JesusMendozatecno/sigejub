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

:: Verificar si el servidor ya esta corriendo
netstat -an | find ":8000" >nul 2>&1
if %ERRORLEVEL% equ 0 (
    echo [OK] El servidor ya esta corriendo en http://localhost:8000
    start http://localhost:8000
    exit /b 0
)

:: Verificar que existe .env
if not exist ".env" (
    echo [SETUP] Creando archivo .env...
    copy .env.example .env >nul
    php artisan key:generate --force
    echo [SETUP] Hecho. Revisa y configura tu base de datos en .env
)

:: Iniciar servidor
echo [INICIANDO] Servidor en http://localhost:8000
start http://localhost:8000
php artisan serve --port=8000

pause
