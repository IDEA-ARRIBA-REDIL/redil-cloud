@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Gestionar Intercesores')

@section('page-style')
@vite([
  'resources/assets/vendor/scss/pages/page-profile.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js',
])
@endsection

@section('page-script')
<script>
  $(document).ready(function() {
    $('.select2').select2({
      dropdownParent: $('#offcanvasCrearIntercesor')
    });
  });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        @if ($errors->any())
          setTimeout(function() {
              var offcanvasElement;

              @if (session('origen_error') == 'editar')
                  offcanvasElement = document.getElementById('offcanvasEditarIntercesor');
              @else
                  offcanvasElement = document.getElementById('offcanvasCrearIntercesor');
              @endif

              if (offcanvasElement) {
                  var offcanvas = new bootstrap.Offcanvas(offcanvasElement);
                  offcanvas.show();
              }
          }, 100);
        @endif

        var offcanvasEl = document.getElementById('offcanvasCrearIntercesor');
        if (offcanvasEl) {
            offcanvasEl.addEventListener('shown.bs.offcanvas', function () {
                if (window.jQuery) {
                    $('#sedes').select2({
                        placeholder: "Seleccione las sedes",
                        dropdownParent: $('#offcanvasCrearIntercesor')
                    });
                    $('#tiposPeticion').select2({
                        placeholder: "Seleccione los tipos de petición",
                        dropdownParent: $('#offcanvasCrearIntercesor')
                    });
                }
            });
        }

        var offcanvasEditEl = document.getElementById('offcanvasEditarIntercesor');
        if (offcanvasEditEl) {
            offcanvasEditEl.addEventListener('shown.bs.offcanvas', function () {
                if (window.jQuery) {
                    $('#edit_sedes').select2({
                        placeholder: "Seleccione las sedes",
                        dropdownParent: $('#offcanvasEditarIntercesor')
                    });
                    $('#edit_tiposPeticion').select2({
                        placeholder: "Seleccione los tipos de petición",
                        dropdownParent: $('#offcanvasEditarIntercesor')
                    });
                }
            });
        }

        // Lógica de toggle para Nuevo Intercesor
        $('#solo_peticiones_asignadas').on('change', function() {
            if ($(this).is(':checked')) {
                $('#contenedor_asignaciones_crear').slideUp();
            } else {
                $('#contenedor_asignaciones_crear').slideDown();
            }
        });

        // Lógica de toggle para Editar Intercesor
        $('#edit_solo_peticiones_asignadas').on('change', function() {
            if ($(this).is(':checked')) {
                $('#contenedor_asignaciones_editar').slideUp();
            } else {
                $('#contenedor_asignaciones_editar').slideDown();
            }
        });

        document.querySelectorAll('.btn-editar-intercesor').forEach(button => {
            button.addEventListener('click', function() {
                @if (!$errors->any() || session('origen_error') != 'editar')
                    const actionUrl = this.dataset.actionUrl;
                    const descripcion = this.dataset.descripcion;
                    const sedesIds = JSON.parse(this.dataset.sedesIds);
                    const tiposIds = JSON.parse(this.dataset.tiposIds);
                    const usuarioNombre = this.dataset.usuarioNombre;
                    const soloAsignadas = this.dataset.soloAsignadas === '1';
                    const verInvitados = this.dataset.verInvitados === '1';

                    const form = document.getElementById('formEditarIntercesor');
                    form.setAttribute('action', actionUrl);

                    document.getElementById('edit_usuario_nombre').value = usuarioNombre;
                    document.getElementById('edit_descripcion').value = descripcion;

                    const switchEdit = document.getElementById('edit_solo_peticiones_asignadas');
                    switchEdit.checked = soloAsignadas;

                    const switchInvitadosEdit = document.getElementById('edit_ver_peticiones_de_invitados');
                    if (switchInvitadosEdit) {
                        switchInvitadosEdit.checked = verInvitados;
                    }

                    if (soloAsignadas) {
                        $('#contenedor_asignaciones_editar').hide();
                    } else {
                        $('#contenedor_asignaciones_editar').show();
                    }

                    $('#edit_sedes').val(sedesIds).trigger('change');
                    $('#edit_tiposPeticion').val(tiposIds).trigger('change');
                @endif
            });
        });
    });
</script>

<script>
    const buscarInput = document.getElementById('buscar');
    const btnBorrarBusquedaPorPalabra = document.getElementById('borrarBusquedaPorPalabra');
    const formularioBuscar = document.getElementById('formBuscar');
    let timeoutId;
    const delay = 1000;

    if (buscarInput) {
        buscarInput.addEventListener('input', function() {
            clearTimeout(timeoutId);

            if (this.value.length >= 3) {
              timeoutId = setTimeout(() => {
                  formularioBuscar.submit();
              }, delay);
            } else if(this.value.length == 0) {
              formularioBuscar.submit();
            }
        });
    }

    if (btnBorrarBusquedaPorPalabra) {
        btnBorrarBusquedaPorPalabra.addEventListener('click', function() {
          buscarInput.value = "";
          formularioBuscar.submit();
        });
    }
</script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const collapseElements = document.querySelectorAll('.collapse');

    collapseElements.forEach(function(collapseEl) {
      collapseEl.addEventListener('show.bs.collapse', function() {
        const triggerButton = document.querySelector(`[data-bs-target="#${collapseEl.id}"]`);
        if (triggerButton) {
          const icon = triggerButton.querySelector('span.ti');
          icon.classList.remove('ti-plus');
          icon.classList.add('ti-minus');
        }
      });

      collapseEl.addEventListener('hide.bs.collapse', function() {
        const triggerButton = document.querySelector(`[data-bs-target="#${collapseEl.id}"]`);
        if (triggerButton) {
          const icon = triggerButton.querySelector('span.ti');
          icon.classList.remove('ti-minus');
          icon.classList.add('ti-plus');
        }
      });
    });
  });
</script>

<script>
  $(document).ready(function() {
      $('body').on('submit', '.form-confirmar-accion', function(e) {
          e.preventDefault();

          var form = $(this);
          var mensaje = form.data('msj-confirmacion') || '¿Estás seguro de que deseas realizar esta acción?';
          var tipo = form.data('swal-tipo') || 'confirmacion';

          var confirmButtonColor = (tipo === 'peligro') ? '#d33' : '#3085d6';
          var icon = (tipo === 'peligro') ? 'warning' : 'question';
          var title = (tipo === 'peligro') ? '¡Acción irreversible!' : 'Confirmar acción';

          Swal.fire({
              title: title,
              text: mensaje,
              icon: icon,
              showCancelButton: true,
              focusConfirm: false,
              confirmButtonText: tipo === 'peligro' ? 'Sí, eliminar' : 'Sí, continuar',
              cancelButtonText: 'Cancelar',
              customClass: {
                confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                cancelButton: 'btn btn-label-secondary waves-effect waves-light'
              },
              buttonsStyling: false
          }).then((result) => {
              if (result.isConfirmed) {
                  form.get(0).submit();
              }
          });
      });
  });
</script>
@endsection

@section('content')
  <div class="row mb-4 mt-4 align-items-center">
      <div class="col-md-6">
          <h4 class="mb-1 fw-semibold text-primary">Gestionar intercesores</h4>
          <p class="mb-0 text-black">Aquí podrás crear y gestionar tus intercesores.</p>
      </div>
  </div>

  @include('layouts.status-msn')

  <form id="formBuscar" class="forms-sample" method="GET" action="{{ route('peticiones.gestionarIntercesores') }}">
    <div class="row mt-5">
      <div class="col-9 col-md-4">
        <div class="input-group input-group-merge bg-white">
          <input id="buscar" name="buscar" type="text" value="{{ $buscar }}" class="form-control" placeholder="Búsqueda por usuario..." aria-describedby="btnBusqueda">
          @if($buscar)
          <span id="borrarBusquedaPorPalabra" class="input-group-text cursor-pointer"><i class="ti ti-x"></i></span>
          @else
          <span class="input-group-text"><i class="ti ti-search"></i></span>
          @endif
        </div>
      </div>

      <div class="col-3 col-md-8 d-flex justify-content-end">
        @if($rolActivo->hasPermissionTo('peticiones.boton_nuevo_intercesor'))
        <button type="button" class="btn btn-primary px-2 px-md-5 rounded-pill" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCrearIntercesor">
            <span class="d-none d-md-block">Nuevo intercesor</span>
            <i class="ti ti-plus ms-1"></i>
        </button>
        @endif
      </div>
    </div>

    <div class="filter-tags py-3">
      <span class="text-black me-5">{{ $intercesores->total() > 1 ? $intercesores->total().' Intercesores' : $intercesores->total().' Intercesor' }}</span>
      @if(isset($tagsBusqueda) && is_array($tagsBusqueda) && count($tagsBusqueda) > 0)
        @foreach($tagsBusqueda as $tag)
          <a type="button" href="{{ route('peticiones.gestionarIntercesores') }}" class="btn btn-xs rounded-pill btn-outline-secondary ps-2 pe-1 mt-1">
            <span class="align-middle">{{ $tag->label }}<i class="ti ti-x ms-1"></i></span>
          </a>
        @endforeach
      @endif
    </div>
  </form>

<!-- Listado de intercesores -->
<div class="row equal-height-row g-4 mt-1">
  @if(count($intercesores) > 0)
  @foreach($intercesores as $intercesor)
  <div class="col equal-height-col col-12 col-md-6" id="intercesor-card-{{ $intercesor->id }}">
    <div class="card rounded-3 shadow border-0">
      <div class="card-header border-bottom d-flex px-4 pt-3 pb-1" style="background-color:#F9F9F9!important">     
        <div class="flex-fill row">
          <div class=" d-flex justify-content-between align-items-center">
            <h5 class="fw-semibold ms-1 text-black m-0">
               {{ $intercesor->usuario->nombre(3) ?? 'Usuario no encontrado' }}
            </h5>
            <span class="badge px-3 py-1 rounded-pill bg-label-{{ $intercesor->activo ? 'primary' : 'danger' }}">
              {{ $intercesor->activo ? 'Activo' : 'Inactivo' }}
            </span>
          </div>
        </div>

        <div class="ms-2">
            <div class="dropdown">
              <button type="button" class="btn btn-sm rounded-circle btn-icon btn-outline-secondary waves-effect" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i> </button>
              <ul class="dropdown-menu dropdown-menu-end">
                @if($rolActivo->hasPermissionTo('peticiones.opcion_editar_intercesor'))
                <li>
                  <button type="button" class="dropdown-item btn-editar-intercesor"
                      data-bs-toggle="offcanvas"
                      data-bs-target="#offcanvasEditarIntercesor"
                      data-action-url="{{ route('peticiones.actualizarIntercesor', $intercesor) }}"
                      data-descripcion="{{ $intercesor->descripcion }}"
                      data-sedes-ids="{{ $intercesor->sedes->pluck('id')->toJson() }}"
                      data-tipos-ids="{{ $intercesor->tipoPeticiones->pluck('id')->toJson() }}"
                      data-usuario-nombre="{{ $intercesor->usuario->nombre(3) ?? 'Usuario no encontrado' }}"
                      data-solo-asignadas="{{ $intercesor->solo_peticiones_asignadas ? '1' : '0' }}"
                      data-ver-invitados="{{ $intercesor->ver_peticiones_de_invitados ? '1' : '0' }}"
                      >
                      Editar
                  </button>
                </li>
                @endif
                @if($rolActivo->hasPermissionTo('peticiones.opcion_activar_desactivar_intercesor'))
                  @if (!$intercesor->activo)
                  <li>
                    <form action="{{ route('peticiones.activar', $intercesor) }}" method="POST" class="form-confirmar-accion"
                          data-msj-confirmacion="¿Seguro que deseas activar a este intercesor?">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="dropdown-item text-dark">Activar</button>
                    </form>
                  </li>
                  @else
                  <li>
                      <form action="{{ route('peticiones.desactivar', $intercesor) }}" method="POST" class="form-confirmar-accion" data-msj-confirmacion="¿Seguro que deseas desactivar a este intercesor?">
                          @csrf
                          @method('PATCH')
                          <button type="submit" class="dropdown-item text-danger">Desactivar</button>
                      </form>
                  </li>
                  @endif
                @endif

                @if($rolActivo->hasPermissionTo('peticiones.opcion_eliminar_intercesor'))
                  <hr class="dropdown-divider">
                  <li>
                      <form action="{{ route('peticiones.eliminarIntercesor', $intercesor) }}" method="POST"
                            class="form-confirmar-accion"
                            data-msj-confirmacion="¿Seguro que deseas eliminar a este intercesor? Esta acción es permanente."
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

      <div class="card-body px-4 py-3">
        <div class="row mt-4">
          
          <div class="col-12 d-flex flex-column">
            <small class="text-black">Descripción</small>
            <small class="fw-semibold text-black ">{{ $intercesor->descripcion ?? 'No especificado'}}</small>
          </div>

          <div class="col-6 d-flex flex-column mt-1">
            <small class="text-black">Email</small>
            <small class="fw-semibold text-black ">{{ $intercesor->usuario->email ?? 'No especificado'}}</small>
          </div>

          <div class="col-6 d-flex flex-column mt-1">
            <small class="text-black">Identificación</small>
            <small class="fw-semibold text-black ">{{ $intercesor->usuario->identificacion ?? 'No indicado'}}</small>
          </div>

          <div class="col-6 d-flex flex-column mt-1">
            <small class="text-black">Teléfono</small>
            <small class="fw-semibold text-black ">{{ $intercesor->usuario->telefono_movil ?? $intercesor->usuario->telefono_fijo ?? 'No indicado' }}</small>
          </div>
        </div>

        <div class="collapse" id="cardBodyIntercesor{{ $intercesor->id }}">
          <div class="row">
            <div class="col-12">
              <hr class="my-3">
            </div>

            @if ($intercesor->solo_peticiones_asignadas)

              <div class="col-12 d-flex flex-column">
                <small class="text-black">¿Solo peticiones asignadas?</small>
                <small class="fw-semibold text-black ">Sí</small>
              </div>
            @else

              <div class="col-12 d-flex flex-column">
                <small class="text-black">¿Solo peticiones asignadas?</small>
                <small class="fw-semibold text-black ">No</small>
              </div>

              <div class="col-12 mt-1">
                <small class="text-black">Tipo de consejerias asignadas:</small>
                <div>
                    @forelse ($intercesor->tipoPeticiones as $tipo)
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
                    @forelse ($intercesor->sedes as $sede)
                    <span type="button" class="btn btn-xs rounded-pill btn-outline-secondary remove-tag ps-2 pe-1 mt-1">
                      {{ $sede->nombre }}
                    </span>
                    @empty
                    <small class="fw-semibold text-black">No asignadas</small>
                    @endforelse
                </div>
              </div>

              <div class="col-12 d-flex flex-column mt-1">
                <small class="text-black">¿Ver peticiones de invitados?</small>
                <small class="fw-semibold text-black ">{{ $intercesor->ver_peticiones_de_invitados ? 'Sí' : 'No' }}</small>
              </div>
            @endif
          </div>
        </div>
      </div>

      <div class="card-footer border-top p-1">        
        <div class="d-flex justify-content-center">
          <button type="button"
            class="btn btn-sm btn-icon btn-outline-secondary rounded-circle my-1 waves-effect"
            data-bs-toggle="collapse"
            data-bs-target="#cardBodyIntercesor{{ $intercesor->id }}">
            <span class="ti ti-plus"></span>
          </button>
        </div>
      </div>
    </div>
  </div>
  @endforeach
  @else
  <div class="col-12 text-center py-5">
      <i class="ti ti-users ti-xl text-muted mb-2 d-block"></i>
      <p class="text-muted">No se encontraron intercesores.</p>
  </div>
  @endif
</div>

<div class="row my-3">
  @if($intercesores->hasPages())
  <div class="col-12 d-flex justify-content-between align-items-center">
    <p class="text-muted small mb-0"> Mostrando {{ $intercesores->firstItem() }}-{{ $intercesores->lastItem() }} de {{ $intercesores->total() }} personas </p>
    {!! $intercesores->appends(request()->input())->links() !!}
  </div>
  @endif
</div>

  {{-- Offcanvas para Crear Intercesor --}}
  <form id="formCrearIntercesor" method="POST" action="{{ route('peticiones.crearIntercesor') }}">
    @csrf
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCrearIntercesor" aria-labelledby="offcanvasCrearIntercesorLabel">
      <div class="offcanvas-header">
          <h4 id="offcanvasCrearIntercesorLabel" class="offcanvas-title text-primary fw-semibold">Nuevo intercesor</h4>
          <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>

      <div class="offcanvas-body pt-6 px-8">
          <div class="row g-3">
            {{-- Buscador de Usuarios --}}
            <div class="col-12">
                @livewire('usuarios.usuarios-para-busqueda', [
                  'id' => 'user_id',
                  'class' => 'col-12 mb-3',
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
            <div class="col-12">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea id="descripcion" name="descripcion" class="form-control" rows="3" placeholder="Ingresa notas o descripción del intercesor...">{{ old('descripcion') }}</textarea>
            </div>

            {{-- Solo peticiones asignadas --}}
            <div class="col-12">
              <div class="form-check form-switch form-switch-lg mb-2">
                  <input type="checkbox" name="solo_peticiones_asignadas" id="solo_peticiones_asignadas" class="form-check-input" value="1" {{ old('solo_peticiones_asignadas', '1') == '1' ? 'checked' : '' }}>
                  <label class="form-check-label fw-medium text-black" for="solo_peticiones_asignadas">¿Solo peticiones asignadas?</label>
              </div>
              <small class="form-text text-black lh-1 fst-italic">Si está activo, el intercesor solo verá las peticiones asignadas directamente a él.</small>
            </div>

            <!-- Contenedor para sedes y tipos de petición (se ocultará si solo_peticiones_asignadas está en true) -->
            <div id="contenedor_asignaciones_crear" class="row g-3" style="{{ old('solo_peticiones_asignadas', '1') == '1' ? 'display: none;' : '' }}">
              <!-- Ver peticiones de invitados -->
              <div class="col-12 mt-1">
                <div class="form-check form-switch form-switch-lg mb-2">
                    <input type="checkbox" name="ver_peticiones_de_invitados" id="ver_peticiones_de_invitados" class="form-check-input" value="1" {{ old('ver_peticiones_de_invitados') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label fw-medium text-black" for="ver_peticiones_de_invitados">¿Ver peticiones de invitados?</label>
                </div>
                <small class="form-text text-black lh-1 fst-italic">Si está activo, el intercesor también verá las peticiones creadas por invitados (personas sin cuenta de usuario) del tipo de petición asignado.</small>
              </div>

              <!-- Sedes -->
              <div class="col-12 mt-1">
                <label for="sedes" class="form-label">Sedes asignadas</label>
                <select id="sedes" name="sedes[]" class="select2 form-select" multiple>
                  @foreach($sedes as $sede)
                  <option value="{{ $sede->id }}" {{ is_array(old('sedes')) && in_array($sede->id, old('sedes')) ? 'selected' : '' }}>{{ $sede->nombre }}</option>
                  @endforeach
                </select>
                <small class="form-text text-muted fst-italic">Si no se selecciona ninguna sede, se le asignarán todas.</small>
                @error('sedes')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <!-- tiposPeticion -->
              <div class="col-12">
                <label for="tiposPeticion" class="form-label">Tipos de Petición asignados</label>
                <select id="tiposPeticion" name="tiposPeticion[]" class="select2 form-select" multiple>
                  @foreach($tiposPeticion as $tipo)
                  <option value="{{ $tipo->id }}" {{ is_array(old('tiposPeticion')) && in_array($tipo->id, old('tiposPeticion')) ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
                  @endforeach
                </select>
                <small class="form-text text-muted fst-italic">Si no se selecciona ningún tipo, se le asignarán todos.</small>
                @error('tiposPeticion')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
      </div>

      <div class="offcanvas-footer border-top p-3 bg-light">
          <button type="submit" class="btn btn-primary rounded-pill me-2 waves-effect">Guardar</button>
          <button type="button" class="btn btn-outline-secondary rounded-pill waves-effect" data-bs-dismiss="offcanvas">Cancelar</button>
      </div>
    </div>
  </form>

  {{-- Offcanvas para EDITAR Intercesor --}}
  <form id="formEditarIntercesor" method="POST" action="">
    @csrf
    @method('PATCH')

    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditarIntercesor" aria-labelledby="offcanvasEditarIntercesorLabel">
      <div class="offcanvas-header">
        <h4 id="offcanvasEditarIntercesorLabel" class="offcanvas-title text-primary fw-semibold">Editar intercesor</h4>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>

      <div class="offcanvas-body pt-6 px-8">
        <div class="row g-3">
          {{-- Usuario (Solo lectura) --}}
          <div class="col-12">
              <label for="edit_usuario_nombre" class="form-label">Intercesor</label>
              <input type="text" id="edit_usuario_nombre" class="form-control" readonly disabled>
          </div>

          {{-- Descripción --}}
          <div class="col-12">
              <label for="edit_descripcion" class="form-label">Descripción</label>
              <textarea id="edit_descripcion" name="descripcion" class="form-control" rows="3" placeholder="Ingresa notas o descripción del intercesor...">{{ old('descripcion') }}</textarea>
          </div>

          {{-- Solo peticiones asignadas --}}
          <div class="col-12">
            <div class="form-check form-switch form-switch-lg mb-2">
                <input type="checkbox" name="solo_peticiones_asignadas" id="edit_solo_peticiones_asignadas" class="form-check-input" value="1">
                <label class="form-check-label fw-medium text-black" for="edit_solo_peticiones_asignadas">¿Solo peticiones asignadas?</label>
            </div>
            <small class="form-text text-black fst-italic">Si está activo, el intercesor solo verá las peticiones asignadas directamente a él.</small>
          </div>

          <!-- Contenedor para sedes y tipos de petición (se ocultará si solo_peticiones_asignadas está en true) -->
          <div id="contenedor_asignaciones_editar" class="row g-3" style="display: none;">
            <!-- Ver peticiones de invitados -->
            <div class="col-12 mt-1">
              <div class="form-check form-switch form-switch-lg mb-2">
                  <input type="checkbox" name="ver_peticiones_de_invitados" id="edit_ver_peticiones_de_invitados" class="form-check-input" value="1">
                  <label class="form-check-label fw-medium text-black" for="edit_ver_peticiones_de_invitados">¿Ver peticiones de invitados?</label>
              </div>
              <small class="form-text text-black fst-italic">Si está activo, el intercesor también verá las peticiones creadas por invitados (personas sin cuenta de usuario) del tipo de petición asignado.</small>
            </div>

            <!-- Sedes -->
            <div class="col-12 mt-1">
              <label for="edit_sedes" class="form-label">Sedes asignadas</label>
              <select id="edit_sedes" name="sedes[]" class="select2 form-select" multiple>
                @foreach($sedes as $sede)
                <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                @endforeach
              </select>
              <small class="form-text text-muted fst-italic">Si no se selecciona ninguna sede, se le asignarán todas.</small>
            </div>

            <!-- tiposPeticion -->
            <div class="col-12">
              <label for="edit_tiposPeticion" class="form-label">Tipos de Petición asignados</label>
              <select id="edit_tiposPeticion" name="tiposPeticion[]" class="select2 form-select" multiple>
                @foreach($tiposPeticion as $tipo)
                <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
              @endforeach
            </select>
            <small class="form-text text-muted fst-italic">Si no se selecciona ningún tipo, se le asignarán todos.</small>
          </div>
        </div>
      </div>

      {{-- Footer con los botones de acción --}}
      <div class="offcanvas-footer border-top p-3 mt-4">
        <button type="submit" class="btn btn-primary waves-effect rounded-pill me-2">Actualizar</button>
        <button type="button" class="btn btn-outline-secondary rounded-pill waves-effect " data-bs-dismiss="offcanvas">Cancelar</button>
      </div>
    </div>
  </form>
@endsection
