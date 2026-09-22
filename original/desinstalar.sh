#!/bin/bash
# ============================================================
#  SIGEJUB - Desinstalador (Linux / macOS)
#  Detiene el servidor, elimina los accesos directos y borra la
#  carpeta de instalacion (donde vive este script).
#  Uso: ./desinstalar.sh [--keep-db]
#    --keep-db  conserva la carpeta database/ (tu base de datos)
# ============================================================

set -u

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
KEEP_DB=0
case "${1:-}" in
    --keep-db|-k) KEEP_DB=1;;
esac

echo "============================================"
echo " SIGEJUB - Desinstalador"
echo "============================================"
echo "  Carpeta: $SCRIPT_DIR"
[ "$KEEP_DB" = 1 ] && echo "  Modo: conservar base de datos (database/)"

PORT=""
if [ -f "$SCRIPT_DIR/.env" ]; then
    PORT=$(grep -E '^APP_PORT=' "$SCRIPT_DIR/.env" 2>/dev/null | head -n1 | cut -d'=' -f2 | tr -d '[:space:]')
    if [ -z "$PORT" ]; then
        PORT=$(grep -E '^APP_URL=' "$SCRIPT_DIR/.env" 2>/dev/null | head -n1 | sed -E 's/^APP_URL=.*:([0-9]+).*/\1/' | tr -d '[:space:]')
    fi
fi
[ -z "$PORT" ] && PORT=8000

echo "[1/3] Deteniendo servidor (puerto $PORT)..."
PIDS=""
if command -v lsof >/dev/null 2>&1; then
    PIDS=$(lsof -t -iTCP:"$PORT" -sTCP:LISTEN 2>/dev/null)
elif command -v fuser >/dev/null 2>&1; then
    PIDS=$(fuser "$PORT"/tcp 2>/dev/null)
fi
OTHER=$(pgrep -f "php -S 127.0.0.1:$PORT -t $SCRIPT_DIR" 2>/dev/null || true)
for PID in $PIDS $OTHER; do
    kill "$PID" 2>/dev/null || kill -9 "$PID" 2>/dev/null || true
done

echo "[2/3] Eliminando accesos directos..."
rm -f "$HOME/.local/share/applications/SIGEJUB.desktop" \
      "$HOME/.local/share/applications/Desinstalar SIGEJUB.desktop"

echo "[3/3] Borrando carpeta de instalacion..."
cd / || exit 1
if [ "$KEEP_DB" = 1 ]; then
    for item in "$SCRIPT_DIR"/* "$SCRIPT_DIR"/.[!.]*; do
        [ -e "$item" ] || continue
        case "$item" in
            "$SCRIPT_DIR/database") continue;;
        esac
        rm -rf -- "$item"
    done
    echo "Se conservo: $SCRIPT_DIR/database/"
else
    rm -rf -- "$SCRIPT_DIR"
fi

echo ""
echo "SIGEJUB desinstalado."
[ "$KEEP_DB" = 1 ] && echo "Tu base de datos queda en $SCRIPT_DIR/database/"