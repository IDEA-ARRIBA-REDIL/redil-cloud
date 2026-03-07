@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Mis citas')

<!-- Page Styles -->
@section('page-style')
@vite([
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/swiper/swiper.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js',
'resources/assets/vendor/libs/swiper/swiper.js'
])
@endsection

@section('page-script')
<script type="module">
  const swiperContainer = document.querySelector('#swiper-with-pagination-cards');
  const swiper = new Swiper(swiperContainer, {
    slidesPerView: "auto",
    spaceBetween: 30,
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
  });
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
@endsection

@section('content')
<h4 class=" mb-1 fw-semibold text-primary">Mis citas</h4>


    @include('layouts.status-msn')

    <!-- Swiper Indicators -->
    <div class="row pt-5">
      <div class="swiper-container swiper-container-horizontal swiper swiper-card-advance-bg" id="swiper-with-pagination-cards">
          <div class="swiper-wrapper">
          <!-- Cards with few info -->
          @foreach( $indicadoresGenerales->chunk(4) as $chunk )
          <div class="swiper-slide">
              <div class="row equal-height-row  g-2">
              @foreach($chunk as $indicador )
              <div class="col equal-height-col col-lg-3 col-12">
                  <a href="{{ route('consejeria.misCitas', $indicador->url) }}">
                  <div class="card border rounded-3 shadow-sm" style="border-bottom: 10px solid {{ $indicador->color}} !important; ">
                      <div class="card-body d-flex flex-row align-items-center p-3">

                      <div class="card-icon me-2">
                        <span class="badge bg-label-primary p-2 rounded-pill">
                          <i class="{{ $indicador->icono }} ti-sm"></i>
                        </span>
                      </div>

                      <div class="card-title mb-0">
                          <p class="text-black mb-0" style="font-size: .8125rem">{{ $indicador->nombre }}</p>
                          <h5 class="mb-0 me-2">{{ $indicador->cantidad }} </h5>
                      </div>

                      </div>
                  </div>
                  </a>
              </div>
              @endforeach
              </div>
          </div>
          @endforeach
          <!--/ Cards with few info -->
          </div>
          <div class="d-flex mt-10">
          <div class="swiper-pagination"></div>
          </div>
      </div>
    </div>
    <!-- /Swiper Indicators -->

    <hr class="my-4">

    <!-- Listado de citas -->
    <div class="row equal-height-row g-4 mt-1">
        @if(count($citas)>0)
        @foreach($citas as $cita)
        <div class="col equal-height-col col-12 col-md-6" id="cita-card-{{ $cita->id }}">
          <div class="card rounded-3 shadow">
              <div class="card-header border-bottom d-flex px-4 pt-3 pb-1" style="background-color:#F9F9F9!important">
                <div class="flex-fill row">
                    <div class=" d-flex justify-content-between align-items-center">
                    <h5 class="fw-semibold ms-1 text-black m-0">
                        {{ $cita->tipoConsejeria->nombre ?? 'Cita General' }}
                    </h5>
                    @php
                        $esPasada = $cita->fecha_hora_fin < now();
                        $esCancelada = $cita->trashed();
                        
                        if ($esCancelada) {
                            $badgeColor = 'danger';
                            $badgeText = 'Cancelada';
                            $badgeIcon = 'ti-circle-x';
                        } elseif ($esPasada) {
                            $badgeColor = 'secondary';
                            $badgeText = 'Finalizada';
                            $badgeIcon = 'ti-check';
                        } else {
                            $badgeColor = 'primary';
                            $badgeText = 'Programada';
                            $badgeIcon = 'ti-calendar';
                        }
                    @endphp
                    <span class="badge rounded-pill fw-light me-1 bg-{{ $badgeColor }}">
                        <i class="ti {{ $badgeIcon }} fs-6 me-1"></i> <span class="text-white"> {{ $badgeText }} </span>
                    </span>
                    </div>
                </div>

                <div class="">
                  <div class="ms-auto">
                    <div class="dropdown zindex-2 p-1 float-end">
                        <button type="button" class="btn btn-sm rounded-pill btn-icon btn-outline-secondary waves-effect" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i> </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                        @if(!$esPasada && !$esCancelada)
                            @if($rolActivo->hasPermissionTo('consejeria.opcion_reprogramar_cita'))
                            <li>
                                <a class="dropdown-item" href="{{ route('consejeria.reprogramarCita', ['cita' => $cita->id, 'origen' => request()->fullUrl()]) }}">
                                    Reprogramar
                                </a>
                            </li>
                            @endif
                            @if($rolActivo->hasPermissionTo('consejeria.opcion_cancelar_cita'))
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#modalCancelarCita{{ $cita->id }}">
                                    <i class="ti ti-trash me-1"></i> Cancelar cita
                                </button>
                            </li>
                            @endif 
                        @endif
                        </ul>
                    </div>
                  </div>
                </div>
              </div>

          

        
              <div class="card-body">
                <div class="row mt-4">
                    <div class="col-6 d-flex flex-column">
                    <small class="text-black"><i class="ti ti-calendar me-1"></i>Fecha:</small>
                    <small class="fw-semibold text-black ">{{ $cita->fecha_hora_inicio->format('d-m-Y') }}</small>
                    </div>

                    <div class="col-6 d-flex flex-column">
                    <small class="text-black"><i class="ti ti-clock me-1"></i>Hora:</small>
                    <small class="fw-semibold text-black ">{{ $cita->fecha_hora_inicio->format('h:i A') }} - {{ $cita->fecha_hora_fin->format('h:i A') }}</small>
                    </div>

                    <div class="col-12">
                    <hr class="my-3 border-1">
                    </div>

                    <div class="col-6 d-flex flex-column mt-1">
                    <small class="text-black">Consejero:</small>
                    <small class="fw-semibold text-black ">{{ $cita->consejero->usuario->nombre(3) ?? 'No asignado' }}</small>
                    </div>

        

                    <div class="col-6 d-flex flex-column mt-1">
                    <small class="text-black">Medio:</small>
                    <small class="fw-semibold text-black ">{{ $cita->medio == 1 ? 'Presencial' : 'Virtual' }}</small>
                    </div>
                    @if($cita->medio == 1)
                    <div class="col-12 d-flex flex-column mt-1">
                    <small class="text-black">Dirección:</small>
                    <small class="fw-semibold text-black ">{{ $cita->consejero->direccion ?? 'No asignado' }}</small>
                    </div>
                    @endif
                    @if($cita->medio == 2)
                    <div class="col-6 d-flex flex-column mt-1">
                    <small class="text-black">Enlace virtual:</small>
                    @if($cita->enlace_virtual)
                      <a href="{{ $cita->enlace_virtual }}" target="_blank" class="small fw-semibold text-primary">Unirse a la reunión</a>
                    @else
                      <small class="fw-semibold text-black ">No asignado</small>
                    @endif
                    </div>
                    @endif

                </div>

                <div class="collapse" id="cardBodyCita{{ $cita->id }}">
                    <div class="col-12">
                    <hr class="my-3 border-1">
                    </div>
                    <h6 class="fw-bold text-black mb-1">Notas</h6>         
                    <small class="text-black ">{{ $cita->notas_paciente ?? 'No indicado' }}</small>

                    @if($cita->trashed() && $cita->notas_cancelacion)
                    <div class="col-12">
                    <hr class="my-3 border-1">
                    </div>
                    <h6 class="fw-bold text-danger mb-1">Motivo de cancelación</h6>         
                    <small class="text-danger">{{ $cita->notas_cancelacion }}</small>
                    @endif
                </div>
              </div>

              <div class="card-footer border-top p-1">
                <div class="d-flex justify-content-center">
                    <button type="button"
                    class="btn btn-xs rounded-pill btn-icon btn-outline-secondary waves-effect my-2"
                    data-bs-toggle="collapse"
                    data-bs-target="#cardBodyCita{{ $cita->id }}">
                    <span class="ti ti-plus"></span>
                    </button>
                </div>
              </div>

          </div>
        </div>

        <!-- Modal Cancelar Cita -->
        <div class="modal fade" id="modalCancelarCita{{ $cita->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCenterTitle">Cancelar Cita</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('consejeria.cancelarCita', $cita->id) }}" method="POST">
                        @csrf

                        <div class="modal-body">
                            <div class="row">
                                <div class="col mb-3">
                                    <p>¿Estás seguro de que deseas cancelar esta cita?</p>
                                    <label for="notas_cancelacion" class="form-label">Motivo de la cancelación (Opcional)</label>
                                    <textarea name="notas_cancelacion" id="notas_cancelacion" class="form-control" rows="3" placeholder="Escribe aquí el motivo..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-danger">Cancelar Cita</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
        @else
        <div class="mt-5 mb-5 py-5">
          <center>
              <i class="ti ti-calendar-off ti-xl text-black mb-3"></i>
              <p class="text-black">No hay citas {{ $tipo == 'pasadas' ? 'pasadas' : 'programadas' }} para mostrar.</p>
              @if($tipo != 'proximas')
                <a href="{{ route('consejeria.misCitas', 'proximas') }}" class="btn btn-sm btn-outline-primary rounded-pill">Ver próximas citas</a>
              @endif
          </center>
        </div>
        @endif
    </div>
    <!--/ Listado de citas -->

  <div class="row my-3">
    @if($citas)
    <p> {{$citas->lastItem()}} <b>de</b> {{$citas->total()}} <b>citas - Página</b> {{ $citas->currentPage() }} </p>
    {!! $citas->appends(request()->input())->links() !!}
    @endif
  </div>

@endsection
