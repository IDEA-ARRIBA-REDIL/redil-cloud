<div>
    {{-- Botones de Acción: Nuevo Ítem y Duplicar Modelo --}}
    <div class="row justify-content-between mb-4 align-items-center">
        <div class="col-md-4 col-12 mt-3">
            <button type="button" class="btn btn-primary rounded-pill" wire:click="abrirOffcanvasCrear">
                <i class="ti ti-plus me-1"></i> Nuevo ítem
            </button>
        </div>
        <div class="col-md-8 col-12 mt-3">
            <button type="button" class=" btn btn-outline-secondary float-md-end float-sm-start rounded-pill"
                wire:click="abrirModalDuplicar">
                <i class="ti ti-copy me-1"></i> Duplicar modelo desde otra materia
            </button>
        </div>
    </div>

    {{-- Mensajes Flash de Sesión --}}
   @include('layouts.status-msn')

    {{-- Listado de Ítems en Tarjetas --}}
    <div class="row equal-height-row">
        @forelse ($itemPlantillas as $item)
            <div class="col equal-height-col col-lg-4 col-md-6 col-12 mb-4">
                <div class="card h-100 shadow rounded">
                    {{-- Botón de menú desplegable (tres puntos) --}}
                    <div style="border-radius: 20px !important;" class="position-absolute border p-1 rounded top-0 end-0 mt-3 me-3 z-1">
                        <div class="dropdown">
                            <button class="btn p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    {{-- Opción Editar --}}
                                    <button class="dropdown-item" type="button"
                                        wire:click="abrirOffcanvasEditar({{ $item->id }})">
                                        Editar
                                    </button>
                                </li>
                                <li>
                                    {{-- Opción Eliminar con confirmación JS --}}
                                    <button class="dropdown-item" type="button"
                                        wire:click="$dispatch('confirmar-eliminacion-item', { id: {{ $item->id }}, nombre: '{{ addslashes($item->nombre) }}' })">
                                        Eliminar
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    {{-- Cuerpo de la tarjeta con detalles del ítem --}}
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <h5 class="card-title mb-0 flex-grow-1 text-truncate" title="{{ $item->nombre }}">
                                {{ $item->nombre }}
                            </h5>
                        </div>
                        <p class="card-text text-muted small mb-2">
                            {{-- Muestra descripción corta o mensaje por defecto --}}
                            {{ $item->contenido ? Str::limit($item->contenido, 80) : 'Sin descripción.' }}
                        </p>
                        {{-- Detalles: Corte y Tipo --}}
                        <div class="row mb-2">
                            <div class="col-6">
                                <small class="text-muted d-block">Corte:</small>
                                <span class="fw-semibold text-black ">{{ $item->corteEscuela->nombre ?? 'N/A' }}</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Tipo:</small>
                                <span
                                    class="fw-semibold text-black ">{{ $item->tipoItem->nombre ?? 'No especificado' }}</span>
                            </div>
                        </div>
                        {{-- Detalles: Orden y Porcentaje --}}
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Orden:</small>
                                <span class="fw-semibold text-black">{{ $item->orden }}</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Porcentaje :</small>
                                <span class="fw-semibold text-black">{{ $item->porcentaje_sugerido ?? 'N/A' }}%</span>
                            </div>
                        </div>
                        {{-- Detalles: Visible y Entregable (con badges) --}}
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted d-block">Visible:</small>
                                <span
                                    class="badge rounded-pill {{ $item->visible_predeterminado ? 'bg-label-success' : 'bg-label-secondary' }}">
                                    {{ $item->visible_predeterminado ? 'Sí' : 'No' }}
                                </span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Entregable:</small>
                                <span
                                    class="badge rounded-pill {{ $item->entregable_predeterminado ? 'bg-label-info' : 'bg-label-secondary' }}">
                                    {{ $item->entregable_predeterminado ? 'Sí' : 'No' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            {{-- Mensaje si no hay ítems --}}
            <div class="col-12">
                <div class="alert alert-secondary text-center" role="alert">
                    <i class="ti ti-info-circle me-2"></i> Aún no has creado plantillas de ítems para esta materia.
                </div>
            </div>
        @endforelse
    </div>

    {{-- ------------------------------------------- --}}
    {{-- Offcanvas para CREAR Ítem                  --}}
    {{-- ------------------------------------------- --}}
    <div class="offcanvas offcanvas-end fade" data-bs-backdrop="true" tabindex="-1" id="offcanvasCrearItem"
        aria-labelledby="offcanvasCrearItemLabel" wire:ignore.self>
        <div class="offcanvas-header">
            <h4 class="offcanvas-title fw-bold text-primary" id="offcanvasCrearItemLabel">Nueva plantilla de ítem</h4>
            <button type="button" class="btn-close text-reset" wire:click="cerrarOffcanvasCrear"
                aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 py-4">
            {{-- Formulario de Creación --}}
            <form wire:submit.prevent="guardarItemNuevo" id="formCrearItem">
                {{-- Campo Nombre --}}
                <div class="mb-3">
                    <label for="nombreCrear" class="form-label">Nombre del ítem <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nombreCrear') is-invalid @enderror" id="nombreCrear" wire:model="nombreCrear" placeholder="Ej: Taller 1, Examen Final">
                    @error('nombreCrear')
                        <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div>
                    @enderror
                </div>
                {{-- Campo Corte Asociado --}}
                <div class="mb-3">
                    <label for="corte_escuela_idCrear" class="form-label">Corte asociado <span class="text-danger">*</span></label>
                    <select class="form-select @error('corte_escuela_idCrear') is-invalid @enderror" id="corte_escuela_idCrear" wire:model="corte_escuela_idCrear">
                        <option value="">Selecciona un corte...</option>
                        @foreach ($cortesEscuela as $corte)
                            <option value="{{ $corte->id }}">{{ $corte->nombre }} (Orden: {{ $corte->orden }})</option>
                        @endforeach
                    </select>
                    @error('corte_escuela_idCrear')
                        <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div>
                    @enderror
                </div>
                {{-- Campo Tipo de Ítem --}}
                <div class="mb-3">
                    <label for="tipo_item_idCrear" class="form-label">Tipo de ítem</label>
                    <select class="form-select @error('tipo_item_idCrear') is-invalid @enderror" id="tipo_item_idCrear" wire:model="tipo_item_idCrear">
                        <option value="">(Opcional) Selecciona un tipo...</option>
                        @foreach ($tiposItem as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                    @error('tipo_item_idCrear')
                        <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div>
                    @enderror
                </div>
                {{-- Campo Contenido/Descripción --}}
                <div class="mb-3">
                    <label for="contenidoCrear" class="form-label">Contenido / Descripción</label>
                    <textarea class="form-control @error('contenidoCrear') is-invalid @enderror" id="contenidoCrear" wire:model="contenidoCrear" rows="3" placeholder="Instrucciones, detalles..."></textarea>
                    @error('contenidoCrear')
                        <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div>
                    @enderror
                </div>
                {{-- Campo Orden --}}
                <div class="mb-3">
                    <label for="ordenCrear" class="form-label">Orden <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('ordenCrear') is-invalid @enderror" id="ordenCrear" wire:model="ordenCrear" min="0" placeholder="Define el orden dentro del corte">
                    @error('ordenCrear')
                        <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div>
                    @enderror
                </div>
                {{-- Campo Porcentaje --}}
                <div class="mb-3">
                    <label for="porcentaje_sugeridoCrear" class="form-label">Porcentaje (%)</label>
                    <input type="number" class="form-control @error('porcentaje_sugeridoCrear') is-invalid @enderror" id="porcentaje_sugeridoCrear" wire:model="porcentaje_sugeridoCrear" min="0" max="100" step="0.01" placeholder="Ej: 10.5">
                    @error('porcentaje_sugeridoCrear')
                        <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div>
                    @enderror
                </div>
                {{-- Switches Visible y Entregable --}}
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="visible_predeterminadoCrear" wire:model="visible_predeterminadoCrear">
                            <label class="form-check-label" for="visible_predeterminadoCrear">¿Visible por defecto?</label>
                        </div>
                        @error('visible_predeterminadoCrear')
                            <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="entregable_predeterminadoCrear" wire:model="entregable_predeterminadoCrear">
                            <label class="form-check-label" for="entregable_predeterminadoCrear">¿Requiere entrega?</label>
                        </div>
                        @error('entregable_predeterminadoCrear')
                            <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </form>
        </div>
        {{-- Footer del Offcanvas Crear --}}
        <div class="offcanvas-footer p-3 border-top">
            <button type="submit" class="btn btn-primary rounded-pill" form="formCrearItem" wire:loading.attr="disabled" wire:target="guardarItemNuevo">
                <span wire:loading wire:target="guardarItemNuevo" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                <span wire:loading.remove wire:target="guardarItemNuevo">Guardar ítem</span>
                <span wire:loading wire:target="guardarItemNuevo">Guardando...</span>
            </button>
            <button type="button" class="btn btn-outline-secondary rounded-pill" wire:click="cerrarOffcanvasCrear">
                Cancelar
            </button>
        </div>
    </div>
    {{-- Fin Offcanvas Crear --}}


    {{-- ------------------------------------------- --}}
    {{-- Offcanvas para EDITAR Ítem                 --}}
    {{-- ------------------------------------------- --}}
    <div class="offcanvas offcanvas-end fade" data-bs-backdrop="true" tabindex="-1" id="offcanvasEditarItem"
        aria-labelledby="offcanvasEditarItemLabel" wire:ignore.self>
        <div class="offcanvas-header">
            <h4 class="offcanvas-title fw-bold text-primary" id="offcanvasEditarItemLabel">Editar plantilla de ítem</h4>
            <button type="button" class="btn-close text-reset" wire:click="cerrarOffcanvasEditar" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 py-4">
            {{-- Formulario de Edición --}}
            <form wire:submit.prevent="actualizarItem" id="formEditarItem">
                {{-- Campo Nombre --}}
                <div class="mb-3">
                    <label for="nombreEditar" class="form-label">Nombre del item <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nombreEditar') is-invalid @enderror" id="nombreEditar" wire:model="nombreEditar">
                    @error('nombreEditar')
                        <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div>
                    @enderror
                </div>
                {{-- Campo Corte Asociado --}}
                <div class="mb-3">
                    <label for="corte_escuela_idEditar" class="form-label">Corte asociado <span class="text-danger">*</span></label>
                    <select class="form-select @error('corte_escuela_idEditar') is-invalid @enderror" id="corte_escuela_idEditar" wire:model="corte_escuela_idEditar">
                        <option value="">Selecciona un corte...</option>
                        @foreach ($cortesEscuela as $corte)
                            <option value="{{ $corte->id }}">{{ $corte->nombre }} (Orden: {{ $corte->orden }})</option>
                        @endforeach
                    </select>
                    @error('corte_escuela_idEditar')
                        <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div>
                    @enderror
                </div>
                {{-- Campo Tipo de Ítem --}}
                <div class="mb-3">
                    <label for="tipo_item_idEditar" class="form-label">Tipo de ítem</label>
                    <select class="form-select @error('tipo_item_idEditar') is-invalid @enderror" id="tipo_item_idEditar" wire:model="tipo_item_idEditar">
                        <option value="">(Opcional) Selecciona un tipo...</option>
                        @foreach ($tiposItem as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                    @error('tipo_item_idEditar')
                        <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div>
                    @enderror
                </div>
                {{-- Campo Contenido/Descripción --}}
                <div class="mb-3">
                    <label for="contenidoEditar" class="form-label">Contenido / Descripción</label>
                    <textarea class="form-control @error('contenidoEditar') is-invalid @enderror" id="contenidoEditar" wire:model="contenidoEditar" rows="3"></textarea>
                    @error('contenidoEditar')
                        <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div>
                    @enderror
                </div>
                {{-- Campo Orden --}}
                <div class="mb-3">
                    <label for="ordenEditar" class="form-label">Orden <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('ordenEditar') is-invalid @enderror" id="ordenEditar" wire:model="ordenEditar" min="0">
                    @error('ordenEditar')
                        <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div>
                    @enderror
                </div>
                {{-- Campo Porcentaje --}}
                <div class="mb-3">
                    <label for="porcentaje_sugeridoEditar" class="form-label">Porcentaje (%)</label>
                    <input type="number" class="form-control @error('porcentaje_sugeridoEditar') is-invalid @enderror" id="porcentaje_sugeridoEditar" wire:model="porcentaje_sugeridoEditar" min="0" max="100" step="0.01">
                    @error('porcentaje_sugeridoEditar')
                        <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div>
                    @enderror
                </div>
                {{-- Switches Visible y Entregable --}}
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="visible_predeterminadoEditar" wire:model="visible_predeterminadoEditar">
                            <label class="form-check-label" for="visible_predeterminadoEditar">¿Visible por defecto?</label>
                        </div>
                        @error('visible_predeterminadoEditar')
                            <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="entregable_predeterminadoEditar" wire:model="entregable_predeterminadoEditar">
                            <label class="form-check-label" for="entregable_predeterminadoEditar">¿Requiere entrega?</label>
                        </div>
                        @error('entregable_predeterminadoEditar')
                            <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </form>
        </div>
        {{-- Footer del Offcanvas Editar --}}
        <div class="offcanvas-footer p-3 border-top">
            <button type="submit" class="btn btn-primary rounded-pill" form="formEditarItem" wire:loading.attr="disabled" wire:target="actualizarItem">
                <span wire:loading wire:target="actualizarItem" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                <span wire:loading.remove wire:target="actualizarItem">Actualizar ítem</span>
                <span wire:loading wire:target="actualizarItem">Actualizando...</span>
            </button>
            <button type="button" class="btn btn-outline-secondary rounded-pill mt-2" wire:click="cerrarOffcanvasEditar">
                Cancelar
            </button>
        </div>
    </div>
    {{-- Fin Offcanvas Editar --}}


    {{-- Modal para Duplicar Modelo Calificativo --}}
    <div class="modal fade" id="modalDuplicarModelo" tabindex="-1" aria-labelledby="modalDuplicarModeloLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDuplicarModeloLabel">Duplicar modelo calificativo</h5>
                    <button type="button" class="btn-close" wire:click="cerrarModalDuplicar" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Contenedor para mostrar errores específicos del modal --}}
                    <div wire:key="error-modal-duplicar-{{ now() }}">
                        @if (session()->has('errorModalDuplicar'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('errorModalDuplicar') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                    </div>
                    <p>Selecciona la materia desde la cual deseas duplicar todas sus plantillas de ítems hacia la materia actual: <strong>{{ $materia->nombre }}</strong>.</p>
                    <p class="small text-muted">Los ítems existentes en la materia actual no se modificarán ni eliminarán.</p>
                    {{-- Select para materia de origen --}}
                    <div class="mb-3">
                        <label for="materiaIdFuenteParaDuplicar" class="form-label">Materia de origen <span class="text-danger">*</span></label>
                        <select class="form-select @error('materiaIdFuenteParaDuplicar') is-invalid @enderror" id="materiaIdFuenteParaDuplicar" wire:model="materiaIdFuenteParaDuplicar">
                            <option value="">Selecciona una materia...</option>
                            @forelse ($materiasParaDuplicar as $materiaFuente)
                                <option value="{{ $materiaFuente->id }}">{{ $materiaFuente->nombre }}</option>
                            @empty
                                <option value="" disabled>No hay otras materias en esta escuela para duplicar.</option>
                            @endforelse
                        </select>
                        @error('materiaIdFuenteParaDuplicar')
                            <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                {{-- Footer del Modal Duplicar --}}
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" wire:click="cerrarModalDuplicar">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary rounded-pill" wire:click="duplicarModeloDeMateria" wire:loading.attr="disabled" wire:target="duplicarModeloDeMateria">
                        <span wire:loading wire:target="duplicarModeloDeMateria" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        <span wire:loading.remove wire:target="duplicarModeloDeMateria">Duplicar modelo</span>
                        <span wire:loading wire:target="duplicarModeloDeMateria">Duplicando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- FIN: Modal para Duplicar Modelo Calificativo --}}

    {{-- Script para controlar Offcanvas, Modal y SweetAlert --}}
    @push('scripts')
        <script>
            // Funciones de ayuda para registrar eventos de Bootstrap en la consola (para depuración)
            function handleBsOffcanvasShow(event) {
                console.log(`EVENTO BOOTSTRAP: show.bs.offcanvas disparado para #${event.target.id}`);
            }

            function handleBsOffcanvasShown(event) {
                console.log(`EVENTO BOOTSTRAP: shown.bs.offcanvas disparado para #${event.target.id}`);
                const backdrop = document.querySelector('.offcanvas-backdrop');
                if (backdrop) {
                    console.log('BACKDROP ENCONTRADO en DOM:', backdrop);
                    console.log('Clases del Backdrop:', backdrop.className);
                    // Puedes añadir más logs de estilos computados si es necesario
                } else {
                    console.error('ERROR: BACKDROP NO ENCONTRADO en DOM después de shown.bs.offcanvas.');
                }
            }

            function handleBsModalShow(event) {
                console.log(`EVENTO BOOTSTRAP: show.bs.modal disparado para #${event.target.id}`);
            }

            function handleBsModalShown(event) {
                console.log(`EVENTO BOOTSTRAP: shown.bs.modal disparado para #${event.target.id}`);
                const backdrop = document.querySelector('.modal-backdrop'); // Para modales
                if (backdrop) {
                    console.log('BACKDROP (MODAL) ENCONTRADO en DOM:', backdrop);
                    console.log('Clases del Backdrop (Modal):', backdrop.className);
                } else {
                    console.error('ERROR: BACKDROP (MODAL) NO ENCONTRADO en DOM después de shown.bs.modal.');
                }
            }

            // --- INICIO CÓDIGO CORREGIDO PARA BACKDROP ---
            // Función auxiliar para eliminar el backdrop manualmente
            function removeBackdropManually() {
                console.log("DEBUG: Evento hidden.bs.offcanvas/hidden.bs.modal detectado, buscando backdrop...");
                // Busca backdrop de offcanvas o modal
                const backdrop = document.querySelector('.offcanvas-backdrop') || document.querySelector('.modal-backdrop');
                if (backdrop) {
                    console.log("DEBUG: Backdrop encontrado, eliminando...", backdrop);
                    backdrop.classList.remove('show'); // Inicia el fade out
                    // Elimina después de la transición (por defecto Bootstrap usa 0.3s)
                     setTimeout(() => {
                         if (backdrop.parentNode) { // Verifica si no fue eliminado ya
                             backdrop.remove();
                             console.log("DEBUG: Backdrop eliminado del DOM.");
                         }
                     }, 300); // Ajusta el tiempo si tus transiciones son diferentes
                } else {
                     console.log("DEBUG: No se encontró backdrop para eliminar.");
                }
            }
            // --- FIN CÓDIGO CORREGIDO PARA BACKDROP ---


            document.addEventListener('livewire:init', () => {
                console.log('Livewire inicializado. Configurando listeners para offcanvas/modal.');

                // Verifica si Bootstrap está cargado (importante para depurar)
                if (typeof bootstrap === 'undefined' || (typeof bootstrap.Offcanvas === 'undefined' && typeof bootstrap.Modal === 'undefined')) {
                    console.error('ERROR CRÍTICO: Bootstrap 5 JS no está cargado o disponible.');
                } else {
                    console.log('Bootstrap JS parece estar cargado.');
                }

                // Función centralizada para manejar la apertura/cierre de Offcanvas y Modales
                const manageBootstrapComponent = (action, componentId, componentType) => {
                    console.log(
                        `DEBUG: manageBootstrapComponent llamado con: action=${action}, componentId=${componentId}, componentType=${componentType}`
                        );
                    const element = document.getElementById(componentId);

                    if (!element) {
                        console.error(`DEBUG: Elemento con ID '${componentId}' NO encontrado.`);
                        return null;
                    }
                    console.log(`DEBUG: Elemento '${componentId}' encontrado:`, element);

                    let instance;
                    try {
                        if (componentType === 'Modal') {
                            instance = bootstrap.Modal.getOrCreateInstance(element);
                            // Limpia y añade listeners para depuración de modales
                            element.removeEventListener('show.bs.modal', handleBsModalShow);
                            element.addEventListener('show.bs.modal', handleBsModalShow, { once: true });
                            element.removeEventListener('shown.bs.modal', handleBsModalShown);
                            element.addEventListener('shown.bs.modal', handleBsModalShown, { once: true });
                            // --- Listener para eliminar backdrop de Modal ---
                            element.removeEventListener('hidden.bs.modal', removeBackdropManually);
                            element.addEventListener('hidden.bs.modal', removeBackdropManually, { once: true });
                            // --- Fin Listener ---
                        } else { // Offcanvas
                            instance = bootstrap.Offcanvas.getOrCreateInstance(element);
                            // Limpia y añade listeners para depuración de offcanvas
                            element.removeEventListener('show.bs.offcanvas', handleBsOffcanvasShow);
                            element.addEventListener('show.bs.offcanvas', handleBsOffcanvasShow, { once: true });
                            element.removeEventListener('shown.bs.offcanvas', handleBsOffcanvasShown);
                            element.addEventListener('shown.bs.offcanvas', handleBsOffcanvasShown, { once: true });
                            // --- Listener para eliminar backdrop de Offcanvas ---
                            element.removeEventListener('hidden.bs.offcanvas', removeBackdropManually);
                            element.addEventListener('hidden.bs.offcanvas', removeBackdropManually, { once: true });
                            // --- Fin Listener ---
                        }
                        console.log(`DEBUG: Instancia Bootstrap para '${componentId}' obtenida/creada:`, instance);
                    } catch (e) {
                        console.error(`DEBUG: Error al obtener/crear instancia Bootstrap para '${componentId}':`, e);
                        return null;
                    }

                    if (instance) {
                        if (action === 'show') {
                            // --- INICIO CÓDIGO CORREGIDO PARA BACKDROP (CREACIÓN) ---
                            // Solo crea backdrop manualmente si NO es un modal (Bootstrap maneja el de modal)
                            // y si el backdrop no existe ya (evita duplicados)
                            if (componentType === 'Offcanvas' && !document.querySelector('.offcanvas-backdrop')) {
                                console.log(`DEBUG: Creando backdrop manualmente para Offcanvas '${componentId}'`);
                                const backdrop = document.createElement('div');
                                backdrop.className = 'offcanvas-backdrop fade'; // Inicia solo con fade
                                document.body.appendChild(backdrop);
                                // Fuerza reflow antes de añadir 'show' para la transición
                                backdrop.offsetHeight;
                                backdrop.classList.add('show');
                            }
                            // --- FIN CÓDIGO CORREGIDO PARA BACKDROP (CREACIÓN) ---

                            console.log(`DEBUG: Ejecutando instance.show() para '${componentId}'`);
                            instance.show();

                        } else if (action === 'hide') {
                            console.log(`DEBUG: Ejecutando instance.hide() para '${componentId}'`);
                            instance.hide();
                            // La eliminación del backdrop ahora se maneja con el listener 'hidden.bs.offcanvas'/'hidden.bs.modal'
                        }
                    } else {
                        console.error(`DEBUG: No se pudo obtener instancia Bootstrap para '${componentId}'.`);
                    }
                    return instance;
                };

                // Listener para el evento Livewire que abre Offcanvas/Modal
                Livewire.on('abrirOffcanvas', (eventDetail) => {
                    const detail = Array.isArray(eventDetail) ? eventDetail[0] : eventDetail;
                    console.log('DEBUG: Evento Livewire "abrirOffcanvas" recibido:', detail);
                    if (detail && detail.nombreModal) {
                        const componentId = detail.nombreModal;
                        // Determina si es Modal u Offcanvas basado en el ID
                        const componentType = componentId.startsWith('modal') ? 'Modal' : 'Offcanvas';
                        manageBootstrapComponent('show', componentId, componentType);
                    } else {
                        console.error('DEBUG: "abrirOffcanvas" no contenía nombreModal:', detail);
                    }
                });

                // Listener para el evento Livewire que cierra Offcanvas/Modal
                Livewire.on('cerrarOffcanvas', (eventDetail) => {
                    const detail = Array.isArray(eventDetail) ? eventDetail[0] : eventDetail;
                    console.log('DEBUG: Evento Livewire "cerrarOffcanvas" recibido:', detail);
                    if (detail && detail.nombreModal) {
                        const componentId = detail.nombreModal;
                        // Determina si es Modal u Offcanvas basado en el ID
                        const componentType = componentId.startsWith('modal') ? 'Modal' : 'Offcanvas';
                        manageBootstrapComponent('hide', componentId, componentType);
                    } else {
                        console.error('DEBUG: "cerrarOffcanvas" no contenía nombreModal:', detail);
                    }
                });

                // Listener para la confirmación de eliminación con SweetAlert
                Livewire.on('confirmar-eliminacion-item', data => {
                    const detail = Array.isArray(data) ? data[0] : data;
                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: `¿Deseas eliminar el ítem "${detail.nombre}"? Esta acción no se puede revertir.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        customClass: {
                            confirmButton: 'btn btn-primary me-2 rounded-pill',
                            cancelButton: 'btn btn-outline-secondary rounded-pill'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Llama al método 'eliminarItem' del componente Livewire
                            @this.call('eliminarItem', detail.id);
                        }
                    });
                });
            });
        </script>
    @endpush
</div>