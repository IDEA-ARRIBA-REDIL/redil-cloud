@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp


@extends('layouts/blankLayout')

@section('title', 'Inscripción')

@section('vendor-style')


@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/apex-charts/apex-charts.scss'])

<style>
    body {
        margin: 0;
        /* Elimina los márgenes predeterminados del cuerpo y html */
        padding: 0;
    }

    #container-completo {
        min-height: 50vh;
        height: auto;
    }

    body {
        overflow-x: hidden;
    }

</style>
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection


@section('page-script')
@vite(['resources/assets/js/form-basic-inputs.js'])



@endsection

@section('content')

<div class="d-flex align-items-center min-vh-100">

    <div class="container">

        <div class="row">
            <div class="col-12 col-lg-12 d-flex align-items-center">
                <div class=" mx-auto my-auto text-center">


                </div>
            </div>
        </div>
    </div>
</div>
@section('content')
{{-- Esta línea carga el componente de Livewire y le pasa la inscripción --}}
@livewire('actividades.gestionar-invitados', ['inscripcionPrincipal' => $inscripcion])
@endsection

@push('scripts')
{{-- Es importante tener el listener de SweetAlert aquí para que el componente pueda usarlo --}}
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('mostrarAlerta', (event) => {
            const detail = Array.isArray(event) ? event[0] : event;
            Swal.fire({
                title: detail.titulo
                , text: detail.texto
                , icon: detail.icono
                , confirmButtonText: 'Entendido'
            });
        });

         Livewire.on('cerrarModalInvitado', (event) => {
            $('#modalAnadirInvitado' ).modal('hide');
        });
    });

</script>
@endpush

@endsection
