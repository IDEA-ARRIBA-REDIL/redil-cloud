@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp


@extends('layouts/blankLayout')

@section('title', 'Menseje')

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
                    <img src="{{ Storage::url('generales/img/otros/tiempo_con_Dios_respuesta.png') }}" class="img-fluid w-50 p-0">
                    <h2 class="text-black fw-bold mb-0 lh-sm mt-3">{{ $titulo }}</h2>
                    <p class="text-black mt-1 mb-5">
                      {{ $descripcion }}
                    </p>

                    <div class="d-grid gap-2 d-sm-flex justify-content-center mt-8 pt-8">
                      <a href="{{ $origen == 'añadirReservas' ? route('reporteReunion.añadirReservas', [$reporte->id]) : route('reporteReunion.'.$origen ) }}" type="button" class="btn btn-primary rounded-pill px-10 py-3" >
                        <span class="align-middle me-sm-1 me-0 ">Salir</span>
                      </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection
