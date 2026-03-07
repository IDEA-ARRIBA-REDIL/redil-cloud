@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Checkout Cursos - CRECER')

@section('page-style')
    <!-- Aquí puedes añadir estilos personalizados adicionales si es necesario -->
@endsection

@section('page-script')
    <!-- Aquí puedes añadir scripts adicionales si es necesario -->
@endsection

@section('content')
    <!-- Inyectamos el componente Livewire para el Checkout -->
    @livewire('cursos.checkout-cursos')
@endsection
