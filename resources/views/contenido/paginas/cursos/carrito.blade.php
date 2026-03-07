@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Carrito de Compras LMS - CRECER')

@section('page-style')
    <!-- Aquí puedes añadir estilos personalizados adicionales si es necesario -->
@endsection

@section('page-script')
    <!-- Aquí puedes añadir scripts adicionales si es necesario -->
@endsection

@section('content')
    <!-- Inyectamos el componente Livewire para el Carrito -->
    @livewire('cursos.carrito-compras')
@endsection
