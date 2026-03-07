@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Mi tiempo con Dios')

@section('vendor-style')

@section('page-style')
<style>
  body {
    overflow-x: hidden;
  }
</style>
@endsection

@section('page-style')
  @vite([
  'resources/assets/vendor/scss/pages/page-profile.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
  ])
@endsection


@section('page-script')
  @vite([
  'resources/assets/js/form-basic-inputs.js',
  ])

  <script type="module">
    $('#formulario').submit(function() {
      e.preventDefault();
      $('.btnGuardar').attr('disabled', 'disabled');

      Swal.fire({
        title: "Espera un momento",
        text: "Ya estamos guardando...",
        icon: "info",
        showCancelButton: false,
        showConfirmButton: false,
        showDenyButton: false
      });
    });
  </script>

  <script>
    $(document).ready(function ()
    {
      let actualStep = 1;
      let maximoStep = @json($cantidadTotalSecciones);

      $(".next-step").click(function ()
      {
        let seccionId = $(this).data('seccion');

        // Obtener los datos del formulario del paso actual
        var datosFormulario = $('#step-' + actualStep).find('input, select, textarea').serializeArray();

        // Convertir los datos del formulario a un objeto
        var data = {};
        $.each(datosFormulario, function() {
          let nameInput = this.name.replace(/\[\]/g, "");
          data[nameInput] = this.value;
        });
        // Llamar al método validar del componente Livewire
        Livewire.dispatch('validar', { seccionId: seccionId, dataSeccion: data });
        Livewire.dispatch('pausarExterno');
      });

      Livewire.on('validacionFormulario', (e) => {
       // Limpiar errores anteriores
        $('.text-danger').remove();
        if (e.resultado) {
          // La validación fue exitosa, pasar al siguiente paso

          $("#step-" + actualStep).addClass('d-none');
          actualStep++;

          $(".prev-step").removeClass('d-none');
          $("#step-" + actualStep).removeClass('d-none');

          if(actualStep > maximoStep)
          {
            // Obtener los datos del formulario del paso actual
            var datosFormulario = $('#formulario').find('input, select, textarea').serializeArray();

            // Convertir los datos del formulario a un objeto
            var data = {};
            $.each(datosFormulario, function() {
                // si es un select2 obtenemos el text y no value
                if($('#formulario select[name="' + this.name + '"]').length){
                  data[this.name] = $('select[name="' + this.name + '"]').find('option:selected').text();
                }else{
                  data[this.name] = this.value;
                }

            });
            Livewire.dispatch('crearResumen', { dataSeccion: data });
          }
        } else {
          // La validación falló, mostrar los errores al usuario

          // Mostrar los errores debajo de cada campo
          $.each(e.errores, function(campo, mensajes) {
            var input = $("body input[name="+campo+"]");
            var divError = $('<div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i> ' + mensajes + '</div>');
            $('#error'+campo).html(divError);
          });
        }
      });

      $(".prev-step").click(function ()
      {
        if (actualStep > 1) {
          $("#step-" + actualStep).addClass('d-none');
          actualStep--;

          if(actualStep == 1) {
            $(".prev-step").addClass('d-none');
          }
          $("#step-" + actualStep).removeClass('d-none');
        }
      });

    });
  </script>

@endsection

@section('content')

  @livewire('TiempoConDios.validar-formulario')
  <div class="col-12 min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center">
      <div class="col-3 text-start">
        <button type="button" class="btn rounded-pill waves-effect waves-light text-white prev-step d-none">
          <span class="ti-xs ti ti-arrow-left me-2"></span>
          <span class="d-none d-md-block fw-normal">Volver</span>
        </button>
      </div>
      <div class="col-6 pl-5 text-center">
        <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Mi tiempo con Dios</h5>
      </div>
      <div class="col-3 text-end">
        <a href="{{ route('dashboard')}}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
          <span class="d-none d-md-block fw-normal">Salir</span>
          <span class="ti-xs ti ti-x mx-2"></span>
        </a>
      </div>
    </nav>

    <div class="pt-5 px-7 px-sm-0" style="padding-bottom: 100px;">
      <div class="col-12 col-sm-8 offset-sm-2 col-lg-8  offset-lg-2">
        <form id="formulario" role="form" class="forms-sample" method="POST" action="{{ route('tiempoConDios.crear') }}" enctype="multipart/form-data">
          @csrf
          @php
            $contador=1;
          @endphp
          @foreach ($secciones as $seccion)
              <!-- Secciones -->
              <div class="step row {{$contador == 1 ? '' : 'd-none'}}" id="step-{{$contador}}" >
                <div class="p-2 col-12">
                  <div class="d-flex align-items-start p-2 mt-1">
                    <div class="badge rounded rounded-circle bg-label-primary p-3 me-1 rounded">
                      <i class="{{ $seccion->icono }} ti-md"></i>
                    </div>
                    <div class="my-auto ms-1 ">
                      <small class="text-muted">Paso {{$contador}} de {{ $cantidadTotalSecciones }} </small>
                      <h6 class="mb-0">{{ $seccion->titulo_step }}</h6>
                    </div>
                  </div>
                  <div class="progress mx-2">
                    <div id="progress-bar" class="progress-bar" role="progressbar" style="width: {{($contador / $cantidadTotalSecciones) * 100}}%;" aria-valuenow="{{($contador / 2) * 100}}" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                </div>

                <div class="row mt-10 m-0 p-0">
                  <h4 class="fw-semibold text-black mb-0">{{$seccion->titulo}}</h4>
                  <p class="text-black my-3 fs-6">{{$seccion->subtitulo}}</p>


                  @foreach ($seccion->campos()->orderBy('orden','asc')->get() as $campo)

                    @if($campo->tipo->id == 1)
                    <div class="{{ $campo->class }}">

                      @if($campo->titulo)
                      <label class="form-label" for="{{$campo->name_id}}">
                        {{ $campo->titulo }}
                      </label>
                      @else
                        {!! $campo->html !!}
                      @endif

                      <textarea id="{{$campo->name_id}}" placeholder="{{ $campo->placeholder }}" name="{{$campo->name_id}}" class="form-control"></textarea>

                      @if($campo->informacion_de_apoyo)
                      <div class="ti-12px mt-2"> <i class="text-info ti ti-info-circle me-1"></i>{{ $campo->informacion_de_apoyo }}</div>
                      @endif
                      <div id="error{{$campo->name_id}}"></div>

                    </div>
                    @elseif($campo->tipo->id == 2)
                    <div class="{{ $campo->class }}">
                      {!! $campo->html !!}
                    </div>
                    @elseif($campo->tipo->id == 3)
                    <div class="{{ $campo->class }}">
                      <img class="img-responsive" src="{{ $configuracion->version == 1  ? Storage::url($configuracion->ruta_almacenamiento.'/img/mi-tiempo-con-dios/'.$campo->url_imagen) : Storage::url($configuracion->ruta_almacenamiento.'/img/mi-tiempo-con-dios/'.$campo->url_imagen)}}" alt="{{ $campo->nombre }}" />
                    </div>
                    @elseif($campo->tipo->id == 4)
                      @livewire('TiempoConDios.reproductor', [
                        'class' => $campo->class
                      ])
                    @elseif($campo->tipo->id == 5)
                      @livewire('TiempoConDios.biblia', [
                        'class' => $campo->class,
                        'name_id' => $campo->name_id
                      ])

                    @endif

                  @endforeach
                </div>

               <div class="w-100 fixed-bottom py-5 px-6 px-sm-0 border-top" style="background-color: #f8f7fa">
                  <div class="col-12 col-sm-8 offset-sm-2 col-lg-6 offset-lg-3 d-grid gap-2 d-sm-flex  {{ $contador == 1 ? 'justify-content-sm-end' : 'justify-content-sm-between' }} ">
                    <button type="button" class="btn btn-label-secondary  rounded-pill btn-outline-secondary px-7 py-2 prev-step d-none" >
                      <span class="align-middle">Volver</span>
                    </button>
                    <button type="{{$contador == $cantidadTotalSecciones ? 'submit': 'button' }}" class="btn  {{$contador == $cantidadTotalSecciones ? 'btnGuardar': '' }} btn-primary rounded-pill  {{$contador != $cantidadTotalSecciones ? 'next-step': '' }} px-7 py-2" data-seccion="{{$seccion->id}}">
                      <span class="align-middle me-sm-1 me-0 ">{{$contador == $cantidadTotalSecciones ? 'Guardar': 'Continuar' }}</span>
                    </button>
                  </div>
                </div>
              </div>
              <!-- /Secciones -->
            @php
              $contador++;
            @endphp
          @endforeach
        </form>
      </div>
    </div>
  </div>

@endsection
