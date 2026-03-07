@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Previsualización - ' . $curso->nombre)

@section('page-style')
    <style>
        body {
            background-color: #f8f8fb;
            /* Tono lila super claro para el fondo principal */
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif;
        }

        .course-hero {
            background-color: #EFEBF5;
            /* Un gris muy tenue */
            border-radius: 12px;
            padding: 2rem;
        }

        .video-playlist-item {
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            border: 1px solid transparent;
            background-color: #ffffff;
            opacity: 0.7;
        }

        .video-playlist-item.active,
        .video-playlist-item:hover {
            opacity: 1;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        .playlist-container {
            max-height: 400px;
            overflow-y: auto;
        }

        /* Custom Scrollbar for playlist */
        .playlist-container::-webkit-scrollbar {
            width: 5px;
        }

        .playlist-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .playlist-container::-webkit-scrollbar-thumb {
            background: #d6d6e7;
            border-radius: 10px;
        }

        .learning-box {
            background-color: #f6f0ff;
            /* Fondo lila claro */
            border: 1px dashed #cba7f9;
            /* Borde punteado morado */
            border-radius: 12px;
            padding: 2rem;
        }

        .sticky-sidebar {
            position: -webkit-sticky;
            position: sticky;
            top: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
        }

       

       


        .accordion-button:focus {
            box-shadow: none;
            border-color: rgba(0, 0, 0, .125);
        }

        

        /* Estilos responsivos (móviles) */
        @media (max-width: 576px) {
            .course-title-mobile {
                line-height: 30px !important;
                font-size: 1.5rem; /* Opcional: ajustar ligeramente el tamaño si es muy grande */
            }
            
            .play-btn-mobile {
                width: 50px !important;
                height: 50px !important;
            }
            
            .play-btn-mobile i {
                font-size: 1.5rem !important;
            }
        }
    </style>
@endsection

@auth
    @include('layouts/sections/navbar/navbar')
@else
    @include('layouts/sections/navbar/navbar-front')
@endauth

@section('content')
    <div class="container-fluid py-5" style="background-color: #F7F5FA; margin-top: 80px;">
        <div class="container my-5">

            <!-- HEADER / HERO -->
            <div class="course-hero mb-5 shadow-sm">
                <div class="row align-items-start">
                    <!-- Video / Image Preview -->
                    <div class="col-lg-8 mb-4 mb-lg-0">
                        <div class="text-black small mb-3 fw-medium">
                            Inicio <span class="mx-1">|</span> Crecimiento espiritual <span class="mx-1">|</span> <span
                                class="text-primary">{{ $curso->nombre }}</span>
                        </div>

                        @if ($curso->video_preview_url)
                            <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow">
                                <iframe src="{{ $curso->video_preview_url }}" title="YouTube video" allowfullscreen
                                    border="0"></iframe>
                            </div>
                        @else
                            <!-- Dummy Video Player Image for mockup -->
                            <div
                                class="ratio ratio-16x9 rounded-3 overflow-hidden shadow bg-dark position-relative d-flex align-items-center justify-content-center group">
                                @if ($curso->imagen_portada)
                                    <img src="{{ \Storage::url($configuracion->ruta_almacenamiento . '/img/cursos/' . $curso->imagen_portada) }}"
                                        alt="{{ $curso->nombre }}" class="img-fluid w-100 h-100"
                                        style="object-fit: cover; opacity: 0.85;">
                                @else
                                    <img src="https://images.unsplash.com/photo-1516321497487-e288fb19713f?q=80&w=2070&auto=format&fit=crop"
                                        alt="Teacher" class="img-fluid w-100 h-100"
                                        style="object-fit: cover; opacity: 0.85;">
                                @endif
                                <div class="position-absolute d-flex align-items-center justify-content-center play-btn-mobile"
                                    style="width: 80px; height: 80px; background-color: #ef4444; border-radius: 50%; cursor: pointer; transition: transform 0.2s; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);">
                                    <i class="ti ti-player-play-filled text-white ms-1" style="font-size: 2.5rem;"></i>
                                </div>
                            </div>
                        @endif

                        <h4 class="mt-4 mb-1 fw-semibold text-black course-title-mobile" style="letter-spacing: -0.5px;">{{ $curso->nombre }}</h4>
                    </div>

                    <!-- Playlist -->
                    <div class="col-lg-4 ps-lg-4">
                        <h5 class="fw-bold mb-3 d-flex align-items-center" style="color: #2b3445;">
                            Contenido destacado
                        </h5>
                        <div class="playlist-container pe-2">
                            @forelse($curso->modulos->take(3) as $moduloIndex => $modulo)
                                <div class="mb-3">
                                    <h6 class="text-black text-uppercase mb-2 fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                        Módulo {{ $loop->iteration }}: {{ $modulo->nombre }}
                                    </h6>
                                    
                                    @forelse($modulo->items as $itemIndex => $item)
                                        @php
                                            // Establecer si es el primer item del primer modulo (para 'desbloqueado')
                                            $isFirstItem = ($moduloIndex == 0 && $itemIndex == 0);
                                            // Identificar si es video (id = 2 asumiendo la estructura habitual)
                                            $isVideo = ($item->curso_item_tipo_id == 2);
                                        @endphp
                                        
                                        <div class="video-playlist-item d-flex align-items-center mb-2 p-2 rounded-3 {{ $isFirstItem ? 'active bg-light border border-primary border-opacity-25' : 'bg-white border text-black' }}" 
                                             style="transition: all 0.2s;">
                                            
                                            @if($isVideo)
                                                {{-- ESTILO PARA VIDEOS --}}
                                                <div class="position-relative me-3 rounded overflow-hidden flex-shrink-0" style="width: 90px; height: 50px;">
                                                    <img src="{{ $curso->imagen_portada ? \Storage::url($configuracion->ruta_almacenamiento . '/img/cursos/' . $curso->imagen_portada) : 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?q=80&w=2070&auto=format&fit=crop' }}"
                                                        alt="Thumb" class="img-fluid w-100 h-100" style="object-fit: cover; filter: {{ $isFirstItem ? 'none' : 'grayscale(50%)' }}; opacity: 0.8;">
                                                    
                                                    @if ($isFirstItem)
                                                        <div class="position-absolute top-50 start-50 translate-middle text-white d-flex align-items-center justify-content-center"
                                                            style="width: 24px; height: 24px; background-color: rgba(99, 102, 241, 0.9); border-radius: 50%; box-shadow: 0 0 5px rgba(0,0,0,0.3);">
                                                            <i class="ti ti-player-play-filled" style="font-size: 0.7rem; margin-left:1px;"></i>
                                                        </div>
                                                    @else
                                                        <div class="position-absolute top-50 start-50 translate-middle text-dark d-flex align-items-center justify-content-center"
                                                            style="width: 24px; height: 24px; background-color: rgba(255,255,255,0.8); border-radius: 50%;">
                                                            <i class="ti ti-lock" style="font-size: 0.8rem;"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                {{-- ESTILO PARA OTROS RECURSOS (Lecturas, Tareas, etc) --}}
                                                <div class="d-flex align-items-center justify-content-center rounded-2 me-3 flex-shrink-0 {{ $isFirstItem ? 'bg-primary bg-opacity-10 text-primary' : 'bg-secondary bg-opacity-10 text-secondary' }}" style="width: 45px; height: 45px;">
                                                    @if($item->curso_item_tipo_id == 1) {{-- Lectura --}}
                                                        <i class="ti ti-book fs-4"></i>
                                                    @elseif($item->curso_item_tipo_id == 3) {{-- Cuestionario/Actividad --}}
                                                        <i class="ti ti-clipboard-list fs-4"></i>
                                                    @else
                                                        <i class="ti ti-file-description fs-4"></i>
                                                    @endif
                                                </div>
                                            @endif
                                            
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h6 class="mb-1 {{ $isFirstItem ? 'text-primary fw-bold' : 'text-dark fw-medium' }} text-truncate" style="font-size: 0.85rem;" title="{{ $item->titulo }}">
                                                    {{ $item->titulo }}
                                                </h6>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge {{ $isVideo ? 'bg-danger bg-opacity-10 text-danger' : 'bg-info bg-opacity-10 text-info' }} rounded-pill" style="font-size: 0.6rem; padding: 0.2rem 0.4rem;">
                                                        {{ $isVideo ? 'Video' : 'Lectura/Recurso' }}
                                                    </span>
                                                    @if(!$isFirstItem)
                                                        <small class="text-black" style="font-size: 0.65rem;"><i class="ti ti-lock me-1"></i>Bloqueado</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-black small italic ps-2">Sin contenido aún.</div>
                                    @endforelse
                                </div>
                            @empty
                                <div class="text-center text-black small mt-4 p-4 border rounded bg-light">
                                    <i class="ti ti-books text-secondary fs-2 mb-2"></i><br>
                                    Aún no hay módulos para mostrar en este curso.
                                </div>
                            @endforelse

                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- MAIN COURSE CONTENT (Left) -->
                <div class="col-lg-8 pe-lg-5 mb-5 mb-lg-0">

                    <!-- Meta tags -->
                    <div class="d-flex align-items-center mb-4 text-black small fw-medium">
                        <div class="me-4  px-3 py-1 d-flex align-items-center"><i
                                class="ti ti-layout-grid me-2 text-primary"></i> Crecimiento</div>
                        <div class=" px-3 py-1 d-flex align-items-center"><i
                                class="ti ti-clock me-2 text-primary"></i>
                            @if ($curso->duracion_estimada_dias >= 30)
                                {{ round($curso->duracion_estimada_dias / 30) }} Meses
                            @else
                                {{ $curso->duracion_estimada_dias }} Días
                            @endif
                        </div>
                    </div>

                    <!-- Descripcion -->
                    <h4 class="fw-bold mb-3" style="color: #2b3445;">Descripción</h4>
                    <div class="text-black mb-5" style="line-height: 1.8; font-size: 0.95rem;">
                        {!! $curso->descripcion_larga !!}
                    </div>

                    <!-- Qué aprenderas -->
                    @if ($curso->aprendizajes->count() > 0)
                        <div class="learning-box mb-5 shadow-sm">
                            <h5 class="fw-bold mb-4" style="color: #2b3445;">¿Qué aprenderás en este curso?</h5>
                            <div class="row">
                                @foreach ($curso->aprendizajes as $aprendizaje)
                                    <div class="col-md-6 mb-3 d-flex align-items-start">
                                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm mt-1"
                                            style="min-width: 24px; min-height: 24px;">
                                            <i class="ti ti-check text-primary"
                                                style="font-size: 0.9rem; font-weight: bold;"></i>
                                        </div>
                                        <span class="text-black text-break pt-2" 
                                            style="font-size: 0.9rem; line-height: 1.5;">{{ $aprendizaje->texto }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Perfil y Requerimientos -->
                    <div class="row mb-5">
                        <div class="col-md-6 mb-4 mb-md-0">
                            <h5 class="fw-bold mb-3" style="color: #2b3445;">Quién puede tomar este curso</h5>
                            <ul class="list-unstyled text-black" style="font-size: 0.95rem;">
                                <!-- Lógica para transformar restricciones a texto -->
                                @php
                                    $restricciones = [];

                                    if ($curso->rangosEdad->count() > 0) {
                                        $rangos = $curso->rangosEdad->pluck('nombre')->join(', ');
                                        $restricciones[] = 'Para edades entre: ' . $rangos;
                                    }

                                    if ($curso->estadosCiviles->count() > 0) {
                                        $civiles = $curso->estadosCiviles->pluck('nombre')->join(' o ');
                                        $restricciones[] = 'Estado Civil: ' . $civiles;
                                    }

                                    if ($curso->genero) {
                                        $restricciones[] =
                                            'Solo para género: ' . ($curso->genero == 'M' ? 'Masculino' : 'Femenino');
                                    }
                                @endphp

                                @if (count($restricciones) > 0)
                                    @foreach ($restricciones as $restriccion)
                                        <li class="mb-3 d-flex align-items-center">
                                            <div style="width: 6px; height: 6px; border-radius: 50%; background-color: #ef4444;"
                                                class="me-3"></div>
                                            {{ $restriccion }}
                                        </li>
                                    @endforeach
                                @else
                                    <li class="mb-3 d-flex align-items-center">
                                        <div style="width: 6px; height: 6px; border-radius: 50%; background-color: #ef4444;"
                                            class="me-3"></div>
                                        Abierto para todo público
                                    </li>
                                @endif
                            </ul>
                        </div>

                        <div class="col-md-6">
                            <h5 class="fw-bold mb-3" style="color: #2b3445;">Requerimientos del curso</h5>
                            <ul class="list-unstyled text-black" style="font-size: 0.95rem;">
                                @php
                                    $requisitos = [];

                                    foreach ($curso->pasosRequisito as $paso) {
                                        $requisitos[] = 'Haber completado: ' . $paso->nombre;
                                    }

                                    foreach ($curso->tareasRequisito as $tarea) {
                                        $requisitos[] = 'Tener requisito: ' . $tarea->nombre;
                                    }
                                @endphp

                                @if (count($requisitos) > 0)
                                    @foreach ($requisitos as $req)
                                        <li class="mb-3 d-flex align-items-center">
                                            <div style="width: 6px; height: 6px; border-radius: 50%; background-color: #ef4444;"
                                                class="me-3"></div>
                                            {{ $req }}
                                        </li>
                                    @endforeach
                                @else
                                    <li class="mb-3 d-flex align-items-center">
                                        <div style="width: 6px; height: 6px; border-radius: 50%; background-color: #ef4444;"
                                            class="me-3"></div>
                                        Sin requerimientos previos
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    @php
                        $totalModulos = $curso->modulos->count();
                        $totalItems = 0;
                        foreach($curso->modulos as $mod) {
                            $totalItems += $mod->items->count();
                        }
                    @endphp

                    <!-- Acordeón Contenido del Curso -->
                    <div class="d-flex justify-content-between align-items-end mb-4 border-bottom pb-3">
                        <h4 class="fw-bold mb-0" style="color: #2b3445;">Contenido del curso</h4>
                        <div class="text-black small fw-medium d-none d-md-flex">
                            <span class="me-4"><i class="ti ti-folder me-1 text-primary"></i> {{ $totalModulos }} {{ $totalModulos == 1 ? 'Modulo' : 'Modulos' }}</span>
                            <span class="me-4"><i class="ti ti-file-description me-1 text-primary"></i> {{ $totalItems }} lecciones</span>
                        </div>
                    </div>

                    <div class="accordion accordion-flush bg-transparent" id="courseContentAccordion">
                        @forelse($curso->modulos as $index => $modulo)
                            <div class="accordion-item bg-transparent border-bottom rounded">
                                <h2 class="accordion-header" id="heading{{ $modulo->id }}">
                                    <button class="accordion-button {{ $index == 0 ? '' : 'collapsed' }} fw-bold bg-transparent px-4 py-4 d-flex align-items-center" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#collapse{{ $modulo->id }}" aria-expanded="{{ $index == 0 ? 'true' : 'false' }}"
                                        aria-controls="collapse{{ $modulo->id }}" style="font-size: 1.1rem;">
                                        
                                        <div class="flex-grow-1 text-start pe-3">
                                            {{ $modulo->nombre }}
                                        </div>
                                        
                                        <div class="d-flex align-items-center text-black small fw-normal ms-auto me-3 flex-shrink-0"
                                            style="font-size: 0.85rem;">
                                            <span class="me-2"><i class="ti ti-file-description text-primary"></i> {{ $modulo->items->count() }} lecciones</span>
                                        </div>

                                    </button>
                                </h2>
                                <div id="collapse{{ $modulo->id }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" aria-labelledby="heading{{ $modulo->id }}"
                                    data-bs-parent="#courseContentAccordion">
                                    <div class="accordion-body ps-0 pt-0 pb-3">
                                        <ul class="list-group list-group-flush ms-4">
                                            @forelse($modulo->items as $item)
                                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 bg-transparent border-0">
                                                    <div class="d-flex align-items-center text-black" style="font-size: 0.95rem;">
                                                        @if($item->tipo && $item->tipo->icono)
                                                            <i class="{{ $item->tipo->icono }} me-3 text-dark"></i>
                                                        @else
                                                            <i class="ti ti-file-description me-3 text-dark"></i>
                                                        @endif
                                                        {{ $item->titulo }}
                                                    </div>
                                                </li>
                                            @empty
                                                <li class="list-group-item px-0 py-3 bg-transparent border-0 text-black small">
                                                    No hay ítems registrados en este módulo.
                                                </li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-black border rounded">Aún no hay contenido estipulado para este curso.</div>
                        @endforelse
                    </div>

                </div>

                <!-- SIDEBAR DETAILS CARD (Right) -->
                <div class="col-lg-4">
                    <div class="card border-0 sticky-sidebar">
                        <div class="card-body p-4 p-xl-5">

                            <!-- Precio -->
                            <div class="mb-4">
                                @if ($curso->es_gratuito)
                                    <div class="text-primary mb-1">Gratis</div>
                                @else
                                    <div class=" mb-1">
                                        <h2 class="text-primary fw-bold">$
                                            {{ number_format($curso->precio, 0, ',', '.') }}</h2>
                                    </div>
                                @endif

                            </div>
                            <hr>
                            <!-- Detalles -->
                            <ul class="list-group list-group-flush mb-4" style="font-size: 0.95rem;">
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 text-black border-bottom-0 pb-4 ">
                                    <span class="fw-medium">Instructor</span>
                                    @php
                                        $creador = $curso->equipo->first(
                                            fn($miembro) => $miembro->tipoCargo &&
                                                $miembro->tipoCargo->nombre == 'Creador',
                                        );
                                    @endphp
                                    <span class="fw-bold text-dark text-end" style="border-bottom: 1px solid #2b3445;">
                                        {{ $creador ? $creador->user->nombre() : 'Plataforma' }}
                                    </span>
                                </li>
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 text-black border-bottom-0 pb-4 ">
                                    <span class="fw-medium">Duración</span>
                                    <span class="fw-bold text-dark text-end">
                                        @if ($curso->duracion_estimada_dias >= 30)
                                            {{ round($curso->duracion_estimada_dias / 30) }} meses
                                        @else
                                            {{ $curso->duracion_estimada_dias }} días
                                        @endif
                                    </span>
                                </li>
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 text-black border-bottom-0 pb-4">
                                    <span class="fw-medium">Lecciones</span>
                                    <span class="fw-bold text-dark text-end">{{ count($curso->modulos->pluck('items')->flatten()) }}</span>
                                </li>
                            </ul>

                            <!-- CTA Boton -->
                            <div class="mt-4">
                                @livewire('cursos.boton-inscripcion-curso', ['curso' => $curso])
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
