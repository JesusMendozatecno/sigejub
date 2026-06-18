
<!DOCTYPE html>
<html class="scroll-smooth" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>SIGEJUB GESTIÓN INTEGRAL 1.0</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;600;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;0,8..60,700;1,8..60,400&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link rel="icon" href="<?php echo e(asset('img/logo-dark.svg')); ?>" type="image/svg+xml">
    <link rel="shortcut icon" href="<?php echo e(asset('img/logo-dark.svg')); ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?php echo e(asset('css/fontawesome/css/all.min.css')); ?>">
    <style>
        body {
            background-color: #f7fafc;
            background-image:
                radial-gradient(circle at 20% 30%, rgba(26, 54, 93, 0.02) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(26, 54, 93, 0.02) 0%, transparent 50%),
                linear-gradient(45deg, rgba(226, 232, 240, 0.2) 0%, rgba(255, 255, 255, 0) 100%);
            background-attachment: fixed;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            border: 1px solid #E2E8F0;
        }
        .shadow-judicial {
            box-shadow: 0 10px 25px -5px rgba(26, 54, 93, 0.05);
        }
        .section-snap { scroll-snap-align: start; }
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fade-in 1s ease-out; }
        @media (prefers-reduced-motion: reduce) {
            .animate-fade-in { animation: none; }
        }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(100,116,139,0.25); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(100,116,139,0.4); }
        ::-webkit-scrollbar-corner { background: transparent; }
        * { scrollbar-width: thin; scrollbar-color: rgba(100,116,139,0.25) transparent; }
        .dark ::-webkit-scrollbar-thumb { background: rgba(148,163,184,0.2); }
        .dark ::-webkit-scrollbar-thumb:hover { background: rgba(148,163,184,0.35); }
        .dark { scrollbar-color: rgba(148,163,184,0.2) transparent; }
    </style>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#002045",
                        "primary-container": "#1a365d",
                        "on-primary": "#ffffff",
                        "on-primary-container": "#86a0cd",
                        "primary-fixed": "#d6e3ff",
                        "primary-fixed-dim": "#adc7f7",
                        "secondary": "#515f74",
                        "secondary-container": "#d1e1fa",
                        "secondary-fixed": "#d4e4fc",
                        "secondary-fixed-dim": "#b8c8e0",
                        "on-secondary": "#ffffff",
                        "on-secondary-container": "#556479",
                        "on-secondary-fixed": "#0d1c2e",
                        "on-secondary-fixed-variant": "#39485c",
                        "tertiary": "#1b2127",
                        "tertiary-container": "#30363c",
                        "tertiary-fixed": "#dde3eb",
                        "tertiary-fixed-dim": "#c1c7cf",
                        "on-tertiary": "#ffffff",
                        "on-tertiary-container": "#989fa6",
                        "on-tertiary-fixed": "#161c22",
                        "on-tertiary-fixed-variant": "#41474e",
                        "error": "#ba1a1a",
                        "error-container": "#ffdad6",
                        "on-error": "#ffffff",
                        "on-error-container": "#93000a",
                        "surface": "#f7fafc",
                        "surface-dim": "#d7dadc",
                        "surface-bright": "#f7fafc",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#f1f4f6",
                        "surface-container": "#ebeef0",
                        "surface-container-high": "#e5e9eb",
                        "surface-container-highest": "#e0e3e5",
                        "on-surface": "#181c1e",
                        "on-surface-variant": "#43474e",
                        "surface-variant": "#e0e3e5",
                        "outline": "#74777f",
                        "outline-variant": "#c4c6cf",
                        "inverse-surface": "#2d3133",
                        "inverse-on-surface": "#eef1f3",
                        "inverse-primary": "#adc7f7",
                        "surface-tint": "#455f88",
                        "background": "#f7fafc",
                        "on-background": "#181c1e"
                    },
                    fontFamily: {
                        "body": ["Public Sans", "system-ui", "sans-serif"],
                        "display": ["Source Serif 4", "Georgia", "serif"]
                    },
                    fontSize: {
                        "display-xl": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "700" }],
                        "display-lg": ["40px", { lineHeight: "48px", letterSpacing: "-0.01em", fontWeight: "700" }],
                        "headline-lg": ["32px", { lineHeight: "40px", fontWeight: "600" }],
                        "headline-md": ["24px", { lineHeight: "32px", fontWeight: "600" }],
                        "headline-sm": ["20px", { lineHeight: "28px", fontWeight: "600" }],
                        "body-lg": ["18px", { lineHeight: "28px" }],
                        "body-md": ["16px", { lineHeight: "24px" }],
                        "body-sm": ["14px", { lineHeight: "20px" }],
                        "label-lg": ["14px", { lineHeight: "20px", letterSpacing: "0.05em", fontWeight: "600" }],
                        "label-md": ["12px", { lineHeight: "16px", letterSpacing: "0.05em", fontWeight: "600" }],
                        "label-sm": ["11px", { lineHeight: "16px", fontWeight: "700" }]
                    },
                    spacing: {
                        "gutter": "24px",
                        "stack-sm": "8px",
                        "stack-md": "16px",
                        "stack-lg": "32px",
                        "margin-mobile": "16px",
                        "margin-desktop": "48px",
                        "container-max": "1280px"
                    }
                }
            }
        }
    </script>
</head>
<body class="font-body text-on-surface selection:bg-primary-container selection:text-on-primary-container">

    <!-- TopNavBar -->
    <nav class="fixed top-0 left-0 w-full z-50 flex justify-between items-center px-margin-mobile md:px-margin-desktop h-16 bg-primary text-on-primary border-b border-white/10 shadow-md">
        <div class="flex items-center gap-stack-md">
            <img src="<?php echo e(asset('img/logo-light.svg')); ?>" alt="SIGEJUB" class="h-10 w-10">
            <span class="font-display text-headline-sm font-bold text-on-primary tracking-tight">SIGEJUB</span>
        </div>
        <div class="hidden md:flex items-center gap-stack-lg">
            <a class="text-on-primary border-b-2 border-on-primary pb-1 font-bold text-label-lg" href="#inicio">Inicio</a>
            <a class="text-on-primary/80 font-medium hover:text-on-primary text-label-lg transition-all" href="#funcionalidades">Funcionalidades</a>
            <a class="text-on-primary/80 font-medium hover:text-on-primary text-label-lg transition-all" href="#noticias">Noticias</a>
            <a class="text-on-primary/80 font-medium hover:text-on-primary text-label-lg transition-all" href="#contacto">Contacto</a>
        </div>
        <div class="flex items-center gap-stack-md">
            <a href="<?php echo e(route('login')); ?>" class="bg-white/10 border border-white/20 text-on-primary px-5 py-2 text-label-lg hover:bg-white/20 transition-all duration-300 scale-95 active:scale-90 rounded-sm">
                Ingresar
            </a>
        </div>
    </nav>

    <main class="w-full">

        <!-- Hero Section (Solo esta seccion tiene fondo de imagen) -->
        <!-- CAMBIAR IMAGEN: Reemplaza 'hero-bg.jpg' en public/img/bg/ por la imagen que desees -->
        <section class="min-h-screen flex flex-col justify-center items-center text-center px-margin-mobile md:px-0 section-snap relative overflow-hidden" id="inicio">
            <!-- Fondo de imagen con overlay oscuro -->
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('<?php echo e(asset('img/bg/hero-bg.jpg')); ?>');"></div>
            <div class="absolute inset-0 bg-primary/70"></div>
            <div class="max-w-4xl mx-auto flex flex-col items-center gap-stack-lg animate-fade-in relative z-10">
                <div>
                    <img src="<?php echo e(asset('img/logo-light.svg')); ?>" alt="SIGEJUB" class="w-28 h-28 mx-auto">
                </div>
                <div>
                    <h1 class="font-display text-display-xl md:text-display-xl text-white mb-3">SIGEJUB 1.0</h1>
                    <p class="font-body text-headline-md text-white/80 max-w-2xl mx-auto">Sistema Integral de Gestión de Jubilaciones</p>
                </div>
                <div class="w-20 h-0.5 bg-white/20 rounded-full"></div>
                <div class="flex flex-wrap justify-center gap-stack-md pt-stack-sm">
                    <a href="<?php echo e(route('login')); ?>" class="bg-white text-primary px-8 py-3 text-label-lg uppercase tracking-widest hover:bg-white/90 transition-colors shadow-md rounded-sm font-bold">
                        Acceder al Sistema
                    </a>
                    <a href="#funcionalidades" class="border border-white/40 text-white px-8 py-3 text-label-lg uppercase tracking-widest hover:bg-white/10 transition-colors rounded-sm">
                        Conocer Más
                    </a>
                </div>
            </div>
        </section>

        <!-- Features Bento Grid -->
        <section class="min-h-screen py-stack-lg flex items-center bg-surface-container-low/50 section-snap" id="funcionalidades">
            <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop w-full">
                <h2 class="font-display text-headline-lg text-primary text-center mb-stack-lg">Excelencia en Gestión de Jubilaciones</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">

                    <div class="glass-card p-stack-lg flex flex-col justify-between shadow-judicial border-l-4 border-l-primary group hover:-translate-y-1 transition-transform">
                        <div>
                            <div class="w-12 h-12 bg-primary/5 rounded-lg flex items-center justify-center mb-stack-md">
                                <i class="fas fa-users-gear text-primary text-xl"></i>
                            </div>
                            <h3 class="font-display text-headline-sm text-primary mb-stack-sm">Gestión de Trabajadores</h3>
                            <p class="font-body text-body-md text-on-surface-variant">Administre el registro completo de trabajadores con expedientes digitales, historial laboral y seguimiento automatizado.</p>
                        </div>
                        <a class="mt-stack-lg inline-flex items-center text-primary font-bold text-label-lg group-hover:gap-2 transition-all" href="<?php echo e(route('login')); ?>">
                            + Detalles <i class="fas fa-arrow-right text-sm ml-1"></i>
                        </a>
                    </div>

                    <div class="glass-card p-stack-lg flex flex-col justify-between shadow-judicial border-l-4 border-l-primary group hover:-translate-y-1 transition-transform">
                        <div>
                            <div class="w-12 h-12 bg-primary/5 rounded-lg flex items-center justify-center mb-stack-md">
                                <i class="fas fa-file-invoice text-primary text-xl"></i>
                            </div>
                            <h3 class="font-display text-headline-sm text-primary mb-stack-sm">Solicitudes y Expedientes</h3>
                            <p class="font-body text-body-md text-on-surface-variant">Gestione solicitudes de jubilación, documentos digitales y expedientes con flujos de aprobación integrados.</p>
                        </div>
                        <a class="mt-stack-lg inline-flex items-center text-primary font-bold text-label-lg group-hover:gap-2 transition-all" href="<?php echo e(route('login')); ?>">
                            + Detalles <i class="fas fa-arrow-right text-sm ml-1"></i>
                        </a>
                    </div>

                    <div class="glass-card p-stack-lg flex flex-col justify-between shadow-judicial border-l-4 border-l-primary group hover:-translate-y-1 transition-transform">
                        <div>
                            <div class="w-12 h-12 bg-primary/5 rounded-lg flex items-center justify-center mb-stack-md">
                                <i class="fas fa-chart-bar text-primary text-xl"></i>
                            </div>
                            <h3 class="font-display text-headline-sm text-primary mb-stack-sm">Reportes Analíticos</h3>
                            <p class="font-body text-body-md text-on-surface-variant">Genere informes detallados sobre prestaciones, años de servicio y proyecciones para la toma de decisiones institucionales.</p>
                        </div>
                        <a class="mt-stack-lg inline-flex items-center text-primary font-bold text-label-lg group-hover:gap-2 transition-all" href="<?php echo e(route('login')); ?>">
                            + Detalles <i class="fas fa-arrow-right text-sm ml-1"></i>
                        </a>
                    </div>

                </div>
            </div>
        </section>

        <!-- News Section -->
        <section class="min-h-screen py-stack-lg bg-surface flex flex-col justify-center section-snap" id="noticias">
            <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop w-full">
                <div class="flex justify-between items-end mb-stack-lg">
                    <h2 class="font-display text-headline-lg text-primary">Noticias Institucionales</h2>
                    <a class="text-primary text-label-lg underline underline-offset-4" href="#">Ver toda la gaceta</a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">

                    <article class="bg-white border border-outline-variant overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="h-48 bg-gradient-to-br from-primary/5 to-primary/10 flex items-center justify-center">
                            <i class="fas fa-newspaper text-primary/30 text-5xl"></i>
                        </div>
                        <div class="p-stack-md">
                            <p class="text-label-sm text-primary uppercase tracking-widest mb-2">15 MAR 2026</p>
                            <h4 class="font-display text-headline-sm text-on-surface mb-stack-sm">Actualización del Sistema 1.0.4</h4>
                            <p class="font-body text-body-sm text-on-surface-variant">Se han integrado nuevos protocolos de seguridad para el manejo de expedientes digitales y optimización del rendimiento general.</p>
                        </div>
                    </article>

                    <article class="bg-white border border-outline-variant overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="h-48 bg-gradient-to-br from-primary/5 to-primary/10 flex items-center justify-center">
                            <i class="fas fa-calendar-check text-primary/30 text-5xl"></i>
                        </div>
                        <div class="p-stack-md">
                            <p class="text-label-sm text-primary uppercase tracking-widest mb-2">10 MAR 2026</p>
                            <h4 class="font-display text-headline-sm text-on-surface mb-stack-sm">Capacitación del Sistema</h4>
                            <p class="font-body text-body-sm text-on-surface-variant">Próxima jornada de capacitación para el uso de la nueva plataforma de gestión de jubilaciones y expedientes digitales.</p>
                        </div>
                    </article>

                    <article class="bg-white border border-outline-variant overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="h-48 bg-gradient-to-br from-primary/5 to-primary/10 flex items-center justify-center">
                            <i class="fas fa-handshake text-primary/30 text-5xl"></i>
                        </div>
                        <div class="p-stack-md">
                            <p class="text-label-sm text-primary uppercase tracking-widest mb-2">05 MAR 2026</p>
                            <h4 class="font-display text-headline-sm text-on-surface mb-stack-sm">Convenio Inter-institucional</h4>
                            <p class="font-body text-body-sm text-on-surface-variant">Firma de convenio para la optimización de recursos tecnológicos en sedes periféricas y modernización de procesos.</p>
                        </div>
                    </article>

                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="min-h-screen py-stack-lg bg-surface-container flex flex-col justify-center section-snap" id="contacto">
            <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop w-full">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-stack-lg items-center">
                    <div>
                        <h2 class="font-display text-headline-lg text-primary mb-stack-md">Soporte Técnico y Contacto</h2>
                        <p class="font-body text-body-lg text-on-surface-variant mb-stack-lg">Estamos a su disposición para resolver cualquier inconveniente técnico o duda sobre el sistema de gestión de jubilaciones.</p>
                        <div class="space-y-stack-md">
                            <div class="flex items-center gap-stack-md">
                                <div class="w-10 h-10 bg-primary flex items-center justify-center rounded-sm">
                                    <i class="fas fa-envelope text-white"></i>
                                </div>
                                <span class="font-body text-body-md">soporte@sigejub.edu</span>
                            </div>
                            <div class="flex items-center gap-stack-md">
                                <div class="w-10 h-10 bg-primary flex items-center justify-center rounded-sm">
                                    <i class="fas fa-phone text-white"></i>
                                </div>
                                <span class="font-body text-body-md">+58 412 123 4567</span>
                            </div>
                            <div class="flex items-center gap-stack-md">
                                <div class="w-10 h-10 bg-primary flex items-center justify-center rounded-sm">
                                    <i class="fas fa-location-dot text-white"></i>
                                </div>
                                <span class="font-body text-body-md">Palacio de Justicia, Caracas, Venezuela</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-stack-lg shadow-judicial border border-outline-variant rounded-sm">
                        <form class="space-y-gutter" onsubmit="event.preventDefault()">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter">
                                <div>
                                    <label class="block text-label-md text-on-surface-variant mb-1 uppercase tracking-wider">Nombre Completo</label>
                                    <input class="w-full border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary h-12 px-4 text-body-md" placeholder="Ej. Juan Pérez" type="text"/>
                                </div>
                                <div>
                                    <label class="block text-label-md text-on-surface-variant mb-1 uppercase tracking-wider">Correo Electrónico</label>
                                    <input class="w-full border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary h-12 px-4 text-body-md" placeholder="correo@institucion.edu" type="email"/>
                                </div>
                            </div>
                            <div>
                                <label class="block text-label-md text-on-surface-variant mb-1 uppercase tracking-wider">Asunto</label>
                                <select class="w-full border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary h-12 px-4 text-body-md">
                                    <option>Soporte Técnico</option>
                                    <option>Consulta de Jubilación</option>
                                    <option>Actualización de Datos</option>
                                    <option>Otro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-label-md text-on-surface-variant mb-1 uppercase tracking-wider">Mensaje</label>
                                <textarea class="w-full border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary px-4 py-2 text-body-md" placeholder="Describa su solicitud detalladamente..." rows="4"></textarea>
                            </div>
                            <button class="w-full bg-primary text-on-primary py-4 text-label-lg uppercase tracking-widest hover:bg-primary-container transition-colors shadow-md rounded-sm">
                                Enviar Solicitud
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="w-full py-stack-lg px-margin-mobile md:px-margin-desktop flex flex-col md:flex-row justify-between items-center gap-stack-md bg-surface-container-highest border-t-2 border-primary/20">
        <div class="flex flex-col items-center md:items-start">
            <span class="font-display text-headline-sm text-primary mb-2">SIGEJUB</span>
            <p class="font-body text-body-sm text-on-surface-variant max-w-xs text-center md:text-left">© <?php echo e(date('Y')); ?> - Sistema de Gestión de Jubilaciones</p>
        </div>
        <div class="flex flex-wrap justify-center gap-stack-md">
            <a class="text-label-sm text-on-surface-variant hover:text-primary underline transition-all" href="#inicio">Inicio</a>
            <a class="text-label-sm text-on-surface-variant hover:text-primary underline transition-all" href="#funcionalidades">Funcionalidades</a>
            <a class="text-label-sm text-on-surface-variant hover:text-primary underline transition-all" href="#noticias">Noticias</a>
            <a class="text-label-sm text-on-surface-variant hover:text-primary underline transition-all" href="#contacto">Contacto</a>
            <a class="text-label-sm text-on-surface-variant hover:text-primary underline transition-all" href="#">Aviso Legal</a>
            <a class="text-label-sm text-on-surface-variant hover:text-primary underline transition-all" href="#">Soporte Técnico</a>
        </div>
        <div class="flex gap-stack-md">
            <i class="fas fa-scale-balanced text-primary text-xl hover:scale-110 transition-transform cursor-pointer"></i>
            <i class="fas fa-shield-halved text-primary text-xl hover:scale-110 transition-transform cursor-pointer"></i>
        </div>
    </footer>

    <script>
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('nav a[href^="#"]');

        function updateActiveLink() {
            let current = "";
            sections.forEach(section => {
                const top = section.offsetTop - 120;
                if (window.scrollY >= top) {
                    current = section.getAttribute('id');
                }
            });
            navLinks.forEach(link => {
                const href = link.getAttribute('href').substring(1);
                link.classList.remove('text-on-primary', 'border-b-2', 'border-on-primary', 'pb-1', 'font-bold');
                link.classList.add('text-on-primary/80', 'font-medium');
                if (href === current) {
                    link.classList.add('text-on-primary', 'border-b-2', 'border-on-primary', 'pb-1', 'font-bold');
                    link.classList.remove('text-on-primary/80', 'font-medium');
                }
            });
        }

        window.addEventListener('scroll', updateActiveLink, { passive: true });
        window.addEventListener('load', updateActiveLink);
    </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\sigejub 2\resources\views/welcome.blade.php ENDPATH**/ ?>