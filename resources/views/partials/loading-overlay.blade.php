<style>
#loading-overlay {
    display: none;
    position: fixed;
    inset: 0;
    width: 100vw;
    height: 100vh;
    background: #0f172a;
    z-index: 100000;
    justify-content: center;
    align-items: center;
}
#loading-overlay.active { display: flex; }
.loading-spinner {
    text-align: center;
    animation: spinnerIn 0.3s ease;
}
.spinner-circle {
    width: 56px; height: 56px;
    border: 4px solid rgba(255,255,255,0.15);
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin: 0 auto 20px;
}
.loading-text {
    font-size: 15px; font-weight: 500;
    color: rgba(255,255,255,0.7); margin: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }
@keyframes spinnerIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
</style>
<div id="loading-overlay">
    <div class="loading-spinner">
        <div class="spinner-circle"></div>
        <p class="loading-text" id="loadingText">Procesando...</p>
    </div>
</div>
