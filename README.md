# SIGEJUB - Sistema Integral de Gestión de Jubilaciones

Sistema para la gestión de jubilaciones: trabajadores, solicitudes, expedientes, nómina y prestaciones sociales.

## Requisitos

- **PHP** ^8.2 (extensiones: `gd`, `zip`, `intl`, `mbstring`, `curl`, `pdo_mysql`)
- **Composer** (gestor de dependencias PHP)
- **Node.js** ^20.19 o ^22.12 (con npm)
- **MySQL / MariaDB**

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
- Instala dependencias frontend (`npm install && npm run build`)

> **Importante:** Antes de ejecutar `composer setup`, asegúrate de que la base de datos MySQL esté corriendo y crea la base de datos (`bd_sigejub`).

## Iniciar el servidor

```bash
php artisan serve --port=8000
```

Luego abre `http://localhost:8000` en el navegador.

### Desarrollo con recarga en vivo

```bash
composer dev
```

Esto inicia simultáneamente: servidor, colas de trabajo, logs y Vite con recarga automática.

## Instalador gráfico (Windows)

Ejecuta `SIGEJUB-Installer.exe` para una instalación guiada con interfaz gráfica.
También puedes usar `setup.bat` o `inicio.php` como alternativas.

## Scripts de inicio rápidos

| Archivo | Descripción |
|---|---|
| `start.bat` | Inicia el servidor y abre el navegador |
| `inicio.php` | Auto-launcher web (funciona con Apache o `php artisan serve`) |
| `detener.bat` | Detiene el servidor |

## Tecnologías

- **Backend:** Laravel 12, PHP 8.2+
- **Frontend:** Blade, Tailwind CSS v4, Vite
- **Base de datos:** MySQL / MariaDB
- **Librerías:** Chart.js, Mermaid.js, DOMPDF (PDF), PhpSpreadsheet (Excel)

## Estructura del proyecto

| Carpeta | Descripción |
|---|---|
| `app/Models` | Modelos Eloquent (Trabajador, Solicitud, Expediente, etc.) |
| `app/Http/Controllers` | Controladores con lógica CRUD y AJAX |
| `app/Services` | Servicios (DashboardCache, NominaExport) |
| `database/migrations` | Migraciones de la base de datos |
| `resources/views` | Plantillas Blade |
| `routes/web.php` | Definición de rutas |
