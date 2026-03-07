@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Reporte de grupo')

@section('page-style')
<style>
  body {
    overflow-x: hidden;
  }
</style>
@endsection

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('page-script')
@vite(['resources/assets/js/form-basic-inputs.js'])

<script>
  // Función auxiliar (sin cambios)
  function sinComillas(e) {
    tecla = (document.all) ? e.keyCode : e.which;
    patron = /[\x5C'"]/;
    te = String.fromCharCode(tecla);
    return !patron.test(te);
  }

  $(document).ready(function() {

      // --- NUEVA FUNCIÓN PARA HABILITAR/DESHABILITAR INPUTS ---
      function actualizarVisibilidadBotonInvitado() {
        // Obtenemos el límite desde el data-attribute que pusimos en el HTML
        const limiteInvitados = parseInt($('#listaDeInvitados').data('limite-invitados')) || 0;
        // Contamos cuántos bloques de invitados hay actualmente en la página
        const invitadosActuales = $('#contenedor-invitados .invitado-bloque').length;

        if (invitadosActuales < limiteInvitados) {
          // Si hay espacio, muestra el botón
          $('#btnAgregarInvitado').show();
        } else {
          // Si se alcanzó el límite, oculta el botón
          $('#btnAgregarInvitado').hide();
        }
      }

      // --- 1. RESTAURAR VISIBILIDAD Y ESTADO AL CARGAR LA PÁGINA ---
      setTimeout(function() {
        if ($('#toggleFamilia').is(':checked')) { $('#listaDeFamiliares').show(); }

        if ($('#toggleInvitados').is(':checked')) {
          $('#listaDeInvitados').show();
          actualizarVisibilidadBotonInvitado();
        }
      }, 10);


      // --- 2. COMPORTAMIENTO DINÁMICO AL HACER CLIC ---
      $('#toggleFamilia').on('change', function() {
        if ($(this).is(':checked')) { $('#listaDeFamiliares').slideDown(); }
        else { $('#listaDeFamiliares').slideUp(); }
      });

      $('#toggleInvitados').on('change', function() {
        // Mostramos u ocultamos el card
        $('#listaDeInvitados').slideToggle($(this).is(':checked'));
        // Y actualizamos el estado de los inputs
        actualizarVisibilidadBotonInvitado();
      });


      // --- 3. LÓGICA PARA AGREGAR/QUITAR INVITADOS ---
      let invitadoCount = parseInt($('#contenedor-invitados').data('last-index'))

      $('#btnAgregarInvitado').on('click', function() {
        invitadoCount++;
        const nuevoInvitadoHtml = `
            <div class="invitado-bloque row mb-4" data-index="${invitadoCount}" style="display:none;">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-title fw-semibold mb-3">Información invitado ${invitadoCount}</h6>
                    <button type="button" class="btn-close btn-sm btn-remover-invitado" aria-label="Close"></button>
                </div>
                <div class="col-12 col-lg-6 mb-3">
                    <label class="form-label">Nombre</label>
                    <input name="invitados[${invitadoCount}][nombre]" placeholder="Ingrese el nombre" type="text" class="form-control"/>
                </div>
                <div class="col-12 col-lg-6 mb-3">
                    <label class="form-label">Email</label>
                    <input name="invitados[${invitadoCount}][email]" placeholder="Ingrese el email" type="email" class="form-control"/>
                </div>
            </div>
        `;
        const nuevoElemento = $(nuevoInvitadoHtml);
        $('#contenedor-invitados').append(nuevoElemento);
        nuevoElemento.slideDown(function() {
            actualizarVisibilidadBotonInvitado(); // Llamamos a la función después de agregar
        });
      });

      $('#contenedor-invitados').on('click', '.btn-remover-invitado', function() {
        $(this).closest('.invitado-bloque').slideUp(function() {
          $(this).remove();
          actualizarVisibilidadBotonInvitado(); // Llamamos a la función después de eliminar
        });
      });

      // Llamada inicial por si acaso no hay 'old'
      actualizarVisibilidadBotonInvitado();
    });

    $('#formulario').submit(function() {
      $('.btnGuardar').attr('disabled', 'disabled');

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

  <div class="min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center">
      <div class="col-3 text-start">
        <a href="{{ route('grupo.lista') }}" type="button" class="d-none btn rounded-pill waves-effect waves-light text-white prev-step">
          <span class="ti-xs ti ti-arrow-left me-2"></span>
          <span class="d-none d-md-block fw-normal">Volver</span>
        </a>
      </div>
      <div class="col-6 pl-5 text-center">
        <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Reservas</h5>
      </div>
      <div class="col-3 text-end">
        <a href="{{ route('reporteReunion.iglesiaVirtual') }}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
          <span class="d-none d-md-block fw-normal">Salir</span>
          <span class="ti-xs ti ti-x mx-2"></span>
        </a>
      </div>
    </nav>



    <div class="container my-5" style="padding-bottom: 100px;">
      <div class="col-12 col-sm-8 offset-sm-2 col-lg-8 offset-lg-2">
        <form id="formulario" role="form" class="forms-sample" method="POST" action="{{ route('reporteReunion.hacerMiReserva', ['reporteReunion' => $reporte, 'user' => $usuario]) }}" enctype="multipart/form-data">
          @csrf
            @include('layouts.status-msn')
            <input type="hidden" name="origen" value="{{ $origen }}">

            <h4 class="fw-semibold text-black ps-0 mb-5">Realiza tu reserva</h4>

              @if($usuarioYaTieneReserva)

                  <div class="text-center my-3">
                  <div class="p-3 d-flex mb-3" style="color:black; font-size:12px;border: solid 2px #95CDDF;border-radius: 14px;">
                    <i class="ti ti-bulb text-secondary me-2"></i>
                    <p class="m-0 text-start"><strong>¡Ya tienes una reserva confirmada!</strong><br> Aún puedes reservar para familiares o invitados si hay cupos disponibles.</p>
                  </div>
                </div>
              @elseif ($usuarioCumpleRequisitos)
                  <input type="hidden" name="toggleMiAsistencia" value="1">
              @endif

            <!-- CARD DE INFORMACIÓN DE LA REUNIÓN -->
            <div class="card mb-5 shadow-sm" style="background-color: #f8f7fa">
                <div class="card-header pb-1">
                    <h6 class="card-title mb-0 fw-semibold">Información de la reunión</h6>
                </div>
                <div class="card-body">
                    <h4 class="fw-bold text-black">{{ $reunion->nombre }}</h4>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <small class="text-black">Fecha y hora</small>
                            <p class="fw-semibold text-black mb-0">{{ Carbon\Carbon::parse($reporte->fecha)->translatedFormat('d F, Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-black">Lugar</small>
                            <p class="fw-semibold text-black mb-0">{{ $reunion->sede->nombre }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($reporte->habilitar_reserva_familiares)

              <div class="mb-5">
                <div class="small form-label text-black">¿Deseas añadir la reserva a uno de tus familiares?</div>
                <label class="switch switch-lg">
                  {{-- CORRECCIÓN 3: Input oculto para switch de familia --}}
                  <input type="hidden" name="toggleFamilia" value="0">
                  <input type="checkbox" value="1" @checked(old('toggleFamilia')) class="switch-input" id="toggleFamilia" name="toggleFamilia" />
                  <span class="switch-toggle-slider">
                      <span class="switch-on">Si</span>
                      <span class="switch-off">No</span>
                  </span>
                </label>
              </div>

              <!-- CARD DE FAMILIARES -->
              @if($familiares->count()>0)
              <div id="listaDeFamiliares" class="card mb-5 shadow-sm"  style="display: none;background-color: #f8f7fa">
                  <div class="card-body">
                    @foreach ($familiares as $persona)
                      <li class="d-flex flex-wrap mb-4 border-bottom pb-3">
                          <div class="avatar me-4">
                              @if($persona->foto == 'default-m.png' || $persona->foto == 'default-f.png')
                                  <span class="avatar-initial rounded-circle border border-3 border-white bg-info">
                                      {{-- Asumimos que el modelo User tiene este método --}}
                                      {{ substr($persona->primer_nombre, 0, 1) . substr($persona->primer_apellido, 0, 1) }}
                                  </span>
                              @elseif($persona->foto)
                                  <img src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $persona->foto) }}"
                                      alt="avatar" class="rounded-circle">
                              @endif
                          </div>
                          <div class="d-flex justify-content-between flex-grow-1">
                              <div class="me-2">
                                  <p class="mb-0 text-heading text-black fw-semibold">
                                      {{ $persona->nombre(3) }}
                                  </p>
                                  <p class="small mb-0">
                                    <i class="ti ti-family fs-6"></i>
                                    {{ $persona->genero ? $persona->nombre_femenino : $persona->nombre_masculino  }}
                                  </p>
                              </div>
                              <div class="">
                                  @if(in_array($persona->id, $usuariosYaReservadosIds))
                                    <span class="badge rounded-pill fw-light text-bg-success">
                                      <i class="ti ti-check"></i>
                                      <span class="text-white"> Ya tiene reserva </span>
                                    </span>
                                  @else
                                    <label class="switch switch-lg mx-auto">
                                        <input type="checkbox" class="switch-input" name="familiares[]" value="{{ $persona->id }}"
                                              @checked(is_array(old('familiares')) && in_array($persona->id, old('familiares')))>
                                        <span class="switch-toggle-slider">
                                            <span class="switch-on">SI</span>
                                            <span class="switch-off">NO</span>
                                        </span>
                                        <span class="switch-label"></span>
                                    </label>
                                  @endif
                              </div>
                          </div>
                      </li>
                    @endforeach
                  </div>
              </div>
              @else
                <div id="listaDeFamiliares" style="display: none;" class="text-center my-3">
                  <div class="p-3 d-flex mb-3" style="color:black; font-size:12px;border: solid 2px #95CDDF;border-radius: 14px;">
                    <i class="ti ti-bulb text-secondary me-2"></i>
                    <p class="m-0 text-start"><strong>¡Ups! </strong><br>  Al parecer no tienes familiares asociados o que cumplan con los requisitos de la reunión.</p>
                  </div>
                </div>
              @endif

            @endif

            @if($reporte->habilitar_reserva_invitados)
              <div class="mb-5">
                  <div class="small form-label text-black">¿Reservar a invitados?</div>
                  <label class="switch switch-lg">
                      <input type="hidden" name="toggleInvitados" value="0">
                      <input type="checkbox" value="1" @checked(old('toggleInvitados')) class="switch-input" id="toggleInvitados" name="toggleInvitados" />
                      <span class="switch-toggle-slider">
                          <span class="switch-on">Si</span>
                          <span class="switch-off">No</span>
                      </span>
                  </label>
              </div>

              @if($invitadosDisponibles > 0 )
              <!-- CARD DE INVITADOS -->
              <div id="listaDeInvitados" class="card mb-5 shadow-sm" style="display: none; background-color: #f8f7fa" data-limite-invitados="{{ $invitadosDisponibles }}">
                <div class="card-body">

                  <div id="contenedor-invitados" data-last-index="{{ collect(old('invitados'))->keys()->last() ?? 1 }}">
                    @foreach(old('invitados', [1 => ['nombre' => null, 'email' => null]]) as $index => $invitado)
                      <div class="invitado-bloque row mb-4" data-index="{{ $index }}">
                        <div class="d-flex justify-content-between align-items-center">
                          <h6 class="card-title fw-semibold mb-3">Información invitado {{ $loop->iteration }}</h6>
                          {{-- El primer invitado no se puede eliminar --}}
                          @if(!$loop->first)
                            <button type="button" class="btn-close btn-sm btn-remover-invitado" aria-label="Close"></button>
                          @endif
                        </div>
                        <div class="col-12 col-lg-6 mb-3">
                          <label class="form-label">Nombre</label>
                          <input name="invitados[{{ $index }}][nombre]" value="{{ $invitado['nombre'] ?? '' }}" placeholder="Ingrese el nombre" type="text" class="form-control @error('invitados.'.$index.'.nombre') is-invalid @enderror"/>
                          @error('invitados.'.$index.'.nombre') <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-lg-6 mb-3">
                          <label class="form-label">Email</label>
                          <input name="invitados[{{ $index }}][email]" value="{{ $invitado['email'] ?? '' }}" placeholder="Ingrese el email" type="email" class="form-control @error('invitados.'.$index.'.email') is-invalid @enderror"/>
                          @error('invitados.'.$index.'.email') <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div> @enderror
                        </div>
                      </div>

                    @endforeach
                  </div>
                  <a id="btnAgregarInvitado" href="javascript:void(0);" class="fw-semibold float-end">Agregar otro invitado <i class="ti ti-circle-plus"></i></a>
                </div>
              </div>
              @else
                <div id="listaDeInvitados" style="display: none;" class="text-center my-3">
                  <div class="p-3 d-flex mb-3" style="color:black; font-size:12px;border: solid 2px #95CDDF;border-radius: 14px;">
                    <i class="ti ti-bulb text-secondary me-2"></i>
                    <p class="m-0 text-start"><strong>¡Ups! </strong><br>  Al parecer llegaste al máximo de invitados permitidos</p>
                  </div>
                </div>
              @endif
            @endif

            <div class="w-100 fixed-bottom py-5 px-6 px-sm-0 border-top" style="background-color: #f8f7fa">
              <div class="col-12 col-sm-8 offset-sm-2 col-lg-6 offset-lg-3 d-grid gap-2 d-sm-flex justify-content-sm-end">

                <button type="submit" class="btn btnGuardar btn-primary rounded-pill px-7 py-2" >
                  <span class="align-middle me-sm-1 me-0 "> Reservar </span>
                </button>
              </div>
            </div>

        </form>
      </div>
    </div>
  </div>



@endsection
