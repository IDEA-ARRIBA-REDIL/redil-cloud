<div>
    @if (session()->has('mensaje_exito'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('mensaje_exito') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('mensaje_error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('mensaje_error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-none bg-transparent">
        <div class="card-header ms-3 px-0 py-3 d-flex gap-2">
            <button type="button" class="btn btn-primary rounded-pill" wire:click="abrirModalAnadir">
                <i class="ti ti-plus me-1"></i> Añadir Grado al Periodo
            </button>
            <button type="button" class="btn btn-outline-primary rounded-pill" wire:click="abrirModalDuplicar">
                <i class="ti ti-copy me-1"></i> Duplicar de otro periodo
            </button>
        </div>

        <div class="row equal-height-row m-3">
            @if ($nivelesPeriodo->count() > 0)
                @foreach ($nivelesPeriodo as $nivelP)
                    <div class="col-12 col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 border shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar avatar-md me-3">
                                        <span class="avatar-initial rounded bg-label-primary">
                                            <i class="ti ti-topology-star-3 ti-md"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 fw-semibold text-black">{{ $nivelP->nivelEscuela->nombre }}</h5>
                                        <small class="text-muted">Grado Académico</small>
                                    </div>
                                </div>

                                <div class="d-flex flex-column gap-2 mb-4">
                                    <div class="d-flex align-items-center text-black">
                                        <i class="ti ti-books me-2"></i>
                                        <span>Materias asociadas:
                                            <strong>{{ \App\Models\MateriaPeriodo::where('periodo_id', $periodo->id)->where('nivel_id', $nivelP->nivel_escuela_id)->count() }}</strong></span>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="{{ route('periodo.materias', ['periodo' => $periodo->id, 'nivel_id' => $nivelP->nivel_escuela_id]) }}"
                                        class="btn btn-outline-primary btn-sm rounded-pill flex-grow-1">
                                        <i class="ti ti-settings me-1"></i> Gestionar Materias
                                    </a>
                                    <button type="button" wire:click="confirmarEliminar({{ $nivelP->id }})"
                                        class="btn btn-outline-danger btn-sm rounded-circle p-1" title="Quitar grado">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12">
                    <div class="alert m-3 alert-secondary text-center py-5 border-dashed" role="alert">
                        <i class="ti ti-info-circle ti-lg d-block mb-2"></i>
                        No hay grados asociados a este periodo todavía.<br>
                        Comience añadiendo uno usando el botón superior.
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Añadir Grado --}}
    @if ($mostrarModalAnadir)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-semibold">Asociar Grado al Periodo</h5>
                        <button type="button" class="btn-close"
                            wire:click="$set('mostrarModalAnadir', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-black">Seleccionar Grado Disponible</label>
                            <select class="form-select @error('nivelSeleccionado') is-invalid @enderror"
                                wire:model="nivelSeleccionado">
                                <option value="">Seleccione un grado...</option>
                                @foreach ($nivelesDisponibles as $nivel)
                                    <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                                @endforeach
                            </select>
                            @error('nivelSeleccionado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <p class="small text-muted">
                            <i class="ti ti-info-circle me-1"></i>
                            Solo se muestran los grados configurados para esta escuela que aún no han sido añadidos a
                            este periodo.
                        </p>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill"
                            wire:click="$set('mostrarModalAnadir', false)">Cancelar</button>
                        <button type="button" class="btn btn-primary rounded-pill" wire:click="anadirNivel"
                            wire:loading.attr="disabled">
                            <span wire:loading wire:target="anadirNivel"
                                class="spinner-border spinner-border-sm me-1"></span>
                            Asociar Grado
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    {{-- Modal Duplicar Configuración --}}
    @if ($mostrarModalDuplicar)
            <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-semibold">Duplicar Configuración de Periodo</h5>
                            <button type="button" class="btn-close"
                                wire:click="$set('mostrarModalDuplicar', false)"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label text-black">Seleccionar Periodo Origen</label>
                                <select class="form-select @error('periodoOrigenId') is-invalid @enderror"
                                    wire:model="periodoOrigenId">
                                    <option value="">Seleccione un periodo...</option>
                                    @foreach ($periodosDisponiblesParaDuplicar as $p)
                                        <option value="{{ $p->id }}">{{ $p->nombre }}
                                            ({{ $p->fecha_inicio->format('Y') }})</option>
                                    @endforeach
                                </select>
                                @error('periodoOrigenId')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="alert alert-warning mb-0">
                                <i class="ti ti-alert-triangle me-1"></i>
                                <strong>Atención:</strong> Esta acción copiará todos los grados, sus materias asociadas,
                                horarios e ítems de evaluación del periodo seleccionado a este periodo. Las materias que
                                ya existen no se duplicarán.
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0">
                            <button type="button" class="btn btn-outline-secondary rounded-pill"
                                wire:click="$set('mostrarModalDuplicar', false)">Cancelar</button>
                            <button type="button" class="btn btn-primary rounded-pill"
                                wire:click="duplicarConfiguracion" wire:loading.attr="disabled">
                                <span wire:loading wire:target="duplicarConfiguracion"
                                    class="spinner-border spinner-border-sm me-1"></span>
                                Confirmar y Duplicar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @push('scripts')
            <script>
                document.addEventListener('livewire:init', () => {
                    Livewire.on('confirmar-eliminar-nivel', (nivelPeriodoId) => {
                        Swal.fire({
                            title: '¿Quitar grado?',
                            text: 'Se eliminará la asociación de este grado con el periodo actual. Las materias ya registradas no se verán afectadas, pero se recomienda limpiar las materias antes.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, quitar',
                            cancelButtonText: 'Cancelar',
                            customClass: {
                                confirmButton: 'btn btn-danger me-3',
                                cancelButton: 'btn btn-label-secondary'
                            },
                            buttonsStyling: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Livewire.dispatch('eliminarNivelConfirmado', {
                                    nivelPeriodoId: nivelPeriodoId
                                });
                            }
                        });
                    });
                });
            </script>
        @endpush
</div>
