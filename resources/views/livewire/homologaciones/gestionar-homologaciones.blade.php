<div>
    {{-- Paso 1 y 2: Selección de Alumno y Escuela --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">

                    {{-- El id 'alumno_id' es crucial para que nuestro JavaScript lo encuentre --}}
                    @livewire('usuarios.usuarios-para-busqueda', [
                        'id' => 'alumno_id',
                        'tipoBuscador' => 'unico',
                        'queUsuariosCargar' => 'todos',
                        'label' => '1. Busque y seleccione un alumno',
                        'placeholder' => 'Escriba el nombre o identificación del alumno...',
                    ])
                    @error('alumnoSeleccionadoId') <div class="text-danger small mt-1">{{ $message }}</div> @enderror


                    <div class="mt-3">
                    <label for="escuela_id" class="form-label">2. Seleccione una escuela</label>
                    <select id="escuela_id" wire:model="escuelaSeleccionadaId" class="form-select">
                        <option value="">-- Elige una escuela --</option>
                        @foreach($escuelas as $escuela)
                            <option value="{{ $escuela->id }}">{{ $escuela->nombre }}</option>
                        @endforeach
                    </select>
                    @error('escuelaSeleccionadaId') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Botón de Búsqueda Modificado --}}
    <div class="text-end mb-4">
        {{--
            LA MAGIA ESTÁ AQUÍ:
            Al hacer clic, ejecutamos JavaScript para obtener el valor del input oculto
            con id="alumno_id" y lo pasamos como parámetro al método buscarMaterias().
        --}}
        <button
            wire:click="buscarMaterias(document.getElementById('alumno_id').value)"
            class="btn btn-primary rounded-pill">

            <span wire:loading.remove wire:target="buscarMaterias">
                <i class="ti ti-search me-1"></i> Buscar materias
            </span>
            <span wire:loading wire:target="buscarMaterias">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Buscando...
            </span>
        </button>
    </div>

    {{-- Indicador de carga --}}
    <div wire:loading wire:target="cargarMateriasHomologables">Cargando materias...</div>

    {{-- Paso 3: Listado de Materias --}}
    @if(!empty($materias))
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Materias homologables</h5>
        </div>
        <div class="card-body">
            {{-- Usamos un 'row' con 'g-4' (gap) para dar espacio entre las tarjetas --}}
            <div class="row g-4">

                @forelse($materias as $materia)
                    {{-- Cada materia ahora es una columna que se adapta al tamaño de la pantalla --}}


                        {{-- Y dentro de cada columna, hay una tarjeta independiente --}}
                        <div class="col-md-3 col-12">
                            <div class="border rounded shadow justify-content-between align-items-center">
                                {{-- Sección Izquierda: Nombre y estado de la materia --}}
                                <div class="bg-lighter ">
                                    <h5 class="p-4  card-title mb-1 fw-semibold">{{ $materia->nombre }}</h5>
                                </div>
                                <div class="col-12 text-end">
                                     @if(isset($materia->estado) && $materia->estado == "1")
                                        <span class="badge m-3 p-3 bg-label-success">Aprobada / Homologada</span>
                                    @endif
                                    {{-- Si no está aprobada, no se muestra nada, como solicitaste --}}

                                     {{-- Sección Derecha: Botón de acción --}}

                                    @if(!$materia->estado)
                                        <button wire:ignore.self  wire:click="abrirModalHomologacion({{ $materia->id }})" class="btn  btn-outline-secondary rounded-pill m-3">
                                            Homologar
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                @empty
                    <div class="col-12">
                        <p class="text-muted text-center">No se encontraron materias para los criterios seleccionados.</p>
                    </div>
                @endforelse

            </div>
        </div>
    </div>
    @endif

    {{-- Modal de Homologación --}}
    @if($showModal)
        <div class="modal fade show d-block" ...>
            <div class="modal-dialog ...">
                <div class="modal-content">
                    <form wire:submit="guardarHomologacion">
                        <div class="modal-header">
                            <h5 class="modal-title">Homologar: {{ $materiaParaHomologar?->nombre }}</h5>

                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="sede_id" class="form-label">Sede</label>
                                <select wire:model="sedeHomologacionId" class="form-select">
                                    <option value="">-- Seleccione --</option>
                                    @foreach($sedes as $sede)
                                        <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('sedeHomologacionId')<span class="text-danger">{{$message}}</span>@enderror
                            </div>
                            <div class="mb-3">
                                <label for="obs" class="form-label">Observación</label>
                                <textarea wire:model="observacionHomologacion" class="form-control"></textarea>
                                @error('observacionHomologacion')<span class="text-danger">{{$message}}</span>@enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" wire:click="$set('showModal', false)" class="btn mt-2 btn-outline-secondary rounded-pill">Cancelar</button>
                            <button type="submit" class="btn btn-primary rounded-pill mt-2">Guardar homologación</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    @push('scripts')
    <script>
        // Se asegura de que el script se ejecute después de que Livewire esté listo.
        document.addEventListener('livewire:init', () => {

            // Escucha el evento 'notificacion' que envías desde tus componentes.
            Livewire.on('notificacion', (event) => {
                // Livewire 3 puede pasar el evento en un array, nos aseguramos de obtener el objeto.
                const data = Array.isArray(event) ? event[0] : event;

                // Muestra el SweetAlert con los datos recibidos del backend.
                Swal.fire({
                    icon: data.tipo || 'success', // 'success' por defecto si no se especifica 'tipo'
                    title: data.titulo || '¡Realizado!', // Un título por defecto
                    text: data.mensaje,
                    timer: 2500, // La alerta se cierra sola después de 2.5 segundos
                    showConfirmButton: false // No es necesario un botón de OK
                });
            });

        });
    </script>
    @endpush
</div>
