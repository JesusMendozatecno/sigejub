<div id="aprobacion-consejo-content">
    <header class="section-header">
        <div class="header-info">
            <h1>Aprobación de <span class="text-blue-accent">Consejo</span></h1>
            <p>Expedientes completos listos para la carta de aprobación del consejo.</p>
        </div>
    </header>

    <div id="expedientesAprobacionGrid">
        <p class="empty-state">Cargando expedientes...</p>
    </div>
</div>

<script>
(function() {
    async function cargarListosAprobacion() {
        const grid = document.getElementById('expedientesAprobacionGrid');
        if (!grid) return;
        try {
            const resp = await fetch('/expedientes/listos-aprobacion');
            const data = await resp.json();

            if (!data.length) {
                grid.innerHTML = '<p class="empty-state">No hay expedientes completos pendientes de aprobación.</p>';
                return;
            }

            grid.innerHTML = '';
            data.forEach(exp => {
                const t = exp.trabajador || {};
                const card = document.createElement('div');
                card.className = 'expediente-card';
                card.innerHTML = `
                    <div class="ec-foto">
                        <div class="ec-avatar"><i class="fas fa-user-check" size="28"></i></div>
                    </div>
                    <div class="ec-info">
                        <strong>${escaparHTML(t.nombres)} ${escaparHTML(t.apellidos)}</strong>
                        <span>${escaparHTML(t.cedula || '—')}</span>
                        <span class="ec-badge completado">Completado (100%)</span>
                    </div>
                    <div class="ec-actions" style="display:flex;align-items:center;gap:8px;">
                        <form class="form-carta-aprobacion" data-id="${exp.id}">
                            <input type="file" name="carta" accept=".pdf" required style="font-size:0.8rem;">
                            <button type="submit" class="btn-submit" style="padding:6px 12px;font-size:0.8rem;">Subir Carta</button>
                        </form>
                    </div>
                `;
                grid.appendChild(card);
            });

            document.querySelectorAll('.form-carta-aprobacion').forEach(form => {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const id = form.dataset.id;
                    const formData = new FormData(form);
                    const btn = form.querySelector('.btn-submit');
                    btn.disabled = true; btn.textContent = 'Subiendo...';
                    try {
                        mostrarCargando('Subiendo carta de aprobación...');
                        const resp = await fetch(`/expedientes/${id}/carta-aprobacion`, {
                            method: 'POST', body: formData,
                            headers: { 'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value
                            }
                        });
                        const data = await resp.json();
                        if (!resp.ok) throw data;
                        mostrarToast(data.mensaje, 'success');
                        cargarListosAprobacion();
                    } catch (err) {
                        mostrarToast(err.mensaje || err.message || 'Error', 'error');
                    } finally {
                        ocultarCargando();
                        btn.disabled = false; btn.textContent = 'Subir Carta';
                    }
                });
            });
        } catch (err) {
            console.error('Error al cargar expedientes listos:', err);
            grid.innerHTML = '<p class="empty-state">Error al cargar expedientes.</p>';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach(m => {
                if (m.target.id === 'aprobacion-consejo' && m.target.classList.contains('active')) {
                    cargarListosAprobacion();
                }
            });
        });
        const seccion = document.getElementById('aprobacion-consejo');
        if (seccion) observer.observe(seccion, { attributes: true, attributeFilter: ['class'] });
    });
})();
</script>
