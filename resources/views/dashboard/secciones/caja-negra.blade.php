<header class="section-header">
    <div class="header-info">
        <h1><i class="fas fa-hard-drive" size="22"></i> Historial</h1>
        <p>Registro inmutable de auditoría — todas las acciones del sistema.</p>
    </div>
    <div class="header-actions">
        <button class="cn-btn-action cn-btn-primary" onclick="exportarCajaNegra()">
            <i class="fas fa-download" size="16"></i>
            <span>Exportar</span>
        </button>
    </div>
</header>

<!-- Filter Toolbar -->
<div class="cn-toolbar">
    <div class="cn-search-wrap">
        <i class="fas fa-search" size="16" class="cn-search-icon"></i>
        <input type="text" id="cnSearch" placeholder="Buscar en descripción...">
    </div>
    <div class="cn-filters">
        <select id="cnUsuario" onchange="cargarCajaNegra()">
            <option value="">Usuario</option>
        </select>
        <select id="cnAccion" onchange="cargarCajaNegra()">
            <option value="">Acción</option>
            <option value="created">Creado</option>
            <option value="updated">Actualizado</option>
            <option value="deleted">Eliminado</option>
        </select>
        <select id="cnTipo" onchange="cargarCajaNegra()">
            <option value="">Tipo</option>
            <option value="trabajador">Trabajador</option>
            <option value="solicitud">Solicitud</option>
            <option value="expediente">Expediente</option>
            <option value="documento">Documento</option>
            <option value="usuario">Usuario</option>
            <option value="notificacion">Notificación</option>
        </select>
        <div class="cn-date-range">
            <input type="date" id="cnDesde" onchange="cargarCajaNegra()" title="Desde">
            <span class="cn-date-sep">→</span>
            <input type="date" id="cnHasta" onchange="cargarCajaNegra()" title="Hasta">
        </div>
    </div>
</div>

<!-- Stats -->
<div class="cn-stats" id="cnStats">
    <div class="cn-stat-card cn-stat-total">
        <i class="fas fa-database" size="22"></i>
        <div><h3 id="cnTotal">0</h3><p>Total registros</p></div>
    </div>
    <div class="cn-stat-card cn-stat-today">
        <i class="fas fa-calendar-check" size="22"></i>
        <div><h3 id="cnHoy">0</h3><p>Registros hoy</p></div>
    </div>
    <div class="cn-stat-card cn-stat-week">
        <i class="fas fa-wave-square" size="22"></i>
        <div><h3 id="cnSemana">0</h3><p>Últimos 7 días</p></div>
    </div>
</div>

<!-- Table -->
<div class="cn-table-wrap">
    <table class="cn-table" id="cnTable">
        <thead>
            <tr>
                <th>FECHA/HORA</th>
                <th>USUARIO</th>
                <th>ACCIÓN</th>
                <th>TIPO</th>
                <th>DESCRIPCIÓN</th>
                <th>IP</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="cnBody">
            <tr><td colspan="7" class="cn-empty">Cargando registros...</td></tr>
        </tbody>
    </table>
</div>

<div class="cn-footer">
    <span id="cnCounter" class="cn-counter">Mostrando 0 registros</span>
    <div class="cn-pagination" id="cnPagination"></div>
</div>

<!-- Detail Modal -->
<div class="modal-overlay" id="cnDetailModal">
    <div class="modal-box cn-detail-modal">
        <div class="cn-modal-head">
            <h3><i class="fas fa-search" size="18"></i> Detalle del Registro</h3>
            <button class="cn-modal-close" onclick="document.getElementById('cnDetailModal').classList.remove('show')">&times;</button>
        </div>
        <div id="cnDetailContent"><p class="cn-modal-loading">Cargando detalle...</p></div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset('css/dashboard/secciones/caja-negra.css') }}">

<script src="{{ asset('js/secciones/caja-negra.js') }}"></script>
