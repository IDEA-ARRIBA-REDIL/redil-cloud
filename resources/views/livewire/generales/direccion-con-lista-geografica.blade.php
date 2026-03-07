
<div class="mb-2 {{$classDireccion}}">
    <label class="form-label" for="direccion">
      {{ $labelDireccion ? $labelDireccion : 'Dirección' }}
    </label>
    <div class="input-group input-group-merge">
      <span class="input-group-text"><i class="ti ti-map"></i></span>
      <input readonly  id="direccion" name="dirección" type="text" value="{{ old('dirección',$direccion) }}" class="form-control bg-secondary" style="background-color: #eceef1!important" spellcheck="false" data-ms-editor="true" placeholder=" Digita la dirección, la ciudad y el país, donde vives.">
      <button wire:click="btnEliminar" class="btn btn-outline-danger waves-effect {{ $direccion ? '' : 'd-none' }}" type="button" data-bs-toggle="modal" data-bs-target="#modalListaGeografica">Eliminar</button>
      <button class="btn btn-outline-primary waves-effect {{ $direccion ? 'd-none' : '' }}" type="button" data-bs-toggle="modal" data-bs-target="#modalListaGeografica">Agregar</button>
    </div>
    @if($errors->has('dirección')) <div class="text-danger form-label">{{ $errors->first('dirección') }}</div> @endif

    <input value="{{ old('barrio_id',$barrioSelect !='otro' ? $barrioSelect : '') }}"  id="barrio_id" name="barrio_id"  type="text" class="form-control d-none">
    <input value="{{ old('barrio_auxiliar',$otroBarrio) }}"  id="barrio_auxiliar" name="barrio_auxiliar"   type="text" class="form-control d-none ">

    <!-- modal lista geografica-->
    <div wire:ignore.self class="modal fade" id="modalListaGeografica" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-simple modal-edit-user">
        <div class="modal-content">
          <div class="modal-body">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="text-center mb-4">
              <h3 class="mb-2"><i class="ti ti-map ti-lg"></i> Construye la ubicación de tu casa</h3>
            </div>

            <div class="row">

              <!-- pais -->
              <div class="mb-2 col-12 col-md-4">
                <label class="form-label" for="pais">
                  Pais
                </label>
                <select wire:model.live="paisSelect"  id="paisSelect" name="pais" class="selectGeografico form-select" {{ $paises->count() > 0 ? '' : 'disabled'}} >
                  <option  value="">Ninguno</option>
                  @foreach ($paises as $pais)
                  <option value="{{$pais->id}}">{{ucwords ($pais->nombre)}}</option>
                  @endforeach
                </select>
              </div>
              <!-- /pais -->

              <!-- Ciudad -->
              <div class="mb-2 col-12 col-md-4">
                  <label class="form-label" for="ciudad">
                    Ciudad
                  </label>
                  <select wire:model.live="ciudadSelect"  id="ciudadSelect" name="ciudad" class="selectGeografico form-select" {{ $ciudades->count() > 0 ? '' : 'disabled'}} >
                    <option  value="">Ninguno</option>
                    @foreach ($ciudades as $ciudad)
                    <option value="{{$ciudad->id}}">{{ucwords ($ciudad->nombre)}}</option>
                    @endforeach
                  </select>
                </div>
              <!-- /Ciudad -->

              <!-- barrio -->
              <div class="mb-2 col-12 col-md-4">
                <label class="form-label" for="ciudad">
                  Barrio
                </label>
                <select wire:model.live="barrioSelect"  id="barrioSelect" name="barrio" class="selectGeografico form-select" {{ $ciudadSelect ? '' : 'disabled'}} >
                  <option  value="">Ninguno</option>
                  <option  value="otro">Otro barrio</option>
                  @foreach ($barrios as $barrio)
                  <option value="{{$barrio->id}}">{{ucwords ($barrio->nombre)}}</option>
                  @endforeach
                </select>
              </div>
              <!-- /barrio -->

              <!-- otro barrio -->
              <div class="mb-2 col-12 col-md-4 {{ $barrioSelect=='otro' ? '' : 'd-none'}}">
                <label class="form-label">
                  Otro barrio
                </label>
                <input wire:model="otroBarrio" type="text" class="form-control" />
              </div>
              <!-- /otro barrio -->

              <div class="mb-2 mt-2 col-12">
                <p class="text-muted text-center">LLena los campos siguiendo el ejemplo. <br>
                  <b>Ejemplo:</b> Calle 23 Diagonal N° 23-43 Apartamento 302 bloque 7
                  </p>
              </div>

              <!--  direccion parte 1 -->
              <div class="mb-2 col-12 col-md-2">
                <label class="form-label" for="ciudad">
                  <b>Calle</b>
                </label>
                <select wire:model="direccionParte1"  id="tipoDireccion1" class="selectGeografico form-select">
                  <option value="">Ninguno</option>
                  @foreach ($tiposFormatoDireccion as $tipo)
                  <option value="{{ucwords ($tipo->nombre)}}">{{ucwords ($tipo->nombre)}}</option>
                  @endforeach
                </select>
              </div>
              <!-- / direccion parte 1-->

               <!-- direccion parte 2 -->
               <div class="mb-2 col-12 col-md-2">
                <label class="form-label">
                  <b>23 Diagonal</b>
                </label>
                <input wire:model="direccionParte2" type="text" class="form-control" />
              </div>
              <!-- /direccion parte 2 -->

              <div class="mb-2 col-12 col-md-2">
                <p class="text-center"><b>N°</b></p>
              </div>

              <!-- direccion parte 3 -->
              <div class="mb-2 col-12 col-md-2">
                <label class="form-label">
                  <b>44-62</b>
                </label>
                <input wire:model="direccionParte3" type="text" class="form-control" />
              </div>
              <!-- /direccion parte 3 -->

              <!-- direccion parte 4 -->
              <div class="mb-2 col-12 col-md-2">
                <label class="form-label" for="ciudad">
                  <b>Apartamento</b>
                </label>
                <select wire:model="direccionParte4"  id="tipoDireccion2" class="selectGeografico form-select">
                  <option value="">Ninguno</option>
                  @foreach ($tiposFormatoDireccion as $tipo)
                  <option value="{{ucwords ($tipo->nombre)}}">{{ucwords ($tipo->nombre)}}</option>
                  @endforeach
                </select>
              </div>
              <!-- /direccion parte 4 -->

              <!-- direccion parte 5 -->
              <div class="mb-2 col-12 col-md-2">
                <label class="form-label">
                  <b>302 Bloque 7</b>
                </label>
                <input wire:model="direccionParte5" type="text" class="form-control" />
              </div>
              <!-- /direccion parte 5 -->
            </div>
          </div>

          <div class="modal-footer text-center">
            <div class="col-12 text-center">
              <button wire:click="addDireccion" type="button" class="btn btn-primary me-sm-3 me-1" data-bs-dismiss="modal">Guardar</button>
              <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--/ modal lista geografica -->

  </div>


  @script
    <script>
      $(document).ready(function() {
        /*$('.selectGeografico').select2({
          dropdownParent: $('#modalListaGeografica')
        });*/

        $(document).on('change','#paisSelect', function()
        {
          let data = $('#paisSelect').select2("val");
          $wire.set('paisSelect', data);
        });

        $(document).on('change','#ciudadSelect', function()
        {
          let data = $('#ciudadSelect').select2("val");
          $wire.set('ciudadSelect', data);
        });

        $(document).on('change','#barrioSelect', function()
        {
          let data = $('#barrioSelect').select2("val");
          $wire.set('barrioSelect', data);
        });

        $(document).on('change','#tipoDireccion1', function()
        {
          let data = $('#tipoDireccion1').select2("val");
          $wire.set('direccionParte1', data);
        });

        $(document).on('change','#tipoDireccion2', function()
        {
          let data = $('#tipoDireccion2').select2("val");
          $wire.set('direccionParte4', data);
        });

      });

      window.addEventListener('render-select2', event => {

          /*$('.selectGeografico').select2({
            dropdownParent: $('#modalListaGeografica')
          });*/
      });

      // Eso arragle un error en los select2 con el scroll cuando esta dentro de un modal
      $('#modalListaGeografica').on('scroll', function (event) {
        $(this).find(".selectGeografico").each(function () {
          $(this).select2({ dropdownParent: $(this).parent() });
        });
      });
    </script>
  @endscript
