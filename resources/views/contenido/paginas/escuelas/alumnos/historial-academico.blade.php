@extends('layouts.layoutMaster')
@section('isEscuelasModule', true)

@section('title', 'Mi Historial de Calificaciones')

@section('content')
    <h4 class="mb-1 fw-semibold text-primary">Mi Historial de Calificaciones</h4>
    <p class="text-black">Aquí puedes ver el registro completo de todas las materias que has cursado.</p>

       {{-- Paso 3: Resultados (aparece al encontrar historial) --}}
       <div class="row equal-height-row g-4 mt-1">
            @if($historial->isNotEmpty())        
                        {{-- En lugar de una tabla, ahora recorremos y creamos una tarjeta por cada registro --}}
                
                    @foreach($historial as $registro)
                    <div class="col equal-height-col col-12 col-md-6">
                        <div class="card mb-3 border">
                            <div class="card-header border-bottom d-flex p-4" style="background-color:#F9F9F9!important">
                                <div class="flex-fill row">
                                    <div class=" d-flex justify-content-between align-items-center">
                                       
                                         <h5 class="fw-semibold ms-1 text-black m-0">
                                            {{ $registro->materia->nombre }}
                                             
                                        </h5>
                                        <a href="{{ route('escuelas.historial.exportar-boletin', $registro->id) }}" 
                                            class="btn btn-outline-secondary rounded-pill" 
                                            data-bs-toggle="tooltip" 
                                            title="Descargar Boletín en PDF">
                                            <i class="ti ti-file-type-pdf ti-md"></i>
                                            <span class=" ms-2">Descargar boletín</span>
                                        </a>
                                    </div>
                                    <div class=" d-flex justify-content-between align-items-center">
                                     @if($registro->aprobado)
                                                <span class="badge bg-label-success fs-6">Aprobado</span>
                                            @else
                                                <span class="badge bg-label-danger fs-6">No aprobado</span>
                                            @endif
                                        </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-start mt-4">

                                    {{-- Columna 1: Información Principal (Materia y Periodo) --}}
                                    @if($registro->es_homologacion == true)
                                    <div class="col-6 d-flex mb-2 flex-column mt-1">
                                        <small class="text-black">Periodo</small>
                                        <small class="fw-semibold text-black "><i class="ti ti-calendar-week"></i>Homologacion</small>
                                    </div>
                                    <div class="col-6 d-flex mb-2 flex-column mt-1">
                                        <small class="text-black">Horario</small>
                                        <small class="fw-semibold text-black "><i class="ti ti-clock me-1"></i> Homologacion</small>
                                    </div>
                                    <div class="col-6 d-flex mb-2  flex-column mt-1">
                                        <small class="text-black">Aula</small>
                                        <small class="fw-semibold text-black "><i class="ti ti-building-community me-1"></i> Homologacion</small>
                                    </div>
                                    <div class="col-6 d-flex mb-2  flex-column mt-1">
                                        <small class="text-black">Maestro</small>
                                        <small class="fw-semibold text-black "> <i class="ti ti-user me-1"></i> Homologacion</small>
                                    </div>
                                    <div class="col-6 d-flex mb-2  flex-column mt-1">
                                        <small class="text-black">Nota Final</small>
                                        <small class="fw-semibold text-black "> 
                                           {{ $registro->nota_final ?? 'N/A' }} 
                                        </small>
                                    </div>
                                    <div class="col-6 d-flex mb-2  flex-column mt-1">
                                        <small class="text-black">Asistencias</small>
                                        <small class="fw-semibold text-black "> 
                                               {{ $registro->total_asistencias ?? 'N/A' }}
                                        </small>
                                    </div>

                                    @else
                                    <div class="col-6 d-flex mb-2 flex-column mt-1">
                                        <small class="text-black">Periodo</small>
                                        <small class="fw-semibold text-black "><i class="ti ti-calendar-week"></i>{{ $registro->periodo->nombre }}</small>
                                    </div>
                                    <div class="col-6 d-flex mb-2 flex-column mt-1">
                                        <small class="text-black">Horario</small>
                                        <small class="fw-semibold text-black "><i class="ti ti-clock me-1"></i> {{ $registro->detalles_matricula->horario }}</small>
                                    </div>
                                    <div class="col-6 d-flex mb-2  flex-column mt-1">
                                        <small class="text-black">Aula</small>
                                        <small class="fw-semibold text-black "><i class="ti ti-building-community me-1"></i> {{ $registro->detalles_matricula->sede }} / {{ $registro->detalles_matricula->aula }}</small>
                                    </div>
                                    <div class="col-6 d-flex mb-2  flex-column mt-1">
                                        <small class="text-black">Maestro</small>
                                        <small class="fw-semibold text-black "> <i class="ti ti-user me-1"></i> Maestro: {{ $registro->detalles_matricula->maestro }}</small>
                                    </div>
                                    <div class="col-6 d-flex mb-2  flex-column mt-1">
                                        <small class="text-black">Nota Final</small>
                                        <small class="fw-semibold text-black "> 
                                           {{ $registro->nota_final ?? 'N/A' }} 
                                        </small>
                                    </div>
                                    <div class="col-6 d-flex mb-2  flex-column mt-1">
                                        <small class="text-black">Asistencias</small>
                                        <small class="fw-semibold text-black "> 
                                               {{ $registro->total_asistencias ?? 'N/A' }}
                                        </small>
                                    </div>
                                    
                                    @endif

                                    
                                </div>
                            </div>
                        </div>
                    </div>
    
                    @endforeach
              
            @elseif($selectedUser)
                <div class="alert alert-info">
                    El alumno seleccionado no tiene historial académico registrado.
                </div>
            @endif
    </div>
@endsection