@section('isEscuelasModule', true)

@extends('layouts.layoutMaster')

@section('title', 'Mis horarios')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/swiper/swiper.scss'])
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

@section('content')
    @include('layouts.status-msn')

    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-1 fw-semibold text-primary">Mis horarios Activos</h4>
            <p class="mb-0">Aquí encontrarás todas los horarios en las que estás matriculado actualmente.</p>
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

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Listado de Matrículas</h5>
        </div>
        <div class="card-body">
            {{-- Encabezados para la vista de escritorio --}}
            <div class="row d-none d-md-flex fw-bold mb-3 border-bottom pb-2">
                <div class="col-md-3">Materia</div>
                <div class="col-md-2">Periodo</div>
                <div class="col-md-3">Horario</div>
                <div class="col-md-2">Sede / Aula</div>
                <div class="col-md-2">Acciones</div>
            </div>

            @forelse ($matriculas as $matricula)
                <div class="card mb-3">
                    <div class="card-body p-0">
                        <div class="row align-items-start p-3">
                            <div class="col-12 col-md-3 mb-2 mb-md-0">
                                <strong class="d-md-none">Materia: </strong>
                                {{-- Usamos el operador Null Safe (?->) para evitar errores si alguna relación es nula --}}
                                {{ $matricula->horarioMateriaPeriodo?->materiaPeriodo?->materia?->nombre ?? 'N/A' }}
                            </div>
                            <div class="col-12 col-md-2 mb-2 mb-md-0">
                                <strong class="d-md-none">Periodo: </strong>
                                {{ $matricula->periodo?->nombre ?? 'N/A' }}
                            </div>

                            {{-- ========================================================== --}}
                            {{-- === INICIO DE LA CORRECCIÓN                             === --}}
                            {{-- ========================================================== --}}
                            <div class="col-12 col-md-3 mb-2 mb-md-0">
                                <strong class="d-md-none">Horario: </strong>
                                {{-- Se corrige "horario" por "horarioMateriaPeriodo" --}}
                                {{ $matricula->horarioMateriaPeriodo?->horarioBase?->dia_semana ?? 'N/D' }},
                                {{ $matricula->horarioMateriaPeriodo?->horarioBase?->hora_inicio_formato ?? 'N/A' }} -
                                {{ $matricula->horarioMateriaPeriodo?->horarioBase?->hora_fin_formato ?? 'N/A' }}
                            </div>
                            <div class="col-12 col-md-2 mb-2 mb-md-0">
                                <strong class="d-md-none">Ubicación: </strong>
                                {{-- Se corrige "horario" por "horarioMateriaPeriodo" --}}
                                {{ $matricula->horarioMateriaPeriodo?->horarioBase?->aula?->sede?->nombre ?? 'N/A' }} /
                                {{ $matricula->horarioMateriaPeriodo?->horarioBase?->aula?->nombre ?? 'N/A' }}
                            </div>
                            <div class="col-12 col-md-2 text-md-start mt-2 mt-md-0">
                                {{-- Se corrige "horario_id" por "horario_materia_periodo_id" --}}
                                <a class="btn btn-outline-secondary rounded-pill"
                                    href="{{ route('alumnos.perfilMateria', ['horario' => $matricula->horario_materia_periodo_id]) }}">
                                    <i class="ti ti-arrow-right ti-xs me-1"></i>Acceder
                                </a>
                            </div>
                            {{-- ========================================================== --}}
                            {{-- === FIN DE LA CORRECCIÓN                                === --}}
                            {{-- ========================================================== --}}
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info text-center" role="alert">
                    <i class="ti ti-info-circle me-2"></i>
                    Actualmente no te encuentras matriculado en ningún curso de un periodo activo.
                </div>
            @endforelse
        </div>
    </div>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/swiper/swiper.js'])
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
