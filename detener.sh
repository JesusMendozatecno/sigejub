#!/bin/bash
# SIGEJUB - Detener servidor (Linux / macOS)
# ============================================

PORT=""
if [ -f ".env" ]; then
    PORT=$(grep -E '^APP_PORT=' .env 2>/dev/null | head -n1 | cut -d'=' -f2 | tr -d '[:space:]')
    if [ -z "$PORT" ]; then
        PORT=$(grep -E '^APP_URL=' .env 2>/dev/null | head -n1 | sed -E 's/^APP_URL=.*:([0-9]+).*/\1/' | tr -d '[:space:]')
    fi
fi
[ -z "$PORT" ] && PORT=8000

echo "Buscando servidor en puerto $PORT..."

PIDS=""
if command -v lsof &> /dev/null; then
    PIDS=$(lsof -t -iTCP:"$PORT" -sTCP:LISTEN 2>/dev/null)
elif command -v fuser &> /dev/null; then
    PIDS=$(fuser "$PORT"/tcp 2>/dev/null)
fi

if [ -z "$PIDS" ]; then
    echo "No hay ningun servidor escuchando en el puerto $PORT."
    exit 0
fi

for PID in $PIDS; do
    echo "Deteniendo proceso PID $PID ..."
    kill "$PID" 2>/dev/null || kill -9 "$PID" 2>/dev/null
done

echo "Servidor detenido."
