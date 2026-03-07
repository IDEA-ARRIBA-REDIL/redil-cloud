@section('isEscuelasModule', true)

@extends('layouts/layoutMaster')

@section('title', 'Gestionar Grados')

@section('content')
    <h4 class="mb-1 fw-semibold text-primary">Gestionar Grados - {{ $escuela->nombre }}</h4>
    <p class="mb-4 text-black">Aquí podrás administrar los grados académicos y sus materias.</p>

<!-- Basic Bootstrap Table -->
<div class="card">
    <div class="card-header flex-column flex-md-row">
        <div class="head-label text-center">
            <h5 class="card-title mb-0">Listado de Grados</h5>
        </div>
        <div class="dt-action-buttons text-end pt-3 pt-md-0">
            <div class="dt-buttons">
                <a href="{{ route('escuelas.niveles.crear', $escuela) }}" class="dt-button create-new btn btn-primary">
                    <span><i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Nuevo Grado</span></span>
                </a>
            </div>
        </div>
    </div>

    <div class="card-body">
        @livewire('escuelas.niveles.gestionar-niveles', ['escuela' => $escuela])
    </div>
</div>
@endsection
