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
})();
