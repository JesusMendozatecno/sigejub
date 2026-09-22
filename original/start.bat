@echo off
title SIGEJUB - Servidor
cd /d "%~dp0"
chcp 65001 >nul 2>&1

echo ============================================
echo  SIGEJUB - Sistema de Jubilaciones
echo ============================================
echo.

:: =============================================
:: BUSCAR PHP — PATH, luego rutas XAMPP comunes
:: =============================================
set PHP_PATH=
where php >nul 2>&1
if %ERRORLEVEL% equ 0 (
    for /f "delims=" %%i in ('where php') do (
        if not defined PHP_PATH set "PHP_PATH=%%i"
    )
)

if not defined PHP_PATH (
    for %%P in (C D E F) do (
        if exist "%%P:\xampp\php\php.exe" set "PHP_PATH=%%P:\xampp\php\php.exe"
        if defined PHP_PATH goto :php_found
    )
    for %%P in (C D E F) do (
        if exist "%%P:\php\php.exe" set "PHP_PATH=%%P:\php\php.exe"
        if defined PHP_PATH goto :php_found
    )
    for %%P in (C D E F) do (
        if exist "%%P:\Program Files\php\php.exe" set "PHP_PATH=%%P:\Program Files\php\php.exe"
        if defined PHP_PATH goto :php_found
    )
    for %%P in (C D E F) do (
        if exist "%%P:\Program Files (x86)\php\php.exe" set "PHP_PATH=%%P:\Program Files (x86)\php\php.exe"
        if defined PHP_PATH goto :php_found
    )
)

:php_found
if not defined PHP_PATH (
    echo [ERROR] PHP no fue encontrado.
    echo.
    echo Se busco en: PATH del sistema, C:\xampp\php, C:\php, C:\Program Files\php
    echo.
    pause
    exit /b 1
)

echo [OK] PHP encontrado en: %PHP_PATH%
echo.

:: =============================================
:: VERIFICAR PHP Y ARTISAN
:: =============================================
"%PHP_PATH%" -r "echo 'PHP_OK';" >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo [ERROR] PHP no responde. Verifica la instalacion.
    pause
    exit /b 1
)
if not exist "artisan" (
    echo [ERROR] No se encuentra el archivo 'artisan' en el directorio actual.
    echo.
    echo         Ejecuta start.bat desde: C:\xampp\htdocs\sigejub 2
    echo         O desde la carpeta donde instalaste SIGEJUB.
    pause
    exit /b 1
)

:: =============================================
:: CREAR .env si no existe
:: =============================================
if not exist ".env" (
    if exist ".env.example" (
        echo [SETUP] Creando archivo .env...
        copy ".env.example" ".env" >nul 2>&1
        "%PHP_PATH%" artisan key:generate --force
        echo [SETUP] .env creado y APP_KEY generada.
        echo.
    ) else (
        echo [AVISO] No se encontro .env.example
        echo.
    )
)

:: =============================================
:: LEER PUERTO desde APP_URL en .env
:: =============================================
set PORT=8000
if exist ".env" (
    for /f "tokens=2 delims==" %%A in ('findstr "^APP_URL=" .env 2^>nul') do set "APP_URL_VAL=%%A"
)
if defined APP_URL_VAL set "PORT=%APP_URL_VAL:http://localhost:=%"
if "%PORT%"=="" set PORT=8000
set URL=http://localhost:%PORT%

:: =============================================
:: VERIFICAR SI EL SERVIDOR YA ESTA CORRIENDO
:: =============================================
netstat -ano 2>nul | findstr /C:":%PORT% " >nul 2>&1
if %ERRORLEVEL% equ 0 (
    echo [OK] Servidor ya disponible en %URL%
    start "" "%URL%"
    exit /b 0
)

:: =============================================
:: INICIAR SERVIDOR EN SEGUNDO PLANO
:: =============================================
echo [INICIANDO] Servidor en %URL% ...
powershell -Command "Start-Process -FilePath '%PHP_PATH%' -ArgumentList 'artisan serve --port=%PORT%' -WindowStyle Hidden -WorkingDirectory '%~dp0'"

:: =============================================
:: ESPERAR A QUE EL PUERTO ESTE LISTO (max 30s)
:: =============================================
echo Esperando respuesta del servidor...
setlocal enabledelayedexpansion
for /l %%i in (1,1,30) do (
    timeout /t 1 /nobreak >nul
    netstat -ano 2>nul | findstr /C:":%PORT% " >nul 2>&1
    if !ERRORLEVEL! equ 0 (
        endlocal
        echo [OK] Servidor listo.
        goto :server_ready
    )
)
endlocal

echo [TIMEOUT] El servidor no respondio en 30s.
echo Verifica que PostgreSQL este corriendo (puerto 5433) y la base de datos este configurada en .env
echo.
echo Presiona cualquier tecla para cerrar...
pause >nul
exit /b 1

:server_ready
timeout /t 1 /nobreak >nul
start "" "%URL%"
echo [OK] Servidor corriendo en %URL%
