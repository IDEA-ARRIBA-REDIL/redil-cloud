@php
$configData = Helper::appClasses();
$isFooter = ($isFooter ?? false);
@endphp

@extends('layouts/blankLayout')

@section('content')
    @livewire('carrito.destinatario', ['actividad' => $actividad])
@endsection
