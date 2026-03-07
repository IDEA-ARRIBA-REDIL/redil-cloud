<div>
    @if (!$validacion['cumple'])
        {{-- ESTADO BLOQUEADO --}}
        <div class="alert alert-warning mb-3" role="alert" style="font-size: 0.85rem;">
            <div class="fw-bold mb-1"><i class="ti ti-alert-triangle me-1"></i> No puedes inscribirte aún:</div>
            <ul class="mb-0 ps-3">
                @foreach ($validacion['razones'] as $razon)
                    <li>{{ $razon }}</li>
                @endforeach
            </ul>
        </div>

        <div class="d-grid gap-2">
            <button class="btn btn-primary disabled opacity-50" type="button">
                @if ($curso->es_gratuito)
                    Inscribirme
                @else
                    Comprar
                @endif
            </button>
        </div>
    @else
        {{-- ESTADO HABILITADO --}}
        <div class="d-grid gap-2">
            @auth
                @if ($curso->es_gratuito)
                    <button wire:click="inscribirGratis" wire:loading.attr="disabled"
                        class="btn btn-success waves-effect waves-light shadow" type="button">
                        <span wire:loading.remove wire:target="inscribirGratis">Inscribirme Gratis</span>
                        <span wire:loading wire:target="inscribirGratis"><i class="ti ti-loader ti-spin me-2"></i>
                            Procesando...</span>
                    </button>
                @else
                    <button wire:click="comprarDirecto" wire:loading.attr="disabled"
                        class="btn btn-primary waves-effect waves-light shadow mb-2" type="button">
                        <span wire:loading.remove wire:target="comprarDirecto">Comprar Ahora</span>
                        <span wire:loading wire:target="comprarDirecto"><i class="ti ti-loader ti-spin me-2"></i> Cargando
                            checkout...</span>
                    </button>

                    @if ($enCarrito)
                        <a href="{{ route('cursos.carrito') }}" class="btn btn-secondary waves-effect shadow-sm"
                            type="button">
                            <i class="ti ti-shopping-cart-check me-1"></i> Ver Carrito ({{ $cantidadCarrito }})
                        </a>
                    @else
                        <button wire:click="agregarAlCarrito" wire:loading.attr="disabled"
                            class="btn btn-outline-secondary waves-effect" type="button">
                            <span wire:loading.remove wire:target="agregarAlCarrito"><i
                                    class="ti ti-shopping-cart-plus me-1"></i> Añadir al carrito</span>
                            <span wire:loading wire:target="agregarAlCarrito"><i class="ti ti-loader ti-spin me-2"></i>
                                Añadiendo...</span>
                        </button>
                    @endif
                @endif
            @else
                @if ($curso->es_gratuito)
                    <a href="{{ route('login') }}" class="btn btn-success waves-effect waves-light shadow">
                        Iniciar sesión para inscribirme
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary waves-effect waves-light shadow mb-2">
                        Iniciar sesión para comprar
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary waves-effect shadow-sm">
                        <i class="ti ti-shopping-cart-plus me-1"></i> Iniciar sesión para añadir al carrito
                    </a>
                @endif
            @endauth
        </div>
    @endif
</div>
