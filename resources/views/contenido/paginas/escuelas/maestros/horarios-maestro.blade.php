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

    {{-- INICIO DE LA SECCIÓN DE HORARIOS EN CARDS --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h5 class="mb-0 fw-semibold text-black">Listado de horarios asignados</h5>
    </div>

    <div class="row mb-5">
        @forelse ($horariosAsignados as $horarioAsignado)
            @php
                $descripcionMateria = $horarioAsignado->materiaPeriodo?->descripcionResumen(20);
            @endphp
            <div class="col-equal-height-col col-12 col-md-6 mb-4">
                <div class="h-100 card d-flex flex-column">
                    <div class="card-header pb-3">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div>
                                <h5 class="mb-0 fw-semibold text-black lh-sm">
                                    {{ $horarioAsignado->materiaPeriodo?->materia?->nombre ?? 'N/A' }}
                                </h5>
                                @if ($descripcionMateria)
                                    <small class="text-muted d-block mt-1">({{ $descripcionMateria }})</small>
                                @endif
                            </div>

                            <form
                                action="{{ route('maestros.eliminarHorarioAsignado', ['maestro' => $maestro, 'horarioMateriaPeriodo' => $horarioAsignado]) }}"
                                method="POST" class="d-inline form-eliminar-asignacion"
                                data-horario-info="{{ $horarioAsignado->materiaPeriodo?->materia?->nombre ?? '' }} ({{ $horarioAsignado->horarioBase?->dia_semana ?? '' }} {{ $horarioAsignado->horarioBase?->hora_inicio_formato ?? '' }})">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="border-radius: 20px;"
                                    class="btn btn-sm btn-icon border waves-effect waves-light"
                                    title="Eliminar asignación" aria-label="Eliminar asignación">
                                    <i class="ti ti-trash text-danger"></i>
                                </button>
                            </form>
                        </div>

                        <div class="d-flex flex-row align-items-center mt-3">
                            <div class="d-flex flex-column">
                                <small class="text-muted"><i class="ti ti-calendar-event me-1"></i>Periodo:</small>
                                <small class="fw-semibold text-black">
                                    {{ $horarioAsignado->materiaPeriodo?->periodo?->nombre ?? 'N/A' }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-0 flex-grow-1">
                        {{-- Bloque destacado de horario y día --}}
                        <div class="p-2 mb-3 rounded border bg-lighter d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div class="d-flex align-items-center">
                                <div class="badge rounded bg-label-primary p-2 me-2">
                                    <i class="ti ti-clock fs-5"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block fw-medium" style="font-size: 0.72rem;">Horario</small>
                                    <span class="fw-bold text-black lh-1">
                                        {{ $horarioAsignado->horarioBase?->hora_inicio_formato ?? 'N/A' }} -
                                        {{ $horarioAsignado->horarioBase?->hora_fin_formato ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <span class="badge bg-label-primary rounded-pill fw-semibold">
                                    <i class="ti ti-calendar-week me-1"></i>{{ $horarioAsignado->horarioBase?->dia_semana ?? 'N/A' }}
                                </span>
                            </div>
                        </div>

                        <div class="row justify-content-between">
                            <div class="col-12 col-sm-6 mb-2 mb-sm-0">
                                <div class="d-flex flex-column text-start">
                                    <small class="text-muted"><i class="ti ti-building-skyscraper me-1"></i>Sede:</small>
                                    <small class="fw-semibold text-black">
                                        {{ $horarioAsignado->horarioBase?->aula?->sede?->nombre ?? 'N/A' }}
                                    </small>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="d-flex flex-column text-start">
                                    <small class="text-muted"><i class="ti ti-door me-1"></i>Aula:</small>
                                    <small class="fw-semibold text-black">
                                        {{ $horarioAsignado->horarioBase?->aula?->nombre ?? 'N/A' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center" role="alert">
                    Este maestro no tiene horarios asignados todavía.
                </div>
            </div>
        @endforelse
    </div>

    @if ($horariosAsignados->hasPages())
        <div class="row mt-4">
            <div class="col-12 d-flex justify-content-center">
                {{ $horariosAsignados->links() }}
            </div>
        </div>
    @endif
    {{-- FIN DE LA SECCIÓN DE HORARIOS EN CARDS --}}

    {{-- Incluir el componente Livewire del modal --}}
    @livewire('maestros.asignar-horario-modal')

@endsection
