# ACORDEON DE CARPETAS (EXPEDIENTES Y PRESTACIONES)

## Lógica
- Las carpetas se muestran por primera letra (A-Z)
- Al hacer clic en el chip o en el header de la carpeta, se ocultan los demás chips y solo se muestra esa carpeta completa
- Aparece botón 'Volver a carpetas' para regresar
- Solo se puede abrir una carpeta a la vez
- Al entrar al detalle de expediente/prestación se usa el botón 'Volver' existente

## Archivos involucrados
- resources/views/dashboard/secciones/expedientes.blade.php
- resources/views/dashboard/secciones/prestaciones.blade.php
- public/css/dashboard/secciones.css
- public/js/dashboard.js (limpieza de estado)

## Notas
- Las tarjetas dentro mantienen su funcionalidad de click
- El header de carpeta es clickeable
