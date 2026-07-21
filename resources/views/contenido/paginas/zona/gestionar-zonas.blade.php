@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Zonas')


@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
    @vite([
      'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
      'resources/assets/vendor/libs/select2/select2.scss'
    ])
@endsection

@section('vendor-script')
    @vite([
      'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
      'resources/assets/vendor/libs/select2/select2.js'
    ])
@endsection

@section('page-script')

@endsection

@section('content')
    <h4 class=" mb-1 fw-semibold text-primary">Gestionar zonas</h4>
    <p class="mb-4 text-black">Administra las diferentes zonas.</p>

    @livewire('Zonas.gestionar-zonas')

@endsection
