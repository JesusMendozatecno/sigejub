// Funciones legacy para compatibilidad
function verDetalleExpediente(id) {
    const event = new CustomEvent('abrir-expediente', { detail: { id } });
    document.dispatchEvent(event);
}
