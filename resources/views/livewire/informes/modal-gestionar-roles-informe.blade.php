<div>
    @if ($showModal)
    <div class="modal-backdrop fade show"></div>
    <div id="modalRoles" class="modal fade show" style="display: block;" tabindex="-1" role="dialog" aria-labelledby="modalRolesLabel" aria-hidden="true">
      <div class="modal-dialog modal-l modal-dialog-centered modal-simple">
        <div class="modal-content p-0">
          <div class="modal-header border-bottom d-flex justify-content-between px-5 pb-3">
            <div>
              <p class="text-black fw-semibold mb-0">Gestionar Roles para: <strong>{{ $nombreInforme }}</strong></p>
            </div>
            <button type="button" class="btn btn-sm" wire:click="cerrarModal" aria-label="Close"><i class="ti ti-x ti-sm"></i></button>
          </div>

          <div class="modal-body px-5 py-8">
            <div wire:ignore x-data x-init="
                $('#selectRoles').select2({
                    dropdownParent: $('#modalRoles'),
                    placeholder: 'Seleccione uno o más roles'
                });

                $('#selectRoles').val(@json($rolesSeleccionados)).trigger('change');

                $('#selectRoles').on('change', function (e) {
                    // Cuando cambia, obtenemos los valores seleccionados...
                    let data = $(this).val();
                    // ...y se los enviamos a la propiedad 'rolesSeleccionados' en Livewire.
                    // @this es una forma de acceder al componente Livewire desde Alpine/JS
                    @this.set('rolesSeleccionados', data);
                });"
              >
                <label for="selectRoles" class="form-label">¿Que roles pueden ver este informe?</label>
                <select id="selectRoles" class="form-select" multiple wire:model="rolesSeleccionados">
                    @foreach ($todosLosRoles as $rol)
                        <option value="{{ $rol->id }}">{{ $rol->name }}</option>
                    @endforeach
                </select>
              </div>
          </div>

          <div class="modal-footer border-top p-5">
            <button type="button" wire:click="cerrarModal" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
            <button type="button" wire:click="guardarRoles" class="btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect">Guardar</button>

          </div>
        </div>
      </div>
    </div>
    @endif
</div>

@script
  <script>
    //// para mostrar el mensaje y cerrar el modal
    $wire.on('msn', () => {
      Swal.fire({
          title: event.detail.msnTitulo,
          html: event.detail.msnTexto,
          icon: event.detail.msnIcono,
          showConfirmButton: false,
          timer: 1500,
          buttonsStyling: false
      });
    });
  </script>
@endscript
