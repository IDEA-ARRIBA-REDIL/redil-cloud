@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp


@extends('layouts/blankLayout')

@section('title', 'Inscripción exitosa')

@section('vendor-style')


@section('vendor-script')
@endsection


@section('page-script')
@endsection

@section('content')

<div class="d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row">
            <div class="col-12 col-lg-12 d-flex align-items-center">
                <div class=" mx-auto my-auto text-center">
                    <img src="{{ Storage::url('generales/img/otros/dibujo_respuesta.png') }}" class="img-fluid w-50 p-0">
                    <h2 class="text-black fw-bold mb-0 lh-sm">Reporte generado</h2>
                    <p class="text-black mt-1 mb-5">
                      Tu reporte de grupo ha sido generado con éxito.
                    </p>


                    <div class="row text-start mt-5 shadow rounded p-4">

                      <p class="col-12 fw-semibold text-black mb-2"> Información </p>

                      <div class="col-6 d-flex flex-column">
                        <small class="text-black">Se realizó el grupo</small>
                        <small class="fw-semibold text-black ">{{ $reporte->no_reporte ? 'No' : 'Si' }}</small>
                      </div>
                      <div class="col-6 d-flex flex-column">
                        <small class="text-black">Fecha</small>
                        <small class="fw-semibold text-black ">{{ $reporte->fecha }}</small>
                      </div>
                    </div>



                    <div class="d-grid gap-2 d-sm-flex justify-content-center mt-5 pt-8">
                      <a href="{{ route('grupo.lista') }}" type="button" class="btn btn-primary rounded-pill px-10 py-3" >
                        <span class="align-middle me-sm-1 me-0 ">Salir</span>
                      </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection
