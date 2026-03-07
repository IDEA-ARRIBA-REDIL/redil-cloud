<div>
  <!-- Modal respuesta -->
  <div wire:ignore.self class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-simple ">
      <div class="modal-content">
        <div class="modal-body">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          <div class="text-center mb-4">
          <h3 class="mb-2">{!! $titulo !!}</h3>
          </div>
          <form wire:submit="addRespuesta" class="row g-3">

            <!-- Observacion -->
            <div wire:ignore>
              <div id="editorRespuesta"></div>
            </div>
            <!--/Observacion-->

            <div class="col-12 text-center">
              <button type="submit" class="btn btn-primary me-sm-3 me-1">Guardar</button>
              <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
  <!--/ Modal respuesta  -->

  <!-- Modal Seguimiento -->
  <div wire:ignore.self class="modal fade" id="modalSeguimiento" tabindex="-2" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-simple ">
      <div class="modal-content">
        <div class="modal-body">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          <div class="text-center mb-4">
          <h3 class="mb-2">{!! $titulo !!}</h3>
          </div>
          <form wire:submit="addSeguimiento" class="row g-3">

            <!-- Observacion -->
            <div wire:ignore>
              <div id="editorSeguimiento"></div>
            </div>
            <!--/Observacion-->

            <div id="buscarBiblia" class="mt-0">
              <button type="button" class="btn btn-success rounded-pill waves-effect waves-light mt-1 btn-sm openBible"> <i class="ti ti-book"> </i> Buscar en la  Biblia</button>
            </div>

            <div id="versiculosRecomendados" class="demo-inline-spacing mt-1">
              {!! $versiculosRecomendados !!}
            </div>

            <div class="col-12 text-center">
              <button type="submit" class="btn btn-primary me-sm-3 me-1">Guardar</button>
              <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!--/ Modal respuesta  -->

  <!-- Modal buscarBiblia -->
  <div wire:ignore.self id="modalBuscarBiblia"  class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-simple   modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          <div class="text-center mb-4">
            <h3 class="mb-2"><i class="ti ti-book ti-lg"></i> Buscar en la Biblia</h3>
            <p class="text-muted">Busca la cita bíblica directamente seleccionando el libro, capítulo y versículos, o busca versículos por una palabra clave.</p>
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

            <div class="mb-2 mb-2 col-12 col-md-3">
              <label class="form-label" for="versiculo">
                Versículo
              </label>
              <input disabled id="versiculo" name="versiculo" value="" type="text" class="form-control" placeholder="Ej. 2-10" />
            </div>

            <div class="mb-2 mb-2 col-2 pt-4">
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

  /* editor Respuesta */
  editorRespuesta = new Quill('#editorRespuesta', {
    bounds: '#editorRespuesta',
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

  editorRespuesta.on('text-change', (delta, oldDelta, source) => {
    $wire.set('respuesta', editorRespuesta.root.innerHTML);
  });
  /* fin editor respuesta */

  /* editor seguimiento */
  editorSeguimiento = new Quill('#editorSeguimiento', {
    bounds: '#editorSeguimiento',
    placeholder: 'Escribe aquí el seguimiento de la persona',
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

  $wire.on('textoInicialSeguimiento', () => {
    editorSeguimiento.root.innerHTML = event.detail.textoInicial;
  });

  editorSeguimiento.on('text-change', (delta, oldDelta, source) => {
    $wire.set('descripcionSeguimiento', editorSeguimiento.root.innerHTML);
  });
  /* fin editor seguimiento */

  $wire.on('abrirModal', () => {
    $('#' + event.detail.nombreModal).modal('show');
  });

  $wire.on('cerrarModal', () => {
    $('#' + event.detail.nombreModal).modal('hide');
  });

  $wire.on('cargarVersiculosRecomendados', () => {
    $wire.dispatch('versiculosSegunTipoPeticion', { peticionId: event.detail.peticionId });
  });

  $(document).on('click', '.add-versiculo', function (e) {
    let verso = $(this).attr("data-verso");
    let cita =  $(this).attr("data-cita");
    editorSeguimiento.root.innerHTML = editorSeguimiento.root.innerHTML+'<p><i>"'+verso+'"</i> <b>('+cita+', RVR60)</b></p>';
    $wire.set('descripcionSeguimiento', editorSeguimiento.root.innerHTML);

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
