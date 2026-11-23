/* ============================================================================
   modales.js — GESTOR CENTRAL DE VENTANAS MODALES
   ----------------------------------------------------------------------------
   ¿Para qué sirve?
   Centraliza el comportamiento de TODAS las ventanas modales del sistema:

     1. La ventana SOLO se cierra con el botón establecido (X, Cancelar, etc.).
        - El clic fuera de la ventana (sobre el fondo) NO la cierra.
        - La tecla ESC TAMPOCO la cierra.
     2. Ofrece funciones para abrir y cerrar ventanas desde cualquier script.
     3. Permite cerrar con solo agregar el atributo data-modal-close al botón,
        sin escribir JavaScript propio.

   ¿Dónde se carga?
   En resources/views/dashboard/index.blade.php con <script defer>, antes del
   resto de scripts del dashboard.

   Uso:
     Modal.abrir('modalTrabajador');        // Abre la ventana
     Modal.cerrar('modalTrabajador');       // Cierra la ventana
     Modal.cerrarTodas();                   // Cierra todas las ventanas abiertas
     Modal.abierta('modalTrabajador');      // Devuelve true/false

   En HTML:
     <button data-modal-close="modalTrabajador">Cancelar</button>
   ============================================================================ */
(function () {
    'use strict';

    /* Cómo se reconoce el fondo (capa exterior) de cada ventana */
    var SELECTOR_OVERLAY = '.modal-overlay, .modal-tasa, .modal-resultado';

    /* Estilos de display que indican que una ventana está abierta */
    var MARCAS_VISIBLE = ['flex', 'block'];

    /* ------------------------------------------------------------------
       Utilidades internas
       ------------------------------------------------------------------ */

    function obtener(id) {
        return typeof id === 'string' ? document.getElementById(id) : id;
    }

    /* ¿Está visible la ventana? Se comprueba por estilo en línea y por clase */
    function abierta(id) {
        var el = obtener(id);
        if (!el) return false;
        if (el.classList.contains('active') || el.classList.contains('show')) return true;
        return MARCAS_VISIBLE.indexOf(el.style.display) !== -1;
    }

    function crearBotonCerrar(modal, dialogo, zonaContenido) {
        var boton = dialogo.querySelector(
            '.modal-standard-close, .btn-close-absolute, .workers-calendar-close, .cn-modal-close, button[title="Cerrar"], button[aria-label*="Cerrar"]'
        );
        if (!boton) {
            boton = document.createElement('button');
            boton.textContent = '\u00d7';
            boton.setAttribute('title', 'Cerrar');
        }
        boton.type = 'button';
        boton.classList.add('modal-standard-close');
        if (!boton.hasAttribute('aria-label')) boton.setAttribute('aria-label', 'Cerrar ventana');
        boton.setAttribute('data-modal-close', modal.id);
        zonaContenido.insertBefore(boton, zonaContenido.firstChild);
    }

    function normalizarDialogo(modal, dialogo) {
        if (dialogo.classList.contains('modal-standardized')) return;

        /* El recorte de foto de perfil conserva su propio diseño: es una
           ventana compacta con el círculo centrado, sin panel lateral. */
        if (modal.id === 'cropModal') return;

        dialogo.setAttribute('role', 'dialog');
        dialogo.setAttribute('aria-modal', 'true');
        var zonaContenido = dialogo.querySelector(':scope > .modal-form-content');
        if (dialogo.classList.contains('modal-container') && zonaContenido) {
            dialogo.classList.add('modal-standardized');
            crearBotonCerrar(modal, dialogo, zonaContenido);
            return;
        }

        var tituloOriginal = dialogo.querySelector('h1, h2, h3, h4');
        var descripcionOriginal = dialogo.querySelector(':scope > p');
        var contenido = document.createElement('main');
        contenido.className = 'modal-standard-content';

        var info = document.createElement('aside');
        info.className = 'modal-standard-info';
        var distintivo = document.createElement('span');
        distintivo.className = 'modal-standard-badge';
        distintivo.textContent = 'SIGEJUB';
        if (tituloOriginal) {
            tituloOriginal.classList.add('modal-standard-title');
            info.appendChild(tituloOriginal);
        } else {
            var tituloInfo = document.createElement('h2');
            tituloInfo.className = 'modal-standard-title';
            tituloInfo.textContent = modal.getAttribute('aria-label') || 'Información';
            info.appendChild(tituloInfo);
        }
        info.insertBefore(distintivo, info.firstChild);
        if (descripcionOriginal) {
            descripcionOriginal.classList.add('modal-standard-description');
            info.appendChild(descripcionOriginal);
        } else {
            var descripcion = document.createElement('p');
            descripcion.className = 'modal-standard-description';
            descripcion.textContent = 'Revisa la información de esta ventana y utiliza el panel de trabajo para completar la acción.';
            info.appendChild(descripcion);
        }

        while (dialogo.firstChild) {
            contenido.appendChild(dialogo.firstChild);
        }
        dialogo.classList.add('modal-standardized', 'modal-standardized-legacy');
        dialogo.appendChild(info);
        dialogo.appendChild(contenido);
        crearBotonCerrar(modal, dialogo, contenido);
    }

    function normalizarModales(contenedor) {
        var selector = [
            '.modal-overlay > .modal-container',
            '.modal-overlay > .modal-delete-box',
            '.modal-overlay > .modal-box',
            '.modal-overlay > .workers-calendar-modal',
            '.modal-tasa > .modal-tasa-box',
            '.modal-resultado > .modal-resultado-box'
        ].join(', ');

        if (contenedor.matches && contenedor.matches(selector)) {
            var modalPadre = contenedor.parentElement;
            if (modalPadre && modalPadre.id) normalizarDialogo(modalPadre, contenedor);
        }
        if (contenedor.querySelectorAll) {
            contenedor.querySelectorAll(selector).forEach(function (dialogo) {
                var modalPadre = dialogo.parentElement;
                if (modalPadre && modalPadre.id) normalizarDialogo(modalPadre, dialogo);
            });
        }
    }

    function iniciarEstandarizacion() {
        normalizarModales(document);
        var observador = new MutationObserver(function (cambios) {
            cambios.forEach(function (cambio) {
                cambio.addedNodes.forEach(function (nodo) {
                    if (nodo.nodeType === 1) normalizarModales(nodo);
                });
            });
        });
        observador.observe(document.body, { childList: true, subtree: true });
    }

    /* ------------------------------------------------------------------
       API pública: abrir y cerrar
       ------------------------------------------------------------------ */

    function abrir(id) {
        var el = obtener(id);
        if (!el) return false;
        el.style.display = 'flex';
        el.classList.add('active');
        return true;
    }

    function cerrar(id) {
        var el = obtener(id);
        if (!el) return false;
        el.style.display = 'none';
        el.classList.remove('active');
        el.classList.remove('show');
        return true;
    }

    /* Cierra todas las ventanas abiertas (útil al cambiar de sección) */
    function cerrarTodas() {
        document.querySelectorAll(SELECTOR_OVERLAY).forEach(function (el) {
            el.style.display = 'none';
            el.classList.remove('active');
            el.classList.remove('show');
        });
    }

    /* ------------------------------------------------------------------
       1. Botones con data-modal-close="id"
       Evita tener que escribir onclick o listeners propios en cada ventana.
       ------------------------------------------------------------------ */
    document.addEventListener('click', function (evento) {
        var objetivo = evento.target;
        if (!objetivo || !objetivo.closest) return;

        var boton = objetivo.closest('[data-modal-close]');
        if (!boton) return;

        evento.preventDefault();
        cerrar(boton.getAttribute('data-modal-close'));
    });

    /* ------------------------------------------------------------------
       2. El clic sobre el fondo NO cierra la ventana
       Se escucha en fase de captura (true) sobre document: así la orden
       "detener la propagación" se ejecuta ANTES de los listeners que cada
       sección registró sobre su propio overlay, y quedan anulados sin tener
       que modificar cada archivo.
       ------------------------------------------------------------------ */
    document.addEventListener('click', function (evento) {
        var objetivo = evento.target;
        if (objetivo && objetivo.classList && objetivo.matches &&
            objetivo.matches(SELECTOR_OVERLAY)) {
            evento.stopPropagation();
            evento.preventDefault();
        }
    }, true);

    /* ------------------------------------------------------------------
       3. La tecla ESC no cierra la ventana
       Igual que el punto anterior: se intercepta en captura y se detiene la
       propagación para que no lleguen los listeners de ESC de las secciones.
       ------------------------------------------------------------------ */
    document.addEventListener('keydown', function (evento) {
        var esEscape = evento.key === 'Escape' || evento.key === 'Esc' || evento.keyCode === 27;
        if (!esEscape) return;

        var hayVentanaAbierta = false;
        document.querySelectorAll(SELECTOR_OVERLAY).forEach(function (el) {
            if (abierta(el)) hayVentanaAbierta = true;
        });

        if (hayVentanaAbierta) {
            evento.stopImmediatePropagation();
            evento.preventDefault();
        }
    }, true);

    /* ------------------------------------------------------------------
       API pública en window.Modal (y alias sueltos)
       ------------------------------------------------------------------ */
    window.Modal = {
        abrir: abrir,
        cerrar: cerrar,
        cerrarTodas: cerrarTodas,
        abierta: abierta
    };

    /* Alias cortos para usar desde atributos onclick existentes */
    window.abrirModal = abrir;
    window.cerrarModal = cerrar;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciarEstandarizacion);
    } else {
        iniciarEstandarizacion();
    }
})();
