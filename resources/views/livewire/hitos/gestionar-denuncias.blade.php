<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti ti-flag text-warning me-2"></i>Bandeja de Moderación y Denuncias</h4>
            <p class="text-muted mb-0">Revisa reportes enviados por miembros de la congregación sobre fotos o contenido en hitos.</p>
        </div>
        <a href="{{ route('hitos.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Volver a Hitos
        </a>
    </div>

    {{-- Filtro de Estado --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex gap-2">
                <button type="button" class="btn {{ $filtroEstado === 'pendiente' ? 'btn-warning' : 'btn-outline-secondary' }}"
                        wire:click="$set('filtroEstado', 'pendiente')">
                    <i class="ti ti-clock me-1"></i> Pendientes
                </button>
                <button type="button" class="btn {{ $filtroEstado === 'resuelta' ? 'btn-success' : 'btn-outline-secondary' }}"
                        wire:click="$set('filtroEstado', 'resuelta')">
                    <i class="ti ti-circle-check me-1"></i> Resueltas
                </button>
            </div>
        </div>
    </div>

    {{-- Tabla de Denuncias --}}
    <div class="card shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Hito / Elemento</th>
                        <th>Reportado por</th>
                        <th>Motivo del Reporte</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th class="text-center" style="width: 200px;">Acciones</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($denuncias as $d)
                        <tr wire:key="denuncia-{{ $d->id }}">
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($d->foto)
                                        <img src="{{ $d->foto->url }}" class="rounded me-2 object-fit-cover" style="width: 48px; height: 48px;">
                                    @endif
                                    <div>
                                        <h6 class="mb-0 fw-semibold">{{ $d->hito->titulo ?? 'Hito no disponible' }}</h6>
                                        <small class="text-muted">{{ $d->foto ? 'Foto reportada' : 'Hito reportado' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $d->user->name ?? 'Usuario anónimo' }}</div>
                                <small class="text-muted">{{ $d->user->email ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-label-danger">{{ $d->motivo }}</span>
                            </td>
                            <td>{{ $d->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge {{ $d->estado === 'pendiente' ? 'bg-label-warning' : 'bg-label-success' }}">
                                    {{ ucfirst($d->estado) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($d->estado === 'pendiente')
                                    <div class="d-flex justify-content-center gap-1">
                                        @if($d->foto)
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    wire:click="eliminarFotoReportada({{ $d->id }}, {{ $d->foto->id }})"
                                                    onclick="confirm('¿Eliminar definitivamente esta foto?') || event.stopImmediatePropagation();"
                                                    title="Eliminar Foto y Resolver">
                                                <i class="ti ti-trash me-1"></i> Borrar Foto
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-success"
                                                wire:click="marcarResuelta({{ $d->id }})" title="Marcar como resuelta">
                                            <i class="ti ti-check me-1"></i> Resolver
                                        </button>
                                    </div>
                                @else
                                    <small class="text-muted">
                                        Resuelto por {{ $d->resueltoPor->name ?? 'Admin' }}<br>
                                        <em>{{ $d->observaciones_admin ?: 'Sin notas' }}</em>
                                    </small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="ti ti-shield-check fs-1 text-success mb-2 d-block"></i>
                                No hay denuncias {{ $filtroEstado }}s en este momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer border-top p-3">
            {{ $denuncias->links() }}
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
