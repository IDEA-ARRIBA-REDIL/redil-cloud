@php
// Usamos la misma variable que viene en tus otras vistas
$configData = Helper::appClasses();
// ¡NUEVO! Usamos Str para revisar si el email es uno por defecto
use Illuminate\Support\Str;
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Próximos Cumpleaños')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/nouislider/nouislider.scss',
'resources/assets/vendor/libs/quill/typography.scss',
'resources/assets/vendor/libs/quill/editor.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/nouislider/nouislider.js',
'resources/assets/vendor/libs/quill/quill.js'
])
@endsection

@section('page-script')

<script>
  let timeoutEscritura = null;

  // Función de filtros (sin cambios)
  function aplicarFiltros(nombre, fechaRango) {
    let url = new URL(window.location.href);
    if (nombre) { url.searchParams.set('nombre', nombre); } else { url.searchParams.delete('nombre'); }
    if (fechaRango) {
      url.searchParams.set('fecha_rango', fechaRango);
      url.searchParams.delete('dias');
    } else {
      url.searchParams.delete('fecha_rango');
    }
    window.location.href = url.toString();
  }

  document.addEventListener('DOMContentLoaded', function() {

    // --- LÓGICA EXISTENTE DE FILTROS Y DATEPICKER (Resumida para no repetir todo) ---
    const bsRangePickerRange = $('#bs-rangepicker-range');
    const filtroNombreInput = document.getElementById('filtro-nombre');
    const sliderDias = document.getElementById('slider-dias');
    const rangoFechaURL = new URLSearchParams(window.location.search).get('fecha_rango');

    // ... (Tu lógica de filtros, datepicker y slider se mantiene igual aquí) ...
    // Asegúrate de NO BORRAR tu lógica de daterangepicker y slider que tenías arriba.

    // Listener del input nombre
    filtroNombreInput.addEventListener('input', function() {
      clearTimeout(timeoutEscritura);
      if (filtroNombreInput.value.length >= 3) {
        aplicarFiltros(filtroNombreInput.value.trim(), bsRangePickerRange.val());
      }
    });

    // Configuración Datepicker (Tu código existente va aquí...)
    if (bsRangePickerRange.length) {
       // ... tu código de daterangepicker ...
        bsRangePickerRange.daterangepicker({
            autoUpdateInput: false,
            // ... resto de tu config ...
            locale: { format: 'DD/MM/YYYY', cancelLabel: 'Cancelar', applyLabel: 'Aplicar' }
        });
        // ... tus eventos on apply y cancel ...
        bsRangePickerRange.on('apply.daterangepicker', function(ev, picker) {
            const fechaFormateada = picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY');
            bsRangePickerRange.val(fechaFormateada);
            aplicarFiltros(filtroNombreInput.value.trim(), fechaFormateada);
        });
        bsRangePickerRange.on('cancel.daterangepicker', function(ev, picker) {
            bsRangePickerRange.val('');
            aplicarFiltros(filtroNombreInput.value.trim(), '');
        });
    }

    // Configuración Slider (Tu código existente va aquí...)
    if (typeof noUiSlider !== 'undefined' && sliderDias && !rangoFechaURL) {
        // ... tu código de slider ...
    }

    // =========================================================
    // AQUI COMIENZA LA CORRECCIÓN DE QUILL
    // =========================================================

    // 1. Inicializar Quill
    // Verificamos que exista el elemento #editor para evitar errores
    if(document.getElementById('editor')){
        var toolbarOptions = [
            ['bold', 'italic', 'underline'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'color': [] }, { 'background': [] }],
            ['clean']
        ];

        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Escribe tu mensaje de felicitación aquí...',
            modules: { toolbar: toolbarOptions }
        });

        // 2. Cargar contenido previo (Old Input)
        var oldMessage = `{!! old('message', '') !!}`;
        if(oldMessage) {
            quill.root.innerHTML = oldMessage;
        }

        // 3. Sincronizar Quill con el Input Oculto "message"
        quill.on('text-change', function() {
            var html = quill.root.innerHTML;
            document.getElementById('message').value = html;
        });

        // 4. Lógica del Modal (Cargar datos y limpiar editor)
        const modalEnviarCorreo = document.getElementById('modalEnviarCorreo');
        if (modalEnviarCorreo) {
            modalEnviarCorreo.addEventListener('show.bs.modal', function(event) {
            // Obtener datos del botón que abrió el modal
            const button = event.relatedTarget;
            if(button){ // Validación por si se abre manualmente
              const nombre = button.getAttribute('data-nombre');
              const email = button.getAttribute('data-email');

              // Llenar campos
              modalEnviarCorreo.querySelector('.modal-title').textContent = 'Enviar correo a ' + nombre;
              modalEnviarCorreo.querySelector('#recipient_email').value = email;
              modalEnviarCorreo.querySelector('#recipient_name_email').value = nombre;
            }

            // Limpiar editor si está vacío el input hidden (nuevo correo)
            // O cargar mensaje por defecto
            if(document.getElementById('message').value === '') {
              quill.root.innerHTML = '<p>¡Hola! Te deseo un muy feliz cumpleaños. 🎉</p>';
            }
            });
        }

        // 5. Validación al enviar el formulario del modal
        const formCorreo = document.getElementById('formEnviarCorreo');
        if(formCorreo){
          formCorreo.addEventListener('submit', function(e) {
            // Actualizar input hidden antes de enviar
            document.getElementById('message').value = quill.root.innerHTML;

            // Validar que no esté vacío (Quill deja un <p><br></p> cuando está vacío)
            if (quill.getText().trim().length === 0) {
              e.preventDefault();
              Swal.fire({
                icon: 'warning',
                title: 'Mensaje vacío',
                text: 'Por favor escribe un mensaje para el cumpleañero.',
                customClass: { confirmButton: 'btn btn-primary' }
              });
            }
          });
        }
    }
  });
</script>
@endsection

@section('content')

{{-- Encabezado de la página --}}
<h4 class="mb-4 fw-semibold text-primary">Próximos cumpleaños</h4>

<div class="mb-4 ">
        <div class="row g-3">
            {{-- Filtro por Nombre --}}
            <div class="col-md-6 col-12">
                <label for="filtro-nombre" class="form-label">Buscar por nombre</label>
                {{-- Mantenemos el valor actual si existe --}}
                <input type="text" id="filtro-nombre" class="form-control" placeholder="Escribe el nombre del cumpleañero..." value="{{ request('nombre') }}">
            </div>

            {{-- Filtro por Rango de Fechas --}}
            <div class="col-md-6 col-12">
                <label for="bs-rangepicker-range" class="form-label">Rango de cumpleaños</label>
                {{-- Inicializamos el valor del input con el filtro actual si existe --}}
                <input type="text" id="bs-rangepicker-range" class="form-control" value="{{ request('fecha_rango') }}">
            </div>

            {{-- SECCIÓN DE TAGS (Aquí se muestran los filtros activos) --}}
            <div class="col-12 filter-tags py-2">
                {{-- 1. Tag de Nombre --}}
                @if(request('nombre'))
                  <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1 me-2" data-field="filtro-nombre">
                      <span class="align-middle">Nombre: {{ request('nombre') }} <i class="ti ti-x"></i></span>
                  </button>
                @endif

                {{-- 2. Tag de Fechas --}}
                @if(request('fecha_rango'))
                  <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1 me-2" data-field="bs-rangepicker-range">
                    <span class="align-middle">Fecha: {{ request('fecha_rango') }} <i class="ti ti-x"></i></span>
                </button>
                @endif

                {{-- 3. Tag de Días (Solo si es diferente a 30 y no hay fecha rango activa) --}}
                @if(request('dias') && request('dias') != 30 && !request('fecha_rango'))
                     <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1 me-2" data-field="slider-dias">
                        <span class="align-middle">Próximos: {{ request('dias') }} días <i class="ti ti-x"></i></span>
                    </button>
                @endif

                {{-- 4. Botón de Limpiar Todo (Aparece si hay CUALQUIER filtro) --}}
                @if(request('nombre') || request('fecha_rango') || (request('dias') && request('dias') != 30))
                  <a href="{{ url()->current() }}" class="btn btn-xs rounded-pill btn-secondary remove-tag ps-2 pe-1 mt-1">
                    <span class="align-middle">Quitar todos los filtros <i class="ti ti-x"></i></span>
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


{{-- Verificamos si la colección tiene datos --}}
@if($cumpleanosProximos30Dias->isNotEmpty())

<div class="row">
  @foreach($cumpleanosProximos30Dias as $usuario)

  @php
  // Calculamos edad
  $edadACumplir = $usuario->proximo_cumpleanos->year - $usuario->fecha_nacimiento->year;
  // Validamos email
  $emailValido = $usuario->email && !Illuminate\Support\Str::contains($usuario->email, 'correopordefecto.com');
  // Preparamos mensaje de WhatsApp
  $mensajeWsp = rawurlencode("¡Hola {$usuario->primer_nombre}! 🥳 ¡Feliz cumpleaños! Deseo que pases un día increíble.");
  @endphp

  <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
    <div class="card h-100 shadow-sm d-flex flex-column">

      {{-- CUERPO DE LA TARJETA --}}
      <div class="card-body text-center pb-3">
        <div class="mx-auto mb-3">
          {{-- FOTO --}}
          <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$usuario->foto) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$usuario->foto }}"
            alt="Foto" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;"
            onerror="this.src='{{ asset('assets/img/avatars/default-m.png') }}'">
        </div>

        <p class="text-primary fw-semibold mb-1">
          <i class="ti ti-cake me-1"></i> {{ $usuario->fecha_nacimiento->format('d \d\e F') }}
        </p>
        <h5 class="card-title mb-1">{{ $usuario->nombre(3) }}</h5>
        <p class="card-text">Cumplirá <strong>{{ $edadACumplir }} años</strong></p>
      </div>

      {{-- FOOTER CON LOS BOTONES --}}
      <div class="card-footer bg-transparent border-top d-flex justify-content-center gap-2 mt-auto pt-3 pb-3">

        @if($usuario->link_whatsapp)
        <a href="{{ $usuario->link_whatsapp }}?text={{ $mensajeWsp }}"
          target="_blank"
          class="btn btn-success btn-sm d-flex align-items-center"
          title="Enviar WhatsApp">
          <i class="ti ti-brand-whatsapp me-1"></i> Felicitar
        </a>
        @endif

        @if($emailValido)
        <button type="button" class="btn btn-primary rounded-pill"
          data-bs-toggle="modal" data-bs-target="#modalEnviarCorreo"
          data-nombre="{{ $usuario->nombre(3) }}" data-email="{{ $usuario->email }}">
          <i class="ti ti-send me-2"></i> Enviar correo
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
{{-- Mensaje si no se encontraron cumpleaños --}}
<div class="alert alert-info" role="alert">
  <h6 class="alert-heading mb-1">¡Todo tranquilo por aquí!</h6>
  <p class="mb-0">No se encontraron cumpleaños en el rango seleccionado.</p>
</div>
@endif


{{-- ====================================================================== --}}
{{-- ==                          MODAL                                   == --}}
{{-- ====================================================================== --}}

<div class="modal fade" id="modalEnviarCorreo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg"> {{-- Agregué modal-lg para más espacio --}}
    <div class="modal-content">
      <form id="formEnviarCorreo" action="{{ route('cumpleanos.enviarCorreo') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="modalCorreoTitle">Enviar correo a...</h5>
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

            {{-- Contenedor visual de Quill --}}
            <div id="editor"></div>

            {{-- Input oculto que llevará los datos al backend --}}
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
