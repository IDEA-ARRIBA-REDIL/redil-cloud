@extends('layouts/blankLayout')

@section('title', 'Crear Categoría de Escuela')

{{-- Estilos y Scripts --}}
@section('vendor-style')
    <style>
        .minus {
            border: solid 2px #1977E5 !important;
            border-radius: 20px;
            padding: 2px !impotant;
            width: 31px;
            height: 30px;
            margin-right: 6px;
            color: #1977E5;
        }

        .plus {
            border: solid 2px #1977E5 !important;
            border-radius: 20px;
            padding: 2px !impotant;
            width: 31px;
            height: 30px;
            margin-left: 6px;
            color: #1977E5;
        }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
    @vite(['resources/assets/vendor/libs/select2/select2.scss'])
    {{-- NUEVO: Estilos para los botones de cantidad, como en la otra vista --}}

@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
    <script type="module">
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: 'Seleccione una opción',
                allowClear: true
            });
        });

        // ==========================================================
        // NUEVO: Script para los botones de cantidad (spinner)
        // ==========================================================
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
    <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center fixed-top">
        {{-- ... (Barra de navegación superior, sin cambios) ... --}}
        <div class="col-3 text-start">
            <a href="{{ route('actividades.categoriasEscuelas', $actividad) }}"
                class="btn rounded-pill waves-effect waves-light text-white">
                <span class="ti-xs ti ti-arrow-left me-2"></span>
                <span class="d-none d-md-block fw-normal">Volver</span>
            </a>
        </div>
        <div class="col-6 pl-5 text-center">
            <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">Nueva categoría de escuela</h5>
        </div>
        <div class="col-3 text-end">
            <a href="{{ route('actividades.categoriasEscuelas', $actividad) }}"
                class="btn rounded-pill waves-effect waves-light text-white">
                <span class="d-none d-md-block fw-normal">Salir</span>
                <span class="ti-xs ti ti-x mx-2"></span>
            </a>
        </div>
    </nav>

    <div class="container-fluid" style="padding-top: 100px; padding-bottom: 20px;">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8">
                <div class="card-header mb-3">
                    <h4 class="mb-0">Crear categoría para la escuela: <span
                            class="text-black fw-bold">{{ $actividad->nombre }}</span></h4>
                </div>

                <form action="{{ route('actividades.storeCategoriaEscuela', $actividad) }}" method="POST"
                    id="formCrearCategoriaEscuela">
                    @csrf
                    <div style="margin-bottom:80px">
                        <div class="card-header">
                            <h5 class="mb-0 fw-semibold">Información general</h5>
                        </div>
                        <div class="card-body row g-4">

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="nombre">Nombre de la categoría</label>
                                <input type="text" name="nombre" id="nombre"
                                    class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}"
                                    required />
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-12 col-md-6">
                                <label class="form-label" for="materia_periodo_id">Materia asociada</label>
                                <select name="materia_periodo_id" id="materia_periodo_id"
                                    class="select2 form-select @error('materia_periodo_id') is-invalid @enderror" required>
                                    <option value="">Seleccione una materia</option>
                                    @foreach ($materiasPeriodo as $mp)
                                        {{-- CAMBIO CLAVE: Usamos la directiva @disabled de Blade para bloquear la opción si su ID está en el array de materias usadas. --}}
                                        <option value="{{ $mp->id }}" @selected(old('materia_periodo_id') == $mp->id)
                                            @disabled(in_array($mp->id, $materiasUsadasIds))>

                                            {{ $mp->materia->nombre }}

                                            {{-- AÑADIDO: Mostramos un texto informativo si la materia ya está asignada. --}}
                                            @if (in_array($mp->id, $materiasUsadasIds))
                                                (Ya asignada)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('materia_periodo_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- El resto del formulario se mantiene igual --}}
                            <div class="col-12" x-data="{ esGratuita: {{ old('es_gratuita') ? 'true' : 'false' }} }">
                                <div class="form-check form-switch form-switch-lg mb-2">
                                    <input type="checkbox" name="es_gratuita" id="es_gratuita" class="form-check-input"
                                        value="1" x-model="esGratuita">
                                    <label class="form-check-label" for="es_gratuita">¿Es gratuita?</label>
                                </div>
                                <div x-show="!esGratuita" x-transition class="row mt-3 pt-3 border-top">
                                    <h6 class="mb-3">Valores por Moneda</h6>
                                    @forelse ($monedasActividad as $moneda)
                                        <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                            <label class="form-label">Valor en: <b>{{ $moneda->nombre }}</b></label>
                                            <input type="number" step="0.01" min="0"
                                                name="valoresMonedas[{{ $moneda->id }}]"
                                                class="form-control @error('valoresMonedas.' . $moneda->id) is-invalid @enderror"
                                                value="{{ old('valoresMonedas.' . $moneda->id) }}">
                                        </div>
                                    @empty
                                        <div class="col-12">
                                            <div class="alert alert-warning">No hay monedas habilitadas.</div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-100 fixed-bottom py-3 px-4 border-top bg-body">
                        <div class="col-12 col-lg-10 col-xl-8 offset-lg-2 ">
                            <a href="{{ route('actividades.categoriasEscuelas', $actividad) }}"
                                class="btn  btn-outline-secondary me-3  rounded-pill">Cancelar</a>
                            <button style="float: right;" type="submit" class="btn  btn-primary rounded-pill">Guardar
                                categoría</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
