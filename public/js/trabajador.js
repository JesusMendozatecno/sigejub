/**
 * trabajador.js — Script para el formulario de registro de trabajador.
 * Gestiona validación, cálculo automático de edad y años de servicio.
 */

document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // REFERENCIAS AL DOM — Modal, botones y formulario
    // ============================================
    const modal = document.getElementById('modalTrabajador');
    const btnAbrir = document.querySelector('.btn-primary-dark');
    const btnCerrar = document.getElementById('closeModal');
    const btnCancelar = document.getElementById('btnCancelar');
    const formTrabajador = document.getElementById('formTrabajador');

    // Cierra el modal ocultándolo con display:none
    const cerrarModalTrabajador = () => {
        if (modal) modal.style.display = 'none';
    };

    // Abre el modal al hacer clic en el botón principal de registro
    if (btnAbrir && modal) {
        btnAbrir.addEventListener('click', () => {
            modal.style.display = 'flex';
        });
    }

    // Cierra el modal desde los botones de cerrar/cancelar
    if (btnCerrar) btnCerrar.addEventListener('click', cerrarModalTrabajador);
    if (btnCancelar) btnCancelar.addEventListener('click', cerrarModalTrabajador);

    // Cierra el modal si el usuario hace clic en el fondo oscuro (overlay)
    window.addEventListener('click', (e) => {
        if (e.target === modal) cerrarModalTrabajador();
    });

    // ============================================
    // ENVÍO DEL FORMULARIO — AJAX con fetch
    // ============================================
    if (formTrabajador) {
        formTrabajador.addEventListener('submit', function(e) {
            e.preventDefault();

            // Toma la URL del atributo data-action o usa la ruta por defecto
            const actionUrl = this.getAttribute('data-action') || "/dashboard/trabajadores";
            const formData = new FormData(this);
            const btnSubmit = this.querySelector('.btn-submit');

            // Deshabilita el botón y cambia el texto mientras se procesa
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerText = 'Guardando...';
            }

            fetch(actionUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value,
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) throw data;
                return data;
            })
            .then(data => {
                alert(data.mensaje || 'Trabajador registrado de manera exitosa.');
                formTrabajador.reset();
                cerrarModalTrabajador();
                location.reload();  // Recarga la página para reflejar el nuevo registro
            })
            .catch(error => {
                console.error('Error procesando el registro:', error);
                if (error.errors) {
                    // Errores de validación de Laravel: concatena el primer mensaje de cada campo
                    let mensajes = '';
                    Object.values(error.errors).forEach(err => {
                        mensajes += err[0] + '\n';
                    });
                    alert(mensajes);
                } else {
                    alert(error.mensaje || error.message || 'Error interno al comunicarse con el servidor.');
                }
            })
            .finally(() => {
                // Restaura el botón de envío siempre, haya éxito o error
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerText = 'Registrar Trabajador';
                }
            });
        });
    }
});