<div>
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-md-3 mb-4 mb-md-0">
            <div class="card shadow-none bg-transparent h-100">
                <div class="card-body p-0">
                    <div class="nav-align-left text-center">
                        <ul class="nav nav-pills flex-column bg-white rounded p-2 shadow-sm">
                            @foreach($categories as $index => $category)
                                <li class="nav-item">
                                    <button type="button" 
                                            class="nav-link mb-1 w-100 text-start d-flex align-items-center {{ $activeCategory === $category ? 'active' : '' }}"
                                            wire:click="setActiveCategory('{{ $category }}')">
                                        <i class="ti ti-palette me-2"></i>
                                        <span class="text-truncate">{{ ucfirst($category) }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom bg-white py-3">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <span class="badge bg-label-primary p-2 me-2">
                            <i class="ti ti-color-swatch ti-sm"></i>
                        </span>
                        <span>Personalización de <span class="text-primary fw-bold">{{ ucfirst($activeCategory) }}</span></span>
                    </h5>
                </div>
                <div class="card-body pt-4">
                    @if($showSuccessMessage)
                        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                            <i class="ti ti-circle-check me-2"></i>
                            <div>Color actualizado correctamente</div>
                            <button type="button" class="btn-close" wire:click="hideMessage"></button>
                        </div>
                    @endif

                    <div class="row g-4">
                        @foreach($settings[$activeCategory] as $setting)
                            <div class="col-sm-6 col-lg-4" wire:key="card-{{ $setting['id'] }}">
                                <div class="card border rounded h-100 transition-all hover-shadow {{ $editingId === $setting['id'] ? 'border-primary' : '' }}">
                                    <!-- Preview Section -->
                                    <div class="card-img-top border-bottom position-relative" 
                                         style="height: 100px; background-color: {{ $setting['value'] }}; {{ $setting['gradient'] == 'true' ? 'background: linear-gradient(135deg, '.$setting['value'].' 0%, '.$setting['value2'].' 100%)' : '' }}">
                                        @if($setting['gradient'] == 'true')
                                            <div class="position-absolute top-0 end-0 m-2">
                                                <span class="badge bg-white text-dark shadow-sm">Gradiente</span>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="card-body p-3">
                                        <div class="d-flex flex-column h-100">
                                            <div class="mb-3">
                                                <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Variable</small>
                                                <h6 class="mb-0 text-truncate fw-semibold" title="{{ $setting['nombre'] }}">{{ $setting['nombre'] }}</h6>
                                            </div>

                                            @if($editingId === $setting['id'])
                                                <!-- Edit Mode -->
                                                <div class="mt-auto">
                                                    <div class="mb-3">
                                                        <label class="form-label small text-muted mb-1">Color principal</label>
                                                        <div class="d-flex align-items-center gap-2 mb-2">
                                                            <input type="color" 
                                                                   class="form-control form-control-color" 
                                                                   wire:model.live="editingValue"
                                                                   title="Seleccionar color">
                                                            <input type="text" 
                                                                   class="form-control form-control-sm font-monospace" 
                                                                   wire:model.live="editingValue" 
                                                                   placeholder="#000000">
                                                        </div>
                                                        
                                                        @if($setting['gradient'] == 'true')
                                                            <label class="form-label small text-muted mb-1">Color gradiente</label>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <input type="color" 
                                                                       class="form-control form-control-color" 
                                                                       wire:model.live="editingValue2"
                                                                       title="Seleccionar color gradiente">
                                                                <input type="text" 
                                                                       class="form-control form-control-sm font-monospace" 
                                                                       wire:model.live="editingValue2" 
                                                                       placeholder="#000000">
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-primary btn-sm flex-grow-1" wire:click="updateColor">
                                                            <i class="ti ti-device-floppy me-1"></i> Guardar
                                                        </button>
                                                        <button class="btn btn-label-secondary btn-sm" wire:click="cancelEditing">
                                                            <i class="ti ti-x"></i>
                                                        </button>
                                                    </div>
                                                    
                                                    @error('editingValue')
                                                        <div class="text-danger mt-1" style="font-size: 0.75rem;">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            @else
                                                <!-- View Mode -->
                                                <div class="mt-auto">
                                                    <div class="d-flex align-items-center justify-content-between mb-3 bg-light rounded p-2">
                                                        <code class="text-primary fw-bold" style="font-size: 0.75rem;">{{ $setting['value'] }}</code>
                                                        @if($setting['gradient'] == 'true')
                                                            <i class="ti ti-arrow-right text-muted mx-1" style="font-size: 0.7rem;"></i>
                                                            <code class="text-info fw-bold" style="font-size: 0.75rem;">{{ $setting['value2'] }}</code>
                                                        @endif
                                                    </div>
                                                    <button class="btn btn-outline-primary btn-sm w-100" 
                                                            wire:click="startEditing({{ $setting['id'] }}, '{{ $setting['value'] }}', '{{ $setting['value2'] }}')">
                                                        <i class="ti ti-edit me-1"></i> Editar
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-shadow:hover {
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important;
            transform: translateY(-2px);
        }
        .transition-all {
            transition: all 0.3s ease;
        }
        .form-control-color {
            width: 45px;
            min-width: 45px;
            height: 38px;
            padding: 4px;
            cursor: pointer;
        }
        .nav-pills .nav-link.active {
            box-shadow: 0 2px 4px rgba(50, 112, 10, 0.4);
        }
        .font-monospace {
            font-family: 'SFMono-Regular', Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
        }
    </style>
</div>
