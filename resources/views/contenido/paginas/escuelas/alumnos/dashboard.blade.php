@section('isEscuelasModule', true)

@extends('layouts.layoutMaster')

@section('title', 'Mis horarios')

@section('content')
    @include('layouts.status-msn')

    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-1 fw-semibold text-primary">Mis horarios Activos</h4>
            <p class="mb-0">Aquí encontrarás todas los horarios en las que estás matriculado actualmente.</p>
        </div>
    </div>

      @if ($banners->isNotEmpty())
        <div id="bannerCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
            <div class="carousel-inner rounded">
                @foreach ($banners as $key => $banner)
                    <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                        <img src="{{ $banner->imagen_url }}" class="d-block w-100" alt="Banner Informativo" style="max-height: 350px; object-fit: cover;">
                       
                    </div>
                @endforeach
            </div>

            {{-- Controles del carrusel --}}
            <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Siguiente</span>
            </button>
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