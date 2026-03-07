<div>
    {{-- Sección de Listado de Reportes --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Reportes de Asistencia para:
                {{ $horarioAsignado->materiaPeriodo->materia->nombre ?? 'Clase' }}</h5>
        </div>
        <div class="card-body">
            @if ($reportesPaginados->isNotEmpty())
                {{-- INICIO DE LA SECCIÓN MODIFICADA --}}

                {{-- Encabezados para la vista de escritorio (md y superior) --}}
                <div class="row d-none d-md-flex fw-bold mb-3 border-bottom pb-2 align-items-center">
                    <div class="col-md-2">Fecha Clase</div>
                    <div class="col-md-1">Estado</div>
                    <div class="col-md-2 text-center">Reportados</div>
                    <div class="col-md-1 text-center">Presentes</div>
                    <div class="col-md-1 text-center">Ausentes</div>
                    <div class="col-md-2 text-center">Link Público</div>
                    <div class="col-md-3">Acciones</div>
                </div>

                @foreach ($reportesPaginados as $reporte)
                    <div class="report-item-card card mb-3 shadow-sm">
                        <div class="card-body">
                            <div class="row gy-3 align-items-center">
                                {{-- Columna: Fecha Clase --}}
                                <div class="col-12 col-md-2">
                                    <strong class="d-md-none">Fecha Clase: </strong>
                                    <span
                                        class="fw-medium">{{ \Carbon\Carbon::parse($reporte->fecha_clase_reportada)->format('d/m/Y') }}</span>
                                    <small
                                        class="d-block text-muted">{{ \Carbon\Carbon::parse($reporte->fecha_clase_reportada)->diffForHumans() }}</small>
                                </div>

                                {{-- Columna: Estado --}}
                                <div class="col-12 col-md-1">
                                    <strong class="d-md-none">Estado: </strong>
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
                                    <span
                                        class="badge {{ $estadoClass }} me-1">{{ ucfirst(str_replace('_', ' ', $reporte->estado_reporte)) }}</span>
                                </div>

                                {{-- Columnas de conteos --}}
                                <div class="col-4 col-md-2 text-md-center">
                                    <strong class="d-md-none">Reportados:
                                    </strong>{{ $reporte->detalles_asistencia_count }}
                                </div>
                                <div class="col-4 col-md-1 text-md-center">
                                    <strong class="d-md-none">Presentes: </strong>{{ $reporte->presentes_count }}
                                </div>
                                <div class="col-4 col-md-1 text-md-center">
                                    <strong class="d-md-none">Ausentes:
                                    </strong>{{ $reporte->detalles_asistencia_count - $reporte->presentes_count }}
                                </div>

                                {{-- Columna: Link Público --}}
                                <div class="col-12 col-md-2 text-md-center">
                                    <strong class="d-md-none d-block mb-1">Link Público: </strong>
                                    <div
                                        class="d-flex flex-column flex-sm-row flex-md-column justify-content-center gap-1">
                                        <a target="_blank"
                                            href="{{ route('maestros.reportarAutoAsistencia', ['horarioAsignado' => $horarioAsignado->id, 'reporte' => $reporte->id]) }}"
                                            class="btn btn-sm btn-outline-info">
                                            <i class="mdi mdi-link-variant me-1"></i> Ver Link
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-secondary copiar-link-btn"
                                            data-url="{{ route('maestros.reportarAutoAsistencia', ['horarioAsignado' => $horarioAsignado->id, 'reporte' => $reporte->id]) }}">
                                            <i class="mdi mdi-content-copy me-1"></i> Copiar Link
                                        </button>
                                    </div>
                                </div>

                                {{-- Columna: Acciones --}}
                                <div class="col-12 col-md-3">
                                    <strong class="d-md-none d-block mb-1">Acciones: </strong>
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
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                {{-- FIN DE LA SECCIÓN MODIFICADA --}}
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
