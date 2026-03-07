@extends('layouts/blankLayout')

{{-- Título de la página --}}
@section('title', 'Editar Categoría')

{{-- Estilos y Scripts (idénticos a la vista de crear) --}}
@section('vendor-style')
@vite(['resources/assets/vendor/libs/select2/select2.scss'])
<style>
    .minus {
        border: solid 2px #1977E5 !important;
        border-radius: 20px;
        padding: 2px !important;
        width: 31px;
        height: 30px;
        margin-right: 6px;
        color: #1977E5;
    }

    .plus {
        border: solid 2px #1977E5 !important;
        border-radius: 20px;
        padding: 2px !important;
        width: 31px;
        height: 30px;
        margin-left: 6px;
        color: #1977E5;
    }

</style>
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
<script type="module">
    $(document).ready(function() {
            $('.select2').select2({
                placeholder: 'Selecciona una o más opciones',
                allowClear: true
            });
        });

        // Script para los botones de cantidad (spinner)
        document.addEventListener('DOMContentLoaded', function() {
            const spinnerGroups = document.querySelectorAll('.input-group');
            spinnerGroups.forEach(group => {
                const decrementButton = group.querySelector('[data-action="decrement"]');
                const incrementButton = group.querySelector('[data-action="increment"]');
                const input = group.querySelector('.quantity-input');
                if (!decrementButton || !incrementButton || !input) return;

                decrementButton.addEventListener('click', function() {
                    let currentValue = parseInt(input.value, 10);
                    const minValue = parseInt(input.min, 10);
                    if (currentValue > minValue) {
                        input.value = currentValue - 1;
                    }
                });
                incrementButton.addEventListener('click', function() {
                    let currentValue = parseInt(input.value, 10);
                    const maxValue = parseInt(input.max, 10);
                    if (isNaN(maxValue) || currentValue < maxValue) {
                        input.value = currentValue + 1;
                    }
                });
            });
        });
    </script>
@endsection

@section('content')
{{-- Barra de navegación superior --}}
<nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center fixed-top">
    <div class="col-3 text-start">
        <a href="{{ route('actividades.categorias', $actividad) }}" class="btn rounded-pill waves-effect waves-light text-white">
            <span class="ti-xs ti ti-arrow-left me-2"></span><span class="d-none d-md-block fw-normal">Volver</span>
        </a>
    </div>
    <div class="col-6 pl-5 text-center">
        {{-- Título cambiado a "Editar" --}}
        <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Editar categoría</h5>
    </div>
    <div class="col-3 text-end">
        <a href="{{ route('actividades.categorias', $actividad) }}" class="btn rounded-pill waves-effect waves-light text-white">
            <span class="d-none d-md-block fw-normal">Salir</span><span class="ti-xs ti ti-x mx-2"></span>
        </a>
    </div>
</nav>

{{-- Contenido principal --}}
<div class="container-fluid" style="padding-top: 100px; padding-bottom: 20px;">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card-header mb-3">
                <h4 class="mb-0">Editando categoría para la actividad: <span class="text-black fw-bold">{{ $actividad->nombre }}</span></h4>
            </div>

            {{-- CAMBIO 1: El formulario apunta a la ruta de 'update' y usa el método PUT --}}
            <form action="{{ route('actividades.updateCategoria', $categoria) }}" method="POST" id="formEditarCategoria">
                @csrf
                @method('PUT')

                <div style="margin-bottom:80px">
                    <div class="card-header">
                        <h5 class="mb-0 fw-semibold">Información general</h5>
                    </div>
                    <div class="card-body row g-4">

                        {{-- CAMBIO 2: Rellenamos todos los campos con los valores existentes del objeto $categoria --}}
                        {{-- El patrón es: old('nombre_campo', $categoria->nombre_campo) --}}

                        <div class="col-12 col-md-4">
                            <label class="form-label" for="nombre">Nombre</label>
                            <input required type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $categoria->nombre) }}" required />
                            @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="aforo">Aforo</label>
                            <div class="input-group">
                                <button class="btn btn-outline-secondary minus rounded-circle p-0 mx-2" type="button" style="width: 25px; margin-top:5px;height: 25px; border: solid 2px #1977E5 !important;" data-action="decrement"><i style="color:#1977E5;" class="ti ti-minus"></i></button>
                                <input required type="number" name="aforo" id="aforo" min="0" value="{{ old('aforo', $categoria->aforo) }}" class="form-control text-center rounded quantity-input @error('aforo') is-invalid @enderror" required>
                                <button class="btn btn-outline-secondary plus rounded-circle p-0 mx-2" type="button" style="width: 25px; margin-top:5px;height: 25px; border: solid 2px #1977E5 !important" data-action="increment"><i style="color:#1977E5;" class="ti ti-plus"></i></button>
                                @error('aforo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- limite de invitados-->
                        @if($actividad->tiene_invitados == true)
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="limiteInvitados">Cantidad limite de invitados </label>
                            {{-- INICIO DE LA CORRECCIÓN --}}
                            <div class="input-group">
                                {{-- Botón de menos --}}
                                <button class="btn btn-outline-secondary minus rounded-circle p-0 mx-2" type="button" style="width: 25px; margin-top:5px;height: 25px; border: solid 2px #1977E5 !important;" data-action="decrement">
                                    <i style="color:#1977E5;" class="ti ti-minus"></i>
                                </button>

                                <input value="{{ old('limite_compras', $categoria->limite_invitados) }}" required type="number" id="limiteInvitados" name="limiteInvitados" min="1" class="form-control text-center rounded quantity-input @error('limiteInvitados') is-invalid @enderror" />
                                {{-- Botón de más --}}
                                <button class="btn btn-outline-secondary plus rounded-circle p-0 mx-2" type="button" style="width: 25px; margin-top:5px;height: 25px;border: solid 2px #1977E5 !important;" data-action="increment">
                                    <i style="color:#1977E5;" class="ti ti-plus"></i>
                                </button>

                                @error('limiteInvitados')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- FIN DE LA CORRECCIÓN --}}
                        </div>
                        @endif

                        <div class="col-12 col-md-3">
                            <label class="form-label" for="limite_compras">Límite de compras</label>
                            <div class="input-group">
                                <button class="btn btn-outline-secondary rounded-circle p-0 mx-2 " type="button" style="width: 25px; margin-top:5px;height: 25px; border: solid 2px #1977E5 !important;" data-action="decrement"><i style="color:#1977E5;" class="ti ti-minus"></i></button>
                                <input required @if ($actividad->tipo->unica_compra == true) max="1" @endif min="1"
                                type="number" name="limite_compras" id="limite_compras"
                                class="form-control text-center rounded quantity-input @error('limite_compras') is-invalid @enderror"
                                value="{{ old('limite_compras', $categoria->limite_compras) }}" />
                                <button class="btn btn-outline-secondary rounded-circle p-0 mx-2" type="button" style="width: 25px; margin-top:5px;height: 25px;border: solid 2px #1977E5 !important;" data-action="increment"><i style="color:#1977E5;" class="ti ti-plus"></i></button>
                                @error('limite_compras')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12" x-data="{ esGratuita: {{ old('es_gratuita', $categoria->es_gratuita) ? 'true' : 'false' }} }">
                            <div class="form-check form-switch form-switch-lg mb-2">
                                <input type="checkbox" name="es_gratuita" id="es_gratuita" class="form-check-input" value="1" x-model="esGratuita" @checked(old('es_gratuita', $categoria->es_gratuita))>
                                <label class="form-check-label" for="es_gratuita">¿Es gratuita?</label>
                            </div>
                            @php $valoresGuardados = $categoria->monedas->pluck('pivot.valor', 'id'); @endphp
                            <div x-show="!esGratuita" x-transition class="row mt-3 pt-3">
                                <h6 class="mb-3">Valores por Moneda</h6>
                                @forelse ($monedasActividad as $moneda)
                                <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                    <label class="form-label" for="valoresMonedas.{{ $moneda->id }}">Valor en:
                                        <b>{{ $moneda->nombre }}</b></label>
                                    <input required type="number" step="0.01" min="0" name="valoresMonedas[{{ $moneda->id }}]" id="valoresMonedas.{{ $moneda->id }}" class="form-control @error('valoresMonedas.' . $moneda->id) is-invalid @enderror" value="{{ old('valoresMonedas.' . $moneda->id, $valoresGuardados[$moneda->id] ?? '') }}">
                                    @error('valoresMonedas.' . $moneda->id)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @empty
                                <div class="col-12">
                                    <div class="alert alert-warning">No hay monedas habilitadas.</div>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        @if ($actividad->restriccion_por_categoria == true)
                        <div class="col-12">
                            <hr>
                            <h5 class="mb-3 fw-semibold"> Restricciones por categoría</h5>
                            <div class="row g-4">

                                {{-- Para los selects, el patrón es similar: old('nombre_campo', $categoria->nombre_campo) --}}
                                <div class="col-lg-6 col-sm-12">
                                    <label class="form-label" for="genero">Género</label>
                                    <select required name="genero" id="genero" class="select2 form-select">
                                        <option value="3" @selected(old('genero', $categoria->genero) == 3)>Ambos</option>
                                        <option value="1" @selected(old('genero', $categoria->genero) == 1)>Hombres</option>
                                        <option value="2" @selected(old('genero', $categoria->genero) == 2)>Mujeres</option>
                                    </select>
                                    @error('genero')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-6 col-sm-12">
                                    <label class="form-label" for="vinculacion_grupo">Vinculación a grupo</label>
                                    <select required name="vinculacion_grupo" id="vinculacion_grupo" class="select2 form-select">
                                        <option value="3" @selected(old('vinculacion_grupo', $categoria->vinculacion_grupo) == 3)>Ambos</option>
                                        <option value="1" @selected(old('vinculacion_grupo', $categoria->vinculacion_grupo) == 1)>Pertenece a grupo
                                        </option>
                                        <option value="2" @selected(old('vinculacion_grupo', $categoria->vinculacion_grupo) == 2)>No pertenece</option>
                                    </select>
                                    @error('vinculacion_grupo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-lg-6 col-sm-12">
                                    <label class="form-label" for="actividad_grupo">Actividad en grupo</label>
                                    <select required name="actividad_grupo" id="actividad_grupo" class="select2 form-select">
                                        <option value="3" @selected(old('actividad_grupo', $categoria->actividad_grupo) == 3)>Ambos</option>
                                        <option value="1" @selected(old('actividad_grupo', $categoria->actividad_grupo) == 1)>Activos</option>
                                        <option value="2" @selected(old('actividad_grupo', $categoria->actividad_grupo) == 2)>Inactivos</option>
                                    </select>
                                    @error('actividad_grupo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Para los selects múltiples, el patrón cambia un poco para manejar los arrays --}}
                                @php $sedesGuardadas = $categoria->sedes->pluck('id')->toArray(); @endphp
                                <div class="col-lg-6 col-sm-12">
                                    <label class="form-label" for="sedes">Sedes habilitadas</label>
                                    <select required name="sedes[]" id="sedes" class="select2 form-select" multiple>
                                        @foreach ($sedes as $sede)
                                        <option value="{{ $sede->id }}" @selected(in_array($sede->id, old('sedes', $sedesGuardadas)))>
                                            {{ $sede->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('sedes')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                @php $rangosGuardados = $categoria->rangosEdad->pluck('id')->toArray(); @endphp
                                <div class="col-lg-6 col-sm-12">
                                    <label class="form-label" for="rangos_edad">Rangos de edad</label>
                                    <select name="rangos_edad[]" id="rangos_edad" class="select2 form-select" multiple>
                                        @foreach ($rangosEdad as $rango)
                                        <option value="{{ $rango->id }}" @selected(in_array($rango->id, old('rangos_edad', $rangosGuardados)))>
                                            {{ $rango->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @php $tiposUsuariosGuardados = $categoria->tipoUsuarios->pluck('id')->toArray(); @endphp
                                <div class="col-lg-6 col-sm-12">
                                    <label class="form-label" for="tipo_usuarios">Tipos de usuario</label>
                                    <select name="tipo_usuarios[]" id="tipo_usuarios" class="select2 form-select" multiple>
                                        @foreach ($tipoUsuarios as $tipo)
                                        <option value="{{ $tipo->id }}" @selected(in_array($tipo->id, old('tipo_usuarios', $tiposUsuariosGuardados)))>
                                            {{ $tipo->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @php $estadosCivilesGuardados = $categoria->estadosCiviles->pluck('id')->toArray(); @endphp
                                <div class="col-lg-6 col-sm-12">
                                    <label class="form-label" for="estados_civiles">Estados civiles</label>
                                    <select name="estados_civiles[]" id="estados_civiles" class="select2 form-select" multiple>
                                        @foreach ($estadosCiviles as $estado)
                                        <option value="{{ $estado->id }}" @selected(in_array($estado->id, old('estados_civiles', $estadosCivilesGuardados)))>
                                            {{ $estado->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @php $tipoServiciosGuardados = $categoria->tipoServicios->pluck('id')->toArray(); @endphp

                                <div class="col-lg-6 col-sm-12">
                                    <label class="form-label" for="tipo_servicios">Tipos de servicios</label>
                                    <select name="tipo_servicios[]" id="tipo_servicios" class="select2 form-select" multiple>
                                        @foreach ($tipoServicios as $tipoSer)
                                        <option value="{{ $tipoSer->id }}" @selected(in_array($tipoSer->id, old('tipo_servicios', $tipoServiciosGuardados)))>
                                            {{ $tipoSer->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 mb-3">
                                   <hr class="my-4">
                                    <h6 class="text-primary mb-3">
                                        <i class="ti ti-clipboard-check me-2"></i>
                                        Pasos crecimiento
                                    </h6>
                                    @livewire('actividad-categoria.gestionar-pasos-requisito', ['categoria' => $categoria])
                                </div>

                                <div class="col-12 mb-3">
                                    @livewire('actividad-categoria.gestionar-pasos-culminados', ['categoria' => $categoria])
                                </div>

                                {{-- NUEVA SECCIÓN: TAREAS DE CONSOLIDACIÓN --}}
                                <div class="col-12 mt-4">
                                    <hr class="my-4">
                                    <h6 class="text-primary mb-3">
                                        <i class="ti ti-clipboard-check me-2"></i>
                                        Tareas de Consolidación
                                    </h6>

                                    {{-- Tareas Requisito --}}
                                    <div class="mb-3">
                                        @livewire('actividad-categoria.gestionar-tareas-requisito', ['categoria' => $categoria])
                                    </div>

                                    {{-- Tareas a Culminar --}}
                                    <div class="mb-3">
                                        @livewire('actividad-categoria.gestionar-tareas-culminadas', ['categoria' => $categoria])
                                    </div>
                                </div>
                                {{-- FIN SECCIÓN: TAREAS DE CONSOLIDACIÓN --}}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Barra de acciones inferior --}}
                <div class="w-100 fixed-bottom py-3 px-4 border-top bg-body">
                    <div class="col-12 col-lg-10 col-xl-8 offset-lg-2 ">
                        <a href="{{ route('actividades.categorias', $actividad) }}" class="btn btn-outline-secondary me-3 rounded-pill">Cancelar</a>
                        <button style="float: right;" type="submit" class="btn btn-primary rounded-pill">Guardar
                            Cambios</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
