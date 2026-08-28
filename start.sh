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

# Read port: APP_PORT, luego APP_URL, default 8000 (igual que start.bat en Windows)
PORT=""
if [ -f ".env" ]; then
    PORT=$(grep -E '^APP_PORT=' .env 2>/dev/null | head -n1 | cut -d'=' -f2 | tr -d '[:space:]')
    if [ -z "$PORT" ]; then
        PORT=$(grep -E '^APP_URL=' .env 2>/dev/null | head -n1 | sed -E 's/^APP_URL=.*:([0-9]+).*/\1/' | tr -d '[:space:]')
    fi
fi
[ -z "$PORT" ] && PORT=8000

# Check if server is already running (Apache or artisan)
if ss -tln 2>/dev/null | grep ":$PORT " > /dev/null || netstat -an 2>/dev/null | grep ":$PORT " > /dev/null; then
    echo "[OK] Servidor disponible en http://localhost:$PORT/"
    xdg-open "http://localhost:$PORT/" 2>/dev/null || open "http://localhost:$PORT/" 2>/dev/null
    exit 0
fi

# Create .env if missing
if [ ! -f ".env" ]; then
    echo "[SETUP] Creando archivo .env..."
    cp .env.example .env
    php artisan key:generate --force
    echo "[SETUP] Hecho. Ejecuta ./setup.sh para configurar la base de datos."
fi

# Start server
echo "[INICIANDO] Servidor en http://localhost:$PORT"
xdg-open "http://localhost:$PORT" 2>/dev/null || open "http://localhost:$PORT" 2>/dev/null &
php artisan serve --port="$PORT"
