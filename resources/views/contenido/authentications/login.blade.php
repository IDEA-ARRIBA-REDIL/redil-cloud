
@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Login')

@section('vendor-style')
<!-- Vendor -->
@vite(['resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css'])
<style>

</style>
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


    <!-- Login -->
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
        <p class="text-muted  fw-light p-0 titulo-descripcion" >{{config('variables.templateDescriptionLogin')}}</p>

        <form id="" class="mb-3" action="{{ route('login') }}" method="POST">
          @csrf

          @include('layouts.status-msn')

          <div class="mb-2">
            <label for="email" class="form-label d-none">Email or Username</label>
            <input  type="text" class="form-control input-login" id="email" name="email" value="{{ old('email',$emailDefault) }}" placeholder="Email" autofocus>
          </div>
          <div class="mb-2 form-password-toggle">
            <div class="d-flex justify-content-between">
              <label class="form-label d-none" for="password">Password</label>
            </div>
            <div class="input-group input-group-merge">
              <input type="password" id="password" class=" input-login form-control" name="password" placeholder="Contraseña" aria-describedby="password" />
              <span style=""  class="input-group-text input-login  cursor-pointer"><i class="ti ti-eye-off"></i></span>
            </div>
          </div>
          <div class="">

          <div class="mt-4">
            <a href="{{ route('password.request') }}">
              <p class="mb-1 text-muted p-0 titulo-descripcion"> ¿Olvidaste tu contraseña? </p>
            </a>
            </div>
            <div class="form-check d-none">
              <input class="form-check-input" type="checkbox" id="remember-me">
              <label class="form-check-label  text-muted titulo-descripcion " for="remember-me">
                Recordarme
              </label>
            </div>
          </div>
          <div class="mt-5">
            <button class="btn rounded-pill btn-primary d-grid w-100 titulo-descripcion">
              Ingresar
            </button>
          </div>
        </form>

        <div id="container-redes" class="container mt-4">
          <div class="divider m-1">
            <div class="divider-text text-muted titulo-descripcion">Siguenos en redes</div>
          </div>

          <div class="d-flex justify-content-center">

            <a href="javascript:;" class="btn btn-icon btn-label-facebook mx-1">
              <i class="tf-icons fa-brands fa-facebook-f fs-5"></i>
            </a>

            <a href="javascript:;" class="btn btn-icon btn-label-instagram mx-1">
              <i class="tf-icons fa-brands fa-instagram fs-5"></i>
            </a>

            <a href="javascript:;" class="btn btn-icon btn-label-youtube mx-1">
              <i class="tf-icons fa-brands fa-youtube fs-5"></i>
            </a>

          </div>
        </div>

        <div id="container-footer" class="mt-10">
          <p id="footer" class="text-algin-start">
            <span class="titulo-descripcion">¿No tienes cuenta ?</span>
            @foreach($formularios as $formulario)
            <a href="{{ route('usuario.nuevoExterior', $formulario) }}">
              <span>{{ $formulario->label }} </span>
            </a>
            @endforeach
          </p>
        </div>

      </div>
    </div>
    <!-- /Login -->

     <!-- /Left Text -->
     <div class="d-none d-lg-flex col-lg-7 p-0">
      <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center"  style="background-image: url({{ asset('assets/img/illustrations/bg-redil2.jpg') }}); background-size: cover;">

      </div>
    </div>
    <!-- /Left Text -->
  </div>
</div>
@endsection
