@extends('layouts.layoutMaster')

@section('title', 'Gestión de Bloques - Consolidación')

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/select2/select2.scss',
        'resources/assets/vendor/libs/animate-css/animate.scss',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/select2/select2.js',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
    ])
@endsection

@section('content')

    <h4 class="mb-1 fw-semibold text-primary">Gestión de bloques de sedes</h4>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @livewire('consolidacion.gestionar-bloques')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        // Si hay scripts globales necesarios para el contenedor de la página
    </script>
@endsection
