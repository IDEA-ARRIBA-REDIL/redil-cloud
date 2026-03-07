<div>
    {{-- TÍTULO PRINCIPAL (Siempre visible) --}}
    <div class="card-header mb-4">
        <h4 class="mb-0">Asistencia actividad: <span class="text-black fw-bold">{{ $actividad->nombre }}</span></h4>
        <p class="mb-0 text-dark">Registra la asistencia escaneando el QR o buscando manualmente en la lista.</p>
    </div>

    {{-- ================================================================= --}}
    {{-- INICIO DEL BLOQUEO DE CONTENIDO                                     --}}
    {{-- Solo se muestra si el módulo está desbloqueado --}}
    {{-- ================================================================= --}}
    @if ($desbloqueado)

        {{-- SECCIÓN DE BOTONES --}}
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                {{-- Botón existente para lanzar el scanner --}}
                <button type="button" class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#qrScannerModal">
                    <span class="ti ti-qrcode me-2"></span>
                    Escanear asistencia con QR
                </button>

                {{-- Botón de Exportar --}}
                <button wire:click="exportarAsistencias" class="btn rounded-pill btn-outline-secondary waves-effect">
                    <i class="ti ti-file-type-xls me-2"></i>
                    Exportar reporte
                </button>
            </div>
        </div>

        {{-- SECCIÓN DE BÚSQUEDA Y LISTADO --}}
        <div class="card-header pt-5">
            <p class="text-black fw-semibold mb-2">Búsqueda y registro manual</p>
            <div class="row">
                <div class="col-12">
                    <input type="text" class="form-control" placeholder="Buscar por nombre o identificación..." wire:model.live.debounce.300ms="busqueda">
                </div>
            </div>
        </div>

        {{-- SECCIÓN DE LISTA DE PARTICIPANTES --}}
        <div class="card-body">
            <div class="row">
                {{-- Spinner de carga para la búsqueda --}}
                <div wire:loading wire:target="busqueda" class="text-center my-3">
                    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Buscando...</span></div>
                </div>

                {{-- Contenedor de la lista (se oculta mientras se busca) --}}
                <div wire:loading.remove wire:target="busqueda">
                    <div class="d-flex justify-content-between align-items-center my-4 px-3">
                        <h6 class="text-black text-uppercase mb-0">Participante</h6>
                        <div class="text-end">
                            <h6 class="text-black text-uppercase mb-0">Asistencias</h6>
                            <small class="text-muted">Hoy: {{ \Carbon\Carbon::today()->isoFormat('dddd DD') }}</small>
                        </div>
                    </div>
                    
                    {{-- Lista --}}
                    <ul class="list-unstyled" style="max-height: 500px; overflow-y: auto;">
                        @forelse ($inscritos as $inscripcion)
                        <li wire:key="asistencia-item-{{ $inscripcion->id }}" class="mb-4 border-bottom pb-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="row g-2 align-items-center">
                                        {{-- Columna de Nombre e Identificación --}}
                                        <div class="col-7 d-flex flex-row">
                                            <div class="avatar me-3">
                                                @if ($inscripcion->user)
                                                @php $iniciales = mb_substr($inscripcion->user->primer_nombre ?? '', 0, 1) . mb_substr($inscripcion->user->primer_apellido ?? '', 0, 1); @endphp
                                                <span class="avatar-initial rounded-circle bg-label-primary">{{ $iniciales ?: 'N/A' }}</span>
                                                @else
                                                <span class="avatar-initial rounded-circle bg-label-secondary">INV</span>
                                                @endif
                                            </div>
                                            <div>
                                                @if ($inscripcion->user_id)
                                                <p class="mb-0 text-heading text-black fw-semibold">{{ $inscripcion->user->nombre(3) }}</p>
                                                <small class="text-muted">ID: {{ $inscripcion->user->identificacion ?? 'N/A' }}</small>
                                                @else
                                                <p class="mb-0 text-heading text-black fw-semibold">{{ $inscripcion->nombre_inscrito }}</p>
                                                <small class="badge bg-label-secondary">Invitado</small>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        {{-- Columna de Asistencia y Botones --}}
                                        <div style="margin-left:-10px" class="col-5 text-end d-flex align-items-center justify-content-end">
                                            {{-- Conteo total de asistencias --}}
                                            <div class="me-4">
                                                <span class="fw-bold fs-5">{{ $inscripcion->asistencias_count }}</span>
                                                <span class="text-muted">/ {{ $totalDiasActividad }}</span>
                                            </div>
                                            
                                            {{-- Lógica del botón de asistencia de HOY --}}
                                            @if (isset($asistenciasRegistradasHoy[$inscripcion->id]))
                                            {{-- Ya asistió hoy: Mostrar insignia --}}
                                            <span class="badge bg-label-primary rounded-pill">
                                                <i class="ti ti-check me-1"></i> <span class="text-white">Si </span>
                                            </span>
                                            @else
                                            {{-- No ha asistido hoy: Mostrar botón --}}
                                            <button wire:click="toggleAsistencia({{ $inscripcion->id }})" 
                                                    wire:loading.attr="disabled" 
                                                    wire:target="toggleAsistencia({{ $inscripcion->id }})"
                                                    class="btn btn-secondary btn-sm rounded-pill">
                                                Registrar
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @empty
                        {{-- Mensaje si no hay resultados --}}
                        <div class="text-center p-4">
                            @if(empty(trim($busqueda)))
                                <p class="text-muted">No se encontraron participantes inscritos en esta actividad.</p>
                            @else
                                <p class="text-muted">No se encontraron resultados para "{{ $busqueda }}".</p>
                            @endif
                        </div>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

    @else
        {{-- ================================================================= --}}
        {{-- SE MUESTRA SI EL MÓDULO ESTÁ BLOQUEADO                        --}}
        {{-- ================================================================= --}}
        <div class="card-body">
            <div class="alert alert-warning text-center" role="alert">
                <h5 class="alert-heading"><i class="ti ti-lock me-2"></i>Módulo Bloqueado</h5>
                <p class="mb-0">Debes ingresar la contraseña correcta para acceder a la gestión de asistencia.</p>
            </div>
        </div>
    @endif
    {{-- =MA=============================================================== --}}
    {{-- FIN DEL BLOQUEO DE CONTENIDO                                        --}}
    {{-- ================================================================= --}}


    
    {{-- ================================================================= --}}
    {{-- MODALES (Deben estar fuera del @if para que los scripts los encuentren) --}}
    {{-- ================================================================= --}}

    {{-- 1. Modal del Escáner QR (Existente) --}}
    <div class="modal fade" id="qrScannerModal" tabindex="-1" aria-labelledby="qrScannerModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrScannerModalLabel">Escanear Código QR de Asistencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- El componente del scanner ahora se carga aquí dentro --}}
                    @livewire('QrScanner.qr-scanner', ['actividad' => $actividad])
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Modal de Contraseña de Asistencia (Nuevo) --}}
    <div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true" 
         wire:ignore.self 
         data-bs-backdrop="static" {{-- No se puede cerrar haciendo clic fuera --}}
         data-bs-keyboard="false"> {{-- No se puede cerrar con 'Esc' --}}
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordModalLabel">Acceso Restringido</h5>
                </div>
                <div class="modal-body">
                    <p>Esta actividad está protegida. Por favor, ingresa la contraseña para gestionar la asistencia.</p>
                    
                    <form wire:submit.prevent="validarContrasena">
                        <div class="mb-3">
                            <label for="contrasenaIngresada" class="form-label">Contraseña</label>
                            <input type="password" 
                                   class="form-control @error('contrasenaIngresada') is-invalid @enderror" 
                                   id="contrasenaIngresada" 
                                   wire:model="contrasenaIngresada"
                                   placeholder="****"
                                   autofocus>
                            @error('contrasenaIngresada')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill w-100" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="validarContrasena">
                                Desbloquear
                            </span>
                            <span wire:loading wire:target="validarContrasena">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Validando...
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    {{-- ========================================================== --}}
    {{-- SCRIPT PARA CONTROLAR EL MODAL DE CONTRASEÑA             --}}
    {{-- ========================================================== --}}
    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            // 1. Instanciar el modal de Bootstrap
            const passwordModalEl = document.getElementById('passwordModal');
            if (passwordModalEl) {
                const passwordModal = new bootstrap.Modal(passwordModalEl);

                // 2. Comprobar si se debe mostrar el modal al cargar la página
                if (@json($requiereContrasena) && !@json($desbloqueado)) {
                    passwordModal.show();
                }

                // 3. Escuchar el evento de éxito de Livewire para cerrar el modal
                Livewire.on('contrasena-correcta', () => {
                    passwordModal.hide();
                });
            }
        });
    </script>
    @endpush

</div>