#!/bin/bash
# ============================================================
#  SIGEJUB - Detener servidor (Linux / macOS)
#  Mata los procesos que escuchan en el puerto de SIGEJUB
#  (usa lsof/fuser) y cualquier php -S de esta carpeta.
# ============================================================

set -u

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

PORT=""
if [ -f "$SCRIPT_DIR/.env" ]; then
    PORT=$(grep -E '^APP_PORT=' "$SCRIPT_DIR/.env" 2>/dev/null | head -n1 | cut -d'=' -f2 | tr -d '[:space:]')
    if [ -z "$PORT" ]; then
        PORT=$(grep -E '^APP_URL=' "$SCRIPT_DIR/.env" 2>/dev/null | head -n1 | sed -E 's/^APP_URL=.*:([0-9]+).*/\1/' | tr -d '[:space:]')
    fi
fi
[ -z "$PORT" ] && PORT=8000

echo "Buscando servidor SIGEJUB en puerto $PORT..."

PIDS=""
if command -v lsof >/dev/null 2>&1; then
    PIDS=$(lsof -t -iTCP:"$PORT" -sTCP:LISTEN 2>/dev/null)
elif command -v fuser >/dev/null 2>&1; then
    PIDS=$(fuser "$PORT"/tcp 2>/dev/null)
fi

# Refuerzo: cualquier php -S lanzado desde esta carpeta
OTHER=$(pgrep -f "php -S 127.0.0.1:$PORT -t $SCRIPT_DIR" 2>/dev/null || true)

if [ -z "$PIDS" ] && [ -z "$OTHER" ]; then
    echo "No hay ningun servidor escuchando en el puerto $PORT."
    exit 0
fi

for PID in $PIDS $OTHER; do
    echo "Deteniendo proceso PID $PID ..."
    kill "$PID" 2>/dev/null || kill -9 "$PID" 2>/dev/null || true
done

sleep 1
echo "Servidor detenido."