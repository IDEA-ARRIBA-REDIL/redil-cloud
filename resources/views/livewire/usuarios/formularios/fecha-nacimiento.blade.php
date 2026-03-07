<!-- fecha nacimiento -->
  <div class="mb-3 {{$class}}">
    <label for="fecha_nacimiento" class="form-label">
      {{$label}}
    </label>
    <div class="input-group input-group-merge">
      <span class="input-group-text "><i class="ti ti-calendar"></i></span>
      <input wire:click='bloquearBtnGuardar' wire:model.debounce="fecha" wire:click.outside="validarFecha()" id="fecha_nacimiento" value="{{ old($nameId, $fechaDefault) }}" placeholder="YYYY-MM-DD" name="{{ $nameId }}" class="fecha_nacimiento form-control fecha-picker" type="text" />

    </div>

    @if($errors->has($nameId) || $mostrarError == true)
    <div wire:ignore class="text-danger ti-12px mt-2">
      <i class="ti ti-circle-x"></i> {{ $errors->first($nameId) ? $errors->first($nameId) : $msnError }}
    </div>
    @endif

  </div>
<!-- fecha nacimiento -->
