@extends('layouts/layoutMaster')

@section('title', $informe->nombre)

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('content')
    @livewire('informes-personalizados.mega-informe', ['informeId' => $informe->id])
@endsection
