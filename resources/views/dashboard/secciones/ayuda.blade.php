{{-- ayuda.blade.php - Sección de Ayuda: paneles desplegables de Desarrolladores (soporte) y Guías (manual y videos) --}}
<style>
.ayuda-grid{display:grid;grid-template-columns:1fr;gap:16px;margin-top:20px;}
.ayuda-panel{background:white;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,0.06);overflow:hidden;border:1px solid #e2e8f0;}
.ayuda-panel-header{width:100%;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:20px 24px;background:linear-gradient(135deg,#1a365d,#1e3a8a);color:white;border:none;cursor:pointer;text-align:left;font-size:1rem;font-weight:700;transition:background 0.2s;}
.ayuda-panel-header:hover{background:linear-gradient(135deg,#1e3a8a,#2563eb);}
.ayuda-panel-header .ayuda-titulo{display:flex;align-items:center;gap:10px;}
.ayuda-panel-header .ayuda-flecha{transition:transform 0.25s;font-size:0.9rem;}
.ayuda-panel-header.open .ayuda-flecha{transform:rotate(180deg);}
.ayuda-panel-body{max-height:0;overflow:hidden;transition:max-height 0.35s ease;background:#f8fafc;}
.ayuda-panel-body.open{max-height:2200px;}
.ayuda-panel-contenido{padding:24px;}
.ayuda-dev-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;}
.ayuda-dev-card{background:white;border:1px solid #e2e8f0;border-radius:14px;padding:20px;display:flex;flex-direction:column;align-items:center;gap:6px;text-align:center;transition:box-shadow 0.2s,transform 0.2s;}
.ayuda-dev-card:hover{box-shadow:0 8px 24px rgba(0,0,0,0.10);transform:translateY(-2px);}
.ayuda-dev-foto{width:88px;height:88px;border-radius:50%;overflow:hidden;position:relative;background:#dbeafe;display:flex;align-items:center;justify-content:center;margin-bottom:8px;border:3px solid #e2e8f0;}
.ayuda-dev-foto .ayuda-dev-iniciales{font-size:1.6rem;font-weight:800;color:#1e3a8a;position:absolute;inset:0;display:flex;align-items:center;justify-content:center;}
.ayuda-dev-foto img{position:relative;width:100%;height:100%;object-fit:cover;z-index:1;}
.ayuda-dev-card h4{margin:0;font-size:1rem;color:#0f172a;}
.ayuda-dev-rol{font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;margin-bottom:6px;}
.ayuda-dev-dato{width:100%;display:flex;justify-content:space-between;gap:10px;font-size:0.8rem;border-top:1px solid #f1f5f9;padding-top:6px;color:#475569;}
.ayuda-dev-dato strong{color:#0f172a;font-weight:600;text-align:left;}
.ayuda-dev-dato span{text-align:right;word-break:break-word;}
.ayuda-manual-card{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;background:white;border:1px solid #e2e8f0;border-radius:14px;padding:20px;margin-bottom:20px;}
.ayuda-manual-info{display:flex;align-items:center;gap:14px;}
.ayuda-manual-info .ayuda-manual-icon{width:52px;height:52px;border-radius:12px;background:#fef3c7;color:#92400e;display:flex;align-items:center;justify-content:center;font-size:1.4rem;}
.ayuda-manual-info h3{margin:0;font-size:0.95rem;color:#0f172a;}
.ayuda-manual-info p{margin:4px 0 0;font-size:0.78rem;color:#64748b;}
.ayuda-videos-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;}
.ayuda-video-placeholder{border:2px dashed #cbd5e1;border-radius:14px;min-height:170px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;color:#94a3b8;text-align:center;padding:20px;background:white;}
.ayuda-video-placeholder i{font-size:1.8rem;}
.ayuda-video-placeholder p{margin:0;font-size:0.8rem;font-weight:600;color:#64748b;}
.ayuda-video iframe{width:100%;aspect-ratio:16/9;border:none;border-radius:12px;display:block;}
.ayuda-seccion-titulo{display:flex;align-items:center;gap:8px;font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:#0f172a;margin:0 0 12px;}
body.dark-mode .ayuda-panel{background:#1e293b;border-color:#334155;}
body.dark-mode .ayuda-panel-body{background:#0f172a;}
body.dark-mode .ayuda-dev-card{background:#1e293b;border-color:#334155;}
body.dark-mode .ayuda-dev-card h4,body.dark-mode .ayuda-dev-dato strong{color:#f1f5f9;}
body.dark-mode .ayuda-dev-dato{color:#94a3b8;border-top-color:#334155;}
body.dark-mode .ayuda-manual-card{background:#1e293b;border-color:#334155;}
body.dark-mode .ayuda-manual-info h3{color:#f1f5f9;}
body.dark-mode .ayuda-manual-info p{color:#94a3b8;}
body.dark-mode .ayuda-seccion-titulo{color:#f1f5f9;}
body.dark-mode .ayuda-video-placeholder{background:#1e293b;border-color:#475569;}
</style>

<header class="section-header">
    <div class="header-info">
        <h1>Ayuda</h1>
        <p>Información de soporte técnico y material de guía para el uso del sistema.</p>
    </div>
    <div class="header-actions">
        <span class="badge-new">Sigejub v1.0</span>
    </div>
</header>

<div class="ayuda-grid">

    {{-- ==============================================
         PANEL DESARROLLADORES
         ============================================== --}}
    <div class="ayuda-panel" id="panelAyudaDesarrolladores">
        <button type="button" class="ayuda-panel-header" onclick="toggleAyudaPanel(this)" aria-expanded="false">
            <span class="ayuda-titulo"><i class="fas fa-code"></i> Desarrolladores</span>
            <i class="fas fa-chevron-down ayuda-flecha"></i>
        </button>
        <div class="ayuda-panel-body">
            <div class="ayuda-panel-contenido">
                <h3 class="ayuda-seccion-titulo"><i class="fas fa-headset"></i> Soporte técnico</h3>
                <p style="font-size:0.82rem;color:#64748b;margin:0 0 18px;">Estos son los desarrolladores del sistema. Contáctelos para cualquier requerimiento o incidencia de soporte.</p>
                <div class="ayuda-dev-grid">

                    {{-- ## DESARROLLADOR 1 ## --}}
                    <div class="ayuda-dev-card">
                        {{-- FOTO: coloque la imagen en public/img/desarrolladores/dev-1.jpg y se mostrará automáticamente. Mientras no exista, se muestran las iniciales. --}}
                        <div class="ayuda-dev-foto">
                            <div class="ayuda-dev-iniciales">ER</div>
                            <img src="{{ asset('img/desarrolladores/dev-1.jpg') }}" alt="Foto Desarrollador 1" onerror="this.style.display='none'">
                        </div>
                        <h4>Edgardo Rodríguez</h4>
                        <span class="ayuda-dev-rol">Desarrollador</span>
                        <div class="ayuda-dev-dato"><strong>Cédula</strong><span>V-31416785</span></div>
                        <div class="ayuda-dev-dato"><strong>Correo</strong><span>edgardorodriguezz.250@gmail.com</span></div>
                        <div class="ayuda-dev-dato"><strong>Teléfono</strong><span>0412-6778272</span></div>
                    </div>

                    {{-- ## DESARROLLADOR 2 ## --}}
                    <div class="ayuda-dev-card">
                        {{-- FOTO: coloque la imagen en public/img/desarrolladores/dev-2.jpg y se mostrará automáticamente. Mientras no exista, se muestran las iniciales. --}}
                        <div class="ayuda-dev-foto">
                            <div class="ayuda-dev-iniciales">YR</div>
                            <img src="{{ asset('img/desarrolladores/dev-2.jpg') }}" alt="Foto Desarrollador 2" onerror="this.style.display='none'">
                        </div>
                        <h4>Yocelianna Rodríguez</h4>
                        <span class="ayuda-dev-rol">Desarrolladora</span>
                        <div class="ayuda-dev-dato"><strong>Cédula</strong><span>V-32201627</span></div>
                        <div class="ayuda-dev-dato"><strong>Correo</strong><span>yocelianna899@gmail.com</span></div>
                        <div class="ayuda-dev-dato"><strong>Teléfono</strong><span>0412-0276113</span></div>
                    </div>

                    {{-- ## DESARROLLADOR 3 ## --}}
                    <div class="ayuda-dev-card">
                        {{-- FOTO: coloque la imagen en public/img/desarrolladores/dev-3.jpg y se mostrará automáticamente. Mientras no exista, se muestran las iniciales. --}}
                        <div class="ayuda-dev-foto">
                            <div class="ayuda-dev-iniciales">FO</div>
                            <img src="{{ asset('img/desarrolladores/dev-3.jpg') }}" alt="Foto Desarrollador 3" onerror="this.style.display='none'">
                        </div>
                        <h4>Frandynson Ochoa</h4>
                        <span class="ayuda-dev-rol">Desarrollador</span>
                        <div class="ayuda-dev-dato"><strong>Cédula</strong><span>V-30562598</span></div>
                        <div class="ayuda-dev-dato"><strong>Correo</strong><span>frandynson777@gmail.com</span></div>
                        <div class="ayuda-dev-dato"><strong>Teléfono</strong><span>0424-5048912</span></div>
                    </div>

                    {{-- ## DESARROLLADOR 4 ## --}}
                    <div class="ayuda-dev-card">
                        {{-- FOTO: coloque la imagen en public/img/desarrolladores/dev-4.jpg y se mostrará automáticamente. Mientras no exista, se muestran las iniciales. --}}
                        <div class="ayuda-dev-foto">
                            <div class="ayuda-dev-iniciales">BV</div>
                            <img src="{{ asset('img/desarrolladores/dev-4.jpg') }}" alt="Foto Desarrollador 4" onerror="this.style.display='none'">
                        </div>
                        <h4>Brayan Vizcaya</h4>
                        <span class="ayuda-dev-rol">Desarrollador</span>
                        <div class="ayuda-dev-dato"><strong>Cédula</strong><span>V-31571097</span></div>
                        <div class="ayuda-dev-dato"><strong>Correo</strong><span>vizcayabrayan32@gmail.com</span></div>
                        <div class="ayuda-dev-dato"><strong>Teléfono</strong><span>0412-6205904</span></div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ==============================================
         PANEL GUÍAS
         ============================================== --}}
    <div class="ayuda-panel" id="panelAyudaGuias">
        <button type="button" class="ayuda-panel-header" onclick="toggleAyudaPanel(this)" aria-expanded="false">
            <span class="ayuda-titulo"><i class="fas fa-book-open"></i> Guías</span>
            <i class="fas fa-chevron-down ayuda-flecha"></i>
        </button>
        <div class="ayuda-panel-body">
            <div class="ayuda-panel-contenido">

                <h3 class="ayuda-seccion-titulo"><i class="fas fa-file-pdf"></i> Manual de usuario</h3>
                {{-- MANUAL PDF: coloque el archivo "manual_usuario_sigejub.pdf" en public/documentos/ y el botón lo abrirá en una pestaña nueva. --}}
                <div class="ayuda-manual-card">
                    <div class="ayuda-manual-info">
                        <div class="ayuda-manual-icon"><i class="fas fa-book"></i></div>
                        <div>
                            <h3>Manual de Usuario SIGEJUB</h3>
                            <p>Guía completa del sistema de jubilaciones: registro, solicitudes, expedientes, nómina y prestaciones.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-primary-dark" onclick="window.open('{{ asset('documentos/manual_usuario_sigejub.pdf') }}','_blank')">
                        <i class="fas fa-download"></i> Ver Manual (PDF)
                    </button>
                </div>

                <h3 class="ayuda-seccion-titulo"><i class="fas fa-video"></i> Videos explicativos</h3>
                <p style="font-size:0.82rem;color:#64748b;margin:0 0 18px;">A continuación se mostrarán los videos explicativos del funcionamiento del sistema.</p>
                <div class="ayuda-videos-grid">

                    {{-- VIDEO 1: reemplace el bloque <div class="ayuda-video">PASTE AQUI</div> por un iframe con el enlace de incrustación (YouTube). Ej.: <div class="ayuda-video"><iframe src="https://www.youtube.com/embed/XXXXXXX" allowfullscreen></iframe></div> --}}
                    <div class="ayuda-video-placeholder">
                        <i class="fas fa-play-circle"></i>
                        <p>Video explicativo 1</p>
                        <span style="font-size:0.7rem;">Aquí se insertará el video.</span>
                    </div>

                    {{-- VIDEO 2: igual que el video 1. --}}
                    <div class="ayuda-video-placeholder">
                        <i class="fas fa-play-circle"></i>
                        <p>Video explicativo 2</p>
                        <span style="font-size:0.7rem;">Aquí se insertará el video.</span>
                    </div>

                    {{-- VIDEO 3: igual que el video 1. --}}
                    <div class="ayuda-video-placeholder">
                        <i class="fas fa-play-circle"></i>
                        <p>Video explicativo 3</p>
                        <span style="font-size:0.7rem;">Aquí se insertará el video.</span>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

<script>
function toggleAyudaPanel(btn) {
    const panel = btn.parentElement;
    const body = panel.querySelector('.ayuda-panel-body');
    const abierto = body.classList.contains('open');

    // Cierra todos los paneles de ayuda
    document.querySelectorAll('#panelAyudaDesarrolladores, #panelAyudaGuias').forEach(function(p) {
        const b = p.querySelector('.ayuda-panel-body');
        const h = p.querySelector('.ayuda-panel-header');
        if (b && b !== body) { b.classList.remove('open'); b.style.maxHeight = null; }
        if (h) h.classList.remove('open');
    });

    if (!abierto) {
        body.classList.add('open');
        body.style.maxHeight = body.scrollHeight + 'px';
        btn.classList.add('open');
        btn.setAttribute('aria-expanded', 'true');
    } else {
        body.classList.remove('open');
        body.style.maxHeight = null;
        btn.classList.remove('open');
        btn.setAttribute('aria-expanded', 'false');
    }
}
</script>