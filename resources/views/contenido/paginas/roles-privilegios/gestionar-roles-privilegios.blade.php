@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Inicio')


@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')

@endsection

@section('content')

     <h4 class=" mb-1 fw-semibold text-primary">Gestionar roles</h4>
    <p class="mb-4 text-black">Administra los roles con sus privilegios.</p>

    @livewire('RolesPrivilegios.gestionar-roles-privilegios')

@endsection
