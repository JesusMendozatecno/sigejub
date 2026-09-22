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
    echo "[1/6] Creando archivo .env..."
    cp .env.example .env
    echo "[OK]"
else
    echo "[1/6] .env ya existe"
fi

# ─── Seleccion de gestor de base de datos ────
echo ""
echo "[2/6] Gestor de base de datos:"
echo "  1) MySQL / MariaDB (phpMyAdmin)"
echo "  2) PostgreSQL"
read -r -p "Opcion [1]: " DB_OPT
DB_OPT="${DB_OPT:-1}"

if [ "$DB_OPT" = "2" ]; then
    DB_CONNECTION=pgsql
    DEF_PORT=5432
    DEF_USER=postgres
    ENGINE_NAME="PostgreSQL"
    EXT_NAME="pdo_pgsql"
else
    DB_CONNECTION=mysql
    DEF_PORT=3306
    DEF_USER=root
    ENGINE_NAME="MySQL/MariaDB"
    EXT_NAME="pdo_mysql"
fi

if php -m | grep -qi "^$EXT_NAME$"; then
    echo "[OK] Extension PHP $EXT_NAME disponible"
else
    echo "[ERROR] Falta la extension PHP '$EXT_NAME' para $ENGINE_NAME."
    echo "        Instalala (ej: sudo apt install php-pgsql / php-mysql) y vuelve a ejecutar."
    exit 1
fi

read -r -p "Host [127.0.0.1]: " DB_HOST_IN
read -r -p "Puerto [$DEF_PORT]: " DB_PORT_IN
read -r -p "Base de datos [bd-sigejub]: " DB_DB_IN
read -r -p "Usuario [$DEF_USER]: " DB_USER_IN
read -r -s -p "Contrasena: " DB_PASS_IN
echo ""

DB_HOST_IN="${DB_HOST_IN:-127.0.0.1}"
DB_PORT_IN="${DB_PORT_IN:-$DEF_PORT}"
DB_DB_IN="${DB_DB_IN:-bd-sigejub}"
DB_USER_IN="${DB_USER_IN:-$DEF_USER}"

set_env_var() {
    local key="$1" val="$2" file=".env"
    if grep -q "^${key}=" "$file"; then
        sed -i.bak "s|^${key}=.*|${key}=${val}|" "$file" && rm -f "${file}.bak"
    else
        echo "${key}=${val}" >> "$file"
    fi
}

set_env_var DB_CONNECTION "$DB_CONNECTION"
set_env_var DB_HOST "$DB_HOST_IN"
set_env_var DB_PORT "$DB_PORT_IN"
set_env_var DB_DATABASE "$DB_DB_IN"
set_env_var DB_USERNAME "$DB_USER_IN"
set_env_var DB_PASSWORD "$DB_PASS_IN"

# Limpiar variables heredadas *_PGSQL para evitar conflictos
for KEY in DB_HOST_PGSQL DB_PORT_PGSQL DB_DATABASE_PGSQL DB_USERNAME_PGSQL DB_PASSWORD_PGSQL; do
    sed -i.bak "/^${KEY}=/d" .env && rm -f .env.bak
done

echo "[OK] .env configurado para $ENGINE_NAME"

# Test de conexion antes de migrar
echo ""
echo "Probando conexion a la base de datos..."
php -r 'try { $dsn = $_SERVER["argv"][1].":host=".$_SERVER["argv"][2].";port=".$_SERVER["argv"][3].";dbname=".$_SERVER["argv"][4]; new PDO($dsn, $_SERVER["argv"][5], $_SERVER["argv"][6]); echo "[OK] Conexion exitosa\n"; } catch (Exception $e) { echo "[AVISO] No se pudo conectar: ".$e->getMessage()."\n"; }' \
    "$DB_CONNECTION" "$DB_HOST_IN" "$DB_PORT_IN" "$DB_DB_IN" "$DB_USER_IN" "$DB_PASS_IN"

# Generate APP_KEY
echo ""
echo "[3/6] Generando APP_KEY..."
php artisan key:generate --force
echo "[OK]"

# Migrate
echo "[4/6] Ejecutando migraciones..."
php artisan migrate --force
echo "[OK]"

# Seed
echo "[5/6] Ejecutando seeders..."
php artisan db:seed --force
echo "[OK]"

# Cache
echo "[6/6] Limpiando cache..."
php artisan optimize:clear
echo "[OK]"

echo ""
echo "============================================"
echo " CONFIGURACION COMPLETADA ($ENGINE_NAME)"
echo "============================================"
echo " Ejecuta: ./start.sh para iniciar"
echo " Detener: ./detener.sh"
echo "============================================"
