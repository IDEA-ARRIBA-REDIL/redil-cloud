@extends('layouts/layoutMaster')

@section('title', 'Calendario de Citas')

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

  <h4 class=" mb-1 fw-semibold text-primary">Calendario de citas</h4>

  @include('layouts.status-msn')

  <div class="row g-3">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          @if($consejero)
          <div id="calendar"></div>
          @else
          <div class="mt-5 mb-5 py-5">
            <center>
              <i class="ti ti-user-exclamation ti-xl text-black"></i>
              <p class="text-black">No tienes un perfil de consejero asociado.</p>
            </center>
          </div>
          @endif
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
              <small class="text-black fw-bold">Persona a aconsejar:</small>
              <p class="text-black" id="modalEventoPaciente"></p>
            </div>

            <div class="col-12 mb-3">
              <small class="text-black fw-bold">Tipo de consejería:</small>
              <p class="text-black" id="modalEventoTipo"></p>
            </div>

            <div class="col-12 mb-3">
              <small class="text-black fw-bold">Números de contacto:</small>
              <p class="text-black" id="modalEventoTelefonos"></p>
            </div>

            <div class="col-12 mb-3">
              <small class="text-black fw-bold">Fecha y hora de inicio: </small>
              <p class="text-black" id="modalEventoInicio"></p>
            </div>

            <div class="col-12 mb-3">
              <small class="text-black fw-bold">Fecha y hora final </small>
              <p class="text-black" id="modalEventoFin"></p>
            </div>

            <div class="col-12 mb-3">
              <small class="text-black fw-bold">Notas: </small>
              <p class="text-black" id="modalEventoNotas"></p>
            </div>
            
            <div class="col-12 mb-3 d-none" id="divConclusiones">
                <small class="text-success fw-bold">Conclusiones: </small>
                <p class="text-success" id="modalEventoConclusiones"></p>
            </div>

            <div class="col-12 mb-3 d-none" id="divMotivoCancelacion">
              <small class="text-danger fw-bold">Motivo de cancelación: </small>
              <p class="text-danger" id="modalEventoNotasCancelacion"></p>
              <small class="text-danger fw-bold">Cancelado por: </small>
              <p class="text-danger" id="modalEventoCanceladoPor"></p>
            </div>

          </div>

        </div>

        <div class="modal-footer border-top p-5">
          <button type="button" class="btn btn-outline-success rounded-pill waves-effect ms-2" id="btnConcluirCitaModal"> <i class="ti me-2 ti-calendar-check"></i> Concluir </button>
          <a href="#" class="btn btn-outline-secondary rounded-pill waves-effect ms-2" id="btnReprogramarCitaModal"> <i class="ti me-2 ti-calendar-repeat"></i> Reprogramar </a>
          <button type="button" class="btn btn-outline-danger rounded-pill waves-effect ms-2" id="btnCancelarCitaModal"> <i class="ti me-2 ti-calendar-x"></i> Cancelar cita</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Cancelar Cita -->
  <div class="modal fade" id="modalCancelarCita" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCenterTitle">Cancelar cita</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="formCancelarCita" action="" method="POST">
            @csrf
            <div class="modal-body">
                <div class="row">
                    <div class="col mb-3">
                        <p>¿Estás seguro de que deseas cancelar esta cita?</p>
                        <label for="notas_cancelacion" class="form-label">Motivo de la cancelación</label>
                        <textarea name="notas_cancelacion" id="notas_cancelacion" class="form-control" rows="3" placeholder="Escribe aquí el motivo..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary rounded-pill waves-effect ms-2">Confirmar cancelación</button>
            </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Concluir Cita -->
  <div class="modal fade" id="modalConcluirCita" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalConcluirTitle">Concluir cita</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="formConcluirCita" action="" method="POST">
            @csrf
            <div class="modal-body">
                <div class="row">
                    <div class="col mb-3">
                        <p class="text-black">Al concluir esta cita, se marcará como <b>reunión realizada</b>.</p>
                        <textarea name="conclusiones_consejero" id="conclusiones_consejero" class="form-control" rows="4" placeholder="Notas, seguimiento o conclusiones de la reunión..."></textarea>
                    </div>
                    
                    <!-- Contenedor de Tareas -->
                    <div class="col-12 mb-3 d-none" id="listaTareasContainer">
                        <label class="form-label fw-semibold fs-6 text-black">¿El usuario ha concluido alguna tarea?</label>
                        <div id="listaTareasBody" class="row">
                            <!-- Las tareas se renderizarán aquí vía JS -->
                        </div>
                    </div>

                    <div class="col-12 mb-3 d-none" id="listaPasosContainer">
                        <label class="form-label fw-semibold fs-6 text-black">¿El usuario ha concluido algún paso de crecimiento?</label>
                        <div id="listaPasosBody" class="row">
                            <!-- Los pasos se renderizarán aquí vía JS -->
                        </div>
                    </div>

                    <div class="col-12 mb-3">
                        <label for="selectTipoUsuario" class="form-label fw-semibold fs-6 text-black">¿El usuario cambio de tipo de usuario?</label>
                        <select class="form-select" id="selectTipoUsuario" name="tipo_usuario_id">
                            <!-- Opciones se llenarán vía JS -->
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success rounded-pill waves-effect ms-2">Marcar como concluida</button>
            </div>
        </form>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
<script type="module">

  document.addEventListener('DOMContentLoaded', function() {

    // Obtener estados desde el backend (Blade -> JS)
    const estadosGlobales = @json($estados);
    const estadosPasosGlobales = @json($estadosPasos);
    const tiposUsuarioGlobales = @json($tiposUsuario);

    const eventoModal = new bootstrap.Modal(document.getElementById('eventoInfoModal'));
    const cancelarCitaModal = new bootstrap.Modal(document.getElementById('modalCancelarCita'));
    const concluirCitaModal = new bootstrap.Modal(document.getElementById('modalConcluirCita')); // <-- ¡NUEVO!
    
    const btnCancelarCitaModal = document.getElementById('btnCancelarCitaModal');
    const btnConcluirCitaModal = document.getElementById('btnConcluirCitaModal'); // <-- ¡NUEVO!
    const btnReprogramar = document.getElementById('btnReprogramarCitaModal');

    const formCancelarCita = document.getElementById('formCancelarCita');
    const formConcluirCita = document.getElementById('formConcluirCita'); // <-- ¡NUEVO!
    
    // Base URLs
    const baseUrlCancelar = "{{ route('consejeria.cancelarCita', '000') }}";
    const baseUrlConcluir = "{{ route('consejeria.concluirCita', '000') }}"; // <-- ¡NUEVO!
    const baseUrlReprogramar = "{{ route('consejeria.reprogramarCita', ['cita' => '000']) }}?origen=" + encodeURIComponent(window.location.href);
    let currentEventId = null;

    // Lógica para ABRIR el modal de CANCELACIÓN
    btnCancelarCitaModal.addEventListener('click', function() {
        if(currentEventId) {
            eventoModal.hide();
            formCancelarCita.action = baseUrlCancelar.replace('000', currentEventId);
            cancelarCitaModal.show();
        }
    });

    // Lógica para ABRIR el modal de CONCLUSIÓN
    btnConcluirCitaModal.addEventListener('click', function() { // <-- ¡NUEVO!
        if(currentEventId) {
            eventoModal.hide();
            formConcluirCita.action = baseUrlConcluir.replace('000', currentEventId);
            // Opcional: Limpiar el textarea de conclusiones al abrir
            document.getElementById('conclusiones_consejero').value = ''; 
            
            // Lógica de Tareas: Renderizar si existen en el evento actual
            const listaTareasContainer = document.getElementById('listaTareasContainer');
            const listaTareasBody = document.getElementById('listaTareasBody');
            listaTareasBody.innerHTML = ''; // Limpiar previo

            // Recuperamos el evento actual desde FullCalendar (necesitamos guardarlo globalmente o buscarlo)
            // Como currentEventId es solo ID, mejor guardamos el objeto 'props' actual en una variable global al hacer click
            // O buscamos el evento en el calendario:
            const eventoActual = calendar.getEventById(currentEventId);
            
            if (eventoActual && eventoActual.extendedProps.tareas && eventoActual.extendedProps.tareas.length > 0) {
                listaTareasContainer.classList.remove('d-none');
                
                eventoActual.extendedProps.tareas.forEach(tarea => {
                    // Determinar estado inicial
                    let estadoInicial = null;
                    let textoBoton = 'Sin asignar';
                    let claseColor = 'secondary';
                    let valorInput = '';

                    if (tarea.estado_actual) {
                        estadoInicial = tarea.estado_actual;
                        textoBoton = `${estadoInicial.nombre} ${estadoInicial.fecha}`;
                        claseColor = estadoInicial.color || 'secondary';
                        valorInput = estadoInicial.id;
                    }

                    // Crear estructura HTML
                    const tareaDiv = document.createElement('div');
                    tareaDiv.className = 'd-flex flex-column border-bottom pb-2  col-6';
                    
                    // Input Hidden para enviar al servidor
                    const inputHidden = document.createElement('input');
                    inputHidden.type = 'hidden';
                    inputHidden.name = `tareas[${tarea.id}]`;
                    inputHidden.value = valorInput;
                    inputHidden.id = `input_tarea_${tarea.id}`;
                    
                    // Si el valor es vacío, deshabilitamos el input para que no se envíe
                    if (!valorInput) {
                        inputHidden.disabled = true;
                    }

                    // Título
                    const titulo = document.createElement('small');
                    titulo.className = 'text-black fw-semibold mb-1';
                    titulo.textContent = tarea.nombre;
                    
                    // Dropdown Group
                    const btnGroup = document.createElement('div');
                    btnGroup.className = 'btn-group';
                    
                    // Botón Trigger
                    const btnToggle = document.createElement('button');
                    btnToggle.type = 'button';
                    btnToggle.className = `btn btn-${claseColor} rounded-pill btn-xs dropdown-toggle waves-effect waves-light`;
                    btnToggle.dataset.bsToggle = 'dropdown';
                    btnToggle.ariaExpanded = 'false';
                    btnToggle.textContent = textoBoton;
                    
                    // Menú Dropdown
                    const dropdownMenu = document.createElement('ul');
                    dropdownMenu.className = 'dropdown-menu';
                    
                    estadosGlobales.forEach(estado => {
                        const li = document.createElement('li');
                        const a = document.createElement('a');
                        a.className = 'dropdown-item';
                        a.href = 'javascript:void(0);';
                        a.textContent = estado.nombre;
                        
                        a.addEventListener('click', function() {
                            // Actualizar Input Hidden
                            inputHidden.value = estado.id;
                            inputHidden.disabled = false; // Habilitar al seleccionar

                            const dropdownInstance = bootstrap.Dropdown.getOrCreateInstance(btnToggle);
                            dropdownInstance.hide();
                            
                            // Actualizar Botón Visual
                            // Al seleccionar uno nuevo, no tenemos fecha, así que solo mostramos el nombre
                            btnToggle.textContent = estado.nombre;
                            
                            // Remover clases de color anteriores (asumiendo formato btn-COLOR)
                            // Para hacerlo bien, reseteamos la clase base y añadimos la nueva
                            btnToggle.className = `btn btn-${estado.color || 'secondary'} rounded-pill btn-xs dropdown-toggle waves-effect waves-light`;
                        });
                        
                        li.appendChild(a);
                        dropdownMenu.appendChild(li);
                    });
                    
                    btnGroup.appendChild(btnToggle);
                    btnGroup.appendChild(dropdownMenu);
                    
                    tareaDiv.appendChild(inputHidden);
                    tareaDiv.appendChild(titulo);
                    tareaDiv.appendChild(btnGroup);
                    
                    listaTareasBody.appendChild(tareaDiv);
                });

                // --- RENDERIZAR PASOS DE CRECIMIENTO ---
                const listaPasosContainer = document.getElementById('listaPasosContainer');
                const listaPasosBody = document.getElementById('listaPasosBody');
                listaPasosBody.innerHTML = ''; // Limpiar previos

                if (eventoActual.extendedProps.pasos_crecimiento && eventoActual.extendedProps.pasos_crecimiento.length > 0) {
                    listaPasosContainer.classList.remove('d-none');
                    
                    eventoActual.extendedProps.pasos_crecimiento.forEach(paso => {
                        // Determinar estado inicial
                        let estadoInicial = null;
                        let textoBoton = 'Sin asignar';
                        let claseColor = 'secondary';
                        let valorInput = '';

                        if (paso.estado_actual) {
                            estadoInicial = paso.estado_actual;
                            textoBoton = `${estadoInicial.nombre} ${estadoInicial.fecha}`;
                            claseColor = estadoInicial.color || 'secondary';
                            valorInput = estadoInicial.id;
                        }

                        // Crear estructura HTML (similar a tareas)
                        const pasoDiv = document.createElement('div');
                        pasoDiv.className = 'd-flex flex-column border-bottom pb-2 col-6';
                        
                        // Input Hidden
                        const inputHidden = document.createElement('input');
                        inputHidden.type = 'hidden';
                        inputHidden.name = `pasos[${paso.id}]`;
                        inputHidden.value = valorInput;
                        inputHidden.id = `input_paso_${paso.id}`;
                        
                        if (!valorInput) {
                            inputHidden.disabled = true;
                        }

                        // Título
                        const titulo = document.createElement('small');
                        titulo.className = 'text-black fw-semibold mb-1';
                        titulo.textContent = paso.nombre;
                        
                        // Dropdown Group
                        const btnGroup = document.createElement('div');
                        btnGroup.className = 'btn-group';
                        
                        // Botón Trigger
                        const btnToggle = document.createElement('button');
                        btnToggle.type = 'button';
                        btnToggle.className = `btn btn-${claseColor} rounded-pill btn-xs dropdown-toggle waves-effect waves-light`;
                        btnToggle.dataset.bsToggle = 'dropdown';
                        btnToggle.ariaExpanded = 'false';
                        btnToggle.textContent = textoBoton;
                        
                        // Menú Dropdown
                        const dropdownMenu = document.createElement('ul');
                        dropdownMenu.className = 'dropdown-menu';
                        
                        estadosPasosGlobales.forEach(estado => {
                            const li = document.createElement('li');
                            const a = document.createElement('a');
                            a.className = 'dropdown-item';
                            a.href = 'javascript:void(0);';
                            a.textContent = estado.nombre;
                            
                            a.addEventListener('click', function() {
                                // Actualizar Input Hidden
                                inputHidden.value = estado.id;
                                inputHidden.disabled = false;
                                
                                const dropdownInstance = bootstrap.Dropdown.getOrCreateInstance(btnToggle);
                                dropdownInstance.hide();

                                // Actualizar Botón Visual
                                btnToggle.textContent = estado.nombre;
                                btnToggle.className = `btn btn-${estado.color || 'secondary'} rounded-pill btn-xs dropdown-toggle waves-effect waves-light`;
                            });
                            
                            li.appendChild(a);
                            dropdownMenu.appendChild(li);
                        });
                        
                        btnGroup.appendChild(btnToggle);
                        btnGroup.appendChild(dropdownMenu);
                        
                        pasoDiv.appendChild(inputHidden);
                        pasoDiv.appendChild(titulo);
                        pasoDiv.appendChild(btnGroup);
                        
                        listaPasosBody.appendChild(pasoDiv);
                    });
                } else {
                    listaPasosContainer.classList.add('d-none');
                }

                // --- RENDERIZAR TIPO DE USUARIO ---
                const selectTipoUsuario = document.getElementById('selectTipoUsuario');
                selectTipoUsuario.innerHTML = ''; // Limpiar opciones

                tiposUsuarioGlobales.forEach(tipo => {
                    const option = document.createElement('option');
                    option.value = tipo.id;
                    option.textContent = tipo.nombre;
                    selectTipoUsuario.appendChild(option);
                });

                // Seleccionar el valor actual
                if (eventoActual.extendedProps.tipo_usuario_id) {
                    selectTipoUsuario.value = eventoActual.extendedProps.tipo_usuario_id;
                }

            } else {
                listaTareasContainer.classList.add('d-none');
            }

            concluirCitaModal.show();
        }
    });

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
      eventSources: [
        {
          url: '{{ route('consejeria.obtenerCitasCalendario') }}',
          failure: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar las citas del calendario.',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-pill'
                },
                buttonsStyling: false
            });
          }
        }
      ],
      editable: false, // Citas no son editables arrastrando por ahora
      selectable: true,
      locale:'es',
      eventDisplay: 'block',
      eventClick: function(info) {

        info.jsEvent.preventDefault();
        const evento = info.event;
        currentEventId = evento.id; 
        const props = evento.extendedProps;

        // Llenar el modal
        document.getElementById('modalEventoTitulo').textContent = "Detalles de la cita";
        document.getElementById('modalEventoPaciente').textContent = props.paciente;
        document.getElementById('modalEventoTipo').textContent = props.tipo_consejeria;
        document.getElementById('modalEventoNotas').textContent = props.notas;
        document.getElementById('modalEventoTelefonos').textContent = props.telefonos;
        
        // Handle Cancelled Status
        const divMotivo = document.getElementById('divMotivoCancelacion');
        const divConclusiones = document.getElementById('divConclusiones');
        const btnCancelar = document.getElementById('btnCancelarCitaModal');
        const btnConcluir = document.getElementById('btnConcluirCitaModal');
        
        // Update Reprogramar Link
        btnReprogramar.href = baseUrlReprogramar.replace('000', currentEventId);

        // 1. Cita Cancelada
        if (props.is_cancelled) {
            divMotivo.classList.remove('d-none');
            document.getElementById('modalEventoNotasCancelacion').textContent = props.notas_cancelacion || 'Sin motivo especificado';
            document.getElementById('modalEventoCanceladoPor').textContent = props.cancelado_por || 'No especificado especificado';
            
            divConclusiones.classList.add('d-none'); // Es cancelada, no puede ser concluida
            btnCancelar.classList.add('d-none'); 
            btnConcluir.classList.add('d-none'); 
            btnReprogramar.classList.add('d-none'); 
        } 
        // 2. Cita Concluida
        else if (props.is_concluida) { // <-- ¡NUEVO!

            divMotivo.classList.add('d-none');
            divConclusiones.classList.remove('d-none');
            document.getElementById('modalEventoConclusiones').textContent = props.conclusiones_consejero || 'No se escribieron conclusiones.';
            
            btnCancelar.classList.add('d-none');
            btnConcluir.classList.add('d-none'); // Ya concluida, no se puede volver a concluir
            btnReprogramar.classList.add('d-none'); // Tampoco se puede reprogramar
        } 
        // 3. Cita Activa
        else { 
            divMotivo.classList.add('d-none');
            divConclusiones.classList.add('d-none');

            btnCancelar.classList.remove('d-none'); // Mostrar Cancelar
            btnConcluir.classList.remove('d-none'); // Mostrar Concluir
            btnReprogramar.classList.remove('d-none'); // Mostrar Reprogramar
        }
        
        document.getElementById('modalEventoInicio').textContent = moment(evento.start).format('DD MMM YYYY, h:mm A');
        document.getElementById('modalEventoFin').textContent = evento.end ? moment(evento.end).format('DD MMM YYYY, h:mm A') : 'N/A';

        eventoModal.show();
      }
    });

    calendar.render();

  });
</script>
@endsection
