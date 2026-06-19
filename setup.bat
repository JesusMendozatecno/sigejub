@echo off
title SIGEJUB - Instalador
cd /d "%~dp0"
echo.
echo  [94mSIGEJUB - Sistema Integral de Gesti[24m[94mn de Jubilaciones[0m
echo  [90m------------------------------------------------[0m
echo.
echo  Usando instalador interactivo...
echo.
timeout /t 1 /nobreak >nul
call install.bat %*
