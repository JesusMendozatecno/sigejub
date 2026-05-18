/**
 * SIGEJUB - Núcleo de Control de Interfaz y Navegación General
 * Controlador de Pestañas y Modales de Solicitud
 */

// Función global para saltar entre módulos (Accesible desde los accesos rápidos de inicio)
function switchTab(id) {
    const menuItems = document.querySelectorAll('.menu-item');
    const sections = document.querySelectorAll('.content-section');
    const targetMenu = document.querySelector(`[data-target="${id}"]`);
    const targetSection = document.getElementById(id);

    if (targetMenu && targetSection) {
        // Remover clases activas de la totalidad de los nodos
        menuItems.forEach(i => i.classList.remove('active'));
        sections.forEach(s => s.classList.remove('active'));

        // Activar de forma emparejada el menú y el contenedor HTML
        targetMenu.classList.add('active');
        targetSection.classList.add('active');

        // Refrescar Lucide exclusivamente si la librería está mapeada en el objeto window
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
}

// Funciones globales para control de la modal de solicitudes
function abrirModal() {
    const modal = document.getElementById('modalSolicitud');
    if (modal) {
        modal.style.display = 'flex'; // Cambiado a flex para mantener consistencia con los CSS modernos
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}

function cerrarModal() {
    const modal = document.getElementById('modalSolicitud');
    if (modal) modal.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar íconos de forma segura tras asegurar la carga del DOM
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Escuchador único y limpio para el menú de navegación lateral
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            switchTab(target);
        });
    });

    // Cierre adaptativo de la modal al hacer clic en el fondo difuminado
    window.addEventListener('click', (event) => {
        const modal = document.getElementById('modalSolicitud');
        if (event.target === modal) {
            cerrarModal();
        }
    });
});