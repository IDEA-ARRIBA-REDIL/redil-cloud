@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Reactivar Cuenta')

@section('vendor-style')
<!-- Vendor -->
@vite(['resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css'])
@endsection

@section('page-style')
<!-- Page -->
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js',
'resources/assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js',
'resources/assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js',
])
@endsection

@section('page-script')
@vite(['resources/assets/js/pages-auth.js'])
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover bg-login-left">
  <div class="authentication-inner row">


    <!-- Formulario de Reactivación -->
    <div class="d-flex col-12 col-lg-5 align-items-center p-sm-12 p-6 ">
      <div class="w-px-400 mx-auto mt-2 mt-lg-3 pt-5">

        <!-- Logo -->
        <div class="app-brand demo d-flex">
          <a href="{{url('/')}}" class="app-brand-link gap-0 d-flex align-self-end">
             <span class=" d-none app-brand-logo demo">
              @include('_partials.macros',["height"=>"50px", "width"=>"50px", "fill"=> "#3772e4" ])
            </span>
            <span class=" menu-text fw-bold h1 titulo-login">{{config('variables.templateName')}}</span>
          </a>
        </div>
        <!-- /Logo -->

        <h3 class=" mb-1 d-none">{{config('variables.templateName')}}</h3>
        <p class="text-muted fw-light p-0 titulo-descripcion" >Ingresa tu correo para recibir un enlace temporal que restaurará tu cuenta dada de baja.</p>

        <form id="formReactivacion" class="mb-3" action="{{ route('auth.reactivar.enviar') }}" method="POST">
          @csrf

          @include('layouts.status-msn')

          <div class="mb-2">
            <label for="email" class="form-label d-none">Correo Electrónico</label>
            <input type="email" class="form-control input-login" id="email" name="email" value="{{ old('email', $email ?? '') }}" placeholder="Tu Correo Electrónico" autofocus required>
          </div>
          
          <div class="mt-5">
            <button class="btn rounded-pill btn-primary d-grid w-100 titulo-descripcion">
              Enviar enlace de reactivación
            </button>
          </div>
          <div class="mt-3 text-center">
            <a href="{{ route('login') }}" class="d-flex align-items-center btn btn-secondary justify-content-center">
              <i class="ti ti-chevron-left scaleX-n1-rtl"></i> Volver al inicio de sesión
            </a>
          </div>
        </form>

      </div>
    </div>
    <!-- /Formulario de Reactivación -->

     <!-- /Left Text -->
     <div class="d-none d-lg-flex col-lg-7 p-0">
      <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center"  style="background-image: url({{ asset('assets/img/illustrations/bg-redil2.jpg') }}); background-size: cover;">

      </div>
    </div>
    <!-- /Left Text -->
  </div>
</div>
@endsection
