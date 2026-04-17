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
      var actividadesOriginales = @json($arrayActividades);
      var calendarEl = document.getElementById('calendar');
      
      // Función para filtrar actividades basadas en los checkboxes seleccionados
      function getActividadesFiltradas() {
        var seleccionados = Array.from(document.querySelectorAll('.input-filter:checked')).map(cb => cb.getAttribute('data-value'));
        var verTodo = document.getElementById('selectAll').checked;
        
        if (verTodo) return actividadesOriginales;
        
        return actividadesOriginales.filter(function(event) {
          var eventTags = event.extendedProps.tags || [];
          
          // Caso: "Sin Tag" (valor '0')
          if (seleccionados.includes('0') && eventTags.length === 0) {
            return true;
          }
          
          // Caso: Algún tag coincide
          return eventTags.some(tag => seleccionados.includes(tag.toString()));
        });
      }

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
        events: function(info, successCallback, failureCallback) {
          successCallback(getActividadesFiltradas());
        },
        editable: true,
        selectable: true,
        locale:'es',
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

      // Escuchar cambios en los filtros
      document.querySelectorAll('.input-filter, .select-all').forEach(el => {
        el.addEventListener('change', function() {
          if (this.id === 'selectAll') {
            document.querySelectorAll('.input-filter').forEach(cb => cb.checked = this.checked);
          } else {
            // Si desmarcas uno individual, desmarcar "View All"
            if (!this.checked) document.getElementById('selectAll').checked = false;
          }
          calendar.refetchEvents();
        });
      });
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
          @foreach($tagsGenerales as $tag)
          <div class="form-check mb-2">
            <input class="form-check-input input-filter" type="checkbox" id="select-{{ $tag->id }}" data-value="{{ $tag->id }}" checked>
            <label class="form-check-label" for="select-{{ $tag->id }}">{{ $tag->nombre }}</label>
          </div>
          @endforeach
          
          <div class="form-check form-check-secondary">
            <input class="form-check-input input-filter" type="checkbox" id="select-others" data-value="0" checked>
            <label class="form-check-label" for="select-others">Sin Categoría (Otros)</label>
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

            <div class="mb-3">
              <label class="form-label" for="tags">Etiquetas (Categorías)</label>
              <select id="tags" name="tags[]" class="select2 form-select" multiple="multiple">
               @foreach($tagsGenerales as $tag)
               <option value="{{$tag->id}}"> {{$tag->nombre}}</option>
               @endforeach
              </select>
            </div>

            <div class="mb-3 row g-2">
              <div class="col-12">
                <div class="form-check form-switch mb-2">
                  <input class="form-check-input" type="checkbox" id="mostrar_en_proximas_actividades" name="mostrar_en_proximas_actividades" checked>
                  <label class="form-check-label" for="mostrar_en_proximas_actividades">¿Ver en próximas actividades?</label>
                </div>
              </div>
              <div class="col-12">
                <div class="form-check form-switch mb-2">
                  <input class="form-check-input" type="checkbox" id="totalmente_publica" name="totalmente_publica" checked>
                  <label class="form-check-label" for="totalmente_publica">¿Vista por todos?</label>
                </div>
              </div>
              <div class="col-12">
                <div class="form-check form-switch mb-2">
                  <input class="form-check-input" type="checkbox" id="habilitada_pdp" name="habilitada_pdp" value="1">
                  <label class="form-check-label" for="habilitada_pdp">¿Habilitar punto de pago?</label>
                </div>
              </div>
              <div class="col-12">
                <div class="form-check form-switch mb-2">
                  <input class="form-check-input" type="checkbox" id="restriccion_por_categoria" name="restriccion_por_categoria">
                  <label class="form-check-label" for="restriccion_por_categoria">Activar restricciones por categoría</label>
                </div>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label" for="eventDescription">Descripción</label>
              <textarea required class="form-control" name="descripcion" id="descripcion">{{ old('descripcion') }}</textarea>
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
