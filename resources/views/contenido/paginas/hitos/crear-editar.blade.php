@extends('layouts/layoutMaster')

@section('title', isset($hitoId) ? 'Editar Hito' : 'Crear Hito')

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/select2/select2.scss',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
        'resources/assets/vendor/libs/flatpickr/flatpickr.scss'
    ])
    <style>
        .select2-container--default .select2-selection--multiple {
            min-height: 38px;
            border-color: #dbdade;
        }
        .select2-container--default .select2-selection--single {
            height: 38px;
            border-color: #dbdade;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-left: 12px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
            right: 8px;
        }
        .select2-dropdown {
            z-index: 9999 !important;
        }
    </style>
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/select2/select2.js',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
        'resources/assets/vendor/libs/flatpickr/flatpickr.js'
    ])
@endsection

@section('content')
    @livewire('hitos.crear-editar-hito', ['hitoId' => $hitoId ?? null])
@endsection
