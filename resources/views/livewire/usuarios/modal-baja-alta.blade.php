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
                  Motivo  <br> @error('motivo') <span class="error">{{ $message }}</span> @enderror
                </label>
                <select wire:model="motivo"  id="motivo" name="motivo" class="select2 form-select" >
                  <option  value="">Ninguno</option>
                  @foreach ($motivosBajasAltas as $motivo)
                  <option value="{{$motivo->id}}">{{$motivo->nombre}}</option>
                  @endforeach
                </select>
              </div>
              <!-- /motivo -->

              <!-- Observacion -->
              <div class="mb-2 col-12 col-md-12">
                <label class="form-label text-black">
                  Observaciones
                </label>
                <textarea wire:model="observacion" class="form-control" rows="2" maxlength="500" spellcheck="false" data-ms-editor="true" placeholder="Detalla aquí las observaciones adicionales .">{{ old('descripcion_peticion') }}</textarea>
              </div>
              <!--/Observacion-->

              <div class="col-12 text-center">
                <button type="reset" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                <button type="submit" class="btn btn-primary me-sm-3 me-1 rounded-pill">Guardar</button>
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
  $wire.on('abrirModal', () => {
    $('#' + event.detail.nombreModal).modal('show');
  });

  $wire.on('msnTieneRegistros', data => {
    Swal.fire({
      title: data.msnTitulo,
      html: data.msnTexto,
      icon: data.msnIcono,
      showCancelButton: false,
      confirmButtonText: 'Si, dar de baja',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
         $wire.$call('abrirModalBajaAlta', data.id, 'baja');
      }
    })
  });

  $wire.on('msnConfirmarEliminacion', data => {
    Swal.fire({
      title: data.msnTitulo,
      html: data.msnTexto,
      icon: data.msnIcono,
      showCancelButton: false,
      confirmButtonText: 'Si, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
         $wire.$call('eliminacionForzada', data.id);
      }
    })
  });
</script>
@endscript
