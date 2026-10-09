@php
    use App\Models\Sede;
    use App\Models\User;
@endphp

@section('isEscuelasModule', true)

@extends('layouts.layoutMaster')

@section('title', 'Calificaciones Multiples')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-style')
    <style>
        .accordion-button:not(.collapsed) {
            color: var(--bs-primary);
            background-color: var(--bs-primary-light);
            /* Un color más suave para el acordeón activo */
        }

        .accordion-button:not(.collapsed)::after {
            background-image: var(--bs-accordion-btn-active-icon);
            transform: var(--bs-accordion-btn-icon-transform);
        }

        .card-item-calificacion .card-body {
            padding: 0.8rem;
        }

        .card-item-calificacion .form-control-sm {
            text-align: center;
            max-width: 80px;
            /* Ancho para input de nota */
            margin: 0 auto 0.5rem auto;
            /* Ancho máximo para el input de nota */
        }

        .accordion-toggle-btn {
            font-size: 1.2rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
@endsection

@section('page-script')
    <script>
        // Inicializar tooltips de Bootstrap si los usas en el componente Livewire o aquí
        document.addEventListener('livewire:navigated', () => { // Para Livewire 3 con navegación SPA
            // O 'DOMContentLoaded' si no usas navegación SPA de Livewire intensamente
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Event listener para los botones "Calificar" individuales
            document.querySelectorAll('.btn-calificar-item').forEach(button => {
                button.addEventListener('click', function(event) {
                    event
                        .preventDefault(); // Prevenir envío de formulario si es parte de uno más grande
                    const itemId = this.dataset.itemId;
                    const alumnoId = this.dataset
                        .alumnoId; // Necesitarás añadir data-alumno-id al botón o al form
                    const notaInputId = `nota_alumno_${alumnoId}_item_${itemId}`;
                    const notaValor = document.getElementById(notaInputId) ? document
                        .getElementById(notaInputId).value : null;

                    if (notaValor === null || notaValor.trim() === '') {
                        Swal.fire('Atención', 'Por favor, ingresa una nota.', 'warning');
                        return;
                    }

                    console.log(
                        `Calificar Alumno ID: ${alumnoId}, Item ID: ${itemId}, Nota: ${notaValor}`
                    );
                    Swal.fire({
                        icon: 'success',
                        title: 'Nota',
                        text: `Nota ${notaValor} para Item ID ${itemId} del Alumno ID ${alumnoId} sería guardada.`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            });

            // Validar inputs de nota
            document.querySelectorAll('.input-nota-item').forEach(input => {
                input.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
                    let val = parseFloat(this.value);
                    if (val > 5.0) this.value = "5.0"; // Asumiendo nota máxima 5.0
                    if (val < 0.0) this.value = "0.0"; // Asumiendo nota mínima 0.0
                });
            });
        });
    </script>
@endsection

@section('content')
    @include('layouts.status-msn')

    {{-- Encabezado de la clasificacion detallada --}}
    <div class="row mb-3">
        <div class="col-12 mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h4 class="mb-1 fw-semibold text-primary">
                        Calificación multiple: <span class="text-black fw-normal">{{ $nombreMateria }}</span>
                    </h4>
                    <p class="mb-0 text-black"><small>{{ $infoClase }}</small></p>
                </div>
            </div>
        </div>
    </div>

    @include('contenido.paginas.escuelas.maestros.nav-modulo')

    <div class="row mb-3" aria-label="Fechas límite de los cortes">
        @foreach (($horarioAsignado->materiaPeriodo?->periodo?->cortesPeriodo ?? collect())->sortBy('corteEscuela.orden') as $corte)
            @php
                $fechaLimite = $corte->fecha_fin?->copy()->endOfDay();
                $ahora = now();
                $diasRestantes = $fechaLimite ? (int) $ahora->copy()->startOfDay()->diffInDays($fechaLimite->copy()->startOfDay(), false) : null;
                $colorAlerta = $diasRestantes === null ? 'info' : ($diasRestantes <= 0 ? 'danger' : ($diasRestantes <= 6 ? 'warning' : 'success'));
            @endphp
            <div class="col-12 col-xl-6">
                <div class="alert alert-{{ $colorAlerta }} mb-2"
                    @if ($fechaLimite)
                        x-data="{
                            limite: {{ $fechaLimite->getTimestampMs() + 1 }},
                            ahora: {{ $ahora->getTimestampMs() }},
                            inicioServidor: {{ $ahora->getTimestampMs() }},
                            inicioCliente: Date.now(),
                            intervalo: null,
                            zonaHoraria: @js(config('app.timezone')),
                            fechaCierre: @js($fechaLimite->format('Y-m-d')),
                            init() {
                                this.actualizar();
                                this.intervalo = setInterval(() => this.actualizar(), 1000);
                            },
                            destroy() { clearInterval(this.intervalo); },
                            actualizar() { this.ahora = this.inicioServidor + Date.now() - this.inicioCliente; },
                            get segundos() { return Math.max(0, Math.ceil((this.limite - this.ahora) / 1000)); },
                            get dias() {
                                const partes = new Intl.DateTimeFormat('en', {
                                    timeZone: this.zonaHoraria, year: 'numeric', month: '2-digit', day: '2-digit'
                                }).formatToParts(new Date(this.ahora));
                                const fecha = Object.fromEntries(partes.map(parte => [parte.type, parte.value]));
                                return Math.round((Date.parse(this.fechaCierre + 'T00:00:00Z') - Date.UTC(Number(fecha.year), Number(fecha.month) - 1, Number(fecha.day))) / 86400000);
                            },
                            get color() { return this.segundos === 0 || this.dias === 0 ? 'danger' : (this.dias <= 6 ? 'warning' : 'success'); },
                            get mensaje() {
                                if (this.segundos === 0) { return 'Plazo del corte vencido'; }
                                if (this.dias === 0) { return 'Hoy vence el corte'; }
                                return 'Quedan ' + this.dias + (this.dias === 1 ? ' día' : ' días') + ' para el cierre';
                            },
                            get tiempo() {
                                const dias = Math.floor(this.segundos / 86400);
                                const horas = Math.floor(this.segundos % 86400 / 3600);
                                const minutos = Math.floor(this.segundos % 3600 / 60);
                                const segundos = this.segundos % 60;
                                return dias + 'd ' + [horas, minutos, segundos].map(valor => String(valor).padStart(2, '0')).join(':');
                            }
                        }"
                        x-bind:class="{ 'alert-{{ $colorAlerta }}': color === '{{ $colorAlerta }}', ['alert-' + color]: true }"
                    @endif
                >
                    <div class="fw-semibold mb-1">
                        <i class="mdi mdi-clock-outline me-1" aria-hidden="true"></i>
                        {{ $corte->corteEscuela?->nombre ?? 'Corte' }}
                    </div>
                    @if ($fechaLimite)
                        <div class="fw-semibold" x-text="mensaje" aria-live="polite">
                            {{ $diasRestantes < 0 ? 'Plazo del corte vencido' : ($diasRestantes === 0 ? 'Hoy vence el corte' : 'Quedan '.$diasRestantes.' días para el cierre') }}
                        </div>
                        <div>Fecha límite: <strong>{{ $fechaLimite->format('d/m/Y') }}</strong> a las 11:59 p. m. ({{ config('app.timezone') }}).</div>
                        <div class="mt-1" x-show="segundos > 0">Tiempo restante: <strong x-text="tiempo"></strong></div>
                    @else
                        <div>Este corte no tiene una fecha límite configurada.</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Contenido Principal: Acordeón de Alumnos --}}
    <div class="row">
        <div class="col-12">
            {{-- Renderizar el componente Livewire, pasándole el HorarioMateriaPeriodo --}}
            @livewire('Maestros.CalificacionMultipleAlumnos', ['horarioAsignado' => $horarioAsignado])
        </div>
    </div>
@endsection
