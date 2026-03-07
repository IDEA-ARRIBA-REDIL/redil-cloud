<div style="margin: 2% 2%;" class="row align-items-center">

    <div class="p-4 col-lg-12 col-md-10 col-sm-12">
        <!-- Stepper (Paso a Paso) -->
        @if ($configuracion->version == 2)
        <div class="m-lg-auto border-0 mb-4">
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-3 gap-md-2">
                <!-- Paso 1 - Carrito -->
                <div class="step {{ $pasoActual == 1 ? 'active' : '' }} d-flex align-items-center">
                    <button type="button" class="step-trigger bg-transparent border-0 p-0" wire:click="volverPaso">
                        <span class="bs-stepper-icon {{ $pasoActual == 1 ? 'btn-primary' : 'bg-light' }}">
                            <svg viewBox="0 0 60 60" class="w-40px h-40px">
                                <use xlink:href="{{ asset('assets/svg/icons/wizard-checkout-cart.svg#wizardCart') }}"></use>
                            </svg>
                        </span>
                        <span class="bs-stepper-label d-none d-md-block mt-2 {{ $pasoActual == 1 ? '' : 'text-muted' }}">Carrito</span>
                    </button>
                </div>

                @if($totalPasos > 1)
                <div class="line d-none d-md-block"><i class="ti ti-chevron-right fs-5 text-muted"></i></div>

                <!-- Paso 2 - Formulario -->
                <div class="step {{ $pasoActual == 2 ? 'active' : '' }} d-flex align-items-center">
                    <button type="button" class="step-trigger bg-transparent border-0 p-0" @if($pasoActual == 1) disabled @endif>
                        <span class="bs-stepper-icon {{ $pasoActual == 2 ? 'btn-primary' : 'bg-light' }}">
                            <svg viewBox="0 0 60 60" class="w-40px h-40px">
                                <use xlink:href="{{ asset('assets/svg/icons/wizard-checkout-address.svg#wizardCheckoutAddress') }}"></use>
                            </svg>
                        </span>
                        <span class="bs-stepper-label d-none d-md-block mt-2 {{ $pasoActual == 2 ? '' : 'text-muted' }}">Formulario</span>
                    </button>
                </div>
                @endif

                <div class="line d-none d-md-block"><i class="ti ti-chevron-right fs-5 text-muted"></i></div>

                <!-- Paso Checkout (Referencial) -->
                <div class="step d-flex align-items-center">
                    <button type="button" class="step-trigger bg-transparent border-0 p-0" disabled>
                        <span class="bs-stepper-icon bg-light">
                            <svg viewBox="0 0 60 60" class="w-40px h-40px">
                                <use xlink:href="{{ asset('assets/svg/icons/wizard-checkout-payment.svg#wizardPayment') }}"></use>
                            </svg>
                        </span>
                        <span class="bs-stepper-label d-none d-md-block mt-2 text-muted">Checkout</span>
                    </button>
                </div>
            </div>
        </div>
        @endif
        <!-- Fin del Stepper -->

        <div style="margin-bottom: 120px;" class="row">
            
            @if($pasoActual == 1)
            {{-- ==========================================
                 PASO 1: SELECCIÓN DE PRODUCTOS Y DATOS
                 ========================================== --}}
            <div class="col-12" wire:key="step-1">
                <!-- Listado del carrito -->
                @if (!$actividad->tipo->unica_compra || $actividad->tiene_invitados || $categoriasCompraPermitidas->count() > 1)
                <h3 class="fw-semibold p-0">Selecciona tus ítems</h3>
                <div class="card p-5 shadow border-top-0 border-1 mb-5 col-xl-12 ">
                    <ul class="list-group mb-4 ">
                        <div class="px-6 py-2">
                            @if ($actividad->tiene_invitados)
                                <div class="mb-3">
                                    <label for="cantidadInvitados" class="form-label fw-semibold">Número de invitados adicionales</label>
                                    <input type="number" id="cantidadInvitados" wire:model.live="cantidadInvitados" class="form-control @error('cantidadInvitados') is-invalid @enderror" min="0" placeholder="0" @if (isset($compraActual->id)) disabled @endif>
                                    @error('cantidadInvitados') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            @endif
                        </div>

                        <div class="@if($actividad->tipo->unica_compra && $actividad->tipo->unica_inscripcion && $actividad->tipo->es_gratuita && $categoriasCompraPermitidas->count() === 1 ) d-none @endif">
                            @forelse ($categoriasCompraPermitidas as $categoria)
                            <li class="border-none list-group-item px-6 py-2">
                                <div class="d-flex gap-4">
                                    <div class="flex-grow-1">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <p class="me-3 mb-2">
                                                    <span class="text-heading fw-bold">{{ $categoria->nombre }}</span>
                                                </p>
                                                @if ($actividad->tipo->unica_compra)
                                                    @if (isset($carrito[$categoria->id]))
                                                    <button wire:click="eliminarDelCarrito({{ $categoria->id }})" class="btn btn-outline-danger fw-semibold">
                                                        <i class="ti ti-shopping-cart-x"></i> Eliminar
                                                    </button>
                                                    @else 
                                                    <button wire:click="agregarAlCarrito({{ $categoria->id }})" class="btn btn-label-secondary fw-semibold btn-outline-secondary " @if (count($carrito)> 0 || isset($compraActual->id)) disabled @endif>
                                                        <i class="ti ti-shopping-cart-plus"></i> Agregar
                                                    </button>
                                                    @endif
                                                @else
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                            <button wire:click="decrementCantidad({{ $categoria->id }})" class="btn minus rounded-pill" @if (isset($compraActual->id) || $actividad->tipo->unica_compra || $actividad->tipo->unica_inscripcion) disabled @endif>-</button>
                                                            <input style="height:35px;width:60px; text-align: center;font-size:20px" type="number" wire:model="cantidades.{{ $categoria->id }}" min="1" max="{{ $categoria->limite_compras }}" class="form-control form-control-sm" @if (isset($compraActual->id) || $actividad->tipo->unica_compra || $actividad->tipo->unica_inscripcion) disabled @endif>
                                                            <button wire:click="incrementCantidad({{ $categoria->id }})" class="btn plus rounded-pill" @if (isset($compraActual->id) || $actividad->tipo->unica_compra || $actividad->tipo->unica_inscripcion) disabled @endif>+</button>
                                                    </div>
                                                    @if (isset($carrito[$categoria->id]))
                                                    <button wire:click="eliminarDelCarrito({{ $categoria->id }})" class="btn   btn-outline-danger fw-semibold">
                                                        <i class="ti ti-shopping-cart-x"></i> Eliminar
                                                    </button>
                                                    @else
                                                    <button wire:click="agregarAlCarrito({{ $categoria->id }})" class="btn  btn-outline-secondary fw-semibold" @if (isset($compraActual->id)) disabled @endif>
                                                        <i class="ti ti-shopping-cart-plus"></i> Agregar
                                                    </button>
                                                    @endif
                                                </div>
                                                @endif
                                            </div>
                                            <div class="col-md-4 d-flex align-items-end justify-content-end d-flex gap-2">
                                                <div class="me-3 mb-2 pt-7">
                                                    @php
                                                    $monedaCategoria = $categoria->monedas()->where('moneda_id', $this->monedaSeleccionada)->first();
                                                    @endphp
                                                    @if ($monedaCategoria && $monedaCategoria->pivot->valor > 0)
                                                    <span class="text-black fw-normal fs-5">{{ Number::currency($monedaCategoria->pivot->valor, $monedaCategoria->nombre_corto) }}</span>
                                                    @else
                                                    <span class="success badge rounded-pill px-7 bg-label-claro-success mb-1">Gratis</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <hr>
                            @empty
                            <p>No hay categorías disponibles para esta actividad.</p>
                            @endforelse
                        </div>
                    </ul>
                </div>
                @endif           

                <!-- Datos del Comprador (Si no está logueado) -->
                @if($actividad->requiere_inicio_sesion == false && !Auth::check())
                <div class="card p-5 shadow border-top-0 border-1 mb-5 col-xl-12">
                    <h5>Completa los datos de contacto</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" wire:model.live="nombreComprador" class="form-control @error('nombreComprador') is-invalid @enderror">
                            @error('nombreComprador') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Número Identificación</label>
                            <input type="text" wire:model.live="identificacionComprador" class="form-control @error('identificacionComprador') is-invalid @enderror">
                            @error('identificacionComprador') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" wire:model.live="EmailComprador" class="form-control @error('EmailComprador') is-invalid @enderror">
                            @error('EmailComprador') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono / Celular</label>
                            <input type="text" wire:model.live="telefonoComprador" class="form-control @error('telefonoComprador') is-invalid @enderror">
                            @error('telefonoComprador') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                @endif

                <!-- Sección de monedas y destinatarios -->
                <div class="row mb-5">
                    @if (count($actividad->monedas) > 1)
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <h6 class="text-dark fw-normal">Elija su moneda de pago</h6>
                            <select wire:model.live="monedaSeleccionada" class="form-select" @if (isset($compraActual->id)) disabled @endif>
                                @foreach ($actividad->monedas as $moneda)
                                <option value="{{ $moneda->id }}">{{ $moneda->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif

                    @if ($actividad->destinatarios()->count() > 0)
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <h6 class="text-dark fw-normal">Elija un destinatario</h6>
                            <select wire:model.live="destinatario" class="form-select" @if (isset($compraActual->id)) disabled @endif>
                                <option value="0">Seleccione un destinatario</option>
                                @foreach ($actividad->destinatarios as $dest)
                                <option value="{{ $dest->id }}">{{ $dest->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif

                    @if (count($relacionesFamiliares) > 0)
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <h6 class="text-dark fw-normal">Inscripción a nombre de</h6>
                            <select wire:model="parienteSeleccionado" class="form-select" @if (isset($compraActual->id)) disabled @endif>
                                <option value="{{ $usuario->id }}">{{ $usuario->primer_nombre }} {{ $usuario->primer_apellido }} (Mí)</option>
                                @foreach ($relacionesFamiliares as $pariente)
                                <option value="{{ $pariente->id }}">{{ $pariente->primer_nombre }} {{ $pariente->primer_apellido }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Resumen del carrito (Step 1) -->
                <div class="card p-5 border-top-0 border-1 shadow">
                    <h5 class="fw-semibold">Resumen de la selección</h5>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                @forelse ($carrito as $item)
                                <tr>
                                    <td>{{ $item['nombre'] }}</td>
                                    <td class="text-end fw-bold">
                                        {{ $item['cantidad'] }} x
                                        {{ $item['precio'] > 0 ? Number::currency($item['precio'], $monedaActual?->nombre_corto) : 'Gratis' }}
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="text-muted italic">No has agregado ítems al carrito.</td></tr>
                                @endforelse
                                <tr class="border-top border-dark">
                                    <td class="h5">Total Estimado</td>
                                    <td class="h5 text-end text-primary fw-bold">{{ $total > 0 ? Number::currency($total, $monedaActual?->nombre_corto) : 'Gratis' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @elseif($pasoActual == 2)
            {{-- ==========================================
                 PASO 2: FORMULARIO DINÁMICO
                 ========================================== --}}
            <div class="col-12" wire:key="step-2">
                <h3 class="fw-semibold mb-3">Información Adicional</h3>
                <p class="text-muted mb-4 italic">Por favor, completa los campos marcados como requeridos para finalizar tu inscripción.</p>

                @foreach ($elementosFormulario as $elemento)
                    @php
                        $clase = $elemento->tipoElemento->getRawOriginal('clase') ?? $elemento->tipoElemento->clase;
                        $errorClass = $errors->has('respuestas.'.$elemento->id) ? 'is-invalid' : '';
                    @endphp

                    @if ($elemento->tipo_elemento_id == 1)
                        {{-- ENCABEZADO DE SECCIÓN --}}
                        <div class="col-12 my-4 border-bottom pb-2">
                            <h4 class="fw-bold mb-1">{{ $elemento->titulo }}</h4>
                            <p class="text-muted small mb-0">{{ $elemento->descripcion }}</p>
                        </div>
                    @else
                        {{-- CAMPOS DE PREGUNTA --}}
                        <div class="card shadow-sm border p-4 mb-4 rounded-3 bg-light bg-opacity-10">
                            <label class="form-label fw-bold fs-6">
                                {{ $elemento->titulo }} 
                                @if($elemento->required) <span class="text-danger">*</span> @endif
                            </label>
                            @if($elemento->descripcion)
                                <p class="small text-muted mb-2">{{ $elemento->descripcion }}</p>
                            @endif

                            @switch($clase)
                                @case('corta')
                                    <input type="text" wire:model.defer="respuestas.{{ $elemento->id }}" class="form-control {{ $errorClass }}">
                                    @break
                                @case('larga')
                                    <textarea wire:model.defer="respuestas.{{ $elemento->id }}" class="form-control {{ $errorClass }}" rows="3"></textarea>
                                    @break
                                @case('si_no')
                                @case('unica_respuesta')
                                    <select wire:model.defer="respuestas.{{ $elemento->id }}" class="form-select {{ $errorClass }}">
                                        <option value="">Seleccione una opción</option>
                                        @foreach ($elemento->opciones as $opcion)
                                            <option value="{{ $opcion->valor_entero }}">{{ $opcion->valor_texto }}</option>
                                        @endforeach
                                    </select>
                                    @break
                                @case('multiple_respuesta')
                                    <div class="row">
                                        @foreach ($elemento->opciones as $opcion)
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="{{ $opcion->valor_entero }}" wire:model.defer="respuestas.{{ $elemento->id }}" id="opt-{{ $opcion->id }}">
                                                    <label class="form-check-label" for="opt-{{ $opcion->id }}">{{ $opcion->valor_texto }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @break
                                @case('fecha')
                                    <input type="date" wire:model.defer="respuestas.{{ $elemento->id }}" class="form-control {{ $errorClass }}">
                                    @break
                                @case('numero')
                                @case('moneda')
                                    <input type="number" wire:model.defer="respuestas.{{ $elemento->id }}" class="form-control {{ $errorClass }}">
                                    @break
                                @case('archivo')
                                @case('imagen')
                                    @if(isset($respuestas[$elemento->id]) && is_string($respuestas[$elemento->id]))
                                        {{-- Mostrar archivo ya subido --}}
                                        <div class="alert alert-secondary d-flex justify-content-between align-items-center">
                                            <span>
                                                <i class="ti {{ $clase == 'archivo' ? 'ti-file' : 'ti-photo' }} fs-4 me-2"></i>
                                                {{ $respuestas[$elemento->id] }}
                                            </span>
                                            <button type="button" class="btn btn-sm btn-danger" wire:click="eliminarRespuesta({{ $elemento->id }})">Cambiar</button>
                                        </div>
                                    @else
                                        {{-- Input para subir nuevo --}}
                                        <input type="file" wire:model="respuestas.{{ $elemento->id }}" class="form-control {{ $errorClass }}" accept="{{ $clase == 'archivo' ? '.pdf,.doc,.docx' : 'image/*' }}">
                                        <div wire:loading wire:target="respuestas.{{ $elemento->id }}" class="mt-2 text-primary small">
                                            <span class="spinner-border spinner-border-sm"></span> Subiendo...
                                        </div>
                                    @endif
                                    @break
                            @endswitch

                            @error('respuestas.'.$elemento->id)
                                <div class="text-danger small mt-1 font-weight-bold">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                @endforeach
            </div>
            @endif
        </div>

        <!-- Barra de Navegación Inferior Fija -->
        <div class="w-100 fixed-bottom py-3 px-4 border-top bg-white z-index-2 shadow-lg">
            <div class="col-12 col-sm-10 offset-sm-1 col-lg-8 offset-lg-2 d-flex justify-content-between align-items-center">
                <div>
                    @if($pasoActual > 1)
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" wire:click="volverPaso">
                        <i class="ti ti-arrow-left me-1"></i> Volver a Carrito
                    </button>
                    @endif
                </div>

                <div>
                    <button type="button" 
                        wire:click="siguientePaso" 
                        wire:loading.attr="disabled"
                        class="btn btn-primary rounded-pill px-5 py-2 fw-bold"
                        @if (empty($carrito)) disabled @endif>
                        
                        <span wire:loading wire:target="siguientePaso">
                            <span class="spinner-border spinner-border-sm me-2"></span> Procesando...
                        </span>
                        
                        <span wire:loading.remove wire:target="siguientePaso">
                            @if($pasoActual < $totalPasos)
                                Continuar a Formulario <i class="ti ti-arrow-right ms-1"></i>
                            @else
                                Finalizar Inscripción <i class="ti ti-check ms-1"></i>
                            @endif
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Overlay de Carga Global -->
    <div wire:loading wire:target="siguientePaso, procesarRegistro" class="loading-overlay flex-column" style="display: none;" wire:loading.class="d-flex">
        <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;"></div>
        <h4 class="text-white mt-4 fw-normal">Estamos guardando tu información y reservando tus cupos...</h4>
        <p class="text-white-50">Por favor, no cierres esta ventana.</p>
    </div>

    <style>
        .loading-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 9999;
            color: white;
            text-align: center;
        }

        .btn[class*=btn-outline-]:disabled, .btn[class*=btn-outline-].disabled {
    background: #e9e9e9 !important;
}

        .minus, .plus {
            border: solid 2px #1977E5 !important;
            border-radius: 50%;
            width: 35px; height: 35px;
            display: flex; justify-content: center; align-items: center;
            color: #1977E5; background: #fff;
            transition: all 0.2s;
        }
        .minus:hover, .plus:hover { background: #1977E5; color: #fff; }

        .step.active .bs-stepper-icon {
            box-shadow: 0 0 15px rgba(59, 113, 254, 0.4);
            transform: scale(1.1);
        }

        .bg-label-claro-success { background: #E8FADF; color: #71DD37; }
        
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
    </style>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('mostrarMensaje', (data) => {
                Swal.fire({
                    title: data[0].titulo,
                    text: data[0].mensaje,
                    icon: data[0].tipo,
                    confirmButtonText: 'Entendido',
                    customClass: { confirmButton: 'btn btn-primary rounded-pill' }
                });
            });
        });
    </script>
</div>
