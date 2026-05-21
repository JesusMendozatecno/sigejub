@echo off
title SIGEJUB - Configuracion Inicial
echo ============================================
echo  SIGEJUB - Configuracion Inicial
echo ============================================
echo.

:: Verificar PHP
where php >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo [ERROR] No se encuentra PHP. Asegurate de que PHP esta instalado y en PATH.
    pause
    exit /b 1
)

:: Crear .env
if not exist ".env" (
    echo [1/5] Creando archivo .env...
    copy .env.example .env >nul
    echo [OK]
) else (
    echo [1/5] .env ya existe
)

:: Generar APP_KEY
echo [2/5] Generando APP_KEY...
php artisan key:generate --force
echo [OK]

:: Migraciones
echo [3/5] Ejecutando migraciones...
php artisan migrate --force
echo [OK]

:: Seeders
echo [4/5] Ejecutando seeders...
php artisan db:seed --force
echo [OK]

:: Cache
echo [5/5] Limpiando cache...
php artisan optimize:clear
echo [OK]

echo.
echo ============================================
echo  CONFIGURACION COMPLETADA
echo ============================================
echo  Ejecuta start.bat para iniciar el servidor
echo ============================================
pause
