# Informe de Auditoría Técnica — SIGEJUB

**Fecha:** 2026-09-06
**Alcance:** Revisión técnica conservadora de todo el proyecto (Laravel 12.59, PHP 8.4.22, MySQL `bd-sigejub`).
**Política aplicada:** *Optimizar sin alterar* — no se cambió arquitectura, diseño, lógica de negocio, validaciones, nombres de tablas/columnas/rutas ni seguridad. Solo se eliminó código muerto confirmado y se aplicaron optimizaciones de bajo riesgo.

---

## 1. Cambios aplicados

| Archivo | Cambio | Comprobación de cero referencias |
|---|---|---|
| `app/Http/Controllers/ChangelogController.php` | Import `Illuminate\Http\Request` eliminado (no usado) | `git grep` sin referencias |
| `app/Http/Controllers/MasterDataController.php` | Import `Illuminate\Validation\ValidationException` eliminado | `git grep` sin referencias |
| `app/Http/Controllers/BackupController.php` | Import `Illuminate\Database\QueryException` eliminado + `->limit(100)` en `verificar()` antes del filtrado PHP | Los backups verificados son recientes; se evita traer el historial completo |
| `app/Http/Controllers/SolicitudController.php` | Línea comentada `// use Barryvdh\DomPDF\Facade\Pdf;` eliminada | Código muerto comentado |
| `app/Services/ValidationService.php` | Eliminados 4 métodos sin uso (`trabajadorConExpediente`, `trabajadorConPrestacion`, `cedulaValida`, `solicitudActivaId`) + import huérfano `App\Models\Prestacion` | `git grep "ValidationService::"` → solo 3 usos reales; los 3 métodos restantes coinciden |
| `resources/views/dashboard/index.blade.php` | Preload `/caja-negra?per_page=1` condicionado a rol `admin`/`superadmin` (`window.SIGEJUB_ROL`); para rol `usuario` se omite el fetch (evitaba un 403 inútil) | Ruta protegida con middleware `role:admin,superadmin` |
| `resources/views/dashboard/secciones/solicitudes.blade.php` | Función local duplicada `escaparHTML` eliminada; ahora usa `window.escaparHTML` | Global definida en `index.blade.php` y `dashboard.js` |

## 2. Optimizaciones evaluadas y NO aplicadas

Se documentan para no reintentar sin necesidad:

| Objeto | Situación | Decisión |
|---|---|---|
| `NominaImportService` (L95) | N+1: 1 query por cédula en el bucle de importación | No tocar: riesgo medio-alto en flujo de carga masiva; la regla prohíbe cambios en lógica de negocio |
| `UserController::index` vs `getStats` | Cálculo de stats casi duplicado | No refactorizar: beneficio marginal en página poco cargada |
| `PrestacionesController::index` | `if (!$t) return null;` + `filter()->values()` | Conservar: red de seguridad ante datos corruptos; coste despreciable |
| `validarCedula` (sesion2.js) | Global sin uso confirmado | Conservar: global, evitable uso dinámico |
| `escaparHTML`/`cachedFetch` duplicados (inline en index + dashboard.js) | Duplicación intencional | Disponibilidad temprana síncrona vs uso en `documentacion.blade.php` |
| CSS fuente sin enlazar (`base/components/layout/dashboard/notifications` + `secciones/*.css`) | Son la fuente; `dashboard.min.css` es snapshot manual sin script de build | Conservar como fuente |
| `dashboard.min.css` (127KB) | No contiene toda la CSS que emiten las secciones inline (`.gradient-bg`, `.reporte-card`, etc.) | No tocar: riesgo visual |
| Dependencias composer | `dompdf` (PrestacionesController L262), `phpspreadsheet` (Nomina services), `tinker` | Todas en uso; no eliminar. No existe `package.json`/npm |

## 3. Hallazgo adicional (no relacionado con rendimiento)

- **Credenciales de superadmin:** el usuario `jesus17otaku@gmail.com` (id 4, rol `superadmin`) existe, pero el hash de `password` **no coincide** con la contraseña documentada `Sigejub0102*` (verificado con `Hash::check`). El login HTTP falla por eso, no por los cambios. **No se modificó** la credencial por respetar la política de no alterar datos. Requiere decisión del propietario.

## 4. Verificación realizada

- `php -l` sobre los 7 archivos PHP modificados: sin errores de sintaxis.
- `node --check` sobre el JS de `solicitudes.blade.php`: sin errores.
- `artisan view:cache`: compila todas las vistas sin error.
- `artisan about`: la app arranca limpio (Laravel 12.59, PHP 8.4.22); sin errores recientes en `laravel.log`.
- Test funcional temporario (Laravel HTTP, `RefreshDatabase` en SQLite, `actingAs` superadmin), luego eliminado: 10 aserciones PASS.
  - `/dashboard` 200 y contiene `SIGEJUB_ROL` y contenido de la sección `solicitudes`.
  - `/solicitudes?per_page=1`, `/trabajadores?per_page=1`, `/expedientes?per_page=1`, `/caja-negra?per_page=1`, `/tasas-cambio/historial`, `/actividades`, `/notificaciones/no-leidas` → todas 200.
- `git grep`: cero referencias rotas a imports/métodos eliminados.

## 5. Estado y riesgos

- 7 archivos modificados, sin commitear (a la espera de aprobación).
- Riesgo residual de los cambios: **bajo**. Ninguno toca lógica de negocio, seguridad ni diseño.
- El `->limit(100)` en `verificar()` asume que los backups a confirmar son recientes; si algún día se verifica un backup anterior al centésimo más reciente, se usaría el filtrado PHP siguiente (que ya existía) — comportamiento equivalente salvo en historiales >100 sin coincidencia.