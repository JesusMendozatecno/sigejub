# SIGEJUB - Guía de Instalación

Guía paso a paso para instalar el Sistema Integral de Gestión de Jubilaciones en **Linux** y **Windows**.

---

## Requisitos generales

- PHP 8.2 o superior (extensiones: `mbstring`, `xml`, `curl`, `gd`, `zip`, `intl`, `sqlite3`, y el driver de la base de datos elegida: `pdo_mysql` o `pdo_pgsql`).
- Composer (el instalador Linux lo instala automáticamente).
- Un gestor de base de datos: **SQLite** (sin servidor), **MySQL/MariaDB** o **PostgreSQL**, a elección durante la instalación.

---

# INSTALACIÓN EN LINUX

El instalador automático (`instalar.sh`) instala PHP, Composer, los drivers y la aplicación en automático, crea los accesos directos y deja el sistema listo para usar.

## Paso 1 — Habilitar los scripts

```bash
chmod +x instalar.sh setup.sh start.sh detener.sh desinstalar.sh
```

## Paso 2 — Ejecutar el instalador

Ejecuta **sin** `sudo` (el script pide contraseña solo cuando la necesita):

```bash
./instalar.sh<--este es el instalador
```

El asistente te irá guiando:

1. **Motor de base de datos** — elige:
   - `1` SQLite (recomendado, no requiere servidor)
   - `2` MySQL / MariaDB
   - `3` PostgreSQL
   nota:aqui el recomendado es sqlite pero para fines de proyecto usar postgresql

2. **Carpeta de instalación** — escribe la ruta absoluta donde quieres la aplicación.
   Ejemplo para el Escritorio:
   ```
   Carpeta de instalacion [/home/tu_usuario/SIGEJUB]: /home/tu_usuario/Escritorio/SIGEJUB
   ```
   > Importante: la ruta debe empezar con `/`. No uses `~` ni un nombre suelto.
3. El script instala lo que falte (PHP, Composer, drivers), copia la aplicación, configura el `.env`, migra la base de datos y carga los datos iniciales (seeders).
4. Al final pregunta si quieres iniciar el sistema ahora. se le tiene que dar S de Si

## Paso 3 — Usar el sistema

- **Iniciar:** `./start.sh` (abre el navegador en http://localhost:[puerto]/. El puerto se elige automáticamente desde 8000).
- **Detener:** `./detener.sh`
- **Desinstalar:** `./desinstalar.sh` (agrega `--keep-db` para conservar la base de datos).
- También quedan accesos directos en el menú de aplicaciones: **"SIGEJUB - Sistema de Jubilaciones"** y **"Desinstalar SIGEJUB"**.

## Instalación manual (si ya tienes PHP y la base de datos)

```bash
./setup.sh   # configura el .env, migra y carga datos
./start.sh   # inicia el servidor
```

## Solución de problemas (Linux)

| Problema | Solución |
|---|---|
| `could not find driver` (pgsql/mysql) | El instalador actual ya lo resuelve solo. A mano: `sudo apt-get install -y php-pgsql` o `sudo apt-get install -y php-mysql`, luego `cd ~/Escritorio/SIGEJUB && php artisan migrate --force && php artisan db:seed --force` |
| `composer install falló` | Revisa el log: `cat /tmp/sigejub-composer.log` |
| PHP viejo (menor a 8.2) | Agrega el PPA ondrej/php: `sudo add-apt-repository ppa:ondrej/php && sudo apt-get update` |
| Error de permisos al ejecutar los scripts | `chmod +x instalar.sh setup.sh start.sh detener.sh desinstalar.sh` |

---

# INSTALACIÓN EN WINDOWS

## Requisitos (Windows)

- Windows 10/11.
- **Opción recomendada:** XAMPP (https://www.apachefriends.org) con PHP 8.2+ (viene en `C:\xampp\php\php.exe`).
- Base de datos: MySQL/MariaDB (incluida en XAMPP) o PostgreSQL si la prefieres.

## Opción A — Instalador gráfico (la más fácil)

1. Copia `SIGEJUB-Installer.exe` a la PC.
2. Haz doble clic en `SIGEJUB-Installer.exe`.
   > También puedes hacer doble clic en `setup.bat`, que abre el mismo instalador.
3. Sigue los pasos del asistente (elige base de datos, carpeta de destino, etc.).
4. Al terminar, el sistema queda listo.

El `.exe` es portable: **no requiere PHP ni Composer** para instalarse.

## Opción B — Manual con XAMPP

### 1) Instalar y preparar XAMPP

1. Descarga e instala XAMPP con PHP 8.2 o superior.
2. Abre el **Panel de Control de XAMPP** y activa Apache y MySQL.

### 2) Colocar la aplicación

Descomprime o clona el proyecto dentro de `C:\xampp\htdocs\`:

```batch
C:\xampp\htdocs\sigejub
```

### 3) Instalar dependencias y configurar

Abre una terminal en la carpeta del proyecto (`C:\xampp\htdocs\sigejub`) y ejecuta:

```batch
:: asegúrate de que php esté en el PATH o usa C:\xampp\php\php.exe
composer install
copy .env.example .env
php artisan key:generate
```

### 4) Configurar la base de datos

Crea la base de datos (por ejemplo `bd-sigejub`) con **phpMyAdmin** (http://localhost/phpmyadmin) o con tu gestor, y edita el bloque de base de datos en `.env`:

```env
DB_CONNECTION=mysql        ; o pgsql si usas PostgreSQL
DB_HOST=127.0.0.1
DB_PORT=3306               ; o 5432 para PostgreSQL
DB_DATABASE=bd-sigejub
DB_USERNAME=root
DB_PASSWORD=
```

> Si usas XAMPP con MySQL, deja usuario `root` y contraseña vacía por defecto.
> Para respaldos con XAMPP agrega en `.env`:
> `BACKUP_MYSQLDUMP_PATH=C:/xampp/mysql/bin/mysqldump.exe`

### 5) Migrar y cargar datos

```batch
php artisan migrate --force
php artisan db:seed --force
```

### 6) Iniciar el sistema

Doble clic en **`start.bat`**:

- Localiza PHP automáticamente (PATH o `C:\xampp\php\php.exe`).
- Inicia el servidor en http://localhost:8000/ y abre el navegador.
- Si el servidor ya está corriendo, solo abre el navegador.

Para **detener**: doble clic en **`detener.bat`**.

### Alternativa con Apache

Si quieres acceder por Apache, abre en el navegador:

```
http://localhost/sigejub/inicio.php
```

(según el nombre de la carpeta dentro de `htdocs`). `inicio.php` arranca el servidor automáticamente si no está corriendo.

---

## Scripts disponibles (resumen)

| Archivo | Descripción |
|---|---|
| `instalar.sh` (Linux) | Instalación automática completa (PHP + Composer + BD + app). |
| `setup.sh` (Linux) | Configuración inicial si ya tienes PHP y la BD. |
| `start.sh` / `start.bat` | Inicia el servidor y abre el navegador. |
| `detener.sh` / `detener.bat` | Detiene el servidor. |
| `desinstalar.sh` (Linux) | Desinstala (usa `--keep-db` para conservar la base de datos). |
| `SIGEJUB-Installer.exe` / `setup.bat` (Windows) | Instalador gráfico de Windows. |
| `inicio.php` | Auto-launcher vía Apache. |
| `build-installer.ps1` (Windows) | Recompila `SIGEJUB-Installer.exe`. |