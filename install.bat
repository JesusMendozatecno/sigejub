@echo off
setlocal enabledelayedexpansion
title SIGEJUB - Instalador
cd /d "%~dp0"

:: ─── Si viene del acceso directo, solo iniciar ─────
if /i "%1"=="--start" goto :quick_start

:: ─── Logotipo ───────────────────────────────────
cls
echo.
echo      [94m  ██████  ██  ██████  ███████  ██   ██  ██    ██  ██████  [0m
echo      [94m ██       ██ ██       ██       ██   ██  ██    ██  ██   ██ [0m
echo      [94m ██   ███ ██ ██   ███ █████    ███████  ██    ██  ██████  [0m
echo      [94m ██    ██ ██ ██    ██ ██       ██   ██  ██    ██  ██   ██ [0m
echo      [94m  ██████  ██  ██████  ███████  ██   ██   ██████   ██████  [0m
echo.
echo         [97mSistema Integral de Gesti[24m[97mn de Jubilaciones[0m
echo         [90mUniversidad Politecnica Territorial de Yaracuy[0m
echo.
echo         [93m===  INSTALADOR AUTOMATICO  ===[0m
echo.
echo   [90mEste programa configurara todos los componentes necesarios[0m
echo   [90mpara poner en marcha el sistema en este equipo.[0m
echo.
echo   [90mRequisitos: PHP 8.2+, Composer, MySQL/MariaDB[0m
echo.
echo   [90mPresiona cualquier tecla para comenzar...[0m
pause >nul
cls

set MISSING_EXT=0

:: ─── 1. Verificar PHP ───────────────────────────────────
echo [93m[1/8] Verificando requisitos...[0m
where php >nul 2>&1
if %errorlevel% neq 0 (
    echo [91m[ERROR] PHP no encontrado. Debes instalarlo y agregarlo al PATH.[0m
    echo         Descarga: https://windows.php.net/download/
    pause
    exit /b 1
)
for /f "delims=" %%I in ('php -r "echo PHP_MAJOR_VERSION;" 2^>nul') do set PHP_MAJOR=%%I
if "%PHP_MAJOR%"=="" set PHP_MAJOR=0
if %PHP_MAJOR% LSS 8 (
    echo [91m[ERROR] Se requiere PHP 8.2 o superior. Version detectada: %PHP_MAJOR%.x[0m
    pause
    exit /b 1
)
echo [92m[OK] PHP %PHP_MAJOR%.x encontrado[0m

where composer >nul 2>&1
if %errorlevel% neq 0 (
    echo [91m[ERROR] Composer no encontrado.[0m
    echo         Descarga: https://getcomposer.org/download/
    pause
    exit /b 1
)
echo [92m[OK] Composer encontrado[0m

:: Extensiones PHP
call :check_ext pdo_mysql
call :check_ext mbstring
call :check_ext xml
call :check_ext zip
call :check_ext fileinfo
call :check_ext tokenizer
call :check_ext json
call :check_ext curl

if %MISSING_EXT% neq 0 (
    echo.
    echo [91m[ADVERTENCIA] Faltan extensiones PHP. El sistema podria no funcionar.[0m
    echo [90mHabilitalas en php.ini (extension=...).[0m
)
echo.

:: ─── 2. Configurar .env ────────────────────────────────
echo [93m[2/8] Configurando variables de entorno...[0m
if not exist ".env" (
    if exist ".env.example" (
        copy .env.example .env >nul
        echo [92m[OK] Archivo .env creado desde .env.example[0m
    ) else (
        echo [91m[ERROR] No se encuentra .env.example[0m
        pause
        exit /b 1
    )
) else (
    echo [92m[OK] Archivo .env ya existe[0m
)

:: ─── 3. Instalar dependencias ──────────────────────────
echo.
echo [93m[3/8] Instalando dependencias de Composer...[0m
call composer install --no-interaction --no-ansi 2>&1 | findstr /V "^$" | findstr /V "^Installing\|^Discovered\|^Package\|^Generating"
echo [92m[OK] Dependencias instaladas[0m

:: ─── 4. Generar APP_KEY ──────────────────────────────
echo.
echo [93m[4/8] Generando clave de aplicacion...[0m
php artisan key:generate --force --no-ansi >nul
echo [92m[OK] APP_KEY generada[0m

:: ─── 5. Configurar base de datos ─────────────────────
echo.
echo [93m[5/8] Configuracion de base de datos...[0m
echo [90mDeja en blanco para usar valores por defecto (XAMPP).[0m
echo.

set /p "DB_HOST=Host DB [127.0.0.1]: "
if "%DB_HOST%"=="" set DB_HOST=127.0.0.1

set /p "DB_PORT=Puerto DB [3306]: "
if "%DB_PORT%"=="" set DB_PORT=3306

set /p "DB_NAME=Nombre BD [bd-sigejub]: "
if "%DB_NAME%"=="" set DB_NAME=bd-sigejub

set /p "DB_USER=Usuario DB [root]: "
if "%DB_USER%"=="" set DB_USER=root

set /p "DB_PASS=Contrasena DB [vacia]: "

powershell -Command "$c = (Get-Content .env); $c = $c -replace 'DB_HOST=.*', 'DB_HOST=%DB_HOST%' -replace 'DB_PORT=.*', 'DB_PORT=%DB_PORT%' -replace 'DB_DATABASE=.*', 'DB_DATABASE=%DB_NAME%' -replace 'DB_USERNAME=.*', 'DB_USERNAME=%DB_USER%' -replace 'DB_PASSWORD=.*', 'DB_PASSWORD=%DB_PASS%'; Set-Content .env -Value $c"
echo [92m[OK] Configuracion guardada en .env[0m

:: ─── 6. Migraciones ────────────────────────────────────
echo.
echo [93m[6/8] Migraciones de base de datos...[0m
echo [90mAsegurate de que la BD '%DB_NAME%' exista en MySQL.[0m
echo.
set /p "RUN_MIGRATE=Ejecutar migraciones ahora? (S/N): "
if /i "!RUN_MIGRATE!"=="S" (
    echo Ejecutando...
    php artisan migrate --force --no-ansi 2>&1
    if !errorlevel! neq 0 (
        echo [91m[ERROR] Migraciones fallaron. Revisa credenciales en .env[0m
        echo         Ejecuta manualmente: php artisan migrate
    ) else (
        echo [92m[OK] Migraciones ejecutadas[0m
    )
) else (
    echo [90m[INFO] Migraciones omitidas. Ejecuta: php artisan migrate[0m
)

:: ─── 7. Limpiar cache + storage link ─────────────────
echo.
echo [93m[7/8] Limpiando cache y generando enlaces...[0m
php artisan optimize:clear --no-ansi >nul 2>&1
if exist "public\storage" rmdir "public\storage" 2>nul
php artisan storage:link --no-ansi >nul 2>&1
echo [92m[OK] Cache y storage link listo[0m

:: ─── 8. Detectar puerto libre ──────────────────────────
echo.
echo [93m[8/8] Detectando puerto disponible...[0m

set PORT=8000
:check_port
netstat -an 2>nul | findstr ":%PORT% " >nul
if !errorlevel! equ 0 (
    set /a PORT+=1
    if !PORT! gtr 9000 (
        echo [91m[ERROR] No se encontro puerto libre entre 8000-9000[0m
        pause
        exit /b 1
    )
    goto check_port
)
echo [92m[OK] Puerto %PORT% disponible[0m

:: Guardar puerto en .env
powershell -Command "$c = (Get-Content .env); $c = $c -replace 'APP_URL=.*', 'APP_URL=http://localhost:%PORT%'; Set-Content .env -Value $c"

:: ─── 9. Acceso directo en escritorio ──────────────────
echo.
echo [93m[OPCIONAL] Acceso directo en el escritorio[0m
set /p "SHORTCUT=Crear acceso directo para iniciar el sistema? (S/N): "
if /i "!SHORTCUT!"=="S" (
    powershell -Command ^
        "$WS = New-Object -ComObject WScript.Shell; " ^
        "$SC = $WS.CreateShortcut([Environment]::GetFolderPath('Desktop') + '\SIGEJUB.lnk'); " ^
        "$SC.TargetPath = '%~f0'; " ^
        "$SC.Arguments = '--start'; " ^
        "$SC.WorkingDirectory = '%~dp0'; " ^
        "$SC.Description = 'SIGEJUB - Sistema de Gestion de Jubilaciones'; " ^
        "$SC.IconLocation = '%~dp0public\img\imagen_2026-05-19_065531142.ico, 0'; " ^
        "$SC.Save()"
    echo [92m[OK] Acceso directo creado en el escritorio[0m
)

:: ─── 10. Iniciar servidor ──────────────────────────────
echo.
echo ============================================
echo  [92m  INSTALACION COMPLETADA [0m
echo ============================================
echo.
echo  [97mPuerto:[0m %PORT%
echo  [97mURL:   [0m http://localhost:%PORT%
echo.
echo  [90mIniciando servidor...[0m
echo  [90m(Ctrl+C para detener)[0m
echo.
timeout /t 3 /nobreak >nul
start http://localhost:%PORT%
php artisan serve --port=%PORT%
goto :eof

:: ═══════════════════════════════════════════════
::  Inicio rapido desde acceso directo
:: ═══════════════════════════════════════════════
:quick_start
cls
echo [92m
   ╔══════════════════════════════════════╗
   ║        SIGEJUB - INICIANDO...       ║
   ╚══════════════════════════════════════╝
[0m

where php >nul 2>&1
if %errorlevel% neq 0 (
    echo [91m[ERROR] PHP no encontrado en el PATH[0m
    pause
    exit /b 1
)

:: Leer puerto guardado en .env
for /f "tokens=2 delims==" %%A in ('findstr "APP_URL=" .env 2^>nul') do set "RAW=%%A"
set PORT=!RAW:http://localhost:=!
if "%PORT%"=="" set PORT=8000

:: Verificar si el puerto sigue libre
netstat -an 2>nul | findstr ":%PORT% " >nul
if !errorlevel! equ 0 (
    echo [90m[INFO] Puerto %PORT% ocupado, buscando otro...[0m
    :find_alt
    set /a PORT+=1
    netstat -an 2>nul | findstr ":%PORT% " >nul
    if !errorlevel! equ 0 (
        if !PORT! lss 9000 goto find_alt
    )
    echo [92m[OK] Usando puerto alternativo: %PORT%[0m
)

echo [92m[OK] Abriendo http://localhost:%PORT%[0m
echo [90mPresiona Ctrl+C para detener el servidor[0m
echo.
start http://localhost:%PORT%
php artisan serve --port=%PORT%
goto :eof

:: ═══════════════════════════════════════════════
::  Verificar extension PHP
:: ═══════════════════════════════════════════════
:check_ext
php -m 2>nul | findstr /i "%~1" >nul
if %errorlevel% equ 0 (
    echo [92m[OK] Extension: %~1[0m
) else (
    echo [91m[Falta] Extension: %~1[0m
    set MISSING_EXT=1
)
exit /b
