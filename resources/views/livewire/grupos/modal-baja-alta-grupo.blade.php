<div>
    <!-- Formulario baja alta-->
    <div wire:ignore.self class="modal fade" id="modaBajaAlta" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-simple modal-edit-user">
        <div class="modal-content">
          <div class="modal-body">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="text-center mb-4">
              <h3 class="mb-2">{!! $titulo !!}</h3>
              <p class="text-muted">Los campos con <span class="badge badge-dot bg-info me-1"></span> son obligatorios</p>
            </div>
            <form wire:submit="crearBajaAlta({{ $grupoId }}, '{{$tipo}}')" class="row g-3">
              <!-- Motivo -->
              <div class="mb-2 col-12 col-md-12">
                <label class="form-label">
                <span class="badge badge-dot bg-info me-1"></span> Motivo  @error('motivo') <span class="error">{{ $message }}</span> @enderror
                </label>
                <input wire:model="motivo" name="motivo" type="text" class="form-control" value="" placeholder="Detalla aquí el motivo de la {{$tipo}}.">
              </div>
              <!--/Motivo -->

              <!-- Observacion -->
              <div class="mb-2 col-12 col-md-12">
                <label class="form-label">
                  Observaciones:
                </label>
                <textarea wire:model="observaciones" name="observaciones" class="form-control" rows="2" maxlength="500" spellcheck="false" data-ms-editor="true" placeholder="Detalla aquí las observaciones adicionales."></textarea>
              </div>
              <!--/Observacion-->

              @if($tipo == 'baja' && $grupo->asistentes()->select('users.id')->count() > 0)
              <div class="mb-2 col-12 col-md-12">
                <div class="alert alert-primary alert-dismissible" role="alert">
                  <h5 class="alert-heading mb-2">Importante</h5>
                  <p class="mb-0"> Actualmente, el grupo tiene {{$grupo->asistentes()->select('users.id')->count()}} de integrante(s), ¿deseas asignar estos integrantes a otro grupo? Para esto debes seleccionar el grupo donde deseas trasladarlos, de contrario si no se selecciona un grupo y se da de baja, todos sus integrantes pasarán a ser usuarios sin grupo.</p>
                </div>
              </div>

                @livewire('Grupos.grupos-para-busqueda',[
                  'id' => 'grupo',
                  'class' => 'mb-2 col-12 col-md-12',
                  'label' => 'Selecciona el grupo',
                  'conDadosDeBaja' => 'no',
                  'grupoSeleccionadoId' => ''
                ])
              @endif

              <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary me-sm-3 me-1">Guardar</button>
                <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
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

  $wire.on('msnConfirmarEliminacion', data => {
    Swal.fire({
      title: data.msnTitulo,
      html: data.msnTexto,
      icon: data.msnIcono,
      showCancelButton: false,
      confirmButtonText: data.confirmButtonText,
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        if (data.tienePermiso == 'si'){
          $wire.$call('eliminacion', data.id);
        }else{
          Swal.fire(
            '¡Ups!',
            'No tienes suficientes privielgios para realizar esta acción.',
            'warning'
          )
        }

      }
    })
  });
</script>
@endscript
