// ============================================
// sesion2.js — Navegación general y modales del SIGEJUB
// Ubicación: Panel principal del dashboard
// Responsabilidades:
//   - Cambio de pestañas/secciones del dashboard (switchTab)
//   - Apertura/cierre del modal de solicitudes
//   - Validación de cédula en inputs
//   - Cierre de modales al hacer clic fuera o presionar ESC
// ============================================

// ============================================
// CAMBIO DE PESTAÑAS — Navegación entre secciones del dashboard
// ============================================

// Función global para saltar entre módulos (accesible desde accesos rápidos del inicio)
function switchTab(id) {
    const menuItems = document.querySelectorAll('.menu-item');
    const sections = document.querySelectorAll('.content-section');
    const targetMenu = document.querySelector(`[data-target="${id}"]`);
    const targetSection = document.getElementById(id);

    if (targetMenu && targetSection) {
        // Primero limpia todas las selecciones activas
        menuItems.forEach(i => i.classList.remove('active'));
        sections.forEach(s => s.classList.remove('active'));

        // Luego activa SOLO el ítem de menú y la sección que corresponden al target
        targetMenu.classList.add('active');
        targetSection.classList.add('active');
    }

    // ============================================
    // CIERRE DE MODALES — Clic fuera o tecla ESC
    // ============================================

    // Cierra cualquier modal con clase .modal-overlay al hacer clic en el fondo
    window.addEventListener('click', function(e) {
        var modals = document.querySelectorAll('.modal-overlay');
        for (var i = 0; i < modals.length; i++) {
            if (e.target === modals[i]) {
                modals[i].style.display = 'none';
            }
        }
    });

    // También cierra todos los modales al presionar la tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay').forEach(function(m) {
                m.style.display = 'none';
            });
        }
    });
}

// ============================================
// VALIDACIÓN DE CÉDULA — Solo permite dígitos numéricos
// ============================================
function validarCedula(input) {
    // Elimina cualquier carácter que no sea dígito (/\D/g = todo lo que no sea 0-9)
    input.value = input.value.replace(/\D/g, '');
}

// ============================================
// MODAL DE SOLICITUDES — Abrir/Cerrar
// ============================================

function abrirModal() {
    const modal = document.getElementById('modalSolicitud');
    if (modal) {
        modal.style.display = 'flex';  // Muestra el modal centrado con flexbox
    }
}

function cerrarModal() {
    const modal = document.getElementById('modalSolicitud');
    if (modal) modal.style.display = 'none';
}

// ============================================
// INICIALIZACIÓN — Eventos al cargar el DOM
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    // Escuchador único para cada ítem del menú lateral de navegación
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            switchTab(target);
        });
    });

    // Cierre adaptativo: clic en el fondo difuminado cierra el modal de solicitud
    window.addEventListener('click', (event) => {
        const modal = document.getElementById('modalSolicitud');
        if (event.target === modal) {
            cerrarModal();
        }
    });
});