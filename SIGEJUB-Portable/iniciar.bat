@echo off
title SIGEJUB - Servidor
cd /d "%~dp0"
chcp 65001 >nul 2>&1

rem =============================================
rem SIN LIMITE DE 30s PARA EL SERVIDOR php -S
rem php -S usa max_execution_time=30 de php.ini
rem y mataba la 1a peticion con Maximum execution time exceeded
rem en arranques lentos. PHP_INI_SCAN_DIR agrega este ini ENCIMA de
rem php.ini sin tocar la instalacion de XAMPP
rem =============================================
if not exist "%~dp0ini" mkdir "%~dp0ini"
if not exist "%~dp0ini\sigejub.ini" echo max_execution_time = 0 >"%~dp0ini\sigejub.ini"
set "PHP_INI_SCAN_DIR=%~dp0ini"

echo ============================================
echo  SIGEJUB - Sistema de Jubilaciones
echo ============================================
echo.

:: =============================================
:: BUSCAR PHP - PATH, luego rutas XAMPP comunes
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

rem =============================================
rem ENLACE DE STORAGE public\storage a storage\app\public
rem =============================================
if not exist "public\storage" (
    if exist "storage\app\public" (
        cmd /c mklink /J "public\storage" "storage\app\public" >nul 2>&1
        if not exist "public\storage" "%PHP_PATH%" artisan storage:link >nul 2>&1
    )
)

rem =============================================
rem LEER PUERTO desde APP_URL en .env
rem =============================================
set PORT=8000
set "APP_URL_VAL="
if exist ".env" (
    for /f "tokens=2 delims==" %%A in ('findstr /B /C:"APP_URL=" .env 2^>nul') do set "APP_URL_VAL=%%A"
)
if defined APP_URL_VAL set "PORT=%APP_URL_VAL:http://localhost:=%"
if defined APP_URL_VAL set "PORT=%PORT:http://127.0.0.1:=%"
if "%PORT%"=="" set PORT=8000
set URL=http://localhost:%PORT%

rem =============================================
rem VERIFICAR SI EL SERVIDOR YA ESTA CORRIENDO
rem =============================================
netstat -ano 2>nul | findstr /R /C:":%PORT% .*LISTENING" >nul 2>&1
if %ERRORLEVEL% equ 0 (
    echo [OK] El servidor ya estaba corriendo en %URL%
    start "" "%URL%"
    goto :fin_ok
)

rem =============================================
rem INICIAR SERVIDOR EN SEGUNDO PLANO (con log)
rem =============================================
echo [INICIANDO] Servidor en %URL% ...
set "SRV_LOG=%~dp0storage\logs\servidor.log"
set "SRV_ERR=%~dp0storage\logs\servidor-error.log"
if not exist "%~dp0storage\logs" mkdir "%~dp0storage\logs" >nul 2>&1

rem Lanza php artisan serve oculto, guardando salida y errores en logs.
powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath '%PHP_PATH%' -ArgumentList 'artisan','serve','--host=127.0.0.1','--port=%PORT%' -WorkingDirectory '%~dp0' -WindowStyle Hidden -RedirectStandardOutput '%SRV_LOG%' -RedirectStandardError '%SRV_ERR%'"

rem =============================================
rem ESPERAR A QUE EL PUERTO ESTE LISTO (max 30s)
rem =============================================
echo Esperando respuesta del servidor...
set "SERVER_OK=0"
setlocal enabledelayedexpansion
for /l %%i in (1,1,30) do (
    ping -n 2 127.0.0.1 >nul
    netstat -ano 2>nul | findstr /R /C:":%PORT% .*LISTENING" >nul 2>&1
    if !ERRORLEVEL! equ 0 set "SERVER_OK=1"
    if "!SERVER_OK!"=="1" goto :srv_listo
)
:srv_listo
endlocal & set "SERVER_OK=%SERVER_OK%"

if "%SERVER_OK%"=="1" goto :server_ready

echo.
echo [ERROR] El servidor no respondio en 30 segundos.
echo.
if exist "%SRV_ERR%" (
    echo ----- Detalle del error -----
    type "%SRV_ERR%"
    echo -----------------------------
)
if exist "%SRV_LOG%" (
    echo ----- Salida del servidor -----
    type "%SRV_LOG%"
    echo -------------------------------
)
echo.
echo Verifica que la base de datos este activa y configurada en .env
echo.
echo Presiona cualquier tecla para cerrar...
pause >nul
exit /b 1

:server_ready
ping -n 2 127.0.0.1 >nul
start "" "%URL%"
echo [OK] Servidor corriendo en %URL%
echo     Para detenerlo: cierra las ventanas de PHP o usa detener.bat

:fin_ok

