@section('isEscuelasModule', true)

@extends('layouts/layoutMaster')

@section('title', 'Matrícula - Selección de Grado')

@section('content')
<h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Escuelas / Matrícula /</span> Selección de Grado
</h4>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Selecciona el Grado a Matricular</h5>
            </div>
            <div class="card-body">
                @livewire('matricula.matricula-nivel-process', ['escuela' => $escuela, 'periodo' => $periodo, 'niveles' => $niveles])
            </div>
        </div>
    </div>
</div>
@endsection
