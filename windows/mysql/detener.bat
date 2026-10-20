@echo off
setlocal enabledelayedexpansion
title SIGEJUB - Detener servidor
cd /d "%~dp0"

rem =============================================
rem LEER PUERTO desde APP_URL en .env
rem =============================================
set PORT=8000
set "APP_URL="
if exist ".env" (
    for /f "tokens=2 delims==" %%A in ('findstr /B /C:"APP_URL=" .env 2^>nul') do set "APP_URL=%%A"
)
if defined APP_URL set "PORT=%APP_URL:http://localhost:=%"
if defined APP_URL set "PORT=%PORT:http://127.0.0.1:=%"
if "%PORT%"=="" set PORT=8000

echo Buscando servidor SIGEJUB en el puerto %PORT%...
set "ENCONTRADO=0"

rem Recorre los PID que escuchan en el puerto; solo mata los que sean php.exe.
for /f "tokens=5" %%A in ('netstat -ano 2^>nul ^| findstr /R /C:":%PORT% .*LISTENING"') do (
    set "PID=%%A"
    set "IMAGEN="
    for /f "tokens=1" %%B in ('tasklist /FI "PID eq %%A" /NH /FO CSV 2^>nul') do set "IMAGEN=%%~B"
    if /I "!IMAGEN!"=="php.exe" (
        echo Deteniendo proceso php.exe PID %%A ...
        taskkill /F /T /PID %%A >nul 2>&1
        set "ENCONTRADO=1"
    )
)

rem Respaldo: si no se detecto por puerto, cierra cualquier php.exe del sistema.
if "!ENCONTRADO!"=="0" (
    tasklist /FI "IMAGENAME eq php.exe" 2>nul | findstr /I "php.exe" >nul 2>&1
    if !ERRORLEVEL! equ 0 (
        echo Cerrando procesos php.exe restantes...
        taskkill /IM php.exe /F /T >nul 2>&1
        set "ENCONTRADO=1"
    )
)

if "!ENCONTRADO!"=="1" (
    echo [OK] Servidor detenido.
) else (
    echo [INFO] No habia ningun servidor SIGEJUB en ejecucion.
)

ping -n 3 127.0.0.1 >nul
endlocal
