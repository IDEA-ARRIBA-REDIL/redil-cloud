@section('isEscuelasModule', true)

@extends('layouts/layoutMaster')

@section('title', 'Gestionar Materias del Grado')

@section('content')
<h4 class="mb-1 fw-semibold text-primary">Gestionar Materias - {{ $nivel->nombre }}</h4>
<p class="mb-4 text-black">Administra las materias asignadas a este grado.</p>

<div class="row">
    <!-- Lista de Materias Asignadas -->
    <div class="col-md-8">
        <div class="card mb-4">
            <h5 class="card-header">Materias del Nivel</h5>
            <div class="card-body">
                @livewire('escuelas.niveles.gestionar-materias-nivel', ['nivel' => $nivel])
            </div>
        </div>
    </div>

    <!-- Agregar Nueva Materia (Existente) -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Vincular Materia</h5>
                <p class="card-text">Agrega materias existentes de la escuela a este nivel.</p>
                <div class="d-grid gap-2">
                     <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalVincularMateria">
                        <i class="ti ti-link me-1"></i> Vincular Materia
                     </button>
                </div>
            </div>
        </div>

         <div class="card mb-4">
            <div class="card-body">
                <a href="{{ route('escuelas.niveles.index', $nivel->escuela) }}" class="btn btn-label-secondary w-100">
                    <i class="ti ti-arrow-left me-1"></i> Volver al Listado
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Vincular Materia -->
<div class="modal fade" id="modalVincularMateria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCenterTitle">Vincular Materia Existente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @livewire('escuelas.niveles.vincular-materia-nivel', ['nivel' => $nivel])
        </div>
    </div>
</div>

@endsection
