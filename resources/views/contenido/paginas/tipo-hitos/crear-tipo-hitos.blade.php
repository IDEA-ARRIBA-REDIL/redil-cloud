@extends('layouts.layoutMaster')

@section('title', 'Nuevo Tipo de Hito')

@section('vendor-style')
@vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
])
@endsection

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark d-flex align-items-center">
                <i class="ti ti-trophy me-2 text-primary"></i> Nuevo Tipo de Hito
            </h4>
            <p class="text-muted mb-0">Define un nuevo tipo de hito y configura sus capacidades operativas.</p>
        </div>
        <a href="{{ route('tipo-hitos.listarTipoHitos') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Volver al Listado
        </a>
    </div>

    @include('layouts.status-msn')

    <form id="formTipoHito" action="{{ route('tipo-hitos.crearTipoHitos') }}" method="POST">
        @csrf

        <div class="row">
            {{-- Columna: Información Básica --}}
            <div class="col-12 col-lg-8">
                <div class="card mb-4 border shadow-sm">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="card-title fw-bold mb-0 text-primary">
                            <i class="ti ti-info-circle me-1 text-primary"></i> Información General
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label fw-semibold text-dark">Nombre del Tipo <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror"
                                       value="{{ old('nombre') }}" placeholder="Ej: Reconocimiento Ministerial" required maxlength="100">
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="slug" class="form-label fw-semibold text-dark">Slug / Identificador</label>
                                <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror"
                                       value="{{ old('slug') }}" placeholder="Auto-generado si se deja vacío">
                                <small class="text-muted">Identificador único en minúsculas (ej: <code>reconocimiento-ministerial</code>).</small>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="icono" class="form-label fw-semibold text-dark">Clase de Icono <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i id="previewIcon" class="{{ old('icono', 'ti ti-award') }}"></i></span>
                                    <input type="text" name="icono" id="icono" class="form-control @error('icono') is-invalid @enderror"
                                           value="{{ old('icono', 'ti ti-award') }}" placeholder="ti ti-award" required>
                                </div>
                                <small class="text-muted">Usa clases de Tabler Icons (ej: <code>ti ti-trophy</code>, <code>ti ti-certificate</code>, <code>ti ti-flame</code>).</small>
                                @error('icono')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="color" class="form-label fw-semibold text-dark">Color Representativo <span class="text-danger">*</span></label>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" name="color" id="color" class="form-control form-control-color"
                                           value="{{ old('color', '#7c5cfc') }}" style="width: 50px; height: 38px;">
                                    <input type="text" id="colorHex" class="form-control" value="{{ old('color', '#7c5cfc') }}" maxlength="7">
                                </div>
                                <small class="text-muted">Color en formato hexadecimal.</small>
                                @error('color')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="descripcion" class="form-label fw-semibold text-dark">Descripción</label>
                                <textarea name="descripcion" id="descripcion" rows="3" class="form-control @error('descripcion') is-invalid @enderror"
                                          placeholder="Explica para qué sirve o cuándo se utiliza este tipo de hito...">{{ old('descripcion') }}</textarea>
                                @error('descripcion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Columna: Capacidades y Flags --}}
            <div class="col-12 col-lg-4">
                <div class="card mb-4 border shadow-sm">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="card-title fw-bold mb-0 text-primary">
                            <i class="ti ti-adjustments me-1 text-primary"></i> Capacidades
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex flex-column gap-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" {{ old('activo', '1') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-dark" for="activo">Tipo Activo</label>
                                <small class="d-block text-muted">Disponible para seleccionar al crear o editar hitos.</small>
                            </div>

                            <hr class="my-1">

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="requiere_trigger" name="requiere_trigger" value="1" {{ old('requiere_trigger') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-dark" for="requiere_trigger">Requiere Triggers / Disparadores</label>
                                <small class="d-block text-muted">Habilita vinculación con pasos de crecimiento, escuelas, grupos o consolidación.</small>
                            </div>

                            <hr class="my-1">

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="requiere_actividad" name="requiere_actividad" value="1" {{ old('requiere_actividad') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-dark" for="requiere_actividad">Requiere Actividad</label>
                                <small class="d-block text-muted">Habilita vinculación directa con el módulo de Actividades/Eventos.</small>
                            </div>

                            <hr class="my-1">

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="permite_fotos_usuario" name="permite_fotos_usuario" value="1" {{ old('permite_fotos_usuario', '1') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-dark" for="permite_fotos_usuario">Permitir Fotos de Usuarios</label>
                                <small class="d-block text-muted">Permite por defecto que los usuarios aporten fotos al hito.</small>
                            </div>

                            <hr class="my-1">

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="permite_likes" name="permite_likes" value="1" {{ old('permite_likes', '1') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-dark" for="permite_likes">Permitir Me Gusta (Likes)</label>
                                <small class="d-block text-muted">Permite a los usuarios interactuar dando "Me gusta" en el muro.</small>
                            </div>

                            <hr class="my-1">

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="evaluacion_dinamica" name="evaluacion_dinamica" value="1" {{ old('evaluacion_dinamica', '1') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-dark" for="evaluacion_dinamica">Evaluación Dinámica</label>
                                <small class="d-block text-muted">Calcula la pertenencia del hito al vuelo sin duplicar registros.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Botones de Acción --}}
            <div class="col-12 d-flex justify-content-between align-items-center mb-4">
                <a href="{{ route('tipo-hitos.listarTipoHitos') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="ti ti-device-floppy me-1"></i> Guardar Tipo de Hito
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputColor = document.getElementById('color');
        const inputColorHex = document.getElementById('colorHex');
        const inputIcono = document.getElementById('icono');
        const previewIcon = document.getElementById('previewIcon');

        // Sincronizar selector de color con input hex
        if (inputColor && inputColorHex) {
            inputColor.addEventListener('input', (e) => {
                inputColorHex.value = e.target.value;
            });
            inputColorHex.addEventListener('input', (e) => {
                if (/^#[0-9A-F]{6}$/i.test(e.target.value)) {
                    inputColor.value = e.target.value;
                }
            });
        }

        // Previsualizar icono en tiempo real
        if (inputIcono && previewIcon) {
            inputIcono.addEventListener('input', (e) => {
                previewIcon.className = e.target.value || 'ti ti-award';
            });
        }
    });
</script>
@endpush
