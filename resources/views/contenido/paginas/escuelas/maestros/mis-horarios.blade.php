{{-- Asume que 'layouts.layoutMaster' es tu layout principal --}}
@section('isEscuelasModule', true)
{{-- resources/views/maestros/horarios_asignados.blade.php (o el nombre que corresponda a esta vista) --}}
@extends('layouts.layoutMaster')

@section('title', 'Horarios Asignados a ' . ($maestro->user->name ?? 'Maestro'))

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/swiper/swiper.scss'])
@endsection

@section('page-style')
    <style>
        .swiper-button-next-banners,
        .swiper-button-prev-banners {
            width: 40px !important;
            height: 40px !important;
            border-radius: 50% !important;
            transition: all 0.3s ease;
        }

        .swiper-button-next-banners:after,
        .swiper-button-prev-banners:after {
            font-size: 18px !important;
            font-weight: bold;
        }

        .swiper-button-next-banners:hover,
        .swiper-button-prev-banners:hover {
            background-color: rgba(255, 255, 255, 0.4);
            transform: scale(1.1);
        }
    </style>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/swiper/swiper.js'])
@endsection

@section('page-script')
    <script type="module">
        const swiperBannersContainer = document.querySelector('#swiper-banners');
        if (swiperBannersContainer) {
            const swiperBanners = new Swiper(swiperBannersContainer, {
                slidesPerView: 1,
                spaceBetween: 10,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                navigation: {
                    nextEl: ".swiper-button-next-banners",
                    prevEl: ".swiper-button-prev-banners",
                },
                pagination: {
                    el: ".swiper-pagination-banners",
                    clickable: true
                },
            });
        }
    </script>
@endsection

@section('content')
    @include('layouts.status-msn')

    <div class="row mb-5 mt-5">
        <div class="me-auto ">
            <h4 class="mb-1 fw-semibold text-primary">Horarios asignados a: {{ $maestro->user->nombre(3) ?? 'N/A' }}
            </h4>
            <p class="mb-0">Puedes ver tus horarios asignados.</p> {{-- Texto corregido para que tenga sentido con el contexto de la vista --}}
        </div>
    </div>

    @if ($banners->isNotEmpty())
        <div id="col-novedades" class="col-12 col-lg-12 mb-4">
            <div class="swiper-container swiper" id="swiper-banners">
                <div class="swiper-wrapper">
                    @foreach ($banners as $banner)
                        <div class="swiper-slide mb-5">
                            <div class="card shadow-none border-0 overflow-hidden rounded-3 position-relative">
                                <img class="w-100" style="height: auto; min-height: 150px; max-height: 350px; object-fit: cover;"
                                    src="{{ $banner->imagen_url }}"
                                    alt="{{ $banner->descripcion ?? 'Banner' }}">
                                @if ($banner->descripcion)
                                    <div class="card-img-overlay d-flex flex-column justify-content-end p-4"
                                        style="background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 60%);">
                                        <div class="d-flex flex-row align-items-center">
                                            <h6 class="text-white fw-semibold mb-0">{{ $banner->descripcion }}</h6>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="swiper-button-next swiper-button-next-banners text-white"></div>
                <div class="swiper-button-prev swiper-button-prev-banners text-white"></div>
                <div class="swiper-pagination swiper-pagination-banners mb-5 d-none"></div>
            </div>
        </div>
    @endif

    {{-- INICIO DE LA SECCIÓN DE HORARIOS EN CARDS --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h5 class="mb-0 fw-semibold text-black">Listado de horarios asignados</h5>
        <div class="d-flex align-items-center gap-2">
            <small class="text-muted">Ordenar por materia:</small>
            <div class="btn-group btn-group-sm" role="group" aria-label="Ordenar por materia">
                <a href="{{ route('maestros.misHorarios', ['user' => $maestro->user_id, 'sort' => 'materia_nombre', 'direction' => 'asc']) }}"
                    class="btn {{ $sortField == 'materia_nombre' && $sortDirection == 'asc' ? 'btn-primary' : 'btn-outline-secondary' }}"
                    title="Orden ascendente (A-Z)">
                    <i class="ti ti-sort-ascending-letters me-1"></i>A-Z
                </a>
                <a href="{{ route('maestros.misHorarios', ['user' => $maestro->user_id, 'sort' => 'materia_nombre', 'direction' => 'desc']) }}"
                    class="btn {{ $sortField == 'materia_nombre' && $sortDirection == 'desc' ? 'btn-primary' : 'btn-outline-secondary' }}"
                    title="Orden descendente (Z-A)">
                    <i class="ti ti-sort-descending-letters me-1"></i>Z-A
                </a>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        @forelse ($horariosAsignados as $horarioAsignado)
            @php
                $descripcionMateria = $horarioAsignado->materiaPeriodo?->descripcionResumen(20);
            @endphp
            <div class="col-equal-height-col col-12 col-md-6 mb-4">
                <div class="h-100 card d-flex flex-column">
                    <div class="card-header pb-3">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div>
                                <h5 class="mb-0 fw-semibold text-black lh-sm">
                                    {{ $horarioAsignado->materiaPeriodo?->materia?->nombre ?? 'N/A' }}
                                </h5>
                                @if ($descripcionMateria)
                                    <small class="text-muted d-block mt-1">({{ $descripcionMateria }})</small>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex flex-row align-items-center mt-3">
                            <div class="d-flex flex-column">
                                <small class="text-muted"><i class="ti ti-calendar-event me-1"></i>Periodo:</small>
                                <small class="fw-semibold text-black">
                                    {{ $horarioAsignado->materiaPeriodo?->periodo?->nombre ?? 'N/A' }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-0 flex-grow-1">
                        {{-- Bloque destacado de horario y día --}}
                        <div class="p-2 mb-3 rounded border bg-lighter d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div class="d-flex align-items-center">
                                <div class="badge rounded bg-label-primary p-2 me-2">
                                    <i class="ti ti-clock fs-5"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block fw-medium" style="font-size: 0.72rem;">Horario</small>
                                    <span class="fw-bold text-black lh-1">
                                        {{ $horarioAsignado->horarioBase?->hora_inicio_formato ?? 'N/A' }} -
                                        {{ $horarioAsignado->horarioBase?->hora_fin_formato ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <span class="badge bg-label-primary rounded-pill fw-semibold">
                                    <i class="ti ti-calendar-week me-1"></i>{{ $horarioAsignado->horarioBase?->dia_semana ?? 'N/A' }}
                                </span>
                            </div>
                        </div>

                        <div class="row justify-content-between">
                            <div class="col-12 col-sm-6 mb-2 mb-sm-0">
                                <div class="d-flex flex-column text-start">
                                    <small class="text-muted"><i class="ti ti-building-skyscraper me-1"></i>Sede:</small>
                                    <small class="fw-semibold text-black">
                                        {{ $horarioAsignado->horarioBase?->aula?->sede?->nombre ?? 'N/A' }}
                                    </small>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="d-flex flex-column text-start">
                                    <small class="text-muted"><i class="ti ti-door me-1"></i>Aula:</small>
                                    <small class="fw-semibold text-black">
                                        {{ $horarioAsignado->horarioBase?->aula?->nombre ?? 'N/A' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-transparent border-top pt-3 d-flex justify-content-end">
                        <a class="btn btn-outline-secondary rounded-pill"
                            href="{{ route('maestros.dashboardClase', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}">
                            Acceder <i class="ti ti-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center" role="alert">
                    Este maestro no tiene horarios asignados todavía.
                </div>
            </div>
        @endforelse
    </div>

    @if ($horariosAsignados->hasPages())
        <div class="row mt-4">
            <div class="col-12 d-flex justify-content-center">
                {{-- Los links de paginación ya incluirán los parámetros de ordenamiento gracias al cambio en el controlador --}}
                {{ $horariosAsignados->links() }}
            </div>
        </div>
    @endif
    {{-- FIN DE LA SECCIÓN DE HORARIOS EN CARDS --}}


@endsection
