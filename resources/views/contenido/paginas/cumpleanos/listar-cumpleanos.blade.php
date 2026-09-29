@php
$configData = Helper::appClasses();
use Illuminate\Support\Str;
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Próximos Cumpleaños')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/quill/typography.scss',
'resources/assets/vendor/libs/quill/editor.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/quill/quill.js'
])
@endsection

@section('page-script')

<script>
  let timeoutEscritura = null;

  // Función para aplicar filtros y recargar con los nuevos parámetros
  function aplicarFiltros(nombre, fechaRango) {
    let url = new URL(window.location.href);
    if (nombre) {
      url.searchParams.set('nombre', nombre);
    } else {
      url.searchParams.delete('nombre');
    }

    if (fechaRango) {
      url.searchParams.set('fecha_rango', fechaRango);
      url.searchParams.delete('dias');
    } else {
      url.searchParams.delete('fecha_rango');
    }

    window.location.href = url.toString();
  }

  document.addEventListener('DOMContentLoaded', function() {
    const bsRangePickerRange = $('#bs-rangepicker-range');
    const filtroNombreInput = document.getElementById('filtro-nombre');

    // 1. Listener de búsqueda por nombre con debounce
    if (filtroNombreInput) {
      filtroNombreInput.addEventListener('input', function() {
        clearTimeout(timeoutEscritura);
        timeoutEscritura = setTimeout(function() {
          aplicarFiltros(filtroNombreInput.value.trim(), bsRangePickerRange.val());
        }, 500);
      });
    }

    // 2. Configuración y activación de DateRangePicker con formato YYYY-MM-DD
    if (bsRangePickerRange.length) {
      const fechaIni = moment('{{ $fechaInicioFiltro->format('Y-m-d') }}');
      const fechaFin = moment('{{ $fechaFinFiltro->format('Y-m-d') }}');

      bsRangePickerRange.daterangepicker({
        startDate: fechaIni,
        endDate: fechaFin,
        autoUpdateInput: true,
        ranges: {
          'Próximos 30 días': [moment(), moment().add(30, 'days')],
          'Próximos 60 días': [moment(), moment().add(60, 'days')],
          'Próximos 90 días': [moment(), moment().add(90, 'days')],
          'Este Mes': [moment().startOf('month'), moment().endOf('month')],
          'Próximo Mes': [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')],
          'Todo el Año': [moment().startOf('year'), moment().endOf('year')]
        },
        locale: {
          format: 'YYYY-MM-DD',
          separator: ' - ',
          applyLabel: 'Aplicar',
          cancelLabel: 'Limpiar',
          fromLabel: 'Desde',
          toLabel: 'Hasta',
          customRangeLabel: 'Rango personalizado',
          daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
          monthNames: [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
          ],
          firstDay: 1
        }
      });

      // Evento al aplicar nuevo rango en el picker
      bsRangePickerRange.on('apply.daterangepicker', function(ev, picker) {
        const fechaFormateada = picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD');
        bsRangePickerRange.val(fechaFormateada);
        aplicarFiltros(filtroNombreInput ? filtroNombreInput.value.trim() : '', fechaFormateada);
      });

      // Evento al limpiar/cancelar en el picker
      bsRangePickerRange.on('cancel.daterangepicker', function(ev, picker) {
        bsRangePickerRange.val('');
        aplicarFiltros(filtroNombreInput ? filtroNombreInput.value.trim() : '', '');
      });
    }

    // 3. Listener para remover tags individuales
    document.querySelectorAll('.btn-remove-tag').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const field = btn.getAttribute('data-field');
        if (field === 'filtro-nombre') {
          aplicarFiltros('', bsRangePickerRange.val());
        } else if (field === 'bs-rangepicker-range') {
          aplicarFiltros(filtroNombreInput ? filtroNombreInput.value.trim() : '', '');
        }
      });
    });

    // 4. Inicialización del editor enriquecido Quill para el modal de correo
    if (document.getElementById('editor')) {
      const toolbarOptions = [
        ['bold', 'italic', 'underline'],
        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
        [{ 'color': [] }, { 'background': [] }],
        ['clean']
      ];

      const quill = new Quill('#editor', {
        theme: 'snow',
        placeholder: 'Escribe tu mensaje de felicitación aquí...',
        modules: { toolbar: toolbarOptions }
      });

      // Sincronizar contenido con el input hidden
      quill.on('text-change', function() {
        document.getElementById('message').value = quill.root.innerHTML;
      });

      // Llenar datos al abrir el modal
      const modalEnviarCorreo = document.getElementById('modalEnviarCorreo');
      if (modalEnviarCorreo) {
        modalEnviarCorreo.addEventListener('show.bs.modal', function(event) {
          const button = event.relatedTarget;
          if (button) {
            const nombre = button.getAttribute('data-nombre');
            const email = button.getAttribute('data-email');

            modalEnviarCorreo.querySelector('.modal-title').textContent = 'Enviar correo a ' + nombre;
            modalEnviarCorreo.querySelector('#recipient_email').value = email;
            modalEnviarCorreo.querySelector('#recipient_name_email').value = nombre;
          }

          if (!document.getElementById('message').value) {
            quill.root.innerHTML = '<p>¡Hola! Te deseo un muy feliz cumpleaños. 🎉</p>';
          }
        });
      }

      // Validación y alerta de carga al enviar el formulario
      const formCorreo = document.getElementById('formEnviarCorreo');
      if (formCorreo) {
        formCorreo.addEventListener('submit', function(e) {
          document.getElementById('message').value = quill.root.innerHTML;

          if (quill.getText().trim().length === 0) {
            e.preventDefault();
            Swal.fire({
              icon: 'warning',
              title: 'Mensaje vacío',
              text: 'Por favor escribe un mensaje para el cumpleañero.',
              customClass: { confirmButton: 'btn btn-primary' }
            });
            return false;
          }

          // Cerrar modal y mostrar alerta de envío en progreso
          const modalInstancia = bootstrap.Modal.getInstance(document.getElementById('modalEnviarCorreo'));
          if (modalInstancia) {
            modalInstancia.hide();
          }

          Swal.fire({
            title: 'Enviando correo...',
            text: 'Por favor espera mientras se envía la felicitación.',
            icon: 'info',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });
        });
      }
    }

    // Alertas de sesión (SweetAlert)
    @if(session('success'))
      Swal.fire({
        icon: 'success',
        title: '¡Enviado!',
        text: "{{ session('success') }}",
        customClass: { confirmButton: 'btn btn-primary' }
      });
    @endif

    @if(session('danger'))
      Swal.fire({
        icon: 'error',
        title: 'Error al enviar',
        text: "{{ session('danger') }}",
        customClass: { confirmButton: 'btn btn-danger' }
      });
    @endif
  });
</script>
@endsection

@section('content')

{{-- Encabezado de la página --}}
<h4 class="mb-4 fw-semibold text-primary">Próximos cumpleaños</h4>

<div class="mb-4">
  <div class="row g-3">
    {{-- Filtro por Nombre --}}
    <div class="col-md-6 col-12">
      <label for="filtro-nombre" class="form-label">Buscar por nombre</label>
      <input type="text" id="filtro-nombre" class="form-control" placeholder="Escribe el nombre del cumpleañero..." value="{{ request('nombre') }}">
    </div>

    {{-- Filtro por Rango de Fechas (Formato año-mes-dia) --}}
    <div class="col-md-6 col-12">
      <label for="bs-rangepicker-range" class="form-label">Rango de fechas</label>
      <input type="text" id="bs-rangepicker-range" class="form-control" value="{{ request('fecha_rango', $rangoFechaTexto) }}">
    </div>

    {{-- SECCIÓN DE TAGS (Filtros activos) --}}
    <div class="col-12 filter-tags py-2">
      @if(request('nombre'))
        <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary btn-remove-tag ps-2 pe-1 mt-1 me-2" data-field="filtro-nombre">
          <span class="align-middle">Nombre: {{ request('nombre') }} <i class="ti ti-x"></i></span>
        </button>
      @endif

      @if(request('fecha_rango'))
        <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary btn-remove-tag ps-2 pe-1 mt-1 me-2" data-field="bs-rangepicker-range">
          <span class="align-middle">Fecha: {{ $rangoFechaTexto }} <i class="ti ti-x"></i></span>
        </button>
      @endif

      @if(request('nombre') || request('fecha_rango'))
        <a href="{{ route('cumpleanos.listarCumpleanos') }}" class="btn btn-xs rounded-pill btn-secondary remove-tag ps-2 pe-1 mt-1">
          <span class="align-middle">Restablecer filtros <i class="ti ti-rotate"></i></span>
        </a>
      @endif
    </div>
  </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  {{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('danger'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  {{ session('danger') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- Listado de Cumpleañeros --}}
@if($cumpleanosProximos30Dias->isNotEmpty())
<div class="row">
  @foreach($cumpleanosProximos30Dias as $usuario)

  @php
  $edadACumplir = $usuario->proximo_cumpleanos->year - $usuario->fecha_nacimiento->year;
  $emailValido = $usuario->email && !Illuminate\Support\Str::contains($usuario->email, 'correopordefecto.com');
  $mensajeWsp = rawurlencode("¡Hola {$usuario->primer_nombre}! 🥳 ¡Feliz cumpleaños! Deseo que pases un día increíble.");
  @endphp

  <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
    <div class="card h-100 shadow-sm d-flex flex-column">

      {{-- CUERPO DE LA TARJETA --}}
      <div class="card-body text-center pb-3">
        <div class="mx-auto mb-3 d-flex justify-content-center">
          @if(!$usuario->foto || $usuario->foto == "default-m.png" || $usuario->foto == "default-f.png")
            <div class="avatar avatar-xl" style="width: 100px; height: 100px;">
              <span class="avatar-initial rounded-circle border border-3 border-white bg-info fs-2">{{ $usuario->inicialesNombre() }}</span>
            </div>
          @else
            <div class="avatar avatar-xl" style="width: 100px; height: 100px;">
              <img src="{{ $usuario->foto_url }}"
                alt="{{ $usuario->foto }}" class="avatar-initial rounded-circle border border-3 border-white bg-info"
                style="width: 100px; height: 100px; object-fit: cover;"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
              <span class="avatar-initial rounded-circle border border-3 border-white bg-info fs-2" style="display: none; width: 100px; height: 100px;">{{ $usuario->inicialesNombre() }}</span>
            </div>
          @endif
        </div>

        <p class="text-primary fw-semibold mb-1">
          <i class="ti ti-cake me-1"></i> {{ $usuario->fecha_nacimiento ? $usuario->fecha_nacimiento->locale('es')->translatedFormat('d \d\e F') : '--' }}
        </p>
        <h5 class="card-title mb-1">{{ $usuario->nombre(3) }}</h5>
        <p class="card-text text-black">Cumplirá <strong>{{ $edadACumplir }} años</strong></p>
      </div>

      {{-- FOOTER CON BOTONES --}}
      <div class="card-footer bg-transparent border-top d-flex justify-content-center gap-2 mt-auto pt-3 pb-3">

        @if($usuario->link_whatsapp)
        <a href="{{ $usuario->link_whatsapp }}?text={{ $mensajeWsp }}"
          target="_blank"
          class="btn btn-success btn-sm rounded-pill d-flex align-items-center"
          title="Enviar WhatsApp">
          <i class="ti ti-brand-whatsapp me-1"></i> Felicitar
        </a>
        @endif

        @if($emailValido)
        <button type="button" class="btn btn-primary btn-sm rounded-pill"
          data-bs-toggle="modal" data-bs-target="#modalEnviarCorreo"
          data-nombre="{{ $usuario->nombre(3) }}" data-email="{{ $usuario->email }}">
          <i class="ti ti-send me-1"></i> Enviar correo
        </button>
        @endif

        @if(!$emailValido && !$usuario->link_whatsapp)
        <span class="text-muted small">Sin contacto</span>
        @endif

      </div>
    </div>
  </div>
  @endforeach
</div>

@else
{{-- Mensaje cuando no hay cumpleañeros en el rango --}}
<div class="alert alert-info" role="alert">
  <h6 class="alert-heading mb-1">¡Todo tranquilo por aquí!</h6>
  <p class="mb-0">No se encontraron cumpleaños en el rango de fechas seleccionado ({{ $rangoFechaTexto }}).</p>
</div>
@endif

{{-- MODAL ENVIAR CORREO --}}
<div class="modal fade" id="modalEnviarCorreo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form id="formEnviarCorreo" action="{{ route('cumpleanos.enviarCorreo') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="modalCorreoTitle">Enviar correo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="recipient_email" id="recipient_email">
          <input type="hidden" name="recipient_name" id="recipient_name_email">

          <div class="col-12 mb-3">
            <label for="subject" class="form-label">Asunto</label>
            <input type="text" class="form-control" name="subject" id="subject" value="¡Feliz cumpleaños!" required>
          </div>

          <div class="col-12 mb-3">
            <label class="form-label">Contenido del mensaje</label>
            <div id="editor"></div>
            <textarea name="message" id="message" class="d-none" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary rounded-pill"><i class="ti ti-send me-2"></i>Enviar</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
