@echo off
setlocal EnableExtensions EnableDelayedExpansion
title SIGEJUB - Detener servidor
cd /d "%~dp0"
chcp 65001 >nul 2>&1

set "APP_DIR=%~dp0"
if "%APP_DIR:~-1%"=="\" set "APP_DIR=%APP_DIR:~0,-1%"

rem ==================================================
rem PUERTO desde APP_URL en .env
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
echo Buscando el servidor SIGEJUB en el puerto !PORT! ...

set "ENCONTRADO=0"

rem ==================================================
rem SOLO se detienen procesos php.exe que pertenecen a
rem ESTA instalacion (su linea de comandos contiene la
rem carpeta de la aplicacion). Nunca se cierran otros
rem PHP del equipo (XAMPP, otros proyectos, etc.).
rem ==================================================
for /f "delims=" %%P in ('powershell -NoProfile -Command "Get-CimInstance Win32_Process | Where-Object { $_.Name -eq 'php.exe' -and $_.CommandLine -like '*!APP_DIR!*' } | ForEach-Object { $_.ProcessId }" 2^>nul') do (
    echo Deteniendo el proceso PHP %%P de SIGEJUB ...
    taskkill /F /T /PID %%P >nul 2>&1
    set "ENCONTRADO=1"
)

if "!ENCONTRADO!"=="1" goto :detenido

echo [INFO] No habia ningun servidor SIGEJUB en ejecucion para esta carpeta.
goto :fin

:detenido
echo [OK] Servidor detenido.
rem Verificacion: si el puerto sigue ocupado lo informamos
set "LIBRE=1"
for /f "delims=" %%L in ('powershell -NoProfile -Command "if (Get-NetTCPConnection -LocalPort !PORT! -State Listen -ErrorAction SilentlyContinue) { 'OCUPADO' }" 2^>nul') do set "LIBRE=0"
if "!LIBRE!"=="0" (
    echo [AVISO] El puerto !PORT! sigue ocupado por otro programa distinto a SIGEJUB.
)

:fin
ping -n 3 127.0.0.1 >nul
endlocal
exit /b 0
