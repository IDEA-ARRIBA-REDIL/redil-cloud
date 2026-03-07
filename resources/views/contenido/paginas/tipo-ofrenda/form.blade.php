<div class="mb-3">
  <label class="form-label">Nombre</label>
  <input type="text" name="nombre" class="form-control"
    value="{{ old('nombre', $tipoOfrenda->nombre ?? '') }}" required>
</div>

<div class="mb-3">
  <label class="form-label">Descripción</label>
  <textarea name="descripcion" class="form-control" required>{{ old('descripcion', $tipoOfrenda->descripcion ?? '') }}</textarea>
</div>

<div class="mb-3">
  <label class="form-label">Código SAP</label>
  <input type="text" name="codigo_sap" class="form-control"
    value="{{ old('codigo_sap', $tipoOfrenda->codigo_sap ?? '') }}" required>
</div>

<div class="form-check mb-2">
  <input type="hidden" name="generica" value="0">
  <input type="checkbox" class="form-check-input" name="generica" value="1"
    {{ old('generica', $tipoOfrenda->generica ?? false) ? 'checked' : '' }}>
  <label class="form-check-label">Genérica</label>
</div>

<div class="form-check mb-2">
  <input type="hidden" name="formulario_donaciones" value="0">
  <input type="checkbox" class="form-check-input" name="formulario_donaciones" value="1"
    {{ old('formulario_donaciones', $tipoOfrenda->formulario_donaciones ?? false) ? 'checked' : '' }}>
  <label class="form-check-label">Formulario Donaciones</label>
</div>

<div class="form-check mb-2">
  <input type="hidden" name="tipo_reunion" value="0">
  <input type="checkbox" class="form-check-input" name="tipo_reunion" value="1"
    {{ old('tipo_reunion', $tipoOfrenda->tipo_reunion ?? false) ? 'checked' : '' }}>
  <label class="form-check-label">Tipo Reunión</label>
</div>

<div class="form-check mb-3">
  <input type="hidden" name="ofrenda_obligatoria" value="0">
  <input type="checkbox" class="form-check-input" name="ofrenda_obligatoria" value="1"
    {{ old('ofrenda_obligatoria', $tipoOfrenda->ofrenda_obligatoria ?? false) ? 'checked' : '' }}>
  <label class="form-check-label">Ofrenda Obligatoria</label>
</div>