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
            <label class="form-label text-black fw-bold ps-1">Selecciona categorías (Multiselección)</label>
            <select style="width:100%; height: 130px;" multiple wire:model.live="categoriasSeleccionadas"
                class="form-select border shadow-none mb-2 text-black fs-6">
                @foreach ($categoriasList as $cat)
                    <option value="{{ $cat->id }}" class="p-2">{{ ucfirst(strtolower($cat->nombre)) }}
                    </option>
                @endforeach
            </select>
            <div class="d-flex justify-content-between">
                <small class="text-muted"><i class="ti ti-info-circle me-1"></i>Mantén presionado para seleccionar
                    varias.</small>
                <small class="text-primary cursor-pointer" wire:click="$set('categoriasSeleccionadas', [])">Limpiar
                    Filtro</small>
            </div>
        </div>
    </div>
    {{-- Barra de Búsqueda y Filtro de Ordenamiento --}}
    <div class="row">

        <div class="col-12 col-md-10 offset-md-1">
            {{-- Buscador reactivo (búsqueda por nombre) --}}
            <div class="input-group float-start mb-5" style="max-width: 900px; border-radius: 8px;">
                <input wire:model.live.debounce.500ms="search" type="text" class="form-control "
                    placeholder="Buscar cursos..." style="width: 30%;">
                <button class="btn btn-primary rounded" type="button"><i class="ti ti-search me-1"></i> Buscar</button>
            </div>

            {{-- Selector de ordenamiento reactivo --}}
            <div class="input-group float-end border px-2 rounded " style="max-width:400px;">
                <span class="text-muted me-2 text-nowrap pt-2">Ordenar por:</span>
                <select wire:model.live="orden" class="form-select border-0 shadow-none text-black fw-bold"
                    style="background-color: transparent; width: auto; cursor: pointer;">
                    <option value="reciente">Últimos</option>
                    <option value="antiguo">Más antiguos</option>
                    <option value="az">A - Z</option>
                </select>
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
