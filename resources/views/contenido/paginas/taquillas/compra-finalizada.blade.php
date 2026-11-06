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
                       

                        @if ($compra->estado == 3 || $esAbono)
                         <img style="width: 240px; height: 240px;"
                            src="{{ Storage::disk('global_media')->url('Reserva-exitosa.png') }}"
                            class="p-0">
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
                         <img style="width: 240px; height: 240px;"
                            src="{{ Storage::disk('global_media')->url('Resumen-exitoso.png') }}"
                            class="p-0">

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

                                {{-- Iteramos sobre las inscripciones si existen, o sobre las matrículas si es escuela --}}
                                @if ($compra->inscripciones->isNotEmpty())
                                    @foreach ($compra->inscripciones as $insc)
                                        <dt class="col-md-8 col-12">
                                            <span class="d-block text-black mb-2">Inscrito:
                                                <strong>{{ $insc->user?->nombre(3) ?? $compra->nombre_completo_comprador }}</strong></span>
                                            <span class="d-block text-black mb-2">{{ $insc->categoriaActividad?->nombre }}</span>
                                        </dt>
                                        <dd class="col-md-4 col-12 fw-medium text-md-end text-sm-start text-heading">
                                            {{-- El precio lo tomamos del total de la compra --}}
                                        </dd>
                                    @endforeach
                                @elseif (isset($matriculas) && $matriculas->isNotEmpty())
                                    @foreach ($matriculas as $matItem)
                                        @php
                                            $materiaItem = $matItem->horarioMateriaPeriodo?->horarioBase?->materia?->nombre 
                                                ?? $matItem->horarioMateriaPeriodo?->materiaPeriodo?->materia?->nombre 
                                                ?? 'Matrícula Escuela';
                                            $alumnoItem = $matItem->user?->nombre(3) ?? ($matItem->user?->name ?? $compra->nombre_completo_comprador);
                                        @endphp
                                        <dt class="col-md-8 col-12">
                                            <span class="d-block text-black mb-1">Alumno matriculado:
                                                <strong>{{ $alumnoItem }}</strong></span>
                                            <span class="d-block text-muted small mb-2"><i class="ti ti-book me-1"></i>{{ $materiaItem }}</span>
                                        </dt>
                                        <dd class="col-md-4 col-12 fw-medium text-md-end text-sm-start text-heading">
                                        </dd>
                                    @endforeach
                                @elseif (isset($matricula) && $matricula)
                                    @php
                                        $materiaItem = $matricula->horarioMateriaPeriodo?->horarioBase?->materia?->nombre 
                                            ?? $matricula->horarioMateriaPeriodo?->materiaPeriodo?->materia?->nombre 
                                            ?? 'Matrícula Escuela';
                                        $alumnoItem = $matricula->user?->nombre(3) ?? ($matricula->user?->name ?? $compra->nombre_completo_comprador);
                                    @endphp
                                    <dt class="col-md-8 col-12">
                                        <span class="d-block text-black mb-1">Alumno matriculado:
                                            <strong>{{ $alumnoItem }}</strong></span>
                                        <span class="d-block text-muted small mb-2"><i class="ti ti-book me-1"></i>{{ $materiaItem }}</span>
                                    </dt>
                                    <dd class="col-md-4 col-12 fw-medium text-md-end text-sm-start text-heading">
                                    </dd>
                                @endif
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
                    @php
                        $coleccionMatriculas = (isset($matriculas) && $matriculas->isNotEmpty()) 
                            ? $matriculas 
                            : (isset($matricula) && $matricula ? collect([$matricula]) : collect());
                    @endphp

                    @if ($coleccionMatriculas->isNotEmpty())
                        @foreach ($coleccionMatriculas as $indiceMat => $mat)
                            @php
                                $horarioBase = $mat->horarioMateriaPeriodo?->horarioBase;
                                $dias = [
                                    0 => 'Domingo',
                                    1 => 'Lunes',
                                    2 => 'Martes',
                                    3 => 'Miércoles',
                                    4 => 'Jueves',
                                    5 => 'Viernes',
                                    6 => 'Sábado',
                                    7 => 'Domingo',
                                ];
                                $diaTexto = isset($horarioBase->dia) ? ($dias[$horarioBase->dia] ?? 'N/D') : 'N/D';
                                $ini = !empty($horarioBase->hora_inicio) ? \Carbon\Carbon::parse($horarioBase->hora_inicio)->format('h:i A') : '';
                                $fin = !empty($horarioBase->hora_fin) ? \Carbon\Carbon::parse($horarioBase->hora_fin)->format('h:i A') : '';
                                $horarioCompleto = ($diaTexto !== 'N/D' ? $diaTexto : '') . ($ini && $fin ? " | {$ini} - {$fin}" : '');

                                $aulaModel = $horarioBase?->aula;
                                $aulaNombre = $aulaModel?->nombre ?? 'N/D';
                                $aulaTipo = $aulaModel?->tipo?->nombre ?? null;

                                // Sede de matrícula (donde se dictan las clases)
                                $sedeMatricula = $mat->sede?->nombre 
                                    ?? $aulaModel?->sede?->nombre 
                                    ?? 'No especificada';

                                // Sede para envío o entrega de material
                                $sedeMaterial = $mat->materialSede?->nombre 
                                    ?? $compra->destinatario?->nombre 
                                    ?? 'No especificada';

                                // Datos académicos adicionales
                                $escuelaNombre = $mat->escuela?->nombre 
                                    ?? $mat->horarioMateriaPeriodo?->materiaPeriodo?->periodo?->escuela?->nombre;
                                $materiaNombre = $horarioBase?->materia?->nombre 
                                    ?? $mat->horarioMateriaPeriodo?->materiaPeriodo?->materia?->nombre;
                                $alumnoMatriculado = $mat->user?->nombre(3) ?? ($mat->user?->name ?? null);
                            @endphp

                            <div class="row px-6 py-9 rounded mb-5 shadow border-top-0 border-5 border-primary">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-semibold text-primary mb-0">
                                        <i class="ti ti-school me-2"></i>Detalles de la Matrícula Académica
                                        @if ($coleccionMatriculas->count() > 1)
                                            <span class="badge bg-label-primary ms-2">#{{ $indiceMat + 1 }}</span>
                                        @endif
                                    </h5>
                                    @if ($escuelaNombre)
                                        <span class="badge bg-label-secondary">{{ $escuelaNombre }}</span>
                                    @endif
                                </div>

                                <div class="row equal-cols g-3">
                                    @if ($alumnoMatriculado || $materiaNombre)
                                        @if ($alumnoMatriculado)
                                            <div class="col-md-6 col-12">
                                                <p class="p-0 m-0 text-muted small"><i class="ti ti-user me-1"></i>Alumno</p>
                                                <p class="pb-2 fw-bold text-black border-bottom">{{ $alumnoMatriculado }}</p>
                                            </div>
                                        @endif
                                        @if ($materiaNombre)
                                            <div class="col-md-6 col-12">
                                                <p class="p-0 m-0 text-muted small"><i class="ti ti-book me-1"></i>Materia / Nivel</p>
                                                <p class="pb-2 fw-bold text-black border-bottom">{{ $materiaNombre }}</p>
                                            </div>
                                        @endif
                                    @endif

                                    <div class="col-md-6 col-12">
                                        <p class="p-0 m-0 text-muted small"><i class="ti ti-map-pin me-1"></i>Sede de matrícula (Clases)</p>
                                        <p class="pb-2 fw-bold text-black border-bottom">
                                            {{ $sedeMatricula }}
                                        </p>
                                    </div>

                                    <div class="col-md-6 col-12">
                                        <p class="p-0 m-0 text-muted small"><i class="ti ti-chalkboard me-1"></i>Aula asignada</p>
                                        <p class="pb-2 fw-bold text-black border-bottom">
                                            {{ $aulaNombre }}
                                            @if ($aulaTipo)
                                                <span class="badge bg-label-info ms-1">{{ $aulaTipo }}</span>
                                            @endif
                                        </p>
                                    </div>

                                    <div class="col-md-6 col-12">
                                        <p class="p-0 m-0 text-muted small"><i class="ti ti-clock me-1"></i>Horario</p>
                                        <p class="pb-2 fw-bold text-black border-bottom">
                                            {{ $horarioCompleto ?: 'N/D' }}
                                        </p>
                                    </div>

                                    <div class="col-md-6 col-12">
                                        <p class="p-0 m-0 text-muted small"><i class="ti ti-package me-1"></i>Sede para entrega de material</p>
                                        <p class="pb-2 fw-bold text-black border-bottom">
                                            {{ $sedeMaterial }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
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
                        $cajaId = $compra->pagos->first()?->registro_caja_id ?? null;
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
