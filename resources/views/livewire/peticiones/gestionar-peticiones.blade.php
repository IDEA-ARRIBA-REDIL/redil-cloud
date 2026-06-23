<div>
  <!-- Se ha consolidado en el modal Responder -->

  <!-- Offcanvas Responder -->
  <div wire:ignore.self class="offcanvas offcanvas-end h-100 d-flex flex-column" tabindex="-1" id="modalResponder" aria-labelledby="offcanvasResponderLabel" style="width: 450px; max-width: 100%;">
    <div class="offcanvas-header pb-2 d-flex justify-content-center flex-column position-relative flex-shrink-0">
      <button type="button" class="btn-close text-reset position-absolute top-0 end-0 m-3" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      <h6 id="offcanvasResponderLabel" class="offcanvas-title mb-0">{!! $titulo !!}</h6>
    </div>
    <div class="offcanvas-body mx-0 pt-0 overflow-y-auto flex-grow-1">
      <form id="formResponder" wire:submit="addResponder" class="pt-0 row g-3">

        <div class="col-12">
          <!-- Observacion -->
          <div wire:ignore class="col-12 mt-2">
            <div id="editorResponder" style="height: 200px;"></div>
          </div>
          <!--/Observacion-->

          <div id="buscarBiblia" class="mt-2">
            <button type="button" class="btn btn-success rounded-pill waves-effect waves-light btn-sm openBible"> <i class="ti ti-book me-3"> </i> Buscar en la Biblia</button>
          </div>

          <div id="versiculosRecomendados" class="demo-inline-spacing mt-1">
            {!! $versiculosRecomendados !!}
          </div>

          <!-- Selector de estado -->
          <div class="col-12 mt-4 px-1 pb-5">
            <label class="form-label d-block mb-3 fw-semibold text-black">Estado de petición:</label>
            <div class="d-flex flex-column gap-2 ms-1">
            
            <div class="form-check mb-0">
              <input name="estadoSiguiente" class="form-check-input" type="radio" value="1" id="estado-pendiente" wire:model="estadoSiguiente" />
              <label class="form-check-label text-black ms-1" for="estado-pendiente">
                Pendiente
              </label>
            </div>

            <div class="form-check mb-0 mt-2">
              <input name="estadoSiguiente" class="form-check-input" type="radio" value="3" id="estado-resuelto" wire:model="estadoSiguiente" />
              <label class="form-check-label text-black ms-1" for="estado-resuelto">
                En proceso
              </label>
            </div>

            <div class="form-check mb-0 mt-2">
              <input name="estadoSiguiente" class="form-check-input" type="radio" value="2" id="estado-cerrado" wire:model="estadoSiguiente" />
              <label class="form-check-label text-black ms-1" for="estado-cerrado">
                Cerrado
              </label>
            </div>

          </div>
        </div>
        <!-- / Selector de estado -->

        </div>
      </form>
    </div>

    <div class="offcanvas-footer border-top p-3 text-center bg-white flex-shrink-0">
      <button type="submit" form="formResponder" class="btn btn-primary rounded-pill me-sm-3 me-1 px-5"> <i class="ti ti-device-floppy me-1"></i> Guardar</button>
      <button type="button" class="btn btn-label-secondary rounded-pill" data-bs-dismiss="offcanvas" aria-label="Close">Cancelar</button>
    </div>
  </div>
  <!--/ Offcanvas Responder  -->

  <!-- Modal buscarBiblia -->
  <div wire:ignore.self id="modalBuscarBiblia"  class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-simple   modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

          <div class="text-center mb-4">
            <h3 class="mb-2 fw-semibold"><i class="ti ti-book ti-lg me-1"></i> Buscar en la Biblia</h3>
            <p class="text-black">Busca la cita bíblica directamente seleccionando el libro, capítulo y versículos, o busca versículos por una palabra clave.</p>
          </div>
          
          <div class="row">
            <div class="mb-2 mb-2 col-12 col-md-4">
              <label class="form-label" for="select-libro">
                Libro
              </label>
              <select id="select-libro" name="select-libro" class="form-select" data-allow-clear="true">
                <option  value="">Ninguno</option>
                @foreach ($libros as $libro)
                <option  value="{{$libro->seudonimo}}" data-capitulos="{{$libro->capitulos}}">{{ucwords ($libro->nombre)}}</option>
                @endforeach
              </select>
            </div>

            <div class="mb-2 mb-2 col-3 col-md-3">
              <label class="form-label" for="select-capitulo">
                Capítulo
              </label>
              <select disabled  id="select-capitulo" name="select-capitulo" class="form-select" data-placeholder="Selecciona el libro">
                <option  value="" selected>Ninguno</option>
              </select>
            </div>

            <div class="mb-2 mb-2 col-12 col-md-4">
              <label class="form-label" for="versiculo">
                Versículo
              </label>
              <input disabled id="versiculo" name="versiculo" value="" type="text" class="form-control" placeholder="Ej. 2-10" />
            </div>

            <div class="mb-2 mb-2 col-1 my-auto">
              <button id="buscar-biblia-versiculo" class="btn btn-outline-primary px-2 px-md-3 waves-effect" type="button"><i class="ti ti-search"></i></button>
            </div>

            <div class="col-12 mb-2 mb-2">
              <label class="form-label" for="select-capitulo">
                Buscar versículos por una palabra clave
              </label>
              <div class="input-group">
                <input id="palabras-claves" name="palabras-claves" type="text" value="" class="form-control" placeholder="Ej Amor" aria-label="" aria-describedby="button-addon2">
                <button class="btn btn-outline-primary px-2 px-md-3" type="button" id="buscar-biblia-palabra-clave"><i class="ti ti-search"></i></button>
              </div>
            </div>

            <div class="col-12 border rounded-2 mt-3">
              <div id="listado-versiculos" class="row">{!! $listadoVersiculos !!}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Modal buscarBiblia  -->

  <!-- Modal Asignar Intercesor -->
  <div wire:ignore.self class="modal fade" id="modalAsignarIntercesor" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                      <h3 class="mb-2 fw-semibold"><i class="ti ti-user-check ti-lg me-1"></i> Reasignar Intercesor</h3>
                      <p class="text-black">Busca y selecciona un intercesor activo para asignarle esta petición.</p>
                    </div>
          
                    <div class="row">
                      <div class="col-12">
                        @livewire('Usuarios.usuarios-para-busqueda', [
                          'id' => 'intercesor_asignado_id',
                          'class' => 'col-12 mb-3',
                          'label' => 'Seleccionar intercesor (*)',
                          'placeholder' => 'Buscar por nombre o identificación...',
                          'queUsuariosCargar' => 'Intercesores',
                          'tipoBuscador' => 'unico',
                          'conDadosDeBaja' => 'no',
                          'modulo' => 'peticiones_asignacion',
                          'obligatorio' => true,
                        ], key('buscador-intercesor-' . ($peticionAsignarId ?? 'none')))
                      </div>
                    </div>

                    <div class="row mt-4">
                      <div class="col-12 text-center">
                        <button type="button" 
                          wire:click="asignarIntercesorConfirmado" 
                          class="btn btn-primary rounded-pill me-sm-3 me-1 px-4" 
                          {{ !$intercesorSeleccionadoId ? 'disabled' : '' }}>
                          <i class="ti ti-check me-1"></i> Confirmar asignación
                        </button>
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  <!--/ Modal Asignar Intercesor -->

</div>

@assets
@vite([
  'resources/assets/vendor/libs/quill/typography.scss',
  'resources/assets/vendor/libs/quill/editor.scss'
])

@vite([
  'resources/assets/vendor/libs/quill/quill.js'
]);
@endassets

@script
<script >

  /* editor Responder */
  editorResponder = new Quill('#editorResponder', {
    bounds: '#editorResponder',
    placeholder: 'Escribe aquí la respuesta de la persona',
    modules: {
      toolbar: [
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'header': 1 }, { 'header': 2 }],
        [{ 'color': [] }, { 'background': [] }],
        [{ 'align': [] }],
        [{ 'size': ['small', false, 'large', 'huge'] }],
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        [{ 'font': [] }],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
        [{ 'indent': '-1'}, { 'indent': '+1' }],
        ['clean']
      ]
    },
    theme: 'snow'
  });

  $wire.on('textoInicialRespuesta', () => {
    editorResponder.root.innerHTML = event.detail.textoInicial;
  });

  editorResponder.on('text-change', (delta, oldDelta, source) => {
    $wire.set('descripcionRespuesta', editorResponder.root.innerHTML);
  });
  /* fin editor responder */

  $wire.on('abrirModal', (data) => {
    let el = document.getElementById(data.nombreModal);
    if(el && el.classList.contains('offcanvas')){
      let bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(el);
      bsOffcanvas.show();
    } else {
      $('#' + data.nombreModal).modal('show');
    }
  });

  $wire.on('cerrarModal', (data) => {
    let el = document.getElementById(data.nombreModal);
    if(el && el.classList.contains('offcanvas')){
      let bsOffcanvas = bootstrap.Offcanvas.getInstance(el);
      if(bsOffcanvas) bsOffcanvas.hide();
    } else {
      $('#' + data.nombreModal).modal('hide');
    }
  });

  // Limpieza manual de backdrops huérfanos si persisten
  document.addEventListener('hidden.bs.offcanvas', function (event) {
    document.querySelectorAll('.offcanvas-backdrop').forEach(el => el.remove());
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
  });

  document.addEventListener('hidden.bs.modal', function (event) {
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
  });

  $wire.on('cargarVersiculosRecomendados', () => {
    $wire.dispatch('versiculosSegunTipoPeticion', { peticionId: event.detail.peticionId });
  });

  $(document).on('click', '.add-versiculo', function (e) {
    let verso = $(this).attr("data-verso");
    let cita =  $(this).attr("data-cita");
    editorResponder.root.innerHTML = editorResponder.root.innerHTML+'<p><i>"'+verso+'"</i> <b>('+cita+', RVR60)</b></p>';
    $wire.set('descripcionRespuesta', editorResponder.root.innerHTML);

    $('#modalBuscarBiblia').modal('hide');
  });

  $(document).on('click', '.openBible', function (e) {
    $('#modalBuscarBiblia').modal('show');
  });

  $(document).on('change', '#select-libro', function (e) {

    var capitulos= $( "#select-libro option:selected" ).attr("data-capitulos");

    $("#select-capitulo").children('option:not(:first)').remove();
    for (var i = 1; i <= capitulos; i++) {
      $("#select-capitulo").append('<option value="'+i+'">'+i+'</option>');
    }

    $("#select-capitulo").removeAttr('disabled');
    $("#versiculo").removeAttr('disabled');

  });

  $(document).on('click', '#buscar-biblia-versiculo', function (e)
  {
    let libro= $("#select-libro").val();
    let capitulo= $("#select-capitulo").val();
    let versiculo= $("#versiculo").val();
    $("#palabras-claves").val("");

    if(libro !="" && capitulo !="" && versiculo!="")
    {
      $("#listado-versiculos").html('<center>  <div class="spinner-border spinner-border-lg text-primary m-3" role="status"> <span class="visually-hidden">Loading...</span> </center>');
      $wire.dispatch('buscarBibliaCita', { libro: libro, capitulo: capitulo, versiculo: versiculo });
    }else{
      if(libro=="")
      {
        $("#select-libro").css("background", "#ffd9d9");
        setTimeout(function(){
          $("#select-libro").css("background", "#ffffff");
        }, 5000);

      }else if(capitulo=="")
      {
        $("#select-capitulo").css("background", "#ffd9d9");
        setTimeout(function(){
          $("#select-capitulo").css("background", "#ffffff");
        }, 5000);
      }else if(versiculo=="")
      {
        $("#versiculo").css("background", "#ffd9d9");
        setTimeout(function(){
          $("#versiculo").css("background", "#ffffff");
        }, 5000);
      }
    }

  });


  $(document).on('click', '#buscar-biblia-palabra-clave', function (e)
  {
    let palabrasClaves= $("#palabras-claves").val();
    $("#select-libro").val("");
    $("#select-capitulo").val("");
    $("#versiculo").val("");

    if(palabrasClaves !="")
    {
      $("#listado-versiculos").html('<center>  <div class="spinner-border spinner-border-lg text-primary m-3" role="status"> <span class="visually-hidden">Loading...</span> </center>');
      $wire.dispatch('buscarBibliaPalabraClave', { palabrasClaves: palabrasClaves });
    }else{
      $("#palabras-claves").css("background", "#ffd9d9");
        setTimeout(function(){
        $("#palabras-claves").css("background", "#ffffff");
      }, 5000);
    }

  });
</script>
@endscript
