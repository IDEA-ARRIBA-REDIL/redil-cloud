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
            @php
                $permisosNames = $grupo->permisos->pluck('name')->toArray();
                $totalPermisos = count($permisosNames);
                $activosCount = $grupo->permisos->filter(fn($p) => isset($rolePermissionIds[$p->id]))->count();
                $todosActivos = ($totalPermisos > 0 && $activosCount === $totalPermisos);
            @endphp
            <div class="col-12 mt-4" wire:key="bloque-{{ $loop->index }}">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 border-bottom pb-2 gap-2">
                            <h4 class="card-title fw-semibold mb-0">{{ $grupo->bloque->nombre }}</h4>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $todosActivos ? 'bg-label-success' : ($activosCount > 0 ? 'bg-label-primary' : 'bg-label-secondary') }}">
                                    {{ $activosCount }}/{{ $totalPermisos }} activos
                                </span>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button"
                                        class="btn {{ $todosActivos ? 'btn-success' : 'btn-outline-primary' }}"
                                        wire:click="activarTodosBloque(@js($permisosNames))"
                                        wire:loading.attr="disabled"
                                        title="Activar todos los permisos de este bloque">
                                        <i class="ti ti-checks me-1"></i> Activar todos
                                    </button>
                                    @if($activosCount > 0)
                                        <button type="button"
                                            class="btn btn-outline-danger"
                                            wire:click="desactivarTodosBloque(@js($permisosNames))"
                                            wire:loading.attr="disabled"
                                            title="Desactivar todos los permisos de este bloque">
                                            <i class="ti ti-x me-1"></i> Desactivar todos
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach ($grupo->permisos as $permiso)
                                <div class="col-6 col-md-3 col-lg-4 mb-4" wire:key="permiso-{{ $permiso->id }}">
                                    <div class="form-label mb-2">
                                        {{ str_replace('_', ' ', $permiso->titulo) }}
                                        @if($rolActivo && $rolActivo->hasPermissionTo($permiso->name))
                                            <i class="ti ti-shield-check text-success ms-1" title="Tu rol activo ya tiene este permiso"></i>
                                        @endif
                                    </div>
                                    <label class="switch switch-lg">
                                        <input type="checkbox"
                                               class="switch-input"
                                               id="permiso_{{ $permiso->id }}"
                                               wire:click="togglePermiso('{{ $permiso->name }}', {{ !isset($rolePermissionIds[$permiso->id]) ? 'true' : 'false' }})"
                                               {{ isset($rolePermissionIds[$permiso->id]) ? 'checked' : '' }} />
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
