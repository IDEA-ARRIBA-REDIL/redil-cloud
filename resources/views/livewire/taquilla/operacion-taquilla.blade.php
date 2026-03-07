<div>
    {{--
    ==================================================================
    CARGA DE SCRIPTS Y ESTILOS (Sin cambios)
    ==================================================================
  --}}
    @section('vendor-style')
        @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
        <style>
            @media(max-width:750px) {


                .text-info {
                    color: black !important;
                    font-size: 12px;
                    padding: 5px !important;
                    border: solid 2px #95CDDF;
                    border-radius: 14px;
                    text-align: justify;
                }
            }

            @media(min-width:850px) {


                .text-info {
                    color: black !important;
                    font-size: 15px;
                    padding: 24px !important;
                    border: solid 2px #95CDDF;
                    border-radius: 14px;
                    text-align: justify;
                }
            }
        </style>
    @endsection

    @section('vendor-script')
        @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/js/app.js'])
    @endsection

    {{-- Cabecera de Taquilla Activa (Sin cambios) --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-semibold text-primary">Taquilla de pago</h4>
            <p class="mb-0 text-black">
                Operando desde: <strong class="text-dark">{{ $cajaActiva->nombre }}</strong>
                ({{ $cajaActiva->puntoDePago->nombre }})
            </p>
        </div>
        <a href="{{ route('taquilla.mis-cajas') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
            <i class="ti ti-logout me-1"></i>
            Cambiar de caja
        </a>
    </div>

    @include('layouts.status-msn')

    {{--
    ==================================================================
    SECCIÓN 1: Formulario de Búsqueda (Se mantiene la funcionalidad)
    ==================================================================
  --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title fw-semibold text-black mb-0">Verificación de asistente</h5>
            <small class="card-subtitle ">Busca al comprador y la actividad para verificar los requisitos.</small>
        </div>
        <div class="card-body">

            <form id="form-verificar-requisitos" wire:submit.prevent="verificarRequisitos">

                <div class="row g-3">

                    {{-- 1. Buscador de Usuario (El Comprador) --}}
                    <div class="col-md-12">
                        <label for="user_id" class="form-label text-black">Buscar por identificacion o nombre</label>
                        @livewire('usuarios.usuarios-para-busqueda', [
                            'id' => 'user_id',
                            'tipoBuscador' => 'unico',
                            'conDadosDeBaja' => 'no',
                            'class' => 'col-12',
                            'placeholder' => 'Buscar por nombre o identificación...',
                            'queUsuariosCargar' => 'todos',
                            'usuarioSeleccionadoId' => $compradorIdActual,
                            'obligatorio' => true,
                        ])
                        @error('compradorIdActual')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>


                    {{-- LÓGICA DE PARIENTES (Sin cambios) --}}
                    @if ($comprador && $parientes->isNotEmpty())
                        <div class="col-12">
                            <label class="form-label text-black">¿La inscripción es para el comprador?</label>
                            <div class="d-flex">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="inscripcion_propia"
                                        id="inscripcion-propia-si" value="1" wire:model.live="esInscripcionPropia">
                                    <label class="form-check-label text-black" for="inscripcion-propia-si">
                                        Sí, a nombre propio ({{ $comprador->nombre(3) }})
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="inscripcion_propia"
                                        id="inscripcion-propia-no" value="0" wire:model.live="esInscripcionPropia">
                                    <label class="form-check-label text-black" for="inscripcion-propia-no">
                                        No, inscribir a un familiar
                                    </label>
                                </div>
                            </div>
                        </div>

                        @if (!$esInscripcionPropia)
                            <div class="col-12" x-transition>
                                <label for="pariente-id-select" class="form-label text-black">Selecciona el familiar a
                                    inscribir</label>
                                <select id="pariente-id-select" class="form-select" wire:model="inscritoIdActual"
                                    required>
                                    <option value="">Selecciona un familiar...</option>
                                    @foreach ($parientes as $pariente)
                                        <option value="{{ $pariente->id }}">
                                            {{ $pariente->nombre(3) }}
                                            ({{ $comprador->genero == 0 ? $pariente->nombre_masculino : $pariente->nombre_femenino }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('inscritoIdActual')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif
                    @endif
                </div>

                {{-- 3. Selector de Actividad --}}
                <div class="col-md-12 mt-5">
                    <label for="actividad_id" class="form-label text-black">Selecciona la actividad</label>
                    <div wire:ignore>
                        <select id="actividad_id" wire:model.defer="actividadIdActual"
                            class="form-select select2-livewire" required>
                            <option value="">Selecciona una actividad...</option>
                            @foreach ($actividadesDisponibles as $actividad)
                                <option value="{{ $actividad->id }}">{{ $actividad->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('actividadIdActual')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>


                <div class="mt-4 text-end">
                    <button type="button" wire:click="limpiar" class="btn btn-outline-secondary rounded-pill">
                        <i class="ti ti-trash me-1"></i>
                        Limpiar
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill me-2">
                        <i class="ti ti-search me-1"></i>
                        Buscar
                    </button>

                </div>

            </form>
        </div>
    </div>

    {{--
    ==================================================================
    SECCIÓN 2: Resultados de Livewire (Diseño Actualizado)
    ==================================================================
  --}}
    @if ($verificacionEnviada)
        <div class="card">

            @if ($validacionExitosa)
                <div class="card-header ">
                    {{-- Se remueve el título del card-header y se integra al card-body --}}
                </div>

                <div class="card-body">
                    @if ($actividadSeleccionada->tipo->tipo_escuelas)
                        <h5 class="mb-3 fw-semibold text-black">Selecciona la materia a matricular:</h5>
                    @elseif($actividadSeleccionada->tipo->es_gratuita)
                        <h5 class="mb-3 fw-semibold text-black">Selecciona la categoria de inscripción:</h5>
                    @else
                        <h5 class="mb-3 fw-semibold text-black">Selecciona la categoria de compra:</h5>
                    @endif

                    <div class="row g-3">
                        @forelse($categoriasDisponibles as $item)
                            @php $categoria = $item->categoria; @endphp
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card h-100 border shadow-sm ">

                                    {{-- 1. CARD HEADER (Título, Estado, Precio) --}}
                                    <div style="background-color:#F9F9F9!important" class="card-header py-3 px-4">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0 fw-semibold text-black">{{ $categoria->nombre }}</h5>

                                            @php
                                                $precio = $categoria
                                                    ->monedas()
                                                    ->where('moneda_id', $monedaPrincipal->id)
                                                    ->first();
                                                $valor = $precio->pivot->valor ?? 0;
                                            @endphp

                                            {{-- Badges de Estado --}}
                                            @switch($item->estado)
                                                @case('INSCRITO')
                                                @case('APROBADA')
                                                    <span class="mb-0 ms-2 px-2 btn btn-sm btn-success text-white rounded-pill">
                                                        {{ $item->estado == 'APROBADA' ? 'Aprobada' : 'Ya inscrito' }}
                                                    </span>
                                                @break

                                                @case('ABONO_PENDIENTE')
                                                    <span
                                                        class="mb-0 ms-2 px-2 badge bg-warning text-white rounded-pill">Abono</span>
                                                @break

                                                @case('BLOQUEADA')
                                                    <span
                                                        class="mb-0 ms-2 px-2 btn btn-sm btn-danger text-white rounded-pill">No
                                                        permitida</span>
                                                @break

                                                @case('DISPONIBLE')
                                                    <span
                                                        class="mb-0 ms-2 px-2 fw-bold badge btn-primary text-white rounded-pill">
                                                        @if ($valor <= 0)
                                                            GRATIS
                                                        @else
                                                            {{ $monedaPrincipal->nombre_corto }}
                                                            ${{ number_format($valor, 0, ',', '.') }}
                                                        @endif
                                                    </span>
                                                @break
                                            @endswitch
                                        </div>
                                    </div>

                                    {{-- 2. CARD BODY (Descripción, Bloqueo, Botones, Costo) --}}
                                    <div class="card-body mt-4 d-flex flex-column">

                                        {{-- 2a. BLOQUE DE BLOQUEO (Diseño Imagen 2) --}}
                                        @if ($item->estado == 'BLOQUEADA')
                                            <div class="alert alert-danger  p-3 mb-3 h-100 d-flex text-start"
                                                role="alert">
                                                {{-- Aseguramos el borde rojo --}}

                                                <div class="d-flex align-items-start me-3">
                                                    {{-- 1. ÍCONO DE BLOQUEO (Columna Izquierda) --}}
                                                    <i style="padding-top: 50%;    font-size: 30px !important;"
                                                        class="ti ti-lock "></i>
                                                </div>

                                                <div class="d-flex flex-column flex-grow-1">
                                                    {{-- 2. CONTENIDO (Columna Derecha) --}}
                                                    <h6 class="alert-heading fw-semibold mb-1">
                                                        No cumples los requisitos:
                                                    </h6>

                                                    {{-- Lista de Motivos (Seguimos usando la lista sin estilo de Bootstrap) --}}
                                                    <ul class="mb-0 ps-0 fw-semibold list-unstyled small">
                                                        @foreach ($item->motivos as $motivo)
                                                            <li class="small text-start">
                                                                {{ $motivo }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>

                                            {{-- 2b. DESCRIPCIÓN (Cuando está disponible) --}}
                                        @else
                                            <p style="  display: -webkit-box;
																																															-webkit-box-orient: vertical;
																																															-webkit-line-clamp: 3;
																																															overflow: hidden;
																																															text-overflow: ellipsis;
																																															text-align: left;"
                                                class="text-black small mb-3">
                                                {{-- Selecciona Materia o Actividad --}}
                                                @if ($actividadSeleccionada->tipo->tipo_escuelas)
                                                    {{ $categoria->materia->descripcion ?? 'Sin descripción de materia.' }}
                                                @else
                                                    {{ $actividadSeleccionada->descripcion ?? 'Sin descripción de actividad.' }}
                                                @endif
                                            </p>
                                        @endif

                                        {{-- 2c. DETALLES DE CUPOS Y ACCIÓN --}}

                                        <div class="mt-auto pt-3">

                                            {{-- Detalles de Cupos/Costo (para tarjetas NO BLOQUEADAS) --}}
                                            @if ($item->estado != 'BLOQUEADA')
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <div class="small">
                                                        <span class="d-block text-muted">Cupos:</span>
                                                        <strong>{{ $categoria->aforo_ocupado ?? 0 }} /
                                                            {{ $categoria->aforo > 0 ? $categoria->aforo : '∞' }}</strong>
                                                    </div>
                                                    <div class="small text-end">
                                                        <span class="d-block text-muted">Costo:</span>
                                                        <strong class="text-primary">
                                                            @if ($valor <= 0)
                                                                GRATIS
                                                            @else
                                                                {{ $monedaPrincipal->nombre_corto }}
                                                                ${{ number_format($valor, 0, ',', '.') }}
                                                            @endif
                                                        </strong>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Botones de Acción --}}
                                            <hr>
                                            <div class="text-end">
                                                @switch($item->estado)
                                                    @case('DISPONIBLE')
                                                        @if ($valor <= 0)
                                                            {{-- BOTÓN GRATIS --}}
                                                            <button type="button"
                                                                class="btn btn-primary btn-md rounded-pill "
                                                                wire:click="procesarInscripcionGratuita({{ $categoria->id }})"
                                                                wire:loading.attr="disabled"
                                                                wire:target="procesarInscripcionGratuita({{ $categoria->id }})">
                                                                <span wire:loading.remove
                                                                    wire:target="procesarInscripcionGratuita({{ $categoria->id }})">
                                                                    @if ($actividadSeleccionada->tipo->tipo_escuelas)
                                                                        Matricular (Gratis)
                                                                    @else
                                                                        Inscribir
                                                                    @endif
                                                                </span>
                                                                <span wire:loading
                                                                    wire:target="procesarInscripcionGratuita({{ $categoria->id }})">
                                                                    <span class="spinner-border spinner-border-sm"
                                                                        role="status" aria-hidden="true"></span>
                                                                </span>
                                                            </button>
                                                        @else
                                                            {{-- BOTÓN PAGO NORMAL/MATRÍCULA --}}
                                                            <a href="{{ route('taquilla.mostrarPaginaDePago', [
                                                                'cajaActiva' => $cajaActiva,
                                                                'comprador' => $comprador,
                                                                'inscrito' => $usuarioAValidar,
                                                                'actividad' => $actividadSeleccionada,
                                                                'categoria' => $categoria,
                                                                'modo' => 'compra',
                                                            ]) }}"
                                                                class="btn btn-primary btn-sm rounded-pill w-100">
                                                                @if ($actividadSeleccionada->tipo->tipo_escuelas)
                                                                    Procesar matrícula
                                                                @else
                                                                    Procesar compra
                                                                @endif
                                                            </a>
                                                        @endif
                                                    @break

                                                    @case('ABONO_PENDIENTE')
                                                        {{-- LÓGICA DE ABONO PENDIENTE --}}
                                                        <div class="alert alert-warning border-warning p-2 mb-2">
                                                            <small class="d-block text-center">
                                                                Pagado: <strong>{{ $monedaPrincipal->nombre_corto }}
                                                                    ${{ number_format($item->totalPagado, 0, ',', '.') }}</strong>
                                                                <br>Faltan: <strong>{{ $monedaPrincipal->nombre_corto }}
                                                                    ${{ number_format($item->valorTotal - $item->totalPagado, 0, ',', '.') }}</strong>
                                                            </small>
                                                        </div>
                                                        <a href="{{ route('taquilla.mostrarPaginaDePago', [
                                                            'cajaActiva' => $cajaActiva,
                                                            'comprador' => $comprador,
                                                            'inscrito' => $usuarioAValidar,
                                                            'actividad' => $actividadSeleccionada,
                                                            'categoria' => $categoria,
                                                            'modo' => 'compra',
                                                        ]) }}"
                                                            class="btn btn-warning btn-sm rounded-pill w-100">
                                                            <i class="ti ti-plus me-1"></i> Agregar abono
                                                        </a>
                                                    @break

                                                    @case('BLOQUEADA')
                                                        <button type="button"
                                                            class="btn bg-light btn-disabled  btn-sm rounded-pill w-100"
                                                            disabled>
                                                            No Disponible
                                                        </button>
                                                    @break

                                                    @case('INSCRITO')
                                                    @case('APROBADA')
                                                        <button type="button"
                                                            class="btn btn-success btn-sm rounded-pill w-100" disabled>
                                                            {{ $item->estado == 'APROBADA' ? 'Aprobada' : 'Ya nnscrito' }}
                                                        </button>
                                                    @break
                                                @endswitch
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                                {{-- Mensaje de Bloqueo Total (Diseño Imagen 1) --}}
                                <div class="col-12">
                                    <div style="border: solid 2px #95CDDF;" class="alert  p-4">

                                        <p class="mb-0 fw-bold  ">
                                            <i class="ti ti-info-circle ti-xl mb-3"></i>
                                            No se puede inscribir a {{ $usuarioAValidar->nombre(3) }} en esta actividad
                                            porque no cumple los requisitos.
                                        </p>
                                        <p class="mb-0">
                                            Motivo: {{ $mensajeError }}
                                        </p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @elseif($mensajeError)
                    {{-- Mensaje de Bloqueo Total (Diseño Imagen 1, en caso de error general) --}}
                    <div class="card-body">
                        <div style="border: solid 2px #95CDDF;" class="alert  p-4">

                            <p class="mb-0 fw-bold">
                                <i class="ti ti-info-circle ti-xl mb-3"></i>
                                No se puede inscribir a
                                {{ $usuarioAValidar ? $usuarioAValidar->nombre(3) : 'seleccionado' }} en esta actividad
                                porque no cumple los requisitos.
                            </p>
                            <p class="mb-0">
                                Motivo: {{ $mensajeError }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        @endif


        {{--
    ==================================================================
    SCRIPT CORREGIDO (Sin cambios funcionales)
    ==================================================================
  --}}
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {

                    // --- 1. Inicialización de Select2 ---
                    const selectActividad = $('#actividad_id');

                    if (selectActividad.length) {
                        selectActividad.select2({
                            placeholder: 'Selecciona una actividad...',
                            allowClear: true
                        });

                        // --- 2. Listener: Select2 -> Livewire ---
                        selectActividad.on('change', function(e) {
                            @this.set('actividadIdActual', e.target.value);
                        });
                    }

                    // --- 3. Listener: Livewire -> Select2 ---
                    @this.on('resetear-select-actividad', () => {
                        if (selectActividad.length) {
                            selectActividad.val(null).trigger('change');
                        }
                    });
                });

                // --- 4. Listeners Globales de Livewire ---
                document.addEventListener('livewire:initialized', () => {

                    // Listener para SweetAlerts
                    Livewire.on('notificacion', (event) => {
                        Swal.fire({
                            title: event.titulo,
                            text: event.mensaje,
                            icon: event.tipo,
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            cancelButtonText: 'Cerrar'
                        })
                    });

                    // Listener para 'usuarios-para-busqueda'
                    Livewire.on('usuario-seleccionado', (event) => {
                        // Re-despacha el evento al método 'cargarParientes'
                        @this.dispatch('usuario-seleccionado', {
                            id: event.id
                        });
                    });

                    // Escucha el evento 'limpiar' de este componente
                    @this.on('resetear-buscador-usuario', () => {
                        // Emite un evento global para el hijo
                        Livewire.dispatch('reset-buscador');
                    });
                });
            </script>
        @endpush

    </div>
