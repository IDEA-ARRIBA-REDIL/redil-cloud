<div>
    <style>
        .attendance-reports-list {
            --attendance-report-columns: minmax(7.5rem, 1.1fr) minmax(7rem, 1.25fr) minmax(4.5rem, .7fr) minmax(4.25rem, .65fr) minmax(4.25rem, .65fr) minmax(7rem, 1.2fr) minmax(8rem, 1.25fr);
        }

        .attendance-reports-header,
        .attendance-report-row {
            display: grid;
            grid-template-columns: var(--attendance-report-columns);
            gap: .5rem;
            align-items: center;
        }

        .attendance-reports-header {
            padding: 0 .75rem .75rem;
            border-bottom: 1px solid var(--bs-border-color);
            color: var(--bs-secondary-color);
            font-size: .7rem;
            font-weight: 600;
        }

        .attendance-reports-header > :not(:first-child):not(:nth-child(2)) {
            text-align: center;
        }

        .attendance-report-card {
            border: 0;
            box-shadow: none;
        }

        .attendance-report-row {
            min-height: 5.5rem;
            padding: .75rem;
        }

        .attendance-report-cell:not(.attendance-date):not(.attendance-status) {
            text-align: center;
        }

        .attendance-cell-label {
            display: none;
        }

        .attendance-status .badge {
            max-width: 100%;
            padding: .35rem .5rem;
            font-size: .6875rem;
            white-space: normal;
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
    <div class="card mb-4">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <h5 class="mb-0">Reportes de Asistencia para:
                {{ $horarioAsignado->materiaPeriodo->materia->nombre ?? 'Clase' }}</h5>
            <button type="button" class="btn btn-sm btn-outline-success" wire:click="exportarTodosLosReportes"
                wire:loading.attr="disabled" wire:target="exportarTodosLosReportes">
                <span wire:loading.remove wire:target="exportarTodosLosReportes">
                    <i class="ti ti-file-spreadsheet me-1"></i> Excel general
                </span>
                <span wire:loading wire:target="exportarTodosLosReportes">
                    <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Generando...
                </span>
            </button>
        </div>
        <div class="card-body">
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
                    <article wire:key="reporte-asistencia-{{ $reporte->id }}"
                        class="attendance-report-card card mb-2 {{ $loop->even ? 'bg-body-secondary' : '' }}">
                        <div class="attendance-report-row">
                            <div class="attendance-report-cell attendance-date">
                                <span class="attendance-cell-label">Fecha clase</span>
                                <span class="fw-medium">{{ \Carbon\Carbon::parse($reporte->fecha_clase_reportada)->format('d/m/Y') }}</span>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($reporte->fecha_clase_reportada)->diffForHumans() }}</small>
                            </div>

                            <div class="attendance-report-cell attendance-status">
                                <span class="attendance-cell-label">Estado</span>
                                    @php
                                        $estadoClass = '';
                                        if ($reporte->estado_reporte === 'pendiente_detalle') {
                                            $estadoClass = 'bg-label-warning';
                                        } elseif ($reporte->estado_reporte === 'completado') {
                                            $estadoClass = 'bg-label-success';
                                        } else {
                                            $estadoClass = 'bg-label-secondary';
                                        }
                                    @endphp
                                <span class="badge {{ $estadoClass }}">{{ ucfirst(str_replace('_', ' ', $reporte->estado_reporte)) }}</span>
                            </div>

                            <div class="attendance-report-cell">
                                <span class="attendance-cell-label">Reportados</span>
                                {{ $reporte->detalles_asistencia_count }}
                            </div>
                            <div class="attendance-report-cell">
                                <span class="attendance-cell-label">Presentes</span>
                                {{ $reporte->presentes_count }}
                            </div>
                            <div class="attendance-report-cell">
                                <span class="attendance-cell-label">Ausentes</span>
                                {{ $reporte->detalles_asistencia_count - $reporte->presentes_count }}
                            </div>

                            <div class="attendance-report-cell attendance-public-link">
                                <span class="attendance-cell-label">Link público</span>
                                <div class="attendance-link-actions">
                                    <a target="_blank"
                                        href="{{ route('maestros.reportarAutoAsistencia', ['horarioAsignado' => $horarioAsignado->id, 'reporte' => $reporte->id]) }}"
                                        class="btn btn-sm btn-outline-info">
                                        <i class="mdi mdi-link-variant me-1"></i> Ver link
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-secondary copiar-link-btn"
                                        data-url="{{ route('maestros.reportarAutoAsistencia', ['horarioAsignado' => $horarioAsignado->id, 'reporte' => $reporte->id]) }}">
                                        <i class="mdi mdi-content-copy me-1"></i> Copiar link
                                    </button>
                                </div>
                            </div>

                            <div class="attendance-report-cell attendance-actions">
                                <span class="attendance-cell-label">Acciones</span>
                                <div class="attendance-report-actions">
                                    <a href="{{ route('maestros.editarReporte', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado, 'reporte' => $reporte]) }}"
                                        class="btn btn-sm btn-primary waves-effect waves-light rounded-pill @if (!$this->verificarSiSePuedeEditarReporte($reporte)) disabled @endif"
                                        aria-disabled="{{ !$this->verificarSiSePuedeEditarReporte($reporte) }}">
                                        <i class="mdi mdi-pencil-outline me-1"></i>
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
            @endif
        </div>
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
                    if (event.target.matches('.copiar-link-btn')) {
                        const button = event.target;
                        const urlToCopy = button.dataset.url;

                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(urlToCopy)
                                .then(() => {
                                    // Éxito al copiar
                                    const originalText = button.innerHTML;
                                    button.innerHTML = '<i class="mdi mdi-check me-1"></i> ¡Copiado!';
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
