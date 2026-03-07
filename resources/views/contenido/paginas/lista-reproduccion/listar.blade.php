@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Campos usuario')

<!-- Page -->
@section('vendor-style')

@vite([
])

@endsection

@section('vendor-script')
  @vite([
  ])
@endsection


@section('page-script')

@endsection

@section('content')
  <h4 class="mb-1 fw-semibold text-primary">Lista de reproducción</h4>
  <p class="mb-8">Aquí podrás gestionar la lista de reproducción</p>

   @livewire('TiempoConDios.gestionar-lista-reproduccion')

@endsection
