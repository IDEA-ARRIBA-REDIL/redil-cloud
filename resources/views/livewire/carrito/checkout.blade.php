<div >
    {{-- Close your eyes. Count to one. That is how long forever feels. --}}
    <div id="row-completo-container" style="margin:2% 0%;" class="row align-items-center w-90">
        <div id="container-completo" class="p-lg-0 px-md-5 col-lg-12 col-md-10 col-sm-12">
            @if ($configuracion->version == 2)
                <!-- Stepper (Paso a Paso) - Versión Mejorada -->
                <div class="m-lg-auto border-0 mb-4">
                    <div class="d-flex flex-wrap justify-content-center align-items-center gap-3 gap-md-2">
                        <!-- Paso 1 - Carrito (Activo) -->
                        <div class="step  d-flex align-items-center">
                            <button type="button" class="step-trigger bg-transparent border-0 p-0">
                                <span class="bs-stepper-icon bg-light">
                                    <svg viewBox="0 0 60 60" class="w-40px h-40px">
                                        <use
                                            xlink:href="{{ asset('assets/svg/icons/wizard-checkout-cart.svg#wizardCart') }}">
                                        </use>
                                    </svg>
                                </span>
                                <span class="bs-stepper-label d-none d-md-block mt-2">Carrito</span>
                            </button>
                        </div>

                        <div class="line d-none d-md-block">
                            <i class="ti ti-chevron-right fs-5 text-muted"></i>
                        </div>

                        <!-- Paso 2 - Formulario -->
                        <div class="step d-flex align-items-center">
                            <button type="button" class="step-trigger bg-transparent border-0 p-0">
                                <span class="bs-stepper-icon bg-light">
                                    <svg viewBox="0 0 60 60" class="w-40px h-40px">
                                        <use
                                            xlink:href="{{ asset('assets/svg/icons/wizard-checkout-address.svg#wizardCheckoutAddress') }}">
                                        </use>
                                    </svg>
                                </span>
                                <span class="bs-stepper-label d-none d-md-block mt-2 text-muted">Formulario</span>
                            </button>
                        </div>

                        <div class="line d-none d-md-block">
                            <i class="ti ti-chevron-right fs-5 text-muted"></i>
                        </div>

                        <!-- Paso 3 - Checkout -->
                        <div class="step active d-flex align-items-center">
                            <button type="button" class="step-trigger bg-transparent border-0 p-0">
                                <span class="bs-stepper-icon btn-primary">
                                    <svg viewBox="0 0 60 60" class="w-40px h-40px">
                                        <use
                                            xlink:href="{{ asset('assets/svg/icons/wizard-checkout-payment.svg#wizardPayment') }}">
                                        </use>
                                    </svg>
                                </span>
                                <span class="bs-stepper-label d-none d-md-block mt-2 text-muted">Checkout</span>
                            </button>
                        </div>

                        <div class="line d-none d-md-block">
                            <i class="ti ti-chevron-right fs-5 text-muted"></i>
                        </div>

                        <!-- Paso 4 - Confirmación -->
                        <div class="step d-flex align-items-center">
                            <button type="button" class="step-trigger bg-transparent border-0 p-0">
                                <span class="bs-stepper-icon bg-light">
                                    <svg viewBox="0 0 60 60" class="w-40px h-40px">
                                        <use
                                            xlink:href="{{ asset('assets/svg/icons/wizard-checkout-confirmation.svg#wizardConfirm') }}">
                                        </use>
                                    </svg>
                                </span>
                                <span class="bs-stepper-label d-none d-md-block mt-2 text-muted">Compra
                                    Finalizada</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
            <!-- Fin del Stepper Mejorado -->
            <h3 class="fw-semibold p-0">Check Out</h3>
            <div class="ps-4 row">

                <!-- Listado del carrito -->
                @include('layouts.status-msn')
                <div class="col-xl-12 shadow  border-top-0 border-1 rounded col-lg-12 p-0 col-md-12 col-sm-12">
                    <div class="card ">
                        <div class="card-header pb-2">
                            <h5 class="text-start fw-semibold">Información del comprador</h5>

                        </div>
                        <div class="card-body">
                            @auth
                                <div style="margin-bottom:40px !important" id="container-formulario-compra-auth">
                                    <div class="form-group row">

                                        <div class="col-lg-6 col-md-6 mt-2 col-sm-12">
                                            <label class="form-label"> Nombre completo </label>
                                            <div style="min-height: 38px;" type="text" wire:model="nombreComprador"
                                                id='nombreComprador' name='nombreComprador' class="form-control">
                                                {{ $usuarioCompra->nombre(3) }}
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 mt-2 col-sm-12">
                                            <label class="form-label"> Número identifación </label>
                                            <div style="min-height: 38px;" type="text"
                                                wire:model="identificacionComprador" id='identificacionComprador'
                                                name='identificacionComprador' class="form-control">
                                                {{ $usuarioCompra->identificacion }}</div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 mt-2 col-sm-12">
                                            <label class="form-label"> Email </label>
                                            <div style="min-height: 38px;" type="text" id='EmailComprador'
                                                wire:model="EmailComprador" name='EmailComprador' class="form-control">
                                                {{ $usuarioCompra->email }}</div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 mt-2 col-sm-12">
                                            <label class="form-label"> Teléfono / Celular </label>
                                            <div style="min-height: 38px;" type="text" id='telefonoComprador'
                                                wire:model="telefonoComprador" name='telefonoComprador'
                                                class="form-control">
                                                {{ $usuarioCompra->telefono_movil }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div id="container-formulario-compra">
                                    <div class="form-group row">

                                        <div class="col-lg-6 col-md-6 mt-2 col-sm-12">
                                            <label class="form-label">Nombre completo</label>
                                            <input type="text" wire:model.live="nombreComprador"
                                                class="form-control @error('nombreComprador') is-invalid @enderror">
                                            @error('nombreComprador')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-6 col-md-6 mt-2 col-sm-12">
                                            <label class="form-label">Número identificación</label>
                                            <input type="text" wire:model.live="identificacionComprador"
                                                class="form-control @error('identificacionComprador') is-invalid @enderror">
                                            @error('identificacionComprador')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-6 col-md-6 mt-2 col-sm-12">
                                            <label class="form-label">Email</label>
                                            <input type="email" wire:model.live="EmailComprador"
                                                class="form-control @error('EmailComprador') is-invalid @enderror">
                                            @error('EmailComprador')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-6 col-md-6 mt-2 col-sm-12">
                                            <label class="form-label">Teléfono / Celular</label>
                                            <input type="text" wire:model.live="telefonoComprador"
                                                class="form-control @error('telefonoComprador') is-invalid @enderror">
                                            @error('telefonoComprador')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>


                                </div>
                            @endauth

                        </div>
                    </div>
                </div>


                <!-- Metodos de pago-->
                <div style="margin-top:30px !important"
                    class="col-12 mt-5  @if ($mostrarPaymentTabs == false) d-none @endif p-0 shadow rounded border-top-0 border-1">
                    <div>
                        <div class="p-7" id="container-metodos" x-data="{ activeTab: '{{ $tiposPagoActividad->first()?->id ?? 0 }}' }">
                            @if ($tiposPagoActividad->isEmpty())
                                <div class="alert alert-warning mb-0">
                                    <div class="d-flex">
                                        <i class="ti ti-alert-triangle fs-4 me-2"></i>
                                        <div>
                                            <h6 class="alert-heading mb-1">Sin métodos de pago</h6>
                                            <span>Esta actividad no tiene métodos de pago configurados en la base de datos. Completa la configuración antes de continuar.</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <h5 class="text-start">Pasarela de pago</h5>

                            <div id="tabs-tipoPago" class="nav-align-top">
                                <ul class="nav nav-pills row-gap-2" id="paymentTabs" role="tablist">
                                    @foreach ($tiposPagoActividad as $tipo)
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link"
                                                :class="{ 'active': activeTab === '{{ $tipo->id }}' }"
                                                @click="activeTab = '{{ $tipo->id }}'; $refs.radio{{ $tipo->id }}.checked = true; $wire.set('tipoPagoSeleccionado', '{{ $tipo->id }}')"
                                                type="button">
                                                <div class="form-check custom-option custom-option-basic">
                                                    <label
                                                        class="form-check-label custom-option-content form-check-input-payment d-flex gap-4 align-items-center">
                                                        <input x-ref="radio{{ $tipo->id }}" name="metodoPago"
                                                            type="radio" class="form-check-input"
                                                            value="{{ $tipo->id }}"
                                                            wire:model="tipoPagoSeleccionado"
                                                            :checked="activeTab === '{{ $tipo->id }}'">
                                                        <span class="custom-option-body">
                                                            <img src="{{ asset('assets/img/illustrations/' . $tipo->imagen) }}"
                                                                alt="{{ $tipo->nombre }}" width="58">
                                                            <span
                                                                class="ms-4 fw-medium text-heading">{{ $tipo->nombre }}</span>
                                                        </span>
                                                    </label>
                                                </div>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="tab-content px-0 pb-0" id="paymentTabsContent">
                                @foreach ($tiposPagoActividad as $tipo)
                                    <div class="tab-pane fade"
                                        :class="{ 'show active': activeTab === '{{ $tipo->id }}' }"
                                        id="pills-{{ $tipo->id }}" role="tabpanel"
                                        aria-labelledby="pills-{{ $tipo->id }}-tab">
                                        <div class="row g-6">
                                            @if ($tipo->id == 5)
                                                 <div class="col-12">
                                                    <div class="mb-3">

                                                        <label class="form-label fw-semibold">Selecciona el estado del pago:</label>
                                                        <select class="form-select @error('estadoPagoSeleccionado') is-invalid @enderror"
                                                                wire:model="estadoPagoSeleccionado">
                                                            <option value="">-- Seleccionar Estado --</option>
                                                            @foreach($tipo->estadosPago as $estado)
                                                                <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('estadoPagoSeleccionado')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($tipo->key_reservada == 'zona')
                                                <div class="col-12">
                                                    <div class="mb-3">
                                                        <p>Todas las transacciones son seguras y están encriptadas.
                                                            Puedes pagar a través de PSE o tarjetas de crédito.</p>
                                                        <img style="width:320px"
                                                            src="{{ asset('assets/img/illustrations/logostc.png') }}">
                                                        <br>
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($tipo->key_reservada == 'efecty')
                                                <div class="col-12">
                                                    <div class="mb-3">
                                                        <p>{!! $tipo->observaciones !!}</p>
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($tipo->key_reservada == 'paypal')
                                                <div class="col-12">
                                                    <div class="mb-3">
                                                        <p>{!! $tipo->observaciones !!}</p>
                                                    </div>
                                                    <img style="width:100px; margin-left:10px"
                                                        src="{{ asset('assets/img/illustrations/paypal.png') }}">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumen del carrito -->
                <div style="margin-bottom:100px;margin-top:30px" id="right-formulario"
                    class="col-xl-12  shadow border-top-0 border-1 p-0 rounded col-lg-12 col-md-12 col-sm-12">
                    @if (isset($compra->id))
                        <div class="card">
                            <div class="card-header">
                                <h5 class="fw-semibold">Detalles de la compra</h5>
                                <p class="text-black fw-semibold">Actividad: {{ $actividad->nombre }}</p>
                                @if ($actividad->tipo->permite_abonos == false)
                                    <p class="text-dark">Items en tu carrito ({{ $compra->carritos->count() }})</p>
                                @else
                                    <p class="text-dark">Items en tu carrito (1)</p>
                                @endif
                                @auth
                                    <p>Usuario a nombre de quien se hace el registro: {{ $usuarioCompra->nombre(3) }} </p>

                                     @php
                                        $pago = $compra->pagos->first();
                                        $matricula = $pago ? $pago->matricula : null;
                                    @endphp

                                    @if($matricula)
                                        <div class="border rounded p-3 mt-2 bg-light">
                                            @if($matricula->materialSede)
                                                <p class="mb-2">
                                                    <label class="fw-semibold text-black">Sede de envío de material:</label>
                                                    <span class="text-black">{{ $matricula->materialSede->nombre }}</span>
                                                </p>
                                            @endif

                                            @if($matricula->horarioMateriaPeriodo && $matricula->horarioMateriaPeriodo->horarioBase)
                                                @php
                                                    $horario = $matricula->horarioMateriaPeriodo->horarioBase;
                                                    $dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
                                                    $diaTexto = $dias[$horario->dia] ?? 'N/D';
                                                    $horaInicio = \Carbon\Carbon::parse($horario->hora_inicio)->format('h:i A');
                                                    $horaFin = \Carbon\Carbon::parse($horario->hora_fin)->format('h:i A');
                                                @endphp

                                                <p class="mb-2">
                                                    <label class="fw-semibold text-black">Horario:</label>
                                                    <span class="text-black">{{ $diaTexto }} de {{ $horaInicio }} a {{ $horaFin }}</span>
                                                </p>

                                                @if($horario->aula)
                                                    <p class="mb-2">
                                                        <label class="fw-semibold text-black">Aula:</label>
                                                        <span class="text-black">{{ $horario->aula->nombre }}</span>
                                                    </p>
                                                @endif

                                            @endif
                                        </div>
                                    @endif
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="rounded p-1">

                            @if ($actividad->tipo->permite_abonos == false && $actividad->tipo->tipo_escuelas == false)
                                <dl class="row mb-5  border rounded p-4 text-heading">

                                    @foreach ($compra->carritos as $item)
                                        @php
                                            $precio = $item->precio * $item->cantidad;

                                        @endphp
                                        <dt class="col-sm-8">{{ $item->categoria->nombre }}</dt>

                                        <dd class="col-sm-4 fw-medium text-end text-heading">
                                            {{ Number::currency($precio) }}
                                            {{ $moneda->nombre_corto }}
                                        </dd>
                                    @endforeach
                                    <hr>
                                    <dt class="col-sm-8">Total</dt>
                                    <dd class="col-sm-4 fw-medium text-end text-heading mb-0">
                                        {{ Number::currency($compra->valor) }}
                                        {{ $moneda->nombre_corto }} </dd>
                                </dl>
                            @elseif($actividad->tipo->permite_abonos == true && $actividad->tipo->tipo_escuelas == false)
                                {{-- INICIO DE LA CORRECCIÓN: Resumen detallado y claro para Abonos --}}
                                <dl class="row mb-5  border rounded p-4 ">
                                    @php
                                        // Estas variables las preparamos en el método mount() del componente
                                        $totalPagadoAnteriormente = $pagosAnteriores->sum('valor');
                                        $saldoRestante =
                                            $valorTotalCategoria - $totalPagadoAnteriormente - $valorAPagarAhora;
                                    @endphp

                                    {{-- Muestra el costo total del cupo o categoría --}}
                                    <dt class="col-sm-8">
                                        <h6 class="fw-semibold"> Valor Total del Evento</h6>
                                    </dt>
                                    <dd class="col-sm-4 fw-medium text-black  text-end">
                                        {{ Number::currency($valorTotalCategoria, $moneda->nombre_corto) }}
                                    </dd>

                                    {{-- Muestra lo que ya se ha pagado, si aplica --}}
                                    @if ($totalPagadoAnteriormente > 0)
                                        <dt class="col-sm-8 text-success">Pagos Anteriores</dt>
                                        <dd class="col-sm-4 fw-medium text-end text-success">
                                            - {{ Number::currency($totalPagadoAnteriormente, $moneda->nombre_corto) }}
                                        </dd>
                                    @endif

                                    @if ($saldoRestante > 0)
                                        <tr style="border-top:solid 2px #e9ecef; margin-top:10px; padding-top:10px">
                                            <dt class="col-sm-8 text-danger pt-2">Saldo Restante</dt>
                                            <dd class="col-sm-4 fw-medium text-end text-danger pt-2">
                                                {{ Number::currency($saldoRestante, $moneda->nombre_corto) }}
                                            </dd>
                                        </tr>
                                    @endif

                                    <hr>

                                    {{-- Muestra de forma destacada el pago que se está por realizar --}}
                                    <dt class="col-sm-8 ">
                                        <h6 class="fw-semibold">Abono a Realizar Hoy </h6>
                                    </dt>
                                    <dd class="col-sm-4 fw-bold text-black text-end mb-0">
                                        {{ Number::currency($valorAPagarAhora, $moneda->nombre_corto) }}
                                    </dd>

                                    {{-- Muestra el saldo que quedará después de este pago --}}

                                </dl>
                                {{-- FIN DE LA CORRECCIÓN --}}
                            @elseif($actividad->tipo->permite_abonos == false && $actividad->tipo->tipo_escuelas == true)
                                <h5 class="fw-semibold"> Sistema de gestión de escuelas </h5>
                                <dl class="row mb-5  border rounded p-4 text-heading">

                                    @foreach ($compra->carritos as $item)
                                        @php
                                            $precio = $item->precio * $item->cantidad;

                                        @endphp
                                        <dt class="col-sm-8">{{ $item->categoria->nombre }}</dt>

                                        <dd class="col-sm-4 fw-medium text-end text-heading">
                                            {{ Number::currency($precio) }}
                                            {{ $moneda->nombre_corto }}
                                        </dd>
                                    @endforeach
                                    <hr>
                                    <dt class="col-sm-8">Total</dt>
                                    <dd class="col-sm-4 fw-medium text-end text-heading mb-0">
                                        {{ Number::currency($compra->valor) }}
                                        {{ $moneda->nombre_corto }} </dd>


                                </dl>
                            @endif
                            @if ($compra->camposAdicionales->count() > 0)
                                <h5 class="fw-semibold">Campos adicionales</h5>
                                <ul>
                                    @foreach ($compra->camposAdicionales as $campo)
                                        <li>
                                            <strong>{{ $campo->campoAdicional->nombre }}:</strong> {{ $campo->respuesta }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                 {{-- TÉRMINOS Y CONDICIONES --}}
                 @if ($actividad->terminos_y_condiciones)
                 <div style="margin-bottom:30px; margin-top:30px" class="col-12 shadow border-top-0 border-1 p-0 rounded">
                     <div class="card">
                         <div class="card-header bg-light">
                             <h5 class="fw-semibold mb-0">Términos y Condiciones</h5>
                         </div>
                         <div class="card-body pt-3">
                             <div class="alert alert-light border">
                                 <p class="mb-0 text-muted">
                                     {!! Str::limit(strip_tags(html_entity_decode($actividad->terminos_y_condiciones)), 200) !!}
                                 </p>
                             </div>

                             <div class="form-check mt-3">
                                 <input class="form-check-input @error('aceptarTerminos') is-invalid @enderror"
                                        type="checkbox"
                                        value=""
                                        id="checkTerminos"
                                        wire:model.live="aceptarTerminos">
                                 <label class="form-check-label user-select-none fw-semibold" for="checkTerminos">
                                     He leído y acepto los términos y condiciones de esta actividad.
                                 </label>
                                 @error('aceptarTerminos')
                                     <div class="invalid-feedback d-block">
                                         {{ $message }}
                                     </div>
                                 @enderror
                             </div>
                         </div>
                     </div>
                 </div>
                 @endif

            </div>

        </div>

        <div class="w-100 fixed-bottom py-5 px-6 px-sm-0 border-top" style="background-color: #FFF">
            @if ($actividad->tipo->permite_abonos == false)
                <div class="col-12  col-lg-8 m-sm-auto offset-lg-2">
                    <a wire:click="redirigirAtras" style="width:160px"
                        class="  btn float-lg-start  ms-5 me-5 btn-outline-secondary rounded-pill btn-next btn-moviles">
                        Anterior
                    </a>
                    <button  style="width:160px" class="  m-sm-auto float-lg-end btn  ms-5 me-5 btn-primary rounded-pill btn-next btn-moviles" wire:click="procesarPago"
                        @if($actividad->terminos_y_condiciones && !$aceptarTerminos) disabled @endif
                        class=" mt-3 me-5 rounded-pill btn btn-primary btn-next">
                        Pagar
                    </button>
                </div>
            @elseif($actividad->tipo->permite_abonos == true)
                <div class="col-12  col-lg-8  offset-lg-2">
                    <a style="width:160px" wire:click="redirigirAtrasAbono"
                        class="  btn float-lg-start  m-sm-auto  ms-5 me-5 btn-outline-secondary rounded-pill btn-next btn-moviles">
                        Anterior Abono
                    </a>
                    <button style="width:160px" class="  m-sm-auto  float-lg-end btn  ms-5 me-5 btn-primary rounded-pill btn-next btn-moviles" wire:click="procesarPago"
                        @if($actividad->terminos_y_condiciones && !$aceptarTerminos) disabled @endif
                        class=" mt-3 me-5 rounded-pill btn btn-primary btn-next">
                        Pagar Abono
                    </button>
                </div>
            @endif
        </div>
        <style>
            #tabs-tipoPago .nav-pills .nav-link.active,
            .nav-pills .nav-link.active:hover,
            .nav-pills .nav-link.active:focus {
                background-color: #ffffff !important;
                color: #fff !important;
                border: solid 0;
                box-shadow: none;
            }

            #tabs-tipoPago .nav-pills .nav-item .nav-link:not(.active):hover {
                border-bottom: none;
                padding-bottom: .5435rem;
                background-color: #ffffff00 !important;
                color: #000000 !important;
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

            @media (max-width: 508px) {
                .btn-moviles{
                    margin-bottom: 10px !important;

                    width: 90% !important;
                    float: none !important;
                }

                #row-completo-container{
                    width: 95% !important;
                }
            }
        </style>

        <script>
            $('.nav-link').on('click', function() {

                const id = $(this).data('id');
                $('#pills-' + id).addClass('d-none');

            });

            document.addEventListener('livewire:initialized', () => {
                Livewire.on('mostrarMensaje', (data) => {
                    Swal.fire({
                        title: data[0].titulo,
                        html: data[0].mensaje,
                        icon: data[0].tipo,
                        confirmButtonText: 'OK',
                        cancelButton: false
                    });
                });
            });

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
        </script>
    </div>
    <!-- Checkout Wizard -->


    </div>
