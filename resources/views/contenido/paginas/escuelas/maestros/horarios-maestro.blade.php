{{-- Asume que 'layouts.layoutMaster' es tu layout principal --}}
@section('isEscuelasModule', true)

@extends('layouts.layoutMaster')

@section('title', 'Horarios Asignados a ' . ($maestro->user->name ?? 'Maestro'))

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    {{-- El script que me proporcionaste se mantiene igual --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('recargarPagina', (event) => {
                window.location.reload();
            });

            Livewire.on('mensajeExito', (message) => {
                console.log('Mensaje Éxito:', message);
            });

            Livewire.on('mensajeError', (message) => {
                console.error('Mensaje Error:', message);
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formsEliminarAsignacion = document.querySelectorAll('.form-eliminar-asignacion');
            formsEliminarAsignacion.forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    const horarioInfo = this.dataset.horarioInfo || 'este horario';
                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: `Se eliminará la asignación del maestro a ${horarioInfo}. ¡No podrás revertirlo!`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, ¡eliminar asignación!',
                        cancelButtonText: 'Cancelar',
                        customClass: {
                            confirmButton: 'btn btn-danger me-2',
                            cancelButton: 'btn btn-label-secondary'
                        },
                        buttonsStyling: false
                    }).then(result => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });

            Livewire.on('mensajeExito', mensaje => {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: mensaje,
                    showConfirmButton: false,
                    timer: 2000
                });
            });
            Livewire.on('mensajeError', mensaje => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: mensaje
                });
            });
            Livewire.on('mensajeInfo', mensaje => {
                Swal.fire({
                    icon: 'info',
                    title: 'Información',
                    text: mensaje
                });
            });

            Livewire.on('horarioAsignadoCorrectamente', () => {
                console.log('Horario asignado, considera refrescar la lista.');
            });
        });
    </script>
@endsection

@section('content')
    {{-- Mensajes Flash (estos son para acciones del controlador, no de Livewire directamente) --}}
    @include('layouts.status-msn')

    <div class="row mb-5 mt-5">
        <div class="me-auto ">
            <h4 class="mb-1 fw-semibold text-primary">Horarios asignados a: {{ $maestro->user->nombre(3) ?? 'N/A' }}
            </h4>
            <p class="mb-0 text-black">Gestiona los horarios y asigna nuevos.</p>
            {{-- Botón para abrir el modal Livewire --}}
            <button class="btn mt-4 btn-primary rounded-pill" type="button"
                onclick="Livewire.dispatch('abrirModalAsignarHorario', { maestroId: {{ $maestro->id }} })">
                <i class="ti ti-plus me-1"></i> Asignar nuevo horario
            </button>
        </div>
    </div>

    {{-- INICIO DE LA SECCIÓN MODIFICADA --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Listado de horarios asignados</h5>
        </div>
        <div class="card-body">
            {{-- Encabezados para la vista de escritorio (md y superior) --}}
            <div class="row d-none d-md-flex fw-bold mb-3 border-bottom pb-2">
                <div class="col-md-2 text-black">Periodo</div>
                <div class="col-md-2 text-black">Materia</div>
                <div class="col-md-1 text-black">Día</div>
                <div class="col-md-2 text-black">Horario</div>
                <div class="col-md-2 text-black">Sede</div>
                <div class="col-md-2 text-black">Aula</div>
                <div class="col-md-1 text-center text-black">Acciones</div>
            </div>

            @forelse ($horariosAsignados as $horarioAsignado)
                <div class="schedule-item-card card mb-3 shadow-sm"> {{-- Tarjeta para cada horario --}}
                    <div class="card-body p-2">
                        <div class="row align-items-center">
                            <div class="col-12 col-md-2 mb-2 mb-md-0">
                                <strong class="d-md-none">Periodo: </strong>
                                {{ $horarioAsignado->materiaPeriodo->periodo->nombre ?? 'N/A' }}
                            </div>
                            <div class="col-12 col-md-2 mb-2 mb-md-0">
                                <strong class="d-md-none">Materia: </strong>
                                {{ $horarioAsignado->materiaPeriodo->materia->nombre ?? 'N/A' }}
                                {{ $horarioAsignado->materiaPeriodo->descripcion ? '(' . $horarioAsignado->materiaPeriodo->descripcion . ')' : '' }}
                            </div>
                            <div class="col-12 col-md-1 mb-2 mb-md-0">
                                <strong class="d-md-none">Día: </strong>
                                {{ $horarioAsignado->horarioBase->dia_semana ?? 'N/A' }}
                            </div>
                            <div class="col-12 col-md-2 mb-2 mb-md-0">
                                <strong class="d-md-none">Horario: </strong>
                                {{ $horarioAsignado->horarioBase->hora_inicio_formato ?? 'N/A' }} -
                                {{ $horarioAsignado->horarioBase->hora_fin_formato ?? 'N/A' }}
                            </div>
                            <div class="col-12 col-md-2 mb-2 mb-md-0">
                                <strong class="d-md-none">Sede: </strong>
                                @if ($horarioAsignado->horarioBase->aula->sede)
                                    {{ $horarioAsignado->horarioBase->aula->sede->nombre }}
                                @else
                                    N/A
                                @endif
                            </div>
                            <div class="col-12 col-md-2 mb-2 mb-md-0">
                                <strong class="d-md-none">Aula: </strong>
                                {{ $horarioAsignado->horarioBase->aula->nombre ?? 'N/A' }}
                            </div>
                            <div class="col-12 col-md-1 text-md-center mt-2 mt-md-0">
                                {{-- La etiqueta strong para "Acciones" en móvil se puede omitir si el botón es suficientemente descriptivo --}}
                                {{-- <strong class="d-md-none">Acciones: </strong> --}}
                                <form
                                    action="{{ route('maestros.eliminarHorarioAsignado', ['maestro' => $maestro, 'horarioMateriaPeriodo' => $horarioAsignado]) }}"
                                    method="POST" class="d-inline form-eliminar-asignacion"
                                    data-horario-info="{{ $horarioAsignado->materiaPeriodo->materia->nombre ?? '' }} ({{ $horarioAsignado->horarioBase->dia_semana ?? '' }} {{ $horarioAsignado->horarioBase->hora_inicio_formato ?? '' }})">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon border waves-effect waves-light"
                                        title="Eliminar asignación">
                                        <i class="ti ti-trash text-danger"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info text-center" role="alert">
                    Este maestro no tiene horarios asignados todavía.
                </div>
            @endforelse
        </div> {{-- Fin card-body --}}

        @if ($horariosAsignados->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $horariosAsignados->links() }}
            </div>
        @endif
    </div>
    {{-- FIN DE LA SECCIÓN MODIFICADA --}}

    {{-- Incluir el componente Livewire del modal --}}
    @livewire('maestros.asignar-horario-modal')

@endsection
