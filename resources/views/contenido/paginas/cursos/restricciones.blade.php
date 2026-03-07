@extends('layouts/layoutMaster')

@section('title', 'Restricciones del Curso')

@section('page-style')
@vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/js/app.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
<script>
     $(document).ready(function() {
            $('.select2').select2({
                width: '100px',
                allowClear: true,
                placeholder: 'Ninguno',
            });
     });

    document.addEventListener('livewire:initialized', () => {
        Livewire.on('msn', (data) => {
            var content = data[0] || data;
            Swal.fire({
                title: content.icon === 'success' ? 'Éxito' : 'Atención',
                text: content.msn,
                icon: content.icon,
                confirmButtonText: 'Aceptar',
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            });
        });
    });
</script>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-1">
                <span class="text-primary fwsemibold">Restricciones curso</span>
            </h4>
            <div class="text-black small">Configura quién puede acceder al curso: {{ $curso->nombre }}</div>
        </div>
        <div>
            <a href="{{ route('cursos.editar', $curso->id) }}" class="btn btn-outline-primary">
                <i class="ti ti-arrow-left me-1"></i> Volver a Editar
            </a>
        </div>
    </div>

    <div class="row">


        {{-- Columna Derecha: Componentes --}}
        <div class="col-md-12">

            {{-- Restricciones Generales --}}
            <div class="card mb-4 border-primary">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold text-primary"> Restricciones Generales</h5>
                </div>
                <div class="card-body pt-4">
                    @livewire('cursos.restricciones.gestionar-restricciones-generales', ['curso' => $curso])
                </div>
            </div>

            {{-- Restricción por Roles --}}
            <div class="card mb-4">
                <div class="card-body">
                    @livewire('cursos.restricciones.gestionar-roles-restriccion', ['curso' => $curso])
                </div>
            </div>

            {{-- Requisitos de Pasos --}}
            <div class="card mb-4">
                <div class="card-body">
                     @livewire('cursos.restricciones.gestionar-pasos-requisito', ['curso' => $curso])
                </div>
            </div>

            {{-- Requisitos de Tareas --}}
            <div class="card mb-4">
                <div class="card-body">
                     @livewire('cursos.restricciones.gestionar-tareas-requisito', ['curso' => $curso])
                </div>
            </div>

            {{-- Sección de Culminación --}}

          <div class="card mb-4">
            <div class="card-body pt-4">
                {{-- Culminar Pasos --}}
                  @livewire('cursos.restricciones.gestionar-pasos-culminar', ['curso' => $curso])

          </div>
          </div>
          <div class="card mb-4">
            <div class="card-body pt-4">

                {{-- Culminar Tareas --}}
                  @livewire('cursos.restricciones.gestionar-tareas-culminar', ['curso' => $curso])
            </div>


        </div>
    </div>
</div>
@endsection
