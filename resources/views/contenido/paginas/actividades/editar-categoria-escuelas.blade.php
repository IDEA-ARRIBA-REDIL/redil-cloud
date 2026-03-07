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
                                        <option value="{{ $mp->id }}" {{-- 1. La opción se preselecciona si es la que ya está guardada --}}
                                            @selected(old('materia_periodo_id', $categoria->materia_periodo_id) == $mp->id) {{-- 2. La opción se deshabilita si su ID está en la lista de usadas --}} @disabled(in_array($mp->id, $materiasUsadasIds))>

                                            {{ $mp->materia->nombre }}

                                            {{-- 3. Se muestra un texto informativo si está usada por otra categoría --}}
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
                            <div class="row d-none">

                            {{-- NUEVA SECCIÓN: PASOS DE CRECIMIENTO --}}
                            @if($actividad->restriccion_por_categoria == true)
                            <div class="col-12 mt-4">
                                <hr class="my-4">
                                <h6 class="text-primary mb-3">
                                    <i class="ti ti-stairs-up me-2"></i>
                                   Pasos de Crecimiento
                                </h6>

                                <div class="mb-3">
                                    @livewire('actividad-categoria.gestionar-pasos-requisito', ['categoria' => $categoria])
                                </div>

                                <div class="mb-3">
                                    @livewire('actividad-categoria.gestionar-pasos-culminados', ['categoria' => $categoria])
                                </div>
                            </div>
                            @endif

                            {{-- NUEVA SECCIÓN: TAREAS DE CONSOLIDACIÓN --}}
                            @if($actividad->restriccion_por_categoria == true)
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
                            @endif
                            {{-- FIN SECCIÓN: TAREAS DE CONSOLIDACIÓN --}}
                            </div>
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
