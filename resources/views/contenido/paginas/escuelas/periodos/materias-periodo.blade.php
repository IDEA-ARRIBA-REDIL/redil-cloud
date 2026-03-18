@section('isEscuelasModule', true)

@extends('layouts/layoutMaster')

@section('title', 'Gestionar Periodos')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss', 'resources/assets/vendor/libs/quill/editor.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/quill/quill.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@endsection

@section('page-script')

@endsection

@section('content')
    @include('layouts.status-msn')
    <div class="row">
        <h4 class="mb-1 fw-semibold text-primary">Gestionar materias: {{ $periodo->nombre }}</h4>

    </div>


    <div class="row">
        <div class="col-md-12">
            <div class="card mb-10 p-1 border-1">
                <ul class="nav nav-pills justify-content-start flex-column flex-md-row  gap-2">

                    <li class="nav-item flex-fill"><a id="tap-principal" href=" {{ route('periodo.actualizar', $periodo) }} "
                            class="nav-link p-3 waves-effect
                                waves-light "
                            data-tap="principal"><i class="ti-xs ti me-2 ti-info-hexagon "></i>
                            Datos
                            principales</a>
                    </li>

                    <li class="nav-item flex-fill"><a id="tap-horarios" href="{{ route('periodo.cortes', $periodo) }} "
                            class="nav-link p-3 waves-effect waves-light " data-tap="horarios"><i
                                class="ti-xs ti me-2 ti-clock"></i> Cortes </a>
                    </li>

                    <li class="nav-item flex-fill"><a id="tap-modelo" href="{{ route('periodo.materias', $periodo) }}"
                            class="nav-link p-3 waves-effect waves-light active" data-tap="modelo"><i
                                class="ti-xs ti me-2 ti-template"></i>
                            {{ $periodo->escuela->tipo_matricula === 'niveles_agrupados' ? 'Grados' : 'Materias' }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    @php
        $nivelId = request('nivel_id');
        $esDeNiveles = $periodo->escuela->tipo_matricula === 'niveles_agrupados';
    @endphp

    <div class="row">
        @if ($esDeNiveles && !$nivelId)
            <h5 class="mb-1 fw-semibold text-black">Gestión de Grados: {{ $periodo->nombre }}</h5>
            <p class="text-black">Asocia los grados que estarán activos en este periodo académico.</p>
        @elseif($esDeNiveles && $nivelId)
            @php $nivelNombre = \App\Models\NivelEscuela::find($nivelId)?->nombre; @endphp
            <h5 class="mb-1 fw-semibold text-black">Materias del Grado: {{ $nivelNombre }}</h5>
            <p class="text-black">Gestiona las materias específicas para este grado en el periodo {{ $periodo->nombre }}.
            </p>
            <div class="mb-3">
                <a href="{{ route('periodo.materias', $periodo) }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                    <i class="ti ti-arrow-left me-1"></i> Volver a Grados
                </a>
            </div>
        @else
            <h5 class="mb-1 fw-semibold text-black">Listado de materias: {{ $periodo->nombre }}</h5>
            <p class="text-black">Aquí podrás gestionar las materias de tu periodo académico.</p>
        @endif
    </div>

    <!-- PORTADA -->
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-10 p-1 border-1">
                @if ($esDeNiveles && !$nivelId)
                    @livewire('Escuelas.NivelesPeriodo', ['periodo' => $periodo])
                @else
                    @livewire('Escuelas.MateriaPeriodo', ['periodo' => $periodo, 'nivel_id' => $nivelId])
                @endif
            </div>
        </div>
    </div>
@endsection
