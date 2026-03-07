@section('isEscuelasModule', true)

@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Modelo Calificativo')

{{-- HEAD - Estilos y Scripts de Vendor (Sin cambios) --}}

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])
@endsection

@section('content')

    @include('layouts.status-msn') {{-- Muestra mensajes flash temporales --}}

    <!-- botonera -->
    <div class="row mb-5 mt-5">
        <div class="me-auto ">
            <h4 class="mb-1 fw-semibold text-primary">Modelo calificativo</h4>

        </div>
    </div>
    <!-- /botonera -->

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-10 p-1 border-1">
                <ul class="nav nav-pills justify-content-start flex-column flex-md-row  gap-2">

                    <li class="nav-item flex-fill"><a id="tap-principal"
                            href="{{ route('materias.gestionar', $materia->id) }} "
                            class="nav-link p-3 waves-effect
                            waves-light "
                            data-tap="principal"><i class="ti-xs ti me-2  ti-info-hexagon"></i>
                            Datos
                            principales</a>
                    </li>

                    <li class="nav-item flex-fill"><a id="tap-horarios"
                            href="{{ route('materias.horarios', $materia->id) }} "
                            class="nav-link p-3 waves-effect waves-light " data-tap="horarios"><i
                                class="ti-xs ti me-2  ti-clock"></i>
                            Listado de horarios</a>
                    </li>

                    <li class="nav-item flex-fill"><a id="tap-modelo" href="{{ route('materias.modelo', $materia->id) }} "
                            class="nav-link p-3 waves-effect waves-light active" data-tap="modelo"><i
                                class="ti-xs ti me-2  ti-template"></i>
                            Modelo de calificación</a>

                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row mb-5 mt-5">
        <div class="me-auto ">
            <h4 class="mb-1 fw-semibold text-black">Datos principales</h4>
            <p class=" text-black">aqui podras crear los items de la materia: <b>{{ $materia->nombre }} </b></p>
        </div>
    </div>
    {{-- ** Inclusión del Componente Livewire ** --}}
    @livewire('Escuelas.GestionItemPlantillas', ['materia' => $materia])





@endsection
