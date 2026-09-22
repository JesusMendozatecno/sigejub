@echo off
cd /d "%~dp0"
for /f "tokens=2 delims==" %%A in ('findstr "^APP_URL=" .env 2^>nul') do set "APP_URL=%%A"
set PORT=%APP_URL:http://localhost:=%
if "%PORT%"=="" set PORT=8000

echo Buscando servidor en puerto %PORT%...
for /f "tokens=5" %%A in ('netstat -ano ^| findstr /C:":%PORT% "') do (
    echo Deteniendo proceso PID %%A ...
    taskkill /f /pid %%A >nul 2>&1
)
echo Servidor detenido.
timeout /t 2 >nul
