#!/bin/bash
# ============================================================
#  SIGEJUB - Instalador para Linux (Debian/Ubuntu y derivados)
#  Instala PHP 8.2+, Composer, copia la aplicacion, configura la
#  base de datos (SQLite / MySQL-MariaDB / PostgreSQL), migra,
#  crea los accesos directos y deja listo para usar ./start.sh
#  Uso: bash instalar.sh   (o ./instalar.sh)
# ============================================================

set -u

SRC_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APT_UPDATED=0
APT_OK=0

info() { echo "[INFO] $*"; }
ok()   { echo "[OK]   $*"; }
warn() { echo "[AVISO] $*"; }
die()  { echo "[ERROR] $*"; exit 1; }

has() { command -v "$1" >/dev/null 2>&1; }

require_apt() {
    if [ "$APT_OK" = 1 ]; then return; fi
    if ! has apt-get; then
        die "Este instalador soporta Debian/Ubuntu (apt-get). En otra distribucion instala PHP 8.2+ (con mbstring, xml, curl, gd, zip, intl, sqlite3/pdo_sqlite y el driver de tu BD), Composer y ejecuta ./setup.sh"
    fi
    APT_OK=1
}

apt_update() {
    if [ "$APT_UPDATED" = 1 ]; then return; fi
    require_apt
    info "Actualizando lista de paquetes (pide tu contrasena sudo)..."
    sudo apt-get update -qq
    APT_UPDATED=1
}

# Compara version PHP >= 8.2 (formato "8.3")
php_ok() {
    local v="$1" maj min rest
    maj="${v%%.*}"; rest="${v#*.}"; min="${rest%%.*}"
    [ "$maj" -gt 8 ] && return 0
    [ "$maj" -lt 8 ] && return 1
    [ "${min:-0}" -ge 2 ]
}

set_env_var() {
    local key="$1" val="$2" file="$3"
    if grep -q "^${key}=" "$file"; then
        sed -i.bak "s|^${key}=.*|${key}=${val}|" "$file" && rm -f "${file}.bak"
    else
        echo "${key}=${val}" >> "$file"
    fi
}

remove_env_var() {
    local key="$1" file="$2"
    sed -i.bak "/^${key}=/d" "$file" && rm -f "${file}.bak"
}

# Puerto libre a partir de 8000
find_free_port() {
    local p
    for p in $(seq 8000 8099); do
        if ! ss -tln 2>/dev/null | grep -q ":$p "; then echo "$p"; return; fi
    done
    echo "8000"
}

echo "============================================================"
echo "  SIGEJUB - Instalador para Linux"
echo "============================================================"
echo ""

# ─── 1) PHP -----------------------------------------------------------------
info "Verificando PHP..."
need_php_install=0
if ! has php; then
    need_php_install=1
else
    PHP_V="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null)"
    if ! php_ok "$PHP_V"; then
        warn "PHP actual es $PHP_V (requerido 8.2+). Se actualizara/instalara."
        need_php_install=1
    fi
fi

if [ "$need_php_install" = 1 ]; then
    require_apt
    apt_update
    info "Instalando PHP y extensiones..."
    sudo DEBIAN_FRONTEND=noninteractive apt-get install -y php-cli php-mbstring php-xml php-curl php-gd php-zip php-intl php-sqlite3 php-mysql php-pgsql php-bcmath php-bz2
fi

PHP_V="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null)"
php_ok "$PHP_V" || die "PHP >= 8.2 es obligatorio para Laravel 12 (tienes $PHP_V). En Ubuntu/Debian antiguos agrega el PPA ondrej/php y vuelve a ejecutar."
ok "PHP $PHP_V disponible"

# Extensiones esenciales (Laravel, DOMPDF y PhpSpreadsheet)
# Se verifica cada modulo con `php -m` y se instala el paquete correspondiente.
MISSING=""
for ext in mbstring dom xml simplexml xmlreader xmlwriter gd zip intl curl fileinfo sqlite3 pdo_sqlite bcmath ctype iconv tokenizer; do
    php -m 2>/dev/null | grep -qi "^${ext}$" || MISSING="$MISSING $ext"
done
if [ -n "$MISSING" ]; then
    warn "Faltan extensiones PHP:$MISSING. Instalando..."
    apt_update
    for ext in $MISSING; do
        case "$ext" in
            xml|simplexml|xmlreader|xmlwriter) pkg="php-xml";;
            mbstring|curl|intl|bcmath|ctype|iconv|tokenizer|fileinfo|gd|zip|sqlite3) pkg="php-$ext";;
            dom) pkg="php-xml";;
            *) pkg="php-$ext";;
        esac
        if ! sudo DEBIAN_FRONTEND=noninteractive apt-get install -y "$pkg" >/dev/null 2>&1; then
            warn "No se pudo instalar el paquete $pkg (revisa PPA ondrej/php si el paquete no existe)."
        fi
    done
fi

# Herramientas que Composer usa para descargar y descomprimir paquetes
if ! has unzip; then
    require_apt
    apt_update
    info "Instalando unzip (requerido por Composer)..."
    sudo DEBIAN_FRONTEND=noninteractive apt-get install -y unzip
fi
if ! has git; then
    require_apt
    apt_update
    info "Instalando git (requerido por Composer)..."
    sudo DEBIAN_FRONTEND=noninteractive apt-get install -y git
fi

# ─── 2) Composer --------------------------------------------------------------
info "Verificando Composer..."
COMPOSER_BIN=""
if has composer; then
    COMPOSER_BIN="composer"
    ok "Composer del sistema"
else
    apt_update
    info "Instalando Composer..."
    if sudo DEBIAN_FRONTEND=noninteractive apt-get install -y composer 2>/dev/null && has composer; then
        COMPOSER_BIN="composer"
        ok "Composer instalado via apt"
    else
        # Fallback: instalador oficial (descarga Fresco)
        TMPDIR_COM="$(mktemp -d)"
        info "Descargando Composer oficial..."
        php -r "copy('https://getcomposer.org/installer', '$TMPDIR_COM/composer-setup.php');"
        if [ ! -s "$TMPDIR_COM/composer-setup.php" ]; then
            rm -rf "$TMPDIR_COM"
            die "No se pudo descargar Composer (revisa internet). Vuelve a ejecutar."
        fi
        php "$TMPDIR_COM/composer-setup.php" --install-dir="$TMPDIR_COM" --filename=composer >/dev/null 2>&1
        [ -x "$TMPDIR_COM/composer" ] || { rm -rf "$TMPDIR_COM"; die "Fallo al generar Composer."; }
        COMPOSER_BIN="php $TMPDIR_COM/composer"
        ok "Composer descargado"
    fi
fi
export COMPOSER_ALLOW_SUPERUSER=1

# ─── 3) Motor de base de datos ------------------------------------------------
echo ""
echo "Motor de base de datos:"
echo "  1) SQLite (recomendado, sin servidor)"
echo "  2) MySQL / MariaDB"
echo "  3) PostgreSQL"
read -r -p "Opcion [1]: " DBOPT
DBOPT="${DBOPT:-1}"
case "$DBOPT" in
    2) DB_ENGINE=mysql;;
    3) DB_ENGINE=pgsql;;
    *) DB_ENGINE=sqlite;;
esac
ok "Motor seleccionado: $DB_ENGINE"

# ─── 4) Carpeta de instalacion -------------------------------------------------
DEFAULT_APP="$HOME/SIGEJUB"
read -r -p "Carpeta de instalacion [$DEFAULT_APP]: " APP_IN
APP_OUT="${APP_IN:-$DEFAULT_APP}"
case "$APP_OUT" in
    /*) APP_DIR="$APP_OUT";;
    "") die "Carpeta vacia";;
    *) APP_DIR="$HOME/$APP_OUT";;
esac

if [ "$APP_DIR" = "$SRC_DIR" ]; then
    die "La carpeta de instalacion no puede ser la misma donde esta el instalador."
fi
if [ -d "$APP_DIR" ] && [ -n "$(ls -A "$APP_DIR" 2>/dev/null)" ]; then
    read -r -p "La carpeta ya existe con contenido. Reinstalar conservando datos? [s/N]: " REIN
    if [ "${REIN:-n}" != "s" ] && [ "${REIN:-n}" != "S" ]; then
        die "Instalacion cancelada."
    fi
    warn "Reinstalando en $APP_DIR (se conserva .env y base de datos)."
else
    mkdir -p "$APP_DIR" || die "No se pudo crear $APP_DIR"
fi

# ─── 5) Copia filtrada de la aplicacion -------------------------------------
info "Copiando aplicacion a $APP_DIR ..."
tar -C "$SRC_DIR" -cf - \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='installer-src' \
    --exclude='paquetes' \
    --exclude='compont' \
    --exclude='tests' \
    --exclude='*.log' \
    --exclude='*.exe' \
    --exclude='*.msi' \
    --exclude='*.ps1' \
    --exclude='*.vbs' \
    --exclude='*.bat' \
    --exclude='*.lnk' \
    --exclude='composer.phar' \
    --exclude='.env' \
    . | tar -C "$APP_DIR" -xf - || die "Fallo al copiar la aplicacion."
ok "Aplicacion copiada"

# ─── 6) .env y configuracion de BD -------------------------------------------
ENV_FILE="$APP_DIR/.env"
if [ ! -f "$ENV_FILE" ]; then
    cp "$APP_DIR/.env.example" "$ENV_FILE"
fi

PORT="$(find_free_port)"
set_env_var APP_PORT "$PORT" "$ENV_FILE"
set_env_var APP_URL "http://localhost:$PORT" "$ENV_FILE"

# Tokens aleatorios para credenciales generadas
GEN_PASS="$(openssl rand -hex 10 2>/dev/null || echo "sigejub$(date +%s)")"

case "$DB_ENGINE" in
    sqlite)
        set_env_var DB_CONNECTION sqlite "$ENV_FILE"
        mkdir -p "$APP_DIR/database"
        : > "$APP_DIR/database/bd-sigejub.sqlite"
        set_env_var DB_DATABASE "$APP_DIR/database/bd-sigejub.sqlite" "$ENV_FILE"
        remove_env_var DB_HOST "$ENV_FILE"
        remove_env_var DB_PORT "$ENV_FILE"
        remove_env_var DB_USERNAME "$ENV_FILE"
        remove_env_var DB_PASSWORD "$ENV_FILE"
        remove_env_var DB_HOST_PGSQL "$ENV_FILE"
        remove_env_var DB_PORT_PGSQL "$ENV_FILE"
        remove_env_var DB_DATABASE_PGSQL "$ENV_FILE"
        remove_env_var DB_USERNAME_PGSQL "$ENV_FILE"
        remove_env_var DB_PASSWORD_PGSQL "$ENV_FILE"
        ;;

    mysql)
        require_apt
        if ! php -m 2>/dev/null | grep -qi '^pdo_mysql$'; then
            apt_update
            info "Instalando driver MySQL/MariaDB para PHP..."
            sudo DEBIAN_FRONTEND=noninteractive apt-get install -y php-mysql
            php -m 2>/dev/null | grep -qi '^pdo_mysql$' || die "No se pudo instalar el driver pdo_mysql."
        fi
        if ! has mysql; then
            apt_update
            info "Instalando MariaDB..."
            sudo DEBIAN_FRONTEND=noninteractive apt-get install -y mariadb-server
        fi
        info "Arrancando MariaDB..."
        sudo systemctl enable --now mariadb >/dev/null 2>&1 || sudo service mariadb start >/dev/null 2>&1 || true
        read_ok=0
        for _ in $(seq 1 30); do
            if sudo mysqladmin ping --silent >/dev/null 2>&1; then read_ok=1; break; fi
            sleep 1
        done
        [ "$read_ok" = 1 ] || die "MariaDB no arranco. Revisa: sudo systemctl status mariadb"
        DB_NAME=sigejub
        DB_USER=sigejub
        info "Creando base de datos '$DB_NAME' y usuario '$DB_USER'..."
        sudo mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${GEN_PASS}'; ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${GEN_PASS}'; GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost'; FLUSH PRIVILEGES;"
        set_env_var DB_CONNECTION mysql "$ENV_FILE"
        set_env_var DB_HOST 127.0.0.1 "$ENV_FILE"
        set_env_var DB_PORT 3306 "$ENV_FILE"
        set_env_var DB_DATABASE "$DB_NAME" "$ENV_FILE"
        set_env_var DB_USERNAME "$DB_USER" "$ENV_FILE"
        set_env_var DB_PASSWORD "$GEN_PASS" "$ENV_FILE"
        for K in DB_HOST_PGSQL DB_PORT_PGSQL DB_DATABASE_PGSQL DB_USERNAME_PGSQL DB_PASSWORD_PGSQL; do
            remove_env_var "$K" "$ENV_FILE"
        done
        ;;

    pgsql)
        require_apt
        if ! php -m 2>/dev/null | grep -qi '^pdo_pgsql$'; then
            apt_update
            info "Instalando driver PostgreSQL para PHP..."
            sudo DEBIAN_FRONTEND=noninteractive apt-get install -y php-pgsql
            php -m 2>/dev/null | grep -qi '^pdo_pgsql$' || die "No se pudo instalar el driver pdo_pgsql."
        fi
        if ! has psql; then
            apt_update
            info "Instalando PostgreSQL..."
            sudo DEBIAN_FRONTEND=noninteractive apt-get install -y postgresql
        fi
        info "Arrancando PostgreSQL..."
        sudo systemctl enable --now postgresql >/dev/null 2>&1 || sudo service postgresql start >/dev/null 2>&1 || true
        read_ok=0
        for _ in $(seq 1 30); do
            if sudo -u postgres pg_isready -h 127.0.0.1 -p 5432 >/dev/null 2>&1; then read_ok=1; break; fi
            sleep 1
        done
        [ "$read_ok" = 1 ] || die "PostgreSQL no arranco. Revisa: sudo systemctl status postgresql"
        DB_NAME=sigejub
        DB_USER=sigejub
        info "Creando rol '$DB_USER' y base '$DB_NAME'..."
        if ! sudo -u postgres psql -tAc "SELECT 1 FROM pg_roles WHERE rolname='${DB_USER}'" | grep -q 1; then
            sudo -u postgres psql -c "CREATE ROLE ${DB_USER} LOGIN PASSWORD '${GEN_PASS}'"
        else
            sudo -u postgres psql -c "ALTER ROLE ${DB_USER} WITH LOGIN PASSWORD '${GEN_PASS}'"
        fi
        if ! sudo -u postgres psql -tAc "SELECT 1 FROM pg_database WHERE datname='${DB_NAME}'" | grep -q 1; then
            sudo -u postgres createdb -O "${DB_USER}" "${DB_NAME}"
        fi
        set_env_var DB_CONNECTION pgsql "$ENV_FILE"
        set_env_var DB_HOST 127.0.0.1 "$ENV_FILE"
        set_env_var DB_PORT 5432 "$ENV_FILE"
        set_env_var DB_DATABASE "$DB_NAME" "$ENV_FILE"
        set_env_var DB_USERNAME "$DB_USER" "$ENV_FILE"
        set_env_var DB_PASSWORD "$GEN_PASS" "$ENV_FILE"
        for K in DB_CONNECTION_PGSQL DB_HOST_PGSQL DB_PORT_PGSQL DB_DATABASE_PGSQL DB_USERNAME_PGSQL DB_PASSWORD_PGSQL; do
            remove_env_var "$K" "$ENV_FILE"
        done
        ;;
esac
ok ".env configurado ($DB_ENGINE, puerto $PORT)"

# ─── 7) Permisos, Composer install, APP_KEY ------------------------------
chmod -R u+rwX,go+rX "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true
mkdir -p "$APP_DIR/storage/logs" "$APP_DIR/storage/framework/sessions" "$APP_DIR/storage/framework/cache/data" "$APP_DIR/storage/framework/views" 2>/dev/null || true

cd "$APP_DIR" || die "No se pudo entrar a $APP_DIR"
info "Ejecutando Composer install..."
if ! COMPOSER_MEMORY_LIMIT=-1 $COMPOSER_BIN install --no-ansi --no-interaction >/tmp/sigejub-composer.log 2>&1; then
    echo "[ERROR] composer install fallo. Ultimas lineas del log:"
    echo "--------------------------------------------------------"
    tail -n 40 /tmp/sigejub-composer.log 2>/dev/null || true
    echo "--------------------------------------------------------"
    die "composer install fallo. Log completo en /tmp/sigejub-composer.log"
fi
[ -f "$APP_DIR/vendor/autoload.php" ] || die "composer install no genero vendor/autoload.php"
ok "Dependencias instaladas"

info "Generando APP_KEY..."
php artisan key:generate --force >/dev/null 2>&1
ok "APP_KEY generado"

# ─── 8) Migraciones y seeders ------------------------------------------------
info "Ejecutando migraciones..."
php artisan migrate --force --no-ansi || die "Las migraciones fallaron. Revisa la configuracion de la base de datos."
ok "Migraciones aplicadas"

info "Ejecutando seeders..."
php artisan db:seed --force --no-ansi || warn "Los seeders fallaron (puedes ejecutarlos luego con 'php artisan db:seed --force')"
ok "Seeders ejecutados"

# ─── 9) storage:link y limpieza ----------------------------------------------
if [ -e "$APP_DIR/public/storage" ]; then
    rm -rf "$APP_DIR/public/storage"
fi
php artisan storage:link >/dev/null 2>&1 || warn "storage:link no se creo"
php artisan optimize:clear >/dev/null 2>&1 || true
chmod -R u+rwX,go+rX "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true
ok "Storage y permisos listos"

# ─── 10) Accesos directos ---------------------------------------------------
chmod +x "$APP_DIR/start.sh" "$APP_DIR/detener.sh" "$APP_DIR/desinstalar.sh" 2>/dev/null || true
chmod +x "$SRC_DIR/start.sh" "$SRC_DIR/detener.sh" "$SRC_DIR/desinstalar.sh" 2>/dev/null || true

DESKTOP_DIR="$HOME/.local/share/applications"
mkdir -p "$DESKTOP_DIR"
ICON="$APP_DIR/public/img/logo-light.svg"

cat > "$DESKTOP_DIR/SIGEJUB.desktop" <<EOF
[Desktop Entry]
Type=Application
Name=SIGEJUB - Sistema de Jubilaciones
Comment=Sistema Integral de Gestion de Jubilaciones
Exec=$APP_DIR/start.sh
Icon=$ICON
Terminal=false
Categories=Office;
StartupNotify=false
EOF

cat > "$DESKTOP_DIR/Desinstalar SIGEJUB.desktop" <<EOF
[Desktop Entry]
Type=Application
Name=Desinstalar SIGEJUB
Comment=Desinstala el sistema (conserva la base de datos con --keep-db)
Exec=$APP_DIR/desinstalar.sh
Icon=$APP_DIR/public/img/logo-dark.svg
Terminal=true
Categories=Office;
EOF
ok "Accesos directos creados en Menu de aplicaciones"

echo ""
echo "============================================================"
echo "  INSTALACION COMPLETADA"
echo "============================================================"
echo "  Pagina: http://localhost:$PORT/"
echo "  Carpeta: $APP_DIR"
echo "  Iniciar: $APP_DIR/start.sh"
echo "  Detener: $APP_DIR/detener.sh"
echo "  Desinstalar: $APP_DIR/desinstalar.sh  (--keep-db conserva la BD)"
echo "  Base de datos: $DB_ENGINE"
if [ "$DB_ENGINE" != "sqlite" ]; then
    echo "  Las credenciales de la BD quedaron guardadas en: $ENV_FILE"
fi
echo "============================================================"
echo ""
read -r -p "Iniciar SIGEJUB ahora? [s/N]: " START_NOW
case "${START_NOW:-n}" in
    s|S) "$APP_DIR/start.sh";;
    *) echo "Listo. Cuando quieras ejecuta: $APP_DIR/start.sh";;
esac