@extends('layouts/layoutMaster')

@section('title', 'Gestión de Hitos')

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
        'resources/assets/vendor/libs/flatpickr/flatpickr.scss'
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
        'resources/assets/vendor/libs/flatpickr/flatpickr.js'
    ])
@endsection

@section('content')
    @livewire('hitos.gestionar-hitos')
@endsection
