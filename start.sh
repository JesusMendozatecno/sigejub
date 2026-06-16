#!/bin/bash
# SIGEJUB - Launcher for Linux / macOS
# ============================================

echo "============================================"
echo " SIGEJUB - Sistema de Jubilaciones"
echo "============================================"
echo ""

# Read port from .env (default 8080)
PORT=$(grep -oP '^APP_PORT=\K\d+' .env 2>/dev/null || echo "8080")

# Check PHP
if ! command -v php &> /dev/null; then
    echo "[ERROR] PHP no esta instalado o no esta en PATH."
    exit 1
fi

# Check if server is already running (Apache or artisan)
if ss -tln 2>/dev/null | grep ":$PORT" > /dev/null || netstat -an 2>/dev/null | grep ":$PORT" > /dev/null; then
    echo "[OK] Servidor disponible en http://localhost:$PORT/"
    xdg-open "http://localhost:$PORT/" 2>/dev/null || open "http://localhost:$PORT/" 2>/dev/null
    exit 0
fi

# Create .env if missing
if [ ! -f ".env" ]; then
    echo "[SETUP] Creando archivo .env..."
    cp .env.example .env
    php artisan key:generate --force
    echo "[SETUP] Hecho. Revisa y configura tu base de datos en .env"
fi

# Start server
echo "[INICIANDO] Servidor en http://localhost:$PORT"
xdg-open "http://localhost:$PORT" 2>/dev/null || open "http://localhost:$PORT" 2>/dev/null
php artisan serve --port="$PORT"
