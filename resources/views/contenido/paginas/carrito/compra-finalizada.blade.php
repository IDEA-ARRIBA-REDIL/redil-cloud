@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
    $esPdf = $esPdf ?? false; // Asegurar que variable exista
@endphp


@if(!$esPdf)
@extends('layouts/blankLayout')
@else
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Comprobante de Pago</title>
    <style>
        body { font-family: sans-serif; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .row { display: block; width: 100%; clear: both; }
        .col-12 { width: 100%; }
        .col-md-6 { width: 50%; float: left; }
        .mb-2 { margin-bottom: 0.5rem; }
        .p-4 { padding: 1.5rem; }
        .card { border: 1px solid #ddd; }
        /* Agrega más estilos básicos si es necesario para PDF */
    </style>
</head>
<body>
@endif

@section('title', 'Recibo')

@section('vendor-style')


@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])

    <style>
        body {
            margin: 0;
            /* Elimina los márgenes predeterminados del cuerpo y html */
            padding: 0;
        }

        #container-completo {
            min-height: 50vh;
            height: auto;
        }

        body {
            overflow-x: hidden;
        }

         @media (max-width: 508px) {
                .btn-moviles{
                    margin-bottom: 10px !important;

                    width: 90% !important;
                    float: none !important;
                }


            }
    </style>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/apex-charts/apexcharts.js'])
@endsection


@section('page-script')
    @vite(['resources/assets/js/form-basic-inputs.js'])



@endsection

@section('content')

    <div class="row card">
        <div class="col-lg-10 col-xl-9 mx-auto">
            <div class="card p-4">
                <div class ="card-header row">
                    @if ($configuracion->version == 1)
                        <div class="text-center mb-4 col-12">
                            {{-- La imagen ahora usa la variable $icono que viene del controlador --}}
                            <img style="width: 240px; height: 240px;"
                                src="{{ Storage::url('generales/img/otros/dibujo_formulario_usuario_respuesta.png') }}"
                                class="p-0">

                            {{-- El título ahora usa el color en un estilo en línea --}}
                            @if ($pago->estadoPago->estado_final_inscripcion)
                                <h2 class="text-black fw-bold mb-0 lh-sm mt-3">Felicidades </h2>
                                <h3 style="color: {{ $colorEncabezado }};" class="fw-bold mb-1">{{ $titulo }}</h3>
                                <p>{{ $mensaje }}</p>
                            @endif
                            @if ($pago->estadoPago->estado_anulado_inscripcion)
                                <h2 class="text-black fw-bold mb-0 lh-sm mt-3">Lo lamentamos no pudimos procesar tu pago,
                                    puedes intentar en 20 minutos </h2>
                                <h3 style="color: {{ $colorEncabezado }};" class="fw-bold mb-1">{{ $titulo }}</h3>
                                <p>{{ $mensaje }}</p>
                            @endif
                            @if ($pago->estadoPago->estado_inicial_defecto)
                                <h2 class="text-black fw-bold mb-0 lh-sm mt-3">Tu pago esta siendo procesado en este
                                    momento, consulta en 1 hora nuevamente. </h2>
                                <h3 style="color: {{ $colorEncabezado }};" class="fw-bold mb-1">{{ $titulo }}</h3>
                                <p>{{ $mensaje }}</p>
                            @endif
                        </div>
                    @else
                        <div class="col-md-6  text-center col-12">
                            <img style="width: 140px; height: 140px;"
                                src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/iglesia/' . $iglesia->logo) }}"
                                class="p-0">
                        </div>
                        <div class="text-center mb-4 col-md-6 col-12">
                            {{-- La imagen ahora usa la variable $icono que viene del controlador --}}
                            <img style="width: 140px; height: 140px;"
                                src="{{ Storage::url('generales/img/otros/dibujo_formulario_usuario_respuesta.png') }}"
                                class="p-0">
                        </div>

                        <div class="col-12 text-center mb-4">
                            {{-- El título ahora usa el color en un estilo en línea --}}
                            <h3 style="color: {{ $colorEncabezado }};" class="fw-bold mb-1">{{ $titulo }}</h3>
                            <p>{{ $mensaje }}</p>
                        </div>
                    @endif


                </div>
                <div class="row px-4 mb-0">
                    <div class="col-12 text-md-start text-sm-start ">
                        <h5 class="fw-semibold text-black">{{ $pago->compra->actividad->nombre }}</h5>
                    </div>
                </div>

                <div class="card-body p-sm-5">
                    <div id="card-materias" class="row  px-6 py-9 rounded  mb-5 shadow border-top-0 border-5 ">

                        <div class="mb-4 col-md-10 col-12">
                            <h5 class="fw-semibold"> Detalles de la compra </h5>
                            <dl class="row mb-5 p-4 text-heading">

                                @foreach ($pago->compra->carritos as $item)
                                    @php
                                        $precio = $item->precio * $item->cantidad;

                                    @endphp
                                    <dt class="col-md-8 col-12">{{ $item->categoria->nombre }}</dt>

                                    <dd class="col-md-4 col-12 fw-medium text-md-end text-sm-start text-heading">
                                        {{ $item->cantidad }} x {{ Number::currency($precio) }}
                                        {{ $pago->moneda->nombre_corto }}
                                    </dd>
                                @endforeach
                                <hr>
                                <dt class="col-md-8 col-12 fw-bold">Total</dt>
                                <dd class="col-md-4 col-12 fw-medium text-md-end text-sm-start text-heading mb-0">

                                    <span class="fw-bold"> {{ Number::currency($pago->compra->valor) }}
                                        {{ $pago->moneda->nombre_corto }} </span>
                                </dd>
                            </dl>

                             {{-- DETALLES DE MATRÍCULA (Solo si es tipo escuela) --}}
                            @if ($pago->compra->actividad->tipo->tipo_escuelas && $pago->matricula)
                                <div class="mt-4 p-4 border rounded bg-light">
                                    <h5 class="fw-semibold mb-3 text-black">Detalles de la matrícula</h5>
                                    <div class="row">
                                        @if($pago->matricula->escuela)
                                            <div class="col-md-6 mb-2 text-black">
                                                <strong>Escuela:</strong> {{ $pago->matricula->escuela->nombre }}
                                            </div>
                                        @endif

                                        @if($pago->matricula->sede)
                                            <div class="col-md-6 mb-2 text-black">
                                                <strong>Sede de Clase:</strong> {{ $pago->matricula->sede->nombre }}
                                            </div>
                                        @endif

                                        @if($pago->matricula->materialSede)
                                            <div class="col-md-6 mb-2 text-black">
                                                <strong>Sede Material:</strong> {{ $pago->matricula->materialSede->nombre }}
                                            </div>
                                        @endif

                                        @if($pago->matricula->horarioMateriaPeriodo)
                                            @php
                                                $hmp = $pago->matricula->horarioMateriaPeriodo;
                                                $horarioBase = $hmp->horarioBase;
                                            @endphp
                                            <div class="col-md-6 mb-2 text-black">
                                                <strong>Materia:</strong> {{ $hmp->materiaPeriodo->materia->nombre ?? 'N/D' }}
                                            </div>

                                            @if($horarioBase)
                                                @php
                                                     $dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
                                                     $dia = $dias[$horarioBase->dia] ?? 'N/D';
                                                     $inicio = \Carbon\Carbon::parse($horarioBase->hora_inicio)->format('h:i A');
                                                     $fin = \Carbon\Carbon::parse($horarioBase->hora_fin)->format('h:i A');
                                                @endphp
                                                <div class="col-12 mb-2   text-black">
                                                    <strong>Horario:</strong> {{ $dia }} de {{ $inicio }} a {{ $fin }}
                                                    @if($horarioBase->aula)
                                                        <br><strong>Aula:</strong> {{ $horarioBase->aula->nombre }} ({{ $horarioBase->aula->sede->nombre ?? '' }})
                                                    @endif
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="text-center mb-4 col-md-2 col-12">
                            <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($datosParaQr . '', 'QRCODE') }}"
                                style="width: 140px; height: 140px;" alt="barcode" />
                        </div>

                    </div>
                    <div class="row  px-6 py-9 rounded  mb-5 shadow border-top-0 border-5 ">


                        <h5 class="fw-semibold"> Información del comprador </h5>
                        @if ($pago->compra->user)
                            <div class="row ">
                                <div class="col-md-6 col-12">
                                    <p class="p-0 m-0">Nombres</p>
                                    <p class="pb-2 fw-bold text-black border-bottom">
                                        {{ $pago->compra->user->primer_nombre }}
                                        {{ $pago->compra->user->segundo_nombre }}</p>
                                </div>
                                <div class="col-md-6 col-12 ">
                                    <p class="p-0 m-0">Apellidos</p>
                                    <p class="pb-2 fw-bold text-black border-bottom">
                                        {{ $pago->compra->user->primer_apellido }}
                                        {{ $pago->compra->user->segundo_apellido }}</p>
                                </div>
                            </div>
                            <div class="row border-bottom">
                                <div class="col-md-6 col-12">
                                    <p class="p-0 m-0">Correo</p>
                                    <p class="pb-2 fw-bold text-black border-bottom">{{ $pago->compra->user->email }}
                                    </p>
                                </div>
                                <div class="col-md-6 col-12">
                                    <p class="p-0 m-0">Identificación</p>
                                    <p class="pb-2 fw-bold text-black border-bottom">
                                        {{ $pago->compra->user->identificacion }}</p>
                                </div>
                                <div class="col-md-6 col-12">
                                    <p class="p-0 m-0">Teléfono:</p>
                                    <p class="pb-2 fw-bold text-black ">
                                        {{ $pago->compra->user->telefono_movil }}</p>
                                </div>

                            </div>
                        @else
                            <div class="row ">
                                <div class="col-md-6 col-12">
                                    <p class="p-0 m-0">Nombre completo</p>
                                    <p class="pb-2 fw-bold text-black border-bottom">
                                        {{ $pago->compra->nombre_completo_comprador }}</p>
                                </div>
                                <div class="col-md-6 col-12">
                                    <p class="p-0 m-0">Correo </p>
                                    <p class="pb-2 fw-bold text-black border-bottom">
                                        {{ $pago->compra->email_comprador }}</p>
                                </div>
                                <div class="col-md-6 col-12">
                                    <p class="p-0 m-0">Identificación</p>
                                    <p class="pb-2 fw-bold text-black border-bottom">
                                        {{ $pago->compra->identificacion_comprador }}</p>
                                </div>
                                <div class="col-md-6 col-12">
                                    <p class="p-0 m-0">Teléfono</p>
                                    <p class="pb-2 fw-bold text-black border-bottom">
                                        {{ $pago->compra->telefono_comprador }}</p>
                                </div>
                            </div>
                        @endif


                    </div>
                    <div class="row  px-6 py-9 rounded  mb-5 shadow border-top-0 border-5 ">

                        <h5 class="fw-semibold"> Información de la compra </h5>
                        <div class="row border-bottom">
                            <div class="col-md-6 col-12 ">
                                <p class="p-0 m-0">Pago #</p>
                                <p class="pb-2 fw-bold text-black border-bottom">
                                    {{ $pago->id }}</p>
                            </div>
                            <div class="col-md-6 col-12 ">
                                <p class="p-0 m-0">Fecha</p>
                                <p class="pb-2 fw-bold text-black border-bottom">
                                    {{ $pago->created_at->format('d/m/Y') }}</p>
                            </div>
                            <div class="col-md-6 col-12">
                                @if ($pago->estadoPago)
                                    <p class="pb-2 m-0">Estado</p>
                                    <p class="pb-2 fw-bold text-black ">
                                        <span class="fw-semibold mt-2 rounded-pill px-2 py-1"
                                            style="background-color: {{ $pago->estadoPago->color ?? '#fff' }}; color: #fff;">
                                            {{ $pago->estadoPago->nombre }}
                                        </span>
                                    </p>
                                @endif
                            </div>
                        </div>

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
            @if(!$esPdf)
            <div class="row  mb-5">
                <div class="col-12 text-center">
                    <a href="{{ route('carrito.descargarComprobante', $pago->id) }}" class="btn btn-outline-danger btn-moviles me-2 rounded-pill px-4">
                        <i class="ti ti-file-type-pdf me-1"></i>
                        Descargar comprobante
                    </a>

                    <a href="{{ route('actividades.proximas') }}" class="btn btn-primary btn-moviles rounded-pill px-4">
                        <i class="ti ti-arrow-left me-1"></i>
                        Volver a las actividades
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
@if(!$esPdf)
@endsection
@else
</body>
</html>
@endif
