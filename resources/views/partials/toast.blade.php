<div id="toast-container"></div>

<div id="loading-overlay">
    <div class="loading-spinner">
        <div class="spinner-circle"></div>
        <p class="loading-text" id="loadingText">Procesando...</p>
    </div>
</div>

<style>
#toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 99999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    pointer-events: none;
}

.toast {
    pointer-events: auto;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px 20px;
    border-radius: 12px;
    min-width: 320px;
    max-width: 480px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    animation: toastIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
    font-family: inherit;
}

.toast.removing {
    animation: toastOut 0.25s ease forwards;
}

.toast-icon {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    margin-top: 2px;
}

.toast-content { flex: 1; }
.toast-title { font-size: 13px; font-weight: 700; margin-bottom: 4px; }
.toast-message { font-size: 12px; line-height: 1.4; }
.toast-close {
    flex-shrink: 0;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    line-height: 1;
    padding: 2px;
    opacity: 0.5;
    transition: opacity 0.2s;
}
.toast-close:hover { opacity: 1; }

.toast.success { background: #f0fdf4; border-color: #22c55e; }
.toast.success .toast-icon { background: #22c55e; color: white; }
.toast.success .toast-title { color: #15803d; }
.toast.success .toast-message { color: #166534; }
.toast.success .toast-close { color: #15803d; }

.toast.error { background: #fef2f2; border-color: #ef4444; }
.toast.error .toast-icon { background: #ef4444; color: white; }
.toast.error .toast-title { color: #b91c1c; }
.toast.error .toast-message { color: #991b1b; }
.toast.error .toast-close { color: #b91c1c; }

.toast.warning { background: #fff7ed; border-color: #f97316; }
.toast.warning .toast-icon { background: #f97316; color: white; }
.toast.warning .toast-title { color: #c2410c; }
.toast.warning .toast-message { color: #9a3412; }
.toast.warning .toast-close { color: #c2410c; }

.toast.info { background: #eff6ff; border-color: #3b82f6; }
.toast.info .toast-icon { background: #3b82f6; color: white; }
.toast.info .toast-title { color: #1e40af; }
.toast.info .toast-message { color: #1e3a8a; }
.toast.info .toast-close { color: #1e40af; }

@keyframes toastIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes toastOut {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}

/* === LOADING SPINNER === */
#loading-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    z-index: 100000;
    justify-content: center;
    align-items: center;
}

#loading-overlay.active {
    display: flex;
}

.loading-spinner {
    background: white;
    padding: 40px 50px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    animation: spinnerIn 0.3s ease;
}

.spinner-circle {
    width: 48px;
    height: 48px;
    border: 4px solid #e2e8f0;
    border-top-color: #2563eb;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin: 0 auto 16px;
}

.loading-text {
    font-size: 14px;
    font-weight: 600;
    color: #475569;
    margin: 0;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@keyframes spinnerIn {
    from { transform: scale(0.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
</style>

<script>
window.mostrarToast = function(message, type, title) {
    const TITLES = {
        success: 'Operación Exitosa',
        error: 'Error',
        warning: 'Advertencia',
        info: 'Información',
    };
    const ICONS = {
        success: '✓',
        error: '✕',
        warning: '!',
        info: 'i',
    };

    const container = document.getElementById('toast-container');
    if (!container) return;

    type = type || 'info';
    title = title || TITLES[type] || '';

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <div class="toast-icon">${ICONS[type] || 'i'}</div>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="this.closest('.toast').classList.add('removing');setTimeout(()=>this.closest('.toast').remove(),300)">&times;</button>
    `;
    container.appendChild(toast);

    const duracion = type === 'error' ? 6000 : type === 'warning' ? 5000 : 4000;
    setTimeout(() => {
        if (toast.parentNode) {
            toast.classList.add('removing');
            setTimeout(() => toast.remove(), 300);
        }
    }, duracion);
};

let _inicioCarga = 0;

window.mostrarCargando = function(texto) {
    const overlay = document.getElementById('loading-overlay');
    const textEl = document.getElementById('loadingText');
    if (textEl) textEl.textContent = texto || 'Procesando...';
    if (overlay) overlay.classList.add('active');
    _inicioCarga = Date.now();
};

window.ocultarCargando = function() {
    const transcurrido = Date.now() - _inicioCarga;
    const restante = 3000 - transcurrido;
    const overlay = document.getElementById('loading-overlay');
    if (!overlay) return;
    if (restante > 0) {
        setTimeout(() => overlay.classList.remove('active'), restante);
    } else {
        overlay.classList.remove('active');
    }
};
</script>
