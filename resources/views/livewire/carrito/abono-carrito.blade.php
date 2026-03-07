<div style="margin: 2% 2%;" class="row align-items-center">
    <div class="p-4 col-lg-12 col-md-10 col-sm-12">
        <!-- Stepper (Paso a Paso) - Versión Mejorada -->
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

                    <!-- Paso 3 - Checkout -->
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
        <!-- Fin del Stepper Mejorado -->

        <div style="margin-bottom: 120px;" class="row">
            @if($pasoActual == 1)
            <div class="col-12" wire:key="step-1">
                <h3 class="fw-semibold p-0">Selecciona tus items</h3>

                <!-- Listado del carrito -->
                <div class="card py-2 px-5 shadow border-top-0 border-1 mb-5 col-xl-12 col-lg-12 col-md-12 col-sm-12">
                    <!-- Lista de categorías para compra -->
                    <ul class="list-group mb-4">

                        @if ($categoriasCompraPermitidas->count() > 0)
                            <li class="list-group-item border-none px-6 py-3">

                                {{-- Si es la primera vez, el usuario debe elegir una categoría --}}
                                @if ($primeraVez)
                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label class="form-label">Elija su categoría para abonar</label>
                                                <select @if ($categoriaAbonoSeleccionada) disabled @endif
                                                    wire:model="categoriaAbonoSeleccionada"
                                                    wire:change="actualizarCategoriaAbonoSeleccionada" class="form-select">
                                                    <option value="">Selecciona una categoría</option>
                                                    @foreach ($categoriasCompraPermitidas as $categoria)
                                                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    {{-- Si ya existe una compra, se muestra la categoría "bloqueada" --}}
                                    <div class="mb-3">
                                        <label class="form-label">Categoría seleccionada:</label>
                                        <h4 class="fw-bold text-primary">
                                            @if ($categoriaAbonoSeleccionada)
                                                {{ $categoriasCompraPermitidas->firstWhere('id', $categoriaAbonoSeleccionada)?->nombre ?? 'No encontrada' }}
                                            @endif
                                        </h4>
                                    </div>
                                @endif

                                @if ($categoriaAbonoSeleccionada)
                                    <div id="container-abonos" class="gap-4 mt-4">
                                        <label class="form-label">Realizar Siguiente Abono</label>
                                        <div class="form-group d-flex mb-10">
                                            {{-- Si el abono actual ya está en el carrito temporal, mostramos la opción de quitarlo --}}
                                            @if (isset($carrito[$categoriaAbonoSeleccionada]))
                                                <div class="d-flex align-items-center">
                                                    <span class="me-3 fs-5">Abono agregado: <b
                                                            class="text-black">{{ Number::currency($carrito[$categoriaAbonoSeleccionada]['precio']) }}</b></span>
                                                    <button
                                                        wire:click="eliminarDelCarritoAbono({{ $categoriaAbonoSeleccionada }})"
                                                        class="btn btn-outline-danger fw-semibold">
                                                        <i class="ti ti-shopping-cart-x"></i> Quitar Abono del Carrito
                                                    </button>
                                                </div>
                                            @else
                                                {{-- Si no está en el carrito, mostramos el input para agregarlo --}}
                                                <input style="width:150px" wire:model.lazy="valorAbono" type="number"
                                                    class="form-control" placeholder="Valor" 
                                                    @if($actividad->pagos_abonos_con_valores_cerrados) disabled @endif />

                                                <button
                                                    wire:click="{{ $primeraVez ? 'agregarAlCarritoAbono' : 'agregarAlCarritoAbonoRetorno' }}({{ $categoriaAbonoSeleccionada }})"
                                                    class="btn ms-3 btn-label-secondary fw-semibold btn-outline-secondary  btn-next">
                                                    <i class="ti ti-shopping-cart-plus"></i> Agregar Abono
                                                </button>
                                            @endif
                                        </div>

                                        @if ($mensajeAbono)
                                            <div class="cuadroInfoGeneral d-flex align-items-center p-3 mt-3 mb-3">
                                                <i style="font-size: xx-large;" class="me-3 ti ti-info-circle"></i>
                                                <div>{!! $mensajeAbono !!}</div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </li>
                        @else
                        @endif

                        {{-- Botón de Actualizar Compra --}}
                        @if (isset($compraActual->id) && $actualizar)
                            <li style="list-style:none; text-align: right;">
                                <button style="width:220px"
                                    wire:click="actualizarCompra({{ $compraActual->id }}, {{ $pagoActual?->id }})"
                                    class="btn ms-3 btn-label-secondary fw-semibold btn-outline-secondary px-7 py-4 btn-next">
                                    Actualizar Compra
                                </button>
                            </li>
                        @endif
                    </ul>
                </div>

                <!-- Sección de monedas y destinatarios -->
                <div class="row">
                    @if (count($actividad->monedas) > 0)
                        <div id="container-moneda" class="p-2 col-lg-6 col-sm-12 mb-3">
                            <div class="form-group">
                                <h6 class="text-dark fw-normal">Elija su moneda de pago</h6>
                                <select @if ($categoriaAbonoSeleccionada) disabled @endif
                                    @if (count($carrito) > 0 || $abonosFinalizados) disabled @endif wire:model.live="monedaSeleccionada"
                                    id="monedaSeleccionada" name="monedaSeleccionada" class="form-select">
                                    @foreach ($actividad->monedas as $moneda)
                                        <option value="{{ $moneda->id }}">{{ $moneda->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    @if ($actividad->destinatarios()->count() > 0)
                        <div id="container-destinatario" class="p-2 col-lg-6 col-sm-12 mb-3">
                            <div class="form-group">
                                <h6 class="text-dark fw-normal">Elija un destinatario</h6>
                                <select @if (count($carrito) > 0) disabled @endif required
                                    wire:model.live="destinatario" id="destinatario" name="destinatario"
                                    class="form-select">
                                    <option value="0">Seleccione un destinatario</option>
                                    @foreach ($actividad->destinatarios as $destinatario)
                                        <option value="{{ $destinatario->id }}">{{ $destinatario->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    <!-- Selector de relaciones familiares -->
                    @if (count($relacionesFamiliares) > 0)
                        <div id="container-menor" class="p-2 col-lg-6 col-sm-12 mb-3">
                            <div class="form-group">
                                <h6 class="text-dark fw-normal">Elija a nombre de quien será la inscripción</h6>
                                <select @if ($categoriaAbonoSeleccionada) disabled @endif
                                    @if (count($carrito) > 0 || $abonosFinalizados) disabled @endif wire:ignore
                                    wire:model="parienteSeleccionado" class="form-select">
                                    <option value="{{ $usuario->id }}">{{ $usuario->primer_nombre }}
                                        {{ $usuario->segundo_nombre }} {{ $usuario->primer_apellido }}</option>
                                    @foreach ($relacionesFamiliares as $pariente)
                                        <option value="{{ $pariente->id }}">{{ $pariente->primer_nombre }}
                                            {{ $usuario->segundo_nombre }} {{ $usuario->primer_apellido }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Lista de campos adicionales -->
                @if (count($camposAdicionalesActividad) > 0)
                    {!! $camposAdicionalesHtml !!}
                @endif

                <!-- Resumen del carrito -->
                <div class="card p-5 border-top-0 border-1 shadow col-xl-12 mt-5 col-lg-8 col-md-12 col-sm-12">
                    <div class="rounded mb-4">
                        <div class="card-header p-0">
                            <h5 class="fw-semibold">Detalles de la compra</h5>
                            <h6>Actividad: {{ $actividad->nombre }}</h6>
                        </div>
                        <h6>Items en tu carrito ({{ count($carrito) }})</h6>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tbody>
                                    @forelse ($carrito as $item)
                                        <tr>
                                            <td class="fw-normal">{{ $item['nombre'] }}</td>
                                            <td class="text-end">
                                                {{ $item['cantidad'] }} x
                                                @if ($item['precio'] > 0)
                                                    {{ Number::currency($item['precio']) }}
                                                    {{ $monedaActual->nombre_corto }}
                                                @else
                                                    <span class="success">Gratis</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2">El carrito está vacío</td>
                                        </tr>
                                    @endforelse

                                    <tr style="border-top:solid 2px #242424">
                                        <td class="text-heading">Total</td>
                                        @if (count($carrito) > 0)
                                            <td class="fw-medium text-end text-heading mb-0">
                                                @if ($total > 0)
                                                    {{ Number::currency($total) }} {{ $monedaActual->nombre_corto }}
                                                @else
                                                    <span class="success fw-semibold">Gratis</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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
                    @if($pasoActual == 1)
                        @if ($actividad->tipo->permite_abonos && $primeraVez)
                            <button class="btn btn-primary rounded-pill px-5 py-2 fw-bold"
                                wire:click="crearAbono" @if (empty($carrito)) disabled @endif>
                                <span wire:loading.remove wire:target="crearAbono">
                                    {{ $totalPasos > 1 ? 'Continuar a Formulario' : 'Continuar con el abono' }} <i class="ti ti-arrow-right ms-1"></i>
                                </span>
                                <span wire:loading wire:target="crearAbono">
                                    <span class="spinner-border spinner-border-sm"></span> Procesando...
                                </span>
                            </button>
                        @elseif(isset($compraActual->id) && !$primeraVez && $actividad->tipo->permite_abonos)
                            <button class="btn btn-primary rounded-pill px-5 py-2 fw-bold"
                                wire:click="crearAbono" @if (empty($carrito)) disabled @endif>
                                <span wire:loading.remove wire:target="crearAbono">
                                    Siguiente <i class="ti ti-arrow-right ms-1"></i>
                                </span>
                                <span wire:loading wire:target="crearAbono">
                                    <span class="spinner-border spinner-border-sm"></span> Procesando...
                                </span>
                            </button>
                        @endif
                    @else
                        <button type="button" 
                            wire:click="finalizarProcesoAbono" 
                            wire:loading.attr="disabled"
                            class="btn btn-primary rounded-pill px-5 py-2 fw-bold">
                            
                            <span wire:loading wire:target="finalizarProcesoAbono">
                                <span class="spinner-border spinner-border-sm me-2"></span> Procesando...
                            </span>
                            
                            <span wire:loading.remove wire:target="finalizarProcesoAbono">
                                Finalizar Inscripción <i class="ti ti-check ms-1"></i>
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- no eliminar este div por favor-->
    <style>
        .minus {
            border: solid 2px #1977E5 !important;
            border-radius: 20px;
            padding: 2px !important;
            width: 31px;
            height: 30px;
            margin-right: 6px;
            color: #1977E5;
            background: transparent;
        }

        .plus {
            border: solid 2px #1977E5 !important;
            border-radius: 20px;
            padding: 2px !important;
            width: 31px;
            height: 30px;
            margin-left: 6px;
            color: #1977E5;
            background: transparent;
        }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }

        body {
            overflow-x: hidden;
            background: #FFF;
        }

        /* Estilos personalizados para el stepper */
        .step {
            margin: 20px;
        }

        .bs-stepper-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .step.active .bs-stepper-icon {

            box-shadow: 0 4px 6px rgba(59, 113, 254, 0.2);
        }

        .step.active .bs-stepper-icon svg {
            fill: white;
            width: 60%;
            height: 60%;
        }

        @media (max-width: 768px) {
            .bs-stepper-icon {
                width: 50px;
                height: 50px;
            }

            .line {
                display: none !important;
            }

            .bs-stepper-label {
                font-size: 0.8rem;
            }
        }
    </style>

    <script>
        Livewire.on('msn', () => {
            Swal.fire({
                title: event.detail.msnTitulo,
                html: event.detail.msnTexto,
                icon: event.detail.msnIcono,
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            });
        });


        document.addEventListener('livewire:initialized', () => {
            Livewire.on('mostrarMensaje', (data) => {
                Swal.fire({
                    title: data[0].titulo,
                    text: data[0].mensaje,
                    icon: data[0].tipo,
                    confirmButtonText: 'OK',
                    cancelButton: true
                });
            });
        });

        document.addEventListener('livewire:initialized', () => {
            Livewire.on('mostrarMensajeMoneda', (data) => {
                Swal.fire({
                    title: data[0].titulo,
                    text: data[0].mensaje,
                    icon: data[0].tipo,
                    confirmButtonText: 'OK',
                    cancelButton: true
                }).then((result) => {

                    // location.reload(); // Recargar la página

                });
            });
        });
    </script>
</div>
