@echo off
title SIGEJUB - Instalacion
cd /d "%~dp0"

echo =============================================
echo  SIGEJUB - Configuracion inicial
echo =============================================
echo.

:: 1. Verificar PHP
where php >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] PHP no encontrado. Instala PHP y agregalo al PATH.
    pause
    exit /b 1
)
echo [OK] PHP encontrado

:: 2. Verificar Composer
where composer >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Composer no encontrado. Instala Composer desde https://getcomposer.org
    pause
    exit /b 1
)
echo [OK] Composer encontrado

:: 3. Copiar .env si no existe
if not exist ".env" (
    copy .env.example .env >nul
    echo [OK] .env creado desde .env.example
) else (
    echo [OK] .env ya existe
)

:: 4. Instalar dependencias
echo.
echo Instalando dependencias de Composer...
call composer install --no-interaction
if %errorlevel% neq 0 (
    echo [ERROR] Fallo composer install
    pause
    exit /b 1
)
echo [OK] Dependencias instaladas

:: 5. Generar key
echo.
echo Generando APP_KEY...
php artisan key:generate --force
echo [OK] APP_KEY generada

:: 6. Crear base de datos y migrar
echo.
echo IMPORTANTE: Antes de continuar, asegurate de tener MySQL corriendo
echo y una base de datos llamada 'bd-sigejub' creada (o la que configures en .env).
echo.
choice /C SN /M "Ejecutar migraciones ahora"
if %errorlevel% equ 1 (
    echo Ejecutando migraciones...
    php artisan migrate --force
    if %errorlevel% neq 0 (
        echo [ADVERTENCIA] Las migraciones fallaron. Revisa tu configuracion de BD en .env
    ) else (
        echo [OK] Migraciones ejecutadas
    )
) else (
    echo [INFO] Migraciones omitidas. Ejecuta 'php artisan migrate' manualmente.
)

:: 7. Limpiar cache
php artisan optimize:clear >nul
echo [OK] Cache limpiada

:: 8. Crear enlace storage
php artisan storage:link >nul 2>&1
echo [OK] Storage link creado

echo.
echo =============================================
echo  Instalacion completada.
echo =============================================
echo.
echo Para iniciar el servidor:
echo   php artisan serve
echo.
echo Luego abre http://127.0.0.1:8000 en tu navegador
echo.
pause
