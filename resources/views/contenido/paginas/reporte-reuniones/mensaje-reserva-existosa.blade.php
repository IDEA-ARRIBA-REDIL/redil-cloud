@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp


@extends('layouts/blankLayout')

@section('title', 'Reserva éxitosa')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection


@section('page-script')
<script>
  function confirmarEliminacion(reservaId, nombreAsistente) {
    Swal.fire({
      title: '¿Estás seguro?',
      html: `Se eliminará la reserva de <strong>${nombreAsistente}</strong>.<br>Esta acción no se puede deshacer.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      customClass: {
        confirmButton: 'btn btn-danger me-2',
        cancelButton: 'btn btn-secondary'
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
        // Si el usuario confirma, se envía el formulario de eliminación
        const form = document.getElementById('formEliminarReserva');
        // Construimos la URL de la acción dinámicamente
        form.action = `/reserva-reunion/${reservaId}/eliminar`;
        form.submit();
      }
    });
  }
</script>
@endsection

@section('content')

<div class="d-flex align-items-center min-vh-100">
  <div class="container">
    <div class="container my-5" style="padding-bottom: 100px;">
      <div class="col-12 col-sm-8 offset-sm-2 col-lg-8 offset-lg-2">
          <div class=" mx-auto my-auto text-center">
            <img src="{{ Storage::url('generales/img/otros/tiempo_con_Dios_respuesta.png') }}" class="img-fluid w-25 p-0">
            <h2 class="text-black fw-bold mb-0 lh-sm mt-3">Mis reserva</h2>
            <p class="text-black mt-1 mb-10">
              ¡Muy bien!, aquí podrás gestionar las reservas que has realizado en esta reunión.
          </div>
          @include('layouts.status-msn')
          <!-- CARD DE INFORMACIÓN DE LA REUNIÓN -->
          <div class="card mb-5 shadow-sm" style="background-color: #f8f7fa">
              <div class="card-header pb-1">
                  <h6 class="card-title mb-0 fw-semibold">Información de la reunión</h6>
              </div>
              <div class="card-body">
                  <h4 class="fw-bold text-black">{{ $reunion->nombre }}</h4>
                  <hr>
                  <div class="row">
                      <div class="col-12 col-md-5 mb-3 mb-md-0">
                          <small class="">Fecha y hora</small>
                          <p class="fw-semibold text-black mb-0">{{ Carbon\Carbon::parse($reporte->fecha)->translatedFormat('d F, Y') }}</p>
                      </div>
                      <div class="col-12 col-md-5 mb-3">
                          <small class="">Lugar</small>
                          <p class="fw-semibold text-black mb-0">{{ $reunion->sede->nombre }}</p>
                      </div>

                      <div class="col-12 col-md-2">
                      </div>
                  </div>
              </div>
          </div>

          @if($user)
            <div class="card mb-5 shadow-sm" style="background-color: #f8f7fa">
              @foreach ($reservas->where('user_id', $user->id) as $reserva)
              <div class="card-header pb-1">
                  <h6 class="card-title mb-0 fw-semibold  text-black">Mi reserva</h6>
              </div>
              <div class="card-body">
                  <div class="row">
                    <div class="col-12 col-md-5 mb-3 mb-md-0">
                        <small class="">Nombre</small>
                        <p class="fw-semibold text-black mb-0">{{ $reserva->usuario->nombre(3) }}</p>

                    </div>
                    <div class="col-12 col-md-5 mb-3">
                        <small class="">Email</small>
                        <p class="fw-semibold text-black mb-0">{{ $reserva->usuario->email ?? 'No definido' }}</p>
                    </div>

                    <div class="col-12 col-md-2 flex-column">
                      <a href="{{ route('reporteReunion.descargarQrReserva', $reserva) }}" target="_blank"  class="btn btn-xs btn-text-info fw-semibold float-end mb-1 p-0">  Qr reserva <i class="ms-1 ti ti-qrcode"></i> </a>
                      <button onclick="confirmarEliminacion({{ $reserva->id }}, '{{ addslashes($reserva->usuario->nombre(3)) }}')" type="button" class="btn btn-xs btn-text-danger fw-semibold float-end mb-1 p-0">  Eliminar <i class="ms-1 ti ti-trash"></i> </button>
                    </div>

                    @if(!$loop->last)
                      <hr>
                    @endif
                  </div>
              </div>
              @endforeach
            </div>


            @if($reservas->where('user_id', '!=', $user->id)->whereNotNull('user_id')->count() > 0)
            <div class="card mb-5 shadow-sm" style="background-color: #f8f7fa">
              <div class="card-header pb-1">
                <h6 class="card-title mb-0 fw-semibold  text-black">Familia</h6>
              </div>
              <div class="card-body">
                @foreach ($reservas->where('user_id', '!=', $user->id)->whereNotNull('user_id') as $reserva)
                  <div class="row">
                      <div class="col-12 col-md-5 mb-3 mb-md-0">
                          <small class="">Nombre</small>
                          <p class="fw-semibold text-black mb-0">{{ $reserva->usuario->nombre(3) }}</p>
                          @if(!$loop->last)
                            <hr>
                          @endif
                      </div>
                      <div class="col-12 col-md-5">
                          <small class="">Email</small>
                          <p class="fw-semibold text-black mb-0">{{ $reserva->usuario->email ?? 'No definido' }}</p>
                          @if(!$loop->last)
                            <hr>
                          @endif
                      </div>

                      <div class="col-12 col-md-2 flex-column">

                            <a href="{{ route('reporteReunion.descargarQrReserva', $reserva) }}" target="_blank"  class="btn btn-xs btn-text-info fw-semibold float-end mb-1 p-0">  Qr reserva <i class="ms-1 ti ti-qrcode"></i> </a>
                            <button onclick="confirmarEliminacion({{ $reserva->id }}, '{{ addslashes($reserva->usuario->nombre(3)) }}')" type="button" class="btn btn-xs btn-text-danger fw-semibold float-end mb-1 p-0">  Eliminar <i class="ms-1 ti ti-trash"></i> </button>

                          @if(!$loop->last)
                            <hr>
                          @endif
                      </div>
                  </div>
                @endforeach
              </div>
            </div>
            @endif
          @endif

          @if($reservas->where('invitado')->count() > 0)
          <div class="card mb-5 shadow-sm" style="background-color: #f8f7fa">
            <div class="card-header pb-1">
                <h6 class="card-title mb-0 fw-semibold text-black">Invitados</h6>
            </div>
            <div class="card-body">
              @foreach ($reservas->where('invitado') as $reserva)
              <div class="row">
                <div class="col-12 col-md-5 mb-3 mb-md-0">
                    <small class="">Nombre</small>
                    <p class="fw-semibold text-black mb-0">{{ $reserva->nombre_invitado }}</p>
                </div>
                <div class="col-12 col-md-5 mb-3 ">
                    <small class="">Email</small>
                    <p class="fw-semibold text-black mb-0">{{ $reserva->email_invitado ?? 'No definido' }}</p>
                </div>

                <div class="col-12 col-md-2 flex-column">
                  <a href="{{ route('reporteReunion.descargarQrReserva', $reserva) }}" target="_blank"  class="btn btn-xs btn-text-info fw-semibold float-end mb-1 p-0">  Qr reserva <i class="ms-1 ti ti-qrcode"></i> </a>
                  <button onclick="confirmarEliminacion({{ $reserva->id }}, '{{ addslashes($reserva->nombre_invitado) }}')" type="button" class="btn btn-xs btn-text-danger fw-semibold float-end mb-1 p-0">  Eliminar <i class="ms-1 ti ti-trash"></i> </button>
                </div>

                @if(!$loop->last)
                  <hr class="my-2">
                @endif

              </div>
              @endforeach
            </div>
          </div>
          @endif

          <div class=" mx-auto my-auto text-center">
            <div class="d-grid gap-2 d-sm-flex justify-content-center pt-8">
              <a href="{{ $origen == 'añadirReservas' ? route('reporteReunion.añadirReservas', [$reporte->id]) : route('reporteReunion.'.$origen ) }}" type="button" class="btn btn-primary rounded-pill px-10 py-3" >
                <span class="align-middle me-sm-1 me-0 ">Salir</span>
              </a>
            </div>
          </div>
      </div>
    </div>
  </div>
</div>


{{-- ✅ FORMULARIO OCULTO PARA LA ELIMINACIÓN --}}
<form id="formEliminarReserva" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
    <input type="hidden" name="origen" id="origenParaEliminar" value="{{ $origen }}">
</form>

@endsection
