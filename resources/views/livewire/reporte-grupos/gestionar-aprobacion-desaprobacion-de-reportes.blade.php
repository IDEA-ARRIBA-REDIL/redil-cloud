<div>


  <!-- revisar reporte -->
  <form  id="formRevision" class="forms-sample" wire:submit.prevent="submitFormulario">
    @csrf
    <div wire:ignore.self class="modal fade" id="modalRevisionReporte" tabindex="-1" aria-hidden="true">

      <div class="modal-dialog modal-lg modal-dialog-centered modal-simple">
        <div class="modal-content p-0">
          <div class="modal-header border-bottom d-flex justify-content-between px-5 pb-3">
            <p class="text-black fw-semibold mb-0">Revisión del reporte N° {{ $reporte ? $reporte->id : '' }}</p>
            <button type="button" class="btn btn-sm" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-x ti-sm"></i></button>
          </div>

          <div class="modal-body px-5 py-8">

            @error('errorGeneral')
            <div class="row">
              <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  {{ $message }}
                </div>
              </div>
            </div>
            @enderror

            <div class="p-3 d-flex mb-3" style="color:black; font-size:12px;border: solid 2px #95CDDF;border-radius: 14px;">
              <i class="ti ti-bulb text-secondary me-2"></i>
              <p class="m-0"> Recuerda que al seleccionar que NO, deberas indicar los motivos de la corrección.</p>
            </div>
            <div class="mb-3">
              <small class="text-black">¿Deseas aprobar el reporte? </small>
            </div>

            <div class="row">
                <div class="col-6 mb-6">
                  <div class="form-check custom-option custom-option-basic rounded-3 shadow-sm border">
                    <label class="form-check-label custom-option-content p-3">
                      <span class="custom-option-header m-0 pb-0">
                        <span class="h6 mb-0 d-flex align-items-center">Si, aprobarlo</span>
                        <input wire:model.live="seAprobo" name="seAprobo" class="form-check-input" type="radio" value="si" id="reporteAprobado" />
                      </span>
                    </label>
                  </div>
                </div>

                <div class="col-6 ">
                  <div class="form-check custom-option custom-option-basic rounded-3 shadow-sm border">
                    <label class="form-check-label custom-option-content p-3">
                      <span class="custom-option-header m-0 pb-0">
                        <span class="h6 mb-0 d-flex align-items-center">No, corregirlo</span>
                        <input wire:model.live="seAprobo" name="seAprobo" class="form-check-input" type="radio" value="no" id="reporteCorregido" />
                      </span>
                    </label>
                  </div>
                </div>

              <!-- motivo -->
              <div id="divMotivo" class="col-12 mb-3 {{ $seAprobo=='si' ? 'd-none' : '' }}">
                  <label for="motivo" class="form-label">Motivo</label>
                  <select wire:model="motivo" class="form-select @error('motivo') is-invalid @enderror"   >
                    <option value="" selected>Selecciona el motivo</option>
                    @foreach($motivos as $motivo)
                    <option value="{{ $motivo->id }}">{{ $motivo->nombre }}</option>
                    @endforeach
                  </select>
                  @if($errors->has('motivo'))
                  <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $errors->first('motivo') }}</div>
                  @endif
                </div>
                <!-- motivo -->

                <!-- descripcion -->
                <div class="col-12 mb-6 {{ $seAprobo=='si' ? 'd-none' : '' }}">
                  <label class="form-label" for="campo_opcional1">
                    Descripcion
                  </label>
                  <textarea onkeypress="return sinComillas(event)" wire:model="descripcion" class="form-control" rows="2" spellcheck="false" data-ms-editor="true" placeholder="">{{ $descripcion }}</textarea>

                </div>
                <!-- /descripcion -->

            </div>



            <div class="row">
              @if($reporte)
              <p class="col-12 fw-semibold text-black mb-2"> Ofrendas genericas </p>
              <div class="col-12">
                <div class="row mb-4">
                  <div class=" {{ $seAprobo=='si' ? 'col-6' : 'col-4' }}">
                    <small class="text-black">Tipo</small>
                  </div>
                  <div class="col-3 {{ $seAprobo=='si' ? 'col-6' : 'col-3' }}">
                    <small class="text-black">Digitado por el encargado</small>
                  </div>

                  <div class="col-1 {{ $seAprobo=='si' ? 'd-none' : '' }}">
                    <small class="text-black"></small>
                  </div>
                  <div class="col-4 {{ $seAprobo=='si' ? 'd-none' : '' }}">
                    <small class="text-black">Valor real</small>
                  </div>
                </div>
              </div>

              @foreach ($ofrendasGenericas as $ofrenda)
              <div class="col-12">
                <div class="row">
                  <div class="{{ $seAprobo=='si' ? 'col-6' : 'col-4' }} my-auto">
                    <small class="text-black">{{ $ofrenda['nombre'] }}</small>
                  </div>
                  <div class="{{ $seAprobo=='si' ? 'col-6' : 'col-3' }} my-auto">
                   <small class="col-12 text-black">$ {{ $ofrenda['valor'] }} {{ $moneda->nombre_corto }}</small>
                  </div>

                  <div class="col-1 my-auto {{ $seAprobo=='si' ? 'd-none' : '' }}">
                  <button type="button" class="btn btn-sm btn-outline-primary waves-effect" wire:click="copiarValorGenerico({{ $ofrenda['tipo_ofrenda_id'] }}, '{{ $ofrenda['valor'] }}')"> = </button>
                  </div>

                  <div class="col-4 my-auto d-flex flex-row {{ $seAprobo=='si' ? 'd-none' : '' }}">
                    <input id="ofrendaGenerica-{{ $ofrenda['tipo_ofrenda_id'] }}"
                      type="number" min="0" step="0.01"
                      class="col-6  form-control"
                      name="{{ $ofrenda['nombre'] }}"
                      value="{{ old('ofrendaGenerica-'.$ofrenda['tipo_ofrenda_id'], $inputsOfrendasGenericas[$ofrenda['tipo_ofrenda_id']]['valor']) }}"
                      wire:model.live="inputsOfrendasGenericas.{{ $ofrenda['tipo_ofrenda_id'] }}.valor"/>
                  </div>
                </div>
                <hr class="my-3 border-2">
              </div>
              @endforeach

              @foreach ($ofrendasEspecificas as $ofrenda)
              <div class="col-12">
                <div class="row">
                  <div class="{{ $seAprobo=='si' ? 'col-6' : 'col-4' }} my-auto">
                    <small class="text-black">{{ $ofrenda['nombre'] }}</small>
                    <small class="text-black fw-semibold">de {{ $ofrenda['dador'] }}</small>
                  </div>
                  <div class="{{ $seAprobo=='si' ? 'col-6' : 'col-3' }} my-auto">
                   <small class="col-12 text-black">$ {{ $ofrenda['valor'] }} {{ $moneda->nombre_corto }}</small>
                  </div>

                  <div class="col-1 my-auto {{ $seAprobo=='si' ? 'd-none' : '' }}">
                  <button type="button" class="btn btn-sm btn-outline-primary waves-effect" wire:click="copiarValorEspecifico({{ $ofrenda['id_ofrenda_existente'] }}, '{{ $ofrenda['valor'] }}')"> = </button>
                  </div>

                  <div class="col-4 my-auto d-flex flex-row {{ $seAprobo=='si' ? 'd-none' : '' }}">
                    <input id="ofrendaEspecifica-{{ $ofrenda['id_ofrenda_existente'] }}"
                      type="number" min="0" step="0.01"
                      class="col-6  form-control"
                      name="{{ $ofrenda['nombre'] }}"
                      value="{{ old('ofrendaEspecifica-'.$ofrenda['id_ofrenda_existente'], $inputsOfrendasEspecificas[$ofrenda['id_ofrenda_existente']]['valor']) }}"
                      wire:model.live="inputsOfrendasEspecificas.{{ $ofrenda['id_ofrenda_existente'] }}.valor"/>
                  </div>
                </div>
                <hr class="my-3 border-2">
              </div>
              @endforeach

              <div class="col-12">
                <div class="row mb-4">
                  <div class="{{ $seAprobo=='si' ? 'col-6' : 'col-4' }}">
                  </div>
                  <div class="{{ $seAprobo=='si' ? 'col-6' : 'col-4' }}">
                    <small class="fw-semibold text-black ">$ {{ $reporte->totalOfrendas() }} {{ $moneda->nombre_corto }}</small>
                  </div>
                  <div class="col-4 {{ $seAprobo=='si' ? 'd-none' : '' }}">
                    <small class="fw-semibold text-black">$ {{ number_format($this->totalValorReal, 2) }} {{ $moneda->nombre_corto }}</small>
                  </div>
                </div>
              </div>

              @endif
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


@assets
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endassets

@script
  <script>
    $wire.on('abrirModal', () => {
      $('#' + event.detail.nombreModal).modal('show');
    });

    $wire.on('cerrarModal', () => {
      $('#' + event.detail.nombreModal).modal('hide');
    });

    window.addEventListener('msn', event => {
      Swal.fire({
        title: event.detail.msnTitulo,
        html: event.detail.msnTexto,
        icon: event.detail.msnIcono,
        customClass: {
          confirmButton: 'btn btn-primary'
        },
        buttonsStyling: false
      });
    });
  </script>
@endscript
