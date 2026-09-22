# SIGEJUB - Sistema Integral de Gestión de Jubilaciones

Sistema para la gestión de jubilaciones: trabajadores, solicitudes, expedientes, nómina y prestaciones sociales.

## Requisitos

- **PHP** ^8.2 (extensiones: `gd`, `zip`, `intl`, `mbstring`, `curl`, `pdo_mysql`, `pdo_pgsql`)
- **Composer** (gestor de dependencias PHP)
- **MySQL / MariaDB** (con phpMyAdmin) **o PostgreSQL** — a elección durante la instalación

## Instalación rápida (Windows)

```bash
:: 1. Clonar el repositorio
git clone https://github.com/JesusMendozatecno/sigejub.git
cd sigejub

:: 2. Instalar dependencias y configurar
composer setup
```

El comando `composer setup` ejecuta automáticamente:
- `composer install`
- Crea el archivo `.env` desde `.env.example`
- Genera la `APP_KEY`
- Ejecuta las migraciones

> **Importante:** Antes de ejecutar `composer setup`, asegúrate de que tu gestor de base de datos (MySQL o PostgreSQL) esté corriendo y crea la base de datos (`bd_sigejub`). Configura la conexión en `.env` (`DB_CONNECTION=mysql|pgsql`).

## Iniciar el servidor

```bash
php artisan serve --port=8000
```

Luego abre `http://localhost:8000` en el navegador.

### Desarrollo

```bash
composer dev
```

Esto inicia el servidor de desarrollo.

## Linux

```bash
./setup.sh   # instalación inicial
./start.sh   # iniciar servidor
```

## Instalador gráfico (Windows)

Ejecuta `SIGEJUB-Installer.exe` para una instalación guiada con interfaz gráfica.
También puedes usar `setup.bat` o `inicio.php` como alternativas.

## Scripts de inicio rápidos

| Archivo | Descripción |
|---|---|
| `start.bat` / `start.sh` | Inicia el servidor y abre el navegador |
| `inicio.php` | Auto-launcher web (funciona con Apache o `php artisan serve`) |
| `detener.bat` / `detener.sh` | Detiene el servidor |

## Tecnologías

- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** Blade, CSS/JS plano servido desde `public/` (sin build)
- **Base de datos:** MySQL/MariaDB (phpMyAdmin) o PostgreSQL — seleccionable en la instalación
- **Librerías:** Chart.js, DOMPDF (PDF), PhpSpreadsheet (Excel)

## Estructura del proyecto

| Carpeta | Descripción |
|---|---|
| `app/Models` | Modelos Eloquent (Trabajador, Solicitud, Expediente, etc.) |
| `app/Http/Controllers` | Controladores con lógica CRUD y AJAX |
| `app/Services` | Servicios (DashboardCache, NominaExport) |
| `database/migrations` | Migraciones de la base de datos |
| `resources/views` | Plantillas Blade |
| `routes/web.php` | Definición de rutas |
