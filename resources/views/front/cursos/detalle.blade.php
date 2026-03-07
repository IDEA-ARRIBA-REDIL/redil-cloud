@extends('layouts/blankLayout')

@section('title', $curso->nombre)

@section('page-style')
    <style>
        .course-hero {
            background-color: #f5f5f9;
            border-radius: 10px;
            padding: 2rem;
        }

        .video-playlist-item {
            transition: background-color 0.2s;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .video-playlist-item:hover {
            background-color: #fff;
            border-color: #e2e8f0;
            box-shadow: 0 0.125rem 0.25rem rgba(165, 163, 174, 0.3);
        }

        .playlist-container {
            max-height: 400px;
            overflow-y: auto;
        }

        /* Custom Scrollbar for playlist */
        .playlist-container::-webkit-scrollbar {
            width: 6px;
        }
        .playlist-container::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        .playlist-container::-webkit-scrollbar-thumb {
            background: #cdd4d9;
            border-radius: 10px;
        }
        .playlist-container::-webkit-scrollbar-thumb:hover {
            background: #a8b1b8;
        }

        .learning-box {
            background-color: #f7f6f9;
            border: 1px dashed #d9d8e7;
            border-radius: 10px;
            padding: 2rem;
        }

        .sticky-sidebar {
            position: -webkit-sticky;
            position: sticky;
            top: 2rem;
        }

        .price-text {
            color: #e83e8c;
            font-size: 2rem;
            font-weight: 700;
        }

        .cta-button {
            background-color: #e83e8c;
            border-color: #e83e8c;
            color: white;
            font-weight: 600;
        }
        .cta-button:hover {
            background-color: #d63384;
            border-color: #d63384;
            color: white;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid py-4" style="background-color: #fdfdfd;">
    <div class="container">

        <!-- HEADER / HERO -->
        <div class="course-hero mb-5">
            <div class="row">
                <!-- Video / Image Preview -->
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <div class="text-muted small mb-2">
                        Inicio | Crecimiento espiritual | {{ $curso->nombre }}
                    </div>

                    @if($curso->video_preview_url)
                        <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm">
                            <iframe src="{{ $curso->video_preview_url }}" title="YouTube video" allowfullscreen></iframe>
                        </div>
                    @else
                        <!-- Dummy Video Player Image for mockup -->
                        <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm bg-dark position-relative d-flex align-items-center justify-content-center">
                            @if($curso->imagen_portada)
                                <img src="{{ \Storage::url($configuracion->ruta_almacenamiento.'/img/cursos/'.$curso->imagen_portada) }}" alt="{{ $curso->nombre }}" class="img-fluid w-100 h-100" style="object-fit: cover; opacity: 0.7;">
                            @else
                                <img src="https://via.placeholder.com/800x450/343a40/ffffff?text=Video+del+Curso" alt="Video Preview" class="img-fluid w-100 h-100" style="object-fit: cover; opacity: 0.7;">
                            @endif
                            <i class="ti ti-player-play-filled position-absolute text-white" style="font-size: 4rem; opacity: 0.8; filter: drop-shadow(0 0 10px rgba(0,0,0,0.5)); cursor: pointer; color: #e83e8c !important;"></i>
                        </div>
                    @endif

                    <h3 class="mt-4 fw-bold text-dark">{{ $curso->nombre }}</h3>
                </div>

                <!-- Playlist -->
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3">Videos del curso</h5>
                    <div class="playlist-container pe-2">
                        <!-- Dummy Playlist Items -->
                        @for($i = 1; $i <= 6; $i++)
                            <div class="video-playlist-item d-flex align-items-center mb-2 p-2 rounded {{ $i == 1 ? 'bg-white shadow-sm border-light' : '' }}">
                                <div class="position-relative me-3">
                                    <img src="https://via.placeholder.com/120x68/e9ecef/495057?text=V{{ $i }}" alt="Thumb" class="rounded" width="100">
                                    @if($i == 1)
                                        <div class="position-absolute top-50 start-50 translate-middle text-white bg-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; opacity: 0.8;">
                                            <i class="ti ti-player-play text-white" style="font-size: 0.8rem;"></i>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <h6 class="mb-1 text-dark {{ $i == 1 ? 'fw-bold' : '' }}" style="font-size: 0.95rem;">Lección Dummy {{ $i }}</h6>
                                    <small class="text-muted" style="color: #e83e8c !important;">{{ rand(3, 15) }}:{{ str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) }}</small>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- MAIN COURSE CONTENT (Left) -->
            <div class="col-lg-8 pe-lg-5 mb-5 mb-lg-0">

                <!-- Meta tags -->
                <div class="d-flex align-items-center mb-4 text-muted small">
                    <div class="me-4"><i class="ti ti-layout-grid me-1"></i> Crecimiento</div>
                    <div><i class="ti ti-clock me-1"></i>
                        @if($curso->duracion_estimada_dias >= 30)
                            {{ round($curso->duracion_estimada_dias / 30) }} Meses
                        @else
                            {{ $curso->duracion_estimada_dias }} Días
                        @endif
                    </div>
                </div>

                <!-- Descripcion -->
                <h4 class="fw-bold mb-3">Descripción</h4>
                <div class="text-muted mb-5 text-justify" style="line-height: 1.8;">
                    {!! $curso->descripcion_larga !!}
                </div>

                <!-- Qué aprenderas -->
                @if($curso->aprendizajes->count() > 0)
                    <div class="learning-box mb-5">
                        <h5 class="fw-bold mb-4">Qué aprenderás en este curso?</h5>
                        <div class="row">
                            @foreach($curso->aprendizajes as $aprendizaje)
                                <div class="col-md-6 mb-3 d-flex align-items-start">
                                    <i class="ti ti-circle-check-filled mt-1 me-2 text-primary" style="font-size: 1.2rem;"></i>
                                    <span class="text-muted small">{{ $aprendizaje->texto }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Perfil y Requerimientos -->
                <div class="row mb-5">
                    <div class="col-md-6 mb-4 mb-md-0">
                        <h5 class="fw-bold mb-4">Quién puede tomar este curso</h5>
                        <ul class="list-unstyled text-muted small">
                            <!-- Lógica para transformar restricciones a texto -->
                            @php
                                $restricciones = [];

                                if($curso->rangosEdad->count() > 0) {
                                    $rangos = $curso->rangosEdad->pluck('nombre')->join(', ');
                                    $restricciones[] = "Para edades entre: " . $rangos;
                                }

                                if($curso->estadosCiviles->count() > 0) {
                                    $civiles = $curso->estadosCiviles->pluck('nombre')->join(' o ');
                                    $restricciones[] = "Estado Civil: " . $civiles;
                                }

                                if($curso->genero) {
                                    $restricciones[] = "Solo para género: " . ($curso->genero == 'M' ? 'Masculino' : 'Femenino');
                                }
                            @endphp

                            @if(count($restricciones) > 0)
                                @foreach($restricciones as $restriccion)
                                    <li class="mb-2 d-flex"><i class="ti ti-point text-danger me-2"></i> {{ $restriccion }}</li>
                                @endforeach
                            @else
                                <li class="mb-2 d-flex"><i class="ti ti-point text-danger me-2"></i> Abierto para todo público</li>
                            @endif
                        </ul>
                    </div>

                    <div class="col-md-6">
                        <h5 class="fw-bold mb-4">Requerimientos del curso</h5>
                        <ul class="list-unstyled text-muted small">
                            @php
                                $requisitos = [];

                                foreach($curso->pasosRequisito as $paso) {
                                    $requisitos[] = "Haber completado: " . $paso->nombre;
                                }

                                foreach($curso->tareasRequisito as $tarea) {
                                    $requisitos[] = "Tener requisito: " . $tarea->nombre;
                                }
                            @endphp

                            @if(count($requisitos) > 0)
                                @foreach($requisitos as $req)
                                    <li class="mb-2 d-flex"><i class="ti ti-point text-danger me-2"></i> {{ $req }}</li>
                                @endforeach
                            @else
                                <li class="mb-2 d-flex"><i class="ti ti-point text-danger me-2"></i> Sin requerimientos previos</li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Acordeón Contenido del Curso (Dummy Data) -->
                <h4 class="fw-bold mb-4">Contenido del curso</h4>
                <div class="d-flex justify-content-end mb-3 text-muted small">
                    <span class="me-3"><i class="ti ti-folder me-1"></i> 3 Secciones</span>
                    <span class="me-3"><i class="ti ti-video me-1"></i> 15 lecciones</span>
                    <span><i class="ti ti-clock me-1"></i> 2h 45m</span>
                </div>

                <div class="accordion accordion-header-primary" id="courseContentAccordion">
                    <!-- Seccion Dummy 1 -->
                    <div class="accordion-item border mb-2 shadow-none">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne" style="color: #ff6b6b;">
                                Getting Started
                                <div class="ms-auto me-3 d-flex align-items-center text-muted small" style="font-size: 0.8rem; font-weight: normal;">
                                    <span class="me-3"><i class="ti ti-video"></i> 4 lectures</span>
                                    <span><i class="ti ti-clock"></i> 51m</span>
                                </div>
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#courseContentAccordion">
                            <div class="accordion-body p-0">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 border-0 border-bottom">
                                        <div class="d-flex align-items-center text-muted small">
                                            <i class="ti ti-player-play-filled me-3 text-dark"></i>
                                            Introduction to the course
                                        </div>
                                        <span class="text-muted small">07:31</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 border-0 border-bottom">
                                        <div class="d-flex align-items-center text-muted small">
                                            <i class="ti ti-player-play-filled me-3 text-dark"></i>
                                            Setup environment
                                        </div>
                                        <span class="text-muted small">12:15</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 border-0 border-bottom">
                                        <div class="d-flex align-items-center text-muted small">
                                            <i class="ti ti-file-description me-3 text-dark"></i>
                                            Course Terms & Conditions
                                        </div>
                                        <span class="text-muted small">5.3 MB</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Seccion Dummy 2 -->
                    <div class="accordion-item border mb-2 shadow-none">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed text-dark fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Secret of Good Design
                                <div class="ms-auto me-3 d-flex align-items-center text-muted small" style="font-size: 0.8rem; font-weight: normal;">
                                    <span class="me-3"><i class="ti ti-video"></i> 5 lectures</span>
                                    <span><i class="ti ti-clock"></i> 1h 20m</span>
                                </div>
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#courseContentAccordion">
                            <div class="accordion-body p-0">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 border-0">
                                        <div class="d-flex align-items-center text-muted small">
                                            <i class="ti ti-player-play-filled me-3 text-dark"></i>
                                            Typography Basics
                                        </div>
                                        <span class="text-muted small">15:00</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- SIDEBAR DETAILS CARD (Right) -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 sticky-sidebar">
                    <div class="card-body p-4">

                        <!-- Precio -->
                        <div class="mb-4 text-center text-lg-start">
                            @if($curso->es_gratuito)
                                <div class="price-text">Gratis</div>
                            @else
                                <div class="price-text">$ {{ number_format($curso->precio, 0, ',', '.') }}</div>
                            @endif
                        </div>

                        <!-- Detalles -->
                        <ul class="list-group list-group-flush mb-4 small">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 text-muted border-light">
                                <span>Instructor</span>
                                @php
                                    $creador = $curso->equipo->first(fn($miembro) => $miembro->tipoCargo && $miembro->tipoCargo->nombre == 'Creador');
                                @endphp
                                <span class="fw-semibold text-dark text-end">
                                    {{ $creador ? $creador->user->nombre() : 'Plataforma' }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 text-muted border-light">
                                <span>Duración</span>
                                <span class="fw-semibold text-dark text-end">
                                    @if($curso->duracion_estimada_dias >= 30)
                                        {{ round($curso->duracion_estimada_dias / 30) }} meses
                                    @else
                                        {{ $curso->duracion_estimada_dias }} días
                                    @endif
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 text-muted border-light">
                                <span>Lecciones</span>
                                <span class="fw-semibold text-dark text-end">15</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 text-muted border-light">
                                <span>Evaluaciones</span>
                                <span class="fw-semibold text-dark text-end">5</span>
                            </li>
                        </ul>

                        <!-- CTA Boton -->
                        <div class="d-grid gap-2">
                            <button class="btn btn-lg cta-button waves-effect waves-light" type="button">Comprar</button>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
