
<header class="section-header">
    <div class="header-info">
        <h1>Nómina Mensual</h1>
        <p>Gestión y exportación de la planilla de nómina institucional.</p>
    </div>
    <div class="header-actions">
        <button type="button" class="btn-primary-dark" onclick="exportarNomina()">
            <i class="fas fa-file-excel"></i> Exportar Planilla
        </button>
    </div>
</header>

<div class="filters-bar-card" style="margin-top: 20px;">
    <div class="filter-group">
        <label>PERÍODO</label>
        <select id="filtroPeriodo">
            <option value="">Mes actual</option>
        </select>
    </div>
    <div class="filter-group">
        <label>TIPO DE NÓMINA</label>
        <select id="filtroTipoNomina">
            <option value="">Todas</option>
            <option value="Docente">Docente</option>
            <option value="Administrativo">Administrativo</option>
            <option value="Obrero">Obrero</option>
        </select>
    </div>
    <div class="total-badge-card" style="margin-left: auto;">
        <div>
            <p>TOTAL TRABAJADORES</p>
            <h2 id="totalNomina">0</h2>
        </div>
        <i class="fas fa-users icon-bg"></i>
    </div>
</div>

<div class="data-table-container" style="margin-top: 20px;">
    <table class="custom-table">
        <thead>
            <tr>
                <th>N°</th>
                <th>CÉDULA</th>
                <th>NOMBRE COMPLETO</th>
                <th>CARGO</th>
                <th>SUELDO BASE</th>
                <th>TOTAL ASIGNACIONES</th>
                <th>TOTAL DEDUCCIONES</th>
                <th>NETO A COBRAR</th>
            </tr>
        </thead>
        <tbody id="tbodyNomina">
        </tbody>
    </table>
</div>

<script>
window.exportarNomina = function() {
    const overlay = document.getElementById('loading-overlay');
    const texto = document.getElementById('loadingText');
    if (overlay) overlay.classList.add('active');
    if (texto) texto.textContent = 'Generando planilla de nómina...';

    fetch('/exportar/nomina')
        .then(function(resp) {
            if (!resp.ok) throw new Error('Error al generar la planilla');
            return resp.blob();
        })
        .then(function(blob) {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'ADM.CONT._' + new Date().toISOString().slice(0,7) + '.xlsx';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            if (overlay) overlay.classList.remove('active');
        })
        .catch(function(err) {
            console.error(err);
            if (overlay) overlay.classList.remove('active');
            mostrarToast('Error al generar la planilla', 'error');
        });
};

(function() {
    fetch('/trabajadores?per_page=500')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var rows = data.data || data;
            var tbody = document.getElementById('tbodyNomina');
            if (!tbody) return;
            var html = '';
            for (var i = 0; i < rows.length; i++) {
                var t = rows[i];
                html += '<tr>' +
                    '<td>' + (i + 1) + '</td>' +
                    '<td>' + t.cedula + '</td>' +
                    '<td>' + t.nombres + ' ' + t.apellidos + '</td>' +
                    '<td>' + (t.cargo || '-') + '</td>' +
                    '<td>' + (t.sueldo_base || '0') + '</td>' +
                    '<td>-</td>' +
                    '<td>-</td>' +
                    '<td>-</td>' +
                    '</tr>';
            }
            tbody.innerHTML = html;
            document.getElementById('totalNomina').textContent = rows.length;
        })
        .catch(function(err) { 
            console.error(err);
        });
})();
</script>
<?php /**PATH C:\xampp\htdocs\sigejub 2\resources\views/dashboard/secciones/nomina.blade.php ENDPATH**/ ?>