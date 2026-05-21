#!/bin/bash
# SIGEJUB - Configuracion Inicial (Linux / macOS)
# ============================================

echo "============================================"
echo " SIGEJUB - Configuracion Inicial"
echo "============================================"
echo ""

# Check PHP
if ! command -v php &> /dev/null; then
    echo "[ERROR] PHP no esta instalado o no esta en PATH."
    exit 1
fi

# Create .env
if [ ! -f ".env" ]; then
    echo "[1/5] Creando archivo .env..."
    cp .env.example .env
    echo "[OK]"
else
    echo "[1/5] .env ya existe"
fi

# Generate APP_KEY
echo "[2/5] Generando APP_KEY..."
php artisan key:generate --force
echo "[OK]"

# Migrate
echo "[3/5] Ejecutando migraciones..."
php artisan migrate --force
echo "[OK]"

# Seed
echo "[4/5] Ejecutando seeders..."
php artisan db:seed --force
echo "[OK]"

# Cache
echo "[5/5] Limpiando cache..."
php artisan optimize:clear
echo "[OK]"

echo ""
echo "============================================"
echo " CONFIGURACION COMPLETADA"
echo "============================================"
echo " Ejecuta: bash start.sh para iniciar"
echo "============================================"
