<div class="container-fluid">


    <div class="row mt-9">
        <div class="col-12 col-md-10 offset-md-1">
            <img src="{{ Storage::url($configuracion->ruta_almacenamiento . '/img/grupos/default.png') }}" alt="Educación"
                class="img-fluid rounded">
        </div>
    </div>


    <div style="background-color: #F7F5FA;    margin-top: -50px;
                 padding-bottom: 45px;" class="row ">
        {{-- Sección: Mis cursos (Solo visible si hay usuario logueado y tiene cursos) --}}
        @auth
            <div class="col-12 col-md-10 offset-md-1 mt-9">
                <div class="row ">
                    @if ($misCursos->count() > 0)
                        <h4 class="fw-bold mt-8 mb-5 text-black">Mis cursos</h4>

                        @foreach ($misCursos as $miCurso)
                            @php
                                // Obtenemos el progreso desde la tabla pivote de curso_users
                                $progreso = $miCurso->usuarios->first()->pivot->porcentaje_progreso ?? 0;
                            @endphp
                            <div class="col-12 col-md-6">
                                <div class="card shadow-sm border-0 h-100 p-3 rounded">
                                    <div class="d-flex flex-row align-items-stretch">
                                        {{-- Miniatura del curso --}}
                                        <div class="flex-shrink-0 me-3 me-sm-4">
                                            <img src="{{ $miCurso->imagen_portada ? Storage::url($configuracion->ruta_almacenamiento . '/img/cursos/portadas/' . $miCurso->imagen_portada) : Storage::url($configuracion->ruta_almacenamiento . '/img/grupos/default.png') }}"
                                                class="rounded-3" style="width: 140px; height: 110px; object-fit: cover;"
                                                alt="{{ $miCurso->nombre }}">
                                        </div>
                                        {{-- Información y Botones --}}
                                        <div class="flex-grow-1 d-flex flex-column justify-content-between">
                                            <div>
                                                <h5 class="fw-bold mb-2 text-black">{{ $miCurso->nombre }}</h5>
                                                <div class="bg-secondary bg-opacity-25 mt-2 mb-1 barra-progreso"
                                                    style="height: 6px; border-radius: 4px;width: 70%;">
                                                    <div class="h-100 bg-success"
                                                        style="width: {{ $progreso }}%; border-radius: 4px;"></div>
                                                </div>
                                                <div class="text-black fw-medium" style="font-size: 0.85rem;">
                                                    {{ $progreso }}% completado</div>
                                            </div>

                                            <div
                                                class="d-flex flex-row justify-content-end align-items-center gap-2 mt-3 mt-sm-2">
                                                <a href="#" class="btn btn-icon rounded"
                                                    style="background-color: #f4f0ff; color: #8c57ff; border: none; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ti ti-restore fs-5"></i>
                                                </a>
                                                <a href="{{ route('cursos.mi-campus', $miCurso->slug) }}"
                                                    class="btn rounded px-4 btn-outline-primary"
                                                    style=" font-weight: 500; height: 40px; display: flex; align-items: center;">
                                                    Continuar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endauth
    </div>
    <div class="row mt-7">
        {{-- Título de Cursos Disponibles y Pestañas de Categorías --}}
        <div class="mb-4 bg-purple text-white p-4 rounded-3 d-flex flex-column align-items-start"
            style="height: 100px;">

        </div>
    </div>
    {{-- Filtros por categoría estílo Tabs (pestañas) --}}
    <div class="row">
        <div id="container-categorias" style="margin-top:-95px;margin-bottom: 20px;"
            class="col-12 col-md-10 offset-md-1 ">
            <h4 class="fw-bold text-white mb-3">Cursos disponibles</h4>
            <div style="" class="d-flex gap-2 flex-wrap">

                <div wire:click="$set('categoriasSeleccionadas', [])"
                    class="badge bg-white text-black fw-regular p-5 border rounded badge-category  fs-6 {{ empty($categoriasSeleccionadas) ? 'active' : '' }}">
                    Todas
                </div>
                @foreach ($categoriasList as $cat)
                    <div wire:click="toggleCategoria('{{ $cat->id }}')"
                        class="badge bg-white text-black fw-regular p-5 border rounded badge-category  fs-6 {{ in_array($cat->id, $categoriasSeleccionadas) ? 'active' : '' }}">
                        {{ ucfirst(strtolower($cat->nombre)) }}
                    </div>
                @endforeach

            </div>
        </div>
        <div id="container-select-categorias" class="col-12 col-md-10 mt-3 pt-3">
            <label class="form-label text-black fw-bold ps-1 text-uppercase small"
                style="letter-spacing: 0.5px;">Selecciona categorías</label>

            <div x-data="{
                open: false,
                search: '',
                selected: @entangle('categoriasSeleccionadas'),
                options: {{ $categoriasList->map(fn($c) => ['id' => (string) $c->id, 'nombre' => ucfirst(strtolower($c->nombre))])->toJson() }},
            
                get filteredOptions() {
                    return this.options.filter(
                        i => i.nombre.toLowerCase().includes(this.search.toLowerCase())
                    );
                },
            
                toggle(id) {
                    id = id.toString();
                    if (this.selected.includes(id)) {
                        this.selected = this.selected.filter(i => i !== id);
                    } else {
                        this.selected.push(id);
                    }
                },
            
                isSelected(id) {
                    return this.selected.includes(id.toString());
                },
            
                getSelectedNames() {
                    return this.options
                        .filter(i => this.selected.includes(i.id.toString()))
                        .map(i => i.nombre);
                }
            }" class="position-relative">

                {{-- Gatillo del Dropdown --}}
                <div @click="open = !open" @click.away="open = false"
                    class="form-select border shadow-sm mb-2 text-black fs-6 d-flex flex-wrap align-items-center gap-2 bg-white cursor-pointer min-h-px-50"
                    style="border-radius: 12px; min-height: 50px; border-color: #e9ecef !important; padding-right: 40px;">

                    <template x-if="selected.length === 0">
                        <span class="text-muted ps-2">Selecciona categorías...</span>
                    </template>

                    <template x-for="name in getSelectedNames()" :key="name">
                        <span
                            class="badge bg-purple bg-opacity-10 text-purple border border-purple border-opacity-25 px-3 py-2"
                            style="border-radius: 8px; font-weight: 500;">
                            <span x-text="name"></span>
                        </span>
                    </template>

                    <i class="ti ti-chevron-down position-absolute end-0 me-3 text-muted transition-all"
                        :style="open ? 'transform: rotate(180deg)' : ''"></i>
                </div>

                {{-- Contenido del Dropdown --}}
                <div x-show="open" x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    class="position-absolute w-100 bg-white border shadow-lg mt-1 p-3"
                    style="border-radius: 15px; border-color: #e9ecef !important; z-index: 1000; max-height: 400px; overflow-y: auto;">

                    {{-- Buscador Interno --}}
                    <div class="mb-3">
                        <div class="input-group border rounded-pill px-3 py-1 bg-light">
                            <span class="input-group-text border-0 bg-transparent text-muted px-2">
                                <i class="ti ti-search fs-5"></i>
                            </span>
                            <input type="text" x-model="search"
                                class="form-control border-0 bg-transparent shadow-none py-1 fs-6"
                                placeholder="Buscar categorías..." @click.stop>
                        </div>
                    </div>

                    {{-- Listado de Opciones --}}
                    <div class="d-flex flex-column gap-1">
                        <template x-for="option in filteredOptions" :key="option.id">
                            <div @click.stop="toggle(option.id)"
                                class="px-3 py-2 rounded-3 d-flex align-items-center justify-content-between cursor-pointer transition-all"
                                :class="isSelected(option.id) ? 'bg-purple text-white shadow-sm' : 'hover-bg-light text-dark'">
                                <span x-text="option.nombre" style="font-weight: 500;"></span>
                                <i class="ti ti-check fs-5" x-show="isSelected(option.id)"></i>
                            </div>
                        </template>
                    </div>

                    <div x-show="filteredOptions.length === 0" class="text-center py-4 text-muted">
                        No se encontraron categorías.
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-2">
                <small class="text-muted"><i class="ti ti-info-circle me-1"></i>Puedes seleccionar múltiples categorías
                    para filtrar los cursos.</small>
                <small class="text-primary cursor-pointer hover-underline fw-medium" @click="selected = []">Limpiar
                    Filtro</small>
            </div>
        </div>
    </div>
    {{-- Barra de Búsqueda y Filtro de Ordenamiento --}}
    <div class="row">

        <div class="col-12 col-md-10 offset-md-1 mb-5">
            <div class="row align-items-center">
                {{-- Buscador reactivo --}}
                <div class="col-12 col-lg-8 mb-3 mb-lg-0">
                    <div class="d-flex align-items-center bg-white border p-1 ps-4"
                        style="border-radius: 15px; border-color: #e9ecef !important;">
                        <input wire:model.live.debounce.500ms="search" type="text"
                            class="form-control border-0 shadow-none text-muted py-2" placeholder="Buscar cursos"
                            style="background: transparent;">
                        <button class="btn btn-primary px-4 py-2 d-flex align-items-center justify-content-center"
                            style="background-color: #9d66ff !important; border-radius: 12px; border: none; height: 45px;">
                            <i class="ti ti-search fs-4 me-2"></i>
                            <span class="fw-medium">Buscar</span>
                        </button>
                    </div>
                </div>

                {{-- Selector de ordenamiento --}}
                <div class="col-12 col-lg-4 d-flex justify-content-lg-end">
                    <div class="d-flex align-items-center bg-white border px-4 py-2"
                        style="border-radius: 15px; border-color: #e9ecef !important; min-width: 280px; height: 55px;">
                        <span class="text-muted text-nowrap me-1">Ordenar por:</span>
                        <select wire:model.live="orden"
                            class="form-select border-0 shadow-none text-black fw-bold p-0 ps-1"
                            style="background: transparent; cursor: pointer; font-size: 0.95rem;">
                            <option value="reciente">Últimos</option>
                            <option value="antiguo">Más antiguos</option>
                            <option value="az">A - Z</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Grid de Cursos Disponibles --}}
    <div class="row g-4 my-9">
        <div class="col-12 col-md-10 offset-md-1 ">

            <div class="row ">
                @forelse($cursosDisponibles as $curso)
                    <div class="col-12  col-lg-3 mb-5">
                        <div class="card h-100 shadow-sm border-0 rounded-3">
                            {{-- Imagen del curso --}}
                            <img src="{{ $curso->imagen_portada ? Storage::url($configuracion->ruta_almacenamiento . '/img/cursos/portadas/' . $curso->imagen_portada) : Storage::url($configuracion->ruta_almacenamiento . '/img/grupos/default.png') }}"
                                class="card-img-top course-card-img" alt="{{ $curso->nombre }}">

                            <div class="card-body p-3 d-flex flex-column">
                                {{-- Etiquetas superioes: Categoría y Duración --}}
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted" style="font-size: 0.75rem;">
                                        <i class="ti ti-grid-dots me-1"></i>
                                        {{ ucfirst(strtolower($curso->categorias->first()->nombre ?? 'General')) }}
                                    </span>
                                    <span class="text-muted" style="font-size: 0.75rem;">
                                        <i class="ti ti-clock me-1"></i>
                                        {{ $curso->duracion_estimada_dias > 0 ? $curso->duracion_estimada_dias . ' Meses' : 'A su ritmo' }}
                                    </span>
                                </div>

                                {{-- Título y Descripción Corta --}}
                                <h6 class="fw-bold text-dark mb-2">{{ ucfirst(strtolower($curso->nombre)) }}</h6>
                                <p class="text-muted small mb-3 flex-grow-1"
                                    style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ ucfirst(strtolower($curso->descripcion_corta ?? 'Sin descripción disponible.')) }}
                                </p>

                                {{-- Sección inferior: Precio y botón de acceso --}}
                                <div class="mt-auto">
                                    <h6 class="fw-bold text-primary mb-3">
                                        {{ $curso->es_gratuito ? 'Gratis' : '$' . number_format($curso->precio, 0) }}
                                    </h6>
                                    <a href="{{ route('cursos.previsualizar', $curso->slug) }}"
                                        class="btn btn-outline-primary waves-effect w-100 rounded">
                                        Ver curso
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <div class="alert alert-light text-muted">
                            No se encontraron cursos que coincidan con los criterios de búsqueda o filtros.
                        </div>
                    </div>
                @endforelse
                <div class="d-flex justify-content-center mt-4">
                    {{ $cursosDisponibles->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Paginación de los cursos --}}



</div>
