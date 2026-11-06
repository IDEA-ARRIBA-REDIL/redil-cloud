@extends('layouts/blankLayout')

@section('title', 'Editar Categoría de Escuela')

{{-- Estilos y Scripts (igual que en la vista de crear) --}}
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
@section('vendor-script') @vite(['resources/assets/vendor/libs/select2/select2.js']) @endsection
@section('page-script')
    <script type="module">
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: 'Seleccione una opción',
                allowClear: true
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
        });
    </script>
@endsection

@section('content')
    <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center fixed-top">
        <div class="col-3 text-start"><a href="{{ route('actividades.categoriasEscuelas', $actividad) }}"
                class="btn rounded-pill waves-effect waves-light text-white"><span
                    class="ti-xs ti ti-arrow-left me-2"></span><span class="d-none d-md-block fw-normal">Volver</span></a>
        </div>
        <div class="col-6 pl-5 text-center">
            <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Editar Categoría de Escuela</h5>
        </div>
        <div class="col-3 text-end"><a href="{{ route('actividades.categoriasEscuelas', $actividad) }}"
                class="btn rounded-pill waves-effect waves-light text-white"><span
                    class="d-none d-md-block fw-normal">Salir</span><span class="ti-xs ti ti-x mx-2"></span></a></div>
    </nav>

    <div class="container-fluid" style="padding-top: 100px; padding-bottom: 20px;">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8">
                <div class="card-header mb-3">
                    <h4 class="mb-0">Editando categoría para la escuela: <span
                            class="text-black fw-bold">{{ $actividad->nombre }}</span></h4>
                </div>

                <form action="{{ route('actividades.updateCategoriaEscuela', $categoria) }}" method="POST"
                    id="formEditarCategoriaEscuela">
                    @csrf
                    @method('PUT')
                    <div style="margin-bottom:80px">
                        <div class="card-header">
                            <h5 class="mb-0 fw-semibold">Información general</h5>
                        </div>
                        <div class="card-body row g-4">

                            {{-- Los campos se rellenan con: old('nombre_campo', $categoria->nombre_campo) --}}
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="nombre">Nombre de la Categoría</label>
                                <input type="text" name="nombre" id="nombre"
                                    class="form-control @error('nombre') is-invalid @enderror"
                                    value="{{ old('nombre', $categoria->nombre) }}" required />
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="materia_periodo_id">Materia Asociada</label>
                                <select name="materia_periodo_id" id="materia_periodo_id"
                                    class="select2 form-select @error('materia_periodo_id') is-invalid @enderror" required>
                                    <option value="">Seleccione una materia</option>
                                    @foreach ($materiasPeriodo as $mp)
                                        <option value="{{ $mp->id }}"
                                            @selected(old('materia_periodo_id', $categoria->materia_periodo_id) == $mp->id)
                                            @disabled(in_array($mp->id, $materiasUsadasIds))>
                                            {{ $mp->materia->nombre }}
                                            @if (in_array($mp->id, $materiasUsadasIds))
                                                (Ya asignada a otra categoría)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('materia_periodo_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="aforo">Aforo (Opcional, dejar vacío para ilimitado)</label>
                                <input type="number" name="aforo" id="aforo" min="0"
                                    class="form-control @error('aforo') is-invalid @enderror"
                                    placeholder="Sin límite"
                                    value="{{ old('aforo', $categoria->aforo) }}" />
                                @error('aforo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 " x-data="{ esGratuita: {{ old('es_gratuita', $categoria->es_gratuita) ? 'true' : 'false' }} }">
                                <div class="form-check form-switch form-switch-lg mb-2 {{ $categoria->es_gratuita ==  false ? 'd-none' : '' }}">

                                    <input type="checkbox" name="es_gratuita" id="es_gratuita" class="form-check-input"
                                        value="1" x-model="esGratuita" @checked(old('es_gratuita', $categoria->es_gratuita))>
                                    <label class="form-check-label" for="es_gratuita">¿Es gratuita?</label>
                                </div>
                                @php $valoresGuardados = $categoria->monedas->pluck('pivot.valor', 'id'); @endphp
                                <div x-show="!esGratuita" x-transition class="row mt-3 pt-3 border-top">
                                    <h6 class="mb-3">Valores por Moneda</h6>
                                    @forelse ($monedasActividad as $moneda)
                                        <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                            <label class="form-label">Valor en: <b>{{ $moneda->nombre }}</b></label>
                                            <input type="number" step="0.01" min="0"
                                                name="valoresMonedas[{{ $moneda->id }}]"
                                                class="form-control @error('valoresMonedas.' . $moneda->id) is-invalid @enderror"
                                                value="{{ old('valoresMonedas.' . $moneda->id, $valoresGuardados[$moneda->id] ?? '') }}">
                                        </div>
                                    @empty
                                        <div class="col-12">
                                            <div class="alert alert-warning">No hay monedas habilitadas.</div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            {{-- SECCIÓN: RESTRICCIONES POR CATEGORÍA --}}
                            @if($actividad->restriccion_por_categoria == true)
                            <div class="col-12">
                                <hr>
                                <h5 class="mb-3 fw-semibold">Restricciones por categoría</h5>
                                <div class="row g-4">

                                    {{-- Género --}}
                                    <div class="col-lg-6 col-sm-12">
                                        <label class="form-label" for="genero">Género</label>
                                        <select required name="genero" id="genero" class="select2 form-select">
                                            <option value="3" @selected(old('genero', $categoria->genero ?? 3) == 3)>Ambos</option>
                                            <option value="1" @selected(old('genero', $categoria->genero) == 1)>Hombres</option>
                                            <option value="2" @selected(old('genero', $categoria->genero) == 2)>Mujeres</option>
                                        </select>
                                        @error('genero')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Vinculación a grupo --}}
                                    <div class="col-lg-6 col-sm-12">
                                        <label class="form-label" for="vinculacion_grupo">Vinculación a grupo</label>
                                        <select required name="vinculacion_grupo" id="vinculacion_grupo" class="select2 form-select">
                                            <option value="3" @selected(old('vinculacion_grupo', $categoria->vinculacion_grupo ?? 3) == 3)>Ambos</option>
                                            <option value="1" @selected(old('vinculacion_grupo', $categoria->vinculacion_grupo) == 1)>Pertenece a grupo</option>
                                            <option value="2" @selected(old('vinculacion_grupo', $categoria->vinculacion_grupo) == 2)>No pertenece</option>
                                        </select>
                                        @error('vinculacion_grupo')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Actividad en grupo --}}
                                    <div class="col-lg-6 col-sm-12">
                                        <label class="form-label" for="actividad_grupo">Actividad en grupo</label>
                                        <select required name="actividad_grupo" id="actividad_grupo" class="select2 form-select">
                                            <option value="3" @selected(old('actividad_grupo', $categoria->actividad_grupo ?? 3) == 3)>Ambos</option>
                                            <option value="1" @selected(old('actividad_grupo', $categoria->actividad_grupo) == 1)>Activos</option>
                                            <option value="2" @selected(old('actividad_grupo', $categoria->actividad_grupo) == 2)>Inactivos</option>
                                        </select>
                                        @error('actividad_grupo')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Sedes habilitadas --}}
                                    @php $sedesGuardadas = $categoria->sedes->pluck('id')->toArray(); @endphp
                                    <div class="col-lg-6 col-sm-12">
                                        <label class="form-label" for="sedes">Sedes habilitadas</label>
                                        <select name="sedes[]" id="sedes" class="select2 form-select" multiple>
                                            @foreach ($sedes as $sede)
                                            <option value="{{ $sede->id }}" @selected(in_array($sede->id, old('sedes', $sedesGuardadas)))>
                                                {{ $sede->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @error('sedes')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Rangos de edad --}}
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

                                    {{-- Tipos de usuario --}}
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

                                    {{-- Estados civiles --}}
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

                                    {{-- Tipos de servicios --}}
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

                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="w-100 fixed-bottom py-3 px-4 border-top bg-body">
                        <div class="col-12 col-lg-10 col-xl-8 offset-lg-2 ">
                            <a href="{{ route('actividades.categoriasEscuelas', $actividad) }}"
                                class="btn btn-outline-secondary me-3 rounded-pill">Cancelar</a>
                            <button style="float: right;" type="submit" class="btn btn-primary rounded-pill">Guardar
                                </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
