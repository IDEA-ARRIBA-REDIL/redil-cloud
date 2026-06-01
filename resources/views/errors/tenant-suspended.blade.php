@extends('layouts.blankLayout')

@section('title', 'Cuenta Suspendida')

@section('content')
<div class="container-xxl container-p-y text-center">
  <div class="misc-wrapper">
    <h2 class="mb-2 mx-2">Cuenta Suspendida</h2>
    <p class="mb-4 mx-2">
      Tu suscripción a REDIL Cloud ha sido suspendida.
      <br>Por favor, comunícate con soporte para reactivar tu cuenta y regularizar tu estado.
    </p>
    <a href="https://wa.me/57300000000" class="btn btn-primary" target="_blank">Contactar Soporte</a>
    <div class="mt-4">
      <img src="{{ asset('assets/img/illustrations/page-misc-error-light.png') }}" alt="Suspendida" width="500" class="img-fluid">
    </div>
  </div>
</div>
@endsection
