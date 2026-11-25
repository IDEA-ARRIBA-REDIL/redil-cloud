<!DOCTYPE html>
<html lang="es">
<head>
    @php
        $whatsappNumero = preg_replace('/\D+/', '', (string) config('services.landing.whatsapp_number', '573000000000')) ?: '573000000000';
        $whatsappMensaje = (string) config('services.landing.whatsapp_default_message', 'Hola, quiero agendar una videollamada para conocer todas las ventajas de REDIL Cloud para mi iglesia.');
        $whatsappUrl = 'https://wa.me/' . $whatsappNumero . '?text=' . rawurlencode($whatsappMensaje);
        $logoUrl = 'https://inicial.redil.cloud/marca/img/branding/logo-redil.png';
        $logoBlanco = Storage::disk('global_media')->url('logo_principal.png');
        $faviconUrl = Storage::disk('global_media')->url('favicon-redil.ico');
        $imgHero = Storage::disk('global_media')->url('landing/imagen-4.png');
        $heroSlides = [
            ['image' => $imgHero, 'title' => 'Tu iglesia, en un solo lugar', 'description' => 'Una visión conectada de miembros, grupos, escuelas y finanzas.'],
            ['image' => Storage::disk('global_media')->url('landing/modulo-miembros.webp'), 'title' => 'Miembros y grupos', 'description' => 'Conoce a cada persona y acompaña a tus líderes y comunidades.'],
            ['image' => Storage::disk('global_media')->url('landing/modulo-escuelas.webp'), 'title' => 'Escuelas y Campus LMS', 'description' => 'Organiza cursos, recursos, notas y el progreso de tus estudiantes.'],
            ['image' => Storage::disk('global_media')->url('landing/modulo-finanzas.webp'), 'title' => 'Finanzas y eventos', 'description' => 'Conecta ofrendas, cajas, inscripciones y reportes para tu equipo.'],
            ['image' => Storage::disk('global_media')->url('landing/modulo-infantil.webp'), 'title' => 'Iglesia Infantil QR', 'description' => 'Acompaña la recepción y entrega de los niños con verificación QR.'],
        ];
        $imgPaises = Storage::disk('global_media')->url('landing/imagen-2.png');
        $imgProblemas = Storage::disk('global_media')->url('landing/imagen-3.png');
        $imgEquipo = Storage::disk('global_media')->url('landing/imagen-5.png');
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REDIL Cloud — Software Inteligente para Iglesias y Ministerios</title>
    <meta name="description" content="Plataforma todo-en-uno para iglesias: gestión de miembros, grupos, iglesia infantil con QR, escuelas y campus LMS, finanzas y crecimiento espiritual en tiempo real.">
    <link rel="icon" href="{{ $faviconUrl }}" type="image/x-icon">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Google Fonts: Lexend Deca -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @livewireStyles

    <style>
        :root {
            --teal-bright: #00E4BB;
            --teal-accent: #00B596;
            --teal-deep: #008A73;
            --ink-950: #071117;
            --ink-900: #0D1B24;
            --ink-800: #162834;
            --ink-600: #4B5C6B;
            --ink-500: #64748B;
            --surface-soft: #F3F8FB;
            --surface-card: #FFFFFF;
            --border-subtle: rgba(13, 27, 36, 0.08);
            --whatsapp: #25D366;
            --whatsapp-dark: #1DA851;
            --header-height: 82px;
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: calc(var(--header-height) + 14px);
        }

        body {
            font-family: "Lexend Deca", system-ui, -apple-system, sans-serif;
            color: var(--ink-600);
            background-color: #FFFFFF;
            -webkit-font-smoothing: antialiased;
            overflow-x: clip;
        }

        h1, h2, h3, h4, h5, h6 {
            color: var(--ink-900);
            letter-spacing: -0.025em;
        }

        /* Skip link para accesibilidad de teclado */
        .skip-link {
            position: fixed;
            inset-block-start: -100px;
            inset-inline-start: 1rem;
            z-index: 1100;
            background: var(--ink-900);
            color: #FFFFFF;
            padding: 0.75rem 1.25rem;
            border-radius: 999px;
            font-weight: 600;
            text-decoration: none;
        }
        .skip-link:focus-visible {
            inset-block-start: 1rem;
            outline: 3px solid var(--teal-bright);
            outline-offset: 2px;
        }

        /* Indicador de progreso de scroll */
        #scroll-progress {
            position: fixed;
            inset-block-start: 0;
            inset-inline-start: 0;
            width: 100%;
            height: 4px;
            z-index: 1060;
            background: linear-gradient(90deg, var(--teal-accent), var(--teal-bright));
            box-shadow: 0 0 12px rgba(0, 228, 187, 0.75);
            transform-origin: 0 50%;
            transform: scaleX(0);
            pointer-events: none;
        }

        @media (prefers-reduced-motion: no-preference) {
            @supports (animation-timeline: scroll()) {
                @keyframes grow-progress {
                    from { transform: scaleX(0); }
                    to { transform: scaleX(1); }
                }
                #scroll-progress {
                    animation: grow-progress auto linear both;
                    animation-timeline: scroll(root block);
                }
            }
        }

        /* Navbar sticky con efecto shrinking/glass en scroll */
        .landing-navbar {
            position: sticky;
            top: 0;
            z-index: 1040;
            background-color: rgba(243, 248, 251, 0.88);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid transparent;
            padding-block: 1rem;
            transition: background-color 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease, padding 0.25s ease;
        }

        .landing-navbar.is-scrolled {
            background-color: rgba(255, 255, 255, 0.97);
            border-bottom-color: var(--border-subtle);
            box-shadow: 0 12px 32px rgba(7, 17, 23, 0.1);
            padding-block: 0.6rem;
        }

        @media (prefers-reduced-motion: no-preference) {
            @supports ((animation-timeline: scroll()) and (animation-range: 0% 100%)) {
                @keyframes navbar-condense {
                    from {
                        background-color: rgba(243, 248, 251, 0.82);
                        border-bottom-color: transparent;
                        box-shadow: none;
                        padding-block: 1rem;
                    }
                    to {
                        background-color: rgba(255, 255, 255, 0.97);
                        border-bottom-color: rgba(13, 27, 36, 0.1);
                        box-shadow: 0 12px 32px rgba(7, 17, 23, 0.1);
                        padding-block: 0.6rem;
                    }
                }
                .landing-navbar {
                    animation: navbar-condense auto linear both;
                    animation-timeline: scroll(block root);
                    animation-range: 0px 140px;
                }
            }
        }

        .landing-nav-link {
            color: var(--ink-800);
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.5rem 0.85rem !important;
            border-radius: 999px;
            transition: color 0.2s ease, background-color 0.2s ease;
        }
        .landing-nav-link:hover,
        .landing-nav-link:focus-visible {
            color: var(--teal-deep);
            background-color: rgba(0, 228, 187, 0.14);
        }

        /* Botones principales y accesibilidad */
        .landing-btn-primary {
            background-color: var(--teal-bright);
            border: 2px solid var(--teal-bright);
            color: var(--ink-950);
            min-height: 48px;
            transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 10px 24px rgba(0, 181, 150, 0.22);
        }
        .landing-btn-primary:hover,
        .landing-btn-primary:focus-visible {
            background-color: #00cfa9;
            border-color: #00cfa9;
            color: var(--ink-950);
            box-shadow: 0 14px 28px rgba(0, 181, 150, 0.3);
        }

        .landing-btn-dark {
            background-color: var(--ink-900);
            border: 2px solid var(--ink-900);
            color: #FFFFFF;
            min-height: 48px;
            transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .landing-btn-dark:hover,
        .landing-btn-dark:focus-visible {
            background-color: var(--ink-800);
            border-color: var(--ink-800);
            color: #FFFFFF;
        }

        .landing-btn-whatsapp {
            background-color: var(--whatsapp);
            border: 2px solid var(--whatsapp);
            color: #062814;
            min-height: 48px;
            transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 10px 24px rgba(37, 211, 102, 0.24);
        }
        .landing-btn-whatsapp:hover,
        .landing-btn-whatsapp:focus-visible {
            background-color: var(--whatsapp-dark);
            border-color: var(--whatsapp-dark);
            color: #FFFFFF;
        }

        .landing-btn-whatsapp-outline {
            background-color: #FFFFFF;
            border: 2px solid rgba(29, 168, 81, 0.35);
            color: #116B34;
            min-height: 48px;
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }
        .landing-btn-whatsapp-outline:hover,
        .landing-btn-whatsapp-outline:focus-visible {
            background-color: rgba(37, 211, 102, 0.12);
            border-color: var(--whatsapp-dark);
            color: #0B4D25;
        }

        a:focus-visible,
        button:focus-visible,
        input:focus-visible,
        select:focus-visible,
        textarea:focus-visible {
            outline: 3px solid var(--teal-accent);
            outline-offset: 2px;
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            background:
                radial-gradient(circle at 88% 18%, rgba(0, 228, 187, 0.22), transparent 44%),
                radial-gradient(circle at 12% 85%, rgba(0, 181, 150, 0.12), transparent 42%),
                linear-gradient(180deg, #F3F8FB 0%, #FFFFFF 100%);
            padding-block: 3.5rem 5rem;
            overflow: hidden;
        }

        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 1rem;
            border-radius: 999px;
            background-color: rgba(0, 228, 187, 0.16);
            color: var(--teal-deep);
            font-weight: 700;
            font-size: 0.82rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .hero-title {
            font-size: clamp(2.4rem, 4.8vw, 4.1rem);
            line-height: 1.07;
            font-weight: 900;
            color: var(--ink-950);
        }

        .text-gradient-teal {
            background: linear-gradient(135deg, var(--teal-deep) 0%, var(--teal-accent) 55%, #00C9A4 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-visual-stage {
            position: relative;
            padding: 1rem;
        }

        .hero-visual-card {
            position: relative;
            background: linear-gradient(155deg, #0B1922 0%, #112735 100%);
            border-radius: 1.75rem;
            padding: 1.25rem;
            box-shadow: 0 28px 65px rgba(7, 17, 23, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .hero-visual-card img {
            border-radius: 1.1rem;
            background-color: #F7FAFC;
        }

        .hero-float-chip {
            position: absolute;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(13, 27, 36, 0.08);
            border-radius: 1rem;
            padding: 0.7rem 1rem;
            box-shadow: 0 16px 36px rgba(7, 17, 23, 0.14);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 3;
        }

        .hero-float-chip.chip-top {
            inset-block-start: -0.75rem;
            inset-inline-end: 0.5rem;
        }

        .hero-float-chip.chip-bottom {
            inset-block-end: -0.75rem;
            inset-inline-start: 0.5rem;
        }

        .chip-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(0, 228, 187, 0.18);
            color: var(--teal-deep);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        /* Sección de métricas */
        .stats-band {
            background-color: var(--ink-950);
            color: #FFFFFF;
            padding-block: 3.75rem;
            position: relative;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 1.35rem;
            padding: 1.75rem 1.5rem;
            height: 100%;
        }

        .stat-number {
            font-size: clamp(2.1rem, 3.6vw, 3rem);
            font-weight: 900;
            color: var(--teal-bright);
            line-height: 1.05;
        }

        /* Títulos de sección */
        .section-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: var(--teal-deep);
            font-weight: 800;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.75rem;
        }

        .section-heading {
            font-size: clamp(2rem, 3.6vw, 3rem);
            font-weight: 900;
            line-height: 1.12;
        }

        /* Tabs interactivas de los 5 pilares (siguiendo modern-web-guidance: tabs) */
        .pillars-section {
            background-color: #FFFFFF;
            padding-block: 5.5rem;
        }

        .pillar-tablist-wrap {
            position: relative;
            margin-bottom: 2.25rem;
        }

        .pillar-tablist {
            display: flex;
            flex-wrap: nowrap;
            gap: 0.5rem;
            overflow-x: auto;
            scroll-snap-type: inline proximity;
            scrollbar-width: none;
            padding: 0.35rem;
            background-color: var(--surface-soft);
            border-radius: 1.15rem;
            border: 1px solid var(--border-subtle);
        }

        .pillar-tablist::-webkit-scrollbar {
            display: none;
        }

        .pillar-tab {
            position: relative;
            flex: none;
            scroll-snap-align: start;
            border: 0;
            background: transparent;
            color: var(--ink-600);
            font-weight: 600;
            font-size: 0.89rem;
            padding: 0.78rem 1rem;
            border-radius: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            white-space: nowrap;
            cursor: pointer;
            transition: color 0.2s ease, background-color 0.2s ease;
        }

        .pillar-tab:hover {
            color: var(--ink-950);
            background-color: rgba(255, 255, 255, 0.7);
        }

        .pillar-tab[aria-selected="true"] {
            background-color: #FFFFFF;
            color: var(--ink-950);
            box-shadow: 0 6px 18px rgba(7, 17, 23, 0.07);
        }

        /* Indicador no dependiente solo de color (WCAG 1.4.1) sin alterar dimensiones */
        .pillar-tab[aria-selected="true"]::after {
            content: "";
            position: absolute;
            inset: auto 1rem 0.25rem;
            border-block-end: 3px solid var(--teal-accent);
            border-radius: 999px;
        }

        /* El elemento role="tabpanel" NO lleva padding/border/background para compatibilidad con hidden="until-found" */
        .pillar-panel-card {
            background: linear-gradient(180deg, #F8FBFD 0%, #FFFFFF 100%);
            border: 1px solid var(--border-subtle);
            border-radius: 1.75rem;
            padding: clamp(1.5rem, 3vw, 2.75rem);
            box-shadow: 0 18px 45px rgba(7, 17, 23, 0.05);
        }

        .pillar-feature-item {
            background: #FFFFFF;
            border: 1px solid var(--border-subtle);
            border-radius: 1rem;
            padding: 1rem 1.1rem;
            height: 100%;
        }

        /* Mockups interactivos de UI SaaS */
        .ui-mockup-frame {
            background: var(--ink-950);
            color: #E2E8F0;
            border-radius: 1.4rem;
            padding: 1.25rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 22px 44px rgba(7, 17, 23, 0.16);
        }

        .ui-mockup-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 0.9rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 0.8rem;
            color: #94A3B8;
        }

        .ui-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            display: inline-block;
        }

        .ui-kpi-tile {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 0.9rem;
            padding: 0.9rem;
        }

        .ui-badge-live {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--teal-bright);
            background: rgba(0, 228, 187, 0.12);
            padding: 0.25rem 0.65rem;
            border-radius: 999px;
        }

        /* Antes vs Con REDIL */
        .comparison-section {
            background-color: var(--surface-soft);
            padding-block: 5.5rem;
        }

        .comparison-card {
            background: #FFFFFF;
            border-radius: 1.5rem;
            padding: 2rem;
            border: 1px solid var(--border-subtle);
            height: 100%;
        }

        .comparison-card.is-redil {
            background: linear-gradient(160deg, var(--ink-950) 0%, #0E2530 100%);
            color: #E2E8F0;
            border-color: rgba(0, 228, 187, 0.25);
        }

        /* Equipo de líderes con recorte real de imagen-5.png */
        .team-section {
            background-color: #FFFFFF;
            padding-block: 5.5rem;
        }

        .team-img-container {
            width: 100%;
            aspect-ratio: 4 / 3;
            position: relative;
            overflow: hidden;
            background-color: #E6F2F6;
        }

        .team-img-sprite {
            position: absolute;
            width: 205%;
            height: 205%;
            max-width: none;
            object-fit: cover;
        }
        .team-sprite-1 { top: -2%; left: -2%; }
        .team-sprite-2 { top: -2%; left: -102%; }
        .team-sprite-3 { top: -102%; left: -2%; }
        .team-sprite-4 { top: -102%; left: -102%; }

        .role-card-btn {
            width: 100%;
            text-align: left;
            padding: 0;
            background: #FFFFFF;
            border: 2px solid var(--border-subtle);
            border-radius: 1.15rem;
            overflow: hidden;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }
        .role-card-btn.is-active,
        .role-card-btn:hover {
            border-color: var(--teal-accent);
            box-shadow: 0 14px 28px rgba(0, 181, 150, 0.14);
        }

        /* Presencia global */
        .presence-section {
            background-color: var(--surface-soft);
            padding-block: 5.5rem;
        }

        /* Banner Aliado */
        .ally-banner {
            background: linear-gradient(135deg, #00B596 0%, #00E4BB 100%);
            padding-block: 4.5rem;
        }

        /* FAQ */
        .faq-section {
            background-color: #FFFFFF;
            padding-block: 5rem;
        }
        .faq-item {
            border: 1px solid var(--border-subtle);
            border-radius: 1.15rem !important;
            overflow: hidden;
            margin-bottom: 0.85rem;
            background: #FFFFFF;
        }
        .faq-item .accordion-button {
            font-weight: 700;
            color: var(--ink-900);
            background-color: #FFFFFF;
            padding: 1.2rem 1.35rem;
            box-shadow: none;
        }
        .faq-item .accordion-button:not(.collapsed) {
            color: var(--teal-deep);
            background-color: rgba(0, 228, 187, 0.08);
        }

        /* Contacto & Conversión */
        .contact-section {
            background:
                radial-gradient(circle at 15% 20%, rgba(0, 228, 187, 0.16), transparent 45%),
                linear-gradient(160deg, var(--ink-950) 0%, #0E232E 100%);
            padding-block: 5.5rem;
            color: #FFFFFF;
        }

        .landing-contact-card {
            background-color: #FFFFFF;
            color: var(--ink-900);
            border-radius: 1.75rem;
            padding: clamp(1.5rem, 3vw, 2.5rem);
            box-shadow: 0 28px 60px rgba(0, 0, 0, 0.28);
        }

        .landing-form-pill {
            background-color: rgba(0, 228, 187, 0.16);
            color: var(--teal-deep);
            font-weight: 700;
            font-size: 0.78rem;
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
        }

        .landing-input {
            font: inherit;
            font-size: max(1rem, 1em);
            min-height: 48px;
            border-radius: 0.85rem;
            border: 1.5px solid #CBD5E1;
            color: var(--ink-900);
            background-color: #FFFFFF;
            padding-inline: 0.95rem;
        }
        .landing-input:focus {
            border-color: var(--teal-accent);
            box-shadow: 0 0 0 0.22rem rgba(0, 181, 150, 0.2);
        }
        .landing-input:user-invalid {
            border-color: #DC2626;
        }

        .landing-success-badge {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background-color: rgba(0, 228, 187, 0.2);
            color: var(--teal-deep);
            font-size: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Footer */
        .landing-footer {
            background-color: #040B0F;
            color: #94A3B8;
            padding-block: 4rem 2.5rem;
        }
        .landing-footer a {
            color: #94A3B8;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .landing-footer a:hover {
            color: var(--teal-bright);
        }

        /* Botón flotante WhatsApp */
        .whatsapp-fab {
            position: fixed;
            inset-block-end: 1.5rem;
            inset-inline-end: 1.5rem;
            z-index: 1050;
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            background-color: var(--whatsapp);
            color: #062814;
            font-weight: 800;
            font-size: 0.92rem;
            padding: 0.85rem 1.25rem;
            border-radius: 999px;
            text-decoration: none;
            box-shadow: 0 16px 34px rgba(37, 211, 102, 0.35);
            transition: transform 0.2s ease, background-color 0.2s ease, color 0.2s ease;
        }
        .whatsapp-fab:hover {
            background-color: var(--whatsapp-dark);
            color: #FFFFFF;
            transform: translateY(-2px);
        }

        /* Entradas progresivas: el contenido sigue visible sin JavaScript. */
        @keyframes landing-enter {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: none; }
        }

        .hero-section {
            padding-block: clamp(3rem, 6vw, 6rem) 4rem;
            background: radial-gradient(ellipse at 88% 38%, #CDF6EB 0%, transparent 48%), #F7FAF9;
            isolation: isolate;
        }
        .hero-section::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            background-image: radial-gradient(#008A7325 1px, transparent 1px);
            background-size: 24px 24px;
            mask-image: linear-gradient(90deg, transparent 40%, #000);
        }
        .hero-title {
            font-size: clamp(2.65rem, 4.7vw, 4rem);
            font-weight: 700;
            letter-spacing: -0.055em;
            line-height: 1.08;
            text-wrap: balance;
        }
        .hero-title .text-gradient-teal {
            display: block;
            margin-top: 0.12em;
            background: none;
            color: #007C67;
        }
        .hero-description { max-width: 33rem; font-size: 1.075rem; line-height: 1.8; }
        .hero-eyebrow { background: #E3F2EC; color: #006C59; font-size: 0.75rem; }
        .hero-actions .btn { font-size: 0.9rem; border-radius: 0.85rem !important; }
        .hero-proof { border-top: 1px solid #D8E5DF; max-width: 32rem; padding-top: 1.25rem !important; font-size: 0.78rem; }
        .hero-visual-stage { padding: 1.5rem 0.5rem; }
        .hero-visual-card { padding: 0.9rem; border-radius: 1.5rem; box-shadow: 0 30px 65px #07251E26, 0 0 0 8px #FFFFFF80; }
        .hero-visual-card img { aspect-ratio: 1 / 1; object-fit: cover; }
        .hero-float-chip { padding: 0.85rem 1rem; border-radius: 0.9rem; }
        .hero-float-chip.chip-top { inset-block-start: -0.75rem; inset-inline-end: -0.75rem; }
        .hero-float-chip.chip-bottom { inset-block-end: -0.75rem; inset-inline-start: -1rem; }
        .hero-explore { display: inline-flex; gap: 0.65rem; align-items: center; color: var(--ink-800); text-underline-offset: 5px; font-size: 0.9rem; }
        .hero-explore:hover { color: var(--teal-deep); }
        .stats-band { padding-block: 2.5rem; }
        .stat-card { border: 0; border-left: 1px solid #FFFFFF26; border-radius: 0; background: transparent; padding: 0.5rem 1.5rem; }
        .stat-number { font-weight: 600; letter-spacing: -0.05em; }
        .stat-card p { color: #A9BBC5 !important; line-height: 1.7; }
        .section-heading { font-weight: 700; letter-spacing: -0.045em; text-wrap: balance; }
        .section-kicker { font-size: 0.75rem; letter-spacing: 0.12em; }
        .pillar-tablist { gap: 0.3rem; padding: 0.5rem; }
        .pillar-tab { flex: 1; justify-content: center; font-size: 0.8rem; min-height: 52px; }
        .pillar-tab[aria-selected="true"] { background: var(--ink-900); color: #FFFFFF; }
        .pillar-tab[aria-selected="true"]::after { border-color: var(--teal-bright); }
        .pillar-panel-card { background: #F5F8F7; box-shadow: none; }
        .pillar-feature-item { border-color: #DBE6E1; transition: border-color 180ms ease, box-shadow 180ms ease; }
        .pillar-feature-item:hover { border-color: #92BDAE; box-shadow: 0 6px 20px #092B1F08; }
        .comparison-card.is-redil { box-shadow: 0 18px 40px #07111715; }
        .ally-banner { background: #D7F5E9; }
        .onboarding-step { height: 100%; padding: 1.75rem; background: #FFFFFFA6; border: 1px solid #B5DCCB; border-radius: 1rem; text-align: left; }
        .onboarding-step .chip-icon { margin-bottom: 1.1rem; background: #FFFFFF; }
        .onboarding-step h3 { font-size: 1.1rem; font-weight: 600; }
        .onboarding-step p { font-size: 0.9rem; line-height: 1.7; margin: 0; }
        .landing-nav-link[aria-current="location"] { color: #006C59; background: #DCF4EA; }
        [x-cloak] { display: none !important; }

        @media (prefers-reduced-motion: no-preference) {
            .hero-copy > * { animation: landing-enter 700ms cubic-bezier(0.22, 1, 0.36, 1) both; }
            .hero-copy > :nth-child(2) { animation-delay: 80ms; }
            .hero-copy > :nth-child(3) { animation-delay: 160ms; }
            .hero-copy > :nth-child(4) { animation-delay: 240ms; }
            .hero-copy > :nth-child(5) { animation-delay: 320ms; }
            .hero-visual-stage { animation: landing-enter 900ms 180ms cubic-bezier(0.22, 1, 0.36, 1) both; }
            .reveal-pending { opacity: 0; transform: translateY(24px); }
            .reveal-pending.reveal-left { transform: translateX(-24px); }
            .reveal-pending.reveal-right { transform: translateX(24px); }
            .reveal-pending.reveal-scale { transform: translateY(18px) scale(0.97); }
            .reveal-pending.is-visible, .reveal-pending:focus-within {
                opacity: 1;
                transform: none;
                transition: opacity 650ms ease, transform 650ms cubic-bezier(0.22, 1, 0.36, 1);
                transition-delay: var(--reveal-delay, 0ms);
            }
            .reveal-stagger-2 { --reveal-delay: 70ms; }
            .reveal-stagger-3 { --reveal-delay: 140ms; }
            .reveal-stagger-4 { --reveal-delay: 210ms; }
            [role="tabpanel"]:not([hidden]) .pillar-panel-card { animation: landing-enter 350ms ease both; }
            .landing-btn-primary:hover, .role-card-btn:hover { transform: translateY(-3px); }
            .role-card-btn { transition: transform 200ms ease, border-color 200ms ease, box-shadow 200ms ease; }
        }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }
        @media (max-width: 575.98px) {
            .hero-section { padding-block: 2rem 3rem; }
            .hero-eyebrow { font-size: 0.65rem; }
            .hero-proof { gap: 0.75rem !important; }
            .hero-visual-stage { margin-top: 0; }
            .stat-card { padding-inline: 0.8rem; }
            .stat-card p { font-size: 0.8rem; }
            .pillar-tablist-wrap { margin-inline: -0.4rem; }
            .pillar-tab { flex: none; }
            .ui-mockup-frame { padding: 0.85rem; }
            .ui-mockup-topbar { flex-wrap: wrap; gap: 0.6rem; }
            .ui-kpi-tile { padding: 0.65rem; overflow-wrap: anywhere; }
            .whatsapp-fab { inset-inline-end: 1rem; inset-block-end: max(1rem, env(safe-area-inset-bottom)); }
        }

        @media (max-width: 991.98px) {
            .hero-float-chip {
                position: static;
                margin-top: 0.75rem;
            }
            .whatsapp-fab span {
                display: none;
            }
            .whatsapp-fab {
                width: 56px;
                height: 56px;
                padding: 0;
                justify-content: center;
            }
        }

        .hero-slides { display: grid; touch-action: pan-y; }
        .hero-slide { grid-area: 1 / 1; margin: 0; opacity: 0; visibility: hidden; transition: opacity 550ms ease, visibility 550ms; }
        .hero-slide.is-active { opacity: 1; visibility: visible; }
        .hero-slide figcaption { padding: 1rem .25rem .5rem; color: #fff; }
        .hero-slide h2 { font-size: clamp(1rem, 2vw, 1.3rem); color: #fff; margin: 0 0 .4rem; }
        .hero-slide p { color: #c1d7db; font-size: .85rem; line-height: 1.6; margin: 0; }
        .hero-carousel-controls { display: flex; align-items: center; justify-content: center; gap: .25rem; margin-top: .5rem; }
        .hero-carousel-controls[hidden] { display: none; }
        .hero-carousel-controls button { border: 0; background: transparent; color: white; min-width: 44px; min-height: 44px; border-radius: 50%; }
        .hero-carousel-controls button:hover { background: #ffffff18; }
        .hero-carousel-controls button:focus-visible { outline: 2px solid var(--teal-bright); outline-offset: 2px; }
        .hero-carousel-dots { display: flex; }
        .hero-carousel-dots button { min-width: 28px; padding: 0; }
        .hero-carousel-dots span { display: block; width: 7px; height: 7px; margin: auto; background: #84969e; border-radius: 9px; transition: width .3s; }
        .hero-carousel-dots [aria-current="true"] span { width: 20px; background: var(--teal-bright); }
        @media (prefers-reduced-motion: reduce) { .hero-slide, .hero-carousel-dots span { transition: none; } }
    </style>
</head>
<body>
    <a href="#contenido-principal" class="skip-link">Saltar al contenido principal</a>

    <!-- Barra de progreso de scroll -->
    <div id="scroll-progress" aria-hidden="true"></div>

    <!-- NAVBAR -->
    <header class="landing-navbar" id="landingNavbar">
        <nav class="navbar navbar-expand-lg p-0" aria-label="Navegación principal">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="#inicio" aria-label="REDIL Cloud, ir al inicio">
                    <img src="{{ $logoUrl }}" alt="REDIL Cloud" class="app-brand-logo" style="width: 200px; max-width: 100%; height: auto;">
                </a>
                <button class="navbar-toggler border-0 p-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarLanding" aria-controls="navbarLanding" aria-expanded="false" aria-label="Abrir menú de navegación">
                    <i class="bi bi-list fs-2 text-dark" aria-hidden="true"></i>
                </button>
                <div class="collapse navbar-collapse justify-content-end" id="navbarLanding">
                    <ul class="navbar-nav align-items-lg-center gap-1 gap-lg-2 mt-3 mt-lg-0">
                        <li class="nav-item"><a class="nav-link landing-nav-link" href="#modulos">Plataforma</a></li>
                        <li class="nav-item"><a class="nav-link landing-nav-link" href="#beneficios">Beneficios</a></li>
                        <li class="nav-item"><a class="nav-link landing-nav-link" href="#equipos">Líderes y Roles</a></li>
                        <li class="nav-item"><a class="nav-link landing-nav-link" href="#preguntas">Preguntas</a></li>
                        <li class="nav-item ms-lg-2">
                            <a href="{{ $whatsappUrl }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="btn landing-btn-whatsapp-outline rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-2">
                                <i class="bi bi-whatsapp" aria-hidden="true"></i>
                                <span>WhatsApp</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#contacto" class="btn landing-btn-primary rounded-pill px-4 py-2 fw-bold">
                                Agendar videollamada
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main id="contenido-principal">
        <!-- HERO SECTION -->
        <section id="inicio" class="hero-section">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6 hero-copy">
                        <div class="hero-eyebrow mb-3">
                            <i class="bi bi-stars" aria-hidden="true"></i>
                            <span>Tecnología al servicio de tu iglesia</span>
                        </div>
                        <h1 class="hero-title mb-4">
                            Tu iglesia conectada.<span class="text-gradient-teal">Más cerca de cada persona.</span>
                        </h1>
                        <p class="hero-description mb-4">
                            Menos tareas dispersas, más tiempo para pastorear. Conecta miembros, grupos, escuelas y finanzas en una plataforma que acompaña el crecimiento de tu congregación.
                        </p>

                        <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-3 mb-4 hero-actions">
                            <a href="#contacto" class="btn landing-btn-primary rounded-pill px-4 py-3 fw-bold d-inline-flex align-items-center justify-content-center gap-2">
                                <span>Agendar videollamada</span>
                                <i class="bi bi-camera-video" aria-hidden="true"></i>
                            </a>
                            <a href="{{ $whatsappUrl }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="btn landing-btn-whatsapp-outline rounded-pill px-4 py-3 fw-bold d-inline-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-whatsapp fs-5" aria-hidden="true"></i>
                                <span>Hablar por WhatsApp</span>
                            </a>
                        </div>

                        <div class="d-flex flex-wrap gap-3 gap-md-4 pt-2 small fw-semibold hero-proof" style="color: var(--ink-800);">
                            <span class="d-inline-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill" style="color: var(--teal-accent);" aria-hidden="true"></i>
                                Implementación acompañada
                            </span>
                            <span class="d-inline-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill" style="color: var(--teal-accent);" aria-hidden="true"></i>
                                Soporte humano en español
                            </span>
                            <span class="d-inline-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill" style="color: var(--teal-accent);" aria-hidden="true"></i>
                                App PWA y Marca Blanca
                            </span>
                        </div>
                        <a href="#modulos" class="hero-explore mt-4"><i class="bi bi-grid" aria-hidden="true"></i> Explorar la plataforma</a>
                    </div>

                    <div class="col-lg-6">
                        <div class="hero-visual-stage">

                            <div class="hero-visual-card">
                                <div class="d-flex align-items-center justify-content-between mb-3 px-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="ui-dot" style="background: #FF5F56;"></span>
                                        <span class="ui-dot" style="background: #FFBD2E;"></span>
                                        <span class="ui-dot" style="background: #27C93F;"></span>
                                        <span class="ms-2 small text-white-50">tuiglesia.redilcloud.com</span>
                                    </div>
                                    <span class="ui-badge-live">
                                        <i class="bi bi-circle-fill" style="font-size: 0.45rem;" aria-hidden="true"></i>
                                        Tiempo real
                                    </span>
                                </div>
                                <section id="heroCarousel" aria-label="Explora los módulos de REDIL" aria-roledescription="carrusel">
                                    <div class="hero-slides" aria-live="off">
                                        @foreach ($heroSlides as $slide)
                                            <figure class="hero-slide {{ $loop->first ? 'is-active' : '' }}" role="group" aria-roledescription="diapositiva" aria-label="{{ $loop->iteration }} de {{ $loop->count }}: {{ $slide['title'] }}" aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                                                <img src="{{ $slide['image'] }}" alt="Ilustración de {{ $slide['title'] }}" width="1254" height="1254" class="img-fluid w-100" loading="{{ $loop->first ? 'eager' : 'lazy' }}" decoding="async" fetchpriority="{{ $loop->first ? 'high' : 'low' }}">
                                                <figcaption>
                                                    <h2>{{ $slide['title'] }}</h2>
                                                    <p>{{ $slide['description'] }}</p>
                                                </figcaption>
                                            </figure>
                                        @endforeach
                                    </div>
                                    <div class="hero-carousel-controls" hidden>
                                        <button type="button" data-carousel-prev aria-label="Módulo anterior"><i class="bi bi-arrow-left" aria-hidden="true"></i></button>
                                        <div class="hero-carousel-dots" role="group" aria-label="Elegir módulo">
                                            @foreach ($heroSlides as $slide)
                                                <button type="button" data-carousel-index="{{ $loop->index }}" aria-label="Ver {{ $slide['title'] }}" aria-current="{{ $loop->first ? 'true' : 'false' }}"><span></span></button>
                                            @endforeach
                                        </div>
                                        <button type="button" data-carousel-next aria-label="Módulo siguiente"><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                                        <button type="button" data-carousel-pause aria-label="Pausar carrusel"><i class="bi bi-pause-fill" aria-hidden="true"></i></button>
                                    </div>
                                </section>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- STATS BAND -->
        <section class="stats-band" aria-label="Trayectoria de REDIL Cloud">
            <div class="container">
                <div class="row g-4">
                    <div class="col-6 col-lg-3 reveal-up reveal-stagger-1">
                        <div class="stat-card">
                            <div class="stat-number">+13</div>
                            <h2 class="h6 fw-bold text-white mt-2 mb-1">Años sirviendo iglesias</h2>
                            <p class="small text-white-50 mb-0">Experiencia real construyendo tecnología para ministerios en crecimiento.</p>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 reveal-up reveal-stagger-2">
                        <div class="stat-card">
                            <div class="stat-number">+500</div>
                            <h2 class="h6 fw-bold text-white mt-2 mb-1">Iglesias activas</h2>
                            <p class="small text-white-50 mb-0">Desde congregaciones locales hasta iglesias multi-sede con miles de miembros.</p>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 reveal-up reveal-stagger-3">
                        <div class="stat-card">
                            <div class="stat-number">20</div>
                            <h2 class="h6 fw-bold text-white mt-2 mb-1">Países con presencia</h2>
                            <p class="small text-white-50 mb-0">Acompañamiento cercano y soporte 100% en español para toda Latinoamérica y EE. UU.</p>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 reveal-up reveal-stagger-4">
                        <div class="stat-card">
                            <div class="stat-number"><i class="bi bi-clock-history" aria-hidden="true"></i></div>
                            <h2 class="h6 fw-bold text-white mt-2 mb-1">Ahorro de tiempo garantizado</h2>
                            <p class="small text-white-50 mb-0">Equipos que recuperan horas cada semana para enfocarse en las personas.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SHOWCASE INTERACTIVO DE LOS 5 PILARES REALES -->
        <section id="modulos" class="pillars-section">
            <div class="container">
                <div class="row justify-content-between align-items-end mb-4 reveal-up">
                    <div class="col-lg-8">
                        <div class="section-kicker">
                            <i class="bi bi-grid-1x2-fill" aria-hidden="true"></i>
                            <span>Ecosistema completo REDIL Cloud</span>
                        </div>
                        <h2 class="section-heading mb-3">
                            Todo lo que tu iglesia necesita, conectado en un solo lugar
                        </h2>
                        <p class="fs-5 mb-0" style="color: var(--ink-600);">
                            Explora las cinco áreas de la plataforma y descubre cómo cada área trabaja sincronizada sin hojas de cálculo dispersas.
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="#contacto" class="btn landing-btn-dark rounded-pill px-4 py-2 fw-semibold">
                            Agendar videollamada <i class="bi bi-arrow-down-right ms-1" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>

                <!-- Tablist accesible de una sola fila con scroll horizontal -->
                <div class="pillar-tablist-wrap reveal-up">
                    <div class="pillar-tablist" role="tablist" aria-label="Pilares de la plataforma REDIL Cloud" id="pillarTablist">
                        <button type="button" class="pillar-tab" role="tab" id="tab-eclesiastica" aria-controls="panel-eclesiastica" aria-selected="true" data-pillar="eclesiastica">
                            <i class="bi bi-people-fill" aria-hidden="true"></i>
                            <span>Gestión de Miembros</span>
                        </button>
                        <button type="button" class="pillar-tab" role="tab" id="tab-lms" aria-controls="panel-lms" aria-selected="false" tabindex="-1" data-pillar="lms">
                            <i class="bi bi-mortarboard-fill" aria-hidden="true"></i>
                            <span>Escuelas &amp; LMS</span>
                        </button>
                        <button type="button" class="pillar-tab" role="tab" id="tab-finanzas" aria-controls="panel-finanzas" aria-selected="false" tabindex="-1" data-pillar="finanzas">
                            <i class="bi bi-cash-coin" aria-hidden="true"></i>
                            <span>Finanzas &amp; Eventos</span>
                        </button>
                        <button type="button" class="pillar-tab" role="tab" id="tab-espiritual" aria-controls="panel-espiritual" aria-selected="false" tabindex="-1" data-pillar="espiritual">
                            <i class="bi bi-heart-pulse-fill" aria-hidden="true"></i>
                            <span>Crecimiento Espiritual</span>
                        </button>
                        <button type="button" class="pillar-tab" role="tab" id="tab-marca" aria-controls="panel-marca" aria-selected="false" tabindex="-1" data-pillar="marca">
                            <i class="bi bi-shield-lock-fill" aria-hidden="true"></i>
                            <span>Marca Blanca &amp; Seguridad</span>
                        </button>
                    </div>
                </div>

                <!-- Paneles de los 5 pilares (sin padding/fondo en el contenedor role="tabpanel") -->
                <div class="pillar-panels reveal-scale">
                    <!-- PILAR 1: GESTIÓN ECLESIÁSTICA -->
                    <div role="tabpanel" id="panel-eclesiastica" aria-labelledby="tab-eclesiastica" data-panel="eclesiastica" tabindex="0">
                        <div class="pillar-panel-card">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-6">
                                    <span class="badge rounded-pill mb-3 px-3 py-2 fw-bold" style="background: rgba(0, 228, 187, 0.18); color: var(--teal-deep);">
                                        Pilar 01 · Pastoreo y Operación
                                    </span>
                                    <h3 class="fw-bolder mb-3" style="font-size: 1.9rem;">
                                        Conoce a cada persona, grupo y familia sin perder seguimiento
                                    </h3>
                                    <p class="mb-4" style="line-height: 1.65;">
                                        Desde la primera visita hasta el liderazgo activo: controla cultos, grupos, consolidación, peticiones de oración e Iglesia Infantil con códigos QR.
                                    </p>
                                    <div class="row g-3 mb-4">
                                        <div class="col-sm-6">
                                            <div class="pillar-feature-item">
                                                <div class="fw-bold text-dark mb-1"><i class="bi bi-diagram-3-fill me-2" style="color: var(--teal-accent);"></i>Grupos y Ministerios</div>
                                                <p class="small mb-0">Estructura jerárquica, reportes semanales y geolocalización de líderes.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="pillar-feature-item">
                                                <div class="fw-bold text-dark mb-1"><i class="bi bi-qr-code-scan me-2" style="color: var(--teal-accent);"></i>Iglesia Infantil QR</div>
                                                <p class="small mb-0">Check-in/out con QR de entrega, salones, alertas médicas y trazabilidad.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="pillar-feature-item">
                                                <div class="fw-bold text-dark mb-1"><i class="bi bi-calendar2-check-fill me-2" style="color: var(--teal-accent);"></i>Reuniones y Reportes</div>
                                                <p class="small mb-0">Asistencia por servicio, aforos, reservas y estadísticas instantáneas.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="pillar-feature-item">
                                                <div class="fw-bold text-dark mb-1"><i class="bi bi-person-heart me-2" style="color: var(--teal-accent);"></i>Consolidación y Peticiones</div>
                                                <p class="small mb-0">Seguimiento de nuevos creyentes, consejerías y oración en equipo.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="#contacto"
                                       data-select-module="Gestión de Miembros y Grupos"
                                       class="btn landing-btn-primary rounded-pill px-4 py-2 fw-bold">
                                        Ver Gestión de Miembros en vivo
                                    </a>
                                </div>
                                <div class="col-lg-6">
                                    <div class="ui-mockup-frame">
                                        <div class="ui-mockup-topbar">
                                            <span><i class="bi bi-people-fill me-1 text-info"></i> Panel Pastoral · En vivo</span>
                                            <span class="ui-badge-live">Iglesia Infantil + Grupos</span>
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <div class="col-4">
                                                <div class="ui-kpi-tile">
                                                    <div class="small text-white-50">Asistencia</div>
                                                    <div class="fs-4 fw-bold text-white">1,420</div>
                                                    <div class="small" style="color: var(--teal-bright);">+12% mes</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="ui-kpi-tile">
                                                    <div class="small text-white-50">Grupos</div>
                                                    <div class="fs-4 fw-bold text-white">48</div>
                                                    <div class="small" style="color: var(--teal-bright);">96% reportan</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="ui-kpi-tile">
                                                    <div class="small text-white-50">Niños QR</div>
                                                    <div class="fs-4 fw-bold text-white">184</div>
                                                    <div class="small" style="color: var(--teal-bright);">100% verificado</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ui-kpi-tile mb-2 d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="fw-semibold text-white small">Check-in Kids · Salón Semillitas</div>
                                                <div class="text-white-50" style="font-size: 0.76rem;">Código QR #RD-4821 · Alerta médica verificada</div>
                                            </div>
                                            <span class="badge bg-success-subtle text-success rounded-pill">Entregado</span>
                                        </div>
                                        <div class="ui-kpi-tile d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="fw-semibold text-white small">Consolidación · 26 nuevos esta semana</div>
                                                <div class="text-white-50" style="font-size: 0.76rem;">Asignados automáticamente a líderes por zona</div>
                                            </div>
                                            <span class="badge rounded-pill" style="background: rgba(0,228,187,0.2); color: var(--teal-bright);">Activo</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PILAR 2: ESCUELAS & LMS -->
                    <div role="tabpanel" id="panel-lms" aria-labelledby="tab-lms" data-panel="lms" hidden="until-found">
                        <div class="pillar-panel-card">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-6">
                                    <span class="badge rounded-pill mb-3 px-3 py-2 fw-bold" style="background: rgba(0, 228, 187, 0.18); color: var(--teal-deep);">
                                        Pilar 02 · Formación y Academia
                                    </span>
                                    <h3 class="fw-bolder mb-3" style="font-size: 1.9rem;">
                                        Tu escuela bíblica y campus virtual LMS en la misma plataforma
                                    </h3>
                                    <p class="mb-4" style="line-height: 1.65;">
                                        Gestiona escuelas presenciales y cursos virtuales con prerrequisitos automáticos, desbloqueo secuencial de lecciones, evaluaciones y calificaciones con historial en PDF.
                                    </p>
                                    <div class="row g-3 mb-4">
                                        <div class="col-sm-6">
                                            <div class="pillar-feature-item">
                                                <div class="fw-bold text-dark mb-1"><i class="bi bi-layers-fill me-2" style="color: var(--teal-accent);"></i>Escuelas, Niveles y Periodos</div>
                                                <p class="small mb-0">Clonación profunda de periodos académicos con horarios y cortes.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="pillar-feature-item">
                                                <div class="fw-bold text-dark mb-1"><i class="bi bi-play-btn-fill me-2" style="color: var(--teal-accent);"></i>Campus Estudiantil LMS</div>
                                                <p class="small mb-0">Videos YouTube/Vimeo con detección de completado, foros y exámenes.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="pillar-feature-item">
                                                <div class="fw-bold text-dark mb-1"><i class="bi bi-award-fill me-2" style="color: var(--teal-accent);"></i>Prerrequisitos Inteligentes</div>
                                                <p class="small mb-0">Valida pasos de crecimiento y cambia roles automáticamente al graduar.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="pillar-feature-item">
                                                <div class="fw-bold text-dark mb-1"><i class="bi bi-file-earmark-pdf-fill me-2" style="color: var(--teal-accent);"></i>Notas y Homologaciones</div>
                                                <p class="small mb-0">Boletines descargables, equivalencias y matrículas con auto-servicio.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="#contacto"
                                       data-select-module="Escuelas y Campus LMS"
                                       class="btn landing-btn-primary rounded-pill px-4 py-2 fw-bold">
                                        Ver Escuelas &amp; LMS en vivo
                                    </a>
                                </div>
                                <div class="col-lg-6">
                                    <div class="ui-mockup-frame">
                                        <div class="ui-mockup-topbar">
                                            <span><i class="bi bi-mortarboard-fill me-1 text-info"></i> Campus Estudiantil · Periodo 2026-II</span>
                                            <span class="ui-badge-live">LMS Activo</span>
                                        </div>
                                        <div class="ui-kpi-tile mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold text-white small">Escuela de Liderazgo · Nivel 2</span>
                                                <span class="small fw-bold" style="color: var(--teal-bright);">78% completado</span>
                                            </div>
                                            <div class="progress" style="height: 8px; background: rgba(255,255,255,0.12);">
                                                <div class="progress-bar" style="width: 78%; background: var(--teal-bright);"></div>
                                            </div>
                                        </div>
                                        <div class="ui-kpi-tile mb-2 d-flex justify-content-between align-items-center">
                                            <span class="small text-white"><i class="bi bi-check-circle-fill me-2" style="color: var(--teal-bright);"></i>Módulo 1: Fundamentos del Servicio</span>
                                            <span class="small text-white-50">Nota: 4.8</span>
                                        </div>
                                        <div class="ui-kpi-tile mb-2 d-flex justify-content-between align-items-center">
                                            <span class="small text-white"><i class="bi bi-play-circle-fill me-2 text-info"></i>Módulo 2: Pastoreo de Grupos (Video)</span>
                                            <span class="badge rounded-pill" style="background: rgba(0,228,187,0.18); color: var(--teal-bright);">En curso</span>
                                        </div>
                                        <div class="ui-kpi-tile d-flex justify-content-between align-items-center opacity-75">
                                            <span class="small text-white-50"><i class="bi bi-lock-fill me-2"></i>Módulo 3: Evaluación Final</span>
                                            <span class="small text-white-50">Desbloqueo secuencial</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PILAR 3: FINANZAS & EVENTOS -->
                    <div role="tabpanel" id="panel-finanzas" aria-labelledby="tab-finanzas" data-panel="finanzas" hidden="until-found">
                        <div class="pillar-panel-card">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-6">
                                    <span class="badge rounded-pill mb-3 px-3 py-2 fw-bold" style="background: rgba(0, 228, 187, 0.18); color: var(--teal-deep);">
                                        Pilar 03 · Tesorería y Actividades
                                    </span>
                                    <h3 class="fw-bolder mb-3" style="font-size: 1.9rem;">
                                        Transparencia financiera, puntos de pago y eventos con inscripciones
                                    </h3>
                                    <p class="mb-4" style="line-height: 1.65;">
                                        Organiza diezmos, ofrendas, cajas físicas y eventos pagos como congresos o campamentos con carrito de compras, abonos y cierres claros para tu tesorería.
                                    </p>
                                    <div class="row g-3 mb-4">
                                        <div class="col-sm-6">
                                            <div class="pillar-feature-item">
                                                <div class="fw-bold text-dark mb-1"><i class="bi bi-shop me-2" style="color: var(--teal-accent);"></i>Puntos de Pago y Cajas</div>
                                                <p class="small mb-0">Control por sede, cajeros autorizados y cierres diarios auditables.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="pillar-feature-item">
                                                <div class="fw-bold text-dark mb-1"><i class="bi bi-cart-check-fill me-2" style="color: var(--teal-accent);"></i>Actividades y Carrito</div>
                                                <p class="small mb-0">Inscripciones en línea para retiros, escuelas y congresos con tarifas.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="pillar-feature-item">
                                                <div class="fw-bold text-dark mb-1"><i class="bi bi-graph-up-arrow me-2" style="color: var(--teal-accent);"></i>Diezmos y Ofrendas</div>
                                                <p class="small mb-0">Reportes por reunión, clasificaciones y visión ejecutiva para pastores.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="pillar-feature-item">
                                                <div class="fw-bold text-dark mb-1"><i class="bi bi-credit-card-2-front-fill me-2" style="color: var(--teal-accent);"></i>Métodos Configurables</div>
                                                <p class="small mb-0">Efectivo, transferencias y pagos por BePay y ZonaPagos configurables (valor adicional).</p>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="#contacto"
                                       data-select-module="Finanzas, Cajas y Eventos"
                                       class="btn landing-btn-primary rounded-pill px-4 py-2 fw-bold">
                                        Ver Finanzas &amp; Eventos en vivo
                                    </a>
                                </div>
                                <div class="col-lg-6">
                                    <div class="ui-mockup-frame">
                                        <div class="ui-mockup-topbar">
                                            <span><i class="bi bi-cash-coin me-1 text-info"></i> Tesorería &amp; Actividades</span>
                                            <span class="ui-badge-live">Cierre cuadrado</span>
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <div class="ui-kpi-tile">
                                                    <div class="small text-white-50">Congreso Jóvenes</div>
                                                    <div class="fs-4 fw-bold text-white">312 cupos</div>
                                                    <div class="small" style="color: var(--teal-bright);">89% pagado</div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="ui-kpi-tile">
                                                    <div class="small text-white-50">Cajas activas</div>
                                                    <div class="fs-4 fw-bold text-white">4 sedes</div>
                                                    <div class="small" style="color: var(--teal-bright);">Sin novedades</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ui-kpi-tile d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-semibold text-white small">Inscripciones + Matrículas integradas</div>
                                                <div class="text-white-50" style="font-size: 0.76rem;">Comprobantes automáticos y seguimiento de abonos en un clic</div>
                                            </div>
                                            <i class="bi bi-check2-circle fs-4" style="color: var(--teal-bright);"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PILAR 4: CRECIMIENTO ESPIRITUAL -->
                    <div role="tabpanel" id="panel-espiritual" aria-labelledby="tab-espiritual" data-panel="espiritual" hidden="until-found">
                        <div class="pillar-panel-card">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-6">
                                    <span class="badge rounded-pill mb-3 px-3 py-2 fw-bold" style="background: rgba(0, 228, 187, 0.18); color: var(--teal-deep);">
                                        Pilar 04 · Discipulado Diario y App PWA
                                    </span>
                                    <h3 class="fw-bolder mb-3" style="font-size: 1.9rem;">
                                        Acompaña la vida espiritual de tu iglesia de lunes a domingo
                                    </h3>
                                    <p class="mb-4" style="line-height: 1.65;">
                                        Más allá de lo administrativo: ofrece a cada miembro devocionales diarios, gestión de crecimiento espiritual individual o por proyecto, pasos de crecimiento y notificaciones push desde la app PWA.
                                    </p>
                                    <div class="row g-3 mb-4">
                                        <div class="col-sm-6">
                                            <div class="pillar-feature-item">
                                                <div class="fw-bold text-dark mb-1"><i class="bi bi-book-half me-2" style="color: var(--teal-accent);"></i>Tiempo con Dios</div>
                                                <p class="small mb-0">Devocionales diarios con lectura bíblica, música y racha de constancia.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="pillar-feature-item">
                                                <div class="fw-bold text-dark mb-1"><i class="bi bi-compass-fill me-2" style="color: var(--teal-accent);"></i>Crecimiento Espiritual</div>
                                                <p class="small mb-0">Gestión de crecimiento espiritual individual o por proyecto, con metas y hábitos acompañados.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="pillar-feature-item">
                                                <div class="fw-bold text-dark mb-1"><i class="bi bi-signpost-split-fill me-2" style="color: var(--teal-accent);"></i>Pasos de Crecimiento</div>
                                                <p class="small mb-0">Hitos espirituales claros conectados con escuelas y ministerios.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="pillar-feature-item">
                                                <div class="fw-bold text-dark mb-1"><i class="bi bi-bell-fill me-2" style="color: var(--teal-accent);"></i>App PWA + Notificaciones</div>
                                                <p class="small mb-0">Instalable en celular sin tiendas complejas, con alertas por rol y sede.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="#contacto"
                                       data-select-module="Crecimiento Espiritual y PWA"
                                       class="btn landing-btn-primary rounded-pill px-4 py-2 fw-bold">
                                        Ver Crecimiento Espiritual en vivo
                                    </a>
                                </div>
                                <div class="col-lg-6">
                                    <div class="ui-mockup-frame">
                                        <div class="ui-mockup-topbar">
                                            <span><i class="bi bi-phone-fill me-1 text-info"></i> Experiencia Miembro · PWA</span>
                                            <span class="ui-badge-live">Racha 14 días</span>
                                        </div>
                                        <div class="ui-kpi-tile mb-3">
                                            <div class="small" style="color: var(--teal-bright);">Versículo del día</div>
                                            <div class="fw-semibold text-white mt-1">"Lámpara es a mis pies tu palabra, y lumbrera a mi camino."</div>
                                            <div class="small text-white-50 mt-1">Salmos 119:105 · Tiempo con Dios</div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="ui-kpi-tile">
                                                    <div class="small text-white-50">Pasos completados</div>
                                                    <div class="fw-bold text-white fs-5">4 / 5 hitos</div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="ui-kpi-tile">
                                                    <div class="small text-white-50">Crecimiento espiritual</div>
                                                    <div class="fw-bold text-white fs-5">Plan activo</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PILAR 5: MARCA BLANCA & SEGURIDAD -->
                    <div role="tabpanel" id="panel-marca" aria-labelledby="tab-marca" data-panel="marca" hidden="until-found">
                        <div class="pillar-panel-card">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-6">
                                    <span class="badge rounded-pill mb-3 px-3 py-2 fw-bold" style="background: rgba(0, 228, 187, 0.18); color: var(--teal-deep);">
                                        Pilar 05 · Identidad y Seguridad
                                    </span>
                                    <h3 class="fw-bolder mb-3" style="font-size: 1.9rem;">
                                        Tu propia identidad visual con privacidad y control total
                                    </h3>
                                    <p class="mb-4" style="line-height: 1.65;">
                                        Cada iglesia cuenta con su propio espacio privado y seguro en la nube, con colores, logotipo y pantalla de acceso propios según el plan.
                                    </p>
                                    <div class="row g-3 mb-4">
                                        <div class="col-sm-6">
                                            <div class="pillar-feature-item">
                                                <div class="fw-bold text-dark mb-1"><i class="bi bi-palette-fill me-2" style="color: var(--teal-accent);"></i>Marca Blanca y Temas</div>
                                                <p class="small mb-0">Personaliza colores, logo y portada de inicio de sesión para tu iglesia.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="pillar-feature-item">
                                                <div class="fw-bold text-dark mb-1"><i class="bi bi-shield-lock-fill me-2" style="color: var(--teal-accent);"></i>Espacio Privado por Iglesia</div>
                                                <p class="small mb-0">Tu congregación opera en un entorno dedicado, seguro y completamente independiente.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="pillar-feature-item">
                                                <div class="fw-bold text-dark mb-1"><i class="bi bi-building-check me-2" style="color: var(--teal-accent);"></i>Soporte Multi-Sede</div>
                                                <p class="small mb-0">Administra sedes, campus y reportes consolidados desde una cuenta.</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="pillar-feature-item">
                                                <div class="fw-bold text-dark mb-1"><i class="bi bi-key-fill me-2" style="color: var(--teal-accent);"></i>Roles y Permisos</div>
                                                <p class="small mb-0">Control granular sobre qué ve y edita cada líder o ministerio.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="#contacto"
                                       data-select-module="Marca Blanca y Multi-sede"
                                       class="btn landing-btn-primary rounded-pill px-4 py-2 fw-bold">
                                        Consultar planes Marca Blanca
                                    </a>
                                </div>
                                <div class="col-lg-6">
                                    <div class="ui-mockup-frame">
                                        <div class="ui-mockup-topbar">
                                            <span><i class="bi bi-shield-check me-1 text-info"></i> Seguridad &amp; Branding</span>
                                            <span class="ui-badge-live">HTTPS · Privado</span>
                                        </div>
                                        <div class="ui-kpi-tile mb-3">
                                            <div class="small text-white-50 mb-1">Dominio exclusivo de tu iglesia</div>
                                            <div class="fw-bold text-white">https://miiglesia.redilcloud.com</div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="ui-kpi-tile">
                                                    <div class="small text-white-50">Entorno</div>
                                                    <div class="fw-bold text-white small mt-1">100% dedicado</div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="ui-kpi-tile">
                                                    <div class="small text-white-50">Permisos</div>
                                                    <div class="fw-bold text-white small mt-1">Control por área</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ANTES VS CON REDIL CLOUD -->
        <section id="beneficios" class="comparison-section">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-5 reveal-left">
                        <div class="section-kicker">
                            <i class="bi bi-lightning-charge-fill" aria-hidden="true"></i>
                            <span>¿Qué problema resolvemos?</span>
                        </div>
                        <h2 class="section-heading mb-3">
                            Adiós al desorden en papel y archivos de Excel
                        </h2>
                        <p class="fs-5 mb-4" style="color: var(--ink-600); line-height: 1.6;">
                            Recupera el tiempo administrativo de tu equipo pastoral y ten visibilidad clara de cada área en segundos.
                        </p>
                        <img src="{{ $imgProblemas }}"
                             alt="Desafíos administrativos que resuelve REDIL Cloud"
                             class="img-fluid rounded-4"
                             loading="lazy">
                    </div>

                    <div class="col-lg-7">
                        <div class="row g-4">
                            <div class="col-md-6 reveal-up reveal-stagger-1">
                                <div class="comparison-card">
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-2 fw-bold mb-3">Sin plataforma</span>
                                    <h3 class="h5 fw-bold mb-3">Gestión dispersa</h3>
                                    <ul class="list-unstyled d-grid gap-3 mb-0 small fw-medium">
                                        <li class="d-flex gap-2">
                                            <i class="bi bi-x-circle-fill text-danger mt-1" aria-hidden="true"></i>
                                            <span>Listados en papel, formularios sueltos y hojas de Excel desactualizadas.</span>
                                        </li>
                                        <li class="d-flex gap-2">
                                            <i class="bi bi-x-circle-fill text-danger mt-1" aria-hidden="true"></i>
                                            <span>Nuevos visitantes que no reciben llamada ni seguimiento a tiempo.</span>
                                        </li>
                                        <li class="d-flex gap-2">
                                            <i class="bi bi-x-circle-fill text-danger mt-1" aria-hidden="true"></i>
                                            <span>Reportes de asistencia y ofrendas que tardan días en consolidarse.</span>
                                        </li>
                                        <li class="d-flex gap-2">
                                            <i class="bi bi-x-circle-fill text-danger mt-1" aria-hidden="true"></i>
                                            <span>Una sola persona saturada cargando toda la información.</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-md-6 reveal-up reveal-stagger-3">
                                <div class="comparison-card is-redil">
                                    <span class="badge rounded-pill px-3 py-2 fw-bold mb-3" style="background: rgba(0, 228, 187, 0.2); color: var(--teal-bright);">Con REDIL Cloud</span>
                                    <h3 class="h5 fw-bold text-white mb-3">Pastoreo inteligente</h3>
                                    <ul class="list-unstyled d-grid gap-3 mb-0 small">
                                        <li class="d-flex gap-2">
                                            <i class="bi bi-check-circle-fill mt-1" style="color: var(--teal-bright);" aria-hidden="true"></i>
                                            <span>Base de datos viva y segura, accesible desde celular, tablet o PC.</span>
                                        </li>
                                        <li class="d-flex gap-2">
                                            <i class="bi bi-check-circle-fill mt-1" style="color: var(--teal-bright);" aria-hidden="true"></i>
                                            <span>Consolidación, grupos y pasos de crecimiento medibles al instante.</span>
                                        </li>
                                        <li class="d-flex gap-2">
                                            <i class="bi bi-check-circle-fill mt-1" style="color: var(--teal-bright);" aria-hidden="true"></i>
                                            <span>Escuelas, finanzas, eventos e Iglesia Infantil QR bajo un solo techo.</span>
                                        </li>
                                        <li class="d-flex gap-2">
                                            <i class="bi bi-check-circle-fill mt-1" style="color: var(--teal-bright);" aria-hidden="true"></i>
                                            <span>Cada líder trabaja con su propia cuenta y permisos exactos.</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- EQUIPO DE LÍDERES / RBAC INTERACTIVO -->
        <section id="equipos" class="team-section" x-data="{ rolActivo: 'pastor' }">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6 reveal-left">
                        <div class="bg-white rounded-4 p-4 shadow-sm border">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="h6 fw-bolder mb-0">Haz clic en un rol para ver su acceso</h3>
                                <span class="badge rounded-pill" style="background: rgba(0, 228, 187, 0.16); color: var(--teal-deep);">Permisos por rol</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <button type="button" class="role-card-btn" :class="{ 'is-active': rolActivo === 'pastor' }" @click="rolActivo = 'pastor'">
                                        <div class="team-img-container">
                                            <img src="{{ $imgEquipo }}" alt="Pastor Principal" class="team-img-sprite team-sprite-1" loading="lazy">
                                        </div>
                                        <div class="p-2 px-3">
                                            <div class="fw-bold text-dark small">Pastor Principal</div>
                                            <div class="text-muted" style="font-size: 0.72rem;">Visión ejecutiva 360°</div>
                                        </div>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="role-card-btn" :class="{ 'is-active': rolActivo === 'jovenes' }" @click="rolActivo = 'jovenes'">
                                        <div class="team-img-container">
                                            <img src="{{ $imgEquipo }}" alt="Líder de Jóvenes" class="team-img-sprite team-sprite-2" loading="lazy">
                                        </div>
                                        <div class="p-2 px-3">
                                            <div class="fw-bold text-dark small">Líder de Ministerio</div>
                                            <div class="text-muted" style="font-size: 0.72rem;">Grupos y eventos</div>
                                        </div>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="role-card-btn" :class="{ 'is-active': rolActivo === 'tesoreria' }" @click="rolActivo = 'tesoreria'">
                                        <div class="team-img-container">
                                            <img src="{{ $imgEquipo }}" alt="Tesorería" class="team-img-sprite team-sprite-3" loading="lazy">
                                        </div>
                                        <div class="p-2 px-3">
                                            <div class="fw-bold text-dark small">Tesorería</div>
                                            <div class="text-muted" style="font-size: 0.72rem;">Cajas y ofrendas</div>
                                        </div>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="role-card-btn" :class="{ 'is-active': rolActivo === 'consolidacion' }" @click="rolActivo = 'consolidacion'">
                                        <div class="team-img-container">
                                            <img src="{{ $imgEquipo }}" alt="Consolidación" class="team-img-sprite team-sprite-4" loading="lazy">
                                        </div>
                                        <div class="p-2 px-3">
                                            <div class="fw-bold text-dark small">Consolidación</div>
                                            <div class="text-muted" style="font-size: 0.72rem;">Nuevos y escuelas</div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 reveal-right">
                        <div class="section-kicker">
                            <i class="bi bi-people-fill" aria-hidden="true"></i>
                            <span>Trabajo en equipo seguro</span>
                        </div>
                        <h2 class="section-heading mb-3">
                            Cada líder con su propia cuenta y permisos exactos
                        </h2>
                        <p class="fs-5 mb-4" style="color: var(--ink-600); line-height: 1.6;">
                            Delega sin perder el control. En REDIL Cloud defines roles personalizados para que cada persona vea únicamente la información de su ministerio o sede.
                        </p>

                        <div class="p-4 rounded-4 border mb-4" style="background-color: var(--surface-soft);">
                            <div x-show="rolActivo === 'pastor'">
                                <div class="fw-bolder text-dark mb-1">Permisos de Pastor Principal</div>
                                <p class="small mb-0">Dashboard completo de crecimiento, asistencia por sedes, alertas de consolidación, resumen financiero y estado académico de escuelas.</p>
                            </div>
                            <div x-show="rolActivo === 'jovenes'" x-cloak>
                                <div class="fw-bolder text-dark mb-1">Permisos de Líder de Ministerio / Grupo</div>
                                <p class="small mb-0">Registro rápido de asistencia desde el celular, seguimiento de sus discípulos, peticiones de oración y comunicación directa con su red.</p>
                            </div>
                            <div x-show="rolActivo === 'tesoreria'" x-cloak>
                                <div class="fw-bolder text-dark mb-1">Permisos de Tesorería y Puntos de Pago</div>
                                <p class="small mb-0">Gestión de cajas, ofrendas por culto, abonos de actividades y reportes contables con privacidad frente a otros roles.</p>
                            </div>
                            <div x-show="rolActivo === 'consolidacion'" x-cloak>
                                <div class="fw-bolder text-dark mb-1">Permisos de Consolidación y Escuelas</div>
                                <p class="small mb-0">Asignación de nuevos creyentes, control de tareas de consolidación, matrículas a escuelas bíblicas y seguimiento de graduación.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- PRESENCIA GLOBAL -->
        <section class="presence-section">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6 text-center reveal-scale">
                        <img src="{{ $imgPaises }}"
                             alt="Presencia de REDIL Cloud en 20 países de habla hispana"
                             class="img-fluid"
                             style="max-width: 92%;"
                             loading="lazy">
                    </div>
                    <div class="col-lg-6 reveal-up">
                        <div class="section-kicker">
                            <i class="bi bi-globe-americas" aria-hidden="true"></i>
                            <span>Comunidad internacional</span>
                        </div>
                        <h2 class="section-heading mb-3">
                            Software REDIL impulsa iglesias en <span class="text-gradient-teal">20 países</span>
                        </h2>
                        <p class="fs-5 mb-4" style="color: var(--ink-600); line-height: 1.65;">
                            Adaptado a la realidad de las iglesias hispanohablantes: monedas, zonas horarias, metodologías de grupos y acompañamiento cercano por WhatsApp y videollamada.
                        </p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="#contacto" class="btn landing-btn-primary rounded-pill px-4 py-3 fw-bold">
                                Agendar videollamada
                            </a>
                            <a href="{{ $whatsappUrl }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="btn landing-btn-whatsapp-outline rounded-pill px-4 py-3 fw-bold d-inline-flex align-items-center gap-2">
                                <i class="bi bi-whatsapp" aria-hidden="true"></i>
                                <span>Escribir a un asesor</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ALLY BANNER -->
        <section class="ally-banner text-center">
            <div class="container reveal-ally">
                <p class="fw-bold text-dark text-uppercase mb-2" style="letter-spacing: 0.22em; font-size: 0.85rem;">
                    Somos más que una herramienta
                </p>
                <h2 class="fw-bolder text-dark mb-3" style="font-size: clamp(2.4rem, 5.2vw, 4.2rem); line-height: 1.05;">
                    Tu visión, con un equipo a tu lado.
                </h2>
                <p class="text-dark fw-medium mx-auto mb-0" style="max-width: 620px; opacity: 0.88;">
                    Te acompañamos en la configuración inicial de tu plataforma y en la capacitación de tus líderes para que tu equipo domine REDIL Cloud desde el primer día.
                </p>
                <div class="row g-3 mt-4">
                    <div class="col-md-4 reveal-up reveal-stagger-1">
                        <div class="onboarding-step">
                            <span class="chip-icon"><i class="bi bi-chat-heart" aria-hidden="true"></i></span>
                            <h3>Conocemos tu iglesia</h3>
                            <p>Una videollamada para entender tu estructura y explorar las áreas que más necesita tu equipo.</p>
                        </div>
                    </div>
                    <div class="col-md-4 reveal-up reveal-stagger-2">
                        <div class="onboarding-step">
                            <span class="chip-icon"><i class="bi bi-sliders" aria-hidden="true"></i></span>
                            <h3>Configuramos contigo</h3>
                            <p>Te acompañamos al organizar sedes, roles y la identidad de tu iglesia en la plataforma.</p>
                        </div>
                    </div>
                    <div class="col-md-4 reveal-up reveal-stagger-3">
                        <div class="onboarding-step">
                            <span class="chip-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                            <h3>Preparamos a tus líderes</h3>
                            <p>Capacitación y soporte humano en español para que tu equipo avance con confianza.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- PREGUNTAS FRECUENTES -->
        <section id="preguntas" class="faq-section">
            <div class="container">
                <div class="row justify-content-center mb-5 text-center reveal-up">
                    <div class="col-lg-8">
                        <div class="section-kicker justify-content-center">
                            <i class="bi bi-question-circle-fill" aria-hidden="true"></i>
                            <span>Preguntas frecuentes</span>
                        </div>
                        <h2 class="section-heading mb-2">Resolvemos tus dudas antes de empezar</h2>
                        <p class="mb-0" style="color: var(--ink-600);">¿Tienes una pregunta específica sobre tu congregación? Escríbenos directo por WhatsApp.</p>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-9 reveal-up">
                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item faq-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true" aria-controls="faq1">
                                        ¿Cuánto tiempo toma implementar REDIL Cloud en nuestra iglesia?
                                    </button>
                                </h3>
                                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-secondary">
                                        Tu entorno en la nube se activa en minutos. Te acompañamos en la configuración inicial de roles, sedes y escuelas, además de capacitar a tus líderes para que puedan operar de inmediato.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item faq-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">
                                        ¿Necesitamos instalar programas en computadores o sirve desde el celular?
                                    </button>
                                </h3>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-secondary">
                                        REDIL Cloud funciona 100% en la nube desde cualquier navegador y además cuenta con experiencia PWA instalable en Android y iOS para líderes, estudiantes y miembros.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item faq-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3">
                                        ¿Qué tan segura y privada es la información de nuestra congregación?
                                    </button>
                                </h3>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-secondary">
                                        Cada iglesia cuenta con su propio entorno privado y seguro en la nube, conexiones cifradas HTTPS y control granular de permisos por rol. Nadie fuera de tu iglesia accede a tus datos.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item faq-item">
                                <h3 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false" aria-controls="faq4">
                                        ¿Podemos personalizar REDIL Cloud con el logo y los colores de nuestra iglesia?
                                    </button>
                                </h3>
                                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-secondary">
                                        Sí. Contamos con planes con logotipo propio y marca blanca completa, incluyendo paleta de colores dinámica y pantalla de acceso personalizada para tu congregación.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN DE CONTACTO & CONVERSIÓN -->
        <section id="contacto" class="contact-section">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-5 reveal-up">
                        <img src="{{ $logoBlanco }}" alt="REDIL Cloud" class="app-brand-logo mb-4" style="width: 200px; max-width: 100%; height: auto;">
                        <h2 class="fw-bolder text-white mb-3" style="font-size: clamp(2rem, 3.6vw, 2.8rem); line-height: 1.12;">
                            Lleva la gestión de tu iglesia al siguiente nivel
                        </h2>
                        <p class="text-white-50 fs-5 mb-4" style="line-height: 1.65;">
                            Agenda una videollamada para mostrar todas las ventajas de nuestro software o escríbenos directo por WhatsApp. Te ayudamos a elegir el plan ideal según tu iglesia.
                        </p>

                        <div class="d-grid gap-3 mb-4">
                            <div class="d-flex align-items-start gap-3">
                                <span class="chip-icon" style="background: rgba(0, 228, 187, 0.16); color: var(--teal-bright);" aria-hidden="true">
                                    <i class="bi bi-camera-video-fill"></i>
                                </span>
                                <div>
                                    <div class="fw-bold text-white">Videollamada personalizada para tu iglesia</div>
                                    <div class="small text-white-50">Recorremos los módulos que más necesitas sin guiones genéricos.</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-start gap-3">
                                <span class="chip-icon" style="background: rgba(37, 211, 102, 0.18); color: var(--whatsapp);" aria-hidden="true">
                                    <i class="bi bi-whatsapp"></i>
                                </span>
                                <div>
                                    <div class="fw-bold text-white">Atención directa por WhatsApp</div>
                                    <div class="small text-white-50">¿Prefieres chatear ahora mismo? Respuesta rápida en horario hábil.</div>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 rounded-4 border border-white border-opacity-10 bg-white bg-opacity-10">
                            <div class="small text-white-50 mb-1">¿Ya recibiste un código de invitación de REDIL?</div>
                            <a href="{{ url('/registro') }}" class="fw-bold text-decoration-none d-inline-flex align-items-center gap-2" style="color: var(--teal-bright);">
                                <span>Activar y registrar mi iglesia aquí</span>
                                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-7 reveal-scale">
                        @livewire('central.contacto-landing')
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- FOOTER -->
    <footer class="landing-footer">
        <div class="container">
            <div class="row g-4 pb-4 border-bottom border-white border-opacity-10">
                <div class="col-lg-5">
                    <img src="{{ $logoBlanco }}" alt="REDIL Cloud" class="app-brand-logo mb-3" style="width: 200px; max-width: 100%; height: auto;">
                    <p class="small mb-2" style="max-width: 360px;">
                        Plataforma en la nube de pastoreo inteligente, formación académica LMS, finanzas y crecimiento espiritual para iglesias.
                    </p>
                </div>
                <div class="col-6 col-lg-3">
                    <h3 class="h6 fw-bold text-white mb-3">Plataforma</h3>
                    <ul class="list-unstyled d-grid gap-2 small">
                        <li><a href="#inicio">Inicio</a></li>
                        <li><a href="#modulos">Módulos y Pilares</a></li>
                        <li><a href="#beneficios">Beneficios</a></li>
                        <li><a href="#equipos">Líderes y Roles</a></li>
                        <li><a href="#preguntas">Preguntas frecuentes</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-4">
                    <h3 class="h6 fw-bold text-white mb-3">Acceso y Contacto</h3>
                    <ul class="list-unstyled d-grid gap-2 small">
                        <li><a href="#contacto">Agendar videollamada</a></li>
                        <li><a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">Hablar por WhatsApp</a></li>
                        <li><a href="{{ url('/registro') }}">Registro con invitación</a></li>
                        <li><a href="{{ url('/admin/login') }}">Acceso administración central</a></li>
                    </ul>
                </div>
            </div>
            <div class="pt-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 small">
                <span>© {{ date('Y') }} REDIL Cloud. Todos los derechos reservados.</span>
                <span>Tecnología en la nube al servicio de tu congregación.</span>
            </div>
        </div>
    </footer>

    <!-- Botón flotante de WhatsApp -->
    <a href="{{ $whatsappUrl }}"
       target="_blank"
       rel="noopener noreferrer"
       class="whatsapp-fab"
       aria-label="Chatear con un asesor de REDIL Cloud por WhatsApp">
        <i class="bi bi-whatsapp fs-4" aria-hidden="true"></i>
        <span>¿Hablamos por WhatsApp?</span>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const carousel = document.getElementById('heroCarousel');
            if (carousel) {
                const slides = [...carousel.querySelectorAll('.hero-slide')];
                const dots = [...carousel.querySelectorAll('[data-carousel-index]')];
                const pauseButton = carousel.querySelector('[data-carousel-pause]');
                const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
                let current = 0;
                let paused = motion.matches;
                let timer;
                let visible = true;
                const schedule = () => {
                    clearTimeout(timer);
                    if (!paused && !document.hidden && visible && !carousel.matches(':hover') && !carousel.contains(document.activeElement)) {
                        timer = setTimeout(() => { show(current + 1); }, 6500);
                    }
                };
                const show = (index) => {
                    current = (index + slides.length) % slides.length;
                    slides.forEach((slide, i) => {
                        slide.classList.toggle('is-active', i === current);
                        slide.setAttribute('aria-hidden', String(i !== current));
                        if (i === current) { slide.querySelector('img').loading = 'eager'; }
                        dots[i].setAttribute('aria-current', String(i === current));
                    });
                    schedule();
                };
                const updatePause = () => {
                    pauseButton.setAttribute('aria-label', paused ? 'Reproducir carrusel' : 'Pausar carrusel');
                    pauseButton.querySelector('i').className = paused ? 'bi bi-play-fill' : 'bi bi-pause-fill';
                    carousel.querySelector('.hero-slides').setAttribute('aria-live', paused ? 'polite' : 'off');
                    schedule();
                };
                const navigate = (index) => { paused = true; show(index); updatePause(); };
                dots.forEach((dot, i) => dot.addEventListener('click', () => navigate(i)));
                carousel.querySelector('[data-carousel-prev]').addEventListener('click', () => navigate(current - 1));
                carousel.querySelector('[data-carousel-next]').addEventListener('click', () => navigate(current + 1));
                pauseButton.addEventListener('click', () => { paused = !paused; updatePause(); });
                carousel.addEventListener('keydown', (event) => {
                    if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
                        event.preventDefault();
                        navigate(current + (event.key === 'ArrowRight' ? 1 : -1));
                    }
                });
                let touchStart = null;
                carousel.addEventListener('touchstart', (event) => { touchStart = event.touches[0].clientX; }, { passive: true });
                carousel.addEventListener('touchend', (event) => {
                    if (touchStart !== null) {
                        const delta = event.changedTouches[0].clientX - touchStart;
                        if (Math.abs(delta) > 50) { navigate(current + (delta < 0 ? 1 : -1)); }
                        touchStart = null;
                    }
                }, { passive: true });
                carousel.addEventListener('mouseenter', () => clearTimeout(timer));
                carousel.addEventListener('mouseleave', schedule);
                carousel.addEventListener('focusin', () => clearTimeout(timer));
                carousel.addEventListener('focusout', () => setTimeout(schedule, 0));
                document.addEventListener('visibilitychange', schedule);
                motion.addEventListener('change', () => { if (motion.matches) { paused = true; updatePause(); } });
                if ('IntersectionObserver' in window) {
                    new IntersectionObserver(([entry]) => { visible = entry.isIntersecting; schedule(); }).observe(carousel);
                }
                carousel.querySelector('.hero-carousel-controls').hidden = false;
                updatePause();
            }

            const prefersMotion = window.matchMedia('(prefers-reduced-motion: no-preference)').matches;

            // 1. Fallback del indicador de progreso de scroll
            if (prefersMotion && !CSS.supports('animation-timeline', 'scroll()')) {
                const progress = document.getElementById('scroll-progress');
                if (progress) {
                    const updateProgress = () => {
                        const scrollable = document.documentElement.scrollHeight - window.innerHeight;
                        const ratio = scrollable > 0 ? Math.min(1, Math.max(0, window.scrollY / scrollable)) : 0;
                        progress.style.transform = `scaleX(${ratio})`;
                    };
                    window.addEventListener('scroll', updateProgress, { passive: true });
                    updateProgress();
                }
            }

            // 2. Fallback del header sticky en scroll
            if (!prefersMotion || !CSS.supports('(animation-timeline: scroll()) and (animation-range: 0% 100%)')) {
                const navbar = document.getElementById('landingNavbar');
                if (navbar) {
                    const toggleScrolled = () => navbar.classList.toggle('is-scrolled', window.scrollY > 36);
                    window.addEventListener('scroll', toggleScrolled, { passive: true });
                    toggleScrolled();
                }
            }

            const motionPreference = window.matchMedia('(prefers-reduced-motion: no-preference)');
            if (prefersMotion && 'IntersectionObserver' in window) {
                const elements = document.querySelectorAll('.reveal-up, .reveal-scale, .reveal-left, .reveal-right, .reveal-ally');
                const observer = new IntersectionObserver((entries, obs) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            obs.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0, rootMargin: '0px 0px -40px 0px' });
                elements.forEach((element) => {
                    if (element.getBoundingClientRect().top >= window.innerHeight) {
                        element.classList.add('reveal-pending');
                        observer.observe(element);
                    }
                });
                motionPreference.addEventListener('change', () => {
                    if (!motionPreference.matches) {
                        observer.disconnect();
                        elements.forEach((element) => element.classList.remove('reveal-pending'));
                    }
                });
            }

            const navLinks = Array.from(document.querySelectorAll('.landing-nav-link'));
            const navSections = navLinks.map((link) => document.querySelector(link.hash)).filter(Boolean);
            let navigationFrame = null;
            const updateNavigation = () => {
                const activeSection = navSections.filter((section) => section.getBoundingClientRect().top <= 180).at(-1);
                navLinks.forEach((link) => {
                    if (activeSection && link.hash === `#${activeSection.id}`) {
                        link.setAttribute('aria-current', 'location');
                    } else {
                        link.removeAttribute('aria-current');
                    }
                });
                navigationFrame = null;
            };
            window.addEventListener('scroll', () => {
                if (navigationFrame === null) {
                    navigationFrame = requestAnimationFrame(updateNavigation);
                }
            }, { passive: true });
            updateNavigation();

            document.querySelectorAll('#navbarLanding a[href^="#"]').forEach((link) => {
                link.addEventListener('click', () => {
                    const menu = document.getElementById('navbarLanding');
                    if (menu.classList.contains('show') && window.bootstrap) {
                        bootstrap.Collapse.getOrCreateInstance(menu).hide();
                    }
                });
            });

            // 4. Tabs accesibles de los 5 pilares (teclado + beforematch + scrollIntoView)
            const tablist = document.getElementById('pillarTablist');
            if (tablist) {
                const tabs = Array.from(tablist.querySelectorAll('[role="tab"]'));
                const panels = Array.from(document.querySelectorAll('[role="tabpanel"][data-panel]'));

                const activateTab = (targetTab, shouldFocus = false) => {
                    const key = targetTab.dataset.pillar;
                    tabs.forEach((tab) => {
                        const selected = tab === targetTab;
                        tab.setAttribute('aria-selected', selected ? 'true' : 'false');
                        tab.tabIndex = selected ? 0 : -1;
                    });
                    panels.forEach((panel) => {
                        const isMatch = panel.dataset.panel === key;
                        if (isMatch) {
                            panel.removeAttribute('hidden');
                            panel.tabIndex = 0;
                        } else {
                            panel.setAttribute('hidden', 'until-found');
                            panel.removeAttribute('tabindex');
                        }
                    });
                    targetTab.scrollIntoView({ block: 'nearest', inline: 'nearest' });
                    if (shouldFocus) {
                        targetTab.focus();
                    }
                };

                tabs.forEach((tab, index) => {
                    tab.addEventListener('click', () => activateTab(tab));
                    tab.addEventListener('keydown', (event) => {
                        if (event.key === 'ArrowRight') {
                            event.preventDefault();
                            activateTab(tabs[(index + 1) % tabs.length], true);
                        } else if (event.key === 'ArrowLeft') {
                            event.preventDefault();
                            activateTab(tabs[(index - 1 + tabs.length) % tabs.length], true);
                        } else if (event.key === 'Home') {
                            event.preventDefault();
                            activateTab(tabs[0], true);
                        } else if (event.key === 'End') {
                            event.preventDefault();
                            activateTab(tabs[tabs.length - 1], true);
                        }
                    });
                });

                panels.forEach((panel) => {
                    panel.addEventListener('beforematch', () => {
                        const matchTab = tabs.find((tab) => tab.dataset.pillar === panel.dataset.panel);
                        if (matchTab) {
                            activateTab(matchTab);
                        }
                    });
                });
            }

            // 5. Conectar CTAs de cada pilar con el selector de módulo del formulario Livewire
            document.querySelectorAll('[data-select-module]').forEach((link) => {
                link.addEventListener('click', () => {
                    const modulo = link.getAttribute('data-select-module');
                    if (modulo && window.Livewire) {
                        window.Livewire.dispatch('seleccionar-modulo-interes', { modulo });
                    }
                });
            });
        });
    </script>
</body>
</html>
