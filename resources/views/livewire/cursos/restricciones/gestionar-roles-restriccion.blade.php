<div class="mb-5" x-data="{ formVisible: {{ $rolesRestingidos->count() == 0 ? 'true' : 'false' }} }">
    <h5 class="fw-bold text-primary mb-1">Restricción por Roles</h5>
    <p class="text-dark small mb-3">Define los roles de usuario que pueden acceder a este curso.</p>

    {{-- Formulario --}}
    <div x-show="formVisible" x-transition class="row g-3 mb-4 align-items-end">
        <div class="col-md-9 col-sm-12" wire:ignore>
            <label class="form-label text-dark small">Seleccionar Rol</label>
            <select id="select-rol-restriccion" class="form-select select2 border-1">
                <option value="">Selecciona una opción</option>
                @foreach($rolesList as $rol)
                    <option value="{{ $rol->id }}">{{ $rol->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-sm-12">
            <button type="button" wire:click="agregarRol" class="btn btn-outline-secondary rounded-pill w-100">
                Agregar
            </button>
        </div>
    </div>

    <div class="col-12 mt-1">
        @error('roleSeleccionado')
            <span class="text-danger small d-block">{{ $message }}</span>
        @enderror
    </div>

    {{-- Tabla --}}
    @if($rolesRestingidos->count() > 0)
        <div class="border rounded-3 p-3 dashed-border" style="border-style: dashed !important; border-color: #e5e7eb !important;">
            <div class="table-responsive">
                <table class="table table-borderless table-hover mb-0">
                    <thead class="text-dark border-bottom">
                        <tr>
                            <th class="fw-normal">Rol</th>
                            <th width="100" class="text-center fw-normal">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rolesRestingidos as $rol)
                            <tr>
                                <td class="align-middle fw-medium">{{ $rol->name }}</td>
                                <td class="text-center align-middle">
                                    <button
                                        type="button"
                                        wire:confirm="¿Estás seguro de eliminar este rol?"
                                        wire:click="eliminarRol({{ $rol->id }})"
                                        class="btn btn-link text-danger p-0">
                                        <i class="ti ti-trash fs-5"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
         <div class="mt-3">
             <a href="#" @click.prevent="formVisible = !formVisible" style="text-decoration: underline;">
                <i class="ti ti-circle-plus"></i> <span x-text="formVisible ? 'Ocultar formulario' : 'Agregar rol'"></span>
            </a>
        </div>
    @else
        <div style="border: 2px solid #95CDDF;" class="rounded-3 p-3 ">
             <div class="d-flex align-items-center text-black mb-2">
                <i class="ti ti-info-circle fs-4 me-2"></i>
                <span class="small">Este curso es público para todos los roles (sujeto a otras restricciones).</span>
            </div>
        </div>
    @endif
</div>

@script
<script>
    $(document).ready(function() {
        $('#select-rol-restriccion').select2({
            width: '100%',
            placeholder: 'Selecciona una opción',
            allowClear: true
        }).on('change', function (e) {
            @this.set('roleSeleccionado', $(this).val());
        });

        Livewire.on('msn', (data) => {
            let msn = data.msn || (data[0] ? data[0].msn : null);
            let icon = data.icon || (data[0] ? data[0].icon : 'info');

            if (icon === 'success' && msn && msn.includes('eliminad')) {
                 Swal.fire(
                    '¡Eliminado!',
                    msn,
                    'success'
                )
            }
        });
    });
</script>
@endscript
