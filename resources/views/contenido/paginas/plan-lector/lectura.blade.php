@extends('layouts/blankLayout')

@section('title', $plan->titulo . ' - Plan Lector')

@section('page-style')
<style>
    /* Estilos base para la vista inmersiva */
    body {
        background-color: #f8f9fa;
        overflow-x: hidden;
    }
    .main-reader-container {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }
</style>
@endsection

@section('content')
<div class="main-reader-container">
    @livewire('plan-lector.lectura', [
        'plan' => $plan, 
        'dia_inicial_id' => $dia_inicial
    ])
</div>
@endsection
