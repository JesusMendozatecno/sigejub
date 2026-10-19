# SIGEJUB — Sistema de Gestión de Jubilaciones

> Documento técnico de referencia. Contiene arquitectura, modelos de datos, roles, rutas, convenciones de código y guía de mantenimiento. Está pensado para que otra IA (o un desarrollador) pueda entender y trabajar en el proyecto sin necesidad de explorarlo primero.

---

## 1. Resumen del negocio

SIGEJUB es un sistema web de gestión de jubilaciones para una institución universitaria pública (UPTYAB). Administra:

- **Directorio de trabajadores** (personal activo y jubilado).
- **Nómina** (asignación de trabajadores a períodos de nómina, con importación masiva).
- **Solicitudes de jubilación** (flujo con estados y aprobación).
- **Expedientes** documentales por solicitud.
- **Prestaciones** y **primas** calculadas por fórmulas.
- **Tasa de cambio** del dólar (sincronización automática + registro manual).
- **Historial / Caja negra** (auditoría de todas las acciones).
- **Reportes y estadísticas** (solo administradores).

---

## 2. Stack tecnológico

| Capa | Tecnología |
|---|---|
| Framework | Laravel 11+ (PHP 8.2+, probado en 8.4) |
| Vistas | Blade (`.blade.php`) + CSS/JS vanilla (sin build step) |
| Base de datos | MySQL 8 (principal) / PostgreSQL 18 (alternativo, puerto 5433) |
| Frontend | HTML5, CSS3 con variables, ES6 (sin framework JS) |
| Gráficos | Chart.js (incluido en `public/js/vendor` o CDN) |
| Iconos | Font Awesome 6 |
| Servidor | XAMPP (Apache + PHP), puerto **8092** en desarrollo, **8000** en producción local |

**No hay** Vue/React/Livewire/Inertia. Toda la interactividad es JavaScript vanilla organizado en IIFE y funciones globales.

---

## 3. Estructura de carpetas

```
sigejub 2/
├── original/                  ← CÓDIGO FUENTE CANÓNICO (siempre editar aquí)
│   ├── app/
│   │   ├── Http/Controllers/  ← 19 controladores
│   │   ├── Models/            ← 25 modelos Eloquent
│   │   └── Services/          ← 10 servicios (lógica de negocio)
│   ├── public/
│   │   ├── css/dashboard/     ← estilos del panel (bundle + capas)
│   │   ├── css/welcome.css    ← landing page
│   │   └── js/                ← dashboard.js + secciones/*.js
│   ├── resources/views/       ← Blade
│   │   ├── dashboard/         ← panel principal
│   │   │   ├── index.blade.php    ← layout maestro
│   │   │   └── secciones/         ← 12 vistas de sección
│   │   ├── usuarios/user.blade.php ← perfil + gestión de usuarios
│   │   └── welcome.blade.php      ← landing
│   ├── routes/web.php
│   ├── database/migrations/
│   └── sigejub.md             ← este archivo
│
├── windows/mysql/             ← copia desplegable (MySQL)
├── windows/postgresql/        ← copia desplegable (PostgreSQL)
├── linux/mysql/               ← copia Linux (MySQL)
├── linux/postgresql/          ← copia Linux (PostgreSQL)
├── SIGEJUB-Portable/          ← paquete portable autocontenido (~101 MB)
└── crear-portable.ps1         ← script que genera el portable
```

**Regla de oro:** el código fuente vive en `original/`. Las 4 copias y la carpeta portable son **artefactos de sincronización** generados por script. Nunca editar directamente en las copias.

### Aplicación instalada

`C:\Users\Jesus\Desktop\SIGEJUB` — instancia desplegada que corre en el puerto **8000** y usa la misma base de datos `bd-sigejub`.

---

## 4. Convenciones de trabajo (muy importantes)

### 4.1 Flujo de cambios

1. Editar **solo** en `original/`.
2. Validar (`php -l`, `node --check`).
3. Sincronizar a las 4 copias + app instalada con SHA256.
4. Regenerar el portable si aplica.
5. Ejecutar pruebas.

### 4.2 Comandos de validación

```powershell
# PHP lint
C:\xampp\php\php.exe -l "ruta\archivo.php"

# JS lint (el más confiable para detectar errores de sintaxis)
node --check "ruta\archivo.js"

# MySQL (¡PowerShell necesita comillas simples para backticks!)
C:\xampp\mysql\bin\mysql.exe -u root -e 'USE `bd-sigejub`; SHOW TABLES;'
```

### 4.3 Gotchas de Windows PowerShell 5.1

| Problema | Solución |
|---|---|
| `Set-Content -Encoding UTF8` añade BOM | Usar `[System.IO.File]::WriteAllText($ruta, $contenido, (New-Object System.Text.UTF8Encoding($false)))` |
| No existen `Tail-` / `Head-` | Usar `Select-Object -Last N` / `Select-Object -First N` |
| `curl` es un alias | Usar `curl.exe` explícitamente |
| No redirigir `2>&1` y `2>$null` a la vez | ParserError "StreamAlreadyRedirected" |
| `rg` (ripgrep) no está instalado | Usar la herramienta Grep o `Select-String` |
| `npx` bloqueado por execution policy | Usar `node` directamente |
| `&&` no funciona en PS 5.1 | Usar `cmd1; if ($?) { cmd2 }` o `;` |
| Backticks en SQL | Envolver el comando entero en comillas simples `'...'` |

### 4.4 Organización de código

- **CSS en archivos separados por capa**, concatenados en un bundle. Ver §7.
- **JS por sección** en `public/js/secciones/`. Ver §8.
- **Blade por sección** en `resources/views/dashboard/secciones/`. Ver §9.
- Comentarios en español, estilo JSDoc breve en funciones públicas.
- Variables CSS para colores/espaciado en `base.css`. No hardcodear colores en nuevas reglas.

---

## 5. Arquitectura de la aplicación

### 5.1 Autenticación y sesión

- Rutas públicas: `/`, `/login`, `/register`, recuperación de contraseña.
- Rutas protegidas bajo `middleware('auth')`.
- Protección CSRF:
  - Formularios: `<input name="_token">` + `@csrf`
  - AJAX: `<meta name="csrf-token">` → header `X-CSRF-TOKEN`
- Throttling: login `5,1`; register `3,1`; ping de actividad `60,1`.
- Sesión con keep-alive vía `POST /actividad/ping`.

### 5.2 Tema (color/visual)

El tema se aplica **en el cliente**, no en el servidor:

```js
// index.blade.php (línea ~97) y dashboard.js inicializarDashboard()
window.SIGEJUB_THEME = '{{ auth()->user()->tema }}';   // light | dark | modern
window.SIGEJUB_ROL  = '{{ auth()->user()->rol }}';
```

Clases en `<body>`:
- `dark-mode` → modo oscuro
- `theme-modern` → modo moderno (glassmorphism/índigo)
- sin clase → modo claro (default)

Esto significa que **curl no puede verificar el tema** (es JS en cliente).

### 5.3 Color de acento del usuario

Cada usuario tiene un `color_acento` (default `#1a365d`, azul institucional). Se expone como variable CSS global:

```css
/* index.blade.php */
:root {
    --accent: {{ auth()->user()->color_acento ?? '#1a365d' }};
    --accent-dark: color-mix(in srgb, var(--accent) 72%, #000 28%);
    --accent-soft: color-mix(in srgb, var(--accent) 12%, transparent);
}
```

**Principio de diseño:** el color del sistema (`--accent`) debe usarse en:
- Header/top-bar
- Botones primarios (registrar, exportar, guardar)
- Títulos de módulo (span `.text-blue-accent`)
- Elementos destacados (tarjeta de tasa del dólar, item activo del menú)

Se cambia desde **Perfil → Personalización** (`PUT /perfil/configuracion`) mediante `window.cambiarColor()` en `dashboard.js`.

---

## 6. Base de datos

Base de datos: **`bd-sigejub`** (MySQL). 33 tablas.

### 6.1 Tablas principales

| Tabla | Propósito |
|---|---|
| `users` | Usuarios del sistema (rol, tema, color_acento, avatar) |
| `trabajadores` | Directorio de personal (con `SoftDeletes`) |
| `nominas` | Períodos de nómina |
| `nomina_trabajador` | Relación trabajador ↔ nómina (pivot) |
| `solicitudes` | Solicitudes de jubilación (con `SoftDeletes`) |
| `expedientes` | Expedientes documentales por solicitud |
| `documentos` | Archivos adjuntos |
| `antecedentes_servicio` | Historial laboral del trabajador |
| `antecedente_documentos` | Documentos de antecedentes |
| `prestaciones` | Prestaciones calculadas |
| `formulas_prestaciones` | Fórmulas de cálculo |
| `primas` | Prima de cargo (solo superadmin) |
| `sueldos` | Tabulador de sueldos |
| `tasas_cambio` | Historial de tasas del dólar |
| `activities` | Auditoría / caja negra |
| `user_notifications` | Notificaciones entre usuarios |
| `changelogs` | Registro de cambios del sistema |
| `cargos`, `grados`, `areas`, `unidades`, `niveles_instruccion`, `tipos_contrato`, `tipos_jubilacion` | Datos maestros |
| `recibos_pago`, `estudios`, `actividades_universitarias`, `dedicaciones` | Datos complementarios |

### 6.2 Tabla `users` (esquema completo)

| Columna | Tipo | Default | Notas |
|---|---|---|---|
| `id` | bigint PK | auto | |
| `nombre` | string | — | ex `name` |
| `apellido` | string | null | |
| `correo` | string unique | — | ex `email` |
| `correo_verificado_en` | timestamp | null | ex `email_verified_at` |
| `password` | string | — | |
| `rol` | enum | `usuario` | valores: `usuario`, `admin`, `superadmin` |
| `avatar` | string | null | ruta bajo `storage/` |
| `telefono` | string | null | ex `phone` |
| `fecha_nacimiento` | date | null | |
| `tema` | string | `light` | ex `theme`; `light\|dark\|modern` |
| `idioma` | string | `es` | ex `language` |
| **`color_acento`** | **string** | **`#1a365d`** | ex `accent_color`; azul institucional |
| `verificacion_dos_pasos` | boolean | false | ex `two_factor_enabled` |
| `secreto_2fa` | string | null | ex `two_factor_secret` |
| `notificacion_correo` | string | `all` | ex `notification_email` |
| `notificacion_sistema` | string | `all` | ex `notification_system` |
| `perfil_publico` | boolean | true | ex `profile_public` |
| `ultimo_acceso` | timestamp | null | ex `last_login_at` |
| `ultimo_acceso_ip` | string | null | ex `last_login_ip` |
| `token_recordar` | string | null | ex `remember_token` |
| `created_at` / `updated_at` | timestamp | | |

**Historial de migraciones de renombre:** `2026_06_03_000001_rename_columns_to_spanish.php` renombró todas las columnas de inglés a español. `2026_06_20_210612` hizo los renombres idempotentes.

### 6.3 Tabla `trabajadores` (campos clave)

`cedula`, `nombres`, `apellidos`, `cuenta_bancaria`, `genero`, `grado_nivel`, `cargo`, `unidad_departamento`, `codigo_empleado`, `sueldo_base`, `denominacion_salario`, `tabulador`, `porcentaje_prima_cargo`, `complemento_prima_cargo`, `es_jefe_coordinador`, `cesta_ticket`, `prima_profesionalizacion`, `sugau`, `afiliacion_sifaiuty`, `fecha_nacimiento`, `edad`, `fecha_ingreso`, `anos_servicio_inst`, `tipo_nomina`, `asignacion`, `tipo_contrato`, `nivel_instruccion`, `fecha_elaboracion`.

- Usa **SoftDeletes** (bajas lógicas).
- Tiene atributo virtual **`estatus`** (`$appends = ['estatus']`) → `activo` / `jubilado`.

### 6.4 Tabla `solicitudes`

`trabajador_id` (FK), `fecha_solicitud`, `periodo`, `tipo_jubilacion`, `observaciones`, `estado`.

Estados del flujo: `pendiente` → `en_revision` → `aprobado` | `rechazado`.

---

## 7. Sistema de estilos (CSS)

### 7.1 Capas del dashboard

Los estilos se cargan en este orden en `index.blade.php`:

| # | Archivo | Contenido |
|---|---|---|
| 1 | `dashboard.min.css` | **Bundle concatenado** de las fuentes base (~4574 líneas). Las fuentes sueltas NO se cargan. |
| 2 | `dark-mode.css` | Overrides para `body.dark-mode` |
| 3 | `modern-theme.css` | Overrides para `body.theme-modern` |
| 4 | `secciones/modal.css` | Estilos de modales |
| 5 | **`rediseno.css`** | Capa de modernización (añadida después; gana en empates por orden) |
| 6 | **`responsive.css`** | Breakpoints y adaptaciones (carga **al final**, por lo tanto gana en empates de especificidad igual) |

En `usuarios/user.blade.php` el orden es: `bundle → dark-mode → modern-theme → modal.css → rediseno → fontawesome → cropper → profile.css` (`profile.css` va al final).

### 7.2 Marcadores del bundle `dashboard.min.css`

El bundle es concatenado de fuentes individuales. Marcadores de inicio de cada bloque:

| Línea aprox. | Fuente |
|---|---|
| 1 | `base.css` |
| 238 | `layout.css` |
| 793 | `components.css` |
| 1211 | `dashboard.css` |
| 1385 | `EXTENSIÓN` |
| 1610 | `trabajadores.css` |
| 3434 | `reportes.css` |
| 3675 | `caja-negra.css` |
| 3768 | `modal.css` |
| 4048 | `profile.css` |

Hay **dos bloques `:root`** en el bundle. La variable `--accent` se define en `index.blade.php` (no en los archivos externos).

### 7.3 Variables CSS principales (`base.css`)

- `--accent` (desde Blade), `--accent-dark`, `--accent-soft`
- `--sidebar-collapsed-width: 72px`
- `--bg-body`, `--bg-card`, `--bg-surface`, `--bg-overlay`
- `--text-primary`, `--text-secondary`, `--text-muted`
- `--border`, `--radius-sm/md/lg`, `--shadow-card`
- `--space-1` … `--space-6`
- `--info`, `--info-light`, `--success`, `--warning`, `--danger`

### 7.4 Patrón de tarjetas

Cada hijo directo de `.content-section` se renderiza como tarjeta:

```css
.dashboard-render-zone > .content-section > *:not(.modal-overlay):not(.modal-tasa):not(.modal-resultado) {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 22px;
    margin-bottom: 16px;
    box-shadow: var(--shadow-card);
}
```

Los modales (`.modal-overlay`, `.modal-tasa`, `.modal-resultado`) quedan **excluidos** de este patrón.

### 7.5 Responsive / autoajuste

`responsive.css` breakpoints:

| Media query | Cubre |
|---|---|
| `max-width: 1024px` | Tablet: sidebar 240px, grids a 1-2 columnas, modales 90% |
| `max-width: 767px` | Móvil: sidebar off-canvas, notif-menu fixed, modales bottom-sheet, tablas con scroll |
| `max-width: 480px` | Móvil pequeño: paddings reducidos |
| `min-width: 1025px and max-width: 1280px` | Portátiles pequeños |
| `min-width: 1441px` | Escritorio grande |
| `min-width: 1921px` | Ultrawide (limita ancho legible) |
| `@media print` | Oculta sidebar, top-bar, toasts, loader |

Tipografía fluida con `clamp()` para títulos de sección y tarjetas de estadísticas.

---

## 8. Sistema de scripts (JavaScript)

### 8.1 Archivos principales

| Archivo | Rol |
|---|---|
| `public/js/dashboard.js` | Orquestador: inicialización, tema, notificaciones, sidebar, helpers (`cachedFetch`, `escaparHTML`, `mostrarToast`) |
| `public/js/secciones/trabajador.js` | **IIFE única** — modal de trabajador, tabla, filtros, stats, calendario |
| `public/js/secciones/modales.js` | API central de modales (`window.Modal`) |
| `public/js/secciones/solicitudes.js` | Autocompletado, modal de solicitud |
| `public/js/theme-welcome.js` | Aplica tema en la landing |
| `public/js/profile.js` | Perfil, cropper de avatar |

### 8.2 API de modales (`modales.js`)

```js
window.Modal = {
    abrir,      // abre un modal por id
    cerrar,     // cierra uno
    cerrarTodas,// cierra todos
    abierta     // consulta
};
```

- `abrir` → `display:'flex'` + clase `active`
- Cierre delegado mediante `[data-modal-close]`
- Intercepta clics en el overlay para cerrar
- Selectores: `.modal-overlay`, `.modal-tasa`, `.modal-resultado`

### 8.3 Sidebar colapsable (menú plegable)

- **Markup:** `index.blade.php` L181-205. Los items son `<li class="menu-item" data-target="...">` con un `<i>` de icono y **nodo de texto desnudo** después.
- **Botón:** `#sidebarToggle` (chevron) dentro de `.top-bar`.
- **Comportamiento:**
  - **Escritorio (≥768px):** alterna `.collapsed` en `.sidebar` → ancho 72px, solo iconos. Persiste en `localStorage['sigejub_sidebar_collapsed']` (`1`/`0`).
  - **Móvil (<768px):** alterna `.open` + overlay (off-canvas).
- **CSS:** `.sidebar.collapsed` en `rediseno.css`. El texto se oculta con `font-size:0` en `.menu-item` y se restaura el tamaño del `<i>`. Tooltip con `::after { content: attr(data-tooltip) }`.
- **Importante:** `responsive.css` carga **después** que `rediseno.css`. Para ganarle se usa selector de mayor especificidad: `.top-bar .sidebar-toggle`.

### 8.4 Notificaciones

- **Trigger + badge + menú** en `.notif-dropdown` (contenedor posicionado).
- Se muestra con la clase `.open` (no `style.display`).
- Cierra al: hacer clic fuera, pulsar `Escape`.
- Datos: `GET /notificaciones` → `[{id, titulo, mensaje, leida, created_at, from_user:{nombre}}]`
- Contador: `GET /notificaciones/no-leidas` → `{count}` (polling cada 30s).
- Marcar: `PUT /notificaciones/{id}/leer` y `PUT /notificaciones/leer-todas`.
- En móvil, `responsive.css` lo convierte en panel fixed full-width.

### 8.5 Trabajador.js — advertencia

`trabajador.js` es una **IIFE única**. La tabla `#tbodyTrabajadores` y `#totalTrabajadores` se pueblan **solo** desde este archivo. **Si el archivo tiene un error de sintaxis, TODA la sección trabajadores muere** (modal, tabla, filtros y stats). Siempre validar con `node --check` tras editar.

---

## 9. Vistas Blade

### 9.1 Layout maestro: `dashboard/index.blade.php`

Estructura:
1. `<head>`: meta, CSS en capas, `:root --accent`
2. `<body>`: loading overlay
3. Script de aplicación inmediata del tema (antes del render)
4. `<header class="top-bar">`: toggle de sidebar, marca, acciones (tema, notificaciones, usuario)
5. `.app-container`:
   - `<aside class="sidebar">` con `.sidebar-menu`
   - `.main-content` → `.dashboard-render-zone` con las secciones
6. Scripts al pie

### 9.2 Secciones (`resources/views/dashboard/secciones/`)

| Archivo | Sección |
|---|---|
| `inicio.blade.php` | Panel de gestión integral, stats, gráfico de barras, vencimientos, actividad, tarjeta de tasa del dólar |
| `trabajadores.blade.php` | Directorio, filtros, tabla, modal de trabajador (`#modalTrabajador`), antecedentes |
| `nomina.blade.php` | Períodos de nómina, importación/exportación |
| `solicitudes.blade.php` | Lista, modal de solicitud, autocompletado |
| `expedientes.blade.php` | Expedientes documentales |
| `prestaciones.blade.php` | Prestaciones y fórmulas |
| `primas.blade.php` | Primas (solo superadmin) |
| `tasas-cambio.blade.php` | Tasa del dólar: actual, sincronización, historial, modal `.modal-tasa` |
| `cargos-grados.blade.php` | Datos maestros |
| `reportes.blade.php` | Reportes y estadísticas (admin/superadmin) |
| `caja-negra.blade.php` | Historial de auditoría (paginado de 20 en 20) |
| `ayuda.blade.php` | Ayuda |

### 9.3 Gestión de usuarios

Está implementada como **sub-tab** dentro del perfil, no como sección del sidebar:
- Vista: `resources/views/usuarios/user.blade.php`, sub-tab `#admin-usuarios`
- Ruta: `GET /perfil/admin/usuarios`
- El contenedor de la tabla usa `<div style="overflow-x:auto">` con `max-height:62vh` para scroll vertical.

---

## 10. Roles y permisos

### 10.1 Roles disponibles

| Rol | Descripción |
|---|---|
| `usuario` | Analista / usuario estándar. Acceso a operación del día a día. |
| `admin` | Administrador. Gestiona usuarios, ve reportes e historial. |
| `superadmin` | Superadministrador. Todo lo del admin + primas, restauración de backups, configuración global. |

### 10.2 Matriz de acceso por módulo

| Módulo / Ruta | `usuario` | `admin` | `superadmin` |
|---|:---:|:---:|:---:|
| Inicio, Trabajadores, Nómina, Solicitudes, Expedientes, Prestaciones, Tasa de Cambio, Ayuda | ✅ | ✅ | ✅ |
| Reportes | ❌ | ✅ | ✅ |
| Historial / Caja negra | ❌ | ✅ | ✅ |
| Cargos y Grados | ❌ | ✅ | ✅ |
| Gestión de usuarios (`/perfil/admin/*`) | ❌ | ✅ | ✅ |
| Documentación / Changelog | ❌ | ✅ | ✅ |
| Backups (generar/listar) | ❌ | ✅ | ✅ |
| Backups (restaurar) | ❌ | ❌ | ✅ |
| Primas | ❌ | ❌ | ✅ |

### 10.3 Cómo se implementa

- **Middleware:** `role:admin,superadmin` y `role:superadmin` en `routes/web.php`.
- **Vistas:** `@if(in_array(Auth::user()->rol, ['admin', 'superadmin']))` / `@if(Auth::user()->rol === 'superadmin')`.
- **JS:** `window.SIGEJUB_ROL` disponible en cliente.

Ejemplo en `routes/web.php`:

```php
Route::middleware(['auth', 'role:admin,superadmin'])->group(function () {
    Route::get('/reportes', ...);
    Route::get('/caja-negra', ...);
});
```

---

## 11. Mapa de rutas

### 11.1 Públicas

| Método | Ruta | Controlador |
|---|---|---|
| GET | `/` | welcome |
| GET/POST | `/login` | `AuthController@login` (POST throttle 5,1) |
| GET/POST | `/register` | `AuthController@register` (POST throttle 3,1) |
| POST | `/password/email` | `AuthController@forgotPassword` |
| POST | `/password/reset` | `AuthController@resetPassword` |
| GET | `/api/public/stats` | stats públicas para el welcome |

### 11.2 Autenticadas (cualquier rol)

| Método | Ruta | Controlador |
|---|---|---|
| GET | `/dashboard` | `AuthController@dashboard` |
| POST | `/logout` | `AuthController@logout` |
| POST | `/actividad/ping` | keep-alive |
| GET | `/perfil` | `UserController@index` |
| PUT | `/perfil/actualizar` | `UserController@updateProfile` |
| POST/DELETE | `/perfil/avatar` | `UserController@uploadAvatar` / `@deleteAvatar` |
| PUT | `/perfil/password` | `UserController@updatePassword` |
| PUT | `/perfil/configuracion` | `UserController@updateSettings` (tema, idioma, color_acento) |
| GET | `/trabajadores` | `TrabajadorController@index` |
| POST | `/trabajadores` | `TrabajadorController@store` |
| PUT/DELETE | `/trabajadores/{id}` | update / destroy |
| GET | `/trabajadores/autocomplete` | autocomplete |
| GET | `/trabajadores-stats/dashboard` | `dashboardStats` |
| GET | `/nomina` | `NominaController@index` |
| GET | `/exportar/nomina` | `NominaExportController@exportar` |
| POST | `/importar/nomina` | `NominaExportController@importar` |
| GET | `/solicitudes` | `SolicitudController@index` |
| GET | `/solicitudes/estadisticas` | `SolicitudController@estadisticas` |
| GET | `/solicitudes/por-mes` | `SolicitudController@porMes` |
| GET | `/solicitudes/vencimientos` | `SolicitudController@vencimientos` |
| POST/PUT/DELETE | `/solicitudes[/{id}]` | store / update / destroy |
| GET | `/tasas-cambio` | `TasaCambioController@index` |
| GET | `/tasas-cambio/estado` | `TasaCambioController@estado` |
| POST | `/tasas-cambio` | `TasaCambioController@store` |
| POST | `/tasas-cambio/sincronizar` | `TasaCambioController@sincronizar` |
| GET | `/actividades` | `AdminController@actividadReciente` |

### 11.3 Solo admin / superadmin

| Método | Ruta |
|---|---|
| GET | `/usuarios`, `/perfil/admin/usuarios`, `/perfil/admin/actividad` |
| PUT/DELETE | `/perfil/admin/usuarios/{id}` |
| POST | `/perfil/admin/config-global`, `/notificaciones` |
| GET | `/reportes`, `/caja-negra`, `/cargos-grados`, `/documentacion` |
| GET/POST | `/backups`, `/backups/generar` |

### 11.4 Solo superadmin

| Método | Ruta |
|---|---|
| POST | `/backups/{archivo}/restaurar` |

---

## 12. Servicios (`app/Services/`)

| Servicio | Responsabilidad |
|---|---|
| `AuditService` | Registra acciones en la caja negra (`activities`) |
| `BackupService` | Generación y restauración de backups |
| `DashboardCache` | Cacheo de estadísticas del dashboard |
| `ImageStorageService` | Subida/almacenamiento de imágenes y avatares |
| `NominaExportService` | Exportación de nómina (Excel/CSV) |
| `NominaImportService` | **Importación masiva de nómina desde Excel** (ver §13) |
| `NotificationService` | Envío de notificaciones entre usuarios |
| `TasaCambioService` | Obtención de la tasa del dólar (API externa + cache + estado) |
| `ValidationService` | Reglas de validación reutilizables |
| `WorkflowService` | Flujo de estados de las solicitudes |

### 12.1 `NominaImportService` (crítico)

Importa trabajadores desde un archivo Excel. Lógica de deduplicación:

1. **Filas totalmente vacías** (columnas B/C/D/E/G/I vacías) se descartan **sin** contar como "omitidos".
2. Si la cédula ya existe → se **enlaza** a la nómina (no se duplica).
3. Si la cédula no existe pero coincide otro trabajador por cédula normalizada → también se enlaza.
4. `normalizarCedula()` ignora el prefijo `V-` / `E-`.
5. `buscarTrabajadorPorCedula()` es el helper que normaliza y busca.

**Nota histórica:** hubo un bug donde siempre se reportaba "+1 omitido" porque la cabecera de la planilla contaba como fila. Fue corregido.

---

## 13. Módulo de nómina

- `nominas` = períodos (ej. "2026").
- `nomina_trabajador` = pivot con los trabajadores asignados a ese período.
- Un mismo trabajador puede estar en varias nóminas.
- Al importar, el sistema reporta: **registrados**, **actualizados** y **omitidos**.
- **Disciplina de datos:** el total de trabajadores en Nómina debe coincidir con el total del Directorio. Si difiere, es un síntoma de duplicados o de trabajadores no enlazados.

---

## 14. Módulo de tasa de cambio

- Modelo `TasaCambio` (tabla `tasas_cambio`): `tasa decimal(12,4)`, `moneda_origen`, `moneda_destino`, `fuente`, `tipo` (`auto`|`manual`), `observacion`, `usuario_id`, `activa`, `fecha_consulta`.
- `TasaCambioService::obtenerEstadoTasa()` devuelve el estado: `actualizada`, `disponible`, `desactualizada`.
- **UI:** la tarjeta de la tasa del dólar usa el color de acento del usuario (`--accent`), no un azul hardcodeado.
- Endpoints: `GET /tasas-cambio/estado`, `POST /tasas-cambio/sincronizar`.

---

## 15. Landing / Welcome

- Vista: `resources/views/welcome.blade.php`
- Estilos: `public/css/welcome.css` (soporta los 3 temas)
- Tema aplicado por `public/js/theme-welcome.js` en el `DOMContentLoaded` (lee `localStorage.sigejub_theme`).
- Muestra estadísticas reales del sistema vía `GET /api/public/stats` → `{trabajadores, solicitudes, jubilados, expedientes}`.
- Los números se renderizan con `Intl.NumberFormat('es-VE')` en IDs: `statTrabajadores`, `statSolicitudes`, `statJubilados`, `statExpedientes`.
- El botón "Ingresar" (`.pw-nav-login`) usa tokens dedicados `--pw-btn-*` para garantizar contraste en hover/focus en los 3 temas.

---

## 16. Problemas conocidos y soluciones aplicadas

| # | Problema | Causa raíz | Solución |
|---|---|---|---|
| 1 | Modal de trabajadores no se desplegaba | **Error de sintaxis** en `trabajador.js` (bloque `keydown` duplicado + `if (modalCal)` sin cerrar) que mataba todo el archivo | Corregido + validado con `node --check` |
| 2 | Conteo de nómina siempre mostraba "+1 omitido" | La cabecera de la planilla contaba como fila | Filtrado de filas vacías en `NominaImportService` |
| 3 | Total de trabajadores no coincidía (20 vs 19) | Trabajador existente no enlazado a la nómina | `buscarTrabajadorPorCedula()` con normalización |
| 4 | Menú plegable no funcionaba en escritorio | `.sidebar-toggle { display:none }` en `responsive.css` ganaba por orden de carga | Cambiado a `display:flex` + selector `.top-bar .sidebar-toggle` con mayor especificidad |
| 5 | Botón "Guardar foto" del avatar no se veía | `#cropModal` no tenía layout flex ni acción sticky | `.modal-box { display:flex; flex-direction:column }` + `.modal-actions { position:sticky; bottom:0 } |
| 6 | Scrollbar en gestión de usuarios | La tabla no tenía límite de altura | `max-height:62vh; overflow-y:auto` |
| 7 | Botón "Ingresar" del welcome con letras invisibles | En tema modern `--pw-primary:#ffffff` → texto blanco sobre fondo blanco translúcido | Tokens dedicados `--pw-btn-*` por tema + `:focus-visible` |
| 8 | Colores de botones/títulos no seguían el color del sistema | Colores hardcodeados `#1a365d` en CSS | Reemplazados por `var(--accent)` |
| 9 | Tarjeta del dólar no usaba el color del usuario | Gradiente hardcodeado | `linear-gradient(135deg, var(--accent), var(--accent-dark))` |

---

## 17. Pruebas y verificación

No hay suite de tests automatizados (PHPUnit/Vitest). La verificación es manual mediante scripts de PowerShell en `Temp\opencode\`:

| Script | Qué hace |
|---|---|
| `test-rediseno.ps1` | Levanta servidor, verifica blade, referencias CSS, GET /login y /register |
| `sync-rediseno.ps1` | Sincroniza archivos a las 4 copias + app instalada con SHA256 |
| `test-portable.ps1` | Verifica la carpeta portable |

### Checklist de verificación tras un cambio

```powershell
# 1. Lint PHP
C:\xampp\php\php.exe -l "original\app\..." 

# 2. Lint JS (imprescindible para trabajador.js)
node --check "original\public\js\secciones\trabajador.js"

# 3. Sincronizar
powershell -ExecutionPolicy Bypass -File "C:\Users\Jesus\AppData\Local\Temp\opencode\sync-rediseno.ps1"

# 4. Probar
powershell -ExecutionPolicy Bypass -File "C:\Users\Jesus\AppData\Local\Temp\opencode\test-rediseno.ps1"

# 5. Regenerar portable (si aplica) — requiere matar php/httpd antes
taskkill /IM php.exe /F /T; taskkill /IM httpd.exe /F /T
powershell -ExecutionPolicy Bypass -File "crear-portable.ps1"
```

---

## 18. Guía para una IA que reciba este proyecto

1. **Siempre editar en `original/`.** Las demás carpetas son copias sincronizadas.
2. **Validar JS con `node --check`**, especialmente `trabajador.js` (un error ahí mata toda una sección).
3. **No romper el patrón de tarjetas** de `.content-section` (§7.4).
4. **Usar `var(--accent)`** en lugar de colores hardcodeados para cualquier elemento destacado.
5. **Respetar el orden de carga de CSS** (§7.1); `responsive.css` va al final y gana en empates.
6. **Los roles se validan en 3 capas:** middleware (rutas), Blade (`@if`), JS (`window.SIGEJUB_ROL`).
7. **El tema se aplica en cliente**, por lo que no es verificable con curl.
8. **PowerShell 5.1** tiene muchas limitaciones (§4.3). Leer esa sección antes de escribir comandos.
9. **Los modales usan la API de `modales.js`** (`window.Modal`), no manejar `display` directamente.
10. **Trabajadores y Solicitudes usan SoftDeletes** → siempre filtrar con `whereNull('deleted_at')` en queries crudos.

---

## 19. Credenciales y entorno de desarrollo

- Servidor dev: puerto **8092** (`original/`), puerto **8000** (app instalada), puerto **8093** (portable).
- Base de datos: `bd-sigejub` (MySQL, XAMPP, sin contraseña por defecto para `root`).
- PostgreSQL alternativo: puerto **5433**.
- Ruta de la app instalada: `C:\Users\Jesus\Desktop\SIGEJUB`.
- PHP: `C:\xampp\php\php.exe` (8.4.22).
- MySQL CLI: `C:\xampp\mysql\bin\mysql.exe`.
- Node: `v24.14.1` (solo para lint, no hay build).

---

*Documento generado para el proyecto SIGEJUB. Actualizarlo cada vez que se añadan módulos, rutas o cambios de arquitectura.*
