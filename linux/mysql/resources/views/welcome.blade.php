<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGEJUB - Sistema Integral de Gestión de Jubilaciones</title>
    <link rel="icon" href="{{ asset('img/logo-dark.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('css/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
    <script src="{{ asset('js/theme-welcome.js') }}" defer></script>
</head>
<body>

    <nav class="pw-nav">
        <div class="pw-nav-inner">
            <a href="/" class="pw-nav-brand">
                <img src="{{ asset('img/logo-light.svg') }}" alt="SIGEJUB">
                <span class="pw-nav-brand-text">SIGEJUB</span>
            </a>
            <button class="pw-nav-toggle" onclick="document.querySelector('.pw-nav-links').classList.toggle('active')" aria-label="Menú">
                <i class="fas fa-bars"></i>
            </button>
            <div class="pw-nav-links">
                <a href="#inicio">Inicio</a>
                <a href="#funcionalidades">Funcionalidades</a>
                <a href="#contacto">Contacto</a>
                <a href="{{ route('login') }}" class="pw-nav-login">Ingresar</a>
            </div>
        </div>
    </nav>

    <main>
        <section class="pw-hero" id="inicio">
            <div class="pw-hero-content">
                <div class="pw-hero-logo">
                    <img src="{{ asset('img/logo-light.svg') }}" alt="SIGEJUB">
                </div>
                <h1 class="pw-hero-title">SIGEJUB 1.0</h1>
                <div class="pw-hero-divider"></div>
                <p class="pw-hero-subtitle">Sistema Integral de Gestión de Jubilaciones. Administre trabajadores, solicitudes, expedientes y prestaciones en una sola plataforma.</p>
                <div class="pw-hero-actions">
                    <a href="{{ route('login') }}" class="pw-btn pw-btn-primary">
                        <i class="fas fa-right-to-bracket"></i> Acceder al Sistema
                    </a>
                    <a href="#funcionalidades" class="pw-btn pw-btn-outline">
                        <i class="fas fa-arrow-down"></i> Conocer Más
                    </a>
                </div>
            </div>
        </section>

        <section class="pw-section pw-features" id="funcionalidades">
            <div class="pw-section-inner">
                <h2 class="pw-section-title">Excelencia en Gestión de Jubilaciones</h2>
                <p class="pw-section-subtitle">Herramientas diseñadas para la administración eficiente del proceso jubilatorio</p>
                <div class="pw-features-grid">
                    <div class="pw-feature-card pw-animate">
                        <div class="pw-feature-icon"><i class="fas fa-users-gear"></i></div>
                        <h3 class="pw-feature-title">Gestión de Trabajadores</h3>
                        <p class="pw-feature-desc">Administre el registro completo de trabajadores con expedientes digitales, historial laboral y seguimiento automatizado.</p>
                    </div>
                    <div class="pw-feature-card pw-animate">
                        <div class="pw-feature-icon"><i class="fas fa-file-invoice"></i></div>
                        <h3 class="pw-feature-title">Solicitudes y Expedientes</h3>
                        <p class="pw-feature-desc">Gestione solicitudes de jubilación, documentos digitales y expedientes con flujos de aprobación integrados.</p>
                    </div>
                    <div class="pw-feature-card pw-animate">
                        <div class="pw-feature-icon"><i class="fas fa-calculator"></i></div>
                        <h3 class="pw-feature-title">Fórmulas de Cálculo</h3>
                        <p class="pw-feature-desc">Defina y aplique fórmulas personalizadas para el cálculo de prestaciones según antigüedad, salario y tipo de jubilación.</p>
                    </div>
                    <div class="pw-feature-card pw-animate">
                        <div class="pw-feature-icon"><i class="fas fa-money-bill-trend-up"></i></div>
                        <h3 class="pw-feature-title">Caja Negra y Nómina</h3>
                        <p class="pw-feature-desc">Control total sobre pagos, retenciones, bonificaciones y generación de nómina para jubilados activos.</p>
                    </div>
                    <div class="pw-feature-card pw-animate">
                        <div class="pw-feature-icon"><i class="fas fa-hand-holding-dollar"></i></div>
                        <h3 class="pw-feature-title">Prestaciones</h3>
                        <p class="pw-feature-desc">Calcule y gestione prestaciones de servicios, bonificación por tiempo de servicio y beneficios complementarios.</p>
                    </div>
                    <div class="pw-feature-card pw-animate">
                        <div class="pw-feature-icon"><i class="fas fa-chart-bar"></i></div>
                        <h3 class="pw-feature-title">Reportes Analíticos</h3>
                        <p class="pw-feature-desc">Genere informes detallados sobre prestaciones, años de servicio y proyecciones para la toma de decisiones.</p>
                    </div>
                    <div class="pw-feature-card pw-animate">
                        <div class="pw-feature-icon"><i class="fas fa-money-exchange"></i></div>
                        <h3 class="pw-feature-title">Tasas de Cambio</h3>
                        <p class="pw-feature-desc">Consulte y administre tasas de cambio actualizadas para conversiones monetarias en cálculos de prestaciones.</p>
                    </div>
                    <div class="pw-feature-card pw-animate">
                        <div class="pw-feature-icon"><i class="fas fa-clock-rotate-left"></i></div>
                        <h3 class="pw-feature-title">Historial Completo</h3>
                        <p class="pw-feature-desc">Registro cronológico de todas las acciones realizadas sobre expedientes, solicitudes y pagos con trazabilidad total.</p>
                    </div>
                    <div class="pw-feature-card pw-animate">
                        <div class="pw-feature-icon"><i class="fas fa-building-columns"></i></div>
                        <h3 class="pw-feature-title">Cargos y Grados</h3>
                        <p class="pw-feature-desc">Administre la estructura organizativa con gestión de cargos, grados jerárquicos y su relación con las prestaciones.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="pw-section pw-stats">
            <div class="pw-section-inner">
                <div class="pw-stats-grid">
                    <div class="pw-stat-item pw-animate">
                        <div class="pw-stat-number">1,200+</div>
                        <div class="pw-stat-label">Trabajadores Registrados</div>
                    </div>
                    <div class="pw-stat-item pw-animate">
                        <div class="pw-stat-number">450+</div>
                        <div class="pw-stat-label">Solicitudes Procesadas</div>
                    </div>
                    <div class="pw-stat-item pw-animate">
                        <div class="pw-stat-number">380+</div>
                        <div class="pw-stat-label">Jubilaciones Activas</div>
                    </div>
                    <div class="pw-stat-item pw-animate">
                        <div class="pw-stat-number">99%</div>
                        <div class="pw-stat-label">Disponibilidad del Sistema</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="pw-section pw-contact" id="contacto">
            <div class="pw-section-inner">
                <h2 class="pw-section-title">Soporte Técnico y Contacto</h2>
                <p class="pw-section-subtitle">Estamos a su disposición para resolver cualquier inconveniente técnico o duda</p>
                <div class="pw-contact-grid">
                    <div class="pw-contact-info pw-animate">
                        <div class="pw-contact-item">
                            <div class="pw-contact-icon"><i class="fas fa-envelope"></i></div>
                            <span class="pw-contact-text">soporte@sigejub.edu</span>
                        </div>
                        <div class="pw-contact-item">
                            <div class="pw-contact-icon"><i class="fas fa-phone"></i></div>
                            <span class="pw-contact-text">+58 412 123 4567</span>
                        </div>
                        <div class="pw-contact-item">
                            <div class="pw-contact-icon"><i class="fas fa-location-dot"></i></div>
                            <span class="pw-contact-text">Palacio de Justicia, Caracas, Venezuela</span>
                        </div>
                        <div class="pw-contact-item">
                            <div class="pw-contact-icon"><i class="fas fa-clock"></i></div>
                            <span class="pw-contact-text">Lunes a Viernes: 8:00 AM - 4:00 PM</span>
                        </div>
                    </div>
                    <div class="pw-contact-form pw-animate">
                        <form onsubmit="event.preventDefault()">
                            <div class="pw-form-row">
                                <div class="pw-form-group">
                                    <label class="pw-form-label">Nombre Completo</label>
                                    <input class="pw-form-input" type="text" placeholder="Ej. Juan Pérez">
                                </div>
                                <div class="pw-form-group">
                                    <label class="pw-form-label">Correo Electrónico</label>
                                    <input class="pw-form-input" type="email" placeholder="correo@institucion.edu">
                                </div>
                            </div>
                            <div class="pw-form-group">
                                <label class="pw-form-label">Asunto</label>
                                <select class="pw-form-select">
                                    <option>Soporte Técnico</option>
                                    <option>Consulta de Jubilación</option>
                                    <option>Actualización de Datos</option>
                                    <option>Otro</option>
                                </select>
                            </div>
                            <div class="pw-form-group">
                                <label class="pw-form-label">Mensaje</label>
                                <textarea class="pw-form-textarea" placeholder="Describa su solicitud detalladamente..." rows="4"></textarea>
                            </div>
                            <button class="pw-form-btn" type="submit">
                                <i class="fas fa-paper-plane"></i> Enviar Solicitud
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="pw-footer">
        <div class="pw-footer-inner">
            <div class="pw-footer-brand">
                <span class="pw-footer-brand-name">SIGEJUB</span>
                <span class="pw-footer-copy">&copy; {{ date('Y') }} - Sistema de Gestión de Jubilaciones</span>
            </div>
            <div class="pw-footer-links">
                <a href="#inicio">Inicio</a>
                <a href="#funcionalidades">Funcionalidades</a>
                <a href="#contacto">Contacto</a>
                <a href="#">Aviso Legal</a>
            </div>
            <div class="pw-footer-icons">
                <i class="fas fa-scale-balanced" title="Transparencia"></i>
                <i class="fas fa-shield-halved" title="Seguridad"></i>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var sections = document.querySelectorAll('section[id]');
        var navLinks = document.querySelectorAll('.pw-nav-links a[href^="#"]');

        function updateActiveLink() {
            var current = '';
            sections.forEach(function(section) {
                var top = section.offsetTop - 120;
                if (window.scrollY >= top) {
                    current = section.getAttribute('id');
                }
            });
            navLinks.forEach(function(link) {
                var href = link.getAttribute('href').substring(1);
                if (href === current) {
                    link.style.color = 'var(--pw-primary)';
                } else {
                    link.style.color = '';
                }
            });
        }

        window.addEventListener('scroll', updateActiveLink, { passive: true });
        updateActiveLink();

        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.pw-animate').forEach(function(el) {
            observer.observe(el);
        });
    });
    </script>
</body>
</html>