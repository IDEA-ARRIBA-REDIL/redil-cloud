@extends('layouts/layoutMaster')

@section('title', 'Nueva Actividad')

@section('vendor-style')
<style>

</style>
@vite([
'resources/assets/vendor/libs/fullcalendar/fullcalendar.scss',
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css'

])
@endsection


@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/app-calendar.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/fullcalendar/fullcalendar.js',

'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js',
'resources/js/app.js',
])
@endsection

@section('page-script')


<script type="module">

    document.addEventListener('DOMContentLoaded', function() {

  var actividades = @json($arrayActividades);
      var calendarEl = document.getElementById('calendar');
      var calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, interactionPlugin, listPlugin, timegridPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
          start: 'prev,next',
          center: 'title',
          end: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        buttonText: {
          today: 'Hoy',
          month: 'Mes',
          week: 'Semana',
          day: 'Día',
          list: 'Lista'
        },
        initialDate: new Date(),
        navLinks: true,
        events: actividades,
        editable: true,
        selectable: true,
        locale:'es',
        select:function(start, allDay){

        },
        dateClick: function(info) {
          var fecha_ini= (moment(info.dateStr).format('YYYY-MM-DD'));
          $('#fecha_inicio').val(fecha_ini);
          document.getElementById('new_actividad').click();

        },
        eventClick: function (info) {
          var idActividad=info.event.id;
          window.open(idActividad+'/actualizar');
      }
    });
      calendar.render();
    });
</script>

<script type="module">

  $(".fecha-picker").flatpickr({
    dateFormat: "Y-m-d",
    disableMobile:true
  });


  $(document).ready(function() {
    $('.select2').select2({
      width: '100px',
      allowClear: true,
      placeholder: 'Ninguno'
    });
    $('.select2').select2({
      dropdownParent: $('#eventForm')
    });

  });
</script>

<script type="text/javascript">
  $('#eventForm').submit(function(){
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
<div class="card app-calendar-wrapper">
  <div class="row g-0">
    <!-- Calendar Sidebar -->
    <div class="col app-calendar-sidebar border-end" id="app-calendar-sidebar">
      <div class="p-4 pt-5 my-sm-0 mb-3">
        <div class="d-grid">
          <button id='new_actividad' class="btn btn-primary rounded-pill btn-toggle-sidebar" data-bs-toggle="offcanvas" data-bs-target="#addEventSidebar" aria-controls="addEventSidebar">
            <i class="ti ti-plus me-1"></i>
            <span class="align-middle">Nueva Actividad</span>
          </button>
        </div>
      </div>
      <div class="border-top mt-3 p-3">
        <!-- inline calendar (flatpicker) -->

        <!-- Filter -->
        <div class="mb-3 ms-3">
          <small class="text-small text-muted text-uppercase align-middle">Filter</small>
        </div>

        <div class="form-check mb-2 ms-3">
          <input class="form-check-input select-all" type="checkbox" id="selectAll" data-value="all" checked>
          <label class="form-check-label" for="selectAll">View All</label>
        </div>

        <div class="app-calendar-events-filter ms-3">
          <div class="form-check form-check-danger mb-2">
            <input class="form-check-input input-filter" type="checkbox" id="select-personal" data-value="personal" checked>
            <label class="form-check-label" for="select-personal">Personal</label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input input-filter" type="checkbox" id="select-business" data-value="business" checked>
            <label class="form-check-label" for="select-business">Business</label>
          </div>
          <div class="form-check form-check-warning mb-2">
            <input class="form-check-input input-filter" type="checkbox" id="select-family" data-value="family" checked>
            <label class="form-check-label" for="select-family">Family</label>
          </div>
          <div class="form-check form-check-success mb-2">
            <input class="form-check-input input-filter" type="checkbox" id="select-holiday" data-value="holiday" checked>
            <label class="form-check-label" for="select-holiday">Holiday</label>
          </div>
          <div class="form-check form-check-info">
            <input class="form-check-input input-filter" type="checkbox" id="select-etc" data-value="etc" checked>
            <label class="form-check-label" for="select-etc">ETC</label>
          </div>
        </div>
      </div>
    </div>
    <!-- /Calendar Sidebar -->

    <!-- Calendar & Modal -->
    <div class="col app-calendar-content">
      <div class="card shadow-none border-0">
        <div class="card-body pb-0">
          <!-- FullCalendar -->

          <div id="calendar"></div>
        </div>
      </div>
      <div class="app-overlay"></div>
      <!-- FullCalendar Offcanvas -->
      <div class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="addEventSidebar" aria-labelledby="addEventSidebarLabel">
        <div class="offcanvas-header my-1">
          <h5 class="offcanvas-title" id="addEventSidebarLabel">Nueva Actividad</h5>
          <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-0">
          <form id="eventForm"  role="form" class="forms-sample" method="POST" action="{{ route('actividades.crear') }}"  enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
              <label class="form-label" for="eventTitle">Nombre</label>
              <input required type="text" value="{{ old('nombre') }}" class="form-control" id="nombre" name="nombre" placeholder="Nombre Actividad" />
            </div>
            <div class="mb-3">
              <label class="form-label" for="tipo_grupo">Tipo de actividad</label>
              <select required id="tipo_actividad" name="tipo_actividad" class="select2 form-select" data-allow-clear="true">
                <option value="0">Selecciona un tipo</option>
               @foreach($tiposActividad as $tipo)
               <option {{ in_array($tipo->id, old('tipo_actividad', [])) ? "selected" : "" }} value="{{$tipo->id}}"> {{$tipo->nombre}}</option>
               @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fecha-picke" for="eventStartDate">Fecha inicio actividad</label>
              <input required id="fecha_inicio" value="{{ old('fecha_inicio') }}" placeholder="YYYY-MM-DD" name="fecha_inicio" class="fecha form-control fecha-picker" type="text" />
            </div>
            <div class="mb-3">
              <label class="form-label fecha-picke" for="eventEndDate">Fecha fin actividad</label>
              <input required id="fecha_fin" value="{{ old('fecha_fin') }}"  placeholder="YYYY-MM-DD" name="fecha_fin" class="fecha form-control fecha-picker" type="text" />

            <div class="mb-3 mt-3">
              <label class="form-label" for="eventGuests">Habilitada para punto de pago</label>
              <select required class="select2 select-event-guests form-select" id="habilitada_pdp" name="habilitada_pdp">
                <option value="0">No</option>
                <option value="1">Si</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label" for="eventDescription">Descripción</label>
              <textarea required value="{{ old('descripcion') }}"   class="form-control" name="descripcion" id="descripcion"></textarea>
            </div>
            <div class="mb-3 d-flex justify-content-sm-between justify-content-start my-4">
              <div>
                <button type="submit" class="btnGuardar btn btn-primary rounded-pill btn-add-event me-sm-3 me-1">Guardar</button>
                <button type="reset" class="btn btn-label-secondary btn-cancel me-sm-0 me-1" data-bs-dismiss="offcanvas">Cancelar</button>
              </div>

            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- /Calendar & Modal -->
  </div>
</div>
@endsection
