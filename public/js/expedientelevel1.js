// ============================================
// expedientelevel1.js — Puente legacy para la apertura de expedientes
// Ubicación: Vista de detalle de expediente
// Responsabilidades:
//   - Función global de compatibilidad para abrir expedientes desde HTML heredado
//   - Dispara un evento personalizado 'abrir-expediente' que los listeners modernos capturan
// ============================================

// ============================================
// Función legacy: verDetalleExpediente
// Llamada desde botones/links en HTML que no pueden modificarse fácilmente
// Dispara un CustomEvent para que el módulo de expedientes (más moderno) lo maneje
// ============================================
function verDetalleExpediente(id) {
    // Crea un evento personalizado con el ID del trabajador
    const event = new CustomEvent('abrir-expediente', { detail: { id } });
    // Lo despacha en el document — algún listener lo capturará y abrirá el modal/panel
    document.dispatchEvent(event);
}
