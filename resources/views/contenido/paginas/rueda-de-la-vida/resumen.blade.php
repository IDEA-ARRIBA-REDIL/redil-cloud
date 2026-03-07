@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
use Carbon\Carbon;
@endphp


@extends('layouts/blankLayout')

@section('title', 'Resumen RV')

@section('vendor-style')

<style>
  .boxShadow{
    padding: 19px;
    box-shadow: 0px 3px 7px #d4d4d4;
    border-radius: 9px;
    min-width: 350px;
    margin-bottom: 7px;
  }

  .texto-danger{
    color:#AA1A1E !important;
  }
</style>

@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/apex-charts/apexcharts.js',
])
@endsection


@section('page-script')
@vite([
'resources/assets/js/form-basic-inputs.js',

])



@endsection

@section('content')

<div class="col-12">
  <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center">
    <div class="col-3 text-start">
      <button type="button" class="btn rounded-pill waves-effect waves-light text-white prev-step d-none">
        <span class="ti-xs ti ti-arrow-left me-2"></span>
        <span class="d-none d-md-block fw-normal">Volver</span>
      </button>
    </div>
    <div class="col-6 pl-5 text-center">
      <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">{{$configuracionRv->nombre_general}} - Historial</h5>
    </div>
    <div class="col-3 text-end">
      <a href="{{ route('dashboard')}}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
        <span class="d-none d-md-block fw-normal">Salir</span>
        <span class="ti-xs ti ti-x mx-2"></span>
      </a>
    </div>
  </nav>

  <div class="col-12 col-sm-8 offset-sm-2 col-lg-8 offset-lg-2 p-7" style="padding-bottom: 100px;">

            <h3  class="fw-semibold mt-5">{{$configuracionRv->nombre_general}}</h3>
            <div class="row">
                @foreach ($seccionesContadorPromedios as $seccion)
                <div class=" col-lg-6 pb-3 col-sm-12 "> 
                    <div id="container-promedios" class="boxShadow col-12" >
                        <div style="float:left;"  class="ps-3  justify-content-start">
                            {{$seccion->nombre_seccion}}
                        </div>
                        <div style="text-align:right;" class="flex-fill pe-3 justify-content-end">
                            <h6 class="fw-semibold @if($seccion->promedio($rueda->id) >= $configuracionRv->promedio_general)text-success @else texto-danger @endif "> 
                                {{$configuracionRv->label_promedio_general}} : {{number_format($seccion->promedio($rueda->id), 1, ',', ' ') }} 
                            </h6>
                        </div>
                    </div>
                </div> 
                @endforeach

                <div class=" col-lg-12 pb-3 col-sm-12 "> 
                    <div id="container-promedios" class="boxShadow col-12" >
                      
                        <span class="@if($rueda->promedio_general >= $configuracionRv->promedio_general)text-success @else texto-danger @endif ">
                            <h4 style="color:#333;float:left"  class="fw-semibold "> 
                            {{$configuracionRv->label_promedio_general}} total : </h4style>
                            <h4 style="text-align: right" class="fw-semibold  @if($rueda->promedio_general >= $configuracionRv->promedio_general)text-success @else texto-danger @endif "> 
                               {{$configuracionRv->label_promedio_general}} :  {{number_format($rueda->promedio_general, 1, ',', ' ') }} 
                            </h4>   </span>
                        
                    </div>
                </div> 
            </div>
            <hr style="margin-top:10px; margin-bottom:30px" >
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 ">
                
                    @foreach($metasRv as $meta)
                    <div class="form-group mb-4">
                      <h4 class="fw-normal"> {{$meta->nombre}}</h4>
                      @php
                      $respuestaMeta=$rueda->metas()->wherePivot('metas_id',$meta->id)->first();
                      @endphp
                        <h6 class="form-control"> {{$respuestaMeta->pivot->valor}} </h6>
                    </div>
                    <div class="row">
                      <h4 class="fw-normal"> {{$configuracionRv->nombre_habitos}}</h4>
                      
                      @foreach($meta->habitos as $habito)

                    
                      @php
                      $respuestaHabitos=$rueda->habitos()->wherePivot('habitos_rueda_vida_id',$habito->id)->first();
                      @endphp
                      <div class="col-lg-6 col-md-6 col-sm-12 mb-4 ">
                      
                        <h6 class="form-control">  @if(isset($respuestaHabitos->id) ) {{$respuestaHabitos->pivot->valor}}@else No registrado @endif </h6>
                      </div>
                      @endforeach
                    </div>
                    <hr>
                    @endforeach
                </div>
            </div>
  </div>
</div>


@endsection
