<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="ti ti-user-check text-info me-2"></i>
                Control de Asistencias — {{ $hito->titulo }}
            </h4>
            <p class="text-muted mb-0">
                Actividad: <strong>{{ $hito->actividad->nombre ?? 'Sin Actividad' }}</strong> |
                Fecha: <strong>{{ $hito->actividad?->fecha ? substr((string)$hito->actividad->fecha, 0, 10) : 'N/A' }}</strong>
            </p>
        </div>
        <a href="{{ route('hitos.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Volver a Hitos
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Buscar usuario por nombre o correo...">
                    </div>
                </div>
                <div class="col-md-6 text-md-end mt-2 mt-md-0">
                    <span class="text-muted small">Al marcar la casilla, el hito se añade automáticamente a la línea de vida del creyente.</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Usuario</th>
                        <th>Correo Electrónico</th>
                        <th>Sede</th>
                        <th class="text-center" style="width: 140px;">¿Asistió?</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($usuarios as $u)
                        @php
                            $asistio = in_array($u->id, $asistentesIds);
                        @endphp
                        <tr wire:key="user-row-{{ $u->id }}">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2 bg-label-primary rounded-circle d-flex align-items-center justify-content-center">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold">{{ $u->name }}</h6>
                                        <small class="text-muted">{{ $u->tipoUsuario->nombre ?? 'Miembro' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->sede->nombre ?? 'Principal' }}</td>
                            <td class="text-center">
                                <button type="button"
                                        wire:click="marcarAsistencia({{ $u->id }}, {{ $asistio ? 'false' : 'true' }})"
                                        class="btn btn-sm {{ $asistio ? 'btn-success' : 'btn-outline-secondary' }}">
                                    <i class="ti ti-{{ $asistio ? 'check' : 'plus' }} me-1"></i>
                                    {{ $asistio ? 'Confirmado' : 'Marcar' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                No se encontraron usuarios coincidentes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer border-top p-3">
            {{ $usuarios->links() }}
        </div>
    </div>

    @script
    <script>
        $wire.on('msn', (data) => {
            let info = Array.isArray(data) ? data[0] : data;
            let icono = info?.msnIcono || info?.tipo || 'info';
            let titulo = info?.msnTitulo || (icono === 'success' ? '¡Éxito!' : 'Notificación');
            let texto = info?.msnTexto || info?.mensaje || '';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: icono,
                    title: titulo,
                    html: texto,
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false
                });
            }
        });
    </script>
    @endscript
</div>
