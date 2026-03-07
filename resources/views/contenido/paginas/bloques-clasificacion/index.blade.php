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


 <h4 class="mb-1 fw-semibold text-primary">Gestión de bloques de clasificación de asistentes</h4>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @livewire('bloque-clasificacion.gestionar-bloques')
                </div>
            </div>
        </div>
    </div>
@endsection
