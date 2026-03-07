@extends('layouts/layoutMaster')

@section('title', 'Gestionar agenda')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/fullcalendar/fullcalendar.scss',
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/fullcalendar/fullcalendar.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js'
])
@endsection




@section('content')

  <h4 class=" mb-3 fw-semibold text-primary">Horarios habituales</h4>


  <!-- Navbar pills -->
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-10 p-1 border-1">
        <ul class="nav nav-pills justify-content-start flex-column flex-md-row  gap-2">
          <li class="nav-item flex-fill"><a id="tap-configurar-horarios" href="{{ route('consejeria.configurarHorariosCosejero', $consejero) }}" class="nav-link p-3 waves-effect waves-light active" data-tap="principal"><i class='ti-xs ti ti-calendar-week me-2'></i> Configuración horario habitual</a></li>
          <li class="nav-item flex-fill"><a id="tap-calendario-fechas" href="{{ route('consejeria.calendarioDeFechasConsejero', $consejero) }}" class="nav-link p-3 waves-effect waves-light"  data-tap="familia"><i class='ti-xs ti ti-calendar-event me-2'></i> Calendario de fechas</a></li>
        </ul>
      </div>
    </div>
  </div>
  <!--/ Navbar pills -->

  <p class="mb-4 text-black">Gestiona tu horario según la disponibilidad de tu agenda, así mismo bloquea tu agenda para eventualidades.</p>


  {{-- (NUEVO) Aquí empieza el formulario de Horario Habitual --}}
  <form id="formHorarioHabituales" class="" method="POST" action="{{ route('consejeria.actualizarHorarioHabitual', $consejero) }}">
    @csrf
    <div class="row g-4">

      {{-- Iteramos sobre los 7 días de la semana --}}
      @foreach ($diasSemana as $diaNumero => $diaNombre)
        @php
          // Verificamos si hay horarios guardados para este día
          $horariosDelDia = $horariosExistentes->get($diaNumero, collect());
          $estaActivo = $horariosDelDia->isNotEmpty();
        @endphp

        <div class="col-md-6">
          <div class="card border rounded-3 {{ $estaActivo ? 'card-dia-activo' : '' }}">
            <div class="card-header d-flex justify-content-between align-items-center py-3 ">
              <h5 class="card-title fw-semibold mb-0 fs-6">{{ $diaNombre }}</h5>
              <div class="form-check form-switch mb-0">
                <input
                  class="form-check-input toggle-dia"
                  type="checkbox"
                  role="switch"
                  id="toggle-dia-{{ $diaNumero }}"
                  data-dia="{{ $diaNumero }}"
                  {{ $estaActivo ? 'checked' : '' }}
                >
              </div>
            </div>

            {{-- El 'collapse' se mostrará si está activo --}}
            <div class="card-body pt-2 collapse {{ $estaActivo ? 'show' : '' }}">

              {{-- Contenedor para las franjas --}}
              <div class="franjas-container">

                @if ($estaActivo)
                  {{-- Si hay horarios, los mostramos --}}
                  @foreach ($horariosDelDia as $index => $horario)
                    <div class="row g-2 align-items-center mb-2 franja-item">
                      <div class="col">
                        <label class="form-label small text-black">Desde</label>
                        <input
                          type="text"
                          class="form-control form-control-sm time-picker"
                          name="horarios[{{ $diaNumero }}][{{ $index }}][inicio]"
                          value="{{ \Carbon\Carbon::parse($horario->hora_inicio)->format('H:i') }}"
                          placeholder="HH:MM"
                        >
                      </div>
                      <div class="col">
                        <label class="form-label small text-black">Hasta</label>
                        <input
                          type="text"
                          class="form-control form-control-sm time-picker"
                          name="horarios[{{ $diaNumero }}][{{ $index }}][fin]"
                          value="{{ \Carbon\Carbon::parse($horario->hora_fin)->format('H:i') }}"
                          placeholder="HH:MM"
                        >
                      </div>
                      <div class="col-auto">
                        <button type="button" class="btn btn-icon btn-text-danger btn-eliminar-franja" style="margin-top: 24px;">
                          <i class="ti ti-trash"></i>
                        </button>
                      </div>
                    </div>
                  @endforeach
                @else
                  {{-- Si está inactivo pero se activa, mostramos una franja por defecto --}}
                  <div class="row g-2 align-items-center mb-2 franja-item">
                    <div class="col">
                      <label class="form-label small text-black">Desde</label>
                      <input
                        type="text"
                        class="form-control form-control-sm  time-picker"
                        name="horarios[{{ $diaNumero }}][0][inicio]"
                        placeholder="HH:MM"
                        disabled {{-- Deshabilitado por defecto --}}
                      >
                    </div>
                    <div class="col">
                      <label class="form-label small text-black">Hasta</label>
                      <input
                        type="text"
                        class="form-control form-control-sm  time-picker"
                        name="horarios[{{ $diaNumero }}][0][fin]"
                        placeholder="HH:MM"
                        disabled {{-- Deshabilitado por defecto --}}
                      >
                    </div>
                    <div class="col-auto">
                      <button type="button" class="btn btn-icon btn-text-danger btn-eliminar-franja" style="margin-top: 24px;">
                        <i class="ti ti-trash"></i>
                      </button>
                    </div>
                  </div>
                @endif
              </div>

              {{-- Botón para añadir más franjas --}}
              <button type="button" class="btn btn-text-secondary waves-effect btn-añadir-franja mt-2" data-dia="{{ $diaNumero }}">
                <i class="ti ti-circle-plus me-1"></i> Añadir otra franja de horario
              </button>
            </div>
          </div>
        </div>
      @endforeach

    </div>
    {{-- Botón de guardado --}}

    {{-- TEMPLATE para la franja horaria (debe estar oculto) --}}
    <template id="franja-template">
      <div class="row g-2 align-items-center mb-2 franja-item">
        <div class="col">
          <label class="form-label small text-black">Desde</label>
          <input type="text" class="form-control form-control-sm time-picker" name="inicio" placeholder="HH:MM">
        </div>
        <div class="col">
          <label class="form-label small text-black">Hasta</label>
          <input type="text" class="form-control form-control-sm time-picker" name="fin" placeholder="HH:MM">
        </div>
        <div class="col-auto">
          <button type="button" class="btn btn-icon btn-text-danger btn-eliminar-franja" style="margin-top: 24px;">
            <i class="ti ti-trash"></i>
          </button>
        </div>
      </div>
    </template>

    <div class="mt-4">
      <button type="submit" class="btn btn-primary rounded-pill px-12 py-2 waves-effect waves-light">Guardar</button>
    </div>
  </form>
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {



      // --- Inicialización de Time Pickers ---
      function initTimePicker(element) {
        flatpickr(element, {
          enableTime: true,
          noCalendar: true,
          dateFormat: "H:i",
          time_24hr: true,
          minuteIncrement: 15,
          locale: "es",
        });
      }
      document.querySelectorAll('.time-picker').forEach(picker => initTimePicker(picker));


      // --- Lógica para el formulario de Horario Habitual ---
      const formHorarioHabitual = document.getElementById('formHorarioHabituales');
      const franjaTemplate = document.getElementById('franja-template');

      // 1. Manejo del TOGGLE (interruptor) del día
      formHorarioHabitual.querySelectorAll('.toggle-dia').forEach(toggle => {
        const card = toggle.closest('.card');
        const collapseTarget = card.querySelector('.collapse');
        const bsCollapse = new bootstrap.Collapse(collapseTarget, { toggle: false });

        toggle.addEventListener('change', function() {
          const inputs = collapseTarget.querySelectorAll('input.time-picker');

          if (this.checked) {
            bsCollapse.show();
            card.classList.add('card-dia-activo');
            inputs.forEach(input => input.disabled = false);
          } else {
            bsCollapse.hide();
            card.classList.remove('card-dia-activo');
            inputs.forEach(input => input.disabled = true);
          }
        });
      });

      // 2. Manejo de botones AÑADIR y ELIMINAR franja
      formHorarioHabitual.addEventListener('click', function(e) {

        // --- AÑADIR FRANJA ---
        // Esto añade los inputs directamente a la card, sin modal.
        if (e.target.classList.contains('btn-añadir-franja')) {
          const diaNumero = e.target.dataset.dia;
          const container = e.target.closest('.card-body').querySelector('.franjas-container');
          const index = container.querySelectorAll('.franja-item').length;

          const newFranja = franjaTemplate.content.cloneNode(true);

          const inputInicio = newFranja.querySelector('input[name="inicio"]');
          const inputFin = newFranja.querySelector('input[name="fin"]');

          inputInicio.name = `horarios[${diaNumero}][${index}][inicio]`;
          inputFin.name = `horarios[${diaNumero}][${index}][fin]`;

          container.appendChild(newFranja);

          // Inicializa los time-pickers para los nuevos inputs
          initTimePicker(inputInicio);
          initTimePicker(inputFin);
        }

        // --- ELIMINAR FRANJA ---
        // Esto elimina la franja (la fila de inputs) directamente.
        if (e.target.closest('.btn-eliminar-franja')) {
          const franjaItem = e.target.closest('.franja-item');
          const container = franjaItem.closest('.franjas-container');

          if (container.querySelectorAll('.franja-item').length > 1) {
            franjaItem.remove(); // Elimina la franja
          } else {
            // Si es la última, solo limpia los valores
            franjaItem.querySelectorAll('input').forEach(input => input.value = '');
          }
        }
      });


      // 3. Envío del formulario con AJAX
      formHorarioHabitual.addEventListener('submit', function(e) {
        e.preventDefault();
        // ... (resto del código de envío AJAX que ya te di) ...
        // ... (Este código ya está correcto) ...
        const formData = new FormData(formHorarioHabitual);
        const url = formHorarioHabitual.action;

        fetch(url, {
          method: 'POST',
          body: formData,
          headers: {
            'X-CSRF-TOKEN': formData.get('_token'),
            'Accept': 'application/json'
          }
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {

          if (status === 200) { // Éxito
            Swal.fire({
              icon: 'success',
              title: '¡Guardado!',
              text: body.message,
              customClass: { confirmButton: 'btn btn-primary rounded-pill' },
              buttonsStyling: false
            });
          } else if (status === 422) { // Error de validación
            // La función en tu controlador ya valida que no se pisen/solapen
            let errorMessages = Object.values(body.errors).flat()[0];
            Swal.fire({
              icon: 'error',
              title: 'Error de validación',
              html: errorMessages,
              customClass: { confirmButton: 'btn btn-primary rounded-pill' },
              buttonsStyling: false
            });
          } else { // Error 500
            throw new Error(body.message || 'Error en el servidor');
          }
        })
        .catch(error => {
          console.error('Error en fetch:', error);
          Swal.fire({
            icon: 'error',
            title: '¡Oops...!',
            text: error.message || 'No se pudo conectar con el servidor.',
            customClass: { confirmButton: 'btn btn-primary rounded-pill' },
            buttonsStyling: false
          });
        });
      });

    });
  </script>
@endsection
