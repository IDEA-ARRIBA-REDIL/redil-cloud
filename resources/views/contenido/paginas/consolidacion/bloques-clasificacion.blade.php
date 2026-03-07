@extends('layouts/layoutMaster')

@section('title', 'Bloques de Clasificación de Asistentes')

@section('page-style')
@vite([
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
])
@endsection

@section('vendor-script')
@vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/select2/select2.js',
])
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Consolidación /</span> Bloques de Clasificación
</h4>

<div class="card">
    <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Gestionar Bloques</h5>
    </div>
    <div class="card-body p-0">
        @livewire('consolidacion.gestionar-bloques-clasificacion')
    </div>
</div>
@endsection
