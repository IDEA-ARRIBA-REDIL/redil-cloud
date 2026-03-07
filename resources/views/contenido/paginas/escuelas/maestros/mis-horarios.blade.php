{{-- Asume que 'layouts.layoutMaster' es tu layout principal --}}
@section('isEscuelasModule', true)
{{-- resources/views/maestros/horarios_asignados.blade.php (o el nombre que corresponda a esta vista) --}}
@extends('layouts.layoutMaster')

@section('title', 'Horarios Asignados a ' . ($maestro->user->name ?? 'Maestro'))

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    {{-- Esta sección permanece vacía según tu vista original --}}
@endsection

@section('content')
    @include('layouts.status-msn')

    <div class="row mb-5 mt-5">
        <div class="me-auto ">
            <h4 class="mb-1 fw-semibold text-primary">Horarios asignados a: {{ $maestro->user->nombre(3) ?? 'N/A' }}
            </h4>
            <p class="mb-0">Puedes ver tus horarios asignados.</p> {{-- Texto corregido para que tenga sentido con el contexto de la vista --}}
        </div>
    </div>


    {{-- INICIO DE LA SECCIÓN MODIFICADA --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Listado de horarios asignados</h5>
        </div>
        <div class="card-body">
            {{-- Encabezados para la vista de escritorio (md y superior) --}}
            <div class="row d-none d-md-flex fw-bold mb-3 border-bottom pb-2 align-items-center">
                <div class="col-md-2">Periodo</div>

                {{-- INICIO: Columna de Materia con Flechas de Ordenamiento --}}
                <div class="col-md-2">
                    Materia
                    <span class="ms-1">
                        {{-- Enlace para ordenar ascendente --}}
                        <a href="{{ route('maestros.misHorarios', ['user' => $maestro->user_id, 'sort' => 'materia_nombre', 'direction' => 'asc']) }}"
                            class="text-decoration-none {{ $sortField == 'materia_nombre' && $sortDirection == 'asc' ? 'text-primary' : 'text-muted' }}">
                            &#9650; {{-- Triángulo hacia arriba --}}
                        </a>
                        {{-- Enlace para ordenar descendente --}}
                        <a href="{{ route('maestros.misHorarios', ['user' => $maestro->user_id, 'sort' => 'materia_nombre', 'direction' => 'desc']) }}"
                            class="text-decoration-none {{ $sortField == 'materia_nombre' && $sortDirection == 'desc' ? 'text-primary' : 'text-muted' }}">
                            &#9660; {{-- Triángulo hacia abajo --}}
                        </a>
                    </span>
                </div>
                {{-- FIN: Columna de Materia --}}

                <div class="col-md-1">Día</div>
                <div class="col-md-2">Horario</div>
                <div class="col-md-2">Sede</div>
                <div class="col-md-1">Aula</div>
                <div class="col-md-2">Acciones</div>
            </div>

            @forelse ($horariosAsignados as $horarioAsignado)
                {{-- CAMBIO: Añadimos un estilo condicional para el color de fondo en las filas pares --}}
                <div class="schedule-item-card card mb-3" style="{{ $loop->even ? 'background-color: #f3f3f3;' : '' }}">
                    <div class="card-body p-0">
                        <div class="row align-items-start p-3">
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
                            <div class="col-12 col-md-1 mb-2 mb-md-0">
                                <strong class="d-md-none">Aula: </strong>
                                {{ $horarioAsignado->horarioBase->aula->nombre ?? 'N/A' }}
                            </div>
                            <div class="col-12 col-md-2 text-md-start mt-2 mt-md-0">
                          
                                <a class="btn btn-outline-secondary rounded-pill"
                                    href="{{ route('maestros.dashboardClase', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}">
                                    Acceder
                                </a>
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
                {{-- Los links de paginación ya incluirán los parámetros de ordenamiento gracias al cambio en el controlador --}}
                {{ $horariosAsignados->links() }}
            </div>
        @endif
    </div>
    {{-- FIN DE LA SECCIÓN MODIFICADA --}}


@endsection
