@extends('layouts/contentNavbarLayout')

@section('title', 'Contenido del plan lector')

@section('content')
    @livewire('plan-lector.gestionar-contenido', ['plan' => $plan])
@endsection
