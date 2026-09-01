@extends('layouts/layoutMaster')

@section('title', 'Control de Asistencias - Hito')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('content')
    @livewire('hitos.gestionar-asistencias', ['hito' => $hito])
@endsection
