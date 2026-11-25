@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
    $colorPrimario = \App\Models\ThemeSetting::where('class', 'primary')->value('value') ?? '#0099d9';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Donaciones y Ofrendas Online')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('page-style')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,700;1,9..40,300&display=swap" rel="stylesheet">
<style>
    @font-face {
        font-family: 'BrittanySignature';
        src: url('https://res.cloudinary.com/difwue7wa/raw/upload/v1780004789/brittany-signature-script.regular_t6h0ni.ttf') format('truetype');
        font-weight: normal;
        font-style: normal;
        font-display: swap;
    }
    @font-face {
        font-family: 'CreatoDisplay';
        src: url('https://res.cloudinary.com/difwue7wa/raw/upload/v1780005392/CreatoDisplay-Light_yumiiz.otf') format('opentype');
        font-weight: 300;
        font-display: swap;
    }
    @font-face {
        font-family: 'CreatoDisplay';
        src: url('https://res.cloudinary.com/difwue7wa/raw/upload/v1780005138/CreatoDisplay-Regular_lwbfti.otf') format('opentype');
        font-weight: 400;
        font-display: swap;
    }
    @font-face {
        font-family: 'CreatoDisplay';
        src: url('https://res.cloudinary.com/difwue7wa/raw/upload/v1780005427/CreatoDisplay-ExtraBold_ps2j04.otf') format('opentype');
        font-weight: 800;
        font-display: swap;
    }
    @font-face {
        font-family: 'CreatoDisplay';
        src: url('https://res.cloudinary.com/difwue7wa/raw/upload/v1780005466/CreatoDisplay-Black_ikfdg1.otf') format('opentype');
        font-weight: 900;
        font-display: swap;
    }

    .donaciones-online-wrapper {
        --donacion-primary: {{ $colorPrimario }};
        --k: #040407;
        --k2: #0a0a10;
        --k3: #111118;
        --k4: #17171f;
        --w: #f3f3f1;
        --wm: rgba(243, 243, 241, 0.58);
        --wd: rgba(243, 243, 241, 0.16);
        --wd2: rgba(243, 243, 241, 0.08);
        --cream: #F0EFE9;
        --ink: #0d0d10;
        --inkm: rgba(13, 13, 16, 0.58);
        --inkd: rgba(13, 13, 16, 0.14);
        --b: #0099d9;
        --b2: #29aee3;
        --b-light: #0089c4;
        --bg: rgba(0, 153, 217, 0.10);
        --cd: 'CreatoDisplay', 'DM Sans', sans-serif;
        --sc: 'BrittanySignature', cursive;
        --sa: 'DM Sans', sans-serif;
        --radius: 14px;
        --danger: #C62828;

        background-color: var(--cream);
        color: var(--ink);
        font-family: var(--sa);
        -webkit-font-smoothing: antialiased;
    }

    .donaciones-online-wrapper .noise {
        position: fixed;
        inset: 0;
        pointer-events: none;
        z-index: 9999;
        opacity: 0.025;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.78' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
    }

    .donaciones-online-wrapper .ic {
        width: 20px;
        height: 20px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.6;
        stroke-linecap: round;
        stroke-linejoin: round;
        flex-shrink: 0;
    }

    /* Header superior oscuro */
    .donaciones-online-wrapper .donation-header {
        background: var(--k);
        border-bottom: 1px solid var(--wd2);
        padding: 18px 0;
    }

    .donaciones-online-wrapper .nlogo {
        font-family: var(--cd);
        font-weight: 900;
        font-size: 15px;
        color: var(--w);
        letter-spacing: -0.2px;
        text-decoration: none;
    }

    .donaciones-online-wrapper .nlogo:hover {
        color: var(--w);
    }

    .donaciones-online-wrapper .secure-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 10px;
        letter-spacing: 2.6px;
        text-transform: uppercase;
        color: var(--wm);
    }

    .donaciones-online-wrapper .secure-pill .ic {
        width: 14px;
        height: 14px;
        color: var(--b2);
    }

    .donaciones-online-wrapper .header-auth-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        color: var(--w);
        padding: 6px 14px;
        border-radius: 100px;
        border: 1px solid var(--wd);
        text-decoration: none;
        transition: border-color 0.2s, background-color 0.2s;
    }

    .donaciones-online-wrapper .header-auth-link:hover {
        border-color: var(--b2);
        background-color: rgba(41, 174, 227, 0.12);
        color: var(--w);
    }

    /* Hero oscuro + Stepper */
    .donaciones-online-wrapper .donation-hero {
        background: var(--k);
        color: var(--w);
        padding: 52px 0 40px;
        position: relative;
        overflow: hidden;
    }

    .donaciones-online-wrapper .donation-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse 70% 55% at 30% 30%, rgba(0, 153, 217, 0.15), transparent 70%);
        pointer-events: none;
        z-index: 1;
    }

    .donaciones-online-wrapper .donation-hero-banner {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
        opacity: 0.10;
        pointer-events: none;
        z-index: 0;
    }

    .donaciones-online-wrapper .donation-hero > .container {
        position: relative;
        z-index: 2;
    }

    .donaciones-online-wrapper .eyebrow {
        font-size: 11px;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: var(--b2);
        font-weight: 700;
        margin-bottom: 16px;
    }

    .donaciones-online-wrapper .hero-h {
        font-family: var(--cd);
        font-weight: 900;
        font-size: clamp(38px, 6.2vw, 70px);
        line-height: 0.98;
        letter-spacing: -2px;
        margin: 0;
        color: var(--w);
    }

    .donaciones-online-wrapper .hero-h em {
        font-family: var(--sc);
        font-style: normal;
        color: var(--b2);
        font-size: 1.28em;
        font-weight: 400;
        padding: 0 0.04em;
    }

    .donaciones-online-wrapper .hero-sub {
        margin: 20px 0 0;
        max-width: 470px;
        font-size: 15.5px;
        line-height: 1.68;
        color: var(--wm);
    }

    /* Stepper */
    .donaciones-online-wrapper .steps-wrap {
        margin-top: 42px;
    }

    .donaciones-online-wrapper .steps {
        display: flex;
        align-items: center;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .donaciones-online-wrapper .steps li {
        display: flex;
        align-items: center;
        flex: 1;
    }

    .donaciones-online-wrapper .steps li:last-child {
        flex: 0;
    }

    .donaciones-online-wrapper .step-trigger {
        display: inline-flex;
        align-items: center;
        background: transparent;
        border: 0;
        padding: 0;
        cursor: default;
    }

    .donaciones-online-wrapper .steps li.done .step-trigger {
        cursor: pointer;
    }

    .donaciones-online-wrapper .step-circle {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--cd);
        font-weight: 800;
        font-size: 12px;
        border: 1px solid var(--wd);
        color: var(--wm);
        flex-shrink: 0;
        transition: all 0.2s;
    }

    .donaciones-online-wrapper .step-circle .ic {
        width: 14px;
        height: 14px;
        stroke-width: 2.2;
        display: none;
    }

    .donaciones-online-wrapper .step-label {
        font-size: 10px;
        letter-spacing: 2.6px;
        text-transform: uppercase;
        color: var(--wm);
        margin-left: 12px;
        white-space: nowrap;
        transition: color 0.2s;
    }

    .donaciones-online-wrapper .step-connector {
        height: 1px;
        background: var(--wd);
        flex: 1;
        margin: 0 18px;
        transition: background-color 0.2s;
    }

    .donaciones-online-wrapper .steps li.done .step-circle {
        background: var(--b);
        border-color: var(--b);
        color: #ffffff;
    }

    .donaciones-online-wrapper .steps li.done .step-circle span {
        display: none;
    }

    .donaciones-online-wrapper .steps li.done .step-circle .ic {
        display: block;
    }

    .donaciones-online-wrapper .steps li.done .step-label {
        color: var(--w);
    }

    .donaciones-online-wrapper .steps li.done .step-connector {
        background: var(--b);
    }

    .donaciones-online-wrapper .steps li.active .step-circle {
        background: var(--w);
        border-color: var(--w);
        color: var(--k);
    }

    .donaciones-online-wrapper .steps li.active .step-label {
        color: var(--w);
        font-weight: 700;
    }

    .donaciones-online-wrapper .steps-mobile {
        display: none;
    }

    .donaciones-online-wrapper .steps-mobile .caption {
        font-size: 10px;
        letter-spacing: 2.6px;
        text-transform: uppercase;
        font-weight: 700;
        color: var(--w);
        margin-bottom: 12px;
    }

    .donaciones-online-wrapper .steps-mobile .track {
        height: 2px;
        background: var(--wd);
        overflow: hidden;
    }

    .donaciones-online-wrapper .steps-mobile .fill {
        height: 100%;
        background: var(--b);
        transition: width 0.3s ease;
    }

    @media (max-width: 760px) {
        .donaciones-online-wrapper .steps-wrap .steps {
            display: none;
        }
        .donaciones-online-wrapper .steps-mobile {
            display: block;
        }
        .donaciones-online-wrapper .donation-hero {
            padding: 36px 0 28px;
        }
        .donaciones-online-wrapper .secure-pill {
            display: none;
        }
    }

    /* Contenido principal (fondo crema) */
    .donaciones-online-wrapper .donation-main {
        padding: 40px 0 88px;
    }

    .donaciones-online-wrapper .donation-card {
        background: #ffffff;
        border: 1px solid var(--inkd);
        border-radius: var(--radius);
        box-shadow: 0 6px 18px rgba(13, 13, 16, 0.04);
    }

    .donaciones-online-wrapper .card-body-pad {
        padding: 36px;
    }

    @media (max-width: 576px) {
        .donaciones-online-wrapper .card-body-pad {
            padding: 22px;
        }
    }

    .donaciones-online-wrapper .step-label-top {
        font-size: 10px;
        letter-spacing: 2.6px;
        text-transform: uppercase;
        color: var(--b-light);
        font-weight: 700;
        margin-bottom: 10px;
    }

    .donaciones-online-wrapper .step-title {
        font-family: var(--cd);
        font-weight: 800;
        font-size: clamp(22px, 2.4vw, 28px);
        line-height: 1.15;
        letter-spacing: -0.4px;
        margin-bottom: 8px;
        color: var(--ink);
    }

    .donaciones-online-wrapper .step-subtitle {
        color: var(--inkm);
        font-size: 15px;
        line-height: 1.65;
        margin-bottom: 26px;
    }

    /* Tarjetas de modalidad (Paso 1) */
    .donaciones-online-wrapper .modality-option {
        background: #ffffff;
        border: 1px solid var(--inkd);
        border-radius: var(--radius);
        padding: 22px;
        cursor: pointer;
        height: 100%;
        transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
        box-shadow: 0 6px 18px rgba(13, 13, 16, 0.04);
    }

    .donaciones-online-wrapper .modality-option:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(13, 13, 16, 0.09);
        border-color: var(--b-light);
    }

    .donaciones-online-wrapper .modality-option.selected {
        border-color: var(--b);
        box-shadow: 0 0 0 1px var(--b), 0 14px 30px rgba(13, 13, 16, 0.06);
    }

    .donaciones-online-wrapper .modality-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: var(--bg);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--b-light);
        margin-bottom: 16px;
        transition: background 0.2s, color 0.2s;
    }

    .donaciones-online-wrapper .modality-icon .ic {
        width: 21px;
        height: 21px;
    }

    .donaciones-online-wrapper .modality-option.selected .modality-icon {
        background: var(--b);
        color: #ffffff;
    }

    .donaciones-online-wrapper .modality-option h6 {
        font-family: var(--cd);
        font-weight: 800;
        font-size: 16px;
        letter-spacing: -0.2px;
        margin-bottom: 8px;
        color: var(--ink);
    }

    .donaciones-online-wrapper .modality-option p {
        font-size: 13px;
        line-height: 1.55;
        color: var(--inkm);
        margin: 0;
    }

    /* Formularios e Inputs */
    .donaciones-online-wrapper label.field-label {
        font-weight: 700;
        font-size: 13px;
        margin-bottom: 8px;
        display: block;
        color: var(--ink);
    }

    .donaciones-online-wrapper .req {
        color: var(--danger);
    }

    .donaciones-online-wrapper .form-select,
    .donaciones-online-wrapper .form-control,
    .donaciones-online-wrapper .input-group-text {
        border-radius: 10px !important;
        border: 1px solid var(--inkd) !important;
        font-family: var(--sa);
        font-size: 14.5px;
        padding: 0.7rem 0.95rem;
        color: var(--ink) !important;
        background-color: #ffffff !important;
        min-height: 46px;
    }

    .donaciones-online-wrapper .form-control:read-only,
    .donaciones-online-wrapper .form-control:disabled,
    .donaciones-online-wrapper .form-select:disabled {
        background-color: #f5f5f4 !important;
        color: var(--inkm) !important;
        cursor: not-allowed;
    }

    .donaciones-online-wrapper .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
        border-right: 0 !important;
        background: var(--cream) !important;
        color: var(--inkm) !important;
        font-weight: 700;
    }

    .donaciones-online-wrapper .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    .donaciones-online-wrapper .form-control::placeholder {
        color: rgba(13, 13, 16, 0.42);
    }

    .donaciones-online-wrapper .form-select:focus,
    .donaciones-online-wrapper .form-control:focus {
        border-color: var(--b) !important;
        box-shadow: 0 0 0 3px rgba(0, 153, 217, 0.15) !important;
    }

    .donaciones-online-wrapper .form-control.is-invalid {
        border-color: var(--danger) !important;
    }

    .donaciones-online-wrapper .form-check-input {
        border: 1px solid rgba(13, 13, 16, 0.35);
    }

    .donaciones-online-wrapper .form-check-input:checked {
        background-color: var(--b) !important;
        border-color: var(--b) !important;
        box-shadow: 0 0 0 3px rgba(0, 153, 217, 0.18) !important;
    }

    .donaciones-online-wrapper .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(0, 153, 217, 0.2) !important;
        border-color: var(--b) !important;
    }

    .donaciones-online-wrapper .policy-link {
        color: var(--b-light) !important;
        font-weight: 700;
        text-decoration: underline;
    }

    /* Select2 adaptado al diseño */
    .donaciones-online-wrapper .select2-container .select2-selection--single {
        height: 46px !important;
        min-height: 46px !important;
        display: flex !important;
        align-items: center !important;
        background-color: #ffffff !important;
        border: 1px solid var(--inkd) !important;
        border-radius: 10px !important;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .donaciones-online-wrapper .select2-container--default.select2-container--focus .select2-selection--single,
    .donaciones-online-wrapper .select2-container--default.select2-container--open .select2-selection--single {
        border-width: 1px !important;
        border-color: var(--b) !important;
        box-shadow: 0 0 0 3px rgba(0, 153, 217, 0.15) !important;
    }

    .donaciones-online-wrapper .select2-container--disabled .select2-selection--single {
        background-color: #f5f5f4 !important;
        color: var(--inkm) !important;
        cursor: not-allowed !important;
    }

    .donaciones-online-wrapper .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: var(--ink) !important;
        font-family: var(--sa);
        font-size: 14.5px;
        line-height: 44px !important;
        padding-left: 0.95rem !important;
        padding-right: 2.2rem !important;
    }

    .donaciones-online-wrapper .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: rgba(13, 13, 16, 0.42) !important;
    }

    .donaciones-online-wrapper .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px !important;
        right: 10px !important;
    }

    /* Botones principales y secundarios */
    .donaciones-online-wrapper .btn-donacion-primary {
        font-family: var(--cd);
        font-weight: 700;
        font-size: 13.5px;
        letter-spacing: 0.3px;
        padding: 14px 28px;
        border-radius: 100px !important;
        background: var(--b) !important;
        color: #ffffff !important;
        border: none !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: transform 0.2s, background-color 0.2s, opacity 0.2s;
        text-decoration: none;
    }

    .donaciones-online-wrapper .btn-donacion-primary:hover,
    .donaciones-online-wrapper .btn-donacion-primary:focus {
        background: var(--b2) !important;
        color: #ffffff !important;
        transform: translateY(-2px);
    }

    .donaciones-online-wrapper .btn-donacion-primary:disabled {
        opacity: 0.55;
        transform: none;
    }

    .donaciones-online-wrapper .btn-donacion-ghost {
        font-family: var(--cd);
        font-weight: 700;
        font-size: 13.5px;
        letter-spacing: 0.3px;
        padding: 14px 26px;
        border-radius: 100px !important;
        background: transparent !important;
        border: 1px solid var(--inkd) !important;
        color: var(--ink) !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: transform 0.2s, border-color 0.2s;
    }

    .donaciones-online-wrapper .btn-donacion-ghost:hover {
        border-color: var(--ink) !important;
        color: var(--ink) !important;
        transform: translateY(-2px);
    }

    .donaciones-online-wrapper .btn-donacion-sm {
        padding: 9px 18px;
        font-size: 12px;
    }

    /* Banner informativo dentro del card */
    .donaciones-online-wrapper .info-banner {
        background: var(--bg);
        border-radius: 10px;
        padding: 14px 16px;
        font-size: 13.5px;
        line-height: 1.6;
        color: var(--ink);
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 24px;
    }

    .donaciones-online-wrapper .info-banner .ic {
        color: var(--b-light);
        margin-top: 2px;
    }

    .donaciones-online-wrapper .warning-banner {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 10px;
        padding: 14px 16px;
        font-size: 13.5px;
        line-height: 1.55;
        color: #78350f;
    }

    /* Chip del donante (Paso 3) */
    .donaciones-online-wrapper .donor-chip {
        display: flex;
        align-items: center;
        gap: 14px;
        background: var(--cream);
        border-radius: var(--radius);
        padding: 14px 16px;
        margin-bottom: 26px;
    }

    .donaciones-online-wrapper .donor-chip .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--k);
        color: var(--w);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--cd);
        font-weight: 800;
        font-size: 13px;
        flex-shrink: 0;
    }

    .donaciones-online-wrapper .donor-chip .name {
        font-weight: 700;
        font-size: 14.5px;
        color: var(--ink);
    }

    .donaciones-online-wrapper .donor-chip .doc {
        font-size: 12px;
        color: var(--inkm);
    }

    .donaciones-online-wrapper .donor-chip .edit-link {
        margin-left: auto;
        font-family: var(--cd);
        font-weight: 700;
        font-size: 12.5px;
        letter-spacing: 0.3px;
        color: var(--b-light);
        text-decoration: none;
        background: transparent;
        border: 0;
        padding: 4px 8px;
        cursor: pointer;
    }

    .donaciones-online-wrapper .donor-chip .edit-link:hover {
        color: var(--b);
        text-decoration: underline;
    }

    /* Chips de monto rápido (Paso 3) */
    .donaciones-online-wrapper .amount-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 12px;
    }

    .donaciones-online-wrapper .amount-chip {
        font-family: var(--cd);
        font-weight: 700;
        font-size: 12.5px;
        letter-spacing: 0.3px;
        padding: 9px 16px;
        border: 1px solid var(--inkd);
        border-radius: 100px;
        color: var(--ink);
        background: #ffffff;
        cursor: pointer;
        transition: all 0.15s;
    }

    .donaciones-online-wrapper .amount-chip:hover {
        border-color: var(--b-light);
    }

    .donaciones-online-wrapper .amount-chip.active {
        background: var(--b);
        border-color: var(--b);
        color: #ffffff;
    }

    /* Pie del panel */
    .donaciones-online-wrapper .pane-footer {
        border-top: 1px solid var(--inkd);
        margin-top: 30px;
        padding-top: 22px;
    }

    /* Sidebar derecho oscuro (Transparencia) */
    .donaciones-online-wrapper .sidebar-note {
        background: var(--k);
        border: 1px solid var(--wd2);
        border-radius: var(--radius);
        padding: 28px;
        color: var(--wm);
        font-size: 14px;
        position: sticky;
        top: 24px;
    }

    .donaciones-online-wrapper .sidebar-note .eyebrow {
        margin-bottom: 10px;
    }

    .donaciones-online-wrapper .sidebar-note h6 {
        font-family: var(--cd);
        font-weight: 900;
        font-size: 22px;
        letter-spacing: -0.4px;
        line-height: 1.15;
        color: var(--w);
        margin-bottom: 22px;
    }

    .donaciones-online-wrapper .sidebar-note ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .donaciones-online-wrapper .sidebar-note li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 16px;
        line-height: 1.6;
        color: var(--wm);
    }

    .donaciones-online-wrapper .sidebar-note li .ic {
        color: var(--b2);
        margin-top: 3px;
    }

    .donaciones-online-wrapper .sidebar-divider {
        height: 1px;
        background: var(--wd2);
        margin: 22px 0 16px;
    }

    .donaciones-online-wrapper .trust-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
        font-size: 12px;
        color: var(--wm);
    }

    .donaciones-online-wrapper .trust-row .ic {
        color: var(--b2);
        width: 15px;
        height: 15px;
    }

    /* Animación suave entre pasos */
    .donaciones-online-wrapper .step-pane-fade {
        animation: donacionFadeIn 0.25s ease;
    }

    @keyframes donacionFadeIn {
        from {
            opacity: 0;
            transform: translateY(6px);
        }
        to {
            opacity: 1;
            transform: none;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .donaciones-online-wrapper .step-pane-fade {
            animation: none;
        }
    }
</style>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
<script>
    window.initDonacionSelect2 = function (el, wire, propName, placeholder) {
        const setup = () => {
            if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.select2 === 'undefined') {
                setTimeout(setup, 50);
                return;
            }
            const $el = window.jQuery(el);
            if (!$el.length) {
                return;
            }
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }
            $el.select2({
                width: '100%',
                placeholder: placeholder || 'Selecciona una opción',
                language: {
                    noResults: function () {
                        return 'No se encontraron resultados';
                    },
                    searching: function () {
                        return 'Buscando...';
                    }
                }
            }).off('change.donacionSelect2').on('change.donacionSelect2', function () {
                wire.set(propName, window.jQuery(this).val());
            });
        };
        setup();
    };
</script>
@endsection

@section('content')
<div
    class="min-vh-100 donaciones-online-wrapper"
    @if(!empty($configuracion?->banner_donacion_ofrenda_url))
        style="--donacion-hero-bg: url('{{ $configuracion->banner_donacion_ofrenda_url }}');"
    @endif
>
    <div class="noise" aria-hidden="true"></div>

    {{-- Encabezado superior oscuro --}}
    <header class="donation-header">
        <div class="container d-flex align-items-center justify-content-between" style="max-width: 1080px;">
            <a href="{{ url('/') }}" class="nlogo d-inline-flex align-items-center gap-2">
                {{ strtoupper($iglesia?->nombre ?? config('app.name')) }}
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="secure-pill">
                    <svg class="ic" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Pago 100% seguro
                </span>
                @auth
                    <a href="{{ route('dashboard') }}" class="header-auth-link">
                        <i class="ti ti-layout-dashboard ti-xs"></i> Mi cuenta
                    </a>
                @else
                    <a href="{{ route('login') }}" class="header-auth-link">
                        <i class="ti ti-login ti-xs"></i> Iniciar sesión
                    </a>
                @endauth
            </div>
        </div>
    </header>

    @if(session('error'))
        <div class="bg-dark border-bottom border-secondary py-3">
            <div class="container" style="max-width: 1080px;">
                <div class="alert alert-danger mb-0">
                    <i class="ti ti-alert-circle me-1"></i> {{ session('error') }}
                </div>
            </div>
        </div>
    @endif

    @livewire('ofrendas.donaciones-online')
</div>
@endsection
