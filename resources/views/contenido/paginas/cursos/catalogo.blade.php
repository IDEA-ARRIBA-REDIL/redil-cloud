@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Catálogo de Cursos')

@section('page-style')
    <style>
        /* Estilos personalizados para el catálogo público */
        .bg-catalog-hero {
            background-color: #f7f6f9;
            /* Color base claro según diseño */
        }

        .text-purple {
            color: #8c57ff;
            /* Morado del diseño */
        }

        .bg-purple {
            background-color: #8c57ff;
        }

        .badge-category {
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        /* Animación y estado activo de la pestaña de categoría */
        .badge-category.active,
        .badge-category:hover {
            border-bottom: 6px solid #ff4d6d !important;
            /* Rosa del diseño */
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
            color: #8c57ff;
            /* Opcional si queremos cambiar el color del texto */
        }

        .course-card-img {
            height: 180px;
            object-fit: cover;
            border-top-left-radius: .375rem;
            border-top-right-radius: .375rem;
        }

        /* Botón rosa */
        .btn-outline-pink {
            color: #ff4d6d;
            border-color: #ff4d6d;
        }

        .btn-outline-pink:hover {
            background-color: #ff4d6d;
            color: white;
        }

        /* Botón púrpura */
        .btn-purple {
            background-color: #8c57ff;
            color: white;
            border: none;
        }

        .btn-purple:hover {
            background-color: #7b4cde;
            color: white;
        }

        /* Barra de progreso de "Mis Cursos" */
        .progress-bar-custom {
            height: 6px;
            border-radius: 4px;
        }

        .progress-bar-fill {
            background-color: #28c76f;
            /* Verde para progreso */
            border-radius: 4px;
        }

        /* Mejoras en las tarjetas */
        .card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .barra-progreso {
            height: 6px;
            border-radius: 4px;
            width: 70%;
        }

        @media (min-width: 1600px) {
            #row-inside-mis-cursos {
                width: 75% !important;
            }

            .bg-catalog-hero {
                height: 100% !important;
            }

            #container-categorias {
                display: block !important;
            }

            #container-select-categorias {
                display: none !important;
            }
        }

        @media (max-width: 1200px) {
            #row-inside-mis-cursos {
                width: 250px !important;


            }

            #container-categorias {
                display: block !important;
            }

            #container-select-categorias {
                display: none !important;
            }

        }

        @media (max-width: 768px) {
            #container-categorias {
                display: none !important;
            }

            #container-select-categorias {
                display: block !important;
            }

            .barra-progreso {
                width: 100%;
            }

            #row-inside-mis-cursos {
                width: 100% !important;
            }

            .bg-catalog-hero {
                height: 100% !important;
            }
        }
    </style>

    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css'])


@endsection


@section('vendor-script')

    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js'])

    <script type="module">
        $(document).ready(function() {

            $('.select2').select2({
                width: '100px',
                allowClear: true,
                placeholder: 'Ninguno',
                dropdownParent: $('#formulario')
            });
        });
    </script>
@endsection

@section('content')
    <!-- Cargamos el componente Livewire que maneja toda la lógica del catálogo -->
    @livewire('cursos.catalogo-cursos')
@endsection
