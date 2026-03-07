<div>
    <div class="modal-body">
        <div class="row">
            <div class="col-12 mb-3">
                <h5 class="modal-title" id="vincularMateriaModalLabel">Vincular Materia al Grado</h5>
                <select id="materia_id" wire:model="materia_id" class="form-select @error('materia_id') is-invalid @enderror">
                    <option value="">Seleccione una materia...</option>
                    @foreach ($materiasDisponibles as $materia)
                        <option value="{{ $materia->id }}">{{ $materia->nombre }}</option>
                    @endforeach
                </select>
                @error('materia_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="es_obligatoria" wire:model="es_obligatoria">
                    <label class="form-check-label" for="es_obligatoria">
                        Es obligatoria para aprobar el nivel
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" wire:click="vincular" wire:loading.attr="disabled">
            <span wire:loading.remove>Vincular</span>
            <span wire:loading>Vinculando...</span>
        </button>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('cerrarModalVincular', (event) => {
                var myModalEl = document.getElementById('modalVincularMateria');
                var modal = bootstrap.Modal.getInstance(myModalEl);
                if(modal) {
                    modal.hide();
                }
            });
        });
    </script>
</div>
