# Plan de Tareas y Backlog de Implementación (SDD) — SIGEJUB

**Sistema Integral de Gestión de Jubilaciones**  
**Documento de Control de Tareas, Épicas y Criterios de Aceptación**  
**Fecha:** Octubre 2026 | **Alineación:** SDD Brownfield  

---

## Matriz de Priorización

- **[M] Must Have (Obligatorio):** Máxima prioridad inmediata (seguridad financiera y modularidad).
- **[S] Should Have (Deseable):** Funcionalidades de alto valor aprobadas (QR, Correo, Fórmulas).
- **[C] Could Have (Opcional):** Optimizaciones y herramientas de soporte.

---

## Épica 1: Blindaje del Núcleo y Seguridad Financiera (Backend)

### [M] Tarea 1.1: Creación del `PrestacionCalculationService`
- **Descripción:** Mover el cálculo de prestaciones, sueldo integral y conversión de divisas exclusivamente al servidor.
- **Archivos Afectados:**
  - `original/app/Services/PrestacionCalculationService.php` (nuevo)
  - `original/app/Http/Controllers/PrestacionesController.php`
- **Criterios de Aceptación (DoD):**
  - `PrestacionesController::store` calcula y persiste los montos internamente usando la fórmula activa.
  - Se descarta cualquier monto o sueldo integral provisto directamente por el cliente en el cuerpo de la petición.
  - Se registra snapshot inmutable de primas, sueldo y tasa BCV en el registro de la prestación.

### [M] Tarea 1.2: Endpoint de Simulación Server-Side (`POST /prestaciones/simular`)
- **Descripción:** Proveer un endpoint seguro para que la interfaz consulte la previsualización del cálculo en tiempo real sin persistir datos.
- **Archivos Afectados:**
  - `original/routes/web.php`
  - `original/app/Http/Controllers/PrestacionesController.php`
- **Criterios de Aceptación (DoD):**
  - Recibe el `trabajador_id` y devuelve el desglose legal completo.
  - La respuesta es idéntica a lo que se guardará formalmente al liquidar.

### [S] Tarea 1.3: Form Requests para Controladores Críticos
- **Descripción:** Extraer reglas de validación en línea hacia clases dedicadas en `app/Http/Requests/`.
- **Archivos Afectados:**
  - `original/app/Http/Requests/StoreTrabajadorRequest.php`
  - `original/app/Http/Requests/StorePrestacionRequest.php`
  - `original/app/Http/Requests/StoreAntecedenteRequest.php`
- **Criterios de Aceptación (DoD):**
  - Controladores libres de lógica de validación repetitiva.
  - Sanitización automática de cédulas (5-8 dígitos) y campos monetarios.

---

## Épica 2: Modularización del Frontend (Vanilla ES6 Nativo)

### [M] Tarea 2.1: Núcleo de Red y Notificaciones (`api-client.js` y `toast.js`)
- **Descripción:** Implementar un cliente `fetch` nativo con manejo de token CSRF, interceptación de sesiones expiradas (401/419) y notificaciones visuales Toast.
- **Archivos Afectados:**
  - `original/public/js/core/api-client.js` (nuevo)
  - `original/public/js/core/toast.js` (nuevo)
- **Criterios de Aceptación (DoD):**
  - Cero dependencias de Node.js o paquetes npm.
  - Manejo unificado de alertas y errores de red en toda la aplicación.

### [M] Tarea 2.2: Descomposición Modular de `trabajador.js` (66 KB)
- **Descripción:** Dividir el script monolítico en módulos ES6 cohesivos organizados por responsabilidad.
- **Archivos Afectados:**
  - `original/public/js/secciones/trabajadores/index.js`
  - `original/public/js/secciones/trabajadores/formulario.js`
  - `original/public/js/secciones/trabajadores/antecedentes.js`
  - `original/public/js/secciones/trabajadores/listado.js`
- **Criterios de Aceptación (DoD):**
  - Reducción del tamaño individual de archivo a menos de 300 líneas.
  - Carga mediante `<script type="module">` sin alterar la experiencia de usuario.
  - Funcionamiento intacto de modales, autocompletado y subida de recaudos.

---

## Épica 3: Validación Documental con Códigos QR y Notificaciones SMTP

### [S] Tarea 3.1: Generación y Validación de Códigos QR en Comprobantes PDF
- **Descripción:** Integrar código QR de autenticidad en los comprobantes de prestaciones y cartas de aprobación generados por DomPDF.
- **Archivos Afectados:**
  - `original/app/Services/DocumentVerificationService.php` (nuevo)
  - `original/resources/views/pdf/comprobante.blade.php`
  - `original/routes/web.php` (ruta `/verificar/{token}`)
- **Criterios de Aceptación (DoD):**
  - El QR se renderiza nítidamente en el PDF sin requerir conexión a internet para generarse.
  - Al escanear el código QR con cualquier teléfono o lector, se muestra una página que certifica los datos del trabajador, fecha de emisión y autenticidad del documento.

### [S] Tarea 3.2: Notificaciones por Correo Electrónico (SMTP)
- **Descripción:** Configurar envío de correos transaccionales ante hitos del flujo de jubilación.
- **Archivos Afectados:**
  - `original/app/Mail/SolicitudAprobadaMail.php` (nuevo)
  - `original/app/Mail/ExpedienteCompletoMail.php` (nuevo)
  - `original/app/Services/NotificationService.php`
- **Criterios de Aceptación (DoD):**
  - Notificación enviada al correo del trabajador y al analista responsable.
  - Si el servidor de correo no está disponible o falla, la operación principal no se interrumpe (manejo de fallos con log de advertencia).

---

## Épica 4: Constructor Interactivo de Fórmulas de Prestaciones

### [S] Tarea 4.1: Motor Seguro de Evaluación de Expresiones (`FormulaEvaluationEngine`)
- **Descripción:** Desarrollar un motor de evaluación matemática que interprete de forma segura fórmulas parametrizadas sin usar `eval()`.
- **Archivos Afectados:**
  - `original/app/Services/FormulaEvaluationEngine.php` (nuevo)
  - `original/app/Http/Controllers/FormulaPrestacionController.php`
- **Criterios de Aceptación (DoD):**
  - Soporta variables `{sueldo_base}`, `{sueldo_integral}`, `{anios_servicio}`, `{total_primas}`.
  - Valida sintaxis antes de guardar para evitar errores matemáticos en producción.

### [S] Tarea 4.2: Interfaz Visual para Administración de Fórmulas
- **Descripción:** Crear una interfaz gráfica donde el administrador puede insertar variables y operadores haciendo clic en botones, con una consola de prueba (*sandbox*).
- **Archivos Afectados:**
  - `original/resources/views/dashboard/secciones/formulas.blade.php`
  - `original/public/js/secciones/formulas/builder.js` (nuevo)
- **Criterios de Aceptación (DoD):**
  - El administrador puede probar una fórmula con datos de un trabajador antes de activarla.
  - Historial de cambios y activación/desactivación de fórmulas con un clic.

---

## Épica 5: Unificación del Repositorio y Pruebas Automatizadas

### [M] Tarea 5.1: Consolidación a Fuente Canónica Única
- **Descripción:** Retirar copias duplicadas del código en `windows/` y `linux/`, dejando la raíz o `original/` como único código fuente de desarrollo.
- **Archivos Afectados:**
  - Directorios `windows/` y `linux/`
  - `.gitignore`
  - `crear-portable.ps1`
- **Criterios de Aceptación (DoD):**
  - Los scripts de instalación toman el código de la fuente única.
  - Disminución del tamaño del repositorio y eliminación de desincronizaciones en Git.

### [M] Tarea 5.2: Suite de Pruebas Unitarias de Cálculos Financieros
- **Descripción:** Crear suite de tests automatizados con PHPUnit para certificar fórmulas y liquidaciones.
- **Archivos Afectados:**
  - `original/tests/Unit/PrestacionCalculationTest.php` (nuevo)
  - `original/tests/Feature/SolicitudJubilacionTest.php` (nuevo)
- **Criterios de Aceptación (DoD):**
  - 100% de éxito en ejecución con `php artisan test`.
  - Validación de compatibilidad probada tanto en MySQL como en PostgreSQL.

---

## Checklist de Progreso e Hitos de Entrega

- [ ] **Hito 1 (Seguridad y Núcleo):** Tareas 1.1, 1.2, 1.3
- [ ] **Hito 2 (Frontend Modular ES6):** Tareas 2.1, 2.2
- [ ] **Hito 3 (QR y Notificaciones SMTP):** Tareas 3.1, 3.2
- [ ] **Hito 4 (Constructor de Fórmulas):** Tareas 4.1, 4.2
- [ ] **Hito 5 (Unificación y Pruebas):** Tareas 5.1, 5.2
