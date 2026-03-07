@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Usuarios')

<!-- Page -->
@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/swiper/swiper.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/swiper/swiper.js'
])
@endsection


@section('page-script')

<script>
  $(document).ready(function() {
    $('.select2').select2({
      dropdownParent: $('#offcanvasCrearConsejero')
    });
  });
</script>

<script>
    // Espera a que todo el DOM esté cargado
    document.addEventListener("DOMContentLoaded", function() {

        @if ($errors->any())
          // Espera 100 milisegundos
          setTimeout(function() {
              var offcanvasElement;

              @if (session('origen_error') == 'editar')
                  // Si el error vino de la edición, abre el offcanvas de EDICIÓN
                  offcanvasElement = document.getElementById('offcanvasEditarConsejero');
              @else
                  // Por defecto (o si el error vino de crear), abre el offcanvas de CREACIÓN
                  offcanvasElement = document.getElementById('offcanvasCrearConsejero');
              @endif

              if (offcanvasElement) {
                  var offcanvas = new bootstrap.Offcanvas(offcanvasElement);
                  offcanvas.show();
              }
          }, 100); // 100ms de retraso
        @endif


        //    se reinicialicen correctamente al mostrarse el offcanvas.
        //    Esto es por si usas el mismo script para abrirlos con un botón.
        var offcanvasEl = document.getElementById('offcanvasCrearConsejero');
        if (offcanvasEl) {
            offcanvasEl.addEventListener('shown.bs.offcanvas', function () {
                // Asegúrate de que JQuery esté cargado si usas $
                if (window.jQuery) {
                    $('#sedes').select2({
                        placeholder: "Seleccione las sedes",
                        dropdownParent: $('#offcanvasCrearConsejero')
                    });
                    $('#tiposConsejeria').select2({
                        placeholder: "Seleccione los tipos",
                        dropdownParent: $('#offcanvasCrearConsejero')
                    });
                }
            });
        }

        var offcanvasEditEl = document.getElementById('offcanvasEditarConsejero');
        if (offcanvasEditEl) {
            offcanvasEditEl.addEventListener('shown.bs.offcanvas', function () {
                if (window.jQuery) {
                    $('#edit_sedes').select2({
                        placeholder: "Seleccione las sedes",
                        dropdownParent: $('#offcanvasEditarConsejero')
                    });
                    $('#edit_tiposConsejeria').select2({
                        placeholder: "Seleccione los tipos",
                        dropdownParent: $('#offcanvasEditarConsejero')
                    });
                }
            });
        }

        document.querySelectorAll('.btn-editar-consejero').forEach(button => {
        button.addEventListener('click', function() {

            // Solo poblamos si no venimos de un error de validación
            @if (!$errors->any() || session('origen_error') != 'editar')

                // Obtiene los datos del botón
                const actionUrl = this.dataset.actionUrl;
                const descripcion = this.dataset.descripcion;
                const sedesIds = JSON.parse(this.dataset.sedesIds);
                const tiposIds = JSON.parse(this.dataset.tiposIds);
                const usuarioNombre = this.dataset.usuarioNombre;
                const atencionPresencial = this.dataset.atencionPresencial == '1';
                const atencionVirtual = this.dataset.atencionVirtual == '1';
                const direccion = this.dataset.direccion;

                const duracionCita = this.dataset.duracionCita;
                const tiempoDescanso = this.dataset.tiempoDescanso;
                const antelacionMinima = this.dataset.antelacionMinima;
                const maximoFuturo = this.dataset.maximoFuturo;

                // 1. Establece la URL de acción del formulario
                const form = document.getElementById('formEditarConsejero');
                form.setAttribute('action', actionUrl);

                // 2. Puebla los campos del formulario
                document.getElementById('edit_usuario_nombre').value = usuarioNombre;
                document.getElementById('edit_descripcion').value = descripcion;

                document.getElementById('edit_atencion_presencial').checked = atencionPresencial;
                document.getElementById('edit_atencion_virtual').checked = atencionVirtual;
                document.getElementById('edit_direccion').value = direccion;

                document.getElementById('edit_duracion_cita').value = duracionCita;
                document.getElementById('edit_tiempo_descanso').value = tiempoDescanso;
                document.getElementById('edit_antelacion_minima').value = antelacionMinima;
                document.getElementById('edit_maximo_futuro').value = maximoFuturo;

                // Disparamos manualmente la lógica de visibilidad de la dirección
                const chkEditPresencial = document.getElementById('edit_atencion_presencial');
                const divEditDireccion = document.getElementById('div_edit_direccion');
                if (chkEditPresencial.checked) {
                    divEditDireccion.style.display = 'block';
                } else {
                    divEditDireccion.style.display = 'none';
                }
                // ---

                // 3. Puebla los Select2 (usando jQuery)
                $('#edit_sedes').val(sedesIds).trigger('change');
                $('#edit_tiposConsejeria').val(tiposIds).trigger('change');

            @endif
            });
        });

    });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
      // --- Lógica para "Crear Consejero" ---
      const chkPresencial = document.getElementById('atencion_presencial');
      const divDireccion = document.getElementById('div_direccion');
      const inputDireccion = document.getElementById('direccion');
      if (chkPresencial && divDireccion) {
          // Función para actualizar la visibilidad
          function toggleDireccion() {
              if (chkPresencial.checked) {
                  divDireccion.style.display = 'block';
              } else {
                  divDireccion.style.display = 'none';
                  inputDireccion.value = '';
              }
          }
          // Actualizar al cambiar el check
          chkPresencial.addEventListener('change', toggleDireccion);
          // Estado inicial al cargar (si 'old' está presente o por defecto)
          toggleDireccion();
      }

      // --- Lógica para "Editar Consejero" ---
      const chkEditPresencial = document.getElementById('edit_atencion_presencial');
      const divEditDireccion = document.getElementById('div_edit_direccion');

      if (chkEditPresencial && divEditDireccion) {
          // Función para actualizar la visibilidad
          function toggleEditDireccion() {
              if (chkEditPresencial.checked) {
                  divEditDireccion.style.display = 'block';
              } else {
                  divEditDireccion.style.display = 'none';
              }
          }
          // Actualizar al cambiar el check
          chkEditPresencial.addEventListener('change', toggleEditDireccion);
          // El estado inicial se gestiona en el listener del botón 'btn-editar-consejero'
      }
  });
</script>

<script>
    const buscarInput = document.getElementById('buscar');
    const btnBorrarBusquedaPorPalabra = document.getElementById('borrarBusquedaPorPalabra');
    const formularioBuscar = document.getElementById('formBuscar');
    let timeoutId;
    const delay = 1000; // Tiempo en milisegundos después de dejar de escribir para enviar el formulario

    buscarInput.addEventListener('input', function() {
        clearTimeout(timeoutId); // Limpiar cualquier timeout anterior

        if (this.value.length >= 3) {
          timeoutId = setTimeout(() => {
              formularioBuscar.submit();
          }, delay);
        }else if(this.value.length == 0)
        {
          formularioBuscar.submit();
        }
    });

    btnBorrarBusquedaPorPalabra.addEventListener('click', function() {
      buscarInput.value = "";
      formularioBuscar.submit();
    });
</script>

<script>
  document.querySelectorAll('.remove-tag').forEach(button => {
    button.addEventListener('click', function() {
      const field = this.dataset.field;
      const fieldAux = this.dataset.field2;
      const value = this.dataset.value;

      const form = document.getElementById('busquedaAvanzada');
      const input = form.querySelector('[id="' + field + '"]');

      if (input && $(input).hasClass('select2')) {
        // Si es un Select2, usa el método 'val' de Select2 para eliminar la opción
        let currentValues = $(input).val();
        if (Array.isArray(currentValues)) {
            // Si es un select múltiple
            const newValue = currentValues.filter(v => v != value);
            $(input).val(newValue).trigger('change');
        } else {
            // Si es un select simple
            $(input).val(null).trigger('change');
        }
      } else if (input && input.tagName === 'SELECT' && input.multiple) {
        // Si es un select múltiple nativo (poco probable con Select2, pero por si acaso)
        let currentValues = Array.from(input.selectedOptions).map(option => option.value);
        const newValue = currentValues.filter(v => v != value);
        for (let i = 0; i < input.options.length; i++) {
            input.options[i].selected = newValue.includes(input.options[i].value);
        }
        $(input).trigger('change'); // Dispara el evento change para otras posibles escuchas*/
      } else if (input && input.tagName === 'SELECT') {
        // Si es un select simple nativo
        input.value = '';
      } else if (input) {
        // Si es un input normal
        input.value = '';
        if(fieldAux)
        {
          const inputAux = form.querySelector('[id="' + fieldAux + '"]');
          inputAux.value = '';
        }
      }

      form.submit();
    });
  });
</script>

<script>
  function sinComillas(e) {
    tecla = (document.all) ? e.keyCode : e.which;
    patron =/[\x5C'"]/;
    te = String.fromCharCode(tecla);
    return !patron.test(te);
  }
</script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Seleccionamos todos los elementos que se pueden colapsar
    const collapseElements = document.querySelectorAll('.collapse');

    collapseElements.forEach(function(collapseEl) {
      // Escuchamos el evento que Bootstrap dispara ANTES de empezar a MOSTRAR el contenido
      collapseEl.addEventListener('show.bs.collapse', function() {
        // Buscamos el botón que controla este div en específico
        const triggerButton = document.querySelector(`[data-bs-target="#${collapseEl.id}"]`);
        if (triggerButton) {
          const icon = triggerButton.querySelector('span.ti');
          // Cambiamos el ícono a 'menos'
          icon.classList.remove('ti-plus');
          icon.classList.add('ti-minus');
        }
      });

      // Escuchamos el evento que Bootstrap dispara ANTES de empezar a OCULTAR el contenido
      collapseEl.addEventListener('hide.bs.collapse', function() {
        const triggerButton = document.querySelector(`[data-bs-target="#${collapseEl.id}"]`);
        if (triggerButton) {
          const icon = triggerButton.querySelector('span.ti');
          // Cambiamos el ícono a 'más'
          icon.classList.remove('ti-minus');
          icon.classList.add('ti-plus');
        }
      });
    });
  });
</script>

<script>
  $(document).ready(function() {

      // Escucha CUALQUIER envío de un formulario que tenga la clase 'form-confirmar-accion'
      // Usamos 'body' para que funcione con contenido dinámico (como el de Livewire)
      $('body').on('submit', '.form-confirmar-accion', function(e) {

          e.preventDefault(); // Detiene el envío normal del formulario

          var form = $(this); // El formulario que se intentó enviar
          var mensaje = form.data('msj-confirmacion') || '¿Estás seguro de que deseas realizar esta acción?';
          var tipo = form.data('swal-tipo') || 'confirmacion'; // Leemos el nuevo atributo

          // Configuración basada en el tipo (normal o peligro)
          var confirmButtonColor = (tipo === 'peligro') ? '#d33' : '#3085d6';
          var confirmButtonText = (tipo === 'peligro') ? 'Sí, ¡eliminar!' : 'Sí, continuar';
          var icon = (tipo === 'peligro') ? 'warning' : 'question';
          var title = (tipo === 'peligro') ? '¡Acción irreversible!' : 'Confirmar acción';

          Swal.fire({
              title: title,
              text: mensaje,
              icon: icon,
              showCancelButton: true,
          focusConfirm: false,
          confirmButtonText: 'Si, eliminar',
          cancelButtonText: 'Cancelar',
          customClass: {
            confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
            cancelButton: 'btn btn-label-secondary waves-effect waves-light'
          },
          buttonsStyling: false
          }).then((result) => {
              if (result.isConfirmed) {
                  // Si el usuario confirma, ahora sí envía el formulario
                  form.get(0).submit();
              }
          });
      });

  });
</script>
@endsection

@section('content')


{{-- Cabecera y Botón de Nuevo Maestro y Filtros --}}
  <div class="row mb-4 mt-4 align-items-center">
      <div class="col-md-6">
          <h4 class="mb-1 fw-semibold text-primary">Gestionar consejeros</h4>
          <p class="mb-0 text-black">Aquí podrás crear y gestionar tus consejeros.</p>
      </div>
  </div>

  @include('layouts.status-msn')

  <form id="formBuscar" class="forms-sample" method="GET" action="{{ route('consejeria.gestionarConsejeros') }}">
    <div class="row mt-5">

      <div class="col-9 col-md-4">
        <div class="input-group input-group-merge bg-white">
          <input id="buscar" name="buscar" type="text" value="{{$buscar}}" class="form-control" placeholder="Busqueda..." aria-describedby="btnBusqueda">
          @if($buscar)
          <span id="borrarBusquedaPorPalabra" class="input-group-text"><i class="ti ti-x"></i></span>
          @else
          <span class="input-group-text"><i class="ti ti-search"></i></span>
          @endif
        </div>
      </div>

      <div class="col-3 col-md-8 d-flex justify-content-end">
        @if($rolActivo->hasPermissionTo('consejeria.boton_nuevo_consejero'))
        <button type="button" class="btn btn-primary px-2 px-md-3" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCrearConsejero"><span class="d-none d-md-block fw-semibold">Nuevo consejero</span><i class="ti ti-plus ms-1"></i> </button>
        @endif
      </div>
    </div>

    <div class="filter-tags py-3">
      <span class="text-black me-5">{{ $consejeros->total() > 1 ? $consejeros->total().' Consejeros' : $consejeros->total().' Consejero' }}</span>
      @if(isset($tagsBusqueda) && is_array($tagsBusqueda))
        @foreach($tagsBusqueda as $tag)
          <button type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1" data-field="{{ $tag->field }}" data-field2="{{ $tag->fieldAux }}" data-value="{{ $tag->value }}">
            <span class="align-middle">{{ $tag->label }}<i class="ti ti-x"></i> </span>
          </button>
        @endforeach
        @if($bandera == 1)
          <a type="button" href="{{ route('consejeria.gestionarConsejeros') }}" class="btn btn-xs rounded-pill btn-secondary remove-tag ps-2 pe-1 mt-1">
            <span class="align-middle">Quitar todos los filtros <i class="ti ti-x"></i> </span>
          </a>
        @endif
      @endif
    </div>
  </form>



<!-- Listado de persona -->
<div class="row equal-height-row g-4 mt-1">
  @if(count($consejeros)>0)
  @foreach($consejeros as $consejero)
  <div class="col equal-height-col col-12 col-md-6" id="persona-card-{{ $consejero->id }}">
    <div class="card rounded-3 shadow">
      <div class="card-header border-bottom d-flex px-4 pt-3 pb-1" style="background-color:#F9F9F9!important">
        <div class="flex-fill row">
          <div class=" d-flex justify-content-between align-items-center">
            <h5 class="fw-semibold ms-1 text-black m-0">
               {{ $consejero->usuario->nombre(3) ?? 'Usuario no encontrado' }}
            </h5>
            <span class="badge px-3 py-1 rounded-pill bg-label-{{ $consejero->activo ? 'primary' : 'danger' }}">
              {{ $consejero->activo ? 'Activo' : 'Inactivo' }}
            </span>
          </div>
        </div>

        <div class="">
          <div class="ms-auto">
            <div class="dropdown zindex-2 p-1 float-end">
              <button type="button" class="btn btn-sm rounded-pill btn-icon btn-outline-secondary waves-effect" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i> </button>
              <ul class="dropdown-menu dropdown-menu-end">
                @if($rolActivo->hasPermissionTo('consejeria.opcion_configurar_horarios'))
                <li><a class="dropdown-item" href="{{ route('consejeria.configurarHorariosCosejero', $consejero) }}">Configurar horarios</a></li>
                @endif
                @if($rolActivo->hasPermissionTo('consejeria.opcion_editar_consejero'))
                <li>
                  <button type="button" class="dropdown-item btn-editar-consejero"
                      data-bs-toggle="offcanvas"
                      data-bs-target="#offcanvasEditarConsejero"
                      data-action-url="{{ route('consejeria.actualizarConsejero', $consejero) }}"
                      data-descripcion="{{ $consejero->descripcion }}"
                      data-sedes-ids="{{ $consejero->sedes->pluck('id')->toJson() }}"
                      data-tipos-ids="{{ $consejero->tipoConsejerias->pluck('id')->toJson() }}"
                      data-usuario-nombre="{{ $consejero->usuario->nombre(3) ?? 'Usuario no encontrado' }}"
                      data-atencion-presencial="{{ $consejero->atencion_presencial ? '1' : '0' }}"
                      data-atencion-virtual="{{ $consejero->atencion_virtual ? '1' : '0' }}"
                      data-direccion="{{ $consejero->direccion }}"
                      data-duracion-cita="{{ $consejero->duracion_cita_minutos }}"
                      data-tiempo-descanso="{{ $consejero->buffer_entre_citas_minutos }}"
                      data-antelacion-minima="{{ $consejero->dias_minimos_antelacion }}"
                      data-maximo-futuro="{{ $consejero->dias_maximos_futuro }}"
                      >
                      Editar
                  </button>
                </li>
                @endif
                @if($rolActivo->hasPermissionTo('consejeria.opcion_activar_desactivar_consejero'))
                  @if (!$consejero->activo)
                  <li>
                    <form action="{{ route('consejeria.activar', $consejero) }}" method="POST" class="form-confirmar-accion"
                          data-msj-confirmacion="¿Seguro que deseas activar a este consejero?">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="dropdown-item text-dark">Activar</button>
                    </form>
                  </li>
                  @endif
                @endif
                <hr class="dropdown-divider">
                @if($rolActivo->hasPermissionTo('consejeria.opcion_activar_desactivar_consejero'))
                  @if ($consejero->activo)
                  <li>
                      <form action="{{ route('consejeria.desactivar', $consejero) }}" method="POST" class="form-confirmar-accion" data-msj-confirmacion="¿Seguro que deseas desactivar a este consejero?">
                          @csrf
                          @method('PATCH')
                          <button type="submit" class="dropdown-item text-danger">Desactivar</button>
                      </form>
                  </li>
                  @endif
                @endif

                @if($rolActivo->hasPermissionTo('consejeria.opcion_eliminar_consejero'))
                  <li>
                      <form action="{{ route('consejeria.eliminarConsejero', $consejero) }}" method="POST"
                            class="form-confirmar-accion"
                            data-msj-confirmacion="¿Seguro que deseas eliminar a este consejero? Esta acción es permanente y no se puede deshacer."
                            data-swal-tipo="peligro">

                          @csrf
                          @method('DELETE')

                          <button type="submit" class="dropdown-item text-danger">
                              Eliminar
                          </button>
                      </form>
                  </li>
                @endif
              </ul>
            </div>
          </div>
        </div>

      </div>

      <div class="card-body">
        <div class="row mt-4">

          <div class="col-12 d-flex flex-column">
            <small class="text-black">Descripción</small>
            <small class="fw-semibold text-black ">{{ $consejero->descripcion ?? 'No especificado'}}</small>
          </div>


          <div class="col-6 d-flex flex-column mt-1">
            <small class="text-black">Email</small>
            <small class="fw-semibold text-black ">{{ $consejero->usuario->email ?? 'No especificado'}}</small>
          </div>

          @php
          $telefonos = collect([
          $consejero->usuario->telefono_fijo,
          $consejero->usuario->telefono_movil
          ])->filter();

          $textoTelefonos = $telefonos->isNotEmpty() ? $telefonos->implode(', ') : 'No indicados';
          @endphp

          <div class="col-6 d-flex flex-column mt-1">
            <small class="text-black">Teléfono</small>
            <small class="fw-semibold text-black ">{{ $telefonos->isNotEmpty() ? $textoTelefonos : 'No especificado'}}</small>
          </div>

          <div class="col-6 d-flex flex-column mt-1">
            <small class="text-black">{{ $consejero->usuario->tipoIdentificacion ? $consejero->usuario->tipoIdentificacion->abreviatura : 'ID'}}</small>
            <small class="fw-semibold text-black ">{{ $consejero->usuario->identificacion ? $consejero->usuario->identificacion : 'No indicado'}}</small>
          </div>
        </div>

        <div class="collapse" id="cardBodyPersona{{ $consejero->id }}">
          <div class="row">

            <div class="col-12">
              <hr class="my-3 border-1">
            </div>

            <div class="col-12 col-md-6 d-flex flex-column mt-1">
                <small class="text-black">Tipo de atención</small>
                <small class="fw-semibold text-black ">
                    @php $atenciones = [];
                    if ($consejero->atencion_presencial) $atenciones[] = 'Presencial';
                    if ($consejero->atencion_virtual) $atenciones[] = 'Virtual';
                    @endphp
                    {{ count($atenciones) > 0 ? implode(', ', $atenciones) : 'No especificado' }}
                </small>
            </div>

            @if($consejero->atencion_presencial && $consejero->direccion)
            <div class="col-12 col-md-6 d-flex flex-column mt-1">
                <small class="text-black">Dirección de atención</small>
                <small class="fw-semibold text-black ">{{ $consejero->direccion }}</small>
            </div>
            @endif

            <div class="col-12 col-md-6 d-flex flex-column mt-1">
              <small class="text-black">Duración de la cita:</small>
              <small class="fw-semibold text-black">{{ $consejero->duracion_cita_minutos }} minutos</small>
            </div>

            <div class="col-12 col-md-6 d-flex flex-column mt-1">
              <small class="text-black">Descanso entre citas:</small>
              <small class="fw-semibold text-black">{{ $consejero->buffer_entre_citas_minutos }} minutos</small>
            </div>

            <div class="col-12 col-md-6 d-flex flex-column mt-1">
              <small class="text-black">Días de antelación:</small>
              <small class="fw-semibold text-black">{{ $consejero->dias_minimos_antelacion }} días</small>
            </div>

            <div class="col-12 col-md-6 d-flex flex-column mt-1">
              <small class="text-black">Diás maximos para agendar:</small>
              <small class="fw-semibold text-black">{{ $consejero->dias_maximos_futuro }} días</small>
            </div>

              <div class="col-12 mt-1">
              <small class="text-black">Tipo de consejerias asignadas:</small>
              <div>
                  @forelse ($consejero->tipoConsejerias as $tipo)
                  <span type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1">
                    {{ $tipo->nombre }}
                  </span>
                  @empty
                  <small class="fw-semibold text-black">No asignadas</small>
                  @endforelse
              </div>
            </div>

            <div class="col-12 mt-1">
              <small class="text-black">Sedes asignadas:</small>
              <div>
                  @forelse ($consejero->sedes as $sede)
                  <span type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1">
                    {{ $sede->nombre }}
                  </span>
                  @empty
                  <small class="fw-semibold text-black">No asignadas</small>
                  @endforelse
              </div>
            </div>
          </div>


        </div>
      </div>

      <div class="card-footer border-top p-1">
        <div class="d-flex justify-content-center">
          <button type="button"
            class="btn btn-xs rounded-pill btn-icon btn-outline-secondary waves-effect my-2"
            data-bs-toggle="collapse"
            data-bs-target="#cardBodyPersona{{ $consejero->id }}">
            <span class="ti ti-plus"></span>
          </button>
        </div>
      </div>
    </div>
  </div>
  @endforeach
  @else
  <div class="mt-5 mb-5 py-5">
    <center>
      <i class="ti ti-user ti-xl"></i>
      <p>La busqueda no arrojo ningun resultado.</p>
    </center>
  </div>
  @endif
</div>
<!--/ Listado de persona -->


<div class="row my-3">
  @if($consejeros)
  <p> {{$consejeros->lastItem()}} <b>de</b> {{$consejeros->total()}} <b>personas - Página</b> {{ $consejeros->currentPage() }} </p>
  {!! $consejeros->appends(request()->input())->links() !!}
  @endif
</div>

  {{-- Offcanvas para Crear Consejero --}}
  <form id="formCrearConsejero" class="" method="POST" action="{{ route('consejeria.crearConsejero') }}">
    @csrf
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCrearConsejero"
      aria-labelledby="offcanvasCrearConsejeroLabel">
      <div class="offcanvas-header">
          <h4 id="offcanvasCrearConsejeroLabel" class="offcanvas-title text-primary fw-semibold">Nuevo consejero</h4>
          <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>

      <div class="offcanvas-body pt-6 px-8">
          <div class="row">
            {{-- Buscador de Usuarios --}}
            <div class="col-12 mb-3">
                @livewire('usuarios.usuarios-para-busqueda', [
                  'id' => 'user_id',
                  'class' => 'col-12 col-md-12 mb-3',
                  'label' => 'Selecciona el usuario',
                  'estiloSeleccion' => 'pequeno',
                  'placeholder' => 'Busca por nombre, identificación o email',
                  'tipoBuscador' => 'unico',
                  'queUsuariosCargar' => 'todos',
                  'conDadosDeBaja' => 'no',
                  'obligatorio' => true,
                ])
            </div>

            {{-- Descripción --}}
            <div class="col-12 mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea id="descripcion" name="descripción" class="form-control" rows="4">{{ old('descripción') }}</textarea>
            </div>

            <div class="col-12 mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="atencion_virtual" name="atención_virtual"
                        {{ old('atención_virtual') == 'on' ? 'checked' : '' }}>
                    <label class="form-check-label" for="atencion_virtual">Habilitar atención virtual</label>
                </div>
            </div>

            <div class="col-12 mb-3">
                <div class="form-check form-switch">
                    {{-- Usamos 'checked' por defecto y 'old' para repoblar si falla la validación --}}
                    <input class="form-check-input" type="checkbox" id="atencion_presencial" name="atención_presencial"
                        {{ old('atención_presencial') == 'on' ? 'checked' : '' }}>
                    <label class="form-check-label" for="atencion_presencial">Habilitar atención presencial</label>
                </div>
            </div>

            {{-- Dirección (Condicional) --}}
            {{-- Mostramos si old('atención_presencial') es 'on' o si no hay 'old' (valor por defecto) --}}
            <div class="col-12 mb-3" id="div_direccion" style="{{ old('atención_presencial', 'on') == 'on' ? '' : 'display: none;' }}">
                <label for="direccion" class="form-label">Dirección (para citas presenciales)</label>
                <input id="direccion" name="dirección" class="form-control" value="{{ old('dirección') }}">
                @error('dirección')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Sedes -->
            <div class="col-12 mb-3">
              <label for="sedes" class="form-label">Asigna las sedes</label>
              <select id="sedes" name="sedes[]" class="select2 form-select" multiple>
                @foreach($sedes as $sede)
                <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                @endforeach
              </select>
              @error('sedes')
                  <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
            </div>

            <!-- tiposConsejeria -->
            <div class="col-12 mb-3">
              <label for="tiposConsejeria" class="form-label">Asigna los tipos de consejería</label>
              <select id="tiposConsejeria" name="tiposConsejeria[]" class="select2 form-select" multiple>
                @foreach($tiposConsejeria as $tipo)
                <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                @endforeach
              </select>
              @error('tiposConsejeria')
                  <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 mb-3">
                <label for="duracion_cita" class="form-label">Duración de la cita (min)</label>
                <input type="number" id="duracion_cita" name="duracion_cita" class="form-control" min="1" value="{{ old('duracion_cita', 45) }}" required>
                @error('duracion_cita')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 mb-3">
                <label for="tiempo_descanso" class="form-label">Descanso entre citas (min)</label>
                <input type="number" id="tiempo_descanso" name="tiempo_descanso" class="form-control" min="0" value="{{ old('tiempo_descanso', 15) }}" required>
                @error('tiempo_descanso')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 mb-3">
                <label for="antelacion_minima" class="form-label">Días de antelación para sacar cita</label>
                <input type="number" id="antelacion_minima" name="antelacion_minima" class="form-control" min="0" value="{{ old('antelacion_minima', 1) }}" required>
                @error('antelacion_minima')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 mb-3">
                <label for="maximo_futuro" class="form-label">Diás maximos para agendar cita</label>
                <input type="number" id="maximo_futuro" name="maximo_futuro" class="form-control" min="1" value="{{ old('maximo_futuro', 30) }}" required>
                @error('maximo_futuro')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
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

  {{-- Offcanvas para EDITAR Consejero --}}
  <form id="formEditarConsejero" class="" method="POST" action=""> {{-- La action se pondrá con JS --}}
    @csrf
    @method('PATCH') {{-- Importante para la ruta de actualización --}}

    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditarConsejero"
      aria-labelledby="offcanvasEditarConsejeroLabel">
      <div class="offcanvas-header">
        <h4 id="offcanvasEditarConsejeroLabel" class="offcanvas-title text-primary fw-semibold">Editar consejero</h4>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>

      <div class="offcanvas-body pt-6 px-8">
        <div class="row">

          {{-- Usuario (Solo lectura) --}}
          <div class="col-12 mb-3">
              <label for="edit_usuario_nombre" class="form-label">Consejero</label>
              <input type="text" id="edit_usuario_nombre" class="form-control" readonly disabled>
          </div>

          {{-- Descripción --}}
          <div class="col-12 mb-3">
            <label for="edit_descripcion" class="form-label">Descripción</label>
            <textarea id="edit_descripcion" name="descripción" class="form-control" rows="4">{{ old('descripción') }}</textarea>
          </div>



          <div class="col-12 mb-3">
              <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="edit_atencion_virtual" name="atención_virtual"
                      {{ old('atención_virtual') == 'on' ? 'checked' : '' }}>
                  <label class="form-check-label" for="edit_atencion_virtual">Habilitar atención virtual</label>
              </div>
          </div>

          <div class="col-12 mb-3">
              <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="edit_atencion_presencial" name="atención_presencial"
                      {{ old('atención_presencial') == 'on' ? 'checked' : '' }}>
                  <label class="form-check-label" for="edit_atencion_presencial">Habilitar atención Presencial</label>
              </div>
          </div>

          {{-- Dirección (Condicional) --}}
          <div class="col-12 mb-3" id="div_edit_direccion" style="{{ old('atencion_presencial') == 'on' ? '' : 'display: none;' }}">
              <label for="edit_direccion" class="form-label">Dirección (para citas presenciales)</label>
              <input id="edit_direccion" name="dirección" class="form-control" value="{{ old('dirección') }}">
              @error('dirección')
                  @if(session('origen_error') == 'editar')
                      <div class="text-danger small mt-1">{{ $message }}</div>
                  @endif
              @enderror
          </div>

          <div class="col-12 mb-3">
            <label for="edit_sedes" class="form-label">Asigna las sedes</label>
            {{-- Usamos old('sedes') para repoblar en caso de error de validación --}}
            <select id="edit_sedes" name="sedes[]" class="form-select" multiple>
              @foreach($sedes as $sede)
              <option value="{{ $sede->id }}"
                {{ (is_array(old('sedes')) && in_array($sede->id, old('sedes'))) ? 'selected' : '' }}>
                {{ $sede->nombre }}
              </option>
              @endforeach
            </select>
            @error('sedes')
                {{-- Asegúrate que los errores se muestren si vienes de 'editar' --}}
                @if(session('origen_error') == 'editar')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @endif
            @enderror
          </div>

          <div class="col-12 mb-3">
            <label for="edit_tiposConsejeria" class="form-label">Asigna los tipos de consejería</label>
            <select id="edit_tiposConsejeria" name="tiposConsejeria[]" class="form-select" multiple>
              @foreach($tiposConsejeria as $tipo)
              <option value="{{ $tipo->id }}"
                {{ (is_array(old('tiposConsejeria')) && in_array($tipo->id, old('tiposConsejeria'))) ? 'selected' : '' }}>
                {{ $tipo->nombre }}
              </option>
              @endforeach
            </select>
            @error('tiposConsejeria')
              {{-- Asegúrate que los errores se muestren si vienes de 'editar' --}}
              @if(session('origen_error') == 'editar')
                  <div class="text-danger small mt-1">{{ $message }}</div>
              @endif
            @enderror
          </div>

          <div class="col-12 mb-3">
              <label for="duracion_cita" class="form-label">Duración de la cita (min)</label>
              <input type="number" id="edit_duracion_cita" name="duracion_cita" class="form-control" min="1" value="{{ old('duracion_cita') }}" required>
              @error('duracion_cita')
                  @if(session('origen_error') == 'editar')
                      <div class="text-danger small mt-1">{{ $message }}</div>
                  @endif
              @enderror
          </div>

          <div class="col-12 mb-3">
              <label for="tiempo_descanso" class="form-label">Descanso entre citas (min)</label>
              <input type="number" id="edit_tiempo_descanso" name="tiempo_descanso" class="form-control" min="0" value="{{ old('tiempo_descanso') }}" required>
              @error('tiempo_descanso')
                  @if(session('origen_error') == 'editar')
                      <div class="text-danger small mt-1">{{ $message }}</div>
                  @endif
              @enderror
          </div>

          <div class="col-12 mb-3">
              <label for="antelacion_minima" class="form-label">Días de antelación para sacar cita</label>
              <input type="number" id="edit_antelacion_minima" name="antelacion_minima" class="form-control" min="0" value="{{ old('antelacion_minima') }}" required>
              @error('antelacion_minima')
                  @if(session('origen_error') == 'editar')
                      <div class="text-danger small mt-1">{{ $message }}</div>
                  @endif
              @enderror
          </div>

          <div class="col-12 mb-3">
              <label for="maximo_futuro" class="form-label">Diás maximos para agendar cita</label>
              <input type="number" id="edit_maximo_futuro" name="maximo_futuro" class="form-control" min="1" value="{{ old('maximo_futuro') }}" required>
              @error('maximo_futuro')
                  @if(session('origen_error') == 'editar')
                      <div class="text-danger small mt-1">{{ $message }}</div>
                  @endif
              @enderror
          </div>
        </div>

      </div>

      {{-- Footer con los botones de acción --}}
      <div class="offcanvas-footer border-top p-3">
        <button type="submit" class="btn btn-primary waves-effect rounded-pill me-2">Actualizar</button>
        <button type="button" class="btn btn-outline-secondary rounded-pill waves-effect " data-bs-dismiss="offcanvas">Cancelar</button>
      </div>
    </div>
  </form>


@endsection
