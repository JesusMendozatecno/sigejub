function verDetalleExpediente(nombre) {
    // Ocultar lista, mostrar detalle
    document.getElementById('expedientes-lista').style.display = 'none';
    document.getElementById('expediente-detalle').style.display = 'block';
    
    // Opcional: Cambiar el nombre dinámicamente
    document.getElementById('nombre-empleado-header').innerText = nombre;
    
    // Reiniciar Lucide para que los iconos carguen en la nueva vista
    lucide.createIcons();
}

function volverALista() {
    document.getElementById('expediente-detalle').style.display = 'none';
    document.getElementById('expedientes-lista').style.display = 'block';
}