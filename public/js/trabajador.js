/**
 * SIGEJUB - Control y Apertura de Expedientes de Trabajadores
 * Lógica Exclusiva de Modales de Registro y Envíos AJAX (Fetch)
 */
document.addEventListener('DOMContentLoaded', function() {
    
    const modal = document.getElementById('modalTrabajador');
    const btnAbrir = document.querySelector('.btn-primary-dark'); 
    const btnCerrar = document.getElementById('closeModal');
    const btnCancelar = document.getElementById('btnCancelar');
    const formTrabajador = document.getElementById('formTrabajador');

    const cerrarModalTrabajador = () => {
        if (modal) modal.style.display = 'none';
    };

    if (btnAbrir && modal) {
        btnAbrir.addEventListener('click', () => {
            modal.style.display = 'flex';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    }

    if (btnCerrar) btnCerrar.addEventListener('click', cerrarModalTrabajador);
    if (btnCancelar) btnCancelar.addEventListener('click', cerrarModalTrabajador);

    window.addEventListener('click', (e) => {
        if (e.target === modal) cerrarModalTrabajador();
    });

    // PROCESAMIENTO AJAX FORM TRABAJADOR
    if (formTrabajador) {
        formTrabajador.addEventListener('submit', function(e) {
            e.preventDefault();

            const actionUrl = this.getAttribute('data-action') || "/dashboard/trabajadores"; 
            const formData = new FormData(this);
            const btnSubmit = this.querySelector('.btn-submit');

            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerText = 'Guardando...';
            }

            fetch(actionUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) throw data;
                return data;
            })
            .then(data => {
                alert(data.message || 'Trabajador registrado de manera exitosa.');
                formTrabajador.reset();
                cerrarModalTrabajador();
                location.reload(); 
            })
            .catch(error => {
                console.error('Error procesando el registro:', error);
                if (error.errors) {
                    let mensajes = '';
                    Object.values(error.errors).forEach(err => {
                        mensajes += err[0] + '\n';
                    });
                    alert(mensajes);
                } else {
                    alert(error.message || 'Error interno al comunicarse con el servidor.');
                }
            })
            .finally(() => {
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerText = 'Registrar Trabajador';
                }
            });
        });
    }
});