@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
    // Obtenemos la primera inscripción como referencia
    $inscripcion = $compra->inscripciones->first();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Recibo de Compra')

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        #container-completo {
            min-height: 50vh;
            height: auto;
        }

        body {
            overflow-x: hidden;
        }
    </style>
@endsection

@section('content')

    @php
        $esAbono = $compra->actividad->tipo->permite_abonos ?? false;
        if ($esAbono) {
            // Asumimos estado 3 = Aprobado
            $totalPagado = $compra->pagos->where('estado_pago_id', 3)->sum('valor');
            $saldoPendiente = $compra->valor - $totalPagado;

            if ($saldoPendiente <= 0) {
                $mensaje = '¡Felicitaciones! Has pagado la totalidad del valor de la compra.';
            } else {
                // Usamos la clase Number directamente
                $mensaje =
                    'Aún debes un valor de ' .
                    Number::currency($saldoPendiente, $compra->moneda->nombre_corto) .
                    '. Tu inscripción solo será confirmada cuando el total del pago se complete.';
            }
        }
    @endphp

    <div class="row card">
        <div class="col-lg-8 col-xl-8 mx-auto">
            <div class="card p-4">
                <div class="card-header row">
                    {{-- 1. Encabezado de Éxito (Simplificado de tu ejemplo) --}}
                    <div class="text-center mb-4 col-12">
                        <img style="width: 240px; height: 240px;"
                            src="{{ Storage::url('generales/img/otros/dibujo_formulario_usuario_respuesta.png') }}"
                            class="p-0">

                        @if ($compra->estado == 3 || $esAbono)
                            {{-- 3 = Pagada --}}
                            <h2 class="text-black fw-bold mb-0 lh-sm mt-3">{{ $titulo }}</h2>
                            <h3 style="color: {{ $colorEncabezado }};" class="fw-bold mb-1">
                                @if ($esAbono)
                                    ¡Pago registrado exitosamente!
                                @else
                                    ¡Felicidades!
                                @endif
                            </h3>
                            <p>
                                @if ($esAbono)
                                    {{ $mensaje }}
                                @else
                                    Hemos enviado una copia de este recibo y tu ticket de ingreso a:
                                    <strong>{{ $compra->email_comprador }}</strong>
                                @endif
                            </p>
                        @else
                            <h2 class="text-black fw-bold mb-0 lh-sm mt-3">Estado pendiente</h2>
                            <h3 style="color: {{ $colorEncabezado }};" class="fw-bold mb-1">Tu pago está siendo
                                procesado
                            </h3>
                            <p>Recibirás una confirmación por correo una vez se apruebe.</p>
                        @endif
                    </div>
                </div>

                <div class="row px-4 mb-0">
                    <div class="col-12 text-md-start text-sm-start ">
                        <span class="d-block text-dark">Actividad: </span>
                        <h5 class="fw-semibold text-black">{{ $compra->actividad->nombre }}</h5>
                    </div>
                </div>

                <div class="card-body p-sm-5">
                    {{-- 2. Detalles de la Inscripción (Adaptado de tu ejemplo) --}}
                    <div id="card-detalles" class="row px-6 py-9 rounded mb-5 shadow border-top-0 border-5 ">

                        <div class="mb-4 col-md-10 col-12">
                            <h5 class="fw-semibold">Detalles de la inscripción</h5>
                            <dl class="row mb-5 p-4 text-heading">

                                {{-- Iteramos sobre las INSCripciones, no los carritos --}}
                                @foreach ($compra->inscripciones as $insc)
                                    <dt class="col-md-8 col-12">
                                        <span class="d-block text-black mb-2">Inscrito:
                                            <strong>{{ $insc->user->nombre(3) }}</strong></span>
                                        <span class="d-block text-black mb-2">{{ $insc->categoriaActividad->nombre }}</span>
                                    </dt>
                                    <dd class="col-md-4 col-12 fw-medium text-md-end text-sm-start text-heading">
                                        {{-- El precio lo tomamos del total de la compra --}}
                                    </dd>
                                @endforeach
                                <hr>
                                @if ($esAbono)
                                    <dt class="col-md-8 col-12 text-black   fw-bold">Abono a compra por total de
                                        {{ Number::currency($compra->valor, $compra->moneda->nombre_corto) }}</dt>
                                @else
                                    <dt class="col-md-8 col-12 text-black   fw-bold">Total Compra</dt>
                                    <dd
                                        class="col-md-4 col-12 text-black fw-medium text-md-end text-sm-start text-heading mb-0">
                                        <span class="fw-bold">
                                            {{ Number::currency($compra->valor, $compra->moneda->nombre_corto) }}
                                        </span>
                                    </dd>
                                @endif

                            </dl>
                        </div>
                        <div class="text-center mb-4 col-md-2 col-12">
                            {{-- Usamos el QR generado en el controlador --}}
                            <img src="data:image/png;base64,{{ $qrCodeBase64 }}" style="width: 140px; height: 140px;"
                                alt="QR Code" />

                        </div>

                    </div>


                    {{-- 2.5. Detalles de Matrícula (ESCUELAS) --}}
                    @if (isset($matricula) && $matricula)
                        <div class="row px-6 py-9 rounded mb-5 shadow border-top-0 border-5 ">
                            <h5 class="fw-semibold">Detalles de la Matrícula</h5>
                            <div class="row equal-cols">
                                <div class="col-md-6  equal-col-height col-12">

                                    <p class="p-0 m-0 text-black">Horario</p>
                                    @php
                                        $horarioBase = $matricula->horarioMateriaPeriodo->horarioBase;
                                        $dias = [
                                            1 => 'Lun',
                                            2 => 'Mar',
                                            3 => 'Mié',
                                            4 => 'Jue',
                                            5 => 'Vie',
                                            6 => 'Sáb',
                                            7 => 'Dom',
                                        ];
                                        $dia = $dias[$horarioBase->dia] ?? 'N/D';
                                        $ini = \Carbon\Carbon::parse($horarioBase->hora_inicio)->format('h:i A');
                                        $fin = \Carbon\Carbon::parse($horarioBase->hora_fin)->format('h:i A');
                                        $aula = $horarioBase->aula->nombre ?? 'N/D';
                                    @endphp
                                    <p class="pb-2 fw-bold text-black ">
                                        {{ $dia }} | {{ $ini }} - {{ $fin }}

                                    </p>
                                    <hr>
                                </div>
                                <div class="col-md-6 equal-col-height col-12">

                                    <p class="p-0 m-0 text-black ">

                                        Aula:
                                    </p>
                                    <p class="pb-2 fw-bold text-black ">{{ $aula }}</p>
                                    <hr>
                                </div>
                                <div class="col-md-6   equal-col-height col-12">

                                    <p class="p-0 m-0 text-black">Sede para envío de material</p>
                                    <p class="pb-2 fw-bold text-black ">
                                        {{ $matricula->materialSede->nombre ?? 'N/A' }}
                                    </p>

                                    <hr>
                                </div>

                            </div>
                        </div>
                    @endif

                    {{-- 3. Información del Comprador (Adaptado de tu ejemplo) --}}
                    <div class="row px-6 py-9 rounded mb-5 shadow border-top-0 border-5 ">
                        <h5 class="fw-semibold">Información del comprador</h5>
                        <div class="row ">
                            <div class="col-md-6 col-12">
                                <p class="p-0 m-0 text-black">Nombre completo</p>
                                <p class="pb-2 fw-bold text-black border-bottom">
                                    {{ $compra->nombre_completo_comprador }}</p>
                            </div>
                            <div class="col-md-6 col-12">
                                <p class="p-0 m-0 text-black">Correo</p>
                                <p class="pb-2 fw-bold text-black border-bottom">
                                    {{ $compra->email_comprador }}</p>
                            </div>
                            <div class="col-md-6 col-12">
                                <p class="p-0 m-0 text-black">Identificación</p>
                                <p class="pb-2 fw-bold text-black border-bottom">
                                    {{ $compra->identificacion_comprador }}</p>
                            </div>
                            <div class="col-md-6 col-12">
                                <p class="p-0 m-0 text-black">Teléfono</p>
                                <p class="pb-2 fw-bold text-black border-bottom">
                                    {{ $compra->telefono_comprador }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Información de los Pagos (¡NUEVO!) --}}
                    <div class="row px-6 py-9 rounded mb-5 shadow border-top-0 border-5 ">
                        <h5 class="fw-semibold">Información de los pagos realizados</h5>
                        <div class="row border-bottom">
                            @foreach ($compra->pagos as $pago)
                                <div class="col-md-4 col-12 ">
                                    <p class="p-0 m-0 text-black">Pago #{{ $pago->id }}
                                        ({{ $pago->tipoPago->nombre }})
                                    </p>
                                    <p class="pb-2 fw-bold text-black ">
                                        {{ Number::currency($pago->valor, $compra->moneda->nombre_corto) }}
                                    </p>
                                </div>
                                <div class="col-md-4 col-12 ">
                                    <p class="p-0 m-0 text-black">Fecha</p>
                                    <p class="pb-2 fw-bold text-black ">
                                        {{ $pago->created_at->format('d/m/Y h:i A') }}</p>
                                </div>
                                <div class="col-md-4 col-12">
                                    @if ($pago->estadoPago)
                                        <p class="pb-2 m-0 text-black">Estado</p>
                                        <p class="pb-2 fw-bold text-black ">
                                            <span class="fw-semibold small mt-2 rounded-pill px-2 py-1"
                                                style="background-color: {{ $pago->estadoPago->color ?? '#fff' }}; color: #fff;">
                                                {{ $pago->estadoPago->nombre }}
                                            </span>
                                        </p>
                                    @endif
                                </div>
                                @if ($pago->codigo_vaucher)
                                    <div class="col-12">
                                        <p class="p-0 m-0 text-black    small">Voucher (Datafono):
                                            {{ $pago->codigo_vaucher }}</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        {{-- Información de la Iglesia (de tu ejemplo) --}}
                        <div class="col-md-6 col-12 text-center mt-5">
                            <p style="font-size:10px" class="mb-2"> {{ $iglesia->nombre }}</p>
                            <p style="font-size:10px" class="mb-2">Direccion: {{ $iglesia->direccion }}</p>
                            <p style="font-size:10px" class="mb-2">PBX : {{ $iglesia->telefono1 }}</p>
                        </div>
                        <div class="col-md-6 col-12 text-center mt-5 ">
                            <p style="font-size:10px" class="mb-2"> Teléfono: {{ $iglesia->telefono1 }}</p>
                            <p style="font-size:10px" class="mb-2"> Nit: {{ $iglesia->identificacion }}</p>
                            <p style="font-size:10px" class="mb-2"> E-mail: {{ $iglesia->email_soporte }}</p>
                        </div>
                    </div>
                </div>

            </div>
            <div class="row mb-5">
                <div class="col-12 text-center">

                    {{-- Botón para volver a la taquilla --}}
                    @php
                        $cajaId = $compra->pagos->first()->registro_caja_id ?? null;
                    @endphp
                    @if ($cajaId)
                        <a href="{{ route('taquilla.operar', $cajaId) }}" class="btn btn-primary rounded-pill px-4">
                            <i class="ti ti-arrow-left me-1"></i>
                            Volver a la Taquilla
                        </a>
                    @else
                        <a href="{{ route('taquilla.mis-cajas') }}" class="btn btn-primary rounded-pill px-4">
                            <i class="ti ti-arrow-left me-1"></i>
                            Volver a Mis Cajas
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
