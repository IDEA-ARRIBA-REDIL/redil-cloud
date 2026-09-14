<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Login predeterminado</h4>
            <p class="text-muted mb-0">Se muestra en todas las iglesias que no tienen personalización de marca activa.</p>
        </div>
        <a href="{{ url('/admin/dashboard') }}" class="btn btn-label-secondary"><i class="bx bx-arrow-back me-1"></i>Volver</a>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form wire:submit="guardar">
        <div class="card mb-4">
            <div class="card-header border-bottom"><h5 class="mb-0">Fondo del panel de acceso</h5></div>
            <div class="card-body pt-4">
                <div class="row g-4 align-items-start">
                    <div class="col-md-5">
                        @if($imagenIzquierda)
                            <img src="{{ $imagenIzquierda->temporaryUrl() }}" class="rounded border w-100" style="height: 260px; object-fit: cover" alt="Vista previa">
                        @elseif($branding->left_image_path)
                            <img src="{{ Storage::disk('global_media')->url($branding->left_image_path) }}" class="rounded border w-100" style="height: 260px; object-fit: cover" alt="Fondo actual">
                        @else
                            <div class="rounded border bg-label-secondary d-flex align-items-center justify-content-center" style="height: 260px">Se usa el fondo de color actual</div>
                        @endif
                    </div>
                    <div class="col-md-7">
                        <label class="form-label" for="imagenIzquierda">Nueva imagen</label>
                        <input id="imagenIzquierda" type="file" class="form-control" wire:model="imagenIzquierda" accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">JPG, PNG o WebP. Máximo 5 MB. Recomendado: 1000 × 1200 px.</div>
                        <div wire:loading wire:target="imagenIzquierda" class="small text-muted mt-2">Cargando imagen...</div>
                        @error('imagenIzquierda') <div class="text-danger small">{{ $message }}</div> @enderror
                        @if($branding->left_image_path)
                            <button type="button" class="btn btn-label-danger btn-sm mt-3" wire:click="eliminarImagenIzquierda" wire:confirm="¿Restaurar el fondo de color predeterminado?">Restaurar fondo de color</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Carrusel derecho</h5>
                <span class="badge bg-label-primary">{{ $branding->slides->count() }} / 20</span>
            </div>
            <div class="card-body pt-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label class="form-label" for="nuevasImagenes">Agregar imágenes</label>
                        <input id="nuevasImagenes" type="file" class="form-control" wire:model="nuevasImagenes" accept="image/jpeg,image/png,image/webp" multiple>
                        <div class="form-text">Hasta 20 imágenes en total, máximo 5 MB cada una. Recomendado: 1400 × 1200 px.</div>
                        <div wire:loading wire:target="nuevasImagenes" class="small text-muted mt-2">Cargando imágenes...</div>
                        @error('nuevasImagenes.*') <div class="text-danger small">{{ $message }}</div> @enderror
                        @error('nuevasImagenes') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="intervalo">Cambio automático</label>
                        <div class="input-group"><input id="intervalo" type="number" class="form-control" wire:model="intervalo" min="3000" max="15000" step="500"><span class="input-group-text">ms</span></div>
                        @error('intervalo') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row g-3">
                    @forelse($branding->slides as $index => $slide)
                        <div class="col-sm-6 col-xl-4" wire:key="default-slide-{{ $slide->id }}">
                            <div class="card border h-100">
                                <img src="{{ Storage::disk('global_media')->url($slide->image_path) }}" class="card-img-top" style="height: 180px; object-fit: cover" alt="{{ $slide->alt_text }}">
                                <div class="card-body p-3">
                                    <label class="form-label small" for="default-slide-url-{{ $slide->id }}">Enlace al hacer clic</label>
                                    <input id="default-slide-url-{{ $slide->id }}" type="url" class="form-control form-control-sm mb-3" wire:model="slideUrls.{{ $slide->id }}" placeholder="https://ejemplo.com/pagar">
                                    @error('slideUrls.'.$slide->id) <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <span class="badge {{ $slide->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">{{ $slide->is_active ? 'Activa' : 'Oculta' }}</span>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-label-secondary" wire:click="moverSlide({{ $slide->id }}, 'up')" @disabled($index === 0)><i class="bx bx-up-arrow-alt"></i></button>
                                            <button type="button" class="btn btn-label-secondary" wire:click="moverSlide({{ $slide->id }}, 'down')" @disabled($index === $branding->slides->count() - 1)><i class="bx bx-down-arrow-alt"></i></button>
                                            <button type="button" class="btn btn-label-warning" wire:click="alternarSlide({{ $slide->id }})"><i class="bx bx-show"></i></button>
                                            <button type="button" class="btn btn-label-danger" wire:click="eliminarSlide({{ $slide->id }})" wire:confirm="¿Eliminar esta imagen?"><i class="bx bx-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><div class="alert alert-warning mb-0">Sin imágenes cargadas se utilizará Banner-login.png como respaldo.</div></div>
                    @endforelse
                </div>
            </div>
        </div>

        <button class="btn btn-primary" type="submit" wire:loading.attr="disabled" wire:target="guardar,imagenIzquierda,nuevasImagenes">
            <span wire:loading.remove wire:target="guardar">Guardar cambios</span><span wire:loading wire:target="guardar">Guardando...</span>
        </button>
    </form>
</div>
