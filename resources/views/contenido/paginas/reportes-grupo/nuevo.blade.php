@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Reporte de grupo')

<!-- Page -->
@section('vendor-style')
@vite([
'resources/assets/vendor/libs/fullcalendar/fullcalendar.scss',
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/select2/select2.scss',

])

<style>
  .fc-event-time, .fc-event-title {
white-space: normal;
font-size: .75em;
}
</style>
@endsection


@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/app-calendar.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/fullcalendar/fullcalendar.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/moment/moment.js',
])
@endsection

@section('page-script')

<script type="module">
  document.addEventListener('DOMContentLoaded', function() {
    var hoy = new Date();
    var calendarEl = document.getElementById('calendar');
    var eventos = @json(isset($eventos) ? $eventos : []);
    var calendar = new Calendar(calendarEl, {
      plugins: [dayGridPlugin],
      initialView: 'dayGridMonth',
      headerToolbar: {
        left: 'title',
        @if(isset($grupo)) ///sino se envio el asistente por la URL no cargamos nada en el calendario
        right: 'prev,next today',
        @else
        right: 'today',
        @endif
      },
      buttonText: { today: 'Hoy' },
      initialDate: new Date(),
      navLinks: true,
      editable: true,
      selectable: true,
      locale:'es',

      unselectAuto: false,
      events: eventos,

      @if(isset($grupo))
      dayCellDidMount: function(arg) {
        let dia = arg.date;
        if(dia > hoy){
          arg.el.style.backgroundColor = '#f0f0f0';
        }

      }

      @else
      dayCellDidMount: function(arg) {

        arg.el.style.backgroundColor = '#f0f0f0';
      }
      @endif

    });
    calendar.render();
  });
</script>

<script type="text/javascript">
  function sinComillas(e) {
    tecla = (document.all) ? e.keyCode : e.which;
    patron =/[\x5C'"]/;
    te = String.fromCharCode(tecla);
    return !patron.test(te);
  }
</script>

<script type="text/javascript">
  $('#formulario').submit(function(){
    $('.btnGuardar').attr('disabled','disabled');

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


@endsection

@section('content')
  <h4 class="mb-1">REPORTE DE GRUPO</h4>
  <p class="mb-4">Crea aquí la información principal del reporte de grupo.</p>

  @include('layouts.status-msn')


  <form id="formulario" role="form" class="forms-sample" method="POST" action="{{ route('reporteGrupo.crear') }}" enctype="multipart/form-data">
  @csrf

  <!-- botonera -->
  <div class="d-flex mb-1 mt-5">
    <div class="me-auto">
      <button type="submit" class="btn btn-primary me-1 btnGuardar">Guardar</button>
      <button type="reset" class="btn btn-label-secondary">Cancelar</button>
    </div>
    <div class="p-2 bd-highlight">
      <p class="text-muted"><span class="badge badge-dot bg-info me-1"></span> Campos obligatorios</p>
    </div>
  </div>
  <!-- /botonera -->

  <div class="row">
    <div class="col-12 col-md-5">
      <div class="card mb-4">
        <h5 class="card-header fw-bold">1. Selecciona el grupo</h5>
        <div class="card-body">
          <div class="row">

            @livewire('Grupos.grupos-para-busqueda',[
              'id' => 'grupoId',
              'class' => 'col-12 col-md-12 mb-4',
              'label' => '¿Qué grupo deseas reportar?',
              'obligatorio' => true,
              'conDadosDeBaja' => 'no',
              'estiloSeleccion' => null,
              'redirect' => 'reporteGrupo.nuevo',
              'redirectClose' => 'reporteGrupo.nuevoReporte',
              'grupoSeleccionadoId'=> $grupoId ? $grupoId : ''
            ])

          </div>
        </div>
      </div>

      <div class="card mb-4">
        <h5 class="card-header fw-bold">2. Ingresa la información basica </h5>
        <div class="card-body">
          <div class="row">
            <!-- tema -->
            <div class="mb-4 col-12">
              <label class="form-label" for="tema">
                <span class="badge badge-dot bg-info me-1"></span>
                ¿Qué mensaje o tema compartiste?
              </label>
              <input id="tema" name="tema" value="{{ old('tema') }}" {{ isset($grupo) ? '' : 'disabled' }} onkeypress="return sinComillas(event)" type="text" class="form-control" />
              @if($errors->has('tema')) <div class="text-danger form-label">{{ $errors->first('tema') }}</div> @endif
            </div>
            <!-- tema -->

            <!-- observaciones -->
            <div class="mb-2 col-12 col-md-12">
              <label class="form-label" for="observacion">
                ¿Hubo alguna observación?
              </label>
              <textarea onkeypress="return sinComillas(event)" id="observacion"  {{ isset($grupo) ? '' : 'disabled' }} name="observacion" class="form-control" rows="2" spellcheck="false" data-ms-editor="true" placeholder="">{{ old('observacion') }}</textarea>
              @if($errors->has('observacion')) <div class="text-danger form-label">{{ $errors->first('observacion') }}</div> @endif
            </div>
            <!-- /observaciones -->

          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-7">
      <div class="card mb-4">
        <h5 class="card-header fw-bold">3. Selecciona la fecha en que se realizó el grupo</h5>
        <div class="card-body">
          <div class="row">
            <!-- FullCalendar -->
            <div id="calendar" ></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- botonera -->
  <div class="d-flex mb-1 mt-5">
    <div class="me-auto">
      <button type="submit" class="btn btn-primary me-1 btnGuardar">Guardar</button>
      <button type="reset" class="btn btn-label-secondary">Cancelar</button>
    </div>
    <div class="p-2 bd-highlight">
      <p class="text-muted"><span class="badge badge-dot bg-info me-1"></span> Campos obligatorios</p>
    </div>
  </div>
  <!-- /botonera -->

  </form>

@endsection
