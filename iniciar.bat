@echo off
cd /d "%~dp0"

for /f "tokens=2 delims==" %%A in ('findstr "^APP_URL=" .env 2^>nul') do set "APP_URL=%%A"
set PORT=%APP_URL:http://localhost:=%
if "%PORT%"=="" set PORT=8000

netstat -an 2>nul | findstr /C:":%PORT% " >nul 2>&1
if errorlevel 1 (
    echo Iniciando servidor SIGEJUB en segundo plano...
    start http://localhost:%PORT%
    powershell -Command "Start-Process php -ArgumentList 'artisan serve --port=%PORT%' -WindowStyle Hidden"
    echo Servidor corriendo en http://localhost:%PORT% (oculto en segundo plano)
) else (
    echo Servidor ya activo. Abriendo http://localhost:%PORT%
    start http://localhost:%PORT%
)
