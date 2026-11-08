@echo off
setlocal EnableExtensions EnableDelayedExpansion
title SIGEJUB - Iniciando sistema
cd /d "%~dp0"
chcp 65001 >nul 2>&1

set "APP_DIR=%~dp0"
if "%APP_DIR:~-1%"=="\" set "APP_DIR=%APP_DIR:~0,-1%"
set "LOG_DIR=%APP_DIR%\storage\logs"
if not exist "%LOG_DIR%" mkdir "%LOG_DIR%" >nul 2>&1
set "STARTLOG=%LOG_DIR%\arranque.log"
set "SRV_LOG=%LOG_DIR%\servidor.log"
set "SRV_ERR=%LOG_DIR%\servidor-error.log"

echo ==================================================
echo  SIGEJUB - Sistema Integral de Gestion de Jubilaciones
echo ==================================================
echo.

rem ==================================================
rem SIN LIMITE DE 30s PARA EL SERVIDOR php -S
rem php -S usa max_execution_time=30 de php.ini y eso
rem cortaba la primera peticion en arranques lentos.
rem PHP_INI_SCAN_DIR agrega este ini ENCIMA de php.ini
rem sin tocar la instalacion de XAMPP.
rem ==================================================
if not exist "%APP_DIR%\ini" mkdir "%APP_DIR%\ini" >nul 2>&1
if not exist "%APP_DIR%\ini\sigejub.ini" echo max_execution_time = 0 >"%APP_DIR%\ini\sigejub.ini"
set "PHP_INI_SCAN_DIR=%APP_DIR%\ini"

rem ==================================================
rem 1. LOCALIZAR PHP
rem    Orden: PHP portable incluido en la instalacion,
rem    PATH del sistema y rutas conocidas de XAMPP.
rem ==================================================
set "PHP_PATH="
if exist "%APP_DIR%\php\php.exe" set "PHP_PATH=%APP_DIR%\php\php.exe"

if not defined PHP_PATH (
    for /f "delims=" %%i in ('where php 2^>nul') do (
        if not defined PHP_PATH set "PHP_PATH=%%i"
    )
)

if not defined PHP_PATH (
    for %%P in (C D E F G) do (
        if not defined PHP_PATH if exist "%%P:\xampp\php\php.exe" set "PHP_PATH=%%P:\xampp\php\php.exe"
    )
)
if not defined PHP_PATH (
    for %%P in (C D E F G) do (
        if not defined PHP_PATH if exist "%%P:\php\php.exe" set "PHP_PATH=%%P:\php\php.exe"
    )
)
if not defined PHP_PATH (
    for %%P in (C D E F G) do (
        if not defined PHP_PATH if exist "%%P:\Program Files\php\php.exe" set "PHP_PATH=%%P:\Program Files\php\php.exe"
    )
)
if not defined PHP_PATH (
    for %%P in (C D E F G) do (
        if not defined PHP_PATH if exist "%%P:\Program Files (x86)\php\php.exe" set "PHP_PATH=%%P:\Program Files (x86)\php\php.exe"
    )
)

if not defined PHP_PATH goto :sin_php
echo [OK] PHP: %PHP_PATH%
echo.

rem ==================================================
rem 2. VERIFICAR PHP Y ARTISAN
rem ==================================================
"%PHP_PATH%" -r "echo 'PHP_OK';" >nul 2>&1
if errorlevel 1 goto :php_mudo
if not exist "%APP_DIR%\artisan" goto :sin_artisan
if not exist "%APP_DIR%\vendor\autoload.php" goto :sin_vendor

rem ==================================================
rem 3. ARCHIVO .env Y APP_KEY
rem ==================================================
if not exist "%APP_DIR%\.env" (
    if exist "%APP_DIR%\.env.example" (
        echo [SETUP] Creando archivo .env ...
        copy "%APP_DIR%\.env.example" "%APP_DIR%\.env" >nul 2>&1
    ) else (
        goto :sin_env
    )
)
findstr /B /C:"APP_KEY=base64:" "%APP_DIR%\.env" >nul 2>&1
if errorlevel 1 (
    echo [SETUP] Generando APP_KEY ...
    "%PHP_PATH%" artisan key:generate --force >nul 2>&1
)

rem ==================================================
rem 4. ENLACE public\storage -> storage\app\public
rem ==================================================
if not exist "%APP_DIR%\public\storage" (
    if exist "%APP_DIR%\storage\app\public" (
        cmd /c mklink /J "%APP_DIR%\public\storage" "%APP_DIR%\storage\app\public" >nul 2>&1
        if not exist "%APP_DIR%\public\storage" "%PHP_PATH%" artisan storage:link >nul 2>&1
    )
)

rem ==================================================
rem 5. BASE DE DATOS PORTATIL (si el instalador la incluyo)
rem ==================================================
if exist "%APP_DIR%\sigejub-start-db.bat" (
    call "%APP_DIR%\sigejub-start-db.bat"
)

rem ==================================================
rem 6. PUERTO (desde APP_URL) Y URL
rem ==================================================
set "APP_URL_VAL="
set "PORT="
if exist "%APP_DIR%\.env" (
    for /f "usebackq tokens=2 delims==" %%A in (`findstr /B /C:"APP_URL=" "%APP_DIR%\.env"`) do set "APP_URL_VAL=%%A"
)
if defined APP_URL_VAL (
    set "PORT=!APP_URL_VAL:http://localhost:=!"
    set "PORT=!PORT:http://127.0.0.1:=!"
    set "PORT=!PORT:https://localhost:=!"
    set "PORT=!PORT:https://127.0.0.1:=!"
    set "PORT=!PORT:http://=!"
    set "PORT=!PORT:https://=!"
    for /f "tokens=1 delims=/" %%B in ("!PORT!") do set "PORT=%%B"
    set "PORT=!PORT:*:=!"
)
if not defined PORT set "PORT=8000"
set "PORT=!PORT: =!"
echo !PORT!| findstr /R "^[0-9][0-9]*$" >nul 2>&1
if errorlevel 1 set "PORT=8000"
set "URL=http://localhost:!PORT!"

echo [INFO] Direccion del sistema: !URL!
echo.

rem ==================================================
rem 7. SI EL SERVIDOR YA RESPONDE, SOLO ABRIR EL NAVEGADOR
rem ==================================================
call :esperar "1"
if "!SERVIDOR_OK!"=="1" (
    echo [OK] El servidor ya estaba en ejecucion.
    start "" "!URL!"
    goto :fin_ok
)

rem ==================================================
rem 8. INICIAR EL SERVIDOR EN SEGUNDO PLANO
rem ==================================================
echo [INICIANDO] Servidor en !URL! ...
if exist "%SRV_LOG%" del "%SRV_LOG%" >nul 2>&1
if exist "%SRV_ERR%" del "%SRV_ERR%" >nul 2>&1

powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath '%PHP_PATH%' -ArgumentList 'artisan','serve','--host=127.0.0.1','--port=!PORT!' -WorkingDirectory '%APP_DIR%' -WindowStyle Hidden -RedirectStandardOutput '%SRV_LOG%' -RedirectStandardError '%SRV_ERR%'"

echo Esperando la respuesta del servidor (max 60s) ...
call :esperar "60"

if "!SERVIDOR_OK!"=="1" (
    echo [OK] Servidor listo.
    start "" "!URL!"
    goto :fin_ok
)

rem ==================================================
rem 9. FALLO: REGISTRAR Y AVISAR AL USUARIO
rem ==================================================
echo [%DATE% %TIME%] ERROR: el servidor no respondio en !URL! >>"%STARTLOG%"
if exist "%SRV_ERR%" type "%SRV_ERR%" >>"%STARTLOG%" 2>nul
if exist "%SRV_LOG%" type "%SRV_LOG%" >>"%STARTLOG%" 2>nul

echo.
echo [ERROR] No se pudo iniciar el servidor.
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
echo Revisa que la base de datos este activa y configurada en .env
echo Registro completo: %STARTLOG%
echo.
powershell -NoProfile -Command "Add-Type -AssemblyName PresentationFramework; [System.Windows.MessageBox]::Show('No se pudo iniciar SIGEJUB.' + [Environment]::NewLine + [Environment]::NewLine + 'Revisa que la base de datos este activa.' + [Environment]::NewLine + 'Detalle en: %STARTLOG%','SIGEJUB','OK','Warning')" >nul 2>&1
pause >nul
exit /b 1

rem ==================================================
rem SUBRUTINA: esperar a que http://127.0.0.1:PORT responda 200
rem Recibe el numero de intentos (1 segundo cada uno)
rem ==================================================
:esperar
set "SERVIDOR_OK=0"
set /a "INTENTOS=%~1"
if %INTENTOS% LSS 1 set "INTENTOS=1"
for /l %%i in (1,1,%INTENTOS%) do (
    powershell -NoProfile -Command "try { $r = Invoke-WebRequest -UseBasicParsing -Uri 'http://127.0.0.1:!PORT!/' -TimeoutSec 3; if ($r.StatusCode -eq 200) { exit 0 } else { exit 1 } } catch { exit 1 }" >nul 2>&1
    if not errorlevel 1 (
        set "SERVIDOR_OK=1"
        goto :eof
    )
    ping -n 2 127.0.0.1 >nul
)
goto :eof

:fin_ok
echo.
echo [OK] SIGEJUB esta disponible en !URL!
echo     Para detener el servidor ejecuta detener.bat
ping -n 3 127.0.0.1 >nul
endlocal
exit /b 0

:sin_php
echo [ERROR] No se encontro PHP 8.2 o superior.
echo.
echo Se busco en: %APP_DIR%\php, PATH, C:\xampp\php, C:\php,
echo              C:\Program Files\php y C:\Program Files (x86)\php
echo.
echo Reinstala SIGEJUB para incluir PHP o instala XAMPP.
powershell -NoProfile -Command "Add-Type -AssemblyName PresentationFramework; [System.Windows.MessageBox]::Show('No se encontro PHP en el equipo.' + [Environment]::NewLine + [Environment]::NewLine + 'Reinstala SIGEJUB para incluir PHP o instala XAMPP.','SIGEJUB','OK','Warning')" >nul 2>&1
pause >nul
exit /b 1

:php_mudo
echo [ERROR] PHP no responde. Puede faltar el runtime VC++.
powershell -NoProfile -Command "Add-Type -AssemblyName PresentationFramework; [System.Windows.MessageBox]::Show('PHP no responde. Instala el runtime Microsoft Visual C++ x64 y vuelve a intentar.','SIGEJUB','OK','Warning')" >nul 2>&1
pause >nul
exit /b 1

:sin_artisan
echo [ERROR] No se encuentra el archivo 'artisan' en %APP_DIR%.
echo         Ejecuta este iniciador desde la carpeta de instalacion de SIGEJUB.
pause >nul
exit /b 1

:sin_vendor
echo [ERROR] Faltan las dependencias de la aplicacion (carpeta vendor).
echo         Reinstala SIGEJUB.
powershell -NoProfile -Command "Add-Type -AssemblyName PresentationFramework; [System.Windows.MessageBox]::Show('Faltan las dependencias de la aplicacion (carpeta vendor).' + [Environment]::NewLine + 'Reinstala SIGEJUB.','SIGEJUB','OK','Warning')" >nul 2>&1
pause >nul
exit /b 1

:sin_env
echo [ERROR] No existe .env ni .env.example en la instalacion.
echo         Reinstala SIGEJUB.
pause >nul
exit /b 1
