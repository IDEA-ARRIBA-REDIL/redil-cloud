@extends('layouts.layoutMaster')

@section('isEscuelasModule', true)

@section('title', 'Gestionar Homologaciones')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('page-script')

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
    <h4 class="mb-1 fw-semibold text-primary">Gestión de homologaciones</h4>
    <p class="text-black">Crea registros de materias aprobadas para alumnos que cursaron fuera del sistema.</p>

    {{-- Aquí se renderizará nuestro componente de Livewire --}}
    @livewire('homologaciones.gestionar-homologaciones')

@endsection