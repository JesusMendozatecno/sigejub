@echo off
cd /d "%~dp0"

:: Leer APP_URL del .env
for /f "tokens=2 delims==" %%A in ('findstr "^APP_URL=" .env 2^>nul') do set "APP_URL=%%A"
set PORT=%APP_URL:http://localhost:=%
if "%PORT%"=="" set PORT=8000

:: Verificar si el puerto esta ocupado
netstat -an 2>nul | findstr ":%PORT% " >nul
if errorlevel 1 (
    :: Puerto libre, iniciar servidor
    echo Iniciando SIGEJUB en http://localhost:%PORT%
    start http://localhost:%PORT%
    php artisan serve --port=%PORT%
) else (
    :: Puerto ocupado, abrir directamente
    echo Puerto %PORT% ya esta en uso.
    echo Abriendo http://localhost:%PORT%
    start http://localhost:%PORT%
)
