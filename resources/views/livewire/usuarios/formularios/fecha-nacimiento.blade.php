<!-- fecha nacimiento -->
  <div class="mb-3 {{$class}}">
    <label for="fecha_nacimiento" class="form-label">
      {{$label}}
    </label>
    <div class="input-group input-group-merge">
      <span class="input-group-text "><i class="ti ti-calendar"></i></span>
      <input 
        x-data 
        x-init="flatpickr($el, { 
          dateFormat: 'Y-m-d', 
          disableMobile: true,
          onChange: function(selectedDates, dateStr) {
            $wire.set('fecha', dateStr);
            $wire.validarFecha();
          }
        })" 
        wire:click='bloquearBtnGuardar' 
        id="fecha_nacimiento" 
        value="{{ old($nameId, $fecha ? $fecha : $fechaDefault) }}" 
        placeholder="YYYY-MM-DD" 
        name="{{ $nameId }}" 
        class="fecha_nacimiento form-control" 
        type="text" />
    </div>

    @if($errors->has($nameId) || $mostrarError == true)
    <div wire:ignore class="text-danger ti-12px mt-2">
      <i class="ti ti-circle-x"></i> {{ $errors->first($nameId) ? $errors->first($nameId) : $msnError }}
    </div>
    @endif

  </div>
<!-- fecha nacimiento -->
