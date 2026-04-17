@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Iglesia Infantil — Check-in')

@section('page-style')
    @vite([
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    ])
    <style>
        /* =====================================================
           Check-in: Layout de pasos siempre visibles
        ===================================================== */
        .checkin-step {
            position: relative;
            padding-left: 52px;
            padding-bottom: 28px;
        }

        .checkin-step:last-child {
            padding-bottom: 0;
        }

        /* Línea vertical conectora */
        .checkin-step::before {
            content: '';
            position: absolute;
            left: 19px;
            top: 40px;
            bottom: 0;
            width: 2px;
            background: #e0e0e0;
        }

        .checkin-step:last-child::before {
            display: none;
        }

        /* Número/ícono circular del paso */
        .step-indicator {
            position: absolute;
            left: 0;
            top: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .step-indicator.pending {
            background: #f0f0f0;
            color: #aaa;
            border: 2px solid #e0e0e0;
        }

        .step-indicator.active {
            background: var(--bs-primary);
            color: white;
            box-shadow: 0 0 0 4px rgba(var(--bs-primary-rgb), 0.15);
        }

        .step-indicator.done {
            background: #28a745;
            color: white;
        }

        /* Cabecera del paso */
        .step-header {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 40px;
            margin-bottom: 12px;
        }

        .step-title {
            font-weight: 600;
            font-size: 0.95rem;
            margin: 0;
        }

        .step-title.text-muted {
            font-weight: 500;
        }

        /* Badge de resumen (cuando el paso está completo y colapsado) */
        .step-summary-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        /* Contenido del paso */
        .step-body {
            padding-top: 2px;
        }

        /* Transiciones suaves para habilitar/deshabilitar */
        .step-body.disabled-step {
            opacity: 0.45;
            pointer-events: none;
            user-select: none;
            filter: grayscale(0.3);
        }

        /* Tarjetas de selección de menor */
        .menor-card {
            cursor: pointer;
            border: 2px solid #e9ecef !important;
            transition: border-color 0.2s, background 0.2s, transform 0.15s;
        }

        .menor-card:hover:not(.already-registered) {
            border-color: var(--bs-primary) !important;
            transform: translateY(-2px);
        }

        .menor-card.selected {
            border-color: var(--bs-primary) !important;
            background-color: rgba(var(--bs-primary-rgb), 0.06) !important;
        }

        .menor-card.already-registered {
            opacity: 0.6;
            cursor: not-allowed;
            border-style: dashed !important;
            background-color: #f8f9fa !important;
        }

        /* Resumen de confirmación */
        .resumen-row {
            display: flex;
            align-items: baseline;
            gap: 8px;
            padding: 6px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .resumen-row:last-child {
            border-bottom: none;
        }

        .resumen-label {
            flex-shrink: 0;
            min-width: 105px;
            font-size: 0.82rem;
            color: #6c757d;
        }

        .resumen-valor {
            font-weight: 600;
            font-size: 0.9rem;
            word-break: break-word;
        }

        /* Botón registrar */
        .btn-registrar {
            min-width: 200px;
        }
    </style>
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    ])
@endsection

@section('page-script')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('checkinForm', () => ({
            // Estado de completitud de cada paso
            reporteReunionId: '',
            reporteNombre: '',

            adultoSeleccionado: null,
            cargandoAdulto: false,

            menores: [],
            menorSeleccionado: null,

            salonId: '',
            salonNombre: '',
            estacionId: '',
            estacionNombre: '',
            estaciones: [],
            indicaciones: '',

            // Computed: qué pasos están habilitados
            get pasoAdultoHabilitado() {
                return !!this.reporteReunionId;
            },
            get pasoMenorHabilitado() {
                return !!this.adultoSeleccionado;
            },
            get pasoSalonHabilitado() {
                return !!this.menorSeleccionado;
            },
            get pasoConfirmarHabilitado() {
                return !!(this.salonId && this.estacionId);
            },

            // Estado visual de cada paso (pending | active | done)
            get estadoPaso0() {
                return this.reporteReunionId ? 'done' : 'active';
            },
            get estadoPaso1() {
                if (!this.pasoAdultoHabilitado) return 'pending';
                return this.adultoSeleccionado ? 'done' : 'active';
            },
            get estadoPaso2() {
                if (!this.pasoMenorHabilitado) return 'pending';
                return this.menorSeleccionado ? 'done' : 'active';
            },
            get estadoPaso3() {
                if (!this.pasoSalonHabilitado) return 'pending';
                return (this.salonId && this.estacionId) ? 'done' : 'active';
            },
            get estadoPaso4() {
                if (!this.pasoConfirmarHabilitado) return 'pending';
                return 'active';
            },

            // 1. Inicialización de listeners de eventos Livewire
            init() {
                // Reporte de reunión (desde componente IglesiaInfantil.reportes-para-checkin)
                window.addEventListener('reporteIglesiaInfantilSeleccionado', (event) => {
                    this.reporteReunionId = event.detail[0]?.reporteId ?? event.detail.reporteId ?? '';
                    this.reporteNombre = event.detail[0]?.nombre ?? event.detail.nombre ?? '';
                });

                // Selección de Adulto (desde componente Usuarios.usuarios-para-busqueda)
                window.addEventListener('usuario-seleccionado', (event) => {
                    const id = event.detail.id;
                    if (id) {
                        this.setAdulto(id);
                    } else {
                        this.adultoSeleccionado = null;
                        this.menores = [];
                        this.menorSeleccionado = null;
                    }
                });
            },

            // 2. Obtener datos del adulto y sus menores a cargo tras selección en Livewire
            async setAdulto(id) {
                this.cargandoAdulto = true;

                // Solo limpiamos el adulto si es uno nuevo; si es el mismo (recarga), lo mantenemos para evitar parpadeos
                if (!this.adultoSeleccionado || this.adultoSeleccionado.id !== id) {
                    this.adultoSeleccionado = null;
                }

                this.menores = [];
                this.menorSeleccionado = null;
                try {
                    const baseUrl = "{{ url('/') }}";
                    // Enviamos el reporteId para que el servidor verifique duplicados
                    const response = await fetch(`${baseUrl}/iglesia-infantil/datos-adulto/${id}?reporte_reunion_id=${this.reporteReunionId}`);

                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.error || 'Error en el servidor');
                    }

                    const data = await response.json();
                    this.adultoSeleccionado = data.adulto;
                    this.menores = data.menores;
                } catch (e) {
                    console.error('Error al cargar datos del adulto:', e);
                    Swal.fire('Error', e.message || 'No se pudieron cargar los datos del responsable.', 'error');
                } finally {
                    this.cargandoAdulto = false;
                }
            },

            // 3. Cargar estaciones del salón seleccionado desde el atributo data del <option>
            cargarEstaciones() {
                this.estacionId = '';
                this.estaciones = [];
                if (!this.salonId) return;

                const opt = document.querySelector(`select[x-model="salonId"] option[value="${this.salonId}"]`);
                if (opt) {
                    const data = JSON.parse(opt.getAttribute('data-estaciones') || '{}');
                    this.estaciones = Object.entries(data).map(([id, nombre]) => ({ id: parseInt(id), nombre }));
                    this.salonNombre = opt.textContent.trim();
                }
            },

            // 4. Actualizar nombre de estación cuando cambia la selección
            actualizarEstacion() {
                const estOpt = this.estaciones.find(e => e.id === parseInt(this.estacionId));
                this.estacionNombre = estOpt ? estOpt.nombre : '';
            },

            // 5. Registrar con confirmación SweetAlert (abre ticket en nueva pestaña y resetea el form)
            confirmarYRegistrar() {
                Swal.fire({
                    title: '¿Confirmar registro?',
                    html: `<div class="text-start" style="font-size:0.9rem;">
                        <p class="mb-1"><strong>Menor:</strong> ${this.menorSeleccionado?.nombre_completo ?? ''}</p>
                        <p class="mb-1"><strong>Adulto:</strong> ${this.adultoSeleccionado?.nombre_completo ?? ''}</p>
                        <p class="mb-1"><strong>Salón:</strong> ${this.salonNombre}</p>
                        <p class="mb-0"><strong>Estación:</strong> ${this.estacionNombre}</p>
                    </div>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="ti ti-baby-carriage me-1"></i>Sí, registrar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#28a745',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Guardamos el ID del adulto para recargar sus menores después
                        const adultoIdPrevia = this.adultoSeleccionado?.id;

                        document.getElementById('formRegistro').submit();

                        // Resetear el formulario para el siguiente registro (sin recargar la página)
                        this.$nextTick(() => {
                            this.menorSeleccionado = null;
                            this.salonId = '';
                            this.estacionId = '';
                            this.estaciones = [];
                            this.indicaciones = '';
                            this.salonNombre = '';
                            this.estacionNombre = '';

                            // Si teníamos un adulto, recargamos sus datos para que se actualice 
                            // el estado 'ya_registrado' de los menores sin quitar al adulto de la vista.
                            if (adultoIdPrevia) {
                                this.setAdulto(adultoIdPrevia);
                            } else {
                                this.adultoSeleccionado = null;
                                this.menores = [];
                            }
                        });
                    }
                });
            },

            // 6. Limpiar adulto y menores para seleccionar otro
            limpiarAdulto() {
                this.adultoSeleccionado = null;
                this.menores = [];
                this.menorSeleccionado = null;
                this.salonId = '';
                this.estacionId = '';
                this.estaciones = [];
                this.indicaciones = '';
            },
        }));
    });
</script>
@endsection

@section('content')

<h4 class="mb-1 fw-semibold text-primary">
    <i class="ti ti-baby-carriage me-2"></i>Check-in Iglesia Infantil
</h4>
<p class="mb-4 text-black">Registro de ingreso de menores al cuidado de la iglesia.</p>

@include('layouts.status-msn')

<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('iglesiaInfantil.listaTurno') }}" class="btn btn-outline-secondary waves-effect">
       Ver lista del turno
    </a>
</div>

{{-- Formulario oculto para envío POST — abre el ticket en nueva pestaña --}}
<form method="POST" action="{{ route('iglesiaInfantil.checkin.registrar') }}" id="formRegistro" target="_blank">
    @csrf
    <input type="hidden" name="reporte_reunion_id" id="hidden_reporte_reunion_id">
    <input type="hidden" name="menor_user_id" id="hidden_menor_user_id">
    <input type="hidden" name="adulto_ingreso_user_id" id="hidden_adulto_ingreso_user_id">
    <input type="hidden" name="salon_infantil_id" id="hidden_salon_infantil_id">
    <input type="hidden" name="estacion_salon_infantil_id" id="hidden_estacion_salon_infantil_id">
    <input type="hidden" name="indicaciones_medicas" id="hidden_indicaciones_medicas">
</form>

{{-- Flujo de check-in: todos los pasos visibles --}}
<div class="row">
    <div class="col-xl-10  col-lg-10 col-12 mx-auto" x-data="checkinForm"
        x-init="
            $watch('reporteReunionId', v => document.getElementById('hidden_reporte_reunion_id').value = v);
            $watch('menorSeleccionado', v => document.getElementById('hidden_menor_user_id').value = v?.id ?? '');
            $watch('adultoSeleccionado', v => document.getElementById('hidden_adulto_ingreso_user_id').value = v?.id ?? '');
            $watch('salonId', v => document.getElementById('hidden_salon_infantil_id').value = v);
            $watch('estacionId', v => document.getElementById('hidden_estacion_salon_infantil_id').value = v);
            $watch('indicaciones', v => document.getElementById('hidden_indicaciones_medicas').value = v);
        ">

        <div class="card">
            <div class="card-body py-4 px-4">

                {{-- =========================================================
                     PASO 1 — Seleccionar el Reporte de Reunión
                     Rara vez cambia; se muestra compacto cuando ya hay uno
                ========================================================= --}}
                <div class="checkin-step">
                    <div class="step-indicator" :class="estadoPaso0">
                        <i x-show="estadoPaso0 === 'done'" class="ti ti-check"></i>
                        <span x-show="estadoPaso0 !== 'done'">1</span>
                    </div>
                    <div class="step-header">
                        <div>
                            <p class="step-title" :class="reporteReunionId ? 'text-black' : 'text-primary'">
                                <i class="ti ti-calendar-event me-1"></i>Reporte de Reunión
                            </p>
                        </div>
                        {{-- Resumen cuando ya está seleccionado --}}
                        <span x-show="reporteReunionId" class="step-summary-badge">
                            <i class="ti ti-circle-check"></i>
                            <span x-text="reporteNombre"></span>
                        </span>
                    </div>

                    <div class="step-body">
                        {{-- Siempre visible —  el componente Livewire permite cambiar en cualquier momento --}}
                        <div class="row">
                            <div class="col-lg-8">
                                <label class="form-label">Reporte de reunión activo <span class="text-danger">*</span></label>
                                @livewire('IglesiaInfantil.reportes-para-checkin')
                                <p class="text-muted small mt-2" x-show="!reporteReunionId">
                                    <i class="ti ti-info-circle me-1"></i>Selecciona el reporte del servicio de hoy para continuar.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- =========================================================
                     PASO 2 — Seleccionar al Adulto Responsable
                     El más frecuente: cambia con cada familia
                ========================================================= --}}
                <div class="checkin-step" :class="{ 'disabled-step': !pasoAdultoHabilitado }">
                    <div class="step-indicator" :class="estadoPaso1">
                        <i x-show="estadoPaso1 === 'done'" class="ti ti-check"></i>
                        <span x-show="estadoPaso1 !== 'done'">2</span>
                    </div>
                    <div class="step-header">
                        <div>
                            <p class="step-title" :class="!pasoAdultoHabilitado ? 'text-muted' : (adultoSeleccionado ? 'text-black' : 'text-primary')">
                                <i class="ti ti-user-search me-1"></i>Adulto Responsable
                            </p>
                        </div>
                        {{-- Resumen + botón cambiar cuando ya está seleccionado --}}
                        <span x-show="adultoSeleccionado" class="step-summary-badge">
                            <i class="ti ti-user-check"></i>
                            <span x-text="adultoSeleccionado?.nombre_completo"></span>
                        </span>
                        <button x-show="adultoSeleccionado" type="button"
                            class="btn btn-sm btn-outline-secondary py-0 px-2"
                            @click="limpiarAdulto()"
                            title="Cambiar adulto">
                            <i class="ti ti-refresh me-1"></i>Cambiar
                        </button>
                    </div>

                    <div class="step-body">
                        {{-- Buscador: siempre visible para poder cambiar rápidamente --}}
                        <div class="row">
                            <div class="col-lg-9">
                                @livewire('Usuarios.usuarios-para-busqueda', [
                                    'queUsuariosCargar' => 'todos',
                                    'tipoBuscador' => 'unico',
                                    'soloVerificados' => false,
                                    'conDadosDeBaja' => 'no',
                                    'placeholder' => 'Buscar responsable por nombre o identificación...',
                                    'label' => 'Buscar adulto responsable'
                                ])
                            </div>
                        </div>
                    </div>
                </div>

                {{-- =========================================================
                     PASO 3 — Seleccionar al Menor
                ========================================================= --}}
                <div class="checkin-step" :class="{ 'disabled-step': !pasoMenorHabilitado }">
                    <div class="step-indicator" :class="estadoPaso2">
                        <i x-show="estadoPaso2 === 'done'" class="ti ti-check"></i>
                        <span x-show="estadoPaso2 !== 'done'">3</span>
                    </div>
                    <div class="step-header">
                        <div>
                            <p class="step-title" :class="!pasoMenorHabilitado ? 'text-muted' : (menorSeleccionado ? 'text-black' : 'text-primary')">
                                <i class="ti ti-baby me-1"></i>Menor
                            </p>
                        </div>
                        <span x-show="menorSeleccionado" class="step-summary-badge">
                            <i class="ti ti-user-check"></i>
                            <span x-text="menorSeleccionado?.nombre_completo"></span>
                        </span>
                        <button x-show="menorSeleccionado" type="button"
                            class="btn btn-sm btn-outline-secondary py-0 px-2"
                            @click="menorSeleccionado = null"
                            title="Cambiar menor">
                            <i class="ti ti-refresh me-1"></i>Cambiar
                        </button>
                    </div>

                    <div class="step-body">
                        {{-- Advertencia si el adulto no tiene menores --}}
                        <div x-show="pasoMenorHabilitado && menores.length === 0 && !cargandoAdulto" class="alert alert-warning py-2 mb-3">
                            <i class="ti ti-alert-triangle me-2"></i>
                            Este adulto no tiene menores registrados a su cargo en el sistema.
                        </div>

                        {{-- Grid de tarjetas de menores --}}
                        <div class="row g-2" x-show="menores.length > 0">
                            <template x-for="menor in menores" :key="menor.id">
                                <div class="col-md-3 col-sm-4 col-6">
                                    <div class="card menor-card border mb-0 h-100"
                                        :class="{ 
                                            'selected': menorSeleccionado && menorSeleccionado.id === menor.id,
                                            'already-registered': menor.ya_registrado 
                                        }"
                                        @click="if(!menor.ya_registrado) { menorSeleccionado = menor }">
                                        <div class="card-body text-center py-3 px-2 position-relative">
                                            <template x-if="menor.ya_registrado">
                                                <span class="badge bg-secondary position-absolute top-0 start-50 translate-middle-x mt-1" style="font-size: 0.6rem;">
                                                    YA REGISTRADO
                                                </span>
                                            </template>
                                            <i class="ti ti-baby mb-1" :class="menor.ya_registrado ? 'text-secondary' : 'text-primary'" style="font-size:2rem;"></i>
                                            <h6 class="mb-0 small fw-semibold text-black" x-text="menor.nombre_completo"></h6>
                                            <small class="text-black opacity-75" x-text="menor.edad + ' años'"></small>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- =========================================================
                     PASO 4 — Salón, Estación e Indicaciones
                ========================================================= --}}
                <div class="checkin-step" :class="{ 'disabled-step': !pasoSalonHabilitado }">
                    <div class="step-indicator" :class="estadoPaso3">
                        <i x-show="estadoPaso3 === 'done'" class="ti ti-check"></i>
                        <span x-show="estadoPaso3 !== 'done'">4</span>
                    </div>
                    <div class="step-header">
                        <p class="step-title" :class="!pasoSalonHabilitado ? 'text-muted' : ((salonId && estacionId) ? 'text-black' : 'text-primary')">
                            <i class="ti ti-door me-1"></i>Salón y Estación
                        </p>
                        <span x-show="salonId && estacionId" class="step-summary-badge">
                            <i class="ti ti-map-pin"></i>
                            <span x-text="salonNombre + ' › ' + estacionNombre"></span>
                        </span>
                    </div>

                    <div class="step-body">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Salón <span class="text-danger">*</span></label>
                                <select class="form-select" x-model="salonId" @change="cargarEstaciones()">
                                    <option value="">Selecciona un salón...</option>
                                    @foreach ($salones as $salon)
                                        <option value="{{ $salon->id }}"
                                            data-estaciones="{{ $salon->estaciones->pluck('nombre', 'id')->toJson() }}">
                                            {{ $salon->nombre }}{{ !$salon->activo ? ' (Inactivo)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Estación <span class="text-danger">*</span></label>
                                <select class="form-select" x-model="estacionId"
                                    @change="actualizarEstacion()"
                                    :disabled="!salonId || estaciones.length === 0">
                                    <option value="">Selecciona una estación...</option>
                                    <template x-for="est in estaciones" :key="est.id">
                                        <option :value="est.id" x-text="est.nombre"></option>
                                    </template>
                                </select>
                                <small x-show="salonId && estaciones.length === 0" class="text-warning">
                                    <i class="ti ti-alert-triangle me-1"></i>Este salón no tiene estaciones asignadas.
                                </small>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Indicaciones médicas del día <span class="text-muted small">(opcional)</span></label>
                                <textarea class="form-control" rows="2" x-model="indicaciones"
                                    placeholder="Ej: tiene gripa leve, alérgico a la penicilina, no toma leche..."></textarea>
                                <small class="text-muted">Registra cualquier condición relevante de hoy para el personal del salón.</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- =========================================================
                     PASO 5 — Confirmar y Registrar
                ========================================================= --}}
                <div class="checkin-step" :class="{ 'disabled-step': !pasoConfirmarHabilitado }">
                    <div class="step-indicator" :class="estadoPaso4">
                        <span>5</span>
                    </div>
                    <div class="step-header">
                        <p class="step-title" :class="!pasoConfirmarHabilitado ? 'text-black' : 'text-primary'">
                            <i class="ti ti-clipboard-check me-1"></i>Confirmar Registro
                        </p>
                    </div>

                    <div class="step-body">
                        {{-- Resumen de lo que se registrará --}}
                        <div class="card border  mb-3" x-show="pasoConfirmarHabilitado">
                            <div class="card-body py-3">
                                <div class="resumen-row">
                                    <span class="resumen-label text-black"><i class="ti ti-calendar-event me-1 text-muted"></i>Reunión</span>
                                    <span class="resumen-valor text-black" x-text="reporteNombre"></span>
                                </div>
                                <div class="resumen-row">
                                    <span class="resumen-label text-black"><i class="ti ti-user me-1 text-muted"></i>Adulto</span>
                                    <span class="resumen-valor text-black" x-text="adultoSeleccionado?.nombre_completo ?? ''"></span>
                                </div>
                                <div class="resumen-row">
                                    <span class="resumen-label text-black"><i class="ti ti-baby-bottle me-1 text-muted"></i>Menor</span>
                                    <span class="resumen-valor text-black" x-text="menorSeleccionado?.nombre_completo ?? ''"></span>
                                </div>
                                <div class="resumen-row">
                                    <span class="resumen-label text-black"><i class="ti ti-door me-1 text-muted"></i>Salón</span>
                                    <span class="resumen-valor text-black" x-text="salonNombre"></span>
                                </div>
                                <div class="resumen-row">
                                    <span class="resumen-label text-black"><i class="ti ti-map-pin me-1 text-muted"></i>Estación</span>
                                    <span class="resumen-valor text-black" x-text="estacionNombre"></span>
                                </div>
                                <div class="resumen-row" x-show="indicaciones">
                                    <span class="resumen-label text-black"><i class="ti ti-nurse me-1 text-muted"></i>Indicaciones</span>
                                    <span class="resumen-valor text-black" x-text="indicaciones"></span>
                                </div>
                            </div>
                        </div>

                        <p x-show="!pasoConfirmarHabilitado" class="text-muted small">
                            <i class="ti ti-lock me-1"></i>Completa los pasos anteriores para habilitar el registro.
                        </p>

                        <button type="button"
                            class="btn btn-success btn-registrar waves-effect waves-light"
                            :disabled="!pasoConfirmarHabilitado"
                            @click="confirmarYRegistrar()">
                            <i class="ti ti-baby-carriage me-2"></i>Registrar ingreso
                            <i class="ti ti-external-link ms-1" title="Se abrirá el ticket"></i>
                        </button>
                        <p class="text-muted small mt-2">
                            <i class="ti ti-ticket me-1"></i>Al registrar se abrirá el ticket de retiro para imprimir.
                        </p>
                    </div>
                </div>

            </div>{{-- fin card-body --}}
        </div>{{-- fin card --}}

    </div>{{-- fin col --}}
</div>{{-- fin row --}}

@endsection
