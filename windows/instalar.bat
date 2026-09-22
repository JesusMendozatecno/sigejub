@echo off
setlocal enabledelayedexpansion
title SIGEJUB - Instalador (Windows)
cd /d "%~dp0"
chcp 65001 >nul 2>&1

echo ============================================
echo  SIGEJUB - Instalador para Windows
echo  (copia la variante segun el motor de BD)
echo ============================================
echo.

:: ============================================================
:: 1) ENCONTRAR PHP (PATH, XAMPP, rutas comunes)
:: ============================================================
set "PHP_PATH="
where php >nul 2>&1
if not errorlevel 1 (
    for /f "delims=" %%i in ('where php') do (
        if not defined PHP_PATH set "PHP_PATH=%%i"
    )
)
if not defined PHP_PATH (
    for %%P in (C D E F) do (
        if exist "%%P:\xampp\php\php.exe" set "PHP_PATH=%%P:\xampp\php\php.exe"
        if defined PHP_PATH goto :php_found
    )
)
if not defined PHP_PATH (
    for %%P in (C D E F) do (
        if exist "%%P:\php\php.exe" set "PHP_PATH=%%P:\php\php.exe"
        if defined PHP_PATH goto :php_found
    )
)
if not defined PHP_PATH (
    for %%P in (C D E F) do (
        if exist "%%P:\Program Files\php\php.exe" set "PHP_PATH=%%P:\Program Files\php\php.exe"
        if defined PHP_PATH goto :php_found
    )
)

:php_found
if not defined PHP_PATH (
    echo [ERROR] PHP no fue encontrado.
    echo.
    echo Instala PHP 8.2+ (o XAMPP) y agregalo al PATH.
    echo.
    pause
    exit /b 1
)
echo [OK] PHP: %PHP_PATH%
"%PHP_PATH%" -r "echo '  version '.PHP_VERSION.PHP_EOL;"
echo.

:: ============================================================
:: 2) ELEGIR MOTOR (define la variante a copiar)
:: ============================================================
echo Motor de base de datos:
echo   1) MySQL / MariaDB   (variante: windows\mysql)
echo   2) PostgreSQL        (variante: windows\postgresql)
set "DBOPT="
set /p DBOPT=Opcion [1]: 
if "%DBOPT%"=="" set DBOPT=1

if "%DBOPT%"=="2" (
    set "DB_ENGINE=pgsql"
    set "VARIANT=postgresql"
    set "EXT_NAME=pdo_pgsql"
    set "DEF_PORT=5432"
    set "DEF_USER=postgres"
    set "DEF_DB=sigejub"
) else (
    set "DB_ENGINE=mysql"
    set "VARIANT=mysql"
    set "EXT_NAME=pdo_mysql"
    set "DEF_PORT=3306"
    set "DEF_USER=root"
    set "DEF_DB=bd-sigejub"
)

set "SRC=%~dp0!VARIANT!"
if not exist "!SRC!\artisan" (
    echo [ERROR] Falta la variante: !SRC!
    echo         El instalador debe vivir en la carpeta "windows" del repositorio.
    pause
    exit /b 1
)
echo [OK] Variante a copiar: !VARIANT!

:: Verificar la extension PHP del motor elegido
"%PHP_PATH%" -m 2>nul | findstr /i /c:"!EXT_NAME!" >nul
if errorlevel 1 (
    echo.
    echo [ERROR] Falta la extension PHP "!EXT_NAME!" para !DB_ENGINE!.
    if "!DB_ENGINE!"=="pgsql" (
        echo         Edita el php.ini de PHP ^(ej: C:\xampp\php\php.ini^) y activa:
        echo             extension=pdo_pgsql
        echo             extension=pgsql
        echo         Reinicia PHP/Apache y vuelve a ejecutar instalar.bat
    ) else (
        echo         Edita el php.ini y activa: extension=pdo_mysql
    )
    echo.
    pause
    exit /b 1
)
echo [OK] Extension !EXT_NAME! disponible
echo.

:: ============================================================
:: 3) CARPETA DE INSTALACION
:: ============================================================
set "DEF_DEST=%USERPROFILE%\SIGEJUB"
set "DEST="
set /p DEST=Carpeta de instalacion [!DEF_DEST!]: 
if "!DEST!"=="" set "DEST=!DEF_DEST!"

:: Si es ruta relativa, se interpreta dentro de %USERPROFILE%
echo !DEST! | findstr /r /c:"^[A-Za-z]:" >nul 2>&1
if errorlevel 1 (
    if not "!DEST!"=="" set "DEST=%USERPROFILE%\!DEST!"
)

if "!DEST!"=="%~dp0" (
    echo [ERROR] La carpeta de instalacion no puede ser la carpeta del instalador.
    pause
    exit /b 1
)

echo.
set "REIN=1"
if exist "!DEST!\artisan" (
    echo [AVISO] La carpeta ya contiene una instalacion de SIGEJUB.
    set /p REIN=Reinstalar conservando .env y datos? [S/N]: 
)
if /i not "!REIN!"=="S" if exist "!DEST!\artisan" (
    echo Instalacion cancelada.
    pause
    exit /b 1
)

:: ============================================================
:: 4) COPIAR LA VARIANTE
:: ============================================================
if not exist "!DEST!" mkdir "!DEST!"
echo [1/7] Copiando variante a !DEST! ...
robocopy "!SRC!" "!DEST!" /E /XD .git vendor node_modules windows linux installer-src paquetes compont tests /XF .env *.log *.sqlite /NFL /NDL /NJH /NJS /NP >nul
set RC=%ERRORLEVEL%
if %RC% GEQ 8 (
    echo [ERROR] Fallo la copia de archivos. Codigo robocopy: %RC%
    pause
    exit /b 1
)
if not exist "!DEST!\artisan" (
    echo [ERROR] La copia no dejo el archivo artisan.
    pause
    exit /b 1
)
echo [OK] Archivos copiados

:: ============================================================
:: 5) .ENV (crear y configurar BD)
:: ============================================================
if not exist "!DEST!\.env" (
    if exist "!DEST!\.env.example" (
        copy /y "!DEST!\.env.example" "!DEST!\.env" >nul
        echo [OK] .env creado desde .env.example
    ) else (
        echo [ERROR] Falta .env.example en la variante.
        pause
        exit /b 1
    )
)

echo.
echo Datos de la base de datos (!DB_ENGINE!):
set "PORT_IN="
set /p PORT_IN=Puerto [!DEF_PORT!]: 
if "!PORT_IN!"=="" set "PORT_IN=!DEF_PORT!"
set "DB_IN="
set /p DB_IN=Base de datos [!DEF_DB!]: 
if "!DB_IN!"=="" set "DB_IN=!DEF_DB!"
set "USER_IN="
set /p USER_IN=Usuario [!DEF_USER!]: 
if "!USER_IN!"=="" set "USER_IN=!DEF_USER!"
set "PASS_IN="
set /p PASS_IN=Contrasena (^Enter si no hay^): 

echo.
set "APP_PORT_IN=8000"
set /p APP_PORT_IN=Puerto del servidor [8000]: 
if "!APP_PORT_IN!"=="" set "APP_PORT_IN=8000"

echo [2/7] Escribiendo .env y verificando la base de datos...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0configurar-env.ps1" -EnvFile "!DEST!\.env" -AppPort "!APP_PORT_IN!" -Connection !DB_ENGINE! -DbHost 127.0.0.1 -DbPort "!PORT_IN!" -Database "!DB_IN!" -Username "!USER_IN!" -Password "!PASS_IN!"
if errorlevel 1 (
    echo [ERROR] No se pudo escribir el archivo .env
    pause
    exit /b 1
)

:: Conexion real a la BD + creacion de la base si no existe
set "SIGEJUB_DB_PASS=!PASS_IN!"
"%PHP_PATH%" -f "%~dp0verificar-bd.php" -- !DB_ENGINE! 127.0.0.1 !PORT_IN! !DB_IN! !USER_IN! >"%TEMP%\sigejub-bd.log" 2>&1
if errorlevel 1 (
    echo [ERROR] No se pudo conectar/crear la base de datos:
    echo --------------------------------------------------------
    type "%TEMP%\sigejub-bd.log"
    echo --------------------------------------------------------
    echo Verifica que el servidor !DB_ENGINE! este corriendo en 127.0.0.1:!PORT_IN!
    echo y que el usuario/contrasena sean correctos. Luego vuelve a ejecutar el instalador.
    pause
    exit /b 1
)
type "%TEMP%\sigejub-bd.log"

:: ============================================================
:: 6) DEPENDENCIAS (composer) Y APP_KEY
:: ============================================================
cd /d "!DEST!"

set "COMPOSER_CMD="
where composer >nul 2>&1
if not errorlevel 1 set "COMPOSER_CMD=composer"

if not defined COMPOSER_CMD (
    echo [3/7] Composer no esta en el PATH. Descargando composer.phar...
    powershell -NoProfile -ExecutionPolicy Bypass -Command "try { [Net.ServicePointManager]::SecurityProtocol=[Net.SecurityProtocolType]::Tls12; Invoke-WebRequest -Uri 'https://getcomposer.org/composer.phar' -OutFile \"$env:TEMP\composer.phar\" } catch { exit 1 }" >nul 2>&1
    if exist "%TEMP%\composer.phar" (
        set "COMPOSER_CMD=php "%TEMP%\composer.phar""
        echo [OK] composer.phar descargado
    )
)

if not defined COMPOSER_CMD (
    echo [ERROR] No hay Composer disponible y no se pudo descargar.
    echo         Instala Composer: https://getcomposer.org/Download/
    echo         Luego ejecuta, dentro de !DEST!:
    echo             composer install
    pause
    exit /b 1
)

echo [3/7] Ejecutando composer install (puede tardar)...
!COMPOSER_CMD! install --no-ansi --no-interaction >"%TEMP%\sigejub-composer.log" 2>&1
if errorlevel 1 (
    echo [ERROR] composer install fallo. Ultimas lineas del log:
    echo --------------------------------------------------------
    powershell -NoProfile -Command "Get-Content -Tail 40 -LiteralPath $env:TEMP\sigejub-composer.log" 2>nul
    echo --------------------------------------------------------
    echo Log completo: %TEMP%\sigejub-composer.log
    pause
    exit /b 1
)
if not exist "vendor\autoload.php" (
    echo [ERROR] composer install no genero vendor\autoload.php
    pause
    exit /b 1
)
echo [OK] Dependencias instaladas

echo [4/7] Generando APP_KEY...
"%PHP_PATH%" artisan key:generate --force >nul 2>&1
if errorlevel 1 (
    echo [AVISO] No se pudo generar la APP_KEY (se reintenta al arrancar).
)

:: ============================================================
:: 7) MIGRACIONES
:: ============================================================
echo [5/7] Ejecutando migraciones...
"%PHP_PATH%" artisan migrate --force --no-ansi >"%TEMP%\sigejub-migrate.log" 2>&1
if errorlevel 1 (
    echo [ERROR] Las migraciones fallaron. Ultimas lineas del log:
    echo --------------------------------------------------------
    powershell -NoProfile -Command "Get-Content -Tail 25 -LiteralPath $env:TEMP\sigejub-migrate.log" 2>nul
    echo --------------------------------------------------------
    echo Revisa que el servidor de BD este activo y que .env tenga las credenciales
    echo correctas. Cuando lo arregles, vuelve a ejecutar el instalador.
    pause
    exit /b 1
)
echo [OK] Migraciones aplicadas

echo [6/7] Ejecutando seeders...
"%PHP_PATH%" artisan db:seed --force --no-ansi >"%TEMP%\sigejub-seed.log" 2>&1
if errorlevel 1 (
    echo [ERROR] Los seeders fallaron. Ultimas lineas del log:
    echo --------------------------------------------------------
    powershell -NoProfile -Command "Get-Content -Tail 25 -LiteralPath $env:TEMP\sigejub-seed.log" 2>nul
    echo --------------------------------------------------------
    echo Luego ejecuta: php artisan db:seed --force
    pause
    exit /b 1
)
echo [OK] Seeders ejecutados

echo [7/7] Limpiando cache...
"%PHP_PATH%" artisan optimize:clear >nul 2>&1
if exist "public\storage" del /q "public\storage" >nul 2>&1
"%PHP_PATH%" artisan storage:link >nul 2>&1

echo.
echo ============================================
echo  INSTALACION COMPLETADA
echo ============================================
echo  Carpeta:   !DEST!
echo  Motor:     !DB_ENGINE!
echo  Servidor:  http://localhost:!APP_PORT_IN!/
echo  Iniciar:   !DEST!\start.bat
echo  Detener:   !DEST!\detener.bat
echo ============================================
echo.

choice /c SN /n /m "Iniciar SIGEJUB ahora? [S/N]: "
if errorlevel 2 goto :fin
call "!DEST!\start.bat"
goto :eof

:fin
echo Listo. Cuando quieras ejecuta: !DEST!\start.bat
pause
