<div>
    {{-- Botón para abrir el modal de creación --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            
        </div>
        <button wire:click="crear()" class="btn btn-primary rounded-pill waves-effect waves-light">
            <i class="ti ti-plus me-1"></i>
            Crear banner
        </button>
    </div>

    {{-- Listado de Banners en formato de Tarjetas --}}
    <div class="row g-4">
        @forelse ($banners as $banner)
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100 border">
                    <div class="position-relative">
                        <img src="{{ $banner->imagen_url }}" class="card-img-top" alt="Imagen del banner" style="height: 180px; width: 100%; object-fit: cover;">
                        <span class="position-absolute top-0 end-0 m-2 badge rounded-pill {{ $banner->activo ? 'bg-success' : 'bg-danger' }}">
                            {{ $banner->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                    <div class="card-body d-flex flex-column p-3">
                        <p class="card-text flex-grow-1 text-dark mb-3 fw-medium">
                            {{ $banner->descripcion ?: 'Sin descripción.' }}
                        </p>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <small class="text-muted">
                                <i class="ti ti-calendar me-1"></i>{{ $banner->created_at ? $banner->created_at->format('Y-m-d') : '' }}
                            </small>
                            <div class="btn-group btn-group-sm">
                                <button wire:click="editar({{ $banner->id }})" class="btn btn-outline-secondary rounded-pill me-1" title="Editar banner">
                                    <i class="ti ti-pencil"></i>
                                </button>
                                <button type="button" onclick="confirmarEliminarBanner({{ $banner->id }})" class="btn btn-outline-danger rounded-pill" title="Eliminar banner">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card text-center p-5 border">
                    <div class="card-body">
                        <i class="ti ti-photo-off text-black display-4 mb-3 d-block"></i>
                        <h5 class="text-black fw-semibold">No hay banners creados</h5>
                        <p class="text-black mb-4">Crea el primer banner informativo para mostrarlo en el dashboard de los alumnos y profesores.</p>
                        <button wire:click="crear()" class="btn btn-primary rounded-pill">
                            <i class="ti ti-plus me-1"></i> Crear banner
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Modal Único para Crear/Editar Banner con Cropper integrado --}}
    @if($modalVisible)
        <div class="modal fade show" style="display: block; background-color: rgba(0, 0, 0, 0.5); z-index: 1050;" tabindex="-1" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom pb-3">
                        <h5 class="modal-title fw-bold">
                            <i class="ti {{ $bannerId ? 'ti-edit' : 'ti-photo-plus' }} me-2 text-primary"></i>
                            {{ $bannerId ? 'Editar banner' : 'Crear nuevo banner' }}
                        </h5>
                        <button wire:click="cerrarModal" type="button" class="btn-close" aria-label="Cerrar"></button>
                    </div>
                    <form wire:submit.prevent="guardar">
                        <div class="modal-body p-4">
                            {{-- Sección de Imagen con Cropper integrado --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold d-block text-dark">
                                    Imagen del banner <span class="text-danger">*</span>
                                </label>
                                <small class="text-black d-block mb-2">Selecciona y recorta la imagen en formato panorámico para una visualización óptima.</small>

                                {{-- Input de archivo invisible que dispara el cropper --}}
                                <input type="file" id="inputArchivoBanner" class="d-none" accept="image/jpeg,image/png,image/webp,image/jpg" onchange="window.manejarSeleccionImagenBanner(event)">

                                {{-- 1. Contenedor de Recorte (In-place Cropper dentro del mismo modal) --}}
                                <div id="areaRecorteBanner" style="display: none;" class="border rounded-3 p-3 bg-dark text-center mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-white small fw-semibold"><i class="ti ti-crop me-1"></i> Ajusta el encuadre del banner:</span>
                                        <span class="badge bg-label-primary rounded-pill">16:6 (Panorámico)</span>
                                    </div>
                                    <div class="img-container mb-3" style="max-height: 350px; min-height: 200px; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; background-color: #111;">
                                        <img id="croppingImageBanner" src="" style="max-width: 100%; max-height: 330px; display: block;" alt="Imagen para recortar">
                                    </div>

                                    {{-- Controles y Botones de acción del Cropper --}}
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2 border-top border-secondary">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-light" onclick="window.cropperBannerInstance && window.cropperBannerInstance.zoom(0.1)" title="Acercar">
                                                <i class="ti ti-zoom-in"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-light" onclick="window.cropperBannerInstance && window.cropperBannerInstance.zoom(-0.1)" title="Alejar">
                                                <i class="ti ti-zoom-out"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-light" onclick="window.cropperBannerInstance && window.cropperBannerInstance.rotate(-90)" title="Girar izquierda">
                                                <i class="ti ti-rotate-2"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-light" onclick="window.cropperBannerInstance && window.cropperBannerInstance.rotate(90)" title="Girar derecha">
                                                <i class="ti ti-rotate-clockwise-2"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-light" onclick="window.cropperBannerInstance && window.cropperBannerInstance.reset()" title="Restablecer">
                                                <i class="ti ti-refresh"></i>
                                            </button>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-light rounded-pill" onclick="window.cancelarRecorteBanner()">
                                                Cancelar recorte
                                            </button>
                                            <button type="button" class="btn btn-sm btn-primary rounded-pill" id="btnAplicarRecorteBanner" onclick="window.aplicarRecorteBanner()">
                                                <span id="btnRecorteSpinnerBanner" class="spinner-border spinner-border-sm me-1 d-none" role="status" aria-hidden="true"></span>
                                                <span id="btnRecorteTextoBanner"><i class="ti ti-check me-1"></i> Aplicar recorte</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- 2. Previsualización Normal / Botón de Selección --}}
                                <div id="areaPreviewBanner" class="border rounded-3 p-3 bg-light text-center">
                                    @if ($rutaArchivoSubida)
                                        {{-- Nueva imagen recortada subida temporalmente --}}
                                        <div class="position-relative d-inline-block w-100">
                                            <img src="{{ tenant_asset($rutaArchivoSubida) }}" class="rounded shadow-sm img-fluid" style="max-height: 220px; width: 100%; object-fit: cover;" alt="Preview Banner">
                                            <div class="mt-2 d-flex justify-content-center gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" onclick="document.getElementById('inputArchivoBanner').click()">
                                                    <i class="ti ti-crop me-1"></i> Cambiar / Recortar otra imagen
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" wire:click="eliminarArchivoLocal">
                                                    <i class="ti ti-trash me-1"></i> Quitar imagen
                                                </button>
                                            </div>
                                        </div>
                                    @elseif ($bannerId && ($bannerActual = \App\Models\BannerEscuela::find($bannerId)) && $bannerActual->imagen)
                                        {{-- Imagen existente del banner al editar --}}
                                        <div class="position-relative d-inline-block w-100">
                                            <img src="{{ $bannerActual->imagen_url }}" class="rounded shadow-sm img-fluid" style="max-height: 220px; width: 100%; object-fit: cover;" alt="Banner Actual">
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" onclick="document.getElementById('inputArchivoBanner').click()">
                                                    <i class="ti ti-crop me-1"></i> Cambiar / Recortar nueva imagen
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        {{-- Estado inicial sin imagen --}}
                                        <div class="py-4">
                                            <i class="ti ti-cloud-upload text-primary display-4 mb-2 d-block"></i>
                                            <p class="text-dark fw-medium mb-1">Haz clic para seleccionar y recortar una imagen</p>
                                            <p class="text-muted small mb-3">Formatos admitidos: JPG, PNG, WEBP (Máx. 5MB)</p>
                                            <button type="button" class="btn btn-primary rounded-pill" onclick="document.getElementById('inputArchivoBanner').click()">
                                                <i class="ti ti-photo me-1"></i> Seleccionar imagen
                                            </button>
                                        </div>
                                    @endif
                                </div>
                                @error('rutaArchivoSubida')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Descripción --}}
                            <div class="mb-3">
                                <label for="descripcion" class="form-label fw-bold text-dark">Descripción o título del banner (opcional):</label>
                                <textarea wire:model="descripcion" class="form-control" id="descripcion" rows="3" placeholder="Ej: Inscripciones abiertas para el nuevo ciclo..."></textarea>
                                @error('descripcion') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            {{-- Estado Activo --}}
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="activo" wire:model="activo">
                                <label class="form-check-label fw-medium text-dark" for="activo">Banner visible y activo</label>
                            </div>
                        </div>
                        <div class="modal-footer border-top pt-3">
                            <button wire:click="cerrarModal" type="button" class="btn btn-outline-secondary rounded-pill">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary rounded-pill" wire:loading.attr="disabled">
                                <span wire:loading wire:target="guardar" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                Guardar banner
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

@assets
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@endassets

@script
<script>
    window.cropperBannerInstance = null;

    // Confirmación de eliminación con SweetAlert2
    window.confirmarEliminarBanner = function(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir la eliminación de este banner!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, ¡eliminar!',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'btn btn-primary me-2 rounded-pill',
                cancelButton: 'btn btn-label-secondary rounded-pill'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $wire.eliminarBanner(id);
            }
        });
    };

    // Selección de imagen para recortar (In-place)
    window.manejarSeleccionImagenBanner = function(event) {
        const file = event.target.files ? event.target.files[0] : null;
        if (!file) return;

        if (!file.type.match(/^image\/(jpeg|png|jpg|webp)$/i)) {
            Swal.fire({
                title: 'Formato no válido',
                text: 'Por favor selecciona una imagen válida (JPG, PNG o WEBP).',
                icon: 'error',
                customClass: { confirmButton: 'btn btn-primary rounded-pill' },
                buttonsStyling: false
            });
            event.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const croppingImage = document.getElementById('croppingImageBanner');
            const areaRecorte = document.getElementById('areaRecorteBanner');
            const areaPreview = document.getElementById('areaPreviewBanner');

            if (croppingImage && areaRecorte && areaPreview) {
                croppingImage.src = e.target.result;
                areaPreview.style.display = 'none';
                areaRecorte.style.display = 'block';

                if (window.cropperBannerInstance) {
                    window.cropperBannerInstance.destroy();
                    window.cropperBannerInstance = null;
                }

                setTimeout(() => {
                    window.cropperBannerInstance = new Cropper(croppingImage, {
                        aspectRatio: 16 / 6,
                        viewMode: 1,
                        autoCropArea: 1,
                        responsive: true,
                        restore: false,
                        checkCrossOrigin: false,
                        zoomable: true,
                        cropBoxResizable: true
                    });
                }, 50);
            }
        };
        reader.readAsDataURL(file);
    };

    // Cancelar el recorte y volver a la vista previa
    window.cancelarRecorteBanner = function() {
        if (window.cropperBannerInstance) {
            window.cropperBannerInstance.destroy();
            window.cropperBannerInstance = null;
        }

        const areaRecorte = document.getElementById('areaRecorteBanner');
        const areaPreview = document.getElementById('areaPreviewBanner');
        const inputArchivo = document.getElementById('inputArchivoBanner');

        if (areaRecorte) areaRecorte.style.display = 'none';
        if (areaPreview) areaPreview.style.display = 'block';
        if (inputArchivo) inputArchivo.value = '';
    };

    // Aplicar recorte y subir directamente
    window.aplicarRecorteBanner = function() {
        if (!window.cropperBannerInstance) return;

        const btnAplicarRecorte = document.getElementById('btnAplicarRecorteBanner');
        const btnRecorteSpinner = document.getElementById('btnRecorteSpinnerBanner');
        const btnRecorteTexto = document.getElementById('btnRecorteTextoBanner');

        if (btnAplicarRecorte) btnAplicarRecorte.disabled = true;
        if (btnRecorteSpinner) btnRecorteSpinner.classList.remove('d-none');
        if (btnRecorteTexto) btnRecorteTexto.innerHTML = 'Subiendo...';

        const canvas = window.cropperBannerInstance.getCroppedCanvas({
            width: 1200,
            height: 450,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });

        canvas.toBlob(function(blob) {
            if (!blob) {
                if (btnAplicarRecorte) btnAplicarRecorte.disabled = false;
                if (btnRecorteSpinner) btnRecorteSpinner.classList.add('d-none');
                if (btnRecorteTexto) btnRecorteTexto.innerHTML = '<i class="ti ti-check me-1"></i> Aplicar recorte';
                return;
            }

            const formData = new FormData();
            formData.append('archivo', blob, 'banner.jpg');

            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const token = tokenMeta ? tokenMeta.getAttribute('content') : '';

            fetch('{{ route("banner-escuela.upload") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (btnAplicarRecorte) btnAplicarRecorte.disabled = false;
                if (btnRecorteSpinner) btnRecorteSpinner.classList.add('d-none');
                if (btnRecorteTexto) btnRecorteTexto.innerHTML = '<i class="ti ti-check me-1"></i> Aplicar recorte';

                if (data.success) {
                    // Ocultar área de recorte y limpiar cropper
                    if (window.cropperBannerInstance) {
                        window.cropperBannerInstance.destroy();
                        window.cropperBannerInstance = null;
                    }
                    const areaRecorte = document.getElementById('areaRecorteBanner');
                    if (areaRecorte) areaRecorte.style.display = 'none';

                    // Actualizar Livewire
                    $wire.set('nombreArchivoSubido', data.nombre);
                    $wire.set('rutaArchivoSubida', data.ruta_relativa);
                } else {
                    Swal.fire({
                        title: 'Error al subir',
                        text: data.message || 'No se pudo subir la imagen del banner.',
                        icon: 'error',
                        customClass: { confirmButton: 'btn btn-primary rounded-pill' },
                        buttonsStyling: false
                    });
                }
            })
            .catch(err => {
                if (btnAplicarRecorte) btnAplicarRecorte.disabled = false;
                if (btnRecorteSpinner) btnRecorteSpinner.classList.add('d-none');
                if (btnRecorteTexto) btnRecorteTexto.innerHTML = '<i class="ti ti-check me-1"></i> Aplicar recorte';

                Swal.fire({
                    title: 'Error de conexión',
                    text: 'Ocurrió un error al procesar y subir la imagen.',
                    icon: 'error',
                    customClass: { confirmButton: 'btn btn-primary rounded-pill' },
                    buttonsStyling: false
                });
            });
        }, 'image/jpeg', 0.92);
    };

    // Escuchar notificaciones del backend
    Livewire.on('notificacion', (event) => {
        const data = Array.isArray(event) ? event[0] : event;
        Swal.fire({
            icon: data.icono || 'success',
            title: data.titulo || 'Notificación',
            text: data.mensaje || '',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    });
</script>
@endscript
