<div class="row">
  <div class="col-md-6 col-12 mb-3">
    <label class="form-label" for="nombre">Nombre</label>
    <input type="text" id="nombre" name="nombre" class="form-control" value="{{ old('nombre', $tipoActividad->nombre ?? '') }}" required />
  </div>
  <div class="col-md-6 col-12 mb-3">
    <label class="form-label" for="color">Color</label>
    <input type="color" id="color" name="color" class="form-control form-control-color w-25%" value="{{ old('color', $tipoActividad->color ?? '#666CE8') }}" title="Elige un color para el tipo de actividad" />
  </div>
  <div class="col-12 mb-3">
    <label class="form-label" for="descripcion">Descripción</label>
    <textarea id="descripcion" name="descripcion" class="form-control" rows="3" required>{{ old('descripcion', $tipoActividad->descripcion ?? '') }}</textarea>
  </div>
</div>

<hr class="my-4">

<h5 class="mb-3">Configuraciones y restricciones</h5>

<div class="row g-3">
  <div class="col-md-4 col-sm-6">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" id="requiere_inscripcion" name="requiere_inscripcion" {{ old('requiere_inscripcion', $tipoActividad->requiere_inscripcion ?? false) ? 'checked' : '' }}>
      <label class="form-check-label" for="requiere_inscripcion">Requiere inscripción</label>
    </div>
  </div>
  <div class="col-md-4 col-sm-6">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" id="requiere_inicio_sesion" name="requiere_inicio_sesion" {{ old('requiere_inicio_sesion', $tipoActividad->requiere_inicio_sesion ?? false) ? 'checked' : '' }}>
      <label class="form-check-label" for="requiere_inicio_sesion">Requiere inicio sesión</label>
    </div>
  </div>
  <div class="col-md-4 col-sm-6">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" id="es_gratuita" name="es_gratuita" {{ old('es_gratuita', $tipoActividad->es_gratuita ?? false) ? 'checked' : '' }}>
      <label class="form-check-label" for="es_gratuita">Es gratuita</label>
    </div>
  </div>
  <div class="col-md-4 col-sm-6">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" id="permite_abonos" name="permite_abonos" {{ old('permite_abonos', $tipoActividad->permite_abonos ?? false) ? 'checked' : '' }}>
      <label class="form-check-label" for="permite_abonos">Permite abonos</label>
    </div>
  </div>
  <div class="col-md-4 col-sm-6">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" id="tipo_escuelas" name="tipo_escuelas" {{ old('tipo_escuelas', $tipoActividad->tipo_escuelas ?? false) ? 'checked' : '' }}>
      <label class="form-check-label" for="tipo_escuelas">Tipo escuelas</label>
    </div>
  </div>
  <div class="col-md-4 col-sm-6">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" id="inscripcion_parientes" name="inscripcion_parientes" {{ old('inscripcion_parientes', $tipoActividad->inscripcion_parientes ?? false) ? 'checked' : '' }}>
      <label class="form-check-label" for="inscripcion_parientes">Inscripción parientes</label>
    </div>
  </div>
</div>

<hr class="my-4">

<h5 class="mb-3">Compras e inscripciones</h5>
<div class="row g-3">
  <div class="col-md-3 col-sm-6">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" id="unica_compra" name="unica_compra" {{ old('unica_compra', $tipoActividad->unica_compra ?? false) ? 'checked' : '' }}>
      <label class="form-check-label" for="unica_compra">Única compra</label>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" id="multiples_compras" name="multiples_compras" {{ old('multiples_compras', $tipoActividad->multiples_compras ?? false) ? 'checked' : '' }}>
      <label class="form-check-label" for="multiples_compras">Múltiples compras</label>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" id="unica_inscripcion" name="unica_inscripcion" {{ old('unica_inscripcion', $tipoActividad->unica_inscripcion ?? false) ? 'checked' : '' }}>
      <label class="form-check-label" for="unica_inscripcion">Única inscripción</label>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" id="multiples_inscripciones" name="multiples_inscripciones" {{ old('multiples_inscripciones', $tipoActividad->multiples_inscripciones ?? false) ? 'checked' : '' }}>
      <label class="form-check-label" for="multiples_inscripciones">Múltiples inscripciones</label>
    </div>
  </div>
</div>

<hr class="my-4">

<h5 class="mb-3">Restricciones de edad</h5>
<div class="row g-3">
  <div class="col-md-6 col-sm-6">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" id="aplicar_restriccion_menores" name="aplicar_restriccion_menores" {{ old('aplicar_restriccion_menores', $tipoActividad->aplicar_restriccion_menores ?? false) ? 'checked' : '' }}>
      <label class="form-check-label" for="aplicar_restriccion_menores">Aplicar restricción menores</label>
    </div>
  </div>
  <div class="col-md-6 col-sm-6">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" id="solo_menores_de_edad" name="solo_menores_de_edad" {{ old('solo_menores_de_edad', $tipoActividad->solo_menores_de_edad ?? false) ? 'checked' : '' }}>
      <label class="form-check-label" for="solo_menores_de_edad">Solo menores de edad</label>
    </div>
  </div>
</div>
