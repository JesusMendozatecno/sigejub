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
    var tmp = document.getElementById('tmp-section-style');
    if (tmp) tmp.remove();
    var menuItems = document.querySelectorAll('.menu-item');
    var sections = document.querySelectorAll('.content-section');
    var targetMenu = document.querySelector('[data-target="' + id + '"]');
    var targetSection = document.getElementById(id);

    if (targetMenu && targetSection) {
        menuItems.forEach(function(i) { i.classList.remove('active'); });
        sections.forEach(function(s) { s.classList.remove('active'); });
        targetMenu.classList.add('active');
        targetSection.classList.add('active');
        localStorage.setItem('sigejub_active_section', id);
    }
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

    // Restaura la sección activa guardada (al recargar la página)
    var savedSection = localStorage.getItem('sigejub_active_section');
    if (savedSection && savedSection !== 'inicio') {
        switchTab(savedSection);
    }

    // Cierre de modales: clic en el fondo difuminado cierra todos los modales
    window.addEventListener('click', function(e) {
        var modals = document.querySelectorAll('.modal-overlay');
        for (var i = 0; i < modals.length; i++) {
            if (e.target === modals[i]) {
                modals[i].style.display = 'none';
            }
        }
    });

    // Cierre de modales con tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay').forEach(function(m) {
                m.style.display = 'none';
            });
        }
    });
});