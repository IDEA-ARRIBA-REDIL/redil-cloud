@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Actividades')

<!-- Page -->
@section('page-style')


@section('vendor-style')
<style>
    .color-picker-container {
        width: 100px;
        /* Ajusta este valor al tamaño que necesites */

    }

    .pickr .pcr-button {
        height: 38px !important;
        width: 40px !important;
        border: solid 1px #3e3e3e;
    }

</style>


@vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss', 'resources/assets/vendor/libs/quill/editor.scss'])


@endsection


@section('vendor-script')
@vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js', 'resources/assets/vendor/libs/quill/quill.js', 'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js', 'resources/js/app.js'])

@endsection


@section('page-script')

<script>
    function sinComillas(e) {
        tecla = (document.all) ? e.keyCode : e.which;
        patron = /[\x5C'"]/;
        te = String.fromCharCode(tecla);
        return !patron.test(te);
    }

</script>

<script>
    document.addEventListener('livewire:initialized', () => {
        // Obtenemos las instancias de los modales de Bootstrap
        const scannerModalEl = document.getElementById('qrScannerModal');
        const errorModalEl = document.getElementById('incorrectQrErrorModal');

        const scannerModal = new bootstrap.Modal(scannerModalEl);
        const errorModal = new bootstrap.Modal(errorModalEl);

        // 1. Escuchamos el evento del backend para mostrar el error
        Livewire.on('showIncorrectQrModal', (data) => {
            const eventData = Array.isArray(data) ? data[0] : data;

            // Cerramos el modal del scanner
            scannerModal.hide();

            // Actualizamos el contenido del modal de error
            document.getElementById('incorrectQrErrorModalTitle').innerHTML = eventData.title;
            document.getElementById('incorrectQrErrorModalBody').innerHTML = eventData.text;

            // Mostramos el modal de error
            errorModal.show();
        });

        // 2. Escuchamos cuando el modal de error SE HA CERRADO
        errorModalEl.addEventListener('hidden.bs.modal', event => {
            // Y volvemos a abrir el modal del scanner
            scannerModal.show();
        });
    });

</script>


@endsection


@section('content')


{{-- Barra de navegación superior --}}
<nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center fixed-top">
    <div class="col-3 text-start">
        <a href="{{ route('actividades.categorias', $actividad) }}" class="btn rounded-pill waves-effect waves-light text-white">
            <span class="ti-xs ti ti-arrow-left me-2"></span><span class="d-none d-md-block fw-normal">Volver</span>
        </a>
    </div>
    <div class="col-6 pl-5 text-center">
        {{-- Título cambiado a "Editar" --}}
        <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Registrar asistencia</h5>
    </div>
    <div class="col-3 text-end">
        <a href="{{ route('actividades.categorias', $actividad) }}" class="btn rounded-pill waves-effect waves-light text-white">
            <span class="d-none d-md-block fw-normal">Salir</span><span class="ti-xs ti ti-x mx-2"></span>
        </a>
    </div>
</nav>
<div class="container-fluid" style="padding-top: 100px; padding-bottom: 20px;">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            @include('layouts.status-msn')
            @livewire('Actividades.asistenciasActividad', [
            'actividad' => $actividad,
            ])

        </div>
    </div>
</div>


<div class="modal fade" id="incorrectQrErrorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="incorrectQrErrorModalTitle"></h5>
            </div>
            <div class="modal-body" id="incorrectQrErrorModalBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

@endsection
