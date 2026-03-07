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
            <!-- Columna Carrito Izquierda -->
            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div
                        class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 fw-bold text-primary" >Mi Carrito </h4>
                        <span class="badge btn-primary text-white rounded-pill px-3 py-2">{{ count($items) }} Ítems</span>
                    </div>

                    <div class="card-body p-0 px-4 pb-4">
                        @if (count($items) > 0)
                            <div class="table-responsive">
                                <table class="table table-borderless align-middle mb-0">
                                    <thead class="border-bottom">
                                        <tr>
                                            <th scope="col" class="text-muted small fw-medium pb-3">CURSO</th>
                                            <th scope="col" class="text-muted small fw-medium pb-3 text-end">PRECIO
                                            </th>
                                            <th scope="col" class="text-muted small fw-medium pb-3 text-center">
                                                ACCIÓN</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($items as $item)
                                            <tr class="border-bottom">
                                                <td class="py-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-light rounded p-3 me-3 text-center"
                                                            style="width: 60px; height: 60px;">
                                                            <i class="ti ti-book text-primary fs-3"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1 fw-bold text-dark">{{ $item['nombre'] }}
                                                            </h6>
                                                            <small class="text-muted">Acceso Inmediato</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end py-4 fw-medium text-dark">
                                                    $ {{ number_format($item['precio'], 0, ',', '.') }}
                                                </td>
                                                <td class="text-center py-4">
                                                    <button wire:click="eliminarItem({{ $item['curso_id'] }})"
                                                        class="btn btn-sm btn-icon btn-label-danger rounded-circle"
                                                        title="Eliminar del carrito" wire:loading.attr="disabled">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="ti ti-shopping-cart-x text-muted"
                                        style="font-size: 4rem; opacity: 0.5;"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-2">Tu carrito está vacío</h5>
                                <p class="text-muted mb-4">Parece que aún no has agregado ningún curso.</p>
                                <a href="{{ route('cursos.gestionar') }}"
                                    class="btn btn-primary shadow-sm px-4">Explorar Cursos</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Columna Resumen Derecha -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 position-sticky" style="top: 2rem;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4" style="color: #2b3445;">Resumen</h5>

                        <div class="d-flex justify-content-between mb-3 text-muted">
                            <span>Subtotal</span>
                            <span>$ {{ number_format($total, 0, ',', '.') }}</span>
                        </div>

                        <hr class="my-4 border-light">

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="fw-bold fs-5 text-dark">Total</span>
                            <span class="fw-bold fs-4 text-primary">$ {{ number_format($total, 0, ',', '.') }}</span>
                        </div>

                        @if (count($items) > 0)
                            @auth
                                <a href="{{ route('cursos.checkout') }}"
                                    class="btn btn-primary w-100 py-3 rounded-3 fw-bold shadow-sm mb-3"
                                    style="font-size: 1.1rem;">
                                    Proceder al Pago <i class="ti ti-arrow-right ms-2"></i>
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="btn btn-primary w-100 py-3 rounded-3 fw-bold shadow-sm mb-3"
                                    style="font-size: 1.1rem;">
                                    Iniciar sesión para pagar <i class="ti ti-login ms-2"></i>
                                </a>
                            @endauth
                        @else
                            <button disabled
                                class="btn btn-secondary w-100 py-3 rounded-3 fw-bold shadow-sm mb-3 opacity-50"
                                style="font-size: 1.1rem;">
                                Proceder al Pago
                            </button>
                        @endif

                        
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
