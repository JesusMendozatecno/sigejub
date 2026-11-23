{{-- prestaciones.blade.php --}}
<style>
.prestaciones-grid-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 18px; }
.prestacion-card { background: white; border-radius: 16px; overflow: hidden; cursor: pointer; transition: all 0.25s ease; border: 1px solid #f1f5f9; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.prestacion-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,35,102,0.1); border-color: #dbeafe; }
.prestacion-card .pc-foto { height: 120px; background: linear-gradient(135deg, #f8fafc, #eef2f6); display: flex; align-items: center; justify-content: center; position: relative; }
.prestacion-card .pc-foto::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 30px; background: linear-gradient(transparent, white); }
.prestacion-card .pc-foto img { width: 100%; height: 100%; object-fit: cover; }
.prestacion-card .pc-avatar { width: 64px; height: 64px; border-radius: 50%; background: #e2e8f0; color: #94a3b8; display: flex; align-items: center; justify-content: center; }
.prestacion-card .pc-info { padding: 14px 16px 16px; display: flex; flex-direction: column; gap: 4px; }
.prestacion-card .pc-info strong { font-size: 0.92rem; color: #0f172a; line-height: 1.3; }
.prestacion-card .pc-info .pc-cedula { font-size: 0.78rem; color: #64748b; }
.prestacion-card .pc-info .pc-row { display: flex; align-items: center; justify-content: space-between; margin-top: 6px; }
.prestacion-card .pc-anios { font-size: 0.75rem; font-weight: 700; color: #1e3a8a; background: #eff6ff; padding: 3px 10px; border-radius: 10px; }
.prestacion-card .pc-badge { font-size: 0.68rem; font-weight: 700; color: #1e40af; background: #dbeafe; padding: 3px 10px; border-radius: 10px; }
.prestacion-card .pc-titulo { font-size: 0.7rem; font-weight: 700; color: #166534; background: #dcfce7; padding: 3px 10px; border-radius: 10px; display: inline-flex; align-items: center; gap: 5px; }
.prestacion-card .pc-monto { font-size: 0.75rem; font-weight: 700; color: #16a34a; background: #ecfdf5; padding: 3px 10px; border-radius: 10px; }

.prestacion-detalle-layout { display: flex; flex-direction: column; gap: 16px; height: calc(100vh - 200px); overflow: hidden; }
.prestacion-info-superior { display: grid; grid-template-columns: auto 1fr auto; gap: 16px; align-items: center; background: white; border-radius: 14px; padding: 16px 20px; border: 1px solid #f1f5f9; box-shadow: 0 2px 8px rgba(0,0,0,0.04); flex-shrink: 0; }
.prestacion-cuerpo { display: grid; grid-template-columns: 1.4fr 1fr; gap: 16px; flex: 1; min-height: 0; }
.prestacion-col-izq, .prestacion-col-der { background: white; border-radius: 14px; padding: 16px 20px; border: 1px solid #f1f5f9; box-shadow: 0 2px 8px rgba(0,0,0,0.04); overflow-y: auto; }
.prestacion-col-der { display: flex; flex-direction: column; }

.worker-mini-card { display: flex; gap: 12px; align-items: center; }
.worker-mini-card .wm-foto { width: 44px; height: 44px; border-radius: 50%; background: #eff6ff; display: flex; align-items: center; justify-content: center; color: #1e40af; flex-shrink: 0; overflow: hidden; }
.worker-mini-card .wm-foto img { width: 100%; height: 100%; object-fit: cover; }
.worker-mini-card h4 { font-size: 0.95rem; color: #0f172a; margin: 0; }
.worker-mini-card p { font-size: 0.78rem; color: #64748b; margin: 2px 0 0; }

.prestacion-actions-bar { display: flex; gap: 8px; align-items: center; }
.prestacion-actions-bar .badge-st { font-size: 0.7rem; padding: 4px 12px; border-radius: 10px; font-weight: 700; white-space: nowrap; }

.subtitulo-sec { font-size: 0.8rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px; padding-bottom: 8px; border-bottom: 2px solid #e2e8f0; margin-bottom: 10px; }
.subtitulo-sec i { color: #2563eb; }

.fila-sueldo { display: flex; align-items: center; gap: 10px; padding: 6px 0; }
.fila-sueldo label { font-size: 0.75rem; color: #64748b; font-weight: 600; white-space: nowrap; }
.fila-sueldo input { flex: 1; padding: 7px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.85rem; font-weight: 600; background: #f8fafc; outline: none; max-width: 180px; }
.fila-sueldo input:focus { border-color: #2563eb; background: white; box-shadow: 0 0 0 3px rgba(37,99,235,0.08); }

.prima-row { display: grid; grid-template-columns: 1fr 90px 1fr; gap: 8px; align-items: center; padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
.prima-row:last-child { border-bottom: none; }
.prima-info .prima-nombre { font-size: 0.8rem; font-weight: 600; color: #0f172a; }
.prima-info .prima-codigo { font-size: 0.65rem; color: #94a3b8; font-family: monospace; }
.prima-valor { font-size: 0.75rem; font-weight: 700; color: #475569; text-align: right; }
.prima-valor span { display: block; font-size: 0.6rem; color: #94a3b8; font-weight: 400; }
.prima-auto { font-size: 0.8rem; font-weight: 600; color: #0f172a; background: #f0fdf4; padding: 6px 10px; border-radius: 6px; border: 1px solid #bbf7d0; text-align: center; }
.prima-auto.na { background: #f8fafc; color: #94a3b8; border-color: #e2e8f0; }

.pct-area { display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border-radius: 8px; padding: 10px 14px; margin-top: 10px; border: 1px solid #e2e8f0; gap: 10px; }
.pct-area strong { font-size: 0.78rem; color: #0f172a; white-space: nowrap; }
.pct-toggle { display: flex; gap: 3px; }
.pct-toggle button { padding: 6px 14px; border: 1px solid #e2e8f0; background: white; border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer; color: #64748b; }
.pct-toggle button.active { background: #1a365d; color: white; border-color: #1a365d; }
.pct-toggle button span { display: block; font-size: 0.6rem; opacity: 0.7; }
.moneda-toggle { display: inline-flex; gap: 3px; margin: 2px 0 8px; }
.moneda-toggle button { padding: 5px 12px; border: 1px solid #e2e8f0; background: white; border-radius: 6px; font-size: 0.78rem; font-weight: 700; cursor: pointer; color: #64748b; }
.moneda-toggle button.active { background: #16a34a; color: white; border-color: #16a34a; }
body.dark-mode .moneda-toggle button { background: #1e293b; border-color: #334155; color: #94a3b8; }
body.dark-mode .moneda-toggle button.active { background: #16a34a; color: white; border-color: #16a34a; }

.btn-prestacion { padding: 10px 16px; border-radius: 8px; font-size: 0.82rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; justify-content: center; transition: all 0.2s; }
.btn-prestacion-primario { background: #1a365d; color: white; }
.btn-prestacion-primario:hover { background: #1e3a8a; box-shadow: 0 4px 12px rgba(26,54,93,0.25); }
.btn-prestacion-exito { background: #16a34a; color: white; }
.btn-prestacion-exito:hover { background: #15803d; }
.btn-prestacion-info { background: #2563eb; color: white; }
.btn-prestacion-info:hover { background: #1d4ed8; }
.btn-prestacion:disabled { opacity: 0.5; cursor: not-allowed; }

.resumen-primas { overflow-y: auto; }
.resumen-item { display: flex; justify-content: space-between; align-items: center; padding: 7px 0; border-bottom: 1px solid #f1f5f9; font-size: 0.78rem; }
.resumen-item:last-child { border-bottom: none; }
.resumen-item .r-label { color: #64748b; }
.resumen-item .r-value { font-weight: 700; color: #0f172a; }

.total-prestacion-box { background: linear-gradient(135deg, #1a365d, #1e3a8a); border-radius: 10px; padding: 14px 18px; color: white; margin-top: 10px; flex-shrink: 0; }
.total-prestacion-box p { font-size: 0.65rem; opacity: 0.7; margin: 0; }
.total-prestacion-box h2 { font-size: 1.3rem; margin: 4px 0 0; }

.btn-volver { display: inline-flex; align-items: center; gap: 6px; background: none; border: 1px solid #e2e8f0; padding: 7px 14px; border-radius: 8px; color: #475569; font-size: 0.78rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-volver:hover { background: #f1f5f9; border-color: #cbd5e1; color: #1a365d; }

/* El estilo de la ventana modal (.modal-resultado / .modal-rfila) vive en css/dashboard/secciones/modal.css */

.hidden { display: none !important; }

@media (max-width: 1024px) { .prestacion-cuerpo { grid-template-columns: 1fr; } .prestacion-detalle-layout { height: auto; overflow: visible; } }
body.dark-mode .prestacion-card { background: #1e293b; border-color: #334155; }
body.dark-mode .prestacion-card:hover { border-color: #3b82f6; }
body.dark-mode .prestacion-card .pc-foto { background: linear-gradient(135deg, #1a2332, #1e293b); }
body.dark-mode .prestacion-card .pc-foto::after { background: linear-gradient(transparent, #1e293b); }
body.dark-mode .prestacion-card .pc-avatar { background: #334155; color: #64748b; }
body.dark-mode .prestacion-card .pc-info strong { color: #f1f5f9; }
body.dark-mode .prestacion-card .pc-cedula { color: #94a3b8; }
body.dark-mode .prestacion-card .pc-anios { background: #1e3a5f; color: #93c5fd; }
body.dark-mode .prestacion-card .pc-badge { background: #1e3a5f; color: #93c5fd; }
body.dark-mode .prestacion-card .pc-titulo { background: #14532d; color: #86efac; }
body.dark-mode .prestacion-card .pc-monto { background: #14532d; color: #86efac; }
body.dark-mode .prestacion-info-superior { background: #1e293b; border-color: #334155; }
body.dark-mode .worker-mini-card h4 { color: #f1f5f9; }
body.dark-mode .worker-mini-card p { color: #94a3b8; }
body.dark-mode .prestacion-col-izq, body.dark-mode .prestacion-col-der { background: #1e293b; border-color: #334155; }
body.dark-mode .subtitulo-sec { color: #f1f5f9; border-bottom-color: #334155; }
body.dark-mode .prima-row { border-bottom-color: #334155; }
body.dark-mode .prima-info .prima-nombre { color: #f1f5f9; }
body.dark-mode .prima-auto { background: #14532d; border-color: #166534; color: #86efac; }
body.dark-mode .prima-auto.na { background: #1e293b; color: #64748b; border-color: #334155; }
body.dark-mode .pct-area { background: #1a2332; border-color: #334155; }
body.dark-mode .pct-area strong { color: #f1f5f9; }
body.dark-mode .pct-toggle button { background: #1e293b; border-color: #334155; color: #94a3b8; }
body.dark-mode .pct-toggle button.active { background: #1e40af; border-color: #1e40af; color: white; }
body.dark-mode .resumen-item { border-bottom-color: #334155; }
body.dark-mode .resumen-item .r-label { color: #94a3b8; }
body.dark-mode .resumen-item .r-value { color: #f1f5f9; }
/* Los estilos de la ventana modal en tema oscuro viven en css/dashboard/secciones/modal.css */
/* ==== Prestaciones: mismas tarjetas oscuras que Expedientes ==== */
#prestaciones #prestacionesGrid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
    margin-top: 16px;
    align-items: stretch;
}
#prestaciones .grupo-alfabetico { margin: 0; min-width: 0; }
#prestaciones .grupo-alfabetico-detalle { grid-column: 1 / -1; min-width: 0; }

/* ---- Tarjeta de letra ---- */
#prestaciones .grupo-alfabetico-header {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    justify-content: flex-start;
    gap: 10px;
    width: 100%;
    min-height: 0;
    padding: 20px;
    overflow: hidden;
    text-align: left;
    cursor: pointer;
    border: 1px solid color-mix(in srgb, var(--accent, #1a365d) 60%, #7f9cc4);
    border-radius: 14px;
    background:
        radial-gradient(130% 100% at 100% 0%, color-mix(in srgb, var(--accent, #1a365d) 70%, transparent) 0%, transparent 62%),
        linear-gradient(160deg, #0f1a2b 0%, #15243a 52%, #1a365d 100%);
    box-shadow: 0 6px 18px rgba(15, 23, 42, .20);
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
}
#prestaciones .grupo-alfabetico-header::before {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-image: radial-gradient(rgba(255, 255, 255, .12) 1px, transparent 1px);
    background-size: 15px 15px;
    opacity: .55;
    -webkit-mask-image: linear-gradient(155deg, #000 0%, transparent 72%);
    mask-image: linear-gradient(155deg, #000 0%, transparent 72%);
}
#prestaciones .grupo-alfabetico-header:hover,
#prestaciones .grupo-alfabetico-header:focus-visible {
    transform: translateY(-4px);
    border-color: color-mix(in srgb, var(--accent, #1a365d) 18%, #a8c4ea);
    box-shadow: 0 18px 34px rgba(15, 23, 42, .34);
    outline: none;
}
#prestaciones .grupo-alfabetico-header:focus-visible {
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent, #1a365d) 40%, transparent), 0 18px 34px rgba(15, 23, 42, .34);
}
#prestaciones .grupo-alfabetico-card-top {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    width: 100%;
}
#prestaciones .grupo-alfabetico-icon {
    width: 46px;
    height: 46px;
    flex: 0 0 46px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, .22);
    background: rgba(255, 255, 255, .12);
    color: #eaf2ff;
    font-size: 1.15rem;
}
#prestaciones .grupo-alfabetico-badge {
    padding: 3px 8px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .24);
    background: rgba(255, 255, 255, .14);
    color: #eaf2ff;
    font-size: .62rem;
    font-weight: 800;
    letter-spacing: .4px;
    text-transform: uppercase;
    white-space: nowrap;
}
#prestaciones .grupo-alfabetico-letra {
    position: relative;
    z-index: 1;
    font-size: 1.5rem;
    line-height: 1;
    font-weight: 800;
    font-style: italic;
    letter-spacing: -.5px;
    color: #fff;
    text-shadow: 0 6px 18px rgba(0, 0, 0, .45);
}
#prestaciones .grupo-alfabetico-count {
    position: relative;
    z-index: 1;
    color: rgba(214, 227, 245, .78);
    font-size: .75rem;
    font-weight: 600;
}

/* ---- Cabecera de la letra abierta ---- */
#prestaciones .grupo-alfabetico-detail-header {
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 20px 22px;
    margin-bottom: 18px;
    border: 1px solid color-mix(in srgb, var(--accent, #1a365d) 55%, #7f9cc4);
    border-radius: 18px;
    background:
        radial-gradient(120% 100% at 0% 0%, color-mix(in srgb, var(--accent, #1a365d) 62%, transparent) 0%, transparent 66%),
        linear-gradient(140deg, #0f1a2b 0%, #1a365d 100%);
    box-shadow: 0 6px 18px rgba(15, 23, 42, .20);
}
#prestaciones .grupo-alfabetico-detail-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
#prestaciones .grupo-alfabetico-detail-header .btn-volver-prestaciones-letras {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    width: auto;
    height: auto;
    min-width: 0;
    padding: 8px 15px;
    border: 1px solid rgba(255, 255, 255, .30);
    border-radius: 999px;
    background: rgba(255, 255, 255, .10);
    color: #eaf2ff;
    font-size: .75rem;
    font-weight: 700;
    white-space: nowrap;
    cursor: pointer;
    transition: background .2s ease, border-color .2s ease;
}
#prestaciones .grupo-alfabetico-detail-header .btn-volver-prestaciones-letras:hover {
    background: rgba(255, 255, 255, .20);
    border-color: rgba(255, 255, 255, .45);
}
#prestaciones .grupo-alfabetico-detail-header h2 {
    margin: 10px 0 0;
    font-size: 1.6rem;
    font-weight: 800;
    font-style: italic;
    letter-spacing: -.5px;
    color: #fff;
}
#prestaciones .grupo-alfabetico-detail-header p {
    margin: 0;
    font-size: .8rem;
    font-weight: 600;
    color: rgba(214, 227, 245, .78);
}

/* ---- Tarjetas de trabajador ---- */
#prestaciones .prestaciones-grid-cards {
    grid-template-columns: repeat(auto-fill, minmax(205px, 1fr));
    gap: 16px;
}
#prestaciones .prestacion-card {
    position: relative;
    display: flex;
    flex-direction: column;
    padding: 0;
    overflow: hidden;
    border: 1px solid color-mix(in srgb, var(--accent, #1a365d) 50%, #6f8fbb);
    border-radius: 16px;
    background: linear-gradient(180deg, #17263c 0%, #101a2b 100%);
    box-shadow: 0 6px 16px rgba(15, 23, 42, .18);
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
}
#prestaciones .prestacion-card:hover {
    transform: translateY(-4px);
    border-color: color-mix(in srgb, var(--accent, #1a365d) 14%, #a8c4ea);
    box-shadow: 0 16px 30px rgba(15, 23, 42, .32);
}
#prestaciones .prestacion-card .pc-foto {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 132px;
    border-radius: 0;
    overflow: hidden;
    background:
        radial-gradient(120% 100% at 50% 0%, color-mix(in srgb, var(--accent, #1a365d) 78%, transparent) 0%, transparent 72%),
        linear-gradient(160deg, #1a365d 0%, #101a2b 100%);
}
#prestaciones .prestacion-card .pc-foto::after { display: none; }
#prestaciones .prestacion-card .pc-foto img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center 25%;
}
#prestaciones .prestacion-card .pc-avatar {
    width: 62px;
    height: 62px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, .22);
    background: rgba(255, 255, 255, .12);
    color: #dbe8f8;
    font-size: 1.4rem;
}
#prestaciones .prestacion-card .pc-info {
    flex: 1 1 auto;
    gap: 4px;
    padding: 13px 15px 15px;
}
#prestaciones .prestacion-card .pc-info strong {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 2.5em;
    font-size: .95rem;
    line-height: 1.3;
    font-weight: 800;
    color: #fff;
}
#prestaciones .prestacion-card .pc-info .pc-cedula {
    font-size: .74rem;
    font-weight: 600;
    color: rgba(203, 216, 234, .75);
}
#prestaciones .prestacion-card .pc-info .pc-row {
    margin-top: 8px;
    justify-content: flex-start;
    flex-wrap: wrap;
    gap: 6px;
}
#prestaciones .prestacion-card .pc-info .pc-row:last-child {
    margin-top: auto;
    padding-top: 10px;
}
#prestaciones .prestacion-card .pc-anios,
#prestaciones .prestacion-card .pc-badge,
#prestaciones .prestacion-card .pc-monto,
#prestaciones .prestacion-card .pc-titulo {
    padding: 4px 10px;
    border: 1px solid transparent;
    border-radius: 999px;
    font-size: .63rem;
    font-weight: 800;
    line-height: 1.35;
    letter-spacing: .3px;
    text-transform: uppercase;
    white-space: normal;
}
#prestaciones .prestacion-card .pc-anios {
    color: #bfdbfe;
    border-color: rgba(147, 197, 253, .45);
    background: rgba(30, 58, 138, .60);
}
#prestaciones .prestacion-card .pc-badge {
    color: #e2e8f0;
    border-color: rgba(203, 213, 225, .35);
    background: rgba(51, 65, 85, .65);
}
#prestaciones .prestacion-card .pc-titulo {
    color: #a7f3d0;
    border-color: rgba(110, 231, 183, .45);
    background: rgba(6, 78, 59, .55);
    text-transform: none;
    letter-spacing: 0;
}
#prestaciones .prestacion-card .pc-monto {
    color: #bbf7d0;
    border-color: rgba(74, 222, 128, .45);
    background: rgba(6, 78, 59, .75);
}
#prestaciones #prestacionesPagination {
    margin-top: 20px;
    padding-top: 0;
    border-top: 0;
}
#prestaciones #prestacionesPagination:empty { display: none; }

body.dark-mode #prestaciones .grupo-alfabetico-header,
body.dark-mode #prestaciones .grupo-alfabetico-detail-header,
body.dark-mode #prestaciones .prestacion-card {
    border-color: color-mix(in srgb, var(--accent, #1a365d) 55%, #3b4f6d);
}

/* ==== Modo claro: las mismas tarjetas en tonos claros del sistema ==== */
body:not(.dark-mode) #prestaciones .grupo-alfabetico-header {
    border-color: #cddcf0;
    background:
        radial-gradient(130% 100% at 100% 0%, rgba(37, 99, 235, .12) 0%, transparent 62%),
        linear-gradient(160deg, #ffffff 0%, #f5f9ff 55%, #e9f1fb 100%);
    box-shadow: 0 6px 18px rgba(15, 23, 42, .08);
}
body:not(.dark-mode) #prestaciones .grupo-alfabetico-header::before {
    background-image: radial-gradient(rgba(26, 54, 93, .10) 1px, transparent 1px);
    opacity: .5;
}
body:not(.dark-mode) #prestaciones .grupo-alfabetico-header:hover,
body:not(.dark-mode) #prestaciones .grupo-alfabetico-header:focus-visible {
    border-color: #9dbbe4;
    box-shadow: 0 16px 30px rgba(26, 54, 93, .14);
}
body:not(.dark-mode) #prestaciones .grupo-alfabetico-header:focus-visible {
    box-shadow: 0 0 0 3px rgba(37, 99, 235, .28), 0 16px 30px rgba(26, 54, 93, .14);
}
body:not(.dark-mode) #prestaciones .grupo-alfabetico-icon {
    border-color: #cfdff5;
    background: #eef4fc;
    color: #1d4ed8;
}
body:not(.dark-mode) #prestaciones .grupo-alfabetico-badge {
    border-color: #cfdff5;
    background: #eef4fc;
    color: #1d4ed8;
}
body:not(.dark-mode) #prestaciones .grupo-alfabetico-letra {
    color: #1a365d;
    text-shadow: 0 6px 18px rgba(26, 54, 93, .14);
}
body:not(.dark-mode) #prestaciones .grupo-alfabetico-count { color: #5b7799; }
body:not(.dark-mode) #prestaciones .grupo-alfabetico-detail-header {
    border-color: #cddcf0;
    background:
        radial-gradient(120% 100% at 0% 0%, rgba(37, 99, 235, .12) 0%, transparent 66%),
        linear-gradient(140deg, #ffffff 0%, #eaf1fb 100%);
    box-shadow: 0 6px 18px rgba(15, 23, 42, .08);
}
body:not(.dark-mode) #prestaciones .grupo-alfabetico-detail-header .btn-volver-prestaciones-letras {
    border-color: #bfd3ee;
    background: #eef4fc;
    color: #1d4ed8;
}
body:not(.dark-mode) #prestaciones .grupo-alfabetico-detail-header .btn-volver-prestaciones-letras:hover {
    background: #dfeafb;
    border-color: #9dbbe4;
}
body:not(.dark-mode) #prestaciones .grupo-alfabetico-detail-header h2 { color: #1a365d; }
body:not(.dark-mode) #prestaciones .grupo-alfabetico-detail-header p { color: #5b7799; }
body:not(.dark-mode) #prestaciones .prestacion-card {
    border-color: #dbe6f5;
    background: #ffffff;
    box-shadow: 0 6px 16px rgba(15, 23, 42, .07);
}
body:not(.dark-mode) #prestaciones .prestacion-card:hover {
    border-color: #b6cdea;
    box-shadow: 0 16px 30px rgba(26, 54, 93, .14);
}
body:not(.dark-mode) #prestaciones .prestacion-card .pc-foto {
    background:
        radial-gradient(120% 100% at 50% 0%, rgba(37, 99, 235, .16) 0%, transparent 72%),
        linear-gradient(160deg, #e8f0fb 0%, #f7fafe 100%);
}
body:not(.dark-mode) #prestaciones .prestacion-card .pc-avatar {
    border-color: #cfdff5;
    background: #ffffff;
    color: #1d4ed8;
}
body:not(.dark-mode) #prestaciones .prestacion-card .pc-info strong { color: #0f172a; }
body:not(.dark-mode) #prestaciones .prestacion-card .pc-info .pc-cedula { color: #5b7799; }
body:not(.dark-mode) #prestaciones .prestacion-card .pc-anios { color: #1e40af; border-color: #bfdbfe; background: #eff6ff; }
body:not(.dark-mode) #prestaciones .prestacion-card .pc-badge { color: #334155; border-color: #cbd5e1; background: #f1f5f9; }
body:not(.dark-mode) #prestaciones .prestacion-card .pc-titulo { color: #166534; border-color: #bbf7d0; background: #dcfce7; }
body:not(.dark-mode) #prestaciones .prestacion-card .pc-monto { color: #15803d; border-color: #bbf7d0; background: #ecfdf5; }

@media (max-width: 767px) {
    #prestaciones .prestaciones-grid-cards {
        grid-template-columns: minmax(0, 1fr);
    }
}
</style>

<header class="section-header">
    <div class="header-info">
        <h1>Cálculo de <span class="text-blue-accent">Prestaciones</span></h1>
        <p>Seleccione un trabajador para ver y calcular sus prestaciones sociales.</p>
    </div>
</header>

<div id="prestaciones-lista">
    <p class="section-description" style="font-size:0.9rem;margin-bottom:16px;">Trabajadores con solicitud aprobada y expediente registrado</p>
    <div class="prestaciones-grid-cards" id="prestacionesGrid">
        <p class="empty-state">Cargando...</p>
    </div>
    <div class="activity-pagination" id="prestacionesPagination"></div>
</div>

<div id="prestacion-detalle" class="hidden">
    <div class="prestacion-detalle-layout">
        <div class="prestacion-info-superior">
            <button class="btn-volver" id="btnVolverPrestaciones">
                <i class="fas fa-arrow-left"></i> Volver
            </button>
            <div class="worker-mini-card">
                <div class="wm-foto" id="detalleFotoMini"><i class="fas fa-user" style="font-size:18px;"></i></div>
                <div>
                    <h4 id="detalleNombreMini"></h4>
                    <p id="detalleInfoMini"></p>
                </div>
            </div>
            <div class="prestacion-actions-bar">
                <span class="badge-st" id="badgeEdad" style="background:#eff6ff;color:#1e40af;">—</span>
                <span class="badge-st" id="badgeTipo" style="background:#ecfdf5;color:#059669;">—</span>
                <span class="badge-st" id="badgeTitulo" style="background:#f0fdf4;color:#166534;display:none;">
                    <i class="fas fa-graduation-cap"></i> <span id="badgeTituloTexto"></span>
                </span>
                <button class="btn-prestacion btn-prestacion-info hidden" id="btnGenerarComprobante">
                    <i class="fas fa-file-invoice"></i> Comprobante
                </button>
            </div>
        </div>

        <div class="prestacion-cuerpo">
            <div class="prestacion-col-izq">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                    <span style="font-size:0.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Moneda de cálculo</span>
                    <div class="moneda-toggle" id="monedaToggle" style="margin:0;">
                        <button type="button" class="active" data-moneda="Bs">Bs.</button>
                        <button type="button" data-moneda="USD">$</button>
                    </div>
                </div>
                <div class="subtitulo-sec"><i class="fas fa-coins"></i> Sueldo Base</div>
                <div class="fila-sueldo">
                    <label id="lblSueldoBase">SUELDO BASE MENSUAL (Bs.)</label>
                    <input type="number" step="0.01" min="0" id="inputSueldoBase" value="0.00" onkeypress="if(!/[0-9.]/.test(event.key))event.preventDefault()">
                </div>

                <div class="subtitulo-sec" style="margin-top:12px;"><i class="fas fa-vault"></i> Fideicomiso (opcional)</div>
                <div class="fila-sueldo">
                    <label style="min-width:150px;">MONTO FIDEICOMISO (Bs.)</label>
                    <input type="number" step="0.01" min="0" id="inputFideicomisoMonto" placeholder="0.00" style="flex:1;" onkeypress="if(!/[0-9.]/.test(event.key))event.preventDefault()">
                    <label style="min-width:90px;">OBSERVACIONES</label>
                    <input type="text" id="inputFideicomisoObs" maxlength="2000" placeholder="Ej.: Depósito en cuenta de ahorro" style="flex:2;">
                </div>
                <div id="fideicomisoDocBox" class="hidden" style="display:none;gap:8px;align-items:center;flex-wrap:wrap;margin-top:4px;font-size:0.75rem;color:#64748b;">
                    <a id="fideicomisoDocLink" href="#" target="_blank" style="color:#2563eb;font-weight:700;"><i class="fas fa-file-pdf"></i> Ver comprobante adjunto</a>
                    <button type="button" id="btnSubirFideicomisoDoc" style="border:1px dashed #94a3b8;background:transparent;border-radius:8px;padding:4px 10px;font-size:0.72rem;cursor:pointer;"><i class="fas fa-upload"></i> Reemplazar comprobante</button>
                    <input type="file" id="inputFideicomisoDoc" accept=".pdf,.jpg,.jpeg,.png" style="display:none;">
                </div>
                <button type="button" id="btnAdjuntarFideicomisoDoc" style="border:1px dashed #94a3b8;background:transparent;border-radius:8px;padding:5px 10px;font-size:0.72rem;cursor:pointer;margin-top:6px;"><i class="fas fa-paperclip"></i> Adjuntar comprobante de fideicomiso</button>

                <div class="subtitulo-sec" style="margin-top:12px;"><i class="fas fa-calculator"></i> Primas Aplicables</div>
                <div id="primasContainer" style="max-height:280px;overflow-y:auto;"><p style="color:#94a3b8;font-size:0.8rem;">Cargando primas...</p></div>

                <div class="pct-area">
                    <strong>Porcentaje de Jubilación</strong>
                    <div class="pct-toggle">
                        <button class="active" type="button" data-pct="100">100% <span>Jubilación</span></button>
                        <button type="button" data-pct="82.5">82.5% <span>Incapacidad</span></button>
                    </div>
                </div>

                <button class="btn-prestacion btn-prestacion-primario" id="btnCalcularPrestacion" style="width:100%;margin-top:12px;">
                    <i class="fas fa-calculator"></i> Calcular Prestaciones
                </button>
            </div>

            <div class="prestacion-col-der">
                <div class="subtitulo-sec"><i class="fas fa-list-check"></i> Resumen de Primas</div>
                <div class="resumen-primas" id="sidebarPrimasResumen">
                    <p style="color:#94a3b8;font-size:0.8rem;">Presione "Calcular Prestaciones" para ver el resumen.</p>
                </div>

                <div class="subtitulo-sec" style="margin-top:14px;"><i class="fas fa-file-contract"></i> Cláusulas de Convención Colectiva</div>
                <div class="resumen-primas" id="sidebarClausulasResumen">
                    <p style="color:#94a3b8;font-size:0.8rem;">Este expediente no tiene cláusulas asignadas.</p>
                </div>
                <p id="clausulasNota" style="font-size:0.72rem;color:#64748b;margin:6px 0 0;line-height:1.45;">
                    Las cláusulas se asignan en el expediente del trabajador y su beneficio se suma al total de prestaciones.
                </p>
                <div class="total-prestacion-box">
                    <p>MONTO TOTAL PRESTACIONES</p>
                    <h2 id="totalPrestacionDisplay">Bs. 0,00</h2>
                </div>
                <div class="resumen-item" style="border-top:2px solid #e2e8f0;margin-top:8px;padding-top:10px;">
                    <span class="r-label"><i class="fas fa-vault" style="color:#2563eb;"></i> Fideicomiso</span>
                    <span class="r-value" id="totalFideicomisoDisplay">Bs. 0,00</span>
                </div>
                <div class="resumen-item" style="border-top:2px dashed #94a3b8;">
                    <span class="r-label" style="font-weight:700;color:#0f172a;">TOTAL GENERAL</span>
                    <span class="r-value" id="totalGeneralDisplay" style="color:#16a34a;">Bs. 0,00</span>
                </div>
                <div id="tasaInfoPrestacion" style="background:#f8fafc;border-radius:8px;padding:10px 14px;margin-top:10px;border:1px solid #e2e8f0;font-size:0.75rem;">
                    <div style="font-weight:700;color:#0f172a;margin-bottom:4px;"><i class="fas fa-dollar-sign" style="color:#2563eb;"></i> Tasa de Cambio</div>
                    <div id="tasaDetallePrestacion" style="color:#64748b;">Cargando...</div>
                </div>
                <button class="btn-prestacion btn-prestacion-exito" id="btnGuardarPrestacion" style="width:100%;margin-top:10px;">
                    <i class="fas fa-save"></i> Guardar y Generar Nómina
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal-resultado" id="modalResultado">
    <div class="modal-resultado-box">
        <h3><i class="fas fa-file-invoice-dollar"></i> Resultado del Cálculo</h3>
        <div id="modalResultadoBody">
            <div class="modal-rfila"><span class="ml">Sueldo Base</span><span class="mr" id="mrSueldoBase">Bs. 0,00</span></div>
            <div class="modal-rfila"><span class="ml">Total Primas</span><span class="mr" id="mrTotalPrimas">Bs. 0,00</span></div>
            <div class="modal-rfila"><span class="ml">Sueldo Integral Mensual</span><span class="mr" id="mrSueldoIntegral">Bs. 0,00</span></div>
            <div class="modal-rfila"><span class="ml">Porcentaje Aplicable</span><span class="mr" id="mrPorcentaje">100%</span></div>
            <div class="modal-rfila"><span class="ml">Años de Servicio</span><span class="mr" id="mrAnios">0</span></div>
            <div class="modal-rfila"><span class="ml">Cláusulas de convención colectiva</span><span class="mr" id="mrClausulas">Bs. 0,00</span></div>
            <div class="modal-rfila"><span class="ml">MONTO TOTAL PRESTACIONES</span><span class="mr" id="mrTotal">Bs. 0,00</span></div>
            <div class="modal-rfila"><span class="ml">Fideicomiso</span><span class="mr" id="mrFideicomiso">Bs. 0,00</span></div>
            <div class="modal-rfila total"><span class="ml">TOTAL GENERAL (Prestaciones + Fideicomiso)</span><span class="mr" id="mrTotalGeneral">Bs. 0,00</span></div>
        </div>
        <div style="display:flex;gap:8px;margin-top:16px;">
            <button class="btn-prestacion btn-prestacion-exito" id="btnGuardarDesdeModal" style="flex:1;">
                <i class="fas fa-save"></i> Guardar
            </button>
            <button class="btn-prestacion btn-prestacion-primario" id="btnCerrarModal" style="flex:1;background:#64748b;">
                Cerrar
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalAnioPrestacion" style="z-index:5500;">
    <div class="modal-delete-box" style="text-align:left;max-width:420px;">
        <h3 style="text-align:center;"><i class="fas fa-calendar-alt"></i> Período de la Prestación</h3>
        <p style="text-align:center;color:#64748b;font-size:0.85rem;margin-bottom:16px;">
            Indique el mes y el año de la nómina a la que pertenece esta prestación.
        </p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="input-group">
                <label>MES DE LA PRESTACIÓN</label>
                <select id="mesPrestacionSelect" required>
                    <option value="">Seleccione el mes</option>
                    @foreach (\App\Models\Nomina::MESES as $numMes => $nombreMes)
                        <option value="{{ $numMes }}" @if ($numMes === (int) now()->month) selected @endif>{{ $nombreMes }}</option>
                    @endforeach
                </select>
            </div>
            <div class="input-group">
                <label>AÑO DE LA PRESTACIÓN</label>
                <select id="anioPrestacionSelect" required>
                    <option value="">Seleccione el año</option>
                </select>
            </div>
        </div>
        <div class="modal-actions" style="justify-content:center;">
            <button type="button" class="btn-cancel" id="btnCancelarAnioPrestacion">Cancelar</button>
            <button type="button" class="btn-submit" id="btnConfirmarAnioPrestacion"><i class="fas fa-arrow-right"></i> Continuar</button>
        </div>
    </div>
</div>

<script>
(function() {
    let trabajadorActualId = null;
    let datosTrabajador = null;
    let primasDisponibles = [];
    let porcentajeActual = 100;
    let ultimoCalculo = null;
    let monedaActual = 'Bs';
    let tasaActual = 0;
    let clausulasActuales = [];

    // Monto en Bs. que aporta una cláusula de la convención colectiva.
    // Las cláusulas informativas (p. ej. la Cláusula 55 de pensión) no suman:
    // su efecto ya está en el porcentaje de jubilación (100% / 82.5%).
    function montoClausula(clausula, sueldoBase, sueldoIntegral, anios) {
        const valor = parseFloat(clausula.valor) || 0;
        let monto = 0;
        switch (clausula.tipo_calculo) {
            case 'porcentaje_sueldo_base': monto = sueldoBase * (valor / 100); break;
            case 'porcentaje_sueldo_integral': monto = sueldoIntegral * (valor / 100); break;
            case 'dias_sueldo_integral': monto = sueldoIntegral > 0 ? (sueldoIntegral / 30) * valor : 0; break;
            case 'monto_fijo': monto = valor; break;
            default: monto = 0;
        }
        if (clausula.por_anio) monto = monto * (parseFloat(anios) || 0);
        return monto;
    }

    // Descripción corta de la regla de cálculo de la cláusula.
    function detalleClausula(c) {
        const valor = parseFloat(c.valor) || 0;
        let texto = '';
        switch (c.tipo_calculo) {
            case 'porcentaje_sueldo_base': texto = valor + '% del sueldo base'; break;
            case 'porcentaje_sueldo_integral': texto = valor + '% del sueldo integral'; break;
            case 'dias_sueldo_integral': texto = valor + ' días de salario integral'; break;
            case 'monto_fijo': texto = fmtBs(valor) + ' fijo'; break;
            default: texto = 'Informativa (sin monto adicional)'; break;
        }
        if (c.por_anio) texto += ' por año de servicio';
        return texto;
    }

    // Pinta la lista de cláusulas del expediente. Si recibe montos calculados,
    // los muestra; si no, solo describe el beneficio.
    function renderClausulas(calculados) {
        const cont = document.getElementById('sidebarClausulasResumen');
        if (!cont) return;
        if (!clausulasActuales.length) {
            cont.innerHTML = '<p style="color:#94a3b8;font-size:0.8rem;">Este expediente no tiene cláusulas asignadas.</p>';
            return;
        }
        cont.innerHTML = clausulasActuales.map(c => {
            const monto = calculados && calculados[c.id] !== undefined ? calculados[c.id] : null;
            const etiqueta = 'Cláusula ' + escaparHTML(c.numero) + ' · ' + escaparHTML(c.nombre);
            const valorHtml = monto === null
                ? '<span style="color:#94a3b8;font-size:0.72rem;">' + escaparHTML(detalleClausula(c)) + '</span>'
                : (monto > 0
                    ? '<span class="r-value" style="color:#0f766e;">' + fmtEnMoneda(monto, monedaActual) + '</span>'
                    : '<span style="color:#94a3b8;font-size:0.72rem;">Sin monto adicional</span>');
            return '<div class="resumen-item" title="' + escaparHTML(c.descripcion || '') + '">' +
                '<span class="r-label">' + etiqueta + '</span>' + valorHtml + '</div>';
        }).join('');
    }

    // Restaura las cláusulas que ya venían guardadas dentro de los detalles.
    function clausulasDesdeDetalles(detalles) {
        return (detalles || []).filter(d => String(d.codigo || '').indexOf('CLAUSULA_') === 0);
    }

    // Escala de profesionalización por nivel académico (se aplica siempre el mayor, sin sumar).
    const NIVELES_PROFESIONALIZACION = [
        { nivel: 25, ids: [7], nombres: ['doctor', 'doctorado', 'phd'] },
        { nivel: 20, ids: [6], nombres: ['magister', 'magister'] },
        { nivel: 15, ids: [5], nombres: ['especialista', 'especializacion'] },
        { nivel: 13, ids: [3, 4], nombres: ['licenciado', 'licenciatura', 'lic', 'ingeniero', 'ingenieria', 'ing'] },
        { nivel: 11, ids: [2], nombres: ['tsu', 'tecnico superior universitario'] },
    ];

    // Devuelve { nivel: porcentaje, etiqueta: string } para el nivel académico del trabajador,
    // o null si no corresponde a ninguno de la escala (p.ej. Bachiller o sin nivel).
    function profesionalizacionDeTrabajador(t) {
        const id = parseInt(t.nivel_instruccion_id, 10);
        const texto = String(t.nivel_educativo_texto || '').toLowerCase();
        const legacy = parseInt(t.nivel_instruccion, 10);
        let mejor = null;
        for (const e of NIVELES_PROFESIONALIZACION) {
            const porId = e.ids.includes(id);
            const porNombre = e.nombres.some(n => texto.includes(n));
            if (porId || porNombre) {
                if (!mejor || e.nivel > mejor.nivel) mejor = e;
            }
        }
        if (!mejor && legacy > 0) {
            // Respaldo con el código legacy (0-5): 1=TSU, 2=Lic/Ing, 3=Esp, 4=Mag, 5=Doc
            const porLegacy = { 1: 11, 2: 13, 3: 15, 4: 20, 5: 25 };
            if (porLegacy[legacy]) mejor = { nivel: porLegacy[legacy], etiqueta: String(porLegacy[legacy]) + '%' };
        }
        if (!mejor) return null;
        return { nivel: mejor.nivel, etiqueta: texto || (mejor.etiqueta || (mejor.nivel + '%')) };
    }

    const PRIMA_CONFIG = {
        PRIMA_HIJO: { workerField: 'numero_hijos', calc: (v, t) => (t.numero_hijos || 0) * v, label: 'Número de hijos' },
        PRIMA_HIJOS_DISCAPACIDAD: { workerField: 'hijos_discapacidad', calc: (v, t) => (t.hijos_discapacidad || 0) * v, label: 'Hijos con discapacidad' },
        PRIMA_ACTIVIDAD_UNIVERSITARIA: { workerField: 'actividad_universitaria', calc: (v, t) => t.actividad_universitaria ? v : 0, label: 'Actividad universitaria' },
        PRIMA_FAMILIAR: { workerField: 'porcentaje_antiguedad', calc: (v, t) => v * (t.total_anos_servicio || 0), label: 'Antigüedad' },
        PRIMA_PROFESIONALIZACION: {
            workerField: 'prima_profesionalizacion',
            calc: (v, t) => {
                const prof = profesionalizacionDeTrabajador(t);
                // Utiliza la base de datos del trabajador ANTES de la prima (sueldo_base del trabajador).
                const base = parseFloat(t.sueldo_base) || 0;
                return prof ? base * (prof.nivel / 100) : 0;
            },
            label: 'Profesionalización'
        },
        PRIMA_RESPONSABILIDAD: { workerField: 'es_jefe_coordinador', calc: (v, t) => t.es_jefe_coordinador ? v : 0, label: 'Responsabilidad' },
        CESTA_TICKET: { workerField: 'cesta_ticket', calc: (v, t) => v, label: 'Cesta Ticket' },
    };

    function fmtBs(v) { return 'Bs. ' + v.toFixed(2).replace('.', ','); }

    // Conversión de moneda: el cálculo interno SIEMPRE trabaja en Bs.
    // tasaActual = Bs por USD. El valor de referencia de cada prima está en dólares ($).
    function simboloMoneda(moneda) { return moneda === 'USD' ? '$' : 'Bs.'; }
    // Convierte un número (interpretado en `moneda`) a su equivalente en Bolívares.
    function aBs(numero, moneda) {
        const v = parseFloat(numero) || 0;
        if (moneda === 'USD' && tasaActual > 0) return v * tasaActual;
        return v;
    }
    // Presenta un monto que está en Bolívares dentro de la moneda deseada.
    function fmtEnMoneda(montoBs, moneda) {
        const m = parseFloat(montoBs) || 0;
        if (moneda === 'USD' && tasaActual > 0) return '$' + (m / tasaActual).toFixed(2).replace('.', ',');
        return fmtBs(m);
    }

    // R4: totales con fideicomiso (el fideicomiso se captura en Bs y se muestra aparte)
    function fideicomisoBs() {
        return parseFloat(document.getElementById('inputFideicomisoMonto')?.value) || 0;
    }
    function actualizarTotalesFideicomiso(montoPrestacionesBs, moneda) {
        const m = moneda || monedaActual;
        const fidei = fideicomisoBs();
        const prest = parseFloat(montoPrestacionesBs) || 0;
        const elF = document.getElementById('totalFideicomisoDisplay');
        const elG = document.getElementById('totalGeneralDisplay');
        if (elF) elF.textContent = fmtEnMoneda(fidei, m);
        if (elG) elG.textContent = fmtEnMoneda(prest + fidei, m);
    }
    // Muestra u oculta el enlace del comprobante de fideicomiso adjunto.
    function mostrarComprobanteFideicomiso(path, puedeAdjuntar) {
        const box = document.getElementById('fideicomisoDocBox');
        const link = document.getElementById('fideicomisoDocLink');
        const btnAdj = document.getElementById('btnAdjuntarFideicomisoDoc');
        if (!box) return;
        if (path) {
            if (link) link.href = '/storage/' + path;
            box.style.display = 'flex';
            if (btnAdj) btnAdj.style.display = 'none';
        } else {
            box.style.display = 'none';
            if (btnAdj) btnAdj.style.display = puedeAdjuntar ? 'inline-flex' : 'none';
        }
    }

    function bindMonedaToggle(toggleId, cb) {
        const cont = document.getElementById(toggleId);
        if (!cont) return;
        cont.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', function() {
                cont.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                cb(this.dataset.moneda);
            });
        });
    }
    function syncSueldoBaseUI() {
        document.getElementById('lblSueldoBase').textContent = 'SUELDO BASE MENSUAL (' + simboloMoneda(monedaActual) + ')';
        document.getElementById('inputSueldoBase').placeholder = monedaActual === 'USD' ? '0.00 $' : '0.00 Bs.';
    }
    bindMonedaToggle('monedaToggle', (m) => { monedaActual = m; syncSueldoBaseUI(); renderPrimas(primasDisponibles, datosTrabajador); });

    document.querySelectorAll('.pct-toggle button').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.pct-toggle button').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            porcentajeActual = parseFloat(this.dataset.pct);
        });
    });

    // === CARPETAS ALFABÉTICAS Y PAGINACIÓN (10 por letra) ===
    let prestacionesGrupos = [];
    let prestacionLetraAbierta = null;
    const prestacionPaginaPorLetra = {};
    const prestacionesPaginasCache = new Map();
    const prestacionesSolicitudesPorLetra = {};
    let prestacionesSolicitudResumen = 0;

    function tarjetaPrestacion(t) {
        const foto = t.foto_carnet ? '/storage/' + t.foto_carnet : '';
        let montoHtml = '';
        if (t.tiene_prestacion && t.monto) {
            montoHtml = '<span class="pc-monto">Bs. ' + parseFloat(t.monto).toFixed(2) + '</span>';
        }
        let titulo = '';
        const profTarjeta = profesionalizacionDeTrabajador(t);
        if (profTarjeta) {
            const texto = String(t.nivel_educativo_texto || '').trim();
            const nom = texto ? texto : (profTarjeta.etiqueta !== String(profTarjeta.nivel) ? profTarjeta.etiqueta : 'Profesional');
            titulo = '<span class="pc-titulo"><i class="fas fa-graduation-cap"></i> ' + escaparHTML(nom) + ' · ' + profTarjeta.nivel + '%</span>';
        }
        return `
            <div class="prestacion-card" data-id="${t.id}">
                <div class="pc-foto">${foto ? '<img src="'+foto+'" alt="">' : '<div class="pc-avatar"><i class="fas fa-user"></i></div>'}</div>
                <div class="pc-info">
                    <strong>${escaparHTML(t.nombres)} ${escaparHTML(t.apellidos)}</strong>
                    <span class="pc-cedula">${escaparHTML(t.cedula)} · ${escaparHTML(t.cargo)}</span>
                    ${titulo ? `<div class="pc-row">${titulo}</div>` : ''}
                    <div class="pc-row">
                        <span class="pc-anios">${escaparHTML(t.total_anos_servicio) || 0} años</span>
                        <span class="pc-badge">${escaparHTML(t.tipo_jubilacion)}</span>
                        ${montoHtml}
                    </div>
                </div>
            </div>`;
    }


    // Pinta las tarjetas de una letra abierta.
    function pintarPrestaciones(items, grid) {
        if (!items || items.length === 0) {
            grid.innerHTML = '<p class="empty-state">No hay trabajadores en esta letra.</p>';
            return;
        }
        grid.innerHTML = items.map(t => tarjetaPrestacion(t)).join('');
        grid.querySelectorAll('.prestacion-card').forEach(card => {
            card.addEventListener('click', function() { abrirDetalle(parseInt(this.dataset.id, 10)); });
        });
    }

    // Pinta las carpetas por letra; al abrir una, su cabecera y su cuadrícula.
    function renderGruposPrestaciones() {
        const grid = document.getElementById('prestacionesGrid');
        if (!grid) return;

        const gruposVisibles = prestacionLetraAbierta
            ? prestacionesGrupos.filter(grupo => grupo.letra === prestacionLetraAbierta)
            : prestacionesGrupos;

        grid.innerHTML = gruposVisibles.map(grupo => {
            const abierta = grupo.letra === prestacionLetraAbierta;
            const letra = escaparHTML(grupo.letra);
            const cantidad = Number(grupo.total) || 0;
            const etiqueta = cantidad === 1 ? 'TRABAJADOR' : 'TRABAJADORES';
            if (abierta) {
                return `
                    <section class="grupo-alfabetico grupo-alfabetico-detalle is-open" data-letra="${letra}">
                        <div class="grupo-alfabetico-detail-header">
                            <div class="grupo-alfabetico-detail-top">
                                <button type="button" class="btn-volver btn-volver-prestaciones-letras">
                                    <i class="fas fa-arrow-left"></i> Volver a letras
                                </button>
                                <span class="grupo-alfabetico-badge">${cantidad} ${etiqueta}</span>
                            </div>
                            <h2>Prestaciones ${letra}</h2>
                            <p>${cantidad} trabajador${cantidad === 1 ? '' : 'es'} con expediente completo</p>
                        </div>
                        <div class="grupo-alfabetico-panel">
                            <div class="prestaciones-grid-cards grupo-alfabetico-grid"><p class="empty-state">Cargando...</p></div>
                        </div>
                    </section>
                `;
            }

            return `
                <section class="grupo-alfabetico" data-letra="${letra}">
                    <button type="button" class="grupo-alfabetico-header" aria-expanded="false" data-letra="${letra}">
                        <span class="grupo-alfabetico-card-top">
                            <span class="grupo-alfabetico-icon"><i class="fas fa-wallet"></i></span>
                            <span class="grupo-alfabetico-badge">${cantidad} ${etiqueta}</span>
                        </span>
                        <span class="grupo-alfabetico-letra">${letra}</span>
                        <span class="grupo-alfabetico-count">${cantidad} trabajador${cantidad === 1 ? '' : 'es'}</span>
                    </button>
                </section>
            `;
        }).join('');

        grid.querySelectorAll('.grupo-alfabetico-header').forEach(boton => {
            boton.addEventListener('click', function() {
                prestacionLetraAbierta = this.dataset.letra;
                renderGruposPrestaciones();
                cargarPrestacionesLetra(prestacionLetraAbierta, prestacionPaginaPorLetra[prestacionLetraAbierta] || 1);
            });
        });
        grid.querySelector('.btn-volver-prestaciones-letras')?.addEventListener('click', function() {
            prestacionLetraAbierta = null;
            renderGruposPrestaciones();
            renderPaginacionPrestaciones(0, 0);
        });
    }

    function renderPaginacionPrestaciones(paginaActual, ultimaPagina) {
        const cont = document.getElementById('prestacionesPagination');
        if (!cont) return;
        if (!prestacionLetraAbierta || !ultimaPagina || ultimaPagina <= 1) {
            cont.innerHTML = '';
            return;
        }

        const paginas = new Set([1, ultimaPagina]);
        for (let pagina = Math.max(2, paginaActual - 2); pagina <= Math.min(ultimaPagina - 1, paginaActual + 2); pagina++) {
            paginas.add(pagina);
        }

        let html = '<button type="button" class="pag-btn" data-p="' + (paginaActual - 1) + '"' + (paginaActual <= 1 ? ' disabled' : '') + '>&lsaquo;</button>';
        let paginaAnterior = 0;
        Array.from(paginas).sort((a, b) => a - b).forEach(pagina => {
            if (paginaAnterior && pagina - paginaAnterior > 1) html += '<span class="pag-info">&hellip;</span>';
            html += '<button type="button" class="pag-btn' + (pagina === paginaActual ? ' active' : '') + '" data-p="' + pagina + '">' + pagina + '</button>';
            paginaAnterior = pagina;
        });
        html += '<button type="button" class="pag-btn" data-p="' + (paginaActual + 1) + '"' + (paginaActual >= ultimaPagina ? ' disabled' : '') + '>&rsaquo;</button>';
        html += '<span class="pag-info">' + paginaActual + ' de ' + ultimaPagina + '</span>';
        cont.innerHTML = html;
        cont.querySelectorAll('.pag-btn[data-p]').forEach(boton => {
            boton.addEventListener('click', function() {
                const pagina = parseInt(this.dataset.p, 10);
                if (pagina >= 1 && pagina <= ultimaPagina && pagina !== paginaActual) {
                    cargarPrestacionesLetra(prestacionLetraAbierta, pagina);
                }
            });
        });
    }

    async function cargarPrestacionesLetra(letra, pagina) {
        const grid = document.getElementById('prestacionesGrid');
        const panel = grid?.querySelector('.grupo-alfabetico-detalle .grupo-alfabetico-grid');
        if (!panel || prestacionLetraAbierta !== letra) return;

        const solicitud = (prestacionesSolicitudesPorLetra[letra] || 0) + 1;
        prestacionesSolicitudesPorLetra[letra] = solicitud;
        panel.innerHTML = '<p class="empty-state">Cargando...</p>';
        const url = '/prestaciones?v=2&letra=' + encodeURIComponent(letra) + '&page=' + pagina + '&per_page=10';
        try {
            let raw = prestacionesPaginasCache.get(url);
            if (!raw) {
                const result = await cachedFetch(url, { ttl: 60000 });
                raw = result.data;
                prestacionesPaginasCache.set(url, raw);
            }
            if (prestacionesSolicitudesPorLetra[letra] !== solicitud || prestacionLetraAbierta !== letra) return;

            const items = Array.isArray(raw) ? raw : (raw.data || []);
            const paginaActual = Number(raw.current_page) || pagina;
            const ultimaPagina = Number(raw.last_page) || 1;
            prestacionPaginaPorLetra[letra] = paginaActual;
            pintarPrestaciones(items, panel);
            renderPaginacionPrestaciones(paginaActual, ultimaPagina);
        } catch (err) {
            console.error('Error prestaciones:', err);
            if (prestacionesSolicitudesPorLetra[letra] === solicitud && prestacionLetraAbierta === letra) {
                panel.innerHTML = '<p class="empty-state">Error al cargar datos.</p>';
            }
        }
    }

    // Carga (o recarga) las carpetas por letra.
    async function cargarPrestaciones() {
        const grid = document.getElementById('prestacionesGrid');
        if (!grid) return;
        const solicitud = ++prestacionesSolicitudResumen;
        grid.innerHTML = '<p class="empty-state">Cargando...</p>';
        renderPaginacionPrestaciones(0, 0);

        try {
            const respuesta = await fetch('/prestaciones/letras', { headers: { 'Accept': 'application/json' } });
            if (!respuesta.ok) throw new Error('No se pudo cargar la organización de prestaciones.');
            const raw = await respuesta.json();
            if (solicitud !== prestacionesSolicitudResumen) return;

            prestacionesGrupos = Array.isArray(raw.letras) ? raw.letras : [];
            if (!prestacionesGrupos.length) {
                prestacionLetraAbierta = null;
                grid.innerHTML = '<p class="empty-state">No hay trabajadores disponibles. Deben tener solicitud aprobada y expediente.</p>';
                return;
            }

            const letrasDisponibles = prestacionesGrupos.map(grupo => grupo.letra);
            if (!prestacionLetraAbierta || !letrasDisponibles.includes(prestacionLetraAbierta)) {
                prestacionLetraAbierta = null;
            }
            renderGruposPrestaciones();
            if (prestacionLetraAbierta) {
                cargarPrestacionesLetra(prestacionLetraAbierta, prestacionPaginaPorLetra[prestacionLetraAbierta] || 1);
            }
        } catch (err) {
            console.error('Error prestaciones:', err);
            if (solicitud === prestacionesSolicitudResumen) {
                grid.innerHTML = '<p class="empty-state">Error al cargar datos.</p>';
            }
        }
    }

    window.abrirDetalle = async function(id) {
        trabajadorActualId = id;
        ultimoCalculo = null;
        try {
            mostrarCargando('Cargando datos del trabajador...');
            const resp = await fetch('/prestaciones/' + id);
            const data = await resp.json();
            datosTrabajador = data.trabajador;
            primasDisponibles = data.primas || [];
            clausulasActuales = data.clausulas || [];
            renderClausulas(null);

            document.getElementById('prestaciones-lista').classList.add('hidden');
            document.getElementById('prestacion-detalle').classList.remove('hidden');

            const nombreCompleto = datosTrabajador.nombres + ' ' + datosTrabajador.apellidos;
            const foto = data.expediente.foto_carnet ? '/storage/' + data.expediente.foto_carnet : '';

            document.getElementById('detalleNombreMini').textContent = nombreCompleto;
            document.getElementById('detalleInfoMini').textContent = datosTrabajador.cedula + ' · ' + datosTrabajador.cargo;
            document.getElementById('detalleFotoMini').innerHTML = foto
                ? '<img src="'+foto+'" style="width:44px;height:44px;border-radius:50%;object-fit:cover;">'
                : '<i class="fas fa-user" style="font-size:18px;"></i>';

            document.getElementById('badgeEdad').textContent = (datosTrabajador.edad || '—') + ' AÑOS';
            document.getElementById('badgeTipo').textContent = data.solicitud.tipo_jubilacion || '—';

            // Mostrar título académico y su porcentaje de profesionalización
            const badgeTitulo = document.getElementById('badgeTitulo');
            const prof = profesionalizacionDeTrabajador(datosTrabajador);
            const tituloTexto = String(datosTrabajador.nivel_educativo_texto || '').trim();
            if (prof) {
                const tituloFinal = tituloTexto ? tituloTexto : (prof.etiqueta !== String(prof.nivel) ? prof.etiqueta : 'Profesional');
                document.getElementById('badgeTituloTexto').textContent = tituloFinal + ' · ' + prof.nivel + '%';
                badgeTitulo.style.display = 'inline-flex';
            } else {
                badgeTitulo.style.display = 'none';
            }

            // Mostrar botón comprobante solo si ya hay prestación guardada
            const btnComp = document.getElementById('btnGenerarComprobante');
            if (data.prestacion) {
                btnComp.classList.remove('hidden');
                const savedDetalles = data.prestacion.detalles || [];
                const savedPct = parseFloat(data.prestacion.porcentaje_jubilacion) || 100;
                btnComp.onclick = () => generarComprobante(
                    parseFloat(data.prestacion.sueldo_integral || 0) - parseFloat(data.prestacion.total_primas || 0),
                    parseFloat(data.prestacion.total_primas || 0),
                    parseFloat(data.prestacion.sueldo_integral || 0),
                    parseFloat(data.prestacion.monto || 0),
                    savedDetalles,
                    savedPct
                );
                // Restaurar resumen, total y porcentaje desde datos guardados
                if (savedDetalles.length) {
                    const esClausula = d => String(d.codigo || '').indexOf('CLAUSULA_') === 0;
                    const resumenHtml = savedDetalles
                        .filter(d => d.monto > 0 && !esClausula(d))
                        .map(d => '<div class="resumen-item"><span class="r-label">' + escaparHTML(d.nombre) + '</span><span class="r-value">' + fmtBs(d.monto) + '</span></div>')
                        .join('');
                    document.getElementById('sidebarPrimasResumen').innerHTML = resumenHtml || '<p style="color:#94a3b8;font-size:0.8rem;">Sin primas activas</p>';
                    const montosGuardados = {};
                    savedDetalles.filter(esClausula).forEach(d => {
                        montosGuardados[parseInt(String(d.codigo).replace('CLAUSULA_', ''), 10)] = parseFloat(d.monto) || 0;
                    });
                    renderClausulas(montosGuardados);
                }
                const savedMonto = parseFloat(data.prestacion.monto) || 0;
                document.getElementById('totalPrestacionDisplay').textContent = fmtBs(savedMonto);
                // R4: restaurar fideicomiso guardado
                const inputFidei = document.getElementById('inputFideicomisoMonto');
                const inputFideiObs = document.getElementById('inputFideicomisoObs');
                if (inputFidei) inputFidei.value = (data.prestacion.fideicomiso_monto !== null && data.prestacion.fideicomiso_monto !== undefined) ? parseFloat(data.prestacion.fideicomiso_monto).toFixed(2) : '';
                if (inputFideiObs) inputFideiObs.value = data.prestacion.fideicomiso_observaciones || '';
                actualizarTotalesFideicomiso(savedMonto, monedaActual);
                mostrarComprobanteFideicomiso(data.prestacion.fideicomiso_documento, true);
                porcentajeActual = savedPct;
                document.querySelectorAll('.pct-toggle button').forEach(b => {
                    b.classList.toggle('active', parseFloat(b.dataset.pct) === savedPct);
                });
                const savedSueldoBase = parseFloat(data.prestacion.sueldo_integral || 0) - parseFloat(data.prestacion.total_primas || 0);
                document.getElementById('inputSueldoBase').value = savedSueldoBase.toFixed(2);
                ultimoCalculo = {
                    sueldoBase: savedSueldoBase,
                    totalPrimas: parseFloat(data.prestacion.total_primas || 0),
                    sueldoIntegral: parseFloat(data.prestacion.sueldo_integral || 0),
                    totalPrestaciones: savedMonto,
                    detalles: savedDetalles
                };
            } else {
                btnComp.classList.add('hidden');
                document.getElementById('totalPrestacionDisplay').textContent = 'Bs. 0,00';
                renderClausulas(null);
                document.getElementById('sidebarPrimasResumen').innerHTML = '<p style="color:#94a3b8;font-size:0.8rem;">Presione "Calcular Prestaciones" para ver el resumen.</p>';
                document.getElementById('inputSueldoBase').value = (datosTrabajador.sueldo_base || 0).toFixed(2);
                const inputFideiV = document.getElementById('inputFideicomisoMonto');
                const inputFideiObsV = document.getElementById('inputFideicomisoObs');
                if (inputFideiV) inputFideiV.value = '';
                if (inputFideiObsV) inputFideiObsV.value = '';
                actualizarTotalesFideicomiso(0, monedaActual);
                mostrarComprobanteFideicomiso(null, false);
            }

            renderPrimas(primasDisponibles, datosTrabajador);

            const tasaDetalle = document.getElementById('tasaDetallePrestacion');
            if (data.prestacion && data.prestacion.tasa_utilizada) {
                tasaActual = parseFloat(data.prestacion.tasa_utilizada) || 0;
                const tFecha = data.prestacion.fecha_tasa_utilizada ? new Date(data.prestacion.fecha_tasa_utilizada).toLocaleDateString('es-VE') : '—';
                tasaDetalle.innerHTML = '<strong style="color:#0f172a;">' + parseFloat(data.prestacion.tasa_utilizada).toLocaleString('es-VE', {minimumFractionDigits:2,maximumFractionDigits:2}) + ' ' + (data.prestacion.moneda_tasa || 'VES/USD') + '</strong><br><span style="font-size:0.68rem;">Congelada: ' + tFecha + ' · ' + (data.prestacion.fuente_tasa || '—') + '</span>';
            } else if (data.tasa_actual) {
                tasaActual = parseFloat(data.tasa_actual.tasa) || 0;
                tasaDetalle.innerHTML = '<strong style="color:#0f172a;">' + parseFloat(data.tasa_actual.tasa).toLocaleString('es-VE', {minimumFractionDigits:2,maximumFractionDigits:2}) + ' VES/USD</strong><br><span style="font-size:0.68rem;">' + data.tasa_actual.fecha + ' · ' + (data.tasa_actual.fuente || '—') + '</span>';
            } else {
                tasaActual = 0;
                tasaDetalle.innerHTML = '<span style="color:#94a3b8;">Sin tasa registrada</span>';
            }
        } catch (err) {
            console.error('Error detalle:', err);
            mostrarToast('Error al cargar datos del trabajador.', 'error');
        } finally {
            ocultarCargando();
        }
    };

    function mesesActividadUniversitaria(f1, f2) {
        if (!f1 || !f2) return null;
        const d = new Date(f1 + 'T00:00:00');
        const h = new Date(f2 + 'T00:00:00');
        if (isNaN(d) || isNaN(h) || h < d) return null;
        let meses = (h.getFullYear() - d.getFullYear()) * 12 + (h.getMonth() - d.getMonth());
        if (h.getDate() < d.getDate()) meses = Math.max(0, meses - 1);
        return meses;
    }

    function renderPrimas(primas, t) {
        const container = document.getElementById('primasContainer');
        container.innerHTML = '';
        if (!t) return;
        if (tasaActual <= 0) {
            container.innerHTML = '<p style="color:#d97706;font-size:0.78rem;">Registre la tasa del dólar para poder trabajar en dólares.</p>';
        }
        primas.forEach(p => {
            const config = PRIMA_CONFIG[p.codigo] || {};
            const row = document.createElement('div');
            row.className = 'prima-row';
            const esProf = p.codigo === 'PRIMA_PROFESIONALIZACION';
            let inputHtml = '';
            if (config.calc) {
                const autoVal = config.calc(p.valor, t);
                const isNa = autoVal === 0 && !t[config.workerField];
                const autoBs = esProf ? autoVal : (autoVal * (tasaActual > 0 ? tasaActual : 0));
                const autoDisplay = monedaActual === 'USD'
                    ? '$' + (esProf ? (tasaActual > 0 ? autoVal / tasaActual : 0) : autoVal).toFixed(2)
                    : 'Bs. ' + autoBs.toFixed(2);
                inputHtml = '<div class="prima-auto' + (isNa ? ' na' : '') + '">' + autoDisplay + '</div>';
            } else {
                inputHtml = '<div class="prima-auto na">—</div>';
            }
            let valorUnitarioDisplay;
            if (esProf) {
                valorUnitarioDisplay = parseFloat(p.valor).toFixed(2) + '%';
            } else if (monedaActual === 'USD') {
                valorUnitarioDisplay = '$' + parseFloat(p.valor).toFixed(2);
            } else {
                valorUnitarioDisplay = 'Bs. ' + (parseFloat(p.valor) * (tasaActual > 0 ? tasaActual : 0)).toFixed(2);
            }
            row.innerHTML = `
                <div class="prima-info">
                    <div class="prima-nombre">${escaparHTML(p.nombre)}</div>
                    <div class="prima-codigo">${escaparHTML(p.codigo)}</div>
                </div>
                <div class="prima-valor">${valorUnitarioDisplay}<span>valor unitario</span></div>
                <div>${inputHtml}</div>`;
            container.appendChild(row);
            // Fila informativa: duración / vigencia de la prima de actividad universitaria
            if (p.codigo === 'PRIMA_ACTIVIDAD_UNIVERSITARIA' && t.actividad_universitaria_info) {
                const info = t.actividad_universitaria_info || {};
                if (info.desde || info.hasta) {
                    const infoRow = document.createElement('div');
                    infoRow.className = 'prima-auto na';
                    infoRow.style.gridColumn = '1 / -1';
                    infoRow.style.marginTop = '-2px';
                    infoRow.style.fontSize = '0.7rem';
                    infoRow.style.textAlign = 'left';
                    let txt = '<i class="fas fa-stopwatch"></i> Vigencia de la prima: ';
                    const trozos = [];
                    if (info.desde) trozos.push('desde ' + info.desde);
                    if (info.hasta) trozos.push('hasta ' + info.hasta);
                    txt += trozos.join(' ');
                    const meses = mesesActividadUniversitaria(info.desde, info.hasta);
                    if (meses !== null) txt += ' · ' + meses + ' mes(es)';
                    infoRow.innerHTML = txt;
                    container.appendChild(infoRow);
                }
            }
        });
    }

    document.getElementById('btnCalcularPrestacion').addEventListener('click', function() {
        if (!datosTrabajador || !primasDisponibles.length) return;

        const sueldoBaseInput = parseFloat(document.getElementById('inputSueldoBase').value) || 0;
        const sueldoBase = aBs(sueldoBaseInput, monedaActual); // siempre en Bs
        const monedaResumen = monedaActual; // moneda de presentación del resultado
        let totalPrimas = 0;
        const detalles = [];
        const resumenHtml = [];

        primasDisponibles.forEach(p => {
            const config = PRIMA_CONFIG[p.codigo] || {};
            let monto = 0;
            if (config.calc) {
                // config.calc devuelve el monto en la moneda de referencia de cada prima.
                // Las primas estándar tienen su valor en dólares ($) -> se convierten a Bs.
                // PRIMA_PROFESIONALIZACION se calcula sobre el sueldo_base del trabajador (en Bs), ya está en Bs.
                monto = config.calc(p.valor, datosTrabajador);
                if (p.codigo !== 'PRIMA_PROFESIONALIZACION' && tasaActual > 0) monto = monto * tasaActual;
            }
            const montoDisplay = fmtEnMoneda(monto, monedaResumen);
            totalPrimas += monto;
            detalles.push({ codigo: p.codigo, nombre: p.nombre, valor_unitario: p.valor, monto });
            if (p.codigo === 'PRIMA_PROFESIONALIZACION') {
                const prof = profesionalizacionDeTrabajador(datosTrabajador);
                const base = parseFloat(datosTrabajador.sueldo_base) || 0;
                if (monto > 0 && prof) {
                    resumenHtml.push('<div class="resumen-item"><span class="r-label">' + escaparHTML(p.nombre) + ' · ' + escaparHTML(prof.etiqueta) + ' (' + prof.nivel + '%) sobre base ' + fmtEnMoneda(base, monedaResumen) + '</span><span class="r-value">' + montoDisplay + '</span></div>');
                } else {
                    resumenHtml.push('<div class="resumen-item"><span class="r-label">' + escaparHTML(p.nombre) + '</span><span class="r-value" style="color:#d97706;">+ ' + montoDisplay + ' · nivel no aplica (0%)</span></div>');
                }
            } else if (monto > 0) {
                resumenHtml.push('<div class="resumen-item"><span class="r-label">' + escaparHTML(p.nombre) + '</span><span class="r-value">' + montoDisplay + '</span></div>');
            }
        });

        const totalAnios = datosTrabajador.total_anos_servicio || 0;
        const sueldoIntegral = sueldoBase + totalPrimas;
        let totalPrestaciones = sueldoIntegral * (porcentajeActual / 100) * totalAnios;

        // Cláusulas de la convención colectiva asignadas en el expediente:
        // su monto se calcula con el sueldo base/integral y los años de servicio,
        // y se suma al monto de prestaciones.
        const montosClausulas = {};
        let totalClausulas = 0;
        clausulasActuales.forEach(c => {
            const monto = montoClausula(c, sueldoBase, sueldoIntegral, totalAnios);
            montosClausulas[c.id] = monto;
            totalClausulas += monto;
            detalles.push({
                codigo: 'CLAUSULA_' + c.id,
                nombre: 'Cláusula ' + c.numero + ' · ' + c.nombre,
                valor_unitario: c.valor,
                monto: monto,
                tipo_calculo: c.tipo_calculo
            });
        });
        totalPrestaciones += totalClausulas;

        ultimoCalculo = { sueldoBase, totalPrimas, sueldoIntegral, totalPrestaciones, detalles };

        // Actualizar resumen en columna derecha
        document.getElementById('sidebarPrimasResumen').innerHTML = resumenHtml.length
            ? resumenHtml.join('')
            : '<p style="color:#94a3b8;font-size:0.8rem;">Sin primas activas</p>';
        renderClausulas(montosClausulas);
        document.getElementById('totalPrestacionDisplay').textContent = fmtEnMoneda(totalPrestaciones, monedaResumen);
        actualizarTotalesFideicomiso(totalPrestaciones, monedaResumen);

        // Mostrar modal con resultado
        document.getElementById('mrSueldoBase').textContent = fmtEnMoneda(sueldoBase, monedaResumen);
        document.getElementById('mrTotalPrimas').textContent = fmtEnMoneda(totalPrimas, monedaResumen);
        document.getElementById('mrSueldoIntegral').textContent = fmtEnMoneda(sueldoIntegral, monedaResumen);
        document.getElementById('mrPorcentaje').textContent = porcentajeActual + '%';
        document.getElementById('mrAnios').textContent = totalAnios;
        document.getElementById('mrClausulas').textContent = fmtEnMoneda(totalClausulas, monedaResumen);
        document.getElementById('mrTotal').textContent = fmtEnMoneda(totalPrestaciones, monedaResumen);
        document.getElementById('mrFideicomiso').textContent = fmtEnMoneda(fideicomisoBs(), monedaResumen);
        document.getElementById('mrTotalGeneral').textContent = fmtEnMoneda(totalPrestaciones + fideicomisoBs(), monedaResumen);
        document.getElementById('modalResultado').classList.add('active');
    });

    function guardarCalculo() {
        if (!ultimoCalculo || !datosTrabajador) {
            mostrarToast('Debe calcular primero las prestaciones.', 'warning');
            return;
        }
        abrirModalAnioPrestacion();
    }

    function abrirModalAnioPrestacion() {
        const sel = document.getElementById('anioPrestacionSelect');
        if (sel) {
            const anioActual = new Date().getFullYear();
            let html = '<option value="">Seleccione el año</option>';
            for (let a = anioActual; a >= 1900; a--) {
                html += '<option value="' + a + '">' + a + '</option>';
            }
            sel.innerHTML = html;
            sel.value = String(anioActual);
        }
        document.getElementById('modalAnioPrestacion').style.display = 'flex';
    }

    function cerrarModalAnioPrestacion() {
        document.getElementById('modalAnioPrestacion').style.display = 'none';
    }

    document.getElementById('btnConfirmarAnioPrestacion').addEventListener('click', function() {
        const sel = document.getElementById('anioPrestacionSelect');
        const selMes = document.getElementById('mesPrestacionSelect');
        if (!sel || !sel.value) {
            mostrarToast('Debe seleccionar el año de la prestación.', 'warning');
            return;
        }
        if (!selMes || !selMes.value) {
            mostrarToast('Debe seleccionar el mes de la prestación.', 'warning');
            return;
        }
        const anio = sel.value;
        const mes = selMes.value;
        cerrarModalAnioPrestacion();
        guardarPrestacion(ultimoCalculo.sueldoBase, ultimoCalculo.totalPrimas, ultimoCalculo.sueldoIntegral, ultimoCalculo.totalPrestaciones, ultimoCalculo.detalles, anio, mes);
    });
    document.getElementById('btnCancelarAnioPrestacion').addEventListener('click', cerrarModalAnioPrestacion);
    document.getElementById('modalAnioPrestacion').addEventListener('click', function(e) {
        if (e.target === this) cerrarModalAnioPrestacion();
    });

    document.getElementById('btnGuardarPrestacion').addEventListener('click', guardarCalculo);
    document.getElementById('btnGuardarDesdeModal').addEventListener('click', function() {
        document.getElementById('modalResultado').classList.remove('active');
        guardarCalculo();
    });
    document.getElementById('btnCerrarModal').addEventListener('click', () => document.getElementById('modalResultado').classList.remove('active'));
    document.getElementById('modalResultado').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });

    // R4: adjuntar/reemplazar el comprobante del fideicomiso
    const inputFideicomisoDoc = document.getElementById('inputFideicomisoDoc');
    const btnAdjuntarFidei = document.getElementById('btnAdjuntarFideicomisoDoc');
    const btnReemplazarFidei = document.getElementById('btnSubirFideicomisoDoc');
    if (btnAdjuntarFidei) btnAdjuntarFidei.addEventListener('click', () => inputFideicomisoDoc?.click());
    if (btnReemplazarFidei) btnReemplazarFidei.addEventListener('click', () => inputFideicomisoDoc?.click());
    if (inputFideicomisoDoc) inputFideicomisoDoc.addEventListener('change', async function() {
        const file = this.files[0];
        if (!file) return;
        if (!trabajadorActualId) { mostrarToast('Abra primero el detalle del trabajador.', 'warning'); this.value = ''; return; }
        try {
            mostrarCargando('Adjuntando comprobante de fideicomiso...');
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const fd = new FormData();
            fd.append('archivo', file);
            const resp = await fetch('/prestaciones/' + trabajadorActualId + '/fideicomiso-documento', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: fd,
            });
            const data = await resp.json().catch(() => ({}));
            if (!resp.ok || (data.estado && data.estado !== 'success')) throw data;
            mostrarToast(data.mensaje || 'Comprobante adjuntado.', 'success');
            mostrarComprobanteFideicomiso(data.archivo, true);
        } catch (err) {
            mostrarToast(err.mensaje || 'Error al adjuntar el comprobante.', 'error');
        } finally {
            ocultarCargando();
            this.value = '';
        }
    });

    async function guardarPrestacion(sueldoBase, totalPrimas, sueldoIntegral, totalPrestaciones, detalles, anio, mes) {
        try {
            mostrarCargando('Guardando cálculo...');
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const resp = await fetch('/prestaciones', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({
                    trabajador_id: trabajadorActualId,
                    anio: anio,
                    mes: mes ? parseInt(mes, 10) : null,
                    sueldo_base: sueldoBase,
                    monto: totalPrestaciones,
                    anios_servicio: datosTrabajador.total_anos_servicio || 0,
                    sueldo_integral: sueldoIntegral,
                    total_primas: totalPrimas,
                    porcentaje_jubilacion: porcentajeActual,
                    detalles: detalles,
                    fideicomiso_monto: fideicomisoBs() || null,
                    fideicomiso_observaciones: (document.getElementById('inputFideicomisoObs')?.value || '').trim() || null,
                })
            });
            const data = await resp.json();
            if (!resp.ok) throw data;
            mostrarToast(data.mensaje || 'Prestaciones guardadas.', 'success');
            if (window.limpiarCacheSigejub) window.limpiarCacheSigejub();
            abrirDetalle(trabajadorActualId);
        } catch (err) {
            mostrarToast(err.mensaje || 'Error al guardar.', 'error');
        } finally {
            ocultarCargando();
        }
    }

    async function generarComprobante(sueldoBase, totalPrimas, sueldoIntegral, totalPrestaciones, detalles, pct) {
        if (!datosTrabajador || !trabajadorActualId) return;
        try {
            mostrarCargando('Generando comprobante...');
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const resp = await fetch('/prestaciones/' + trabajadorActualId + '/comprobante', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({
                    sueldo_base: sueldoBase,
                    total_primas: totalPrimas,
                    sueldo_integral: sueldoIntegral,
                    total_prestaciones: totalPrestaciones,
                    porcentaje_jubilacion: pct ?? porcentajeActual,
                    detalles: detalles,
                })
            });
            if (!resp.ok) throw await resp.json();
            const blob = await resp.blob();
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Comprobante_Prestaciones_' + datosTrabajador.cedula.replace(/[^a-zA-Z0-9]/g, '') + '.pdf';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            mostrarToast('Comprobante generado correctamente.', 'success');
        } catch (err) {
            mostrarToast(err.mensaje || 'Error al generar comprobante.', 'error');
        } finally {
            ocultarCargando();
        }
    }

    document.getElementById('btnVolverPrestaciones').addEventListener('click', function() {
        document.getElementById('prestacion-detalle').classList.add('hidden');
        document.getElementById('prestaciones-lista').classList.remove('hidden');
        trabajadorActualId = null;
        datosTrabajador = null;
        ultimoCalculo = null;
        cargarPrestaciones();
    });

    const observer = new MutationObserver((mutations) => {
        mutations.forEach(m => {
            if (m.target.id === 'prestaciones' && m.target.classList.contains('active')) {
                if (document.getElementById('prestacion-detalle').classList.contains('hidden')) {
                    cargarPrestaciones();
                }
            }
        });
    });
    const seccion = document.getElementById('prestaciones');
    if (seccion) observer.observe(seccion, { attributes: true, attributeFilter: ['class'] });

    // La lista solo se carga si la sección está visible (o al activarla vía
    // el observer de arriba); así entrar al dashboard no dispara este viaje.
    if (document.getElementById('prestaciones')?.classList.contains('active')) cargarPrestaciones(1);
})();
</script>
