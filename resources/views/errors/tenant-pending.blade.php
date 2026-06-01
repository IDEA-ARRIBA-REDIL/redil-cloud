@extends('layouts.blankLayout')

@section('title', 'Cuenta en Revisión')

@section('content')
<div class="container-xxl container-p-y text-center">
  <div class="misc-wrapper">
    <h2 class="mb-2 mx-2">Cuenta Pendiente de Aprobación</h2>
    <p class="mb-4 mx-2">
      Tu registro de REDIL Cloud está siendo procesado por nuestro equipo.
      <br>Pronto nos pondremos en contacto contigo para finalizar la activación de tu cuenta.
    </p>
    <a href="{{ env('APP_URL') }}" class="btn btn-primary">Volver al Inicio</a>
    <div class="mt-4">
      <img src="{{ asset('assets/img/illustrations/girl-doing-yoga-light.png') }}" alt="Pendiente" width="500" class="img-fluid">
    </div>
  </div>
</div>
@endsection
