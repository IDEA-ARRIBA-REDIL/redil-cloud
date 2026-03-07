<div>
  <!-- nuevo reporte -->
  <form  id="formNuevoReporte" class="forms-sample" wire:submit.prevent="submitFormulario">
    @csrf
    <div wire:ignore.self class="modal fade" id="modalNuevoReporte" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-md modal-dialog-centered modal-simple">
        <div class="modal-content p-0">
          <div class="modal-header border-bottom d-flex justify-content-between px-5 pb-3">
            <p class="text-black fw-semibold mb-0">Crear reporte</p>
            <button type="button" class="btn btn-sm" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-x ti-sm"></i></button>
          </div>

          <div class="modal-body px-5 py-8">

            <div class="row">

              @error('errorGeneral')
                <div class="col-12">
                  <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $message }}
                  </div>
                </div>
              @enderror

              <div class="col-12 mb-3">
                <small class="text-black">¿Se realizó el grupo? </small>
              </div>

              <div class="col-6 mb-6">
                <div class="form-check custom-option custom-option-basic rounded-3 shadow-sm border">
                  <label class="form-check-label custom-option-content p-3">
                    <span class="custom-option-header m-0 pb-0">
                      <span class="h6 mb-0 d-flex align-items-center">Si</span>
                      <input wire:model.live="seRealizo" name="seRealizo" class="form-check-input" type="radio" value="si" id="siSeRealizo" />
                    </span>
                  </label>
                </div>
              </div>

              <div class="col-6 ">
                <div class="form-check custom-option custom-option-basic rounded-3 shadow-sm border">
                  <label class="form-check-label custom-option-content p-3">
                    <span class="custom-option-header m-0 pb-0">
                      <span class="h6 mb-0 d-flex align-items-center">No</span>
                      <input wire:model.live="seRealizo" name="seRealizo" class="form-check-input" type="radio" value="no" id="noSeRealizo" />
                    </span>
                  </label>
                </div>
              </div>

              <!-- tema -->
              <div id="divTema" class="col-12 mb-6 {{ $seRealizo=='no' ? 'd-none' : '' }}">
                <label class="form-label" for="tema">
                  Tema
                </label>
                <input wire:model="tema" value="{{ old('tema') }}" onkeypress="return sinComillas(event)" type="text" class="form-control  @error('tema') is-invalid @enderror"/>
                @if($errors->has('tema')) <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $errors->first('tema') }}</div> @endif
              </div>
              <!-- tema -->

              @if(!$fechaAutomatica)
              <div id="contenedorFechaReporte" class="col-12 mb-6">
                <label for="fecha" class="form-label">
                  Fecha
                </label>
                <div class="input-group input-group-merge @error('fecha') is-invalid @enderror">
                  <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                  <input
                      wire:model="fecha"
                      placeholder="YYYY-MM-DD"
                      class="form-control fecha-picker @error('fecha') is-invalid @enderror"
                      type="text" />
                </div>
                @error('fecha')
                <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div>
                @enderror
              </div>
              @endif

              <!-- motivo -->
              <div id="divMotivo" class="col-12 mb-6 {{ $seRealizo=='si' ? 'd-none' : '' }}">
                <label for="motivo" class="form-label">Motivo</label>
                <select wire:model.live="motivo" class="form-select @error('motivo') is-invalid @enderror"   >
                  <option value="" selected>Selecciona el motivo</option>
                  @foreach($motivosNoReporte as $motivo)
                  <option value="{{ $motivo->id }}">{{ $motivo->nombre }}</option>
                  @endforeach
                </select>
                @if($errors->has('motivo'))
                <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $errors->first('motivo') }}</div>
                @endif
              </div>

              @if($requiereDescripcionAdicional)
              <div class="col-12 mb-6">
                <label class="form-label" for="descripcionAdicionalMotivo">Descripción Adicional</label>
                <textarea wire:model="descripcionAdicionalMotivo" class="form-control @error('descripcionAdicionalMotivo') is-invalid @enderror" rows="3"></textarea>
                 @error('descripcionAdicionalMotivo')
                <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $message }}</div>
                @enderror
              </div>
              @endif
              <!-- motivo -->

              <div class="col-12">
                <div class="p-3 d-flex mb-3" style="color:black; font-size:12px;border: solid 2px #95CDDF;border-radius: 14px;">
                  <i class="ti ti-bulb text-secondary me-2"></i>
                  <p class="m-0"> Recuerda que al seleccionar que NO, se finalizará el reporte como “Grupo no realizado”.</p>
                </div>
              </div>

            </div>

          </div>

          <div class="modal-footer border-top p-5">
            <button type="button" data-bs-dismiss="modal" class="btn btn-sm py-2 px-4 rounded-pill btn-outline-secondary waves-effect">Cancelar</button>
            <button type="submit" class="btnGuardar btn btn-sm py-2 px-4 rounded-pill btn-primary waves-effect waves-light">Confirmar</button>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>


@script
  <script>
     let flatpickrInstance = null; // Variable para guardar la instancia de Flatpickr


    $wire.on('abrirModal', () => {

      const  diaReunionParaFiltrar = event.detail.diaReunion;
      const modalSelector = '#' + event.detail.nombreModal;

      // Selecciona el input del datepicker dentro del modal que se abre
      const inputElement = document.querySelector(modalSelector + ' .fecha-picker');

      if (inputElement) {
          // Destruye la instancia anterior de Flatpickr si existe
          // Esto es importante si el modal se reutiliza con diferentes configuraciones
          if (flatpickrInstance) {
              flatpickrInstance.destroy();
          }

          // Opciones de configuración para Flatpickr
          let flatpickrConfig = {
              dateFormat: "Y-m-d", // Formato de fecha que usa tu input
              // locale: "es", // Si quieres localizarlo a español (necesitas importar el locale de Flatpickr)
              maxDate: "today",    // Para cumplir el requisito de no fechas futuras
          };

          // Si se proporcionó un diaReunion y es válido (0-6)
          if (diaReunionParaFiltrar !== null && diaReunionParaFiltrar >= 0 && diaReunionParaFiltrar <= 6) {

              // Aquí se añade dinámicamente la propiedad 'enable' al objeto de configuración.
              flatpickrConfig.enable = [
                  function(date) {
                      // JavaScript's getDay(): 0 = Domingo, 1 = Lunes, ..., 6 = Sábado
                      return date.getDay() === diaReunionParaFiltrar;
                  }
              ];
          }

          // Inicializa Flatpickr en el elemento input
          flatpickrInstance = flatpickr(inputElement, flatpickrConfig);
      } else {
          console.warn('Elemento .fecha-picker no encontrado dentro del modal: ' + modalSelector);
      }

      $('#' + event.detail.nombreModal).modal('show');

    });
  </script>



@endscript
