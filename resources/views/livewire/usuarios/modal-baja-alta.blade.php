<div>
    <!-- Formulario baja alta-->
    <div wire:ignore.self class="modal fade" id="modaBajaAlta" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-simple modal-edit-user">
        <div class="modal-content">
          <div class="modal-body">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="text-center mb-4">
              <h5 class="mb-2 fw-semibold text-black">{!! $titulo !!}</h5>
            </div>
            <form wire:submit="editarBajaAlta({{ $usuarioId }}, '{{$tipo}}')" class="row g-3">
              <!-- motivo -->
              <div class="mb-2 col-12 col-md-12">
                <label class="form-label text-black" for="motivo">
                  <span class="badge badge-dot bg-info me-1"></span>                  
                  Motivo <br> @error('motivo') <span class="error text-danger">{{ $message }}</span> @enderror
                </label>
                <select wire:model="motivo" id="motivo" name="motivo" class="form-select">
                  <option value="">Selecciona un motivo</option>
                  @foreach ($motivosBajasAltas as $m)
                  <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                  @endforeach
                </select>
              </div>
              <!-- /motivo -->

              <!-- Observacion -->
              <div class="mb-2 col-12 col-md-12">
                <label class="form-label text-black">
                  Observaciones
                </label>
                <textarea wire:model="observacion" class="form-control" rows="2" maxlength="500" spellcheck="false" placeholder="Detalla aquí las observaciones adicionales."></textarea>
              </div>
              <!--/Observacion-->

              <div class="col-12 text-center">
                <button type="button" class="btn btn-outline-secondary rounded-pill me-2" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-pill">
                  <span wire:loading.remove wire:target="editarBajaAlta">Guardar</span>
                  <span wire:loading wire:target="editarBajaAlta"><i class="ti ti-loader-2 ti-spin me-1"></i>Guardando...</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <!--/ Formulario baja alta -->
</div>

@script
<script>
  $wire.on('abrirModal', (event) => {
    const modalId = (event && event.nombreModal) || (event[0] && event[0].nombreModal) || 'modaBajaAlta';
    $('#' + modalId).modal('show');
  });

  $wire.on('msnTieneRegistros', (data) => {
    const payload = Array.isArray(data) ? data[0] : data;
    Swal.fire({
      title: payload.msnTitulo,
      html: payload.msnTexto,
      icon: payload.msnIcono,
      showCancelButton: true,
      confirmButtonText: 'Sí, dar de baja',
      cancelButtonText: 'Cancelar',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
         $wire.$call('abrirModalBajaAlta', payload.id, 'baja');
      }
    });
  });

  $wire.on('msnConfirmarEliminarSinRegistros', (data) => {
    const payload = Array.isArray(data) ? data[0] : data;
    Swal.fire({
      title: payload.msnTitulo,
      html: payload.msnTexto,
      icon: payload.msnIcono,
      showCancelButton: true,
      showDenyButton: true,
      confirmButtonText: 'Sí, eliminar',
      denyButtonText: 'Dar de baja',
      cancelButtonText: 'Cancelar',
      customClass: {
        confirmButton: 'btn btn-danger me-2',
        denyButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
         $wire.$call('confirmarEliminacion', payload.id);
      } else if (result.isDenied) {
         $wire.$call('abrirModalBajaAlta', payload.id, 'baja');
      }
    });
  });

  $wire.on('msnConfirmarEliminacion', (data) => {
    const payload = Array.isArray(data) ? data[0] : data;
    Swal.fire({
      title: payload.msnTitulo,
      html: payload.msnTexto,
      icon: payload.msnIcono,
      input: 'password',
      inputPlaceholder: 'Tu contraseña actual',
      inputAttributes: {
        autocapitalize: 'off',
        autocorrect: 'off',
        autocomplete: 'current-password'
      },
      showCancelButton: true,
      confirmButtonText: 'Sí, purgar definitivamente',
      cancelButtonText: 'Cancelar',
      customClass: {
        confirmButton: 'btn btn-danger me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false,
      preConfirm: (password) => {
        if (!password) {
          Swal.showValidationMessage('Debes ingresar tu contraseña para autorizar la eliminación.');
        }
        return password;
      }
    }).then((result) => {
      if (result.isConfirmed) {
         $wire.$call('eliminacionForzada', payload.id, result.value);
      }
    });
  });

  $wire.on('alertaError', (data) => {
    const payload = Array.isArray(data) ? data[0] : data;
    Swal.fire({
      icon: 'error',
      title: 'Operación denegada',
      text: (payload && payload.mensaje) || 'No fue posible completar la acción.',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    });
  });
</script>
@endscript
