@extends('layouts/layoutMaster')

@section('title', 'Gestionar agenda')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/fullcalendar/fullcalendar.scss',
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/fullcalendar/fullcalendar.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/moment/moment.js',
])
@endsection




@section('content')

  <h4 class=" mb-1 fw-semibold text-primary">Calendario</h4>

  <!-- Navbar pills -->
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-10 p-1 border-1">
        <ul class="nav nav-pills justify-content-start flex-column flex-md-row  gap-2">
          <li class="nav-item flex-fill"><a id="tap-configurar-horarios" href="{{ route('consejeria.configurarHorariosCosejero', $consejero) }}" class="nav-link p-3 waves-effect waves-light" data-tap="principal"><i class='ti-xs ti ti-calendar-week me-2'></i> Configuración horario habitual</a></li>
          <li class="nav-item flex-fill"><a id="tap-calendario-fechas" href="{{ route('consejeria.calendarioDeFechasConsejero', $consejero) }}" class="nav-link p-3 waves-effect waves-light active"  data-tap="familia"><i class='ti-xs ti ti-calendar-event me-2'></i> Calendario de fechas</a></li>
        </ul>
      </div>
    </div>
  </div>
  <!--/ Navbar pills -->

  <p class="mb-4 text-black">Observa aqui los horarios extendidos y los bloqueados configurados.</p>

  <div class="d-flex flex-wrap align-items-center gap-2 mb-5">
    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2 d-flex align-items-center gap-2" data-bs-toggle="offcanvas" data-bs-target="#offcanvasHorarioExtendido">
      <i class="ti ti-plus"></i>
      <span>Añadir horario extendido</span>
    </button>

    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2 d-flex align-items-center gap-2" data-bs-toggle="offcanvas" data-bs-target="#offcanvasHorarioBloqueados">
      <i class="ti ti-lock"></i>
      <span>Bloquear espacio</span>
    </button>
  </div>

  <div class="row g-3">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div id="calendar"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="eventoInfoModal" tabindex="-1" aria-labelledby="eventoInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered modal-simple">
      <div class="modal-content p-0">
        <div class="modal-header border-bottom d-flex justify-content-between px-5 pb-3">
          <p class="text-black fw-semibold mb-0" id="modalEventoTitulo"></p>
          <button type="button" class="btn btn-sm" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-x ti-sm"></i></button>
        </div>

        <div class="modal-body px-5 py-8">

          <div class="row">

            <div class="col-12 mb-3">
              <small class="text-black fw-bold">Descripción:</small>
              <p id="modalEventoDescripcion"></p>
            </div>

            <div class="col-12 mb-3">
              <small class="text-black fw-bold">Fecha y hora de inicio: </small>
              <p id="modalEventoInicio"></p>
            </div>

            <div class="col-12 mb-3">
              <small class="text-black fw-bold">Fecha y hora final </small>
              <p id="modalEventoFin"></p>
            </div>

          </div>

        </div>

        <div class="modal-footer border-top p-5">

          <button type="button" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-primary waves-effect waves-light" id="btnEditarEvento"><i class="ti-xs ti ti-edit me-2"></i>  Editar</button>

          {{-- Añadimos un formulario para el botón de eliminar --}}
          <form id="formEliminarEvento" method="POST" class="mb-0">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect waves-light" id="btnEliminarEvento">
              <i class="ti-xs ti ti-trash me-2"></i>
              Eliminar
            </button>
          </form>

        </div>
      </div>
    </div>
  </div>


  <form id="formHorarioExtendido" class="" method="POST" action="{{ route('consejeria.addHorarioExtendido', $consejero) }}">
    @csrf
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasHorarioExtendido" aria-labelledby="offcanvasHorarioExtendidoLabel">
      <div class="offcanvas-header my-1 px-8">
          <h4 id="offcanvasHorarioExtendidoLabel" class="offcanvas-title text-primary fw-semibold">Añadir horario extendido</h4>
          <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>

      <div class="offcanvas-body pt-6 px-8">
          <div class="mb-4">
                <span class="text-black ti-14px mb-4">Define un rango de fecha y hora en el que estarás disponible, fuera de tu horario habitual. Estos horarios se visualizarán en el calendario de fechas </span>
              </div>
          <div class="row">

            <div class="col-12 mb-3">
                <label for="add_fecha_inicio" class="form-label">Inicio</label>
                <input type="text" class="form-control datetime-picker" id="add_fecha_inicio" name="fecha_inicio" placeholder="YYYY-MM-DD HH:MM" required>
            </div>

            <div class="col-12 mb-3">
                <label for="add_fecha_fin" class="form-label">Fin</label>
                <input type="text" class="form-control datetime-picker" id="add_fecha_fin" name="fecha_fin" placeholder="YYYY-MM-DD HH:MM" required>
            </div>

             <div class="col-12 mb-3">
                <label for="add_motivo" class="form-label">Motivo</label>
                <input type="text" class="form-control" id="add_motivo" name="motivo" placeholder="Ej: Jornada especial">
            </div>

          </div>

      </div>

      {{-- Footer con los botones de acción --}}
      <div class="offcanvas-footer border-top p-3">
          <button type="submit" class="btn btn-primary waves-effect rounded-pill me-2">Guardar</button>
          <button type="button" class="btn btn-outline-secondary rounded-pill waves-effect " data-bs-dismiss="offcanvas">Cancelar</button>
      </div>
    </div>
  </form>

  <form id="formHorarioBloqueados" class="" method="POST" action="{{ route('consejeria.addHorarioBloqueado', $consejero) }}">
    @csrf
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasHorarioBloqueados" aria-labelledby="offcanvasHorarioBloqueadosLabel">
      <div class="offcanvas-header my-1 px-8">
          <h4 id="offcanvasHorarioBloqueadosLabel" class="offcanvas-title text-primary fw-semibold">Bloquear horario</h4>
          <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>

      <div class="offcanvas-body pt-6 px-8">
          <div class="mb-4">
                <span class="text-black ti-14px mb-4">Define un rango de fecha y hora en donde no vas a estar disponible. Estos horarios se visualizarán en el calendario de fechas </span>
              </div>
          <div class="row">
            <div class="col-12 mb-3">
                <label for="block_fecha_inicio" class="form-label">Inicio del bloqueo</label>
                <input type="text" class="form-control datetime-picker" id="block_fecha_inicio" name="fecha_inicio" placeholder="YYYY-MM-DD HH:MM" required>
            </div>

            <div class="col-12 mb-3">
                <label for="block_fecha_fin" class="form-label">Fin del bloqueo</label>
                <input type="text" class="form-control datetime-picker" id="block_fecha_fin" name="fecha_fin" placeholder="YYYY-MM-DD HH:MM" required>
            </div>

            <div class="col-12 mb-3">
                <label for="block_motivo" class="form-label">Motivo</label>
                <input type="text" class="form-control" id="block_motivo" name="motivo" placeholder="Ej: Cita médica">
            </div>

          </div>

      </div>

      {{-- Footer con los botones de acción --}}
      <div class="offcanvas-footer border-top p-3">
          <button type="submit" class="btn btn-primary waves-effect rounded-pill me-2">Guardar</button>
          <button type="button" class="btn btn-outline-secondary rounded-pill waves-effect " data-bs-dismiss="offcanvas">Cancelar</button>
      </div>
    </div>
  </form>

  <form id="formEditarHorarioExtendido" class="" method="POST" action=""> {{-- El 'action' se pondrá con JS --}}
    @csrf
    @method('PATCH')
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditarHorarioExtendido" aria-labelledby="offcanvasEditarHorarioExtendidoLabel">
      <div class="offcanvas-header my-1 px-8">
          <h4 id="offcanvasEditarHorarioExtendidoLabel" class="offcanvas-title text-primary fw-semibold">Editar horario extendido</h4>
          <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body pt-6 px-8">
          <div class="row">
            <div class="col-12 mb-3">
                <label for="edit_add_fecha_inicio" class="form-label">Inicio</label>
                <input type="text" class="form-control datetime-picker" id="edit_add_fecha_inicio" name="fecha_inicio" required>
            </div>
            <div class="col-12 mb-3">
                <label for="edit_add_fecha_fin" class="form-label">Fin</label>
                <input type="text" class="form-control datetime-picker" id="edit_add_fecha_fin" name="fecha_fin" required>
            </div>
            <div class="col-12 mb-3">
                <label for="edit_add_motivo" class="form-label">Motivo</label>
                <input type="text" class="form-control" id="edit_add_motivo" name="motivo">
            </div>
          </div>
      </div>
      <div class="offcanvas-footer border-top p-3">
          <button type="submit" class="btn btn-primary waves-effect rounded-pill me-2">Actualizar</button>
          <button type="button" class="btn btn-outline-secondary rounded-pill waves-effect " data-bs-dismiss="offcanvas">Cancelar</button>
      </div>
    </div>
  </form>

  <form id="formEditarHorarioBloqueado" class="" method="POST" action=""> {{-- El 'action' se pondrá con JS --}}
    @csrf
    @method('PATCH')
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditarHorarioBloqueado" aria-labelledby="offcanvasEditarHorarioBloqueadoLabel">
      <div class="offcanvas-header my-1 px-8">
          <h4 id="offcanvasEditarHorarioBloqueadoLabel" class="offcanvas-title text-primary fw-semibold">Editar horario bloqueado</h4>
          <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body pt-6 px-8">
          <div class="row">
            <div class="col-12 mb-3">
                <label for="edit_block_fecha_inicio" class="form-label">Inicio del bloqueo</label>
                <input type="text" class="form-control datetime-picker" id="edit_block_fecha_inicio" name="fecha_inicio" required>
            </div>
            <div class="col-12 mb-3">
                <label for="edit_block_fecha_fin" class="form-label">Fin del bloqueo</label>
                <input type="text" class="form-control datetime-picker" id="edit_block_fecha_fin" name="fecha_fin" required>
            </div>
            <div class="col-12 mb-3">
                <label for="edit_block_motivo" class="form-label">Motivo</label>
                <input type="text" class="form-control" id="edit_block_motivo" name="motivo">
            </div>
          </div>
      </div>
      <div class="offcanvas-footer border-top p-3">
          <button type="submit" class="btn btn-primary waves-effect rounded-pill me-2">Actualizar</button>
          <button type="button" class="btn btn-outline-secondary rounded-pill waves-effect " data-bs-dismiss="offcanvas">Cancelar</button>
      </div>
    </div>
  </form>

@endsection

@section('page-script')
<script type="module">

  document.addEventListener('DOMContentLoaded', function() {

    // --- (NUEVO) INICIALIZACIÓN DE PICKERS DE FECHA Y HORA ---
    flatpickr('.datetime-picker', {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        time_24hr: true, // Cambia a true si prefieres 24h
        minuteIncrement: 15, // Opcional: saltos de 15 min
        locale: "es", // Opcional: si tienes el script de localización de flatpickr
    });

    let eventoParaEditar = null;

     // CALENDARIO
    const eventoModal = new bootstrap.Modal(document.getElementById('eventoInfoModal'));
    var calendarEl = document.getElementById('calendar');
    var calendar = new Calendar(calendarEl, {
      plugins: [dayGridPlugin, interactionPlugin, listPlugin, timegridPlugin],
      initialView: 'dayGridMonth',
      headerToolbar: {
        start: 'prev,next',
        center: 'title',
        end: 'dayGridMonth,timeGridWeek,listMonth'
      },
      buttonText: {
        month: 'Mes',
        week: 'Semana',
        list: 'Lista'
      },
      initialDate: new Date(),
      navLinks: true,
      // --- ¡CAMBIO IMPORTANTE! ---
      // Usamos eventSources para cargar múltiples fuentes
      eventSources: [

        // FUENTE 1: Tus eventos (Adicional/Bloqueado) desde la URL
        {
          url: '{{ route('consejeria.obtenerHorariosCalendario', $consejero) }}',
          failure: function() {
            alert('Error al cargar los eventos del calendario.');
          }
        },

        // FUENTE 2: Los días NO habituales (fondo gris)
        {
          events: [
            {
              // Obtenemos el array de días [0, 6] (Dom, Sab) del controlador
              daysOfWeek: @json($diasNoHabitualesFC),
              display: 'background',
              color: '#9b9b9bff', // Color gris claro (similar al de tu tema)
              allDay: true
            }
          ]
        }
      ],
      // --- FIN DEL CAMBIO ---
      editable: true,
      selectable: true,
      locale:'es',
      select:function(start, allDay){

      },
      eventDisplay: 'block',
      eventClick: function(info) {
        info.jsEvent.preventDefault();
        const evento = info.event;

        // Guardamos el evento para la edición
        eventoParaEditar = evento;

        // Llenar el modal (como ya lo tienes)
        document.getElementById('modalEventoTitulo').textContent = "Horario "+evento.extendedProps.tipo_evento;
        document.getElementById('modalEventoDescripcion').textContent = evento.title;
        document.getElementById('modalEventoInicio').textContent = moment(evento.start).format('DD MMM YYYY, h:mm A');
        document.getElementById('modalEventoFin').textContent = evento.end ? moment(evento.end).format('DD MMM YYYY, h:mm A') : 'N/A';

        // Configurar el formulario de eliminación (como ya lo tienes)
        const formEliminar = document.getElementById('formEliminarEvento');
        let deleteUrl = '';
        if (evento.extendedProps.tipo_evento === 'adicional') {
          deleteUrl = '{{ route('consejeria.eliminarHorarioAdicional', ['id' => ':id']) }}'.replace(':id', evento.id);
        } else {
          deleteUrl = '{{ route('consejeria.eliminarHorarioBloqueado', ['id' => ':id']) }}'.replace(':id', evento.id);
        }
        formEliminar.action = deleteUrl;

        eventoModal.show();
      }
    });

    calendar.render();

    // ---  LÓGICA DEL BOTÓN EDITAR ---
    // (Obtenemos las instancias de los nuevos offcanvas)
    const offcanvasEditarHorarioExtendidoElement = document.getElementById('offcanvasEditarHorarioExtendido');
    const offcanvasEditarHorarioExtendido = new bootstrap.Offcanvas(offcanvasEditarHorarioExtendidoElement);
    const formEditarHorarioExtendido = document.getElementById('formEditarHorarioExtendido');

    const offcanvasEditarHorarioBloqueadoElement = document.getElementById('offcanvasEditarHorarioBloqueado');
    const offcanvasEditarHorarioBloqueado = new bootstrap.Offcanvas(offcanvasEditarHorarioBloqueadoElement);
    const formEditarHorarioBloqueado = document.getElementById('formEditarHorarioBloqueado');

    document.getElementById('btnEditarEvento').addEventListener('click', function() {
        if (!eventoParaEditar) return;
        eventoModal.hide(); // Ocultamos el modal

        const tipo = eventoParaEditar.extendedProps.tipo_evento;
        const inicioStr = moment(eventoParaEditar.start).format('YYYY-MM-DD HH:mm');
        const finStr = moment(eventoParaEditar.end).format('YYYY-MM-DD HH:mm');

        if (tipo === 'adicional') {
            // Llenar el formulario de EDICIÓN Adicional
            formEditarHorarioExtendido.querySelector('#edit_add_fecha_inicio').value = inicioStr;
            formEditarHorarioExtendido.querySelector('#edit_add_fecha_fin').value = finStr;
            formEditarHorarioExtendido.querySelector('#edit_add_motivo').value = eventoParaEditar.title;
            // Asignar la URL de actualización al 'action'
            formEditarHorarioExtendido.action = '{{ route('consejeria.actualizarHorarioAdicional', ['id' => ':id']) }}'.replace(':id', eventoParaEditar.id);
            // Mostrar offcanvas de EDICIÓN
            offcanvasEditarHorarioExtendido.show();

        } else if (tipo === 'bloqueado') {
            // Llenar el formulario de EDICIÓN Bloqueado
            formEditarHorarioBloqueado.querySelector('#edit_block_fecha_inicio').value = inicioStr;
            formEditarHorarioBloqueado.querySelector('#edit_block_fecha_fin').value = finStr;
            formEditarHorarioBloqueado.querySelector('#edit_block_motivo').value = eventoParaEditar.title;
            // Asignar la URL de actualización al 'action'
            formEditarHorarioBloqueado.action = '{{ route('consejeria.actualizarHorarioBloqueado', ['id' => ':id']) }}'.replace(':id', eventoParaEditar.id);
            // Mostrar offcanvas de EDICIÓN
            offcanvasEditarHorarioBloqueado.show();
        }
    });

      // --- (NUEVO) 'submit' Formulario EDITAR Extendido ---
    formEditarHorarioExtendido.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(formEditarHorarioExtendido);

        fetch(formEditarHorarioExtendido.action, {
            method: 'POST', // Usamos POST para simular PATCH
            body: formData, // El @method('PATCH') en el HTML se encarga
            headers: { 'X-CSRF-TOKEN': formData.get('_token'), 'Accept': 'application/json' }
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                offcanvasEditarHorarioExtendido.hide();
                Swal.fire({ icon: 'success', title: '¡Actualizado!', text: body.message, /* ... */ });
                calendar.refetchEvents();
            } else if (status === 422) { /* ... (manejo de error 422) ... */ }
        })
        .catch(error => { /* ... (manejo de error) ... */ });
    });

    // --- (NUEVO) 'submit' Formulario EDITAR Bloqueado ---
    formEditarHorarioBloqueado.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(formEditarHorarioBloqueado);

        fetch(formEditarHorarioBloqueado.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': formData.get('_token'), 'Accept': 'application/json' }
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                offcanvasEditarHorarioBloqueado.hide();
                Swal.fire({ icon: 'success', title: '¡Actualizado!', text: body.message, /* ... */ });
                calendar.refetchEvents();
            } else if (status === 422) {
                // --- ERROR DE VALIDACIÓN ---
                // 'body.errors' contendrá los mensajes del Form Request

                // Creamos una lista de errores
                let errorMessages = Object.values(body.errors).map(errors => errors.join('<br>')).join('<br>');

                Swal.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    html: errorMessages, // Usamos html para los saltos de línea
                    customClass: {
                        confirmButton: 'btn btn-primary rounded-pill'
                    },
                    buttonsStyling: false
                });
            } else {
                 // --- OTRO TIPO DE ERROR (Ej: 500) ---
                throw new Error(body.message || 'Ocurrió un error en el servidor.');
            }
        })
        .catch(error => {
            // --- ERROR DE RED O DEL SERVIDOR ---
            console.error('Error en fetch:', error);
            Swal.fire({
                icon: 'error',
                title: '¡Oops...!',
                text: error.message || 'No se pudo conectar con el servidor. Inténtalo de nuevo.',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-pill'
                },
                buttonsStyling: false
            });
        });
    });



    // ---  MANEJO DE FORMULARIO HORARIO EXTENDIDO ---
    const formHorarioExtendido = document.getElementById('formHorarioExtendido');
    const offcanvasHorarioExtendidoElement = document.getElementById('offcanvasHorarioExtendido');
    // Obtenemos la instancia de Bootstrap del Offcanvas
    const offcanvasHorarioExtendido = new bootstrap.Offcanvas(offcanvasHorarioExtendidoElement);

    formHorarioExtendido.addEventListener('submit', function(e) {
        e.preventDefault(); // ¡Muy importante! Evita que la página se recargue

        const formData = new FormData(formHorarioExtendido);
        const url = formHorarioExtendido.action;

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': formData.get('_token'), // Laravel necesita esto
                'Accept': 'application/json' // Pedimos una respuesta JSON
            }
        })
        .then(response => {
            // Convertimos la respuesta a JSON
            return response.json().then(data => ({ status: response.status, body: data }));
        })
        .then(({ status, body }) => {

            if (status === 200 && body.success) {
                // --- ¡ÉXITO! ---

                // 1. Cerramos el Offcanvas
                offcanvasHorarioExtendido.hide();

                // 2. Mostramos una alerta de éxito
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado!',
                    text: body.message,
                    customClass: {
                        confirmButton: 'btn btn-primary rounded-pill'
                    },
                    buttonsStyling: false
                });

                // 3. Limpiamos el formulario para la próxima vez
                formHorarioExtendido.reset();

                // Opcional: podrías querer recargar el calendario aquí si tienes uno
                 calendar.refetchEvents();

            } else if (status === 422) {
                // --- ERROR DE VALIDACIÓN ---
                // 'body.errors' contendrá los mensajes del Form Request

                // Creamos una lista de errores
                let errorMessages = Object.values(body.errors).map(errors => errors.join('<br>')).join('<br>');

                Swal.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    html: errorMessages, // Usamos html para los saltos de línea
                    customClass: {
                        confirmButton: 'btn btn-primary rounded-pill'
                    },
                    buttonsStyling: false
                });
            } else {
                 // --- OTRO TIPO DE ERROR (Ej: 500) ---
                throw new Error(body.message || 'Ocurrió un error en el servidor.');
            }
        })
        .catch(error => {
            // --- ERROR DE RED O DEL SERVIDOR ---
            console.error('Error en fetch:', error);
            Swal.fire({
                icon: 'error',
                title: '¡Oops...!',
                text: error.message || 'No se pudo conectar con el servidor. Inténtalo de nuevo.',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-pill'
                },
                buttonsStyling: false
            });
        });
    });

    // --- MANEJO DE FORMULARIO HORARIO BLOQUEADO ---
    const formHorarioBloqueado = document.getElementById('formHorarioBloqueados');
    const offcanvasHorarioBloqueadoElement = document.getElementById('offcanvasHorarioBloqueados');
    const offcanvasHorarioBloqueado = new bootstrap.Offcanvas(offcanvasHorarioBloqueadoElement);

    formHorarioBloqueado.addEventListener('submit', function(e) {
        e.preventDefault(); // ¡Evita la recarga de la página!

        const formData = new FormData(formHorarioBloqueado);
        const url = formHorarioBloqueado.action;

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': formData.get('_token'),
                'Accept': 'application/json'
            }
        })
        .then(response => {
            return response.json().then(data => ({ status: response.status, body: data }));
        })
        .then(({ status, body }) => {

            if (status === 200 && body.success) {
                // --- ¡ÉXITO! ---
                offcanvasHorarioBloqueado.hide();
                Swal.fire({
                    icon: 'success',
                    title: '¡Horario bloqueado!',
                    text: body.message,
                    customClass: {
                        confirmButton: 'btn btn-primary rounded-pill'
                    },
                    buttonsStyling: false
                });
                formHorarioBloqueado.reset();
                calendar.refetchEvents();
            } else if (status === 422) {
                // --- ERROR DE VALIDACIÓN ---
                let errorMessages = Object.values(body.errors).map(errors => errors.join('<br>')).join('<br>');
                Swal.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    html: errorMessages,
                    customClass: {
                        confirmButton: 'btn btn-primary rounded-pill'
                    },
                    buttonsStyling: false
                });
            } else {
                 // --- OTRO TIPO DE ERROR (Ej: 500) ---
                throw new Error(body.message || 'Ocurrió un error en el servidor.');
            }
        })
        .catch(error => {
            // --- ERROR DE RED O DEL SERVIDOR ---
            console.error('Error en fetch:', error);
            Swal.fire({
                icon: 'error',
                title: '¡Oops...!',
                text: error.message || 'No se pudo conectar con el servidor.',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-pill'
                },
                buttonsStyling: false
            });
        });
    });


    // --- ELIMINAR  ---
    const formEliminar = document.getElementById('formEliminarEvento');
    formEliminar.addEventListener('submit', function(e) {
        e.preventDefault(); // ¡Prevenimos el envío normal!

        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esto!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-outline-secondary' },
            buttonsStyling: false
        }).then(function(result) {
            if (result.isConfirmed) {

                const url = formEliminar.action;
                const token = formEliminar.querySelector('input[name="_token"]').value;

                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        eventoModal.hide(); // Ocultamos el modal
                        calendar.refetchEvents(); // ¡Refrescamos el calendario!
                        Swal.fire('¡Eliminado!', data.message, 'success');
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
                });
            }
        });
    });

  });
</script>
@endsection
