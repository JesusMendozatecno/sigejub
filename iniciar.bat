@echo off
cd /d "%~dp0"

for /f "tokens=2 delims==" %%A in ('findstr "^APP_URL=" .env 2^>nul') do set "APP_URL=%%A"
set PORT=%APP_URL:http://localhost:=%
if "%PORT%"=="" set PORT=8000
set URL=http://localhost:%PORT%

:: Verificar PHP
where php >nul 2>&1
if errorlevel 1 (
    echo [ERROR] PHP no encontrado en el PATH
    echo Ejecuta SIGEJUB-Installer.exe para instalar.
    pause
    exit /b 1
)

:: Matar proceso anterior en el puerto (si existe)
for /f "tokens=5" %%A in ('netstat -ano ^| findstr /C:":%PORT% "') do (
    taskkill /f /pid %%A >nul 2>&1
)
timeout /t 1 /nobreak >nul

:: Iniciar servidor oculto (sin ventanas visibles)
echo Iniciando servidor SIGEJUB...
powershell -WindowStyle Hidden -Command "Start-Process php -ArgumentList 'artisan serve --port=%PORT%' -WindowStyle Hidden"

:: Esperar a que el puerto este escuchando (max 30s)
echo Esperando servidor en %URL% ...
powershell -Command "$p=[int]%PORT%; $ok=$false; for($i=0;$i -lt 30;$i++){ try { $t=New-Object System.Net.Sockets.TcpClient; $t.Connect('127.0.0.1',$p); $t.Close(); $ok=$true; break } catch { Start-Sleep 1 } }; if(!$ok){ write-host 'timeout' }"

start "" "%URL%"
echo Servidor corriendo en %URL%
