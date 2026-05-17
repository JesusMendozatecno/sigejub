document.addEventListener('DOMContentLoaded', () => {
    // 1. Lógica de Filtros por Estatus
    const filterButtons = document.querySelectorAll('#statusFilters button');
    const tableRows = document.querySelectorAll('.custom-table tbody tr');

    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            // Cambiar estado activo del botón
            filterButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const status = btn.getAttribute('data-status');

            // Filtrar filas
            tableRows.forEach(row => {
                const rowStatus = row.querySelector('.badge-status').classList;
                
                if (status === 'all') {
                    row.style.display = '';
                } else if (rowStatus.contains(status)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    // 2. Lógica de Paginación Simple
    const pageButtons = document.querySelectorAll('.pagination button');
    pageButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            if (!this.disabled) {
                pageButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                // Aquí podrías añadir la lógica para cargar datos de la página X
                console.log(`Cargando página: ${this.innerText}`);
            }
        });
    });
});