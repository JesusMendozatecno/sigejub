/**
 * expedientelevel1.js — Script del nivel 1 de expedientes.
 * Gestiona la búsqueda de trabajadores por cédula y creación de expedientes.
 */

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
