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
                                class="ti-xs ti me-2 ti-template"></i> Materias </a>

                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <h5 class="mb-1 fw-semibold text-black">Listado de materias: {{ $periodo->nombre }}</h5>
        <p class="text-black">aquí podras crear y gestionar los cortes de tu periodo </p>
    </div>
    <!-- PORTADA -->
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-10 p-1 border-1">
                @livewire('Escuelas.MateriaPeriodo', ['periodo' => $periodo])
            </div>
        </div>
    </div>
@endsection
