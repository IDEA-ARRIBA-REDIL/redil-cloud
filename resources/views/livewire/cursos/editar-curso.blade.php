<div>
    <form wire:submit.prevent="update">
        {{-- Card 1: Información Básica --}}
        <!-- PORTADA -->
        <div class="card mb-4 rounded rounded-3">
             <div class="position-relative">
                 @if ($imagen_portada && !is_string($imagen_portada))
                    <img src="{{ $imagen_portada->temporaryUrl() }}" class="card-img-top object-fit-cover" alt="Portada Nueva" style="height: 300px; width: 100%;">
                @elseif($imagen_actual)
                     @php
                        $configuracion = \App\Models\Configuracion::find(1);
                        $ruta = $configuracion->ruta_almacenamiento . '/img/cursos/portadas/' . $imagen_actual;
                    @endphp
                    <img src="{{ Storage::url($ruta) }}" class="card-img-top object-fit-cover" alt="Portada Actual" style="height: 300px; width: 100%;" onerror="this.onerror=null;this.src='{{ asset('assets/img/backgrounds/default.png') }}';">
                @else
                     <img src="{{ asset('assets/img/backgrounds/default.png')  }}" class="card-img-top object-fit-cover" alt="Portada Default" style="height: 300px; width: 100%;">
                @endif

                <button type="button" onclick="document.getElementById('input_imagen_portada').click()" style="background-color: rgba(255, 255, 255, 0.8);" class="btn btn-sm rounded-pill waves-effect waves-light position-absolute bottom-0 end-0 mb-3 me-3 text-dark fw-bold p-2 shadow-sm">
                    Cambiar portada <i style="padding-left: 5px;" class="ti ti-camera"></i>
                </button>
                <input type="file" id="input_imagen_portada" wire:model.live="imagen_portada" accept="image/*" class="d-none">
             </div>

            <div class="card-body p-4">
               <h5 class="mb-1 fw-semibold text-black">Editar curso: {{ $nombre }}</h5>
               <p class="mb-0 text-muted">Actualiza la información, configuración y contenido multimedia de este curso.</p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0 fw-semibold">1. Información básica</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre del curso</label>
                        <input type="text" class="form-control" wire:model.live="nombre" placeholder="Ej: Introducción a la Fe">
                        @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6" wire:ignore>
                        <label class="form-label">Carrera (Opcional)</label>
                        <select class="form-select select2" wire:model="carrera_id" id="select_carrera">
                            <option value="">Seleccione una carrera</option>
                            @foreach($carrerasList as $carrera)
                                <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                            @endforeach
                        </select>
                        @error('carrera_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Slug (URL)</label>
                        <input type="text" class="form-control" wire:model="slug" readonly>
                        @error('slug') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6" wire:ignore>
                        <label class="form-label">Categorías</label>
                        <select class="form-select select2" multiple wire:model="categorias_seleccionadas" id="select_categorias">
                            @foreach($categoriasList as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                        @error('categorias_seleccionadas') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Descripción larga</label>
                         <div wire:ignore>
                            <div id="editor-descripcion" style="height: 150px;">{!! $descripcion_larga !!}</div>
                        </div>
                        <input type="hidden" wire:model="descripcion_larga" id="descripcion_larga_input">
                        @error('descripcion_larga') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripción corta</label>
                        <textarea class="form-control" wire:model="descripcion_corta" rows="2"></textarea>
                    </div>
                         <div class="col-md-4">
                            <label class="form-label">Nivel de dificultad</label>
                            <select class="form-select" wire:model="nivel_dificultad">
                                <option value="Todas">Todas</option>
                                <option value="Principiante">Principiante</option>
                                <option value="Intermedio">Intermedio</option>
                                <option value="Avanzado">Avanzado</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                             <label class="form-label">Duración estimada (Días)</label>
                             <input type="number" class="form-control" wire:model="duracion_estimada_dias" min="0">
                             <div class="form-text">0 = Sin estimación</div>
                        </div>
                         <div class="col-md-4">
                             <label class="form-label">Cupos totales</label>
                             <input type="number" class="form-control" wire:model="cupos_totales" min="1" placeholder="Ilimitado">
                             <div class="form-text">Dejar vacío para ilimitado</div>
                        </div>
                         <div class="col-md-4">
                             <label class="form-label">Acceso limitado (Días)</label>
                             <input type="number" class="form-control" wire:model="dias_acceso_limitado" min="1" placeholder="De por vida">
                             <div class="form-text">Días de acceso tras compra. Vacío = De por vida.</div>
                        </div>
                         <div class="col-md-4">
                             <label class="form-label">Fecha inicio (Opcional)</label>
                             <input type="date" class="form-control fecha-picker" wire:model="fecha_inicio">
                        </div>
                    <div class="col-md-4" wire:ignore>
                        <label class="form-label">Estado</label>
                        <select class="form-select select2" wire:model="estado" id="select_estado">
                            <option value="Borrador">Borrador</option>
                            <option value="Publicado">Publicado</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                     <div class="col-md-4">
                        <label class="form-label">Orden destacado</label>
                         <input type="number" class="form-control" wire:model="orden_destacado" min="0">
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Precios y Configuración --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0 fw-semibold">2. Precios y configuración</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="small fw-medium mb-1">¿Es gratuito?</div>
                        <label class="switch switch-lg">
                            <input type="checkbox" wire:model.live="es_gratuito" class="switch-input" />
                            <span class="switch-toggle-slider">
                                <span class="switch-on"></span>
                                <span class="switch-off"></span>
                            </span>
                        </label>
                    </div>

                    @if(!$es_gratuito)
                         <div class="col-md-3" wire:ignore>
                            <label class="form-label">Moneda</label>
                            <select class="form-select select2" wire:model.live="moneda_id" id="select_moneda">
                                <option value="">Seleccione Moneda</option>
                                @foreach($monedas as $moneda)
                                    <option value="{{ $moneda->id }}">{{ $moneda->nombre }} ({{ $moneda->simbolo }})</option>
                                @endforeach
                            </select>
                            @error('moneda_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Precio regular</label>
                            <input type="number" class="form-control" wire:model="precio" step="0.01">
                            @error('precio') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Precio comparación (Antes)</label>
                            <input type="number" class="form-control" wire:model="precio_comparacion" step="0.01">
                        </div>

                         <div class="col-12 mt-3">
                            <label class="form-label d-block">Métodos de pago permitidos</label>
                             @if($moneda_id)
                                 <div>
                                     <select wire:key="select-tipos-pago-{{ $moneda_id }}" wire:ignore class="form-select select2" multiple wire:model="tipos_pago_seleccionados" id="select_tipos_pago" data-placeholder="Seleccione métodos de pago...">
                                        @foreach($tiposPagoFiltrados as $pago)
                                            <option value="{{ $pago->id }}">{{ $pago->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if($tiposPagoFiltrados->isEmpty())
                                    <span class="text-muted small">No hay métodos de pago disponibles para esta moneda o configuración.</span>
                                @endif
                             @else
                                <div class="alert alert-warning py-2 mb-0 small">
                                    Seleccione una moneda para ver los métodos de pago disponibles.
                                </div>
                             @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Card 3: Multimedia --}}
        <div class="card mb-4">
             <div class="card-header">
                <h5 class="mb-0 fw-semibold">3. Multimedia</h5>
            </div>
            <div class="card-body">
                 <div class="row g-3">
                     <div class="col-md-6">
                        <label class="form-label">URL video preview (YouTube/Vimeo)</label>
                        <input type="url" class="form-control" wire:model="video_preview_url" placeholder="https://youtube.com/watch?v=...">
                        @error('video_preview_url') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                 </div>
            </div>
        </div>

        {{-- Card 4: Restricciones y Reglas --}}
        <div class="card mb-4">
             <div class="card-header">
                <h5 class="mb-0 fw-semibold">4. Restricciones y reglas (Básico)</h5>
            </div>
            <div class="card-body">
                 <div class="row g-3">
                     <div class="col-md-6" wire:ignore>
                        <label class="form-label">Roles permitidos</label>
                        <select class="form-select select2" multiple wire:model="roles_seleccionados" id="select_roles">
                             @foreach($rolesList as $rol)
                                <option value="{{ $rol->id }}">{{ $rol->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mt-4">
                        <a href="{{ route('cursos.restricciones', $curso->id) }}" class="btn btn-outline-primary w-100">
                            <i class="ti ti-settings me-1"></i> Gestionar restricciones avanzadas (Roles, Pasos, Tareas)
                        </a>
                    </div>
                 </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mb-4">
            <a href="{{ route('cursos.gestionar') }}" class="btn btn-outline-secondary rounded-pill me-2">Cancelar</a>
            <button type="submit" class="btn btn-primary rounded-pill">Actualizar curso</button>
        </div>
    </form>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script>
        document.addEventListener('livewire:initialized', () => {
             var quill = new Quill('#editor-descripcion', {
                theme: 'snow',
                placeholder: 'Escribe la descripción del curso aquí...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'header': 1 }, { 'header': 2 }],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'align': [] }],
                        [{ 'size': ['small', false, 'large', 'huge'] }],
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        [{ 'font': [] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        ['link', 'image', 'video'],
                        ['clean']
                    ]
                }
            });

            quill.on('text-change', function() {
                // Actualiza la propiedad Livewire
                @this.set('descripcion_larga', quill.root.innerHTML);
            });

            // Function to init Select2
            function initSelect2() {
                 $('.select2').each(function() {
                    // Skip if it's the currency selector and already initialized
                    if ($(this).attr('id') === 'select_moneda' && $(this).hasClass('select2-hidden-accessible')) {
                        return;
                    }

                    if ($(this).data('select2')) {
                        $(this).select2('destroy');
                    }
                });

                $('.select2').not('.select2-hidden-accessible').select2({
                    width: '100%',
                    placeholder: 'Seleccione...',
                    allowClear: true
                }).on('change', function () {
                    let data = $(this).val();
                    let id = $(this).attr('id');

                    if (id === 'select_moneda') {
                        @this.set('moneda_id', data);
                    } else if (id === 'select_tipos_pago') {
                        @this.set('tipos_pago_seleccionados', data);
                    } else if (id === 'select_categorias') {
                        @this.set('categorias_seleccionadas', data);
                    } else if (id === 'select_roles') {
                        @this.set('roles_seleccionados', data);
                    } else if (id === 'select_carrera') {
                        @this.set('carrera_id', data);
                    } else if (id === 'select_estado') {
                        @this.set('estado', data);
                    }
                });
            }

            // Initial Load
            initSelect2();

            // Re-init on Livewire event
            @this.on('initSelect2', () => {
                setTimeout(() => {
                    initSelect2();
                }, 100);
            });

            // Initialize new elements after DOM changes
            Livewire.hook('morph.updated', ({ el, component }) => {
                initSelect2();
            });
        });
    </script>
</div>
