#!/bin/bash
# SIGEJUB - Launcher for Linux / macOS
# ============================================

echo "============================================"
echo " SIGEJUB - Sistema de Jubilaciones"
echo "============================================"
echo ""

# Check PHP
if ! command -v php &> /dev/null; then
    echo "[ERROR] PHP no esta instalado o no esta en PATH."
    exit 1
fi

# Check if server is already running
if netstat -an 2>/dev/null | grep ":8000" > /dev/null; then
    echo "[OK] El servidor ya esta corriendo en http://localhost:8000"
    xdg-open http://localhost:8000 2>/dev/null || open http://localhost:8000 2>/dev/null
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
echo "[INICIANDO] Servidor en http://localhost:8000"
xdg-open http://localhost:8000 2>/dev/null || open http://localhost:8000 2>/dev/null
php artisan serve --port=8000
