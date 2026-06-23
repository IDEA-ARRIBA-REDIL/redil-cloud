<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Software para Iglesias - REDIL Cloud</title>
    <meta name="description" content="Administra tu congregación en tiempo real. Pastoreo inteligente para iglesias de todos los tamaños.">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Google Fonts: Afacad Flux & Lexend Deca -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Lexend+Deca:wght@100..900&display=swap" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800;900&family=Open+Sans:wght@400;600;700&display=swap');

        :root {
            --primary: #63e3bb; /* The vibrant teal from the design */
            --primary-dark: #63e3bb;
            --text-dark: #111827;
            --text-gray: #6b7280;
            --bg-light-grey: #f3f8fb; /* Top section, problems, team */
        }

        body {
            font-family: "Lexend Deca", sans-serif;
            font-optical-sizing: auto;
            font-style: normal;
            color: var(--text-gray);
            -webkit-font-smoothing: antialiased;
        }



        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: "Lexend Deca", sans-serif;
            font-optical-sizing: auto;
            font-style: normal;
            color: var(--text-dark);
        }

        .text-primary { color: var(--primary) !important; }
        .text-lista { color: #00E4BB !important; }
        .text-formulario::placeholder {
          color: #A9AEB3 !important;
          opacity: 1;
        }
        .text-formulario { color: #A9AEB3 !important; }
        .bg-primary { background-color: var(--primary) !important; }
        .text-dark { color: var(--text-dark) !important; }
        .bg-light-grey { background-color: var(--bg-light-grey) !important; }
        .bg-contacto{background-color:#00a78b}
        .bg-footer{background-color:#000a0b}

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        /* Rounded elements */
        .rounded-4 { border-radius: 1rem !important; }
        .rounded-5 { border-radius: 1.5rem !important; }

        /* Play Button */
        .play-btn-wrapper:hover .play-btn {
            transform: scale(1.05);
        }
        .play-btn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            color: var(--primary);
            font-size: 1.5rem;
            transition: transform 0.2s;
        }

        /* Feature grid icons */
        .feature-icon-box {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background-color: rgba(0, 208, 156, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: var(--primary);
            flex-shrink: 0;
        }

        /* Forms */
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(0, 208, 156, 0.25);
        }
        .form-control, .form-select {
            border-color: #e5e7eb;
        }

        /* Team Image Slicing */
        .team-img-container {
            width: 100%;
            padding-top: 75%; /* 4:3 aspect ratio roughly */
            position: relative;
            overflow: hidden;
            background-color: #e5e7eb;
        }
        .team-img {
            position: absolute;
            width: 200%;
            height: 200%;
            object-fit: cover;
        }
        .team-img-1 { top: 0; left: 0; }
        .team-img-2 { top: 0; left: -100%; }
        .team-img-3 { top: -100%; left: 0; }
        .team-img-4 { top: -100%; left: -100%; }

        .fw-bolder{font-weight: 900 !important;}
        /* Hover effects */
        .hover-white:hover { color: white !important; }

        .bg-hero{
            background-color: #f3f8fb;
        }

        .hero-section {
            background-image: url('{{ Storage::disk("global_media")->url("/landing/imagen 1 (1).png") }}');
            background-position: right -50px bottom;
            background-repeat: no-repeat;
            background-size: 68%;
        }

        @media (max-width: 991px) {
            .bg-hero-row {
                background-position: bottom center;
                background-size: 120%;
            }
            .hero-mobile-space {
                min-height: 380px;
            }
        }
        .bg-feature1-row {
            background-image: url('{{ Storage::disk("global_media")->url("/landing/imagen-4.png") }}');
            background-position: right -50px center;
            background-repeat: no-repeat;
            background-size: 55%;
        }

        @media (max-width: 991px) {
            .bg-feature1-row {
                background-position: bottom center;
                background-size: 100%;
            }
            .feature1-mobile-space {
                min-height: 380px;
            }
        }
        /* Navbar active state */
        .navbar-brand {
            text-decoration: none;
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light-white pt-4 pb-3">
        <div class="container">
            <!-- Custom Logo as SVG -->
            <a class="navbar-brand d-flex align-items-center" href="#inicio">
                <img style="width:100px !important" src="{{ Storage::disk('global_media')->url('OPCION 1 NEGRO (1).png') }}">
                <div class="ms-2 lh-1 text-dark">

                </div>
            </a>
            <button class="navbar-toggler border-0 p-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list fs-1 text-dark"></i>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center gap-3 fw-semibold">
                    <li class="nav-item"><a class="nav-link text-dark" href="#inicio">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link text-dark" href="#beneficios">Beneficios</a></li>
                    <li class="nav-item"><a class="nav-link text-dark" href="#caracteristicas">Características</a></li>
                    <li class="nav-item"><a class="nav-link text-dark" href="#modulos">Módulos</a></li>
                    <li class="nav-item ms-lg-3"><a class="btn rounded-pill px-4 py-2 fw-bold text-dark shadow-sm" style="background-color: #00E4BB !important;" href="#contacto">Contacto</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section id="inicio" class="hero-section pb-5 pt-4" style="background-color: #F3F8FB; overflow: hidden;">
        <div class="container">
            <div class="row align-items-center pb-5 pb-lg-0">
                <div class="col-lg-6 mb-5 mb-lg-0 pt-5 pb-lg-5 position-relative" style="z-index: 2;">
                    <h1 class="fw-bolder mb-4" style="font-size: 3.8rem; line-height: 1.1; letter-spacing: -1px;">
                        El software más<br>
                        inteligente para<br>
                        tu <span style="color:#00B596">congregación</span>
                    </h1>
                    <p class="fs-5 text-dark mb-5 pe-lg-5" style="line-height: 1.6;">
                        Administra tu congregación en tiempo real desde cualquier dispositivo. Pastoreo inteligente para iglesias de todos los tamaños.
                    </p>
                    <div class="d-flex align-items-center gap-4 mb-5">
                        <a href="#" style="background-color: #00E4BB !important;" class="btn  rounded-pill text-dark px-4 py-3 fw-bold text-white fs-6 shadow-sm">Solicitar demo gratis</a>
                        <a href="#" class="play-btn-wrapper d-flex align-items-center text-dark text-decoration-none fw-bold fs-6">
                            <div class="play-btn"><i class="bi bi-play-fill"></i></div>
                            <span class="ms-3">Ver modulos</span>
                        </a>
                    </div>
                    <div class="d-flex flex-wrap gap-4  text-dark fs-6">
                        <span class="d-flex align-items-center"><i class="bi bi-check2 text-primary fs-4 me-1"></i> Facil de usar</span>
                        <span class="d-flex align-items-center"><i class="bi bi-check2 text-primary fs-4 me-1"></i> Soporte en español</span>
                        <span class="d-flex align-items-center"><i class="bi bi-check2 text-primary fs-4 me-1"></i> Seguro y Confiable</span>
                    </div>
                </div>
                <div class="col-lg-6">
                </div>
            </div>
        </div>
    </section>

    <!-- STATS SECTION -->
    <section class="stats-section py-5 bg-white border-bottom border-light">
        <div class="container my-4">
            <div class="row text-center">
                <div class="col-md-4 border-end border-light">
                    <h2 class="fw-bolder mb-2" style="font-size: 3.8rem; letter-spacing: -1px;"><span class="text-primary fs-3  fw-bold">↑</span> 13</h2>
                    <h5 class="fw-bold mb-3 text-dark">Años al servicio</h5>
                    <p class="text-secondary small px-lg-4 mb-0 fw-semibold">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do</p>
                </div>
                <div class="col-md-4 border-end border-light mt-4 mt-md-0">
                    <h2 class="fw-bolder mb-2" style="font-size: 3.8rem; letter-spacing: -1px;"><span class="text-primary fs-3  fw-bold">↑</span> +500</h2>
                    <h5 class="fw-bold mb-3 text-dark">Iglesias Activas</h5>
                    <p class="text-secondary small px-lg-4 mb-0 fw-semibold">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do</p>
                </div>
                <div class="col-md-4 mt-4 mt-md-0">
                    <h2 class="fw-bolder mb-2" style="font-size: 3.8rem; letter-spacing: -1px;"><span class="text-primary fs-3  fw-bold">↑</span> 99%</h2>
                    <h5 class="fw-bold mb-3 text-dark">Satisfacción de Usuarios</h5>
                    <p class="text-secondary small px-lg-4 mb-0 fw-semibold">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do</p>
                </div>
            </div>
        </div>
    </section>

    <!-- PRESENCE SECTION -->
    <section class="presence-section py-5 bg-white">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0 text-center text-lg-start">
                    <img src="{{ Storage::disk('global_media')->url('/landing/imagen-2.png') }}" class="img-fluid" alt="Presencia Global" style="max-width: 95%;">
                </div>
                <div class="col-lg-6 ps-lg-5">
                    <h2 class="fw-bolder mb-4" style="font-size: 3.5rem; line-height: 1.1; letter-spacing: -1px;">
                        Software redil<br>
                        tiene presencia<br>
                        en <span style="color:#00B596">20 paises</span>
                    </h2>
                    <p class="fs-5 text-secondary fw-semibold mb-5 pe-lg-4" style="line-height: 1.6;">
                        Administra tu congregación en tiempo real desde cualquier dispositivo. Pastoreo inteligente para iglesias de todos los tamaños.
                    </p>
                    <div class="d-flex align-items-center gap-4">
                        <a href="#" style="background-color: #00E4BB !important;" class="btn rounded-pill text-dark px-4 py-3 fw-bold text-white fs-6 shadow-sm">Solicitar demo gratis</a>
                        <a href="#" class="play-btn-wrapper d-flex align-items-center text-dark text-decoration-none fw-bold fs-6">
                            <div class="play-btn"><i class="bi bi-play-fill"></i></div>
                            <span class="ms-3">Ver modulos</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ALLY BANNER -->
    <section style="background-color: #00E4BB;" class="ally-banner py-5">
        <div class="container text-center py-5">
            <h6 class="fw-bold text-dark mb-3" style="letter-spacing: 3px;">SOMOS MÁS QUE UNA HERRAMIENTA</h6>
            <h1 class="fw-bolder text-dark mb-0" style="font-size: 4.5rem; letter-spacing: -1px;">SOMOS UN ALIADO</h1>
        </div>
    </section>

    <!-- PROBLEMS SECTION -->
    <section id="beneficios" class="problems-section py-5 bg-light-grey">
        <div class="container py-5 my-4">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <img src="{{ Storage::disk('global_media')->url('/landing/imagen-3.png') }}" class=" w-100" alt="Problemas de organización">
                </div>
                <div class="col-lg-6 ps-lg-5">
                    <h6 style="color:#00B192" class=" fw-bold mb-3" style="letter-spacing: 1px;">¿QUÉ PROBLEMA RESOLVEMOS?</h6>
                    <h2 class="fw-bolder mb-4" style="font-size: 3.2rem; line-height: 1.1; letter-spacing: -1px;">
                        Soluciona los<br>
                        problemas de<br>
                        organización.
                    </h2>
                    <p class="text-secondary fs-5 mb-5 fw-semibold" style="line-height: 1.6;">
                        Redil centraliza toda la información de tu iglesia en un solo lugar, eliminando el desorden y ahorrando tiempo para que puedas enfocarte en lo que realmente importa: las personas.
                    </p>
                    <h6 style="color:#00B192;font-size: 26px;" class=" fw-bold mb-4" style="letter-spacing: 1px;">NO MÁS</h6>
                    <div class="row g-4">
                        <div class="col-sm-6 d-flex align-items-center gap-3">
                            <div style="color:#00B396" class="fs-3"><i class="bi bi-file-earmark-spreadsheet"></i></div>
                            <span class="fw-bold text-dark lh-sm">Registros en papel<br>o Excel</span>
                        </div>
                        <div class="col-sm-6 d-flex align-items-center gap-3">
                            <div style="color:#00B396" class="fs-3"><i class="bi bi-folder-x"></i></div>
                            <span class="fw-bold text-dark lh-sm">Información<br>desorganizada</span>
                        </div>
                        <div class="col-sm-6 d-flex align-items-center gap-3">
                            <div style="color:#00B396" class="fs-3"><i class="bi bi-hourglass-bottom"></i></div>
                            <span class="fw-bold text-dark lh-sm">Falta de tiempo para<br>lo importante</span>
                        </div>
                        <div class="col-sm-6 d-flex align-items-center gap-3">
                            <div style="color:#00B396" class="fs-3"><i class="bi bi-chat-dots"></i></div>
                            <span class="fw-bold text-dark lh-sm">Comunicación<br>ineficiente</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURE 1 (TABLET) -->
    <section id="caracteristicas" class="feature-1-section bg-feature1-row py-5 bg-white" style="overflow: hidden;">
        <div class="container py-5 my-4">
            <div class="row align-items-center  pb-5 pb-lg-0">
                <div class="col-lg-7 mb-5 mb-lg-0 pe-lg-5 position-relative" style="z-index: 2;">
                    <h6 class="text-lista fw-bold mb-3" style="letter-spacing: 1px;">INFORMACIÓN CLARA Y OPORTUNA</h6>
                    <h2 class="fw-bolder mb-4" style="font-size: 3.2rem; line-height: 1.1; letter-spacing: -1px;">
                        Conoce el estado real<br>
                        de tu congregación
                    </h2>
                    <p class="text-secondary fs-5 mb-5 fw-semibold" style="line-height: 1.6;">
                        Accede a datos de asistencia, crecimiento e ingresos financieros en tiempo real, desde cualquier dispositivo y en cualquier momento.
                    </p>
                    <ul class="list-unstyled mb-5 fs-7">
                        <li class="d-flex align-items-start gap-3 mb-4 fw-bold text-secondary">
                            <i class="bi bi-check-circle-fill text-lista mt-1"></i> Métricas de asistencia por grupo y celdas actualizadas al instante.
                        </li>
                        <li class="d-flex align-items-start gap-3 mb-4 fw-bold text-secondary">
                            <i class="bi bi-check-circle-fill text-lista mt-1"></i> Reportes de crecimiento y consolidación en un solo panel.
                        </li>
                        <li class="d-flex align-items-start gap-3 mb-4 fw-bold text-secondary">
                            <i class="bi bi-check-circle-fill text-lista mt-1"></i> Control financiero de diezmos, ofrendas y donaciones.
                        </li>
                    </ul>
                    <a href="#" class="btn btn-primary rounded-pill px-4 py-3 fw-bold text-dark fs-6 shadow-sm">Explorar Funcionalidades</a>
                </div>
                <div class="col-lg-5 feature1-mobile-space">
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURE 2 (TEAM) -->
    <section class="feature-2-section py-5 bg-light-grey">
        <div class="container py-5 my-4">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <!-- The custom Team Card -->
                    <div class="bg-white rounded-5 p-4 shadow-sm " style="border: 1px solid #f0f0f0; ">
                        <h5 class="fw-bold text-center mb-4 text-dark">Equipo de Líderes</h5>
                        <div class="row g-3">
                            <!-- Pastor Principal -->
                            <div class="col-6">
                                <div class="border border-light rounded-4 overflow-hidden">
                                    <div class="team-img-container">

                                    </div>
                                    <div class="p-2 px-3 bg-white d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 24px; height: 24px; background-color: var(--primary);"><i class="bi bi-person-fill" style="font-size: 0.8rem;"></i></div>
                                        <div class="w-100">
                                            <div class="fw-bold text-dark" style="font-size: 0.8rem;">Pastor Principal</div>
                                            <div class="text-secondary d-flex justify-content-between align-items-center" style="font-size: 0.7rem;">
                                                Acceso activo <span class="rounded-circle" style="width: 6px; height: 6px; background-color: var(--primary);"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Lider Jovenes -->
                            <div class="col-6">
                                <div class="border border-light rounded-4 overflow-hidden">
                                    <div class="team-img-container">

                                    </div>
                                    <div class="p-2 px-3 bg-white d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 24px; height: 24px; background-color: var(--primary);"><i class="bi bi-person-fill" style="font-size: 0.8rem;"></i></div>
                                        <div class="w-100">
                                            <div class="fw-bold text-dark" style="font-size: 0.8rem;">Líder Jóvenes</div>
                                            <div class="text-secondary d-flex justify-content-between align-items-center" style="font-size: 0.7rem;">
                                                Acceso activo <span class="rounded-circle" style="width: 6px; height: 6px; background-color: var(--primary);"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Tesoreria -->
                            <div class="col-6">
                                <div class="border border-light rounded-4 overflow-hidden">
                                    <div class="team-img-container">

                                    </div>
                                    <div class="p-2 px-3 bg-white d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 24px; height: 24px; background-color: var(--primary);"><i class="bi bi-person-fill" style="font-size: 0.8rem;"></i></div>
                                        <div class="w-100">
                                            <div class="fw-bold text-dark" style="font-size: 0.8rem;">Tesorería</div>
                                            <div class="text-secondary d-flex justify-content-between align-items-center" style="font-size: 0.7rem;">
                                                Acceso activo <span class="rounded-circle" style="width: 6px; height: 6px; background-color: var(--primary);"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Consolidacion -->
                            <div class="col-6">
                                <div class="border border-light rounded-4 overflow-hidden">
                                    <div class="team-img-container">

                                    </div>
                                    <div class="p-2 px-3 bg-white d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 24px; height: 24px; background-color: var(--primary);"><i class="bi bi-person-fill" style="font-size: 0.8rem;"></i></div>
                                        <div class="w-100">
                                            <div class="fw-bold text-dark" style="font-size: 0.8rem;">Consolidación</div>
                                            <div class="text-secondary d-flex justify-content-between align-items-center" style="font-size: 0.7rem;">
                                                Acceso activo <span class="rounded-circle" style="width: 6px; height: 6px; background-color: var(--primary);"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 ps-lg-5">
                    <h6 style="color:#00B192" class="fw-bold mb-3" style="letter-spacing: 1px;">TRABAJO EN EQUIPO</h6>
                    <h2 class="fw-bolder mb-4" style="font-size: 3.2rem; line-height: 1.1; letter-spacing: -1px;">
                        Cada líder con su<br>
                        propia cuenta
                    </h2>
                    <p class="text-secondary fs-5 mb-5 fw-semibold" style="line-height: 1.6;">
                        REDIL no asigna una sola persona para gestionar todo. Cada líder tiene su propio acceso, limitado a su área de responsabilidad.
                    </p>
                    <div class="d-flex align-items-center gap-4 mb-4">
                        <div style="color:#00B396" class="fs-3"><i class="bi bi-person-badge"></i></div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Roles Personalizados</h5>
                            <p class="text-secondary fw-semibold mb-0">Define permisos por área: pastores, diáconos, tesoreros y más.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-4">
                        <div style="color:#00B396" class="fs-3"><i class="bi bi-shield-check"></i></div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Información Segura</h5>
                            <p class="text-secondary fw-semibold mb-0">Cada usuario solo ve la información correspondiente a su rol.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MODULES SECTION -->
    <section id="modulos" class="modules-section py-5 bg-white">
        <div class="container py-5 my-4">
            <div class="text-center mb-5">
                <h6 style="color:#00B192; letter-spacing: 1px;" class="fw-bold mb-3 text-uppercase">Módulos del Sistema</h6>
                <h2 class="fw-bolder mb-4" style="font-size: 3.2rem; line-height: 1.1; letter-spacing: -1px;">
                    Todo lo que necesita tu<br>
                    iglesia en un solo lugar
                </h2>
            </div>

            <div class="row g-4">
                <!-- Module 1 -->
                <div class="col-lg-4 col-md-6">
                    <div class="p-4 rounded-4 h-100 border-0" style="background-color: #f4f7f9;">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-3" style="width: 48px; height: 48px; background-color: #2bc4a5; color: white; font-size: 1.5rem;">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-3">Gestión de Miembros</h5>
                        <p class="text-secondary fw-semibold mb-0" style="font-size: 0.95rem; line-height: 1.6;">Registro completo de datos personales, seguimiento espiritual y estado de cada miembro de la congregación.</p>
                    </div>
                </div>
                <!-- Module 2 -->
                <div class="col-lg-4 col-md-6">
                    <div class="p-4 rounded-4 h-100 border-0" style="background-color: #f4f7f9;">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-3" style="width: 48px; height: 48px; background-color: #2bc4a5; color: white; font-size: 1.5rem;">
                            <i class="bi bi-calendar2-check-fill"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-3">Asistencia & Eventos</h5>
                        <p class="text-secondary fw-semibold mb-0" style="font-size: 0.95rem; line-height: 1.6;">Registro completo de datos personales, seguimiento espiritual y estado de cada miembro de la congregación.</p>
                    </div>
                </div>
                <!-- Module 3 -->
                <div class="col-lg-4 col-md-6">
                    <div class="p-4 rounded-4 h-100 border-0" style="background-color: #f4f7f9;">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-3" style="width: 48px; height: 48px; background-color: #2bc4a5; color: white; font-size: 1.5rem;">
                            <i class="bi bi-currency-exchange"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-3">Finanzas & Tesorería</h5>
                        <p class="text-secondary fw-semibold mb-0" style="font-size: 0.95rem; line-height: 1.6;">Gestiona diezmos, ofrendas, proyectos y genera reportes financieros con transparencia total.</p>
                    </div>
                </div>
                <!-- Module 4 -->
                <div class="col-lg-4 col-md-6">
                    <div class="p-4 rounded-4 h-100 border-0" style="background-color: #f4f7f9;">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-3" style="width: 48px; height: 48px; background-color: #2bc4a5; color: white; font-size: 1.5rem;">
                            <i class="bi bi-diagram-3-fill"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-3">Grupos & Células</h5>
                        <p class="text-secondary fw-semibold mb-0" style="font-size: 0.95rem; line-height: 1.6;">Organiza grupos de ministerio, redes y células con mapas digitales y estructura visual de la congregación.</p>
                    </div>
                </div>
                <!-- Module 5 -->
                <div class="col-lg-4 col-md-6">
                    <div class="p-4 rounded-4 h-100 border-0" style="background-color: #f4f7f9;">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-3" style="width: 48px; height: 48px; background-color: #2bc4a5; color: white; font-size: 1.5rem;">
                            <i class="bi bi-tree-fill"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-3">Consolidación</h5>
                        <p class="text-secondary fw-semibold mb-0" style="font-size: 0.95rem; line-height: 1.6;">Proceso estructurado para el seguimiento y consolidación de nuevos creyentes hasta su madurez en la fe.</p>
                    </div>
                </div>
                <!-- Module 6 -->
                <div class="col-lg-4 col-md-6">
                    <div class="p-4 rounded-4 h-100 border-0" style="background-color: #f4f7f9;">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-3" style="width: 48px; height: 48px; background-color: #2bc4a5; color: white; font-size: 1.5rem;">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-3">Escuela & LMS</h5>
                        <p class="text-secondary fw-semibold mb-0" style="font-size: 0.95rem; line-height: 1.6;">Plataforma de formación integrada con cursos, evaluaciones y seguimiento del progreso académico espiritual.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTACT SECTION -->
    <section id="contacto" class="contact-section bg-contacto text-white">
        <div style="    padding: 6% 8%;" >
            <div style="background-color:#00d0af" class="row  align-items-center">
                <div class="col-lg-6" style="padding: 8%;
    text-align: center;">
                    <div class="align-items-center gap-3 mb-4">
                        <img style="width:80% !important" src="{{ Storage::disk('global_media')->url('OPCION 1 BLANCO (1).png') }}">
                    </div>

                    <p class="fs-5 mb-5 fw-semibold" style="line-height: 1.6;">
                        Cuéntanos sobre tu congregación y te ayudaremos a encontrar el plan ideal. Respuesta en menos de 24 horas.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="#" class="text-white border border-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.2rem; text-decoration: none;"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white border border-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.2rem; text-decoration: none;"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white border border-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.2rem; text-decoration: none;"><i class="bi bi-tiktok"></i></a>
                        <a href="#" class="text-white border border-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.2rem; text-decoration: none;"><i class="bi bi-pinterest"></i></a>
                    </div>
                </div>
                <div class="col-lg-6 bg-white">
                    <div class=" text-dark  p-4 p-md-5  mx-auto" style="max-width: 650px;">
                        <h4 class="fw-bolder mb-4" style="font-size: 1.8rem;">Envíanos un mensaje</h4>
                        <form>
                            <div class="row g-3 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-dark small mb-1">Nombre de la iglesia</label>
                                    <input type="text" class="form-control bg-white rounded-3 text-formulario py-2 " placeholder="Ej. Iglesia Central">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-dark small mb-1">Nombre del Pastor/Líder</label>
                                    <input type="text" class="form-control bg-white rounded-3 text-formulario py-2 " placeholder="Ej. Iglesia Central">
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-dark small mb-1">E-mail</label>
                                    <input type="email" class="form-control bg-white rounded-3 text-formulario py-2 " placeholder="correo@iglesia.com">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-dark small mb-1">WhatsApp / Teléfono</label>
                                    <input type="text" class="form-control bg-white rounded-3 text-formulario py-2 " placeholder="Ej. Iglesia Central">
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-dark small mb-1">País</label>
                                    <select class="form-select bg-white rounded-3 text-formulario py-2  text-formulario">
                                        <option>Selecciona Tu País...</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-dark small mb-1">Tamaño de la Congregación</label>
                                    <select class="form-select bg-white rounded-3 text-formulario py-2  text-formulario">
                                        <option>Número de miembros...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark small mb-1">Mensaje</label>
                                <textarea class="form-control bg-white rounded-3 text-formulario py-2 " rows="4" placeholder="Cuéntanos sobre tu congregación y en qué podemos ayudarte"></textarea>
                            </div>
                            <div class="d-flex justify-content-center">
                                <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold text-dark w-80 fs-6 shadow-sm mt-2">Enviar Solicitud</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-footer text-white py-5">
        <div class="container pt-5 pb-3">
            <div class="row">
                <div class="col-lg-5 pe-lg-5 mb-5 mb-lg-0">
                    <div class="d-flex align-items-center gap-3 mb-4">
                         <img style="width:50% !important" src="{{ Storage::disk('global_media')->url('OPCION 1 BLANCO (1).png') }}">

                    </div>
                    <p class="text-secondary mb-1 fw-semibold">Software Redil - Pastoreo Inteligente.</p>
                    <p class="text-secondary fw-semibold">Tecnología en la nube para tu congregación.</p>
                </div>
                <div class="col-lg-3 col-sm-6 mb-4 mb-lg-0">
                    <h5 class="fw-bold text-white mb-4">Navegación</h5>
                    <ul class="list-unstyled text-secondary lh-lg fw-semibold">
                        <li><a href="#inicio" class="text-decoration-none text-secondary hover-white">Inicio</a></li>
                        <li><a href="#beneficios" class="text-decoration-none text-secondary hover-white">Beneficios</a></li>
                        <li><a href="#caracteristicas" class="text-decoration-none text-secondary hover-white">Características</a></li>
                        <li><a href="#modulos" class="text-decoration-none text-secondary hover-white">Módulos</a></li>
                        <li><a href="#contacto" class="text-decoration-none text-secondary hover-white">Contacto</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-sm-6 mb-4 mb-lg-0">
                    <h5 class="fw-bold text-white mb-4">Empresa</h5>
                    <ul class="list-unstyled text-secondary lh-lg fw-semibold">
                        <li><a href="#" class="text-decoration-none text-secondary hover-white">Acerca de</a></li>
                        <li><a href="#" class="text-decoration-none text-secondary hover-white">Noticias</a></li>
                        <li><a href="#" class="text-decoration-none text-secondary hover-white">Alianzas</a></li>
                        <li><a href="#" class="text-decoration-none text-secondary hover-white">Contacto</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-5 pt-4 border-top border-secondary text-secondary small fw-semibold">
                © 2026 REDIL Cloud. Todos los derechos reservados.
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS (needed for navbar toggle) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
