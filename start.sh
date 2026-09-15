#!/bin/bash
# ============================================================
#  SIGEJUB - Iniciar servidor (Linux / macOS)
#  Usa el servidor integrado de PHP (php -S) igual que en Windows.
#  Lee APP_PORT / APP_URL del .env de la carpeta donde vive este script.
# ============================================================

set -u

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR" || exit 1

# PHP
if ! command -v php >/dev/null 2>&1; then
    echo "[ERROR] PHP no esta instalado o no esta en PATH."
    exit 1
fi

# Puerto: APP_PORT, luego APP_URL, default 8000
PORT=""
if [ -f ".env" ]; then
    PORT=$(grep -E '^APP_PORT=' .env 2>/dev/null | head -n1 | cut -d'=' -f2 | tr -d '[:space:]')
    if [ -z "$PORT" ]; then
        PORT=$(grep -E '^APP_URL=' .env 2>/dev/null | head -n1 | sed -E 's/^APP_URL=.*:([0-9]+).*/\1/' | tr -d '[:space:]')
    fi
fi
[ -z "$PORT" ] && PORT=8000

URL="http://localhost:$PORT/"

echo "============================================"
echo " SIGEJUB - Sistema de Jubilaciones"
echo "============================================"

# Si ya hay un servidor escuchando, solo abrir el navegador
if ss -tln 2>/dev/null | grep ":$PORT " >/dev/null || netstat -an 2>/dev/null | grep ":$PORT " >/dev/null; then
    echo "[OK] Servidor disponible en $URL"
    xdg-open "$URL" 2>/dev/null || open "$URL" 2>/dev/null || true
    exit 0
fi

if [ ! -f ".env" ]; then
    echo "[ERROR] No existe .env. Ejecuta primero: ./instalar.sh"
    exit 1
fi

mkdir -p "$SCRIPT_DIR/storage/logs"

echo "[INICIANDO] Servidor en $URL"
nohup php -S 127.0.0.1:"$PORT" -t "$SCRIPT_DIR/public" > "$SCRIPT_DIR/storage/logs/php-server.log" 2>&1 &
SERVER_PID=$!

# Pequena espera para dar tiempo a que levante y abrir el navegador
sleep 2
if kill -0 "$SERVER_PID" 2>/dev/null; then
    echo "[OK] Servidor PHP iniciado (PID $SERVER_PID)"
else
    echo "[ERROR] El servidor no arranco. Revisa: $SCRIPT_DIR/storage/logs/php-server.log"
    exit 1
fi

xdg-open "$URL" 2>/dev/null || open "$URL" 2>/dev/null || true

echo "Para detener: ./detener.sh"