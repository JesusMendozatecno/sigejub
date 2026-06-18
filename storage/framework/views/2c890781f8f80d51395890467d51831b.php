
<style>
.dark-mode .diagram-card { background:#1e293b !important; border-color:#334155 !important; }
.dark-mode .diagram-card h2 { color:#f1f5f9 !important; }
.dark-mode .diagram-card p { color:#94a3b8 !important; }
.dark-mode .tab-diag { background:#1e293b !important; color:#94a3b8 !important; }
.dark-mode .tab-diag.active, .dark-mode .tab-diag[style*="background: #2563eb"] { background:#2563eb !important; color:#fff !important; }
.dark-mode .tab-nav-diagramas { border-bottom-color:#334155 !important; }
</style>
<header class="section-header-flex">
    <div class="header-info">
        <h1>Diagramas del Sistema</h1>
        <p>Diagramas UML, ER y de flujo del sistema SIGEJUB</p>
    </div>
    <div class="header-actions">
        <div class="actions-group">
            <button class="btn-export-pdf" id="btnExportarDiag" type="button" onclick="exportarDiagrama()">
                <i class="fas fa-download"></i>
                <span>Exportar imagen</span>
            </button>
        </div>
    </div>
</header>

<div class="tab-nav-diagramas" style="display:flex;gap:6px;margin-top:20px;flex-wrap:wrap;border-bottom:2px solid #e2e8f0;padding-bottom:0;">
    <button class="tab-diag active" data-diag="flujo" style="padding:10px 18px;border:none;background:#f1f5f9;color:#475569;font-size:13px;font-weight:600;border-radius:8px 8px 0 0;cursor:pointer;transition:all 0.2s;">
        <i class="fas fa-arrow-right-arrow-left" style="margin-right:6px;"></i>Flujo
    </button>
    <button class="tab-diag" data-diag="clase-modelos" style="padding:10px 18px;border:none;background:#f1f5f9;color:#475569;font-size:13px;font-weight:600;border-radius:8px 8px 0 0;cursor:pointer;transition:all 0.2s;">
        <i class="fas fa-sitemap" style="margin-right:6px;"></i>Clase (Modelos)
    </button>
    <button class="tab-diag" data-diag="clase-controladores" style="padding:10px 18px;border:none;background:#f1f5f9;color:#475569;font-size:13px;font-weight:600;border-radius:8px 8px 0 0;cursor:pointer;transition:all 0.2s;">
        <i class="fas fa-cogs" style="margin-right:6px;"></i>Clase (Controladores)
    </button>
    <button class="tab-diag" data-diag="logico" style="padding:10px 18px;border:none;background:#f1f5f9;color:#475569;font-size:13px;font-weight:600;border-radius:8px 8px 0 0;cursor:pointer;transition:all 0.2s;">
        <i class="fas fa-table" style="margin-right:6px;"></i>Modelo Lógico
    </button>
    <button class="tab-diag" data-diag="er" style="padding:10px 18px;border:none;background:#f1f5f9;color:#475569;font-size:13px;font-weight:600;border-radius:8px 8px 0 0;cursor:pointer;transition:all 0.2s;">
        <i class="fas fa-database" style="margin-right:6px;"></i>E-R
    </button>
    <button class="tab-diag" data-diag="casos" style="padding:10px 18px;border:none;background:#f1f5f9;color:#475569;font-size:13px;font-weight:600;border-radius:8px 8px 0 0;cursor:pointer;transition:all 0.2s;">
        <i class="fas fa-users-gear" style="margin-right:6px;"></i>Casos de Uso
    </button>
</div>

<div class="diagram-container">
    <div class="diagram-panel active" id="diag-flujo">
        <div class="diagram-card" style="background:#fff;border-radius:12px;padding:30px;margin-top:20px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border:1px solid #e2e8f0;">
            <pre class="mermaid" style="text-align:center;">
                graph LR
                    INICIO([Ingreso al Sistema]) --> LOGIN{¿Sesión activa?}
                    LOGIN -->|No| F_LOGIN[Formulario Login]
                    LOGIN -->|Sí| DASHBOARD

                    F_LOGIN --> CRED{¿Credenciales válidas?}
                    CRED -->|No| F_LOGIN
                    CRED -->|Sí| DASHBOARD

                    subgraph Registro
                        REG[Formulario Registro] --> VALID{Validar datos}
                        VALID -->|Datos completos| CREAR[Crear Usuario]
                        CREAR --> ACTIVITY[Registrar actividad en bitácora]
                        ACTIVITY --> REG_OK[Redirigir a Login]
                    end

                    DASHBOARD --> SECCIONES{Seleccionar módulo}
                    SECCIONES --> TRABAJADORES[Gestión Trabajadores]
                    SECCIONES --> SOLICITUDES[Gestión Solicitudes]
                    SECCIONES --> EXPEDIENTES[Gestión Expedientes]
                    SECCIONES --> PRESTACIONES[Gestión Prestaciones]
                    SECCIONES --> PERFIL[Perfil / Configuración]

                    subgraph Trabajadores
                        TRABAJADORES --> T_LIST[Listar / Buscar]
                        TRABAJADORES --> T_CREATE[Registrar]
                        TRABAJADORES --> T_EDIT[Editar]
                        TRABAJADORES --> T_DELETE[Eliminar]
                        T_CREATE --> T_CALC[Calcular edad y años servicio]
                    end

                    subgraph Solicitudes
                        SOLICITUDES --> S_LIST[Listar / Filtrar]
                        SOLICITUDES --> S_CREATE[Crear]
                        SOLICITUDES --> S_ESTADO[Cambiar Estado]
                        S_CREATE --> S_VALID{¿Trabajador sin solicitud activa?}
                        S_VALID -->|Sí| S_SAVE[Guardar Solicitud]
                        S_VALID -->|No| S_ERROR[Error: ya tiene solicitud]
                        S_ESTADO --> S_PEND[Pendiente]
                        S_PEND --> S_REV[En Revisión]
                        S_REV --> S_APR{Aprobado?}
                        S_APR -->|Sí| S_OK[Aprobado]
                        S_APR -->|No| S_NO[Rechazado]
                    end

                    subgraph Expedientes
                        EXPEDIENTES --> E_BUSCAR[Buscar por Cédula]
                        E_BUSCAR --> E_CREAR[Crear Expediente]
                        E_CREAR --> E_FOTO[Subir Foto Carnet]
                        E_CREAR --> E_DOCS[Subir Documentos]
                        E_DOCS --> E_VALIDAR[Validar Documentos]
                        E_VALIDAR --> E_APR[Aprobado]
                        E_VALIDAR --> E_REC[Rechazado + nota]
                        E_VALIDAR --> E_GLOBAL[Recalcular % Estado Global]
                    end

                    subgraph Prestaciones
                        PRESTACIONES --> P_LIST[Listar Prestaciones]
                        PRESTACIONES --> P_CALC[Calcular Monto]
                        P_CALC --> P_FORM[Sueldo base, primas, %]
                        P_FORM --> P_RESULT[Mostrar resultado final]
                    end

                    S_OK -.-> EXPEDIENTES
                    E_GLOBAL -.-> PRESTACIONES
            </pre>
        </div>
    </div>

    <div class="diagram-panel" id="diag-clase-modelos" style="display:none;">
        <div class="diagram-card" style="background:#fff;border-radius:12px;padding:30px;margin-top:20px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border:1px solid #e2e8f0;">
            <pre class="mermaid" style="text-align:center;">
                classDiagram
                    class User {
                        -id: bigIncrements PK
                        -nombre: string
                        -apellido: string
                        -correo: string UK
                        -password: string
                        -rol: enum[admin, analista]
                        -avatar: string|null
                        -tema: string
                        -idioma: string
                        -color_acento: string
                        -verificacion_dos_pasos: boolean
                        -ultimo_acceso: datetime|null
                        -ultimo_acceso_ip: string|null
                        +login() bool
                        +logout() void
                    }

                    class Trabajador {
                        -id: bigIncrements PK
                        -cedula: string UK
                        -nombres: string
                        -apellidos: string
                        -genero: enum[M, F]
                        -grado_nivel: string
                        -cargo: string
                        -unidad_departamento: string
                        -fecha_nacimiento: date
                        -edad: int
                        -fecha_ingreso: date
                        -anos_servicio_inst: int
                        -anos_servicio_externo: int
                        -total_anos_servicio: int
                        -nivel_instruccion: int
                        -numero_hijos: int
                        -hijos_discapacidad: int
                        -porcentaje_antiguedad: decimal
                        -porcentaje_caja_ahorro: decimal
                        -deleted_at: timestamp|null
                        +getEstatusAttribute() string
                    }

                    class Solicitud {
                        -id: bigIncrements PK
                        -trabajador_id: foreignId FK
                        -fecha_solicitud: date
                        -periodo: string|null
                        -tipo_jubilacion: string|null
                        -observaciones: text|null
                        -estado: enum[pendiente, revision, aprobado, rechazado]
                        +trabajador() BelongsTo
                    }

                    class Expediente {
                        -id: bigIncrements PK
                        -trabajador_id: foreignId FK
                        -solicitud_id: foreignId FK
                        -foto_carnet: string|null
                        -estado_global: int [0-100]
                        -notas_admin: text|null
                        +trabajador() BelongsTo
                        +solicitud() BelongsTo
                        +documentos() HasMany
                    }

                    class Documento {
                        -id: bigIncrements PK
                        -expediente_id: foreignId FK
                        -nombre: string
                        -archivo: string
                        -estado: enum[en_revision, aprobado, rechazado]
                        -nota_rechazo: text|null
                        +expediente() BelongsTo
                    }

                    class Prestacion {
                        -id: bigIncrements PK
                        -trabajador_id: foreignId FK
                        -anios_servicio: int
                        -monto: decimal(12,2)
                        +trabajador() BelongsTo
                    }

                    class Activity {
                        -id: bigIncrements PK
                        -user_id: foreignId FK|null
                        -accion: string [created, updated, deleted]
                        -tipo_entidad: string
                        -entidad_id: int|null
                        -descripcion: string
                        -direccion_ip: string|null
                        -navegador: text|null
                        -valores_anteriores: array|null
                        -valores_nuevos: array|null
                        -datos_peticion: array|null
                        +log() Activity
                        +user() BelongsTo
                    }

                    class UserNotification {
                        -id: bigIncrements PK
                        -user_id: foreignId FK
                        -from_user_id: foreignId FK|null
                        -titulo: string
                        -mensaje: text
                        -tipo: string [info, warning, success, error]
                        -leida: boolean
                        +user() BelongsTo
                        +fromUser() BelongsTo
                    }

                    User "1" --> "N" Activity : user_id
                    User "1" --> "N" UserNotification : user_id
                    User "1" --> "N" UserNotification : from_user_id

                    Trabajador "1" --> "N" Solicitud : trabajador_id
                    Trabajador "1" --> "1" Expediente : trabajador_id
                    Trabajador "1" --> "N" Prestacion : trabajador_id

                    Solicitud "1" --> "1" Expediente : solicitud_id

                    Expediente "1" --> "N" Documento : expediente_id
            </pre>
        </div>
    </div>

    <div class="diagram-panel" id="diag-clase-controladores" style="display:none;">
        <div class="diagram-card" style="background:#fff;border-radius:12px;padding:30px;margin-top:20px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border:1px solid #e2e8f0;">
            <pre class="mermaid" style="text-align:center;">
                classDiagram
                    class AuthController {
                        +loginForm() View
                        +login(Request) JSON|Redirect
                        +registerForm() View
                        +register(Request) Redirect
                        +dashboard() View
                        +logout() Redirect
                    }

                    class UserController {
                        +index() View
                        +updateProfile(Request) JSON
                        +uploadAvatar(Request) JSON
                        +deleteAvatar() JSON
                        +updatePassword(Request) JSON
                        +updateSettings(Request) JSON
                        +updateNotifications(Request) JSON
                        +toggle2FA(Request) JSON
                        +getSessions() JSON
                        +destroySession(id) JSON
                        +destroyOtherSessions() JSON
                        +getActivity() JSON
                        +getStats() JSON
                        +adminUsers(Request) JSON
                        +adminUpdateUser(Request, id) JSON
                        +adminDeleteUser(id) JSON
                        +adminActivity() JSON
                        +adminGlobalConfig(Request) JSON
                    }

                    class TrabajadorController {
                        +index(Request) JSON
                        +show(id) JSON
                        +store(Request) JSON
                        +update(Request, id) JSON
                        +destroy(id) JSON
                        +dashboardStats() JSON
                    }

                    class SolicitudController {
                        +index(Request) JSON
                        +porMes() JSON
                        +vencimientos() JSON
                        +show(id) JSON
                        +store(Request) JSON
                        +update(Request, id) JSON
                        +destroy(id) JSON
                        +exportarPDF(Request) PDF
                    }

                    class ExpedienteController {
                        +index() JSON
                        +buscarTrabajador(Request) JSON
                        +store(Request) JSON
                        +show(id) JSON
                        +subirDocumento(Request, id) JSON
                        +reemplazarDocumento(Request, id) JSON
                        +updateDocumentoStatus(Request, id) JSON
                        +updateNotas(Request, id) JSON
                        +recalcularEstadoGlobal(id) void
                    }

                    class PrestacionesController {
                        +index() JSON
                        +show(id) JSON
                        +store(Request) JSON
                    }

                    class AdminController {
                        +usuarios(Request) View|JSON
                        +showUsuario(id) JSON
                        +updateUsuario(Request, id) JSON
                        +actividades(Request) JSON
                        +actividadResumen(Request) JSON
                        +enviarNotificacion(Request) JSON
                        +misNotificaciones() JSON
                        +notificacionesNoLeidas() JSON
                        +marcarLeida(id) JSON
                        +marcarTodasLeidas() JSON
                    }

                    class CajaNegraController {
                        -verifyAdmin() void
                        +index(Request) JSON
                        +show(id) JSON
                        +stats() JSON
                        +exportar(Request) PDF
                        +usuarios() JSON
                    }

                    class ActivityController {
                        +index() JSON
                    }

                    class ChangelogController {
                        +index() JSON
                        +view() View
                        +generate() JSON
                    }
            </pre>
        </div>
    </div>

    <div class="diagram-panel" id="diag-logico" style="display:none;">
        <div class="diagram-card" style="background:#fff;border-radius:12px;padding:30px;margin-top:20px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border:1px solid #e2e8f0;">
            <pre class="mermaid" style="text-align:center;">
                erDiagram
                    users ||--o{ activities : "user_id FK"
                    users ||--o{ notifications : "user_id FK"
                    users ||--o{ notifications : "from_user_id FK"
                    users ||--o{ sessions : "user_id FK"

                    trabajadores ||--o{ solicitudes : "trabajador_id FK"
                    trabajadores ||--o{ prestaciones : "trabajador_id FK"
                    trabajadores ||--o{ expedientes : "trabajador_id FK"

                    solicitudes ||--o{ expedientes : "solicitud_id FK"

                    expedientes ||--o{ documentos : "expediente_id FK"

                    users {
                        bigint id PK
                        string nombre
                        string apellido
                        string correo UK
                        string password
                        enum rol "admin | analista"
                        string avatar "nullable"
                        string tema
                        string idioma
                        string color_acento
                        boolean verificacion_dos_pasos
                        timestamp ultimo_acceso "nullable"
                        string ultimo_acceso_ip "nullable"
                    }

                    trabajadores {
                        bigint id PK
                        string cedula UK
                        string nombres
                        string apellidos
                        string cuenta_bancaria "nullable"
                        enum genero
                        string grado_nivel
                        string cargo
                        string unidad_departamento
                        date fecha_nacimiento
                        int edad
                        date fecha_ingreso
                        int anos_servicio_inst
                        int anos_servicio_externo
                        int total_anos_servicio
                        int nivel_instruccion
                        int numero_hijos
                        int hijos_discapacidad
                        decimal porcentaje_antiguedad
                        decimal porcentaje_caja_ahorro
                        timestamp deleted_at "nullable"
                    }

                    solicitudes {
                        bigint id PK
                        bigint trabajador_id FK
                        date fecha_solicitud
                        string periodo "nullable"
                        string tipo_jubilacion "nullable"
                        text observaciones "nullable"
                        enum estado "pendiente|revision|aprobado|rechazado"
                    }

                    expedientes {
                        bigint id PK
                        bigint trabajador_id FK
                        bigint solicitud_id FK
                        string foto_carnet "nullable"
                        int estado_global "0-100"
                        text notas_admin "nullable"
                    }

                    documentos {
                        bigint id PK
                        bigint expediente_id FK
                        string nombre
                        string archivo
                        enum estado "en_revision|aprobado|rechazado"
                        text nota_rechazo "nullable"
                    }

                    prestaciones {
                        bigint id PK
                        bigint trabajador_id FK
                        int anios_servicio
                        decimal monto
                    }

                    activities {
                        bigint id PK
                        bigint user_id FK "nullable"
                        string accion "created|updated|deleted"
                        string tipo_entidad
                        bigint entidad_id "nullable"
                        string descripcion
                        string direccion_ip "nullable"
                        text navegador "nullable"
                        longtext valores_anteriores "nullable"
                        longtext valores_nuevos "nullable"
                        index ix_created_at
                        index ix_accion
                    }

                    notifications {
                        bigint id PK
                        bigint user_id FK
                        bigint from_user_id FK "nullable"
                        string titulo
                        text mensaje
                        string tipo "info|warning|success|error"
                        boolean leida
                    }

                    changelogs {
                        bigint id PK
                        string nombre_autor
                        string correo_autor
                        string hash_commit UK
                        text mensaje_commit
                        text descripcion "nullable"
                        string tipo "default: change"
                        string seccion "nullable"
                    }

                    sessions {
                        string id PK
                        bigint user_id FK "nullable"
                        string direccion_ip "nullable"
                        text navegador "nullable"
                        longtext payload
                        int ultima_actividad
                    }
            </pre>
        </div>
    </div>

    <div class="diagram-panel" id="diag-er" style="display:none;">
        <div class="diagram-card" style="background:#fff;border-radius:12px;padding:30px;margin-top:20px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border:1px solid #e2e8f0;">
            <pre class="mermaid" style="text-align:center;">
                erDiagram
                    USUARIO ||--o{ ACTIVIDAD : realiza
                    USUARIO ||--o{ NOTIFICACION : notifica
                    USUARIO ||--o{ SESION : inicia

                    TRABAJADOR ||--o{ SOLICITUD : presenta
                    TRABAJADOR ||--|| EXPEDIENTE : "tiene 1"
                    TRABAJADOR ||--o{ PRESTACION : recibe

                    SOLICITUD ||--|| EXPEDIENTE : genera

                    EXPEDIENTE ||--o{ DOCUMENTO : contiene

                    USUARIO {
                        string correo PK
                        string nombre
                        string apellido
                        string rol
                        string contraseña
                    }

                    TRABAJADOR {
                        string cedula PK
                        string nombres
                        string apellidos
                        string cargo
                        int total_anos_servicio
                    }

                    SOLICITUD {
                        int id PK
                        date fecha_solicitud
                        string tipo_jubilacion
                        enum estado
                    }

                    EXPEDIENTE {
                        int id PK
                        string foto_carnet
                        int estado_global
                        text notas_admin
                    }

                    DOCUMENTO {
                        int id PK
                        string nombre
                        string archivo
                        enum estado
                    }

                    PRESTACION {
                        int id PK
                        int anios_servicio
                        decimal monto
                    }

                    ACTIVIDAD {
                        int id PK
                        string accion
                        string tipo_entidad
                        string descripcion
                        datetime fecha
                    }

                    NOTIFICACION {
                        int id PK
                        string titulo
                        string mensaje
                        boolean leida
                    }

                    SESION {
                        string id PK
                        string direccion_ip
                        string navegador
                    }
            </pre>
        </div>
    </div>

    <div class="diagram-panel" id="diag-casos" style="display:none;">
        <div class="diagram-card" style="background:#fff;border-radius:12px;padding:30px;margin-top:20px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border:1px solid #e2e8f0;">
            <pre class="mermaid" style="text-align:center;">
                graph LR
                    classDef actor fill:#e0f2fe,stroke:#0284c7,stroke-width:2px
                    classDef admin fill:#fef2f2,stroke:#dc2626,stroke-width:2px

                    V((Visitante)):::actor
                    A((Analista)):::actor
                    ADM((Administrador)):::admin

                    subgraph Autenticacion
                        U1[Iniciar Sesión]
                        U2[Cerrar Sesión]
                        U3[Registrarse]
                    end

                    subgraph Trabajadores
                        T1[Registrar]
                        T2[Editar]
                        T3[Eliminar]
                        T4[Consultar]
                        T5[Estadísticas]
                    end

                    subgraph Solicitudes
                        S1[Crear]
                        S2[Cambiar Estado]
                        S3[Filtrar]
                        S4[Exportar PDF]
                    end

                    subgraph Expedientes
                        E1[Crear]
                        E2[Subir Docs]
                        E3[Validar]
                        E4[Reemplazar]
                        E5[Estado Global]
                    end

                    subgraph Prestaciones
                        P1[Calcular]
                        P2[Consultar]
                    end

                    subgraph Reportes
                        R1[Ver Reportes]
                        R2[Exportar]
                    end

                    subgraph Admin
                        AD1[Usuarios]
                        AD2[Auditoría]
                        AD3[Notificaciones]
                        AD4[Configuración]
                        AD5[Caja Negra]
                    end

                    subgraph Perfil
                        PR1[Perfil]
                        PR2[Contraseña]
                        PR3[Avatar]
                        PR4[Tema]
                        PR5[Actividad]
                        PR6[Sesiones]
                        PR7[2FA]
                    end

                    V --> U1 & U3
                    A --> U1 & U2
                    ADM --> U1 & U2

                    A --> T1 & T2 & T4 & T5
                    ADM --> T1 & T2 & T3 & T4 & T5

                    A --> S1 & S2 & S3 & S4
                    ADM --> S1 & S2 & S3 & S4

                    A --> E1 & E2 & E3 & E4 & E5
                    ADM --> E1 & E2 & E3 & E4 & E5

                    A --> P1 & P2
                    ADM --> P1 & P2

                    A --> R1 & R2
                    ADM --> R1 & R2

                    ADM --> AD1 & AD2 & AD3 & AD4 & AD5

                    A --> PR1 & PR2 & PR3 & PR4 & PR5 & PR6
                    ADM --> PR1 & PR2 & PR3 & PR4 & PR5 & PR6 & PR7
            </pre>
        </div>
    </div>
</div>

<script src="<?php echo e(asset('js/mermaid/mermaid.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/mermaid/html-to-image.js')); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    mermaid.initialize({
        startOnLoad: false,
        theme: 'base',
        themeVariables: {
            primaryColor: '#2563eb',
            primaryTextColor: '#ffffff',
            primaryBorderColor: '#1d4ed8',
            lineColor: '#64748b',
            secondaryColor: '#f1f5f9',
            tertiaryColor: '#f8fafc',
            fontFamily: 'system-ui, sans-serif',
            fontSize: '18px',
        },
        flowchart: { useMaxWidth: true, htmlLabels: true, curve: 'basis', padding: 20, nodeSpacing: 60, rankSpacing: 80 },
        sequence: { useMaxWidth: true },
        class: { useMaxWidth: true },
    });

    var panelActual = 'diag-flujo';

    var renderDiag = function(panelId) {
        var panel = document.getElementById(panelId);
        var el = panel?.querySelector('.mermaid');
        if (el && !el.dataset.rendered) {
            mermaid.run({ nodes: [el] }).then(function() {
                el.dataset.rendered = '1';
            }).catch(function(){});
        }
    };

    document.querySelectorAll('.tab-diag').forEach(function(tab) {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.tab-diag').forEach(function(t) {
                t.style.background = '#f1f5f9';
                t.style.color = '#475569';
            });
            this.style.background = '#2563eb';
            this.style.color = '#ffffff';
            document.querySelectorAll('.diagram-panel').forEach(function(p) { p.style.display = 'none'; });
            panelActual = 'diag-' + this.dataset.diag;
            document.getElementById(panelActual).style.display = 'block';
            renderDiag(panelActual);
        });
    });

    var seccion = document.getElementById('diagramas');
    var obs = new MutationObserver(function() {
        if (seccion.style.display !== 'none' && window.getComputedStyle(seccion).display !== 'none') {
            renderDiag('diag-flujo');
        }
    });
    obs.observe(seccion, { attributes: true, attributeFilter: ['style', 'class'] });

    window.exportarDiagrama = function() {
        var panel = document.getElementById(panelActual);
        var target = panel?.querySelector('.diagram-card');
        if (!target) { mostrarToast('Primero espera a que se renderice el diagrama', 'warning'); return; }

        var nombre = panelActual.replace('diag-', '');
        var nombres = {
            flujo: 'diagrama-flujo',
            'clase-modelos': 'diagrama-clase-modelos',
            'clase-controladores': 'diagrama-clase-controladores',
            logico: 'modelo-logico-relacional',
            er: 'modelo-entidad-relacion',
            casos: 'diagrama-casos-de-uso'
        };
        var nombreArchivo = nombres[nombre] || 'diagrama';

        var svg = target.querySelector('svg');
        if (!svg) { mostrarToast('No se encontró el diagrama', 'warning'); return; }

        var origStyle = {
            w: target.style.width, mw: target.style.maxWidth, ov: target.style.overflow,
            h: target.style.height,
            sw: svg.style.width, sh: svg.style.height, smw: svg.style.maxWidth
        };

        var vb = svg.getAttribute('viewBox');
        if (!vb) { mostrarToast('El diagrama aún no se ha renderizado', 'warning'); return; }
        var parts = vb.split(' ').map(Number);
        var vbW = parts[2], vbH = parts[3];
        var padding = 60;
        var totalW = vbW + padding;
        var totalH = vbH + padding;

        svg.style.maxWidth = 'none';
        svg.style.width = vbW + 'px';
        svg.style.height = vbH + 'px';

        target.style.width = totalW + 'px';
        target.style.height = totalH + 'px';
        target.style.maxWidth = 'none';
        target.style.overflow = 'visible';

        htmlToImage.toPng(target, {
            backgroundColor: document.body.classList.contains('dark-mode') ? '#1e293b' : '#ffffff',
            pixelRatio: 3
        }).then(function(dataUrl) {
            target.style.width = origStyle.w;
            target.style.height = origStyle.h;
            target.style.maxWidth = origStyle.mw;
            target.style.overflow = origStyle.ov;
            svg.style.maxWidth = origStyle.smw;
            svg.style.width = origStyle.sw;
            svg.style.height = origStyle.sh;
            var link = document.createElement('a');
            link.download = nombreArchivo + '.png';
            link.href = dataUrl;
            link.click();
            mostrarToast('Diagrama exportado como PNG', 'success');
        }).catch(function(err) {
            target.style.width = origStyle.w;
            target.style.height = origStyle.h;
            target.style.maxWidth = origStyle.mw;
            target.style.overflow = origStyle.ov;
            svg.style.maxWidth = origStyle.smw;
            svg.style.width = origStyle.sw;
            svg.style.height = origStyle.sh;
            var svgElem = panel?.querySelector('svg');
            if (!svgElem) return;
            var serializer = new XMLSerializer();
            var svgStr = serializer.serializeToString(svgElem);
            var blob = new Blob([svgStr], { type: 'image/svg+xml;charset=utf-8' });
            var url = URL.createObjectURL(blob);
            var link = document.createElement('a');
            link.download = nombreArchivo + '.svg';
            link.href = url;
            link.click();
            mostrarToast('Exportado como SVG (el navegador no soporta PNG)', 'info');
        });
    };
});
</script>
<?php /**PATH C:\xampp\htdocs\sigejub 2\resources\views\dashboard\secciones\diagramas.blade.php ENDPATH**/ ?>