<div>
    <style>
        .attendance-reports-shell {
            border: 1px solid var(--bs-border-color);
            border-radius: 0.85rem;
            box-shadow: 0 0.15rem 0.6rem rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .attendance-header-icon {
            width: 2.15rem;
            height: 2.15rem;
            border-radius: 0.6rem;
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .attendance-reports-list {
            --attendance-report-columns: minmax(8rem, 1.15fr) minmax(7.5rem, 1.2fr) minmax(4.5rem, .7fr) minmax(4.5rem, .7fr) minmax(4.5rem, .7fr) minmax(8.5rem, 1.2fr) minmax(9.5rem, 1.3fr);
        }

        .attendance-reports-header,
        .attendance-report-row {
            display: grid;
            grid-template-columns: var(--attendance-report-columns);
            gap: .65rem;
            align-items: center;
        }

        .attendance-reports-header {
            padding: 0.65rem 0.85rem;
            margin-bottom: 0.65rem;
            border-radius: 0.55rem;
            background-color: var(--bs-gray-100);
            border: 1px solid var(--bs-border-color);
            color: var(--bs-secondary-color);
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .attendance-reports-header > :not(:first-child):not(:nth-child(2)) {
            text-align: center;
        }

        .attendance-report-card {
            border: 1px solid var(--bs-border-color);
            border-radius: 0.7rem;
            box-shadow: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .attendance-report-card:hover {
            border-color: rgba(var(--bs-primary-rgb), 0.35);
            box-shadow: 0 0.2rem 0.6rem rgba(15, 23, 42, 0.04);
        }

        .attendance-report-row {
            min-height: 5.25rem;
            padding: .85rem;
        }

        .attendance-report-cell:not(.attendance-date):not(.attendance-status) {
            text-align: center;
        }

        .attendance-cell-label {
            display: none;
        }

        .attendance-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            padding: 0.3rem 0.65rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
            line-height: 1.2;
            max-width: 100%;
            white-space: normal;
            text-align: center;
        }

        .attendance-pill-success {
            background-color: rgba(40, 199, 111, 0.14);
            color: #167943 !important;
        }

        .attendance-pill-warning {
            background-color: rgba(255, 159, 67, 0.18);
            color: #9a5700 !important;
        }

        .attendance-pill-neutral {
            background-color: rgba(108, 117, 125, 0.12);
            color: #495057 !important;
        }

        .attendance-metric-value {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            padding: 0.2rem 0.5rem;
            border-radius: 0.45rem;
            font-weight: 700;
            font-size: 0.85rem;
            background-color: var(--bs-gray-100);
            color: #2f2b3d;
        }

        .attendance-metric-value.is-present {
            background-color: rgba(40, 199, 111, 0.12);
            color: #167943;
        }

        .attendance-metric-value.is-absent {
            background-color: rgba(255, 76, 81, 0.12);
            color: #c82333;
        }

        .attendance-link-actions,
        .attendance-report-actions {
            display: grid;
            gap: .3rem;
        }

        .attendance-link-actions .btn,
        .attendance-report-actions .btn {
            width: 100%;
            min-height: 1.75rem;
            padding: .25rem .4rem;
            font-size: .6875rem;
            line-height: 1.2;
            white-space: nowrap;
        }

        .attendance-report-actions {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        @media (max-width: 1199.98px) {
            .attendance-reports-header,
            .attendance-report-row {
                gap: .25rem;
            }

            .attendance-report-row {
                padding-right: .5rem;
                padding-left: .5rem;
            }

            .attendance-link-actions .btn,
            .attendance-report-actions .btn,
            .attendance-status .badge {
                font-size: .625rem;
            }
        }

        @media (max-width: 991.98px) {
            .attendance-reports-header {
                display: none;
            }

            .attendance-report-card {
                border: 1px solid var(--bs-border-color);
            }

            .attendance-report-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                min-height: auto;
                gap: .875rem 1rem;
                padding: 1rem;
            }

            .attendance-report-cell {
                display: grid;
                gap: .2rem;
                text-align: left !important;
            }

            .attendance-cell-label {
                display: block;
                color: var(--bs-secondary-color);
                font-size: .7rem;
                font-weight: 600;
            }

            .attendance-public-link,
            .attendance-actions {
                grid-column: 1 / -1;
            }

            .attendance-link-actions,
            .attendance-report-actions {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .attendance-report-row,
            .attendance-link-actions,
            .attendance-report-actions {
                grid-template-columns: minmax(0, 1fr);
            }
        }
    </style>

    {{-- Sección de Listado de Reportes --}}
    <div class="card attendance-reports-shell mb-4">
        <div class="card-header bg-transparent border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 py-3">
            <div class="d-flex align-items-center gap-2">
                <span class="attendance-header-icon">
                    <i class="ti ti-clipboard-list"></i>
                </span>
                <div>
                    <h5 class="mb-0 fw-bold text-dark fs-6">Reportes de Asistencia para:
                        {{ $horarioAsignado->materiaPeriodo->materia->nombre ?? 'Clase' }}</h5>
                    <small class="text-muted">Historial de clases reportadas, enlaces públicos y exportación</small>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" wire:click="exportarTodosLosReportes"
                wire:loading.attr="disabled" wire:target="exportarTodosLosReportes">
                <span wire:loading.remove wire:target="exportarTodosLosReportes">
                    <i class="ti ti-file-spreadsheet me-1"></i> Excel general
                </span>
                <span wire:loading wire:target="exportarTodosLosReportes">
                    <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Generando...
                </span>
            </button>
        </div>
        <div class="card-body p-3 p-md-4">
            @if ($reportesPaginados->isNotEmpty())
                <div class="attendance-reports-list">
                <div class="attendance-reports-header" aria-hidden="true">
                    <div>Fecha clase</div>
                    <div>Estado</div>
                    <div>Reportados</div>
                    <div>Presentes</div>
                    <div>Ausentes</div>
                    <div>Link público</div>
                    <div>Acciones</div>
                </div>

                @foreach ($reportesPaginados as $reporte)
                    @php
                        $ausentesCount = max(0, $reporte->detalles_asistencia_count - $reporte->presentes_count);
                        $estadoPillClass = match ($reporte->estado_reporte) {
                            'pendiente_detalle' => 'attendance-pill-warning',
                            'completado' => 'attendance-pill-success',
                            default => 'attendance-pill-neutral',
                        };
                        $estadoIcono = match ($reporte->estado_reporte) {
                            'pendiente_detalle' => 'ti-clock-exclamation',
                            'completado' => 'ti-circle-check',
                            default => 'ti-info-circle',
                        };
                    @endphp
                    <article wire:key="reporte-asistencia-{{ $reporte->id }}"
                        class="attendance-report-card card mb-2 {{ $loop->even ? 'bg-lighter' : '' }}">
                        <div class="attendance-report-row">
                            <div class="attendance-report-cell attendance-date">
                                <span class="attendance-cell-label">Fecha clase</span>
                                <span class="fw-semibold text-dark d-inline-flex align-items-center gap-1">
                                    <i class="ti ti-calendar-event text-primary"></i>
                                    {{ \Carbon\Carbon::parse($reporte->fecha_clase_reportada)->format('d/m/Y') }}
                                </span>
                                <small class="text-muted d-block">{{ \Carbon\Carbon::parse($reporte->fecha_clase_reportada)->diffForHumans() }}</small>
                            </div>

                            <div class="attendance-report-cell attendance-status">
                                <span class="attendance-cell-label">Estado</span>
                                <span class="attendance-pill {{ $estadoPillClass }}">
                                    <i class="ti {{ $estadoIcono }}"></i>
                                    {{ ucfirst(str_replace('_', ' ', $reporte->estado_reporte)) }}
                                </span>
                            </div>

                            <div class="attendance-report-cell">
                                <span class="attendance-cell-label">Reportados</span>
                                <span class="attendance-metric-value">{{ $reporte->detalles_asistencia_count }}</span>
                            </div>
                            <div class="attendance-report-cell">
                                <span class="attendance-cell-label">Presentes</span>
                                <span class="attendance-metric-value is-present">{{ $reporte->presentes_count }}</span>
                            </div>
                            <div class="attendance-report-cell">
                                <span class="attendance-cell-label">Ausentes</span>
                                <span class="attendance-metric-value {{ $ausentesCount > 0 ? 'is-absent' : '' }}">{{ $ausentesCount }}</span>
                            </div>

                            <div class="attendance-report-cell attendance-public-link">
                                <span class="attendance-cell-label">Link público</span>
                                <div class="attendance-link-actions">
                                    <a target="_blank"
                                        href="{{ route('maestros.reportarAutoAsistencia', ['horarioAsignado' => $horarioAsignado->id, 'reporte' => $reporte->id]) }}"
                                        class="btn btn-sm btn-outline-info rounded-pill">
                                        <i class="ti ti-external-link me-1"></i> Ver link
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill copiar-link-btn"
                                        data-url="{{ route('maestros.reportarAutoAsistencia', ['horarioAsignado' => $horarioAsignado->id, 'reporte' => $reporte->id]) }}">
                                        <i class="ti ti-copy me-1"></i> Copiar link
                                    </button>
                                </div>
                            </div>

                            <div class="attendance-report-cell attendance-actions">
                                <span class="attendance-cell-label">Acciones</span>
                                <div class="attendance-report-actions">
                                    @php($puedeEditar = $this->verificarSiSePuedeEditarReporte($reporte))
                                    <a href="{{ route('maestros.editarReporte', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado, 'reporte' => $reporte]) }}"
                                        class="btn btn-sm btn-primary waves-effect waves-light rounded-pill @if (!$puedeEditar) disabled @endif"
                                        aria-disabled="{{ $puedeEditar ? 'false' : 'true' }}">
                                        <i class="ti ti-pencil me-1"></i>
                                        @if ($reporte->estado_reporte === 'pendiente_detalle' && $reporte->detalles_asistencia_count === 0)
                                            Registrar
                                        @else
                                            Editar
                                        @endif
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill"
                                        wire:click="exportarReporte({{ $reporte->id }})" wire:loading.attr="disabled"
                                        wire:target="exportarReporte({{ $reporte->id }})">
                                        <span wire:loading.remove wire:target="exportarReporte({{ $reporte->id }})">
                                            <i class="ti ti-file-spreadsheet me-1"></i> Excel
                                        </span>
                                        <span wire:loading wire:target="exportarReporte({{ $reporte->id }})">
                                            <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Generando...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
                </div>
            @else
                <div class="text-center text-muted py-5 bg-lighter rounded-3">
                    <i class="ti ti-clipboard-off fs-2 d-block mb-2"></i>
                    <h6 class="mb-1 fw-semibold text-dark">Sin reportes de asistencia</h6>
                    <p class="mb-0 small">Aún no se han creado reportes de asistencia para esta clase.</p>
                </div>
            @endif
        </div>
        @if ($reportesPaginados->hasPages())
            <div class="card-footer bg-transparent border-top d-flex justify-content-center py-3">
                {{ $reportesPaginados->links() }}
            </div>
        @endif
    </div>

    {{-- Modal para Editar/Registrar Detalles de Asistencia --}}
    {{-- El ID del modal debe ser único y el que tu JS espera para abrir/cerrar si usas JS de Bootstrap --}}
    {{-- Si controlas el modal puramente con Livewire, asegúrate que las clases 'show', 'd-block' se apliquen con $mostrarModalDetalles --}}

    {{-- Backdrop para el modal si se controla puramente con Livewire --}}


    @push('scripts')
        <script>
            document.addEventListener('livewire:initialized', () => {
                @this.on('abrirModalReporteAlumnos', (event) => {

                    $('#modalReporteAsistenciaAlumnosDetalleLivewire').modal('show');
                });

                @this.on('cerrarModalReporteAlumnos', (event) => {
                    $('#modalReporteAsistenciaAlumnosDetalleLivewire').modal('hide');

                });

                // Nueva lógica para los botones "Copiar Link"
                document.body.addEventListener('click', function(event) {
                    const button = event.target.closest('.copiar-link-btn');
                    if (button) {
                        const urlToCopy = button.dataset.url;

                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(urlToCopy)
                                .then(() => {
                                    // Éxito al copiar
                                    const originalText = button.innerHTML;
                                    button.innerHTML = '<i class="ti ti-check me-1"></i> ¡Copiado!';
                                    button.classList.add('btn-success'); // Opcional: cambiar color
                                    button.classList.remove('btn-outline-secondary');

                                    setTimeout(() => {
                                        button.innerHTML = originalText;
                                        button.classList.remove('btn-success');
                                        button.classList.add('btn-outline-secondary');
                                    }, 2500); // Volver al texto original después de 2.5 segundos
                                })
                                .catch(err => {
                                    console.error('Error al intentar copiar el link: ', err);
                                    // Opcional: Mostrar un error al usuario, por ejemplo, con SweetAlert
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error al copiar',
                                        text: 'No se pudo copiar el link al portapapeles. Inténtalo manualmente.',
                                        confirmButtonColor: '#696cff'
                                    });
                                });
                        } else {
                            // Fallback para navegadores muy antiguos o contextos no seguros (http)
                            console.warn('La API del portapapeles no está disponible.');
                            Swal.fire({
                                icon: 'warning',
                                title: 'No disponible',
                                text: 'La función de copiar no está disponible en tu navegador o en este contexto (se requiere HTTPS).',
                                confirmButtonColor: '#696cff'
                            });
                        }
                    }
                });
            });


            Livewire.on('mostrarNotificacion', (event) => {
                const detail = Array.isArray(event) ? event[0] :
                    event; // Livewire 3 puede pasar el evento en un array
                Swal.fire({
                    icon: 'success',
                    title: detail.titulo, // Título del modal
                    text: detail.texto, // Texto del cuerpo del modal
                    timer: detail.timer || 2500, // Duración antes de que se cierre solo (opcional)
                    showCancelButton: false,
                    showConfirmButton: false
                });
            });


            // Opcional: Si quieres que Livewire sepa cuando el modal se cierra por otros medios (ej. tecla ESC si keyboard no es false)
            // modalElement.addEventListener('hidden.bs.modal', function (event) {
            //     if (@this.mostrarModalDetalles) { // Solo si Livewire piensa que está abierto
            //         @this.call('resetearEstadoModal');
            //     }
            // });
        </script>
    @endpush
</div>
