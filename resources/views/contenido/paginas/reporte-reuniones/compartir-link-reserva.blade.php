@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp


@extends('layouts/blankLayout')

@section('title', 'Mi asistencia')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
])
@endsection


@section('page-script')

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const botonCompartir = document.getElementById('compartirLink');
        const enlaceAsistencia = document.getElementById('linkParaCompartir');

        botonCompartir.addEventListener('click', function() {
            const enlaceCompartir = enlaceAsistencia.getAttribute('href');
            const mensaje = encodeURIComponent('Has la reserva a la reunión {{ $reporteReunion->reunion->nombre }} ( Fecha: {{ $reporteReunion->fecha }} Hora: {{ Carbon\Carbon::parse($reporteReunion->hora)->format("g:i a") }} ) : ' + enlaceCompartir);
            const urlWhatsApp = `https://wa.me/?text=${mensaje}`;
            window.open(urlWhatsApp, '_blank');
        });
    });
  </script>

   <script>
      document.addEventListener('DOMContentLoaded', function() {
          const botonCopiar = document.getElementById('copiarLink');
          const enlaceAsistencia = document.getElementById('linkParaCompartir');

          botonCopiar.addEventListener('click', function() {
              const enlaceACopiar = enlaceAsistencia.getAttribute('href');

              navigator.clipboard.writeText(enlaceACopiar)
                  .then(() => {
                      Swal.fire({
                          icon: 'success',
                          title: '¡Copiado!', // Un título más corto
                          text: 'El link se ha copiado al portapapeles.',
                          timer: 1500, // Opcional: para que se cierre automáticamente
                          showConfirmButton: false // Opcional: para no mostrar el botón de "OK"
                      });
                  })
                  .catch(err => {
                      console.error('Error al copiar el enlace: ', err);
                      Swal.fire({
                          icon: 'error',
                          title: '¡Error!',
                          text: 'No se pudo copiar el link al portapapeles.',
                      });
                  });
          });
      });
  </script>


@endsection

@section('content')

<div class="d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row">
            <div class="col-12 col-lg-12 d-flex align-items-center">
                <div class=" mx-auto my-auto text-center">

                  <img src="{{ Storage::url('generales/img/otros/dibujo_respuesta.png') }}" class="img-fluid w-50 p-0">

                  <h2 class="text-black fw-bold mb-0 lh-sm">Link para reservar</h2>
                  <p class="text-black mt-2 mb-5">
                    Comparte este link para reservar tu asistencia a la reunión:
                    <br>
                    <br><b>{{ $reporteReunion->reunion->nombre }}</b><br> <b> Fecha: </b> {{ $reporteReunion->fecha }} <b> Hora:</b> {{ Carbon\Carbon::parse($reporteReunion->hora)->format('g:i a') }}.
                  </p>

                  <div class="d-flex justify-content-center mb-5 mt-1">
                    <button id="copiarLink" type="button" class="btn btn-outline-secondary waves-effect me-2" >
                      <span class="align-middle">Copiar link <i class="ti ti-copy"></i></span>
                    </button>
                    <button id="compartirLink" type="button" class="btn btn-outline-secondary waves-effect" >
                      <span class="align-middle">Compartir link <i class="ti ti-share"></i></span>
                    </button>
                  </div>

                  <div class="d-flex justify-content-center mb-5">
                    <a id="linkParaCompartir" href="{{ route('reporteReunion.miReserva', $reporteReunion->id) }}" class=" text-center">{{ route('reporteReunion.miReserva', $reporteReunion->id) }}</a>
                  </div>

                  <div class="d-grid gap-2 d-sm-flex justify-content-center">
                    <a href="{{ url()->previous() }}" type="button" class="btn btn-primary rounded-pill px-10 py-3">
                      <span class="align-middle me-sm-1 me-0 px-10">Atras</span>
                    </a>
                  </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
