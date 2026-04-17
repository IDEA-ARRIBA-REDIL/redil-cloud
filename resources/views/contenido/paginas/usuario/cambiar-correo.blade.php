@extends('layouts/layoutMaster')

@section('title', 'Cambiar Correo Electrónico')

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
@if(session('email_cambiado_exitosamente'))
<script type="module">
  $(function() {
    Swal.fire({
      title: '¡Correo Actualizado!',
      text: 'Tu correo electrónico ha sido actualizado correctamente. Serás redirigido al dashboard en unos segundos.',
      icon: 'success',
      showConfirmButton: true,
      confirmButtonText: 'Ir al Dashboard',
      timer: 5000,
      timerProgressBar: true,
      allowOutsideClick: false,
      allowEscapeKey: false,
      customClass: {
        confirmButton: 'btn btn-primary rounded-pill'
      },
      buttonsStyling: false
    }).then((result) => {
      window.location.href = "{{ route('dashboard') }}";
    });
  });
</script>
@endif
@endsection

@section('content')
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-transparent py-4">
        <h4 class="card-title mb-0 text-primary">
          <i class="ti ti-mail-opened me-2"></i> Cambiar Correo Electrónico
        </h4>
        <p class="text-muted mb-0 small">Procedimiento seguro de actualización de cuenta</p>
      </div>
      
      <div class="card-body">
        @include('layouts.status-msn')

        <!-- 1. Mostrar el correo actual como referencia -->
        <div class="mb-4">
          <label class="form-label text-uppercase fw-bold text-muted small">Correo Actual</label>
          <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="ti ti-mail"></i></span>
            <input type="text" class="form-control bg-light" value="{{ $usuario->email }}" readonly>
          </div>
        </div>

        @if(!$solicitudPendiente)
          <!-- ESTADO: Inicial / Solicitar Código -->
          <form action="{{ route('usuario.solicitarCodigoCorreo') }}" method="POST" id="formSolicitar">
            @csrf
            <div class="mb-4">
              <label class="form-label fw-bold" for="correo_nuevo">Nuevo Correo Electrónico</label>
              <div class="input-group input-group-merge @error('correo_nuevo') is-invalid @enderror">
                <span class="input-group-text"><i class="ti ti-mail-fast text-primary"></i></span>
                <input type="email" name="correo_nuevo" id="correo_nuevo" 
                       class="form-control @error('correo_nuevo') is-invalid @enderror" 
                       placeholder="ejemplo@correo.com" required>
              </div>
              @error('correo_nuevo')
                <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
              <div class="form-text mt-2">
                Enviaremos un código de verificación de 6 dígitos a esta dirección.
              </div>
            </div>

            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary rounded-pill waves-effect waves-light">
                <i class="ti ti-send me-2"></i> Enviar código de verificación
              </button>
            </div>
          </form>
        @else
          <!-- ESTADO: Verificación de Código -->
          <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
            <i class="ti ti-info-circle me-2"></i>
            <div>
              Hemos enviado un código a <strong>{{ $solicitudPendiente->correo_nuevo }}</strong>. Por favor, revísalo para continuar.
            </div>
          </div>

          <form action="{{ route('usuario.verificarCambioCorreo') }}" method="POST">
            @csrf
            <div class="mb-4">
              <label class="form-label fw-bold text-center d-block mb-3" for="codigo">Ingresa el código de 6 dígitos</label>
              <input type="text" name="codigo" id="codigo" 
                     class="form-control form-control-lg text-center fw-bold @error('codigo') is-invalid @enderror" 
                     maxlength="6" placeholder="000000" style="letter-spacing: 0.5rem; font-size: 1.5rem;" required>
              @error('codigo')
                <div class="text-danger text-center mt-2 small">{{ $message }}</div>
              @enderror
            </div>

            <div class="d-grid gap-2 mb-3">
              <button type="submit" class="btn btn-success rounded-pill waves-effect waves-light">
                <i class="ti ti-check me-2"></i> Finalizar y cambiar correo
              </button>
            </div>
          </form>

          <hr class="my-4">

          <div class="text-center">
            <p class="mb-1 small text-muted">¿No recibiste el código o te equivocaste de correo?</p>
            <form action="{{ route('usuario.solicitarCodigoCorreo') }}" method="POST" class="d-inline">
              @csrf
              <input type="hidden" name="correo_nuevo" value="{{ $solicitudPendiente->correo_nuevo }}">
              <button type="submit" class="btn btn-link btn-sm text-primary p-0">Reenviar código</button>
            </form>
            <span class="mx-2">|</span>
            <a href="javascript:void(0);" onclick="window.location.reload();" class="btn btn-link btn-sm text-secondary p-0">Cambiar dirección</a>
          </div>
        @endif
      </div>
      
      <div class="card-footer   border-0 text-center py-3">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-pill small">
          <i class="ti ti-arrow-left me-1"></i> Volver al inicio
        </a>
      </div>
    </div>
  </div>
</div>

<style>
  /* Personalización extra para el diseño premium solicitado */
  .card {
    transition: transform 0.2s ease-in-out;
  }
  .card:hover {
    transform: translateY(-5px);
  }
  .input-group-text {
    background-color: transparent;
  }
  .form-control:focus {
    box-shadow: none;
    border-color: #7367f0;
  }
  .btn-primary {
    background: linear-gradient(135deg, #7367f0 0%, #9e95f5 100%);
    border: none;
  }
</style>
@endsection
