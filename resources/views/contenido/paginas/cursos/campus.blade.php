@extends('layouts/contentNavbarLayout')

@section('title', 'Campus - ' . $curso->nombre)

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


    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss'])


@endsection
@section('vendor-script')
    @vite(['resources/js/app.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js'])

@endsection

@section('vendor-style')
    <!-- Incluir estilos si fuesen necesarios -->
    <style>
        /* Ajustes específicos para el acordeón del campus para que se vea como playlist */
        #temarioAccordion .accordion-button::after {
            background-size: 1rem;
        }

        #temarioAccordion .accordion-button:not(.collapsed) {
            box-shadow: none;
            background-color: transparent;
        }


        .learning-box {
            background-color: #f6f0ff;

            border-radius: 12px;
            padding: 2rem;
        }
    </style>
@endsection

@section('page-style')
@endsection

@section('content')

    <!-- Montamos el componente Livewire y el pasamos el slug de la ruta -->
    @livewire('cursos.campus-curso', ['slug' => $slug])

@endsection
