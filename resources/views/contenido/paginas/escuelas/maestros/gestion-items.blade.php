{{-- Asume que 'layouts.layoutMaster' es tu layout principal --}}
@section('isEscuelasModule', true)
{{-- resources/views/maestros/horarios_asignados.blade.php --}}
@extends('layouts.layoutMaster')
@section('title', 'Gestionar Items - ' . $nombreMateria)

@section('vendor-style')


@vite(['resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css'])

@endsection

@section('vendor-script')
@vite(['resources/js/app.js', 'resources/assets/vendor/libs/quill/quill.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js'])
<script>
   $(".fecha-picker").flatpickr({
            dateFormat: "Y-m-d",
            disableMobile: true
        });
</script>
@endsection




@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h4 class="fw-bold text-primary py-3 mb-4">
               Gestionar Items
            </h4>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Gestión de Items de Evaluación</h5>
                        <small class="text-muted">{{ $nombreMateria }} - {{ $infoClase }} </small><br>
                        <small class="text-muted">ID:{{ $horarioAsignado->id}}</small>
                    </div>
                     <a href="{{ route('maestros.dashboardClase', [$maestro->id, $horarioAsignado->id]) }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Volver al Dashboard
                    </a>
                </div>
                <div class="card-body">
                    @livewire('escuelas.gestion-items-corte-materia-periodo', ['horarioAsignado' => $horarioAsignado])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
