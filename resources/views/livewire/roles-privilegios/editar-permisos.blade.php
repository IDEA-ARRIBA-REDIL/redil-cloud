<div>
    <div class="row mb-4 mt-5">
        <div class="col-12">
            <div class="input-group">
                <span class="input-group-text"><i class="ti ti-search"></i></span>
                <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Buscar bloque o permiso..." aria-label="Buscar">
            </div>
        </div>
    </div>
    <div class="row">
        @foreach ($bloquesPermisos as $grupo)
            <div class="col-12 mt-4">
                <div class="card h-100 ">
                    <div class="card-header pb-0">
                        <h4 class="card-title fw-semibold mb-3 border-bottom">{{ $grupo->bloque->nombre }}</h5>

                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach ($grupo->permisos as $permiso)
                                <div class="col-6 col-md-3 col-lg-4 mb-4">
                                    <div class="form-label mb-2">{{ str_replace('_', ' ', $permiso->titulo) }}</div>
                                    <label class="switch switch-lg">
                                        <input type="checkbox"
                                               class="switch-input"
                                               id="permiso_{{ $permiso->id }}"
                                               wire:click="togglePermiso('{{ $permiso->name }}', {{ !$role->hasPermissionTo($permiso->name) ? 'true' : 'false' }})"
                                               {{ $role->hasPermissionTo($permiso->name) ? 'checked' : '' }} />
                                        <span class="switch-toggle-slider">
                                            <span class="switch-on">Si</span>
                                            <span class="switch-off">No</span>
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
