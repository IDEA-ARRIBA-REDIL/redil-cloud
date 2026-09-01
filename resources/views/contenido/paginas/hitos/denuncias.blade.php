@extends('layouts/layoutMaster')

@section('title', 'Bandeja de Denuncias - Hitos')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('content')
    @livewire('hitos.gestionar-denuncias')
@endsection
