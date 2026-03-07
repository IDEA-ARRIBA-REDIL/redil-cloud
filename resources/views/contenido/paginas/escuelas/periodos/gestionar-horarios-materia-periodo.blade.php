@section('isEscuelasModule', true)

@extends('layouts/layoutMaster')

@section('title', 'Gestionar Horarios Periodo')

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

    <h4 class="mb-1 fw-semibold text-primary">Gestionar horarios materia período: {{ $materiaPeriodo->materia->nombre }}</h4>
    <p class="text-black">aquí podras crear y gestionar los cortes de tu período </p>

    @include('layouts.status-msn')
    <!-- PORTADA -->
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-10 p-1 border-1">
                @livewire('Escuelas.HorariosMateriaPeriodo', ['materiaPeriodo' => $materiaPeriodo])
            </div>
        </div>
    </div>
@endsection
