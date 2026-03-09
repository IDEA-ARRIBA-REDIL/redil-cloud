<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Software para Iglesias y Pastores - REDIL Cloud</title>
    <meta name="description" content="Administra tu congregación en tiempo real. Software inteligente para iglesias con presencia en 16 países.">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Google Fonts: Plus Jakarta Sans + Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        /* =====================================
           DESIGN TOKENS (HexTone-inspired)
        ===================================== */
        :root {
            --primary:       #1b86fa;
            --primary-dark:  #0a6fd8;
            --heading:       #120036;
            --body-text:     #6c757d;
            --nav-text:      #353f4f;
            --bg-white:      #ffffff;
            --bg-light:      #f8faff;
            --bg-alt:        #f0f4ff;
            --border:        #e4ecff;
            --success:       #12c46e;
            --font-head:     'Plus Jakarta Sans', sans-serif;
            --font-body:     'Inter', sans-serif;
        }

        * { box-sizing: border-box; }

        body {
            font-family: var(--font-body);
            color: var(--body-text);
            background: var(--bg-white);
            line-height: 1.7;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5 {
            font-family: var(--font-head);
            color: var(--heading);
            font-weight: 700;
        }

        /* =====================================
           NAVBAR
        ===================================== */
        .navbar {
            background: var(--bg-white);
            padding: 18px 0;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar-brand {
            font-family: var(--font-head);
            font-weight: 800;
            font-size: 1.4rem;
            color: var(--heading) !important;
            text-decoration: none;
        }
        .navbar-brand span { color: var(--primary); }
        .nav-link {
            font-family: var(--font-head);
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--nav-text) !important;
            padding: 6px 14px !important;
            transition: color .2s;
        }
        .nav-link:hover { color: var(--primary) !important; }
        .btn-nav-cta {
            background: var(--primary);
            color: #fff !important;
            font-family: var(--font-head);
            font-weight: 600;
            font-size: 0.9rem;
            padding: 10px 22px;
            border-radius: 8px;
            border: none;
            transition: background .2s, transform .2s;
            text-decoration: none;
        }
        .btn-nav-cta:hover { background: var(--primary-dark); transform: translateY(-1px); }

        /* =====================================
           HERO SECTION
        ===================================== */
        .hero-section {
            background: var(--bg-light);
            border-radius: 0 0 32px 32px;
            padding: 90px 0 60px;
            margin: 0 20px;
            position: relative;
            overflow: hidden;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            top: -100px; right: -100px;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(27,134,250,0.10) 0%, transparent 70%);
            border-radius: 50%;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 100px;
            padding: 8px 18px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--nav-text);
            margin-bottom: 24px;
            box-shadow: 0 2px 12px rgba(27,134,250,0.08);
        }
        .hero-badge .dot {
            width: 8px; height: 8px;
            background: var(--success);
            border-radius: 50%;
        }
        .hero-title {
            font-size: clamp(2.2rem, 5vw, 3.4rem);
            font-weight: 800;
            color: var(--heading);
            line-height: 1.2;
            margin-bottom: 22px;
        }
        .hero-subtitle {
            font-size: 1.1rem;
            color: var(--body-text);
            max-width: 480px;
            margin-bottom: 36px;
        }
        .btn-primary-main {
            background: var(--primary);
            color: #fff;
            font-family: var(--font-head);
            font-weight: 700;
            font-size: 1rem;
            padding: 14px 30px;
            border-radius: 8px;
            border: none;
            text-decoration: none;
            display: inline-block;
            transition: background .2s, transform .2s, box-shadow .2s;
        }
        .btn-primary-main:hover {
            background: var(--primary-dark);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(27,134,250,0.3);
        }
        .btn-secondary-main {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--nav-text);
            font-family: var(--font-head);
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            padding: 14px 0;
            transition: color .2s;
        }
        .btn-secondary-main .play-btn {
            width: 44px; height: 44px;
            background: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            font-size: 1rem;
            color: var(--primary);
        }
        .btn-secondary-main:hover { color: var(--primary); }
        .hero-illustration {
            position: relative;
        }
        .hero-illustration img {
            width: 100%;
            max-width: 520px;
            filter: drop-shadow(0 20px 40px rgba(27,134,250,0.15));
        }

        /* =====================================
           TRUSTED BY LOGOS
        ===================================== */
        .trust-section {
            padding: 50px 0;
            border-bottom: 1px solid var(--border);
        }
        .trust-label {
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--body-text);
        }
        .trust-logos {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 48px;
            flex-wrap: wrap;
            margin-top: 28px;
        }
        .trust-logo {
            font-family: var(--font-head);
            font-weight: 700;
            font-size: 1.1rem;
            color: #b0bec5;
            letter-spacing: -0.5px;
        }

        /* =====================================
           SECTION LABELS
        ===================================== */
        .section-label {
            display: inline-block;
            font-family: var(--font-head);
            font-weight: 700;
            font-size: 0.78rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--primary);
            margin-bottom: 14px;
        }
        .section-title {
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            font-weight: 800;
            color: var(--heading);
            line-height: 1.2;
            margin-bottom: 18px;
        }
        .section-sub {
            font-size: 1.05rem;
            color: var(--body-text);
            max-width: 520px;
        }

        /* =====================================
           FEATURE ALTERNATING SECTIONS
        ===================================== */
        .feature-section { padding: 90px 0; }
        .feature-section.bg-alt { background: var(--bg-alt); }

        .feature-check {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 14px;
        }
        .feature-check i {
            color: var(--primary);
            font-size: 1.1rem;
            margin-top: 3px;
            flex-shrink: 0;
        }
        .feature-check p { margin: 0; color: var(--body-text); }

        .feature-card-mockup {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(27,134,250,0.12);
            padding: 28px;
            position: relative;
        }
        .stat-chip {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 28px rgba(0,0,0,0.10);
            padding: 16px 22px;
            display: inline-block;
        }
        .stat-chip .stat-val {
            font-family: var(--font-head);
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--heading);
        }
        .stat-chip .stat-label {
            font-size: 0.8rem;
            color: var(--body-text);
        }

        /* =====================================
           MINI ICON FEATURES
        ===================================== */
        .mini-feature {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 30px;
        }
        .mini-feature-icon {
            width: 52px; height: 52px;
            background: rgba(27,134,250,0.1);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            color: var(--primary);
            font-size: 1.4rem;
            flex-shrink: 0;
        }
        .mini-feature h6 {
            font-family: var(--font-head);
            font-weight: 700;
            color: var(--heading);
            margin-bottom: 4px;
        }
        .mini-feature p { margin: 0; font-size: 0.9rem; }

        /* =====================================
           STATISTICS COUNTER SECTION
        ===================================== */
        .stats-section {
            padding: 80px 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }
        .stat-item {
            text-align: center;
            padding: 30px 20px;
        }
        .stat-item .stat-number {
            font-family: var(--font-head);
            font-size: 3rem;
            font-weight: 800;
            color: var(--heading);
            line-height: 1;
            margin-bottom: 8px;
        }
        .stat-item .stat-number .arrow {
            font-size: 1.6rem;
            color: var(--success);
        }
        .stat-item h6 {
            font-family: var(--font-head);
            font-weight: 700;
            color: var(--heading);
        }
        .stat-divider {
            width: 1px;
            background: var(--border);
            align-self: stretch;
            margin: 20px 0;
        }

        /* =====================================
           BENEFITS GRID
        ===================================== */
        .benefits-section { padding: 90px 0; background: var(--bg-light); }
        .benefit-card {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            height: 100%;
            border: 1px solid var(--border);
            transition: box-shadow .3s, transform .3s;
        }
        .benefit-card:hover {
            box-shadow: 0 12px 40px rgba(27,134,250,0.12);
            transform: translateY(-4px);
        }
        .benefit-icon {
            width: 56px; height: 56px;
            background: rgba(27,134,250,0.1);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            color: var(--primary);
            font-size: 1.5rem;
            margin-bottom: 18px;
        }
        .benefit-card h5 {
            font-size: 1.05rem;
            margin-bottom: 10px;
        }

        /* =====================================
           CONTACT SECTION
        ===================================== */
        .contact-section { padding: 90px 0; }
        .contact-info-item {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }
        .contact-info-item .icon-wrap {
            width: 50px; height: 50px;
            background: rgba(27,134,250,0.1);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: var(--primary);
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        .contact-info-item h6 {
            font-family: var(--font-head);
            font-weight: 700;
            margin-bottom: 2px;
        }
        .contact-info-item p { margin: 0; font-size: 0.9rem; }
        .contact-form-wrap {
            background: #fff;
            border-radius: 20px;
            padding: 44px;
            box-shadow: 0 20px 60px rgba(18,0,54,0.08);
            border: 1px solid var(--border);
        }
        .form-control {
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(27,134,250,0.12);
        }
        .form-label {
            font-family: var(--font-head);
            font-weight: 600;
            font-size: 0.88rem;
            color: var(--heading);
        }
        .btn-submit {
            background: var(--primary);
            color: #fff;
            font-family: var(--font-head);
            font-weight: 700;
            font-size: 1rem;
            padding: 14px;
            border-radius: 10px;
            border: none;
            width: 100%;
            transition: background .2s, transform .2s, box-shadow .2s;
        }
        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(27,134,250,0.3);
        }

        /* =====================================
           CTA BANNER
        ===================================== */
        .cta-banner {
            background: linear-gradient(135deg, #1b86fa 0%, #0a4fa3 100%);
            border-radius: 24px;
            padding: 70px 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin: 30px 0;
        }
        .cta-banner::before, .cta-banner::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.07);
        }
        .cta-banner::before { width: 350px; height: 350px; top: -120px; right: -80px; }
        .cta-banner::after  { width: 250px; height: 250px; bottom: -100px; left: -60px; }
        .cta-banner h2 {
            color: #fff;
            font-size: clamp(1.8rem, 4vw, 2.6rem);
            margin-bottom: 16px;
        }
        .cta-banner p { color: rgba(255,255,255,0.8); font-size: 1.05rem; margin-bottom: 32px; }
        .btn-cta-white {
            background: #fff;
            color: var(--primary);
            font-family: var(--font-head);
            font-weight: 700;
            font-size: 1rem;
            padding: 14px 36px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-block;
            transition: transform .2s, box-shadow .2s;
        }
        .btn-cta-white:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            color: var(--primary-dark);
        }

        /* =====================================
           FOOTER
        ===================================== */
        .footer {
            background: var(--heading);
            color: rgba(255,255,255,0.7);
            padding: 60px 0 30px;
        }
        .footer-brand {
            font-family: var(--font-head);
            font-weight: 800;
            font-size: 1.4rem;
            color: #fff;
            margin-bottom: 14px;
        }
        .footer-brand span { color: var(--primary); }
        .footer h6 {
            font-family: var(--font-head);
            font-weight: 700;
            color: #fff;
            margin-bottom: 16px;
        }
        .footer a {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
            font-size: 0.9rem;
            transition: color .2s;
        }
        .footer a:hover { color: #fff; }
        .footer-divider {
            border-color: rgba(255,255,255,0.1);
            margin: 40px 0 24px;
        }
        .social-links { display: flex; gap: 14px; margin-top: 16px; }
        .social-link {
            width: 38px; height: 38px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: rgba(255,255,255,0.7) !important;
            font-size: 1rem;
            transition: background .2s, color .2s;
        }
        .social-link:hover {
            background: var(--primary);
            color: #fff !important;
        }

        /* =====================================
           RESPONSIVE TWEAKS
        ===================================== */
        @media (max-width: 768px) {
            .hero-section { margin: 0 10px; padding: 60px 0 40px; }
            .feature-section { padding: 60px 0; }
            .contact-form-wrap { padding: 28px 20px; }
            .cta-banner { padding: 50px 24px; }
            .stats-section .stat-divider { display: none; }
        }

        /* Smooth scroll */
        html { scroll-behavior: smooth; }

        /* Grid pattern overlay */
        .grid-bg {
            background-image: radial-gradient(circle, #d8e6ff 1px, transparent 1px);
            background-size: 28px 28px;
        }
    </style>
</head>
<body>

<!-- =====================================
     NAVBAR
===================================== -->
<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="#">
            <span>&#9678;</span> REDIL <span>Cloud</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <i class="bi bi-list fs-2" style="color:var(--heading)"></i>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="#inicio">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="#beneficios">Beneficios</a></li>
                <li class="nav-item"><a class="nav-link" href="#modulos">Módulos</a></li>
                <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <a href="#contacto" style="color:var(--nav-text); font-family:var(--font-head); font-weight:600; text-decoration:none; font-size:.95rem;">Iniciar Sesión</a>
                <a href="#contacto" class="btn-nav-cta">Solicitar Demo</a>
            </div>
        </div>
    </div>
</nav>

<!-- =====================================
     HERO SECTION
===================================== -->
<section id="inicio" class="py-4">
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="hero-badge">
                        <span class="dot"></span>
                        Presencia en 16 países · Software activo
                    </div>
                    <h1 class="hero-title">
                        El software más inteligente para tu <span style="color:var(--primary)">iglesia</span>
                    </h1>
                    <p class="hero-subtitle">
                        Administra tu congregación en tiempo real desde cualquier dispositivo. Pastoreo inteligente para iglesias de todos los tamaños.
                    </p>
                    <div class="d-flex align-items-center gap-4 flex-wrap">
                        <a href="#contacto" class="btn-primary-main">Solicitar Demo gratis</a>
                        <a href="#modulos" class="btn-secondary-main">
                            <span class="play-btn"><i class="bi bi-play-fill"></i></span>
                            Ver módulos
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 d-flex justify-content-center hero-illustration">
                    <!-- Mockup visual -->
                    <div style="position:relative; width:100%; max-width:480px;">
                        <div class="grid-bg" style="border-radius:24px; padding:30px; background-color:#f0f5ff;">
                            <!-- Mock dashboard card -->
                            <div style="background:#fff; border-radius:16px; padding:20px; box-shadow:0 8px 32px rgba(27,134,250,0.12); margin-bottom:16px;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span style="font-family:var(--font-head); font-weight:700; color:var(--heading); font-size:.95rem;">Mi Congregación</span>
                                    <span style="background:rgba(18,196,110,0.12); color:var(--success); font-size:.75rem; font-weight:700; padding:4px 10px; border-radius:100px;">+12% este mes</span>
                                </div>
                                <div class="row text-center g-2">
                                    <div class="col-4">
                                        <div style="font-family:var(--font-head); font-weight:800; font-size:1.7rem; color:var(--heading);">245</div>
                                        <div style="font-size:.75rem; color:var(--body-text);">Miembros</div>
                                    </div>
                                    <div class="col-4">
                                        <div style="font-family:var(--font-head); font-weight:800; font-size:1.7rem; color:var(--heading);">18</div>
                                        <div style="font-size:.75rem; color:var(--body-text);">Grupos</div>
                                    </div>
                                    <div class="col-4">
                                        <div style="font-family:var(--font-head); font-weight:800; font-size:1.7rem; color:var(--heading);">92%</div>
                                        <div style="font-size:.75rem; color:var(--body-text);">Asistencia</div>
                                    </div>
                                </div>
                            </div>
                            <!-- Mini chips -->
                            <div class="d-flex gap-2 flex-wrap">
                                <div style="background:#fff; border-radius:12px; padding:12px 18px; box-shadow:0 4px 16px rgba(0,0,0,0.07); flex:1; min-width:120px;">
                                    <div style="font-size:.75rem; color:var(--body-text); margin-bottom:4px;">Diezmos</div>
                                    <div style="font-family:var(--font-head); font-weight:800; color:var(--heading);">$14,580</div>
                                </div>
                                <div style="background:var(--primary); border-radius:12px; padding:12px 18px; flex:1; min-width:120px;">
                                    <div style="font-size:.75rem; color:rgba(255,255,255,.75); margin-bottom:4px;">Nuevos</div>
                                    <div style="font-family:var(--font-head); font-weight:800; color:#fff;">+7 hoy</div>
                                </div>
                            </div>
                        </div>
                        <!-- Floating chip -->
                        <div style="position:absolute; bottom:-16px; right:-16px; background:#fff; border-radius:14px; padding:14px 20px; box-shadow:0 8px 28px rgba(0,0,0,0.12); display:flex; align-items:center; gap:10px;">
                            <div style="width:36px;height:36px;background:rgba(27,134,250,0.12);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--primary);">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div>
                                <div style="font-size:.7rem;color:var(--body-text);">Líderes activos</div>
                                <div style="font-family:var(--font-head);font-weight:700;color:var(--heading);font-size:.9rem;">32 en línea</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================
     TRUSTED BY
===================================== -->
<section class="trust-section">
    <div class="container text-center">
        <p class="trust-label">Con presencia en más de 16 países</p>
        <div class="trust-logos">
            <span class="trust-logo">Colombia</span>
            <span class="trust-logo">México</span>
            <span class="trust-logo">Venezuela</span>
            <span class="trust-logo">Ecuador</span>
            <span class="trust-logo">Perú</span>
            <span class="trust-logo">España</span>
            <span class="trust-logo">EE.UU.</span>
        </div>
    </div>
</section>

<!-- =====================================
     FEATURE 1 — Información Valiosa
===================================== -->
<section class="feature-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="section-label">Información Valiosa</span>
                <h2 class="section-title">Conoce el estado real de tu congregación</h2>
                <p class="section-sub mb-4">Accede a datos de asistencia, crecimiento e ingresos financieros en tiempo real, desde cualquier dispositivo y en cualquier momento.</p>
                <div class="feature-check">
                    <i class="bi bi-check-circle-fill"></i>
                    <p>Métricas de asistencia por grupo y celda actualizadas al instante.</p>
                </div>
                <div class="feature-check">
                    <i class="bi bi-check-circle-fill"></i>
                    <p>Reportes de crecimiento y consolidación en un solo panel.</p>
                </div>
                <div class="feature-check">
                    <i class="bi bi-check-circle-fill"></i>
                    <p>Control financiero de diezmos, ofrendas y donaciones.</p>
                </div>
                <a href="#contacto" class="btn-primary-main mt-3" style="font-size:.9rem; padding:12px 24px;">Explorar funcionalidades</a>
            </div>
            <div class="col-lg-6 d-flex justify-content-center">
                <div style="position:relative; width:100%; max-width:460px;">
                    <div class="grid-bg" style="border-radius:20px; padding:24px; background:#f8faff;">
                        <div class="feature-card-mockup">
                            <div style="font-family:var(--font-head);font-weight:700;color:var(--heading);margin-bottom:16px;">Panel de Asistencia</div>
                            <!-- Fake bar chart -->
                            <div class="d-flex align-items-end gap-2" style="height:100px; margin-bottom:8px;">
                                @foreach([60,80,50,90,70,85,95] as $h)
                                <div style="flex:1; height:{{$h}}%; background:{{ $h > 80 ? '#1b86fa' : 'rgba(27,134,250,0.2)' }}; border-radius:6px 6px 0 0; transition:all .3s;"></div>
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-between" style="font-size:.7rem; color:var(--body-text);">
                                <span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span><span>Dom</span>
                            </div>
                        </div>
                        <!-- Floating stat -->
                        <div style="position:absolute;top:-16px;right:10px;background:#fff;border-radius:12px;padding:12px 18px;box-shadow:0 8px 24px rgba(0,0,0,0.10);">
                            <div style="font-size:.7rem;color:var(--body-text);">Asistencia Dom.</div>
                            <div style="font-family:var(--font-head);font-weight:800;font-size:1.3rem;color:var(--heading);">95% <span style="font-size:.75rem;color:var(--success);">↑</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================
     FEATURE 2 — Trabajo en Equipo (reversed)
===================================== -->
<section class="feature-section bg-alt">
    <div class="container">
        <div class="row align-items-center g-5 flex-lg-row-reverse">
            <div class="col-lg-6">
                <span class="section-label">Trabajo en Equipo</span>
                <h2 class="section-title">Cada líder, su propia cuenta y cobertura</h2>
                <p class="section-sub mb-4">REDIL no asigna una sola persona para gestionar todo. Cada líder tiene su propio acceso, limitado a su área de responsabilidad.</p>
                <div class="mini-feature">
                    <div class="mini-feature-icon"><i class="bi bi-person-badge-fill"></i></div>
                    <div>
                        <h6>Roles Personalizados</h6>
                        <p>Define permisos por área: pastores, diáconos, tesoreros y más.</p>
                    </div>
                </div>
                <div class="mini-feature">
                    <div class="mini-feature-icon"><i class="bi bi-shield-lock-fill"></i></div>
                    <div>
                        <h6>Información Segura</h6>
                        <p>Cada usuario solo ve la información correspondiente a su rol.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-flex justify-content-center">
                <div style="position:relative; width:100%; max-width:420px;">
                    <!-- Team cards -->
                    <div style="background:#fff; border-radius:18px; padding:24px; box-shadow:0 16px 48px rgba(27,134,250,0.10); border:1px solid var(--border);">
                        <div style="font-family:var(--font-head);font-weight:700;color:var(--heading);margin-bottom:18px;">Equipo de Líderes</div>
                        @foreach([['Pastor Principal','#1b86fa','bi-person-fill'],['Líder de Jóvenes','#7c4dff','bi-people-fill'],['Tesorería','#12c46e','bi-cash-coin'],['Consolidación','#f59e0b','bi-heart-fill']] as $member)
                        <div class="d-flex align-items-center gap-3 mb-3 p-2" style="border-radius:10px; background:var(--bg-light);">
                            <div style="width:38px;height:38px;background:{{$member[1]}};border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;">
                                <i class="bi {{$member[2]}}"></i>
                            </div>
                            <div>
                                <div style="font-family:var(--font-head);font-weight:600;font-size:.88rem;color:var(--heading);">{{$member[0]}}</div>
                                <div style="font-size:.75rem;color:var(--body-text);">Acceso activo</div>
                            </div>
                            <div class="ms-auto">
                                <span style="width:8px;height:8px;background:var(--success);border-radius:50%;display:inline-block;"></span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================
     STATISTICS SECTION
===================================== -->
<section class="stats-section">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="stat-item">
                    <div class="stat-number"><span class="arrow">↑</span> 16</div>
                    <h6>Países con Presencia</h6>
                    <p style="font-size:.9rem;">Implementado en congregaciones de Colombia, México, España y muchos más.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item" style="border-left:1px solid var(--border); border-right:1px solid var(--border);">
                    <div class="stat-number"><span class="arrow">↑</span> +500</div>
                    <h6>Iglesias Activas</h6>
                    <p style="font-size:.9rem;">Cientos de comunidades de fe confían en REDIL para gestionar su ministerio.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <div class="stat-number"><span class="arrow">↑</span> 99%</div>
                    <h6>Satisfacción de Usuarios</h6>
                    <p style="font-size:.9rem;">Soporte continuo y actualizaciones constantes garantizan la mejor experiencia.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================
     BENEFITS GRID
===================================== -->
<section id="modulos" class="benefits-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Módulos del Sistema</span>
            <h2 class="section-title mx-auto" style="max-width:560px;">Todo lo que necesita tu iglesia en un solo lugar</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="bi bi-people-fill"></i></div>
                    <h5>Gestión de Miembros</h5>
                    <p style="font-size:.9rem;">Registro completo de datos personales, seguimiento espiritual y estado de cada miembro de la congregación.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="bi bi-calendar-check-fill"></i></div>
                    <h5>Asistencia & Eventos</h5>
                    <p style="font-size:.9rem;">Control de asistencia en cultos, grupos celulares y eventos especiales con reportes automatizados.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="bi bi-cash-coin"></i></div>
                    <h5>Finanzas & Tesorería</h5>
                    <p style="font-size:.9rem;">Gestiona diezmos, ofrendas, proyectos y genera reportes financieros con transparencia total.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="bi bi-diagram-3-fill"></i></div>
                    <h5>Grupos & Células</h5>
                    <p style="font-size:.9rem;">Organiza grupos de ministerio, redes y células con mapas digitales y estructura visual de la congregación.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="bi bi-graph-up-arrow"></i></div>
                    <h5>Consolidación</h5>
                    <p style="font-size:.9rem;">Proceso estructurado para el seguimiento y consolidación de nuevos creyentes hasta su madurez en la fe.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="bi bi-mortarboard-fill"></i></div>
                    <h5>Escuela & LMS</h5>
                    <p style="font-size:.9rem;">Plataforma de formación integrada con cursos, evaluaciones y seguimiento del progreso académico espiritual.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================
     CTA BANNER
===================================== -->
<section class="py-5">
    <div class="container">
        <div class="cta-banner">
            <h2>¿Listo para llevar tu iglesia al siguiente nivel?</h2>
            <p>Únete a cientos de iglesias que ya confían en REDIL Cloud para su administración.</p>
            <a href="#contacto" class="btn-cta-white">Comienza gratis hoy</a>
        </div>
    </div>
</section>

<!-- =====================================
     CONTACT SECTION
===================================== -->
<section id="contacto" class="contact-section">
    <div class="container">
        <div class="row g-5 align-items-start">
            <div class="col-lg-5">
                <span class="section-label">Contáctanos</span>
                <h2 class="section-title">Nos encantaría saber de tu iglesia</h2>
                <p class="mb-40" style="margin-bottom:36px;">Cuéntanos sobre tu congregación y te ayudaremos a encontrar el plan ideal. Respuesta en menos de 24 horas.</p>

                <div class="contact-info-item">
                    <div class="icon-wrap"><i class="bi bi-geo-alt-fill"></i></div>
                    <div>
                        <h6>Dirección</h6>
                        <p>Calle 25A # 3 – 06 Tuluá, Colombia</p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="icon-wrap"><i class="bi bi-envelope-fill"></i></div>
                    <div>
                        <h6>Correo Electrónico</h6>
                        <p>info@redil.co</p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="icon-wrap"><i class="bi bi-whatsapp"></i></div>
                    <div>
                        <h6>WhatsApp</h6>
                        <p>+(57) 318 712 7025</p>
                    </div>
                </div>

                <div class="social-links mt-3">
                    <a href="https://www.facebook.com/SoftwareRedil/" target="_blank" class="social-link"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.instagram.com/software.redil/" target="_blank" class="social-link"><i class="bi bi-instagram"></i></a>
                    <a href="https://www.youtube.com/channel/UCY1VhTUnhyhd_UywEKo2udg" target="_blank" class="social-link"><i class="bi bi-youtube"></i></a>
                    <a href="https://twitter.com/redil_software" target="_blank" class="social-link"><i class="bi bi-twitter"></i></a>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="contact-form-wrap">
                    <h4 style="font-family:var(--font-head); font-weight:800; color:var(--heading); margin-bottom:28px;">Envíanos un mensaje</h4>
                    <form id="contactForm" onsubmit="handleSubmit(event)">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre de la Iglesia</label>
                                <input type="text" class="form-control" id="church" placeholder="Ej. Iglesia Central" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombre del Pastor / Líder</label>
                                <input type="text" class="form-control" id="name" placeholder="Tu nombre completo" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" placeholder="correo@iglesia.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">WhatsApp / Teléfono</label>
                                <input type="tel" class="form-control" id="phone" placeholder="+57 300 000 0000" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">País</label>
                                <select class="form-control" id="country">
                                    <option value="">Selecciona tu país...</option>
                                    <option>Colombia</option>
                                    <option>México</option>
                                    <option>Venezuela</option>
                                    <option>Ecuador</option>
                                    <option>Perú</option>
                                    <option>Argentina</option>
                                    <option>España</option>
                                    <option>Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tamaño de la Congregación</label>
                                <select class="form-control" id="size">
                                    <option value="">Número de miembros...</option>
                                    <option>1 – 50 miembros</option>
                                    <option>51 – 200 miembros</option>
                                    <option>201 – 500 miembros</option>
                                    <option>Más de 500 miembros</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Mensaje</label>
                                <textarea class="form-control" id="message" rows="4" placeholder="Cuéntanos sobre tu congregación y en qué podemos ayudarte..."></textarea>
                            </div>
                            <div class="col-12 mt-2">
                                <button type="submit" class="btn-submit" id="submitBtn">
                                    Enviar Solicitud &nbsp;<i class="bi bi-arrow-right"></i>
                                </button>
                                <div id="successMsg" style="display:none; margin-top:16px; background:rgba(18,196,110,0.1); border:1px solid var(--success); border-radius:10px; padding:14px; color:#0a7d45; font-weight:600; text-align:center;">
                                    <i class="bi bi-check-circle-fill me-2"></i> ¡Mensaje enviado! Pronto nos comunicaremos contigo.
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================
     FOOTER
===================================== -->
<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand">&#9678; REDIL <span>Cloud</span></div>
                <p style="font-size:.9rem; max-width:280px; line-height:1.7;">Software Redil – Pastoreo Inteligente. Tecnología en la nube para tu congregación.</p>
            </div>
            <div class="col-lg-2 col-md-6">
                <h6>Producto</h6>
                <a href="#modulos">Módulos</a>
                <a href="#beneficios">Beneficios</a>
                <a href="#contacto">Precios</a>
                <a href="#contacto">Soporte</a>
            </div>
            <div class="col-lg-2 col-md-6">
                <h6>Empresa</h6>
                <a href="#">Acerca de</a>
                <a href="#">Noticias</a>
                <a href="#">Alianzas</a>
                <a href="#contacto">Contacto</a>
            </div>
            <div class="col-lg-4 col-md-6">
                <h6>Síguenos</h6>
                <p style="font-size:.9rem;">Conoce todo lo que tenemos para ti en nuestras redes sociales.</p>
                <div class="social-links">
                    <a href="https://www.facebook.com/SoftwareRedil/" target="_blank" class="social-link"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.instagram.com/software.redil/" target="_blank" class="social-link"><i class="bi bi-instagram"></i></a>
                    <a href="https://co.pinterest.com/SofwareRedil/" target="_blank" class="social-link"><i class="bi bi-pinterest"></i></a>
                    <a href="https://www.youtube.com/channel/UCY1VhTUnhyhd_UywEKo2udg" target="_blank" class="social-link"><i class="bi bi-youtube"></i></a>
                    <a href="https://twitter.com/redil_software" target="_blank" class="social-link"><i class="bi bi-twitter-x"></i></a>
                </div>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <p style="margin:0; font-size:.85rem;">&copy; <span id="year"></span> REDIL Cloud. Todos los derechos reservados.</p>
            <p style="margin:0; font-size:.85rem;">Hecho con <i class="bi bi-heart-fill" style="color:var(--primary)"></i> para la iglesia</p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Year
    document.getElementById('year').textContent = new Date().getFullYear();

    // Form submit
    function handleSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('submitBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Enviando...';
        btn.disabled = true;
        setTimeout(() => {
            btn.style.display = 'none';
            document.getElementById('successMsg').style.display = 'block';
        }, 1200);
    }

    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            const target = document.querySelector(a.getAttribute('href'));
            if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
        });
    });

    // Animate stats on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) e.target.classList.add('visible');
        });
    }, { threshold: 0.2 });
    document.querySelectorAll('.stat-item').forEach(el => observer.observe(el));
</script>
</body>
</html>
