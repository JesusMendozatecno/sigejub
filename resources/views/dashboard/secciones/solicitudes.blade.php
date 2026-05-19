<header class="section-header">
    <div class="header-info">
        <h1>Gestión de <span class="text-blue-accent">Solicitudes</span></h1>
        <p>Administre y procese nuevas peticiones de retiro institucional.</p>
    </div>
    <div class="header-actions">
        <button type="button" class="btn-primary-dark" onclick="abrirModalSolicitud()">
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
            <i data-lucide="sliders-horizontal" size=\"16\"></i> Filtros Avanzados
        </button>
        <button class="btn-outline">
            <i data-lucide="download" size=\"16\"></i> Exportar
        </button>
    </div>
</div>

<table class="custom-table">
    <thead>
        <tr>
            <th>FOLIO</th>
            <th>TRABAJADOR</th>
            <th>TIPO DE RETIRO</th>
            <th>FECHA APERTURA</th>
            <th>ESTATUS</th>
            <th>ACCIONES</th>
        </tr>
    </thead>
    <tbody id="tbodySolicitudes">
        </tbody>
</table>