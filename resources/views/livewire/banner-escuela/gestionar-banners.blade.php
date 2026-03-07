<div>
    {{-- Botón para abrir el modal de creación --}}
    <div class="d-flex justify-content-end mb-4">
        <button wire:click="crear()" class="btn btn-primary rounded-pill">
            <i class="mdi mdi-plus me-1"></i>
            Crear nuevo banner
        </button>
    </div>

    {{-- Listado de Banners en formato de Tarjetas --}}
    <div class="row g-4">
        @forelse ($banners as $banner)
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100">
                    <img src="{{ $banner->imagen_url }}" class="card-img-top" alt="Imagen del banner" style="height: 180px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                   
                        <p class="card-text flex-grow-1 fw-semibold">{{ $banner->descripcion ?: 'Sin descripción.' }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            @if ($banner->activo)
                                <span class="badge bg-label-success">Activo</span>
                            @else
                                <span class="badge bg-label-danger">Inactivo</span>
                            @endif
                            <div>
                                <button wire:click="editar({{ $banner->id }})" class="btn btn-sm btn-outline-secondary"><i class="ti ti-pencil"></i></button>
                                <button wire:click="confirmarBorrado({{ $banner->id }})" class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    Aún no hay banners informativos creados.
                </div>
            </div>
        @endforelse
    </div>

    {{-- Modal para Crear/Editar Banner (Estilo Bootstrap) --}}
    @if($modalVisible)
        <div class="modal fade show" style="display: block;" tabindex="-1" >
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $bannerId ? 'Editar' : 'Crear' }} Banner</h5>
                        <button wire:click="$set('modalVisible', false)" type="button" class="btn-close"></button>
                    </div>
                    <form wire:submit.prevent="guardar">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="imagen" class="form-label">Imagen del banner (Máx 5MB)</label>
                                <input wire:model="imagen" type="file" class="form-control" id="imagen">
                                <div wire:loading wire:target="imagen" class="small text-muted mt-1">Subiendo...</div>
                                @error('imagen') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción (opcional):</label>
                                <textarea wire:model="descripcion" class="form-control" id="descripcion" rows="4" placeholder="Ej: Inscripciones abiertas para el nuevo semestre..."></textarea>
                                @error('descripcion') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                             <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="activo" wire:model="activo">
                                <label class="form-check-label" for="activo">Mantener activo</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button wire:click="$set('modalVisible', false)" type="button" class="btn btn-outline-secondary rounded-pill">Cancelar</button>
                            <button type="submit" class="btn btn-primary rounded-pill">
                                <span wire:loading wire:target="guardar" class="spinner-border  spinner-border-sm" role="status" aria-hidden="true"></span>
                                Guardar 
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    @section('page-script')
    <script>
        document.addEventListener('livewire:initialized', () => {

            // Escucha el evento 'notificacion' para mostrar alertas de éxito/error
            Livewire.on('notificacion', (event) => {
                Swal.fire({
                    icon: event.icono,
                    title: event.titulo,
                    text: event.mensaje,
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            });

            // Escucha el evento 'confirmar-eliminacion' para el borrado
            Livewire.on('confirmar-eliminacion', (event) => {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡No podrás revertir esta acción!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, ¡eliminar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Si el usuario confirma, se llama al método 'eliminarBanner' del backend
                        Livewire.dispatch('eliminarBanner', { id: event.id });
                    }
                })
            });
        });
    </script>
    @endsection
</div>