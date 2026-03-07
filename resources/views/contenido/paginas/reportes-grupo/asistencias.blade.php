@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Reporte de grupo')

@section('vendor-style')

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
  @vite([
  'resources/assets/js/form-basic-inputs.js',
  ])


  <script>
    document.addEventListener('DOMContentLoaded', function() {
        var modalLinkAsistencia = new bootstrap.Modal(document.getElementById('modalLinkAsistencia'));

        @if($mostrarLinkAsistencia)
        modalLinkAsistencia.show();
        @endif

    });
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const botonCompartir = document.getElementById('compartirLink');
        const enlaceAsistencia = document.getElementById('linkAsistencia');

        botonCompartir.addEventListener('click', function() {
            const enlaceCompartir = enlaceAsistencia.getAttribute('href');
            const mensaje = encodeURIComponent('Registra la asistencia al grupo aquí: ' + enlaceCompartir);
            const urlWhatsApp = `https://wa.me/?text=${mensaje}`;
            window.open(urlWhatsApp, '_blank');
        });
    });
  </script>

 <script>
    document.addEventListener('DOMContentLoaded', function() {
        const botonCopiar = document.getElementById('copiarLink');
        const enlaceAsistencia = document.getElementById('linkAsistencia');

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

  <div class="col-12 min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center">
      <div class="col-3 text-start">
        <a href="{{ route('grupo.lista') }}" type="button" class="btn rounded-pill waves-effect waves-light text-white prev-step">
          <span class="ti-xs ti ti-arrow-left me-2"></span>
          <span class="d-none d-md-block fw-normal">Volver</span>
        </a>
      </div>
      <div class="col-6 pl-5 text-center">
        <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Finalizar reporte</h5>
      </div>
      <div class="col-3 text-end">
        <a href="{{ route('dashboard')}}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
          <span class="d-none d-md-block fw-normal">Salir</span>
          <span class="ti-xs ti ti-x mx-2"></span>
        </a>
      </div>
    </nav>

    <div class="pt-5 px-7 px-sm-0" style="padding-bottom: 100px;">
      @livewire('ReporteGrupos.asistencias', [
      'reporte' => $reporte,
      ])
    </div>
  </div>


  <!-- Modal link de asistencia -->
  <div class="modal fade" id="modalLinkAsistencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen" role="document">
      <div class="modal-content">
        <div class="modal-body">
          <div class="row">
              <div class="col-12 col-md-6 offset-md-3 d-flex align-items-center">
                  <div class=" mx-auto my-auto text-center">
                      <img src="{{ Storage::url('generales/img/otros/dibujo_respuesta.png') }}" class="img-fluid w-50 p-0">
                      <h2 class="text-black fw-bold mb-0 lh-sm">Link de asistencia</h2>
                      <p class="text-black mt-2 mb-5">
                        Comparte este link de asistencia para tu grupo, el link expira en 1 hora.
                      </p>

                      <div class="row text-start m-0">
                        <div class="p-3 d-flex mb-3" style="color:black; font-size:12px;border: solid 2px #95CDDF;border-radius: 14px;">
                          <i class="ti ti-bulb text-secondary me-2"></i>
                          <p class="m-0"> Recuerda, una vez finalizada la toma de asistencia, debes ingresar en el botón “completa reporte” en el listado de grupos para finalizar el proceso.</p>
                        </div>
                      </div>

                      <div class="d-flex justify-content-center mb-5 mt-1">

                        <button id="copiarLink" type="button" class="btn btn-outline-secondary waves-effect me-2" >
                           <span class="align-middle">Copiar link <i class="ti ti-copy"></i></span>
                         </button>
                          <button id="compartirLink" type="button" class="btn btn-outline-secondary waves-effect" >
                           <span class="align-middle">Compartir link <i class="ti ti-share"></i></span>
                         </button>
                      </div>

                      <div class="d-flex justify-content-center mb-5">
                        <a id="linkAsistencia" href="{{ route('reporteGrupo.miAsistencia', $reporte->id) }}" class=" text-center">{{ route('reporteGrupo.miAsistencia', $reporte->id) }}</a>
                      </div>

                      <div class="d-grid gap-2 d-sm-flex justify-content-center">
                        <a href="{{ route('grupo.lista') }}" type="button" class="btn btn-primary rounded-pill px-10 py-3">
                          <span class="align-middle me-sm-1 me-0 px-10">Salir</span>
                        </a>
                        <button type="button" class="btn btn-primary rounded-pill px-10 py-3" data-bs-dismiss="modal">
                          <span class="align-middle me-sm-1 me-0 px-10">Continuar</span>
                        </button>
                      </div>

                  </div>
              </div>
          </div>

        </div>
      </div>
    </div>
  </div>

@endsection
