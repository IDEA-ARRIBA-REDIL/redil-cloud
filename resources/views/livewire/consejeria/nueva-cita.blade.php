<div class="container my-5" style="padding-bottom: 100px;">
    <div class="col-12 col-sm-10 offset-sm-1 col-lg-8 offset-lg-2">

        <!-- Reemplazamos el <form> de Blade por el de Livewire -->
        <form id="formulario" role="form" class="forms-sample" wire:submit="guardarCita">

            <!-- Contenido del formulario (se oculta al guardar) -->
            <div wire:loading.remove wire:target="guardarCita">

                <!-- Manejo de mensajes de sesión de Livewire -->
                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                <h4 class="fw-semibold text-black ps-0 mb-5">Agendando cita para {{ $paciente->nombre(3) }} {{ $paciente->sede->nombre }} {{ $paciente->sede->id }}</h4>

                <!-- 1. MOTIVO DE LA CITA (Siempre visible) -->
                <div id="divMotivoCita" class="card mb-5 shadow-sm" style="background-color: #f8f7fa">
                    <div class="card-header pb-1">
                        <h6 class="card-title mb-0 fw-semibold">Selecciona una opción</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mt-3">
                            <div class="col-md-12 mb-3 mb-md-0">
                                <div class="mb-3 col-12 col-md-12">
                                    <label class="form-label" for="tipoDeCita">
                                        Motivo de la cita
                                    </label>
                                    <!--
                                        - wire:model.live actualiza la propiedad en PHP en tiempo real.
                                        - El name="tipoDeCita" ya no es necesario.
                                    -->
                                    <select id="tipoDeCita" wire:model.live="tipoDeCita" class="select2 form-select" data-allow-clear="true">
                                        <option value="" selected>Selecciona un motivo...</option>

                                        <!-- Loop dinámico desde la BD -->
                                        @foreach($tiposDeConsejeria as $tipo)
                                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                        @endforeach

                                    </select>
                                    <!-- Error de validación de Livewire -->
                                    @error('tipoDeCita') <div class="text-danger form-label">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-md-12 mb-3 mb-md-0">
                              <label for="notas_paciente" class="form-label">Describe brevemente tu situación</label>
                              <div>
                                  <textarea class="form-control" id="notas_paciente" wire:model="notas_paciente"
                                      @disabled(!$tipoDeCita)
                                  ></textarea>
                              </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- 2. MEDIO DE LA CITA (Visible si se seleccionó motivo) -->
                @if($tipoDeCita)
                <div id="divMedioDeLaCita" class="card mb-5 shadow-sm" style="background-color: #f8f7fa">
                    <div class="card-header pb-1">
                        <h6 class="card-title mb-0 fw-semibold">¿Qué tipo de cita deseas agendar?</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mt-3">

                            <div class="col-6 mb-6">
                                <div class="form-check custom-option custom-option-basic rounded-3 shadow-sm border">
                                    <label class="form-check-label custom-option-content p-3">
                                        <span class="custom-option-header m-0 pb-0">
                                            <span class="h6 mb-0 d-flex align-items-center"><i class="ti ti-building-community me-3"></i> Presencial</span>
                                            <!--
                                                - wire:model.live para actualizar la propiedad.
                                                - Cambiamos value="si" por "presencial" (más claro).
                                                - El name="" ya no es necesario.
                                            -->
                                            <input wire:model.live="medioDeLaCita" class="form-check-input" type="radio" value="1" id="citaPresencial" />
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-6 ">
                                <div class="form-check custom-option custom-option-basic rounded-3 shadow-sm border">
                                    <label class="form-check-label custom-option-content p-3">
                                        <span class="custom-option-header m-0 pb-0">
                                            <span class="h6 mb-0 d-flex align-items-center"><i class="ti ti-device-computer-camera me-3"></i> Virtual</span>
                                            <input wire:model.live="medioDeLaCita" class="form-check-input" type="radio" value="2" id="citaVirtual" />
                                        </span>
                                    </label>
                                </div>
                            </div>
                            @error('medioDeLaCita') <div class="text-danger form-label mt-2">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                @endif

                <!-- 3. CONSEJERO (Visible si se seleccionó medio) -->
                @if($medioDeLaCita)
                <div id="divConsejero" class="card mb-5 shadow-sm" style="background-color: #f8f7fa">
                    <div class="card-header pb-1">
                        <h6 class="card-title mb-0 fw-semibold">Elige el consejero</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mt-3">
                            @if(!$consejeroSeleccionado)
                                <div class="col-12">
                                    <label class="form-label">Buscar consejero</label>
                                    
                                    {{-- 
                                        NUEVO CONTENEDOR WRAPPER:
                                        1. 'position-relative' aquí asegura que el dropdown se alinee a ESTE div, no a la columna entera.
                                        2. x-data controla la visibilidad.
                                    --}}
                                    <div class="position-relative w-100" 
                                        x-data="{ mostrarResultados: false }" 
                                        @click.outside="mostrarResultados = false">

                                        {{-- INPUT --}}
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="ti ti-search"></i></span>
                                            <input type="text" class="form-control" 
                                                placeholder="Buscar por nombre, correo o identificación..."
                                                wire:model.live.debounce.300ms="busquedaConsejero"
                                                {{-- Eventos para mostrar la lista --}}
                                                @focus="mostrarResultados = true" 
                                                @click="mostrarResultados = true"
                                                @keydown.escape="mostrarResultados = false"
                                                autocomplete="off"
                                            >
                                        </div>

                                        {{-- LISTA DROPDOWN --}}
                                        {{-- 
                                            AJUSTES VISUALES:
                                            - top: 100%: Empieza justo debajo del input.
                                            - start-0 end-0: Asegura que ocupe el 100% del ancho del padre.
                                            - z-index: Elevado para flotar sobre lo demás.
                                        --}}
                                        @if(!empty($consejerosEncontrados) && count($consejerosEncontrados) > 0)
                                            <div class="list-group position-absolute start-0 end-0 shadow-lg bg-white" 
                                                style="top: 100%; z-index: 1050; max-height: 200px; overflow-y: auto; margin-top: 5px; border-radius: 0.375rem; display: none;"
                                                x-show="mostrarResultados"
                                                x-transition.opacity.duration.200ms
                                            >
                                                @foreach($consejerosEncontrados as $consejero)
                                                    <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-2"
                                                        wire:click="seleccionarConsejero({{ $consejero->id }})">
                                                        <div class="avatar avatar-sm">
                                                            <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $consejero->foto) : $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $consejero->foto }}" alt="Avatar" class="rounded-circle">
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0">{{ $consejero->nombre(3) }}</h6>
                                                            <small class="text-black">{{ $consejero->email }}</small>
                                                        </div>
                                                    </button>
                                                @endforeach
                                                
                                                @if(count($consejerosEncontrados) >= $cantidadConsejeros)
                                                    <div x-data x-intersect="$wire.cargarMasConsejeros()" class="p-2 text-center">
                                                        <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                                                        <small class="text-black ms-1">Cargando más...</small>
                                                    </div>
                                                @endif
                                            </div>
                                        @elseif(count($consejerosEncontrados) == 0 && $medioDeLaCita)
                                            <div class="list-group position-absolute start-0 end-0 shadow-lg bg-white py-5" 
                                                style="top: 100%; z-index: 1050; display: none;"
                                                x-show="mostrarResultados">
                                                <div class=" text-center text-black ">
                                                    No se encontraron consejeros.
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                {{-- ... El código del "else" (consejero seleccionado) se mantiene igual ... --}}
                                <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between p-3 border rounded bg-white shadow-sm">
                                    {{-- ... contenido tarjeta consejero ... --}}
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar avatar-md">
                                            <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $consejeroSeleccionado->foto) : $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $consejeroSeleccionado->foto }}"  alt="Avatar" class="rounded-circle">
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">{{ $consejeroSeleccionado->nombre(3) }}</h6>
                                            <small class="text-black">Consejero seleccionado</small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-icon btn-label-danger rounded-pill"
                                        wire:click="limpiarConsejero" title="Cambiar consejero">
                                        <i class="ti ti-x"></i>
                                    </button>
                                </div>
                                </div>
                            @endif
                            @error('consejeroId') <div class="text-danger form-label mt-2">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                @endif

                <!-- 4. HORARIO (Visible si se seleccionó consejero) -->
                @if($consejeroId)
                    <div id="divHorario" class="card mb-5 shadow-sm" style="background-color: #f8f7fa">
                        <div class="card-header pb-1">
                            <h6 class="card-title mb-0 fw-semibold">Proximos horarios disponibles</h6>
                        </div>
                        <div class="card-body">

                            @if(!empty($horariosDisponibles))

                                <div class="d-flex align-items-center gap-2" x-data>

                                    <button type="button"
                                        class="btn btn-icon btn-sm btn-outline-secondary rounded-pill"
                                        style="flex-shrink: 0;" {{-- Evita que el botón se encoja --}}
                                        @click="$refs.dayScroller.scrollBy({ left: -250, behavior: 'smooth' })">
                                    <span class="ti ti-chevron-left"></span>
                                    </button>

                                    <div class="day-scroll-container mb-0" x-ref="dayScroller">
                                        <div class="d-inline-flex gap-2 py-1">

                                            @foreach(array_keys($horariosDisponibles) as $fecha)
                                                <button type="button"
                                                        wire:click="seleccionarDia('{{ $fecha }}')"
                                                        class="btn btn-sm text-center {{ $diaSeleccionado == $fecha ? 'btn-primary' : 'btn-outline-primary' }}"
                                                        style="white-space: nowrap;">
                                                        {{ \Carbon\Carbon::parse($fecha)->isoFormat('ddd') }} <br>
                                                        {{ \Carbon\Carbon::parse($fecha)->isoFormat('D MMM') }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    <button type="button"
                                        class="btn btn-icon btn-sm btn-outline-secondary rounded-pill"
                                        style="flex-shrink: 0;" {{-- Evita que el botón se encoja --}}
                                        @click="$refs.dayScroller.scrollBy({ left: 250, behavior: 'smooth' })">
                                        <span class="ti ti-chevron-right"></span>
                                    </button>
                                </div>
                                <hr class="my-2">

                                @if($diaSeleccionado && isset($horariosDisponibles[$diaSeleccionado]))
                                    @php
                                    $fecha = $diaSeleccionado;
                                    $horas = $horariosDisponibles[$diaSeleccionado];
                                    @endphp

                                    <small class="fw-bold mb-3 d-block">{{ \Carbon\Carbon::parse($fecha)->isoFormat('dddd, D [de] MMMM') }}</small>

                                    <div class="row mb-2">
                                        @foreach($horas as $hora)
                                        <div class="col-6 col-md-3 mb-2">
                                            <div class="form-check custom-option custom-option-basic rounded-3 shadow-sm border">
                                                <label class="form-check-label custom-option-content p-3">
                                                    <span class="custom-option-header m-0 pb-0">
                                                        <span class="h6 mb-0 d-flex align-items-center">
                                                            <i class="ti ti-calendar-time me-3"></i>
                                                            {{ \Carbon\Carbon::parse($hora)->format('g:i a') }}
                                                        </span>
                                                        <input wire:model.live="horarioSeleccionado" class="form-check-input" type="radio" value="{{ $fecha }} {{ $hora }}" id="horario-{{ $fecha }}-{{ $hora }}" />
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>

                                @endif
                            @else
                                <div class="col-12">
                                    <p class="text-center text-black">No hay horarios disponibles para este consejero.</p>
                                </div>
                            @endif

                            @error('horarioSeleccionado') <div class="text-danger form-label mt-2">{{ $message }}</div> @enderror

                        </div>
                    </div>
                @endif

            </div>
            <!-- Fin del contenido del formulario que se oculta -->

            <!-- SPINNER DE CARGA (Visible al guardar) -->
            <div wire:loading wire:target="guardarCita" class="text-center w-100 py-10">
                <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 300px;">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <h4 class="fw-semibold text-black">Agendando cita, por favor espere...</h4>
                    <p class="text-black">Esto puede tomar unos segundos.</p>
                </div>
            </div>

            <!-- BOTÓN DE GUARDAR (Siempre visible) -->
            <div class="w-100 fixed-bottom py-5 px-6 px-sm-0 border-top" style="background-color: #f8f7fa">
                <div class="col-12 col-sm-8 offset-sm-2 col-lg-6 offset-lg-3 d-grid gap-2 d-sm-flex justify-content-sm-end">

                    <!--
                        - El botón es de tipo "submit" para disparar wire:submit.
                        - Agregamos un indicador de carga (loading state).
                        - Deshabilitamos el botón mientras se procesa.
                    -->
                    <button type="submit" class="btn btnGuardar btn-primary rounded-pill px-7 py-2" wire:loading.attr="disabled">

                        <!-- Texto normal -->
                        <span wire:loading.remove wire:target="guardarCita">
                            <span class="align-middle me-sm-1 me-0"> Agendar </span>
                        </span>

                        <!-- Icono de carga -->
                        <span wire:loading wire:target="guardarCita">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Agendando...
                        </span>

                    </button>
                </div>
            </div>

        </form>
    </div>
</div>
