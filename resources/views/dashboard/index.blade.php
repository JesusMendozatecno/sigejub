<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGEJUB - Sistema de Jubilaciones</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard/dashboard.css') }}">
    <!-- Estilos Base y Layout -->
    <link rel="stylesheet" href="{{ asset('css/dashboard/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/components.css') }}">

    <!-- Estilos por Sección -->
    <link rel="stylesheet" href="{{ asset('css/dashboard/secciones/inicio.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/secciones/solicitud.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/secciones/expediente.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/secciones/trabajadores.css') }}">
     <link rel="stylesheet" href="{{ asset('css/dashboard/secciones/trabajadores2.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/secciones/prestaciones.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/secciones/modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard/secciones/reportes.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Estilos rápidos para funcionalidad de pestañas */
        .content-section { display: none; animation: fadeIn 0.3s ease; }
        .content-section.active { display: block; }
        .menu-item { cursor: pointer; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
        /* Ajustes estéticos para parecerse a las imágenes */
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; }
        .status-activo { background: #e6fcf5; color: #0ca678; }
        .status-jubilado { background: #e7f5ff; color: #1c7ed6; }
        .status-suspension { background: #fff5f5; color: #fa5252; }
        .data-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo-box" style="background: #1a365d; color: white; padding: 8px; border-radius: 8px;">🏛️</div>
        <div>
            <h3 style="margin:0; font-size: 1.1rem;">Gestión Jubilaciones</h3>
            <span style="font-size: 0.7rem; color: #667085;">ARQUITECTURA DE CONFIANZA</span>
        </div>
    </div>

    <nav class="sidebar-menu">
        <ul>
            <li class="menu-item active" data-target="inicio"><i data-lucide="home"></i> Inicio</li>
            <li class="menu-item" data-target="trabajadores"><i data-lucide="users"></i> Trabajadores</li>
            <li class="menu-item" data-target="solicitudes"><i data-lucide="file-text"></i> Solicitudes</li>
            <li class="menu-item" data-target="expedientes"><i data-lucide="folder"></i> Expedientes</li>
            <li class="menu-item" data-target="prestaciones"><i data-lucide="wallet"></i> Prestaciones</li>
            <li class="menu-item" data-target="reportes"><i data-lucide="bar-chart-3"></i> Reportes</li>
        </ul>
    </nav>

    <div class="sidebar-footer" style="padding: 20px; border-top: 1px solid #eee;">
    <a href="{{ route('usuarios.user') }}" class="user-profile-mini" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit; padding: 8px; border-radius: 8px; transition: background 0.2s;">
        <style>
            .user-profile-mini:hover { background-color: #f1f5f9; }
        </style>
        
        <div class="avatar" style="width: 35px; height: 35px; border-radius: 50%; background: #dbeafe; color: #1e3a8a; display:flex; align-items:center; justify-content:center;">
            <i data-lucide="settings" size="18"></i>
        </div>
        
        <div style="flex-grow: 1;">
            <p style="margin:0; font-size: 0.85rem; font-weight: bold; line-height: 1;">{{ Auth::user()->name }}</p>
            <span style="font-size: 0.75rem; color: #666;">Gestionar cuenta</span>
        </div>
        
        <i data-lucide="chevron-right" size="14" style="color: #94a3b8;"></i>
    </a>
</div>

</aside>

<main class="main-content">
    <header class="top-bar">
        <div class="search-container">
            <i data-lucide="search"></i>
            <input type="text" placeholder="Buscar por nombre o cédula...">
        </div>
        <div class="user-info" style="display:flex; gap: 20px; align-items: center;">
            <i data-lucide="bell"></i>
            <i data-lucide="settings"></i>
            <span style="color: #1a365d; font-weight: bold;">Sistema de Jubilaciones</span>
        </div>
    </header>

    <!-- SECCIÓN 1: INICIO (Dashboard General) -->
<div id="inicio" class="content-section active">
    
    <!-- Encabezado -->
    <header class="welcome-header">
        <div class="welcome-text">
            <p class="subtitle"></p>
            <h1>Panel de Gestión Integral</h1>
        </div>
        <button class="btn-primary" onclick="switchTab('solicitudes')">
            <i data-lucide="plus-circle"></i> Nueva Solicitud
        </button>
    </header>

    <!-- Tarjetas de Estadísticas -->
    <section class="stats-grid">
        <div class="stat-card">
            <div class="card-head">
                <div class="icon-wrap blue"><i data-lucide="users"></i></div>
                <span class="trend positive">+4%</span>
            </div>
            <p>TOTAL TRABAJADORES</p>
            <h2>1,240</h2>
        </div>

        <div class="stat-card">
            <div class="card-head">
                <div class="icon-wrap orange"><i data-lucide="clock"></i></div>
                <span class="trend neutral">Actual</span>
            </div>
            <p>PENDIENTES</p>
            <h2 class="text-orange">45</h2>
        </div>

        <div class="stat-card">
            <div class="card-head">
                <div class="icon-wrap green"><i data-lucide="check-circle"></i></div>
                <span class="trend positive">+12</span>
            </div>
            <p>APROBADAS</p>
            <h2 class="text-green">82</h2>
        </div>

        <div class="stat-card">
            <div class="card-head">
                <div class="icon-wrap red"><i data-lucide="x-circle"></i></div>
                <span class="trend negative">-2</span>
            </div>
            <p>RECHAZADAS</p>
            <h2 class="text-red">12</h2>
        </div>
    </section>

    <!-- Layout Central: Gráfico y Accesos -->
    <div class="content-layout">
        <!-- Gráfico -->
        <section class="chart-container">
            <div class="chart-header">
                <div>
                    <h3>Solicitudes por Mes</h3>
                    <p class="chart-subtitle">Volumen de tramitación - Año Actual</p>
                </div>
                <div class="chart-toggle">
                    <button class="active">Mensual</button>
                    <button>Anual</button>
                </div>
            </div>
            <div class="bar-chart">
                <div class="bar-group"><div class="bar" style="height: 40%;"></div><span>ENE</span></div>
                <div class="bar-group"><div class="bar" style="height: 60%;"></div><span>FEB</span></div>
                <div class="bar-group active"><div class="bar" style="height: 90%;"><span class="val">32</span></div><span>MAR</span></div>
                <div class="bar-group"><div class="bar" style="height: 70%;"></div><span>ABR</span></div>
                <div class="bar-group"><div class="bar" style="height: 80%;"></div><span>MAY</span></div>
                <div class="bar-group"><div class="bar" style="height: 50%;"></div><span>JUN</span></div>
            </div>
        </section>

        <!-- Accesos Rápidos -->
        <section class="quick-access-dark">
            <h3>Accesos Rápidos</h3>
            <p>Gestione los procesos fundamentales de forma directa.</p>
            <div class="access-grid">
                <div class="access-box" onclick="switchTab('trabajadores')">
                    <i data-lucide="users"></i>
                    <p>TRABAJADORES</p>
                </div>
                <div class="access-box" onclick="switchTab('prestaciones')">
                    <i data-lucide="wallet"></i>
                    <p>PRESTACIONES</p>
                </div>
                <div class="access-box" onclick="switchTab('reportes')">
                    <i data-lucide="bar-chart-3"></i>
                    <p>REPORTES</p>
                </div>
                <div class="access-box">
                    <i data-lucide="help-circle"></i>
                    <p>GUÍAS</p>
                </div>
            </div>
        </section>
    </div>

    <!-- Layout Inferior: Actividad y Vencimientos -->
    <div class="content-layout">
        <section class="recent-activity">
            <h3 class="section-title"><i data-lucide="history"></i> Actividad Reciente</h3>
            <div class="activity-list">
                <div class="activity-item">
                    <div class="activity-icon blue"><i data-lucide="file-up"></i></div>
                    <div class="activity-text">
                        <p>Juan Pérez subió su cédula</p>
                        <span>Expediente #9923 • Hace 15 min</span>
                    </div>
                    <a href="#">Ver</a>
                </div>
                <div class="activity-item">
                    <div class="activity-icon green"><i data-lucide="check-circle"></i></div>
                    <div class="activity-text">
                        <p>Solicitud #452 aprobada</p>
                        <span>Admin. Maria • Hace 2 horas</span>
                    </div>
                    <a href="#">Detalle</a>
                </div>
            </div>
        </section>

        <section class="deadlines-container">
            <div class="deadlines-card">
                <h3>Próximos Vencimientos</h3>
                <div class="deadline-item urgent">
                    <span class="tag">Urgente</span>
                    <p>Revisión de Cálculo: Ana Belén</p>
                    <span>Vence hoy, 16:00h</span>
                </div>
                <div class="deadline-item warning">
                    <span class="tag">Mañana</span>
                    <p>Firma de Dictamen #882</p>
                    <span>Pendiente de firma digital</span>
                </div>
            </div>
            <div class="info-banner-dark">
                <div class="info-head">
                    <i data-lucide="lightbulb"></i>
                    <h4>Dato Institucional</h4>
                </div>
                <p>Este mes se han procesado un 15% más de jubilaciones que el promedio anterior.</p>
            </div>
        </section>
    </div>
</div>


    <!-- SECCIÓN 2: TRABAJADORES -->
<div id="trabajadores" class="content-section">
    <header class="section-header">
        <div class="header-info">
            <h1>Directorio de Trabajadores</h1>
            <p>Gestione la información laboral y el estatus institucional de los miembros activos y jubilados.</p>
        </div>
        <button class="btn-primary-dark">
            <i data-lucide="user-plus"></i> Agregar Trabajador
        </button>
    </header>

    <!-- Filtros y Contador -->
    <section class="filters-row">
        <div class="filter-group">
            <label>FILTRAR POR CARGO</label>
            <select><option>Todos los cargos</option></select>
        </div>
        <div class="filter-group">
            <label>FILTRAR POR ESTATUS</label>
            <select><option>Cualquier estatus</option></select>
        </div>
        <div class="filter-group">
            <label>TIPO DE NÓMINA</label>
            <select><option>Todas</option></select>
        </div>
        <div class="total-badge-card">
            <div>
                <p>TOTAL REGISTRADOS</p>
                <h2>1,248</h2>
            </div>
            <i data-lucide="users" class="icon-bg"></i>
        </div>
    </section>

    <!-- Tabla de Datos -->
    <div class="data-table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>NOMBRE COMPLETO</th>
                    <th>CÉDULA</th>
                    <th>CARGO</th>
                    <th>TIPO</th>
                    <th>ESTATUS</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <!-- Fila 1 -->
                <tr>
                    <td>TR-8821</td>
                    <td>
                        <div class="user-cell">
                            <span class="avatar blue">AG</span>
                            <strong>Ana García</strong>
                        </div>
                    </td>
                    <td>12.345.678</td>
                    <td>Profesora Titular</td>
                    <td><span class="badge-type docente">DOCENTE</span></td>
                    <td><span class="dot active"></span> Activo</td>
                    <td class="actions">
                        <i data-lucide="folder-open"></i>
                        <i data-lucide="edit-3"></i>
                        <i data-lucide="trash-2"></i>
                    </td>
                </tr>
                <!-- Fila 2 -->
                <tr>
                    <td>TR-8822</td>
                    <td>
                        <div class="user-cell">
                            <span class="avatar purple">RM</span>
                            <strong>Ricardo Mendoza</strong>
                        </div>
                    </td>
                    <td>10.122.334</td>
                    <td>Analista de Sistemas</td>
                    <td><span class="badge-type admin">ADMINISTRATIVO</span></td>
                    <td><span class="dot jubilado"></span> Jubilado</td>
                    <td class="actions">
                        <i data-lucide="folder-open"></i>
                        <i data-lucide="edit-3"></i>
                        <i data-lucide="trash-2"></i>
                    </td>
                </tr>
            </tbody>
        </table>
        <!-- Paginación -->
        <div class="table-footer">
            <span>Mostrando 1 - 10 de 1,248 trabajadores</span>
            <div class="pagination">
                <button>&lt;</button>
                <button class="active">1</button>
                <button>2</button>
                <button>3</button>
                <span>...</span>
                <button>125</button>
                <button>&gt;</button>
            </div>
        </div>
    </div>

    <!-- Widgets Inferiores -->
    <div class="content-layout">
        <div class="promo-card-blue">
            <div class="promo-content">
                <h3>Próximas Jubilaciones</h3>
                <p>Hay 14 docentes que cumplen los requisitos de años de servicio este trimestre. Inicie el proceso de revisión de expedientes.</p>
                <button class="btn-white">Ver Calendario</button>
            </div>
            <div class="promo-icon-watermark">
                <i data-lucide="scroll"></i>
            </div>
        </div>

        <div class="audit-card">
            <div class="audit-header">
                <i data-lucide="check-circle-2"></i> AUDITORÍA AL DÍA
            </div>
            <h3>Estatus de Datos</h3>
            <p>El 98.4% de los expedientes cuentan con documentación digitalizada completa.</p>
            <div class="progress-container">
                <div class="progress-bar" style="width: 98%;"></div>
            </div>
            <span class="progress-val">98%</span>
        </div>
    </div>
</div>

<div id="modalTrabajador" class="modal-overlay">
    <div class="modal-container">
        <aside class="modal-sidebar">
            <span class="badge-new">Sigejub v1.0</span>
            <h1>Registrar<br>Nuevo<br>Trabajador</h1>
            <p>Complete el expediente institucional para iniciar el cálculo de antigüedad y estatus jubilatorio.</p>
            
            <div style="margin-top: auto; font-size: 0.75rem; color: #64748b;">
                <i data-lucide="info" style="width: 14px; vertical-align: middle;"></i> 
                Asegúrese de que la cédula sea exacta para evitar duplicados.
            </div>
        </aside>

        <main class="modal-form-content">
            <button class="btn-close-absolute" id="closeModal" type="button">&times;</button>
            
            <form id="formTrabajador">
                @csrf <section class="form-section">
                    <h3><i data-lucide="user"></i> Datos Personales</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>CÉDULA DE IDENTIDAD</label>
                            <input type="text" name="cedula" required placeholder="V-00000000">
                        </div>
                        <div class="input-group">
                            <label>GÉNERO</label>
                            <select name="genero" required>
                                <option value="" disabled selected>Seleccione...</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>NOMBRES</label>
                            <input type="text" name="nombres" required>
                        </div>
                        <div class="input-group">
                            <label>APELLIDOS</label>
                            <input type="text" name="apellidos" required>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>FECHA DE NACIMIENTO</label>
                            <input type="date" name="fecha_nacimiento" required title="Necesario para calcular la edad automáticamente">
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <h3><i data-lucide="building"></i> Datos Institucionales</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>CARGO ACTUAL</label>
                            <input type="text" name="cargo" required>
                        </div>
                        <div class="input-group">
                            <label>UNIDAD O DEPARTAMENTO</label>
                            <input type="text" name="unidad_departamento" required>
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>GRADO / NIVEL</label>
                            <input type="text" name="grado_nivel" placeholder="Ej: P1, B1..." required>
                        </div>
                        <div class="input-group">
                            <label>FECHA DE INGRESO</label>
                            <input type="date" name="fecha_ingreso" required title="Necesario para calcular antigüedad institucional">
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>AÑOS ADM. PÚBLICA (EXTERNO)</label>
                            <input type="number" name="anos_servicio_externo" value="0" min="0">
                        </div>
                        <div class="input-group">
                            <label>% ANTIGÜEDAD (OPCIONAL)</label>
                            <input type="number" step="0.01" name="porcentaje_antiguedad" value="0">
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <h3><i data-lucide="graduation-cap"></i> Información Socio-Económica</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>NIVEL DE INSTRUCCIÓN</label>
                            <select name="nivel_instruccion">
                                <option value="1">TSU</option>
                                <option value="2">Licenciado / Ingeniero</option>
                                <option value="3">Especialista</option>
                                <option value="4">Magíster</option>
                                <option value="5">Doctorado</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>NÚMERO DE CUENTA (BDV)</label>
                            <input type="text" name="cuenta_bancaria" placeholder="0102..." pattern="\d{20}" title="Deben ser 20 dígitos">
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>CANTIDAD DE HIJOS</label>
                            <input type="number" name="numero_hijos" value="0" min="0">
                        </div>
                        <div class="input-group">
                            <label>HIJOS CON DISCAPACIDAD</label>
                            <select name="hijos_discapacidad">
                                <option value="0">No</option>
                                <option value="1">Sí</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row-1">
                        <div class="input-group">
                            <label>% CAJA DE AHORRO</label>
                            <input type="number" step="0.01" name="porcentaje_caja_ahorro" value="0">
                        </div>
                    </div>
                </section>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="btnCancelar">Descartar</button>
                    <button type="submit" class="btn-submit">Registrar Trabajador</button>
                </div>
            </form>
        </main>
    </div>
</div>
<!-- SECCIÓN 3: SOLICITUDES -->
<div id="solicitudes" class="content-section">
    <header class="section-header">
        <div class="header-info">
            <h1>Gestión de <span class="text-blue-accent">Solicitudes</span></h1>
            <p>Administre y procese nuevas peticiones de retiro institucional.</p>
        </div>
        <!-- Botón que activa el Modal -->
        <div class="header-actions">
            <button type="button" class="btn-primary-dark" onclick="abrirModal()">
                <i data-lucide="plus-circle" size="20"></i> Nueva Solicitud
            </button>
        </div>
    </header>

        <div class="list-header">
            <div class="list-title-area">
                <h2 style="font-size: 1.5rem; color: #0f172a;">Listado de Solicitudes</h2>
                <div class="tab-filters" id="statusFilters">
                    <button class="active" data-status="all">Todas</button>
                    <button data-status="pending">Pendientes</button>
                    <button data-status="approved">Aprobadas</button>
                    <button data-status="rejected">Rechazadas</button>
                </div>
            </div>
            <div class="list-actions">
                <button class="btn-outline">
                    <i data-lucide="sliders-horizontal" size="16"></i> Filtros Avanzados
                </button>
                <button class="btn-outline">
                    <i data-lucide="download" size="16"></i> Exportar
                </button>
            </div>
        </div>

        <table class="custom-table">
            <thead>
                <tr>
                    <th>FOLIO</th>
                    <th>TRABAJADOR</th>
                    <th>FECHA SOLICITUD</th>
                    <th>PERÍODO</th>
                    <th>ESTATUS</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <!-- ... Filas de la tabla (mantener igual) ... -->
                <tr>
                    <td class="folio">#SOL-2024-001</td>
                    <td>
                        <div class="worker-info">
                            <strong>Dr. Roberto Hernández</strong>
                            <span>Facultad de Medicina</span>
                        </div>
                    </td>
                    <td>15 Oct 2023</td>
                    <td>Otoño 2024</td>
                    <td><span class="badge-status pending">PENDIENTE</span></td>
                    <td class="actions">
                        <i data-lucide="eye"></i>
                        <i data-lucide="edit-2"></i>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <div class="table-footer">
            <span>Mostrando 1 - 4 de 24 solicitudes</span>
            <div class="pagination">
                <button disabled>&lt;</button>
                <button class="active">1</button>
                <button>2</button>
                <button>3</button>
                <button>&gt;</button>
            </div>
        </div>
    </section>

    <!-- Tarjetas de Métricas Inferiores -->
    <section class="metrics-row">
        <div class="metric-card">
            <div class="metric-icon orange"><i data-lucide="hourglass"></i></div>
            <div class="metric-data">
                <h3>12</h3>
                <p>ESPERANDO REVISIÓN</p>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon green"><i data-lucide="check-circle-2"></i></div>
            <div class="metric-data">
                <h3>158</h3>
                <p>TOTAL APROBADAS</p>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon blue"><i data-lucide="timer"></i></div>
            <div class="metric-data">
                <h3>4.2 días</h3>
                <p>TIEMPO PROMEDIO</p>
            </div>
        </div>
    </section>
</div>


 <!-- SECCIÓN 4: EXPEDIENTES -->
<div id="expedientes" class="content-section">
    
    <!-- NIVEL 1: VISTA DE TODAS LAS TARJETAS (Se muestra por defecto) -->
    <div id="expedientes-lista">
        <div class="section-header-flex">
            <h1 class="page-title">Gestión de Expedientes Digitales</h1>
            <div class="search-container">
                <i data-lucide="search"></i>
                <input type="text" placeholder="Buscar expediente por nombre o cédula...">
            </div>
        </div>

            <div class="expediente-mini-card" onclick="verDetalleExpediente('Ricardo Mendoza')">
            <!-- Parte de la Imagen -->
            <div class="card-image-top">
                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Ricardo" alt="Foto">
            </div>
            
            <!-- Parte de los Datos -->
            <div class="card-info-bottom">
                <strong>Ricardo Mendoza</strong>
                <span>V-12.456.789</span>
                <div class="card-status-pill pending">En Revisión</div>
            </div>
        </div>
            <!-- Repetir tarjetas para otros trabajadores... -->
        </div>
    </div>

    <!-- NIVEL 2: VISTA DE DETALLE (Oculta por defecto) -->
    <div id="expediente-detalle" style="display: none;">
    <!-- Encabezado Superior -->
    <header class="expediente-header">
        <div class="header-left">
            <nav class="breadcrumb">EXPEDIENTES > <span>GESTIÓN DE DOCUMENTOS</span></nav>
            <h1>Expediente Digital: <span id="nombre-empleado-header">Ricardo M.</span></h1>
        </div>
        <div class="header-right">
            <div class="global-status-card">
                <div class="status-info">
                    <span class="label">ESTADO GLOBAL</span>
                    <span class="value incomplete">Incompleto (3/5)</span>
                </div>
                <div class="status-chart">
                    <span class="pct">60%</span>
                </div>
            </div>
        </div>
    </header>

    <div class="gestion-container">
        <!-- Columna Izquierda: Perfil y Asistente -->
        <aside class="gestion-sidebar">
            <div class="profile-summary-card">
                <div class="user-main-info">
                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Ricardo" class="img-profile-sm">
                    <div class="user-texts">
                        <h3>Ricardo Mendoza</h3>
                        <span>V-12.456.789</span>
                        <span class="facultad-badge">FACULTAD DE INGENIERÍA</span>
                    </div>
                </div>
                <div class="user-details-list">
                    <div class="detail-item"><span>Años de Servicio</span> <strong>28 Años</strong></div>
                    <div class="detail-item"><span>Cargo Actual</span> <strong>Titular IV</strong></div>
                    <div class="detail-item"><span>Fecha de Solicitud</span> <strong>12 Oct 2023</strong></div>
                </div>
            </div>

            <div class="asistente-carga-card">
                <h4>Asistente de Carga</h4>
                <p>Arrastre aquí los archivos PDF para una clasificación automática por IA institucional.</p>
                <div class="drop-zone-blue">
                    <div class="drop-icon-box">
                        <i data-lucide="upload"></i>
                    </div>
                    <span>SOLTAR ARCHIVOS</span>
                </div>
            </div>
            
            <button class="btn-back-minimal" onclick="volverALista()">
                <div class="icon-circle">
                    <i data-lucide="chevron-left"></i>
                </div>
                <span>Volver al listado</span>
            </button>
        </aside>

        <!-- Columna Derecha: Listado de Documentos -->
        <main class="gestion-content">
            <div class="docs-card-container">
                <div class="docs-header-flex">
                    <h3>LISTADO DE DOCUMENTOS OBLIGATORIOS</h3>
                    <button class="btn-history"><i data-lucide="history"></i> Historial de Cambios</button>
                </div>

                <div class="docs-list">
                    <!-- Fila Documento: Cargado -->
                    <div class="doc-item-row">
                        <div class="doc-icon success"><i data-lucide="user-check"></i></div>
                        <div class="doc-info">
                            <h4>Cédula de Identidad</h4>
                            <span class="status-tag success">● CARGADO <small>Verificado hace 2 días</small></span>
                        </div>
                        <div class="doc-btns">
                            <button class="btn-view"><i data-lucide="eye"></i></button>
                            <button class="btn-action-outline">REEMPLAZAR</button>
                        </div>
                    </div>

                    <!-- Fila Documento: Pendiente -->
                    <div class="doc-item-row">
                        <div class="doc-icon gray"><i data-lucide="file-text"></i></div>
                        <div class="doc-info">
                            <h4>Oficio de Solicitud</h4>
                            <span class="status-tag pending">● PENDIENTE <small>Requerido para avanzar</small></span>
                        </div>
                        <div class="doc-btns">
                            <button class="btn-action-primary"><i data-lucide="upload"></i> SUBIR ARCHIVO</button>
                        </div>
                    </div>

                    <!-- Fila Documento: Rechazado -->
                    <div class="doc-item-row error">
                        <div class="doc-icon danger"><i data-lucide="file-x"></i></div>
                        <div class="doc-info">
                            <h4>Punto de Cuenta</h4>
                            <span class="status-tag danger">● RECHAZADO <small>"Firma del Rector ausente"</small></span>
                        </div>
                        <div class="doc-btns">
                            <button class="btn-action-primary"><i data-lucide="upload"></i> CORREGIR</button>
                        </div>
                    </div>
                </div>

                <div class="docs-footer">
                    <div class="footer-text">
                        <strong>Resumen de Cumplimiento</strong>
                        <p>Faltan 2 documentos obligatorios para completar el expediente.</p>
                    </div>
                    <button class="btn-finalize" disabled>FINALIZAR EXPEDIENTE</button>
                </div>
            </div>
        </main>
    </div>
</div>

    <!-- SECCIÓN 5: PRESTACIONES -->
<div id="prestaciones" class="content-section">
    <div class="breadcrumb-area">
        <span class="breadcrumb">Panel de Control > Nómina > <strong>Cálculo de Prestaciones</strong></span>
    </div>

    <header class="section-header">
        <div class="header-info">
            <h1>Cálculo Técnico de Prestaciones</h1>
            <p>Determine el salario integral y las acumulaciones de ley para el personal universitario.</p>
        </div>
    </header>

    <div class="prestaciones-grid">
        <!-- Columna Principal -->
        <div class="calculation-main">
            <!-- Selector de Trabajador -->
            <div class="worker-selector-card">
                <div class="worker-avatar-box">
                    <i data-lucide="user"></i>
                </div>
                <div class="worker-details">
                    <h3>Dr. Ricardo J. Villasmil</h3>
                    <p>C.I. 12.456.789 • Profesor Titular • 28 años de servicio</p>
                    <div class="badge-row">
                        <span class="badge-status active-bg">ACTIVO</span>
                        <span class="badge-status info-bg">DEDICACIÓN EXCLUSIVA</span>
                    </div>
                </div>
                <button class="btn-ghost-blue">Cambiar Trabajador</button>
            </div>

            <!-- Estructura de Ingresos -->
            <div class="income-structure-card">
                <div class="card-title-row">
                    <div class="title-with-icon">
                        <i data-lucide="banknote"></i>
                        <h3>Estructura de Ingresos Mensuales</h3>
                    </div>
                    <span class="disclaimer">Valores expresados en divisas según tasa BCV</span>
                </div>

                <div class="inputs-grid-2x2">
                    <div class="calc-input-group">
                        <label>SUELDO BASE</label>
                        <div class="currency-input"><span>$</span><input type="text" value="1250.00"></div>
                    </div>
                    <div class="calc-input-group">
                        <label>PRIMA PROFESIONALIZACIÓN (20%)</label>
                        <div class="currency-input"><span>$</span><input type="text" value="250.00"></div>
                    </div>
                    <div class="calc-input-group">
                        <label>PRIMA POR HIJOS (CANT. 2)</label>
                        <div class="currency-input"><span>$</span><input type="text" value="85.50"></div>
                    </div>
                    <div class="calc-input-group">
                        <label>PRIMA DE ANTIGÜEDAD (FIJA)</label>
                        <div class="currency-input"><span>$</span><input type="text" value="312.00"></div>
                    </div>
                </div>

                <!-- Porcentaje Aplicable -->
                <div class="jubilation-percent-area">
                    <div class="percent-info">
                        <strong>Porcentaje de Jubilación Aplicable</strong>
                        <p>Según el artículo 42 de la Ley Orgánica de Universidades.</p>
                    </div>
                    <div class="percent-toggle">
                        <button class="active">100% <span>Jubilación</span></button>
                        <button>82.5% <span>Incapacidad</span></button>
                    </div>
                </div>
            </div>

            <!-- Salario Integral y Factor -->
            <div class="bottom-metrics">
                <div class="metric-box-white border-blue">
                    <div class="metric-icon-small blue-bg"><i data-lucide="calculator"></i></div>
                    <div class="metric-content">
                        <span class="tag-top">BASE MENSUAL</span>
                        <p>Salario Integral Estimado</p>
                        <h2>$ 1.897,50</h2>
                    </div>
                </div>
                <div class="metric-box-white border-green">
                    <div class="metric-icon-small green-bg"><i data-lucide="history"></i></div>
                    <div class="metric-content">
                        <span class="tag-top">VIGENCIA: 2024</span>
                        <p>Factor de Antigüedad (Años)</p>
                        <h2>28,4</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Lateral (Sidebar de Totales) -->
        <aside class="calculation-sidebar">
            <div class="consolidated-card">
                <p class="card-label">RESUMEN CONSOLIDADO</p>
                <span class="total-subtitle">Total Prestaciones Acumuladas</span>
                <h1 class="total-amount">$ 53.889,00</h1>
                
                <div class="sub-amounts">
                    <div class="sub-item">
                        <span>Monto Mensual</span>
                        <strong>$ 1.897,50</strong>
                    </div>
                    <div class="sub-item">
                        <span>Ahorro Caja</span>
                        <strong>$ 4.560,12</strong>
                    </div>
                </div>

                <div class="liquidation-status">
                    <div class="status-header">
                        <span>Estado de Liquidación</span>
                        <span class="status-tag-green">PRE-APROBADO</span>
                    </div>
                    <div class="progress-bar-thin">
                        <div class="fill" style="width: 80%;"></div>
                    </div>
                </div>
            </div>

            <div class="action-buttons-stack">
                <button class="btn-dark-full"><i data-lucide="printer"></i> Generar Comprobante</button>
                <button class="btn-outline-full"><i data-lucide="refresh-cw"></i> Actualizar Historial</button>
                <p class="helper-text">Al procesar, se enviará una notificación al Departamento de Finanzas y al trabajador.</p>
            </div>

            <div class="doc-checklist-card">
                <h3>DOCUMENTOS REQUERIDOS</h3>
                <ul class="checklist">
                    <li class="done"><i data-lucide="check-circle-2"></i> Certificación de Cargos (Actualizado)</li>
                    <li class="done"><i data-lucide="check-circle-2"></i> Constancia de Años de Servicio</li>
                    <li class="pending"><i data-lucide="circle"></i> Acta de Cese de Funciones</li>
                </ul>
            </div>
        </aside>
    </div>
</div>

   <!-- SECCIÓN 6: REPORTES -->
<div id="reportes" class="content-section">
    <header class="section-header-flex">
        <div class="header-info">
            <h1>Centro de Reportes</h1>
            <p>Visualización y análisis de datos del sistema institucional</p>
        </div>
        <div class="header-actions">
    
            <div class="actions-group">
                <!-- Botón Excel -->
                <button class="btn-export-excel">
                    <i data-lucide="file-spreadsheet"></i>
                    <span>Exportar a Excel</span>
                </button>

                <!-- Botón PDF (Asegúrate que tenga su propia clase de estilo) -->
                <button class="btn-export-pdf">
                    <i data-lucide="file-text"></i>
                    <span>Exportar a PDF</span>
                </button>
            </div>
    </header>

    <!-- Barra de Filtros -->
    <div class="filters-bar-card">
        <div class="filter-group">
            <label>RANGO DE FECHAS</label>
            <div class="filter-input">
                <i data-lucide="calendar"></i>
                <span>01/01/2023 - 31/12/2023</span>
            </div>
        </div>
        <div class="filter-group">
            <label>ESTATUS DEL TRÁMITE</label>
            <select><option>Todos los estatus</option></select>
        </div>
        <div class="filter-group">
            <label>DEPARTAMENTO</label>
            <select><option>Todas las facultades</option></select>
        </div>
        <button class="btn-filter-apply"><i data-lucide="filter"></i> Aplicar Filtros</button>
    </div>

    <!-- Tarjetas de Métricas -->
    <div class="metrics-grid-3">
        <div class="metric-card-simple">
            <div class="metric-icon-box blue-light"><i data-lucide="clock"></i></div>
            <span class="metric-label">TIEMPO PROMEDIO</span>
            <div class="metric-value-row">
                <h2>42 <span>días</span></h2>
            </div>
            <span class="trend down"><i data-lucide="trending-down"></i> -5.2% respecto al mes anterior</span>
        </div>

        <div class="metric-card-simple">
            <div class="metric-icon-box green-light"><i data-lucide="check-square"></i></div>
            <span class="metric-label">TRÁMITES FINALIZADOS</span>
            <div class="metric-value-row">
                <h2>1,284</h2>
            </div>
            <span class="trend up"><i data-lucide="trending-up"></i> +12% en el último trimestre</span>
        </div>

        <div class="metric-card-wide">
            <div class="metric-content-left">
                <div class="metric-icon-box indigo-light"><i data-lucide="bar-chart-3"></i></div>
                <span class="metric-label">TOTAL PRESTACIONES PAGADAS</span>
                <div class="metric-value-row">
                    <h2>$84,295,000 <span class="currency-tag">MXN</span></h2>
                </div>
                <div class="legend-mini">
                    <span class="dot-indigo"></span> Docentes
                    <span class="dot-teal"></span> Administrativos
                </div>
            </div>
            <div class="metric-visual-right">
                <!-- Ilustración simplificada de edificio institucional -->
                <i data-lucide="landmark" class="bg-icon-watermark"></i>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="charts-grid-2">
        <div class="chart-card">
            <div class="chart-header">
                <h3>Trabajadores Jubilados por Año</h3>
                <div class="toggle-group">
                    <span>LINEAL</span>
                    <span class="active">BARRAS</span>
                </div>
            </div>
            <div class="chart-placeholder-bars">
                <!-- Representación visual de las barras -->
                <div class="bar-container"><div class="bar" style="height: 20%;"></div><span>2019</span></div>
                <div class="bar-container"><div class="bar" style="height: 35%;"></div><span>2020</span></div>
                <div class="bar-container"><div class="bar" style="height: 25%;"></div><span>2021</span></div>
                <div class="bar-container"><div class="bar" style="height: 45%;"></div><span>2022</span></div>
                <div class="bar-container active"><div class="bar" style="height: 80%;"></div><span>2023</span><small>284</small></div>
            </div>
        </div>

        <div class="chart-card">
            <h3>Solicitudes por Estatus Actual</h3>
            <div class="donut-chart-area">
                <div class="donut-visual">
                    <div class="inner-text">
                        <strong>452</strong>
                        <span>TOTAL ACTIVAS</span>
                    </div>
                </div>
                <ul class="chart-legend-list">
                    <li><span class="dot blue"></span> En Trámite <strong>60%</strong></li>
                    <li><span class="dot green"></span> Aprobadas <strong>25%</strong></li>
                    <li><span class="dot red"></span> Rechazadas <strong>15%</strong></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Tabla Resumen -->
    <div class="table-card-full">
        <div class="table-header">
            <h3>Resumen Estadístico por Departamento</h3>
            <a href="#" class="view-all">Ver desglose completo <i data-lucide="arrow-right"></i></a>
        </div>
        <table class="report-table">
            <thead>
                <tr>
                    <th>DEPARTAMENTO / FACULTAD</th>
                    <th>PLANTILLA ACTIVA</th>
                    <th>JUBILACIONES 2023</th>
                    <th>TIEMPO PROMEDIO</th>
                    <th>MONTO TOTAL ESTIMADO</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="dept-cell">
                            <div class="dept-icon blue"><i data-lucide="microscope"></i></div>
                            <div><strong>Facultad de Ingeniería</strong><br><small>División de Ciencias Básicas</small></div>
                        </div>
                    </td>
                    <td>450</td>
                    <td><strong class="text-indigo">32</strong></td>
                    <td><span class="badge-time green">38 días</span></td>
                    <td>$12,450,000</td>
                </tr>
                <tr>
                    <td>
                        <div class="dept-cell">
                            <div class="dept-icon green"><i data-lucide="stethoscop"></i></div>
                            <div><strong>Facultad de Medicina</strong><br><small>Unidad de Especialidades</small></div>
                        </div>
                    </td>
                    <td>620</td>
                    <td><strong class="text-indigo">48</strong></td>
                    <td><span class="badge-time green">45 días</span></td>
                    <td>$18,220,000</td>
                </tr>
                <tr>
                    <td>
                        <div class="dept-cell">
                            <div class="dept-icon orange"><i data-lucide="gavel"></i></div>
                            <div><strong>Facultad de Derecho</strong><br><small>Ciencias Sociales</small></div>
                        </div>
                    </td>
                    <td>280</td>
                    <td><strong class="text-indigo">15</strong></td>
                    <td><span class="badge-time blue">52 días</span></td>
                    <td>$6,840,000</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

    <!-- BOTÓN LOGOUT SIEMPRE AL FINAL -->
    <form method="POST" action="{{ route('logout') }}" style="margin-top: 40px;">
        @csrf
        <button class="btn-logout" style="background: none; border: 1px solid #fa5252; color: #fa5252; padding: 8px 16px; border-radius: 6px; cursor: pointer;">
            <i data-lucide="log-out" size="16"></i> Cerrar sesión
        </button>
    </form>
</main>

<!-- Modal de Registro de Solicitud -->
<div id="modalSolicitud" class="modal-overlay">
    <div class="modal-container">
        <!-- Lado Izquierdo: Info Visual -->
        <aside class="modal-sidebar">
            <span class="badge-new">NUEVO REGISTRO</span>
            <h1>Registro de Solicitud de Jubilación</h1>
            <p>Complete cuidadosamente todos los campos requeridos para iniciar el proceso de retiro administrativo del trabajador.</p>
            
            <div class="modal-info-list">
                <div class="info-item">
                    <i data-lucide="info"></i>
                    <span>Los documentos PDF deben ser legibles y estar actualizados.</span>
                </div>
                <div class="info-item">
                    <i data-lucide="shield-check"></i>
                    <span>Este proceso cumple con la normativa de seguridad de datos institucionales.</span>
                </div>
            </div>
        </aside>

        <!-- Lado Derecho: Formulario -->
        <main class="modal-form-content">
            <form id="formNuevaJubilacion">
                <!-- Información General -->
                <section class="form-section">
                    <h3><i data-lucide="file-text"></i> Información General</h3>
                    <div class="form-row-3">
                        <div class="input-group">
                            <label>INGRESE LA CÉDULA</label>
                            <select name="cedula"><option>Seleccione...</option></select>
                        </div>
                        <div class="input-group">
                            <label>FECHA SOLICITUD</label>
                            <input type="date">
                        </div>
                        <div class="input-group">
                            <label>PERIODO</label>
                            <select><option>2024 - A</option></select>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>TIPO DE JUBILACIÓN</label>
                        <select><option>Edad Avanzada</option></select>
                    </div>
                </section>

                <!-- Datos Personales -->
                <section class="form-section">
                    <h3><i data-lucide="user"></i> Datos Personales</h3>
                    <div class="form-row-2">
                        <div class="input-group">
                            <label>NOMBRE COMPLETO</label>
                            <input type="text" placeholder="Ej: Carlos Eduardo Méndez">
                        </div>
                        <div class="input-group">
                            <label>CÉDULA</label>
                            <input type="text" placeholder="V-00.000.000">
                        </div>
                    </div>
                    <div class="form-row-3">
                        <div class="input-group">
                            <label>FECHA NACIMIENTO</label>
                            <input type="date">
                        </div>
                        <div class="input-group">
                            <label>EDAD CALCULADA</label>
                            <input type="text" readonly placeholder="62 años">
                        </div>
                        <div class="input-group">
                            <label>ESTADO CIVIL</label>
                            <select><option>Soltero/a</option></select>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>DIRECCIÓN DE HABITACIÓN</label>
                        <input type="text">
                    </div>
                </section>

               <!-- SECCIÓN: DATOS LABORALES -->
            <section class="form-section">
                <h3><i data-lucide="briefcase"></i> Datos Laborales</h3>
                <div class="form-row-2">
                    <div class="input-group">
                        <label>N° EMPLEADO</label>
                        <input type="text" placeholder="000000">
                    </div>
                    <div class="input-group">
                        <label>CARGO ACTUAL</label>
                        <input type="text" placeholder="Ej: Analista de Sistemas">
                    </div>
                </div>
                <div class="form-row-3">
                    <div class="input-group">
                        <label>TIPO TRABAJADOR</label>
                        <select><option>Fijo</option><option>Contratado</option></select>
                    </div>
                    <div class="input-group">
                        <label>FECHA INGRESO</label>
                        <input type="date">
                    </div>
                    <div class="input-group">
                        <label>FECHA EGRESO</label>
                        <input type="date">
                    </div>
                </div>
                <div class="form-row-2">
                    <div class="input-group">
                        <label>AÑOS SERV. CALC.</label>
                        <input type="text" readonly value="25 años" class="input-readonly">
                    </div>
                    <div class="input-group">
                        <label>DEPARTAMENTO</label>
                        <input type="text" placeholder="Ej: Recursos Humanos">
                    </div>
                </div>
            </section>

            <!-- SECCIÓN: DOCUMENTOS (Actualizada) -->
            <section class="form-section">
                <h3><i data-lucide="file-check"></i> Documentos Requeridos</h3>
                <div class="docs-grid">
                    <label class="doc-item"><input type="checkbox"> Cédula Identidad <span class="upload-link"><i data-lucide="upload"></i> PDF</span></label>
                    <label class="doc-item"><input type="checkbox"> Acta Nacimiento <span class="upload-link"><i data-lucide="upload"></i> PDF</span></label>
                    <label class="doc-item"><input type="checkbox"> Constancia Trabajo <span class="upload-link"><i data-lucide="upload"></i> PDF</span></label>
                    <label class="doc-item"><input type="checkbox"> Constancia Antigüedad <span class="upload-link"><i data-lucide="upload"></i> PDF</span></label>
                    <label class="doc-item"><input type="checkbox"> Últimos Recibos <span class="upload-link"><i data-lucide="upload"></i> PDF</span></label>
                    <label class="doc-item"><input type="checkbox"> Certificado Salarial <span class="upload-link"><i data-lucide="upload"></i> PDF</span></label>
                </div>
            </section>

            <!-- SECCIÓN: BENEFICIARIOS -->
            <section class="form-section">
                <div class="section-header-inline">
                    <h3><i data-lucide="users"></i> Beneficiarios</h3>
                    <button type="button" class="btn-add-small">AÑADIR BENEFICIARIO</button>
                </div>
                <table class="mini-table">
                    <thead>
                        <tr>
                            <th>NOMBRE</th>
                            <th>CÉDULA</th>
                            <th>PARENTESCO</th>
                            <th>TELÉFONO</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Elena Ramírez</td>
                            <td>V-12.345.678</td>
                            <td>Cónyuge</td>
                            <td>0414-1234567</td>
                            <td><button class="btn-del"><i data-lucide="trash-2" size="14"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </section>

<!-- SECCIÓN: OBSERVACIONES -->
<section class="form-section">
    <h3><i data-lucide="align-left"></i> Observaciones</h3>
    <textarea class="form-textarea" placeholder="Indique cualquier detalle adicional relevante para el trámite..."></textarea>
</section>

                <!-- Footer del Formulario -->
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn-submit">Registrar Solicitud</button>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalTrabajador');
    const btnAbrir = document.querySelector('.btn-primary-dark'); // Botón "Agregar Trabajador"
    const btnCerrar = document.getElementById('closeModal');
    const btnCancelar = document.getElementById('btnCancelar');

    // Función para abrir la modal
    btnAbrir.addEventListener('click', () => {
        modal.style.display = 'flex';
    });

    // Función para cerrar
    const cerrarModal = () => {
        modal.style.display = 'none';
    };

    btnCerrar.addEventListener('click', cerrarModal);
    btnCancelar.addEventListener('click', cerrarModal);

    // Cerrar si hace clic fuera de la ventana blanca
    window.addEventListener('click', (e) => {
        if (e.target === modal) cerrarModal();
    });

    
});

  document.getElementById('formTrabajador').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const btnSubmit = document.querySelector('.btn-submit');

    btnSubmit.disabled = true;
    btnSubmit.innerText = 'Guardando...';

    fetch("{{ route('trabajador') }}", {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Accept': 'application/json'
        }
    })
    .then(async response => {
        const data = await response.json();

        if (!response.ok) {
            throw data;
        }

        return data;
    })
    .then(data => {
        alert(data.message);

        document.getElementById('formTrabajador').reset();

        location.reload();
    })
    .catch(error => {
        console.error(error);

        if (error.errors) {
            let mensajes = '';

            Object.values(error.errors).forEach(err => {
                mensajes += err[0] + '\n';
            });

            alert(mensajes);
        } else {
            alert(error.message || 'Error interno del servidor');
        }
    })
    .finally(() => {
        btnSubmit.disabled = false;
        btnSubmit.innerText = 'Registrar Trabajador';
    });
});
</script>
<script src="{{ asset('js/trabajador.js') }}"></script>
<script src="{{ asset('js/sesion2.js') }}"></script>
<script src="{{ asset('js/trabajador.js') }}"></script>
<script src="{{ asset('js/tabla1.js') }}"></script>
<script src="{{ asset('js/expedientelevel1.js') }}"></script>

</body>
</html>