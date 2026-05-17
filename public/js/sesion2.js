 // Inicializar iconos
    lucide.createIcons();

    // Lógica de navegación entre secciones
    const menuItems = document.querySelectorAll('.menu-item');
    const sections = document.querySelectorAll('.content-section');

    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            const target = item.getAttribute('data-target');
            switchTab(target);
        });
    });

    function switchTab(id) {
        // Remover clases activas
        menuItems.forEach(i => i.classList.remove('active'));
        sections.forEach(s => s.classList.remove('active'));

        // Activar la seleccionada
        document.querySelector(`[data-target="${id}"]`).classList.add('active');
        document.getElementById(id).classList.add('active');

        // Refrescar iconos por si hay nuevos en la sección
        lucide.createIcons();
    }

// Función para abrir el modal
function abrirModal() {
    const modal = document.getElementById('modalSolicitud');
    modal.classList.add('active');
    lucide.createIcons(); // Recargar iconos dentro del modal
}

// Función para cerrar el modal
function cerrarModal() {
    const modal = document.getElementById('modalSolicitud');
    modal.classList.remove('active');
}

// Cerrar si se hace click fuera del contenedor blanco
window.onclick = function(event) {
    const modal = document.getElementById('modalSolicitud');
    if (event.target == modal) {
        cerrarModal();
    }
}

