@php
    $configData = Helper::appClasses();
@endphp
<div>
    @auth
        @include('layouts/sections/navbar/navbar')
    @else
        @include('layouts/sections/navbar/navbar-front')
    @endauth

    <div class="container-fluid py-5" style="background-color: #f8f8fb; min-height: 100vh; margin-top: 80px;">
        <div class="container pb-5">
        <div class="row pt-4">
            <!-- Columna Formulario Izquierda -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                        <h4 class="mb-0 fw-bold text-primary" >Checkout de Cursos</h4>
                        <p class="text-muted small mt-1">Completa tu información y elige un método de pago.</p>
                    </div>

                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3" style="color: #4b5563;">Detalles de Facturación</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-medium">Nombre Completo</label>
                                <input type="text" class="form-control" wire:model="nombreComprador"
                                    placeholder="Ej. Juan Pérez">
                                @error('nombreComprador')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-medium">Identificación (Cédula/NIT)</label>
                                <input type="text" class="form-control" wire:model="identificacionComprador"
                                    placeholder="Ej. 10203040">
                                @error('identificacionComprador')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-medium">Correo Electrónico</label>
                                <input type="email" class="form-control" wire:model="EmailComprador"
                                    placeholder="ejemplo@correo.com">
                                @error('EmailComprador')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-medium">Teléfono / Celular</label>
                                <input type="text" class="form-control" wire:model="telefonoComprador"
                                    placeholder="Ej. 3001234567">
                                @error('telefonoComprador')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4 border-light">

                        <h5 class="fw-bold mb-3" style="color: #4b5563;">Método de Pago</h5>
                        @if ($tiposPagoDisponibles->isEmpty())
                            <div class="alert alert-warning mb-0 border-0 shadow-sm" style="background-color: #fff3cd; color: #856404;">
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-alert-triangle fs-4 me-3"></i>
                                    <div>
                                        <span class="fw-bold d-block">Sin métodos de pago configurados</span>
                                        <small>Los cursos seleccionados en tu carrito no tienen métodos de pago habilitados. Por favor, contacta con soporte o con el administrador.</small>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="row g-3">
                                @foreach ($tiposPagoDisponibles as $tipo)
                                    <div class="col-md-6">
                                        <div class="form-check custom-option custom-option-basic border rounded-3 p-2 bg-white {{ $tipoPagoSeleccionado == $tipo->id ? 'border-primary shadow-sm' : '' }}"
                                            style="cursor: pointer; {{ $tipoPagoSeleccionado == $tipo->id ? 'border-width: 2px !important;' : '' }}"
                                            wire:click="$set('tipoPagoSeleccionado', {{ $tipo->id }})">
                                            <label
                                                class="form-check-label p-2 w-100 d-flex align-items-center"
                                                for="tipoPago{{ $tipo->id }}">
                                                <input class="form-check-input d-none" type="radio"
                                                    wire:model="tipoPagoSeleccionado" value="{{ $tipo->id }}"
                                                    id="tipoPago{{ $tipo->id }}">
                                                
                                                @if($tipo->imagen)
                                                    <div class="me-3 flex-shrink-0">
                                                        <img src="{{ asset('assets/img/illustrations/' . $tipo->imagen) }}" alt="{{ $tipo->nombre }}" style="height: 40px; width: auto; object-fit: contain;">
                                                    </div>
                                                @endif

                                                <div class="flex-grow-1">
                                                    <span class="d-block fw-bold text-dark">{{ $tipo->nombre }}</span>
                                                    <small class="text-muted">Paga de forma segura</small>
                                                </div>
                                                
                                                <i class="ti ti-check ms-auto text-primary fs-4 {{ $tipoPagoSeleccionado == $tipo->id ? 'd-block' : 'd-none' }}"></i>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('tipoPagoSeleccionado')
                                <span class="text-danger small mt-2 d-block">{{ $message }}</span>
                            @enderror
                        @endif
                    </div>
                </div>
            </div>

            <!-- Columna Resumen Derecha -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 position-sticky" style="top: 2rem;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4" style="color: #2b3445;">Resumen del Pedido</h5>

                        @if ($carrito)
                            <div class="mb-4">
                                @foreach ($carrito->items as $item)
                                    <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                                        <div class="me-3">
                                            <h6 class="mb-1 fw-bold text-dark text-truncate" style="max-width: 200px;">
                                                {{ $item['nombre'] }}</h6>
                                            <small class="text-muted">Curso Digital LMS</small>
                                        </div>
                                        <span class="fw-medium text-dark">$
                                            {{ number_format($item['precio'], 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4 pt-2">
                                <span class="fw-bold fs-5 text-dark">Total a Pagar</span>
                                <span class="fw-bold fs-4 text-primary">$
                                    {{ number_format($carrito->total, 0, ',', '.') }}</span>
                            </div>

                            @auth
                                <button wire:click="procesarPago" wire:loading.attr="disabled"
                                    class="btn btn-primary w-100 py-3 rounded-3 fw-bold shadow-sm"
                                    style="font-size: 1.1rem;">
                                    <span wire:loading.remove wire:target="procesarPago">Confirmar y Pagar</span>
                                    <span wire:loading wire:target="procesarPago"><i class="ti ti-loader ti-spin me-2"></i>
                                        Procesando...</span>
                                </button>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-primary w-100 py-3 rounded-3 fw-bold shadow-sm"
                                    style="font-size: 1.1rem;">
                                    Iniciar sesión para pagar
                                </a>
                            @endauth

                            <div class="text-center mt-3">
                                <small class="text-muted"><i class="ti ti-lock me-1"></i> Transacción 100% segura y
                                    encriptada</small>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="ti ti-shopping-cart text-muted mb-2" style="font-size: 2rem;"></i>
                                <p class="text-muted mb-0">No hay cursos en tu carrito.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
