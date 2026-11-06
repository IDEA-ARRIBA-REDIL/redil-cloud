<div>
  <!-- Se ha consolidado en el modal Responder -->

  <!-- Offcanvas Responder -->
  <div wire:ignore.self class="offcanvas offcanvas-end h-100 d-flex flex-column" tabindex="-1" id="modalResponder" aria-labelledby="offcanvasResponderLabel" data-bs-backdrop="true" data-bs-scroll="false" style="width: 450px; max-width: 100%;">
    <div class="offcanvas-header pb-2 d-flex justify-content-center flex-column position-relative flex-shrink-0">
      <button type="button" class="btn-close text-reset position-absolute top-0 end-0 m-3" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      <h6 id="offcanvasResponderLabel" class="offcanvas-title mb-0">{!! $titulo !!}</h6>
    </div>
    <div class="offcanvas-body mx-0 pt-0 overflow-y-auto flex-grow-1">
      <form id="formResponder" wire:submit="addResponder" class="pt-0 row g-3">

        <div class="col-12">
          <style>
            #editorResponder.ql-container {
              height: 220px !important;
              max-height: 220px !important;
              overflow: hidden !important;
              border-bottom-left-radius: 0.375rem;
              border-bottom-right-radius: 0.375rem;
            }
            #editorResponder .ql-editor {
              height: 100% !important;
              min-height: 0 !important;
              max-height: 220px !important;
              overflow-y: auto !important;
              word-break: break-word;
            }
          </style>
          <!-- Observacion -->
          <div wire:ignore class="col-12 mt-2">
            <div id="editorResponder" style="height: 220px;"></div>
          </div>
          <!--/Observacion-->

          <div id="buscarBiblia" class="mt-3">
            <button type="button" class="btn btn-success rounded-pill waves-effect waves-light btn-sm openBible">
              <i class="ti ti-book me-1"></i> Buscar en la Biblia
            </button>
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
  <div wire:ignore.self id="modalBuscarBiblia" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-simple modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

          <div class="text-center mb-4">
            <h3 class="mb-1 fw-semibold"><i class="ti ti-book ti-lg me-1 text-primary"></i> Buscar en la Biblia</h3>
            <p class="text-black small mb-0">Consulta pasajes bíblicos o versículos por palabra clave para enriquecer tu respuesta pastoral.</p>
          </div>

          <div class="row g-2">
            <!-- Barra de Versión -->
            <div class="col-12 mb-2">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-2 bg-light rounded-3 border">
                <div class="d-flex align-items-center gap-2">
                  <label class="form-label mb-0 fw-semibold text-nowrap" for="select-version">
                    <i class="ti ti-bookmarks text-primary me-1"></i> Versión:
                  </label>
                  <select id="select-version" name="select-version" class="form-select form-select-sm w-auto">
                    @if(isset($versiones) && $versiones->isNotEmpty())
                      @foreach ($versiones as $v)
                        <option value="{{ $v->id }}" {{ $v->id == $versionSeleccionadaId ? 'selected' : '' }}>
                          {{ $v->sigla ?? $v->abreviatura }} - {{ $v->nombre }}
                        </option>
                      @endforeach
                    @else
                      <option value="">RVR1960 - Reina-Valera 1960</option>
                    @endif
                  </select>
                </div>
              </div>
            </div>

            <!-- Búsqueda por Cita Bíblica -->
            <div class="col-12 col-md-5">
              <label class="form-label fw-semibold" for="select-libro">
                Libro
              </label>
              <select id="select-libro" name="select-libro" class="form-select" data-allow-clear="true">
                <option value="">Selecciona un libro...</option>
                @if(isset($librosAt) && $librosAt->isNotEmpty())
                  <optgroup label="Antiguo Testamento">
                    @foreach ($librosAt as $l)
                      <option value="{{ $l->id }}" data-capitulos="{{ $l->capitulos }}">{{ $l->nombre }}</option>
                    @endforeach
                  </optgroup>
                @endif
                @if(isset($librosNt) && $librosNt->isNotEmpty())
                  <optgroup label="Nuevo Testamento">
                    @foreach ($librosNt as $l)
                      <option value="{{ $l->id }}" data-capitulos="{{ $l->capitulos }}">{{ $l->nombre }}</option>
                    @endforeach
                  </optgroup>
                @endif
                @if((!isset($librosAt) || $librosAt->isEmpty()) && isset($libros))
                  @foreach ($libros as $libro)
                    <option value="{{ $libro->id ?? $libro->seudonimo }}" data-capitulos="{{ $libro->capitulos }}">{{ ucwords($libro->nombre) }}</option>
                  @endforeach
                @endif
              </select>
            </div>

            <div class="col-6 col-md-3">
              <label class="form-label fw-semibold" for="select-capitulo">
                Capítulo
              </label>
              <select disabled id="select-capitulo" name="select-capitulo" class="form-select" data-placeholder="Cap...">
                <option value="" selected>Cap...</option>
              </select>
            </div>

            <div class="col-6 col-md-3">
              <label class="form-label fw-semibold" for="versiculo">
                Versículo
              </label>
              <input disabled id="versiculo" name="versiculo" value="" type="text" class="form-control" placeholder="Ej. 16 o 1-5" />
            </div>

            <div class="col-12 col-md-1 d-flex align-items-end">
              <button id="buscar-biblia-versiculo" class="btn btn-primary w-100 px-2 waves-effect" type="button" title="Buscar cita bíblica">
                <i class="ti ti-search"></i>
              </button>
            </div>

            <!-- Separador -->
            <div class="col-12 my-2">
              <div class="d-flex align-items-center">
                <hr class="flex-grow-1 my-0">
                <span class="px-3 text-black small fw-semibold">O BUSCA POR PALABRA CLAVE</span>
                <hr class="flex-grow-1 my-0">
              </div>
            </div>

            <!-- Temas frecuentes de oración -->
            <div class="col-12 mb-1">
              <div class="d-flex align-items-center flex-wrap gap-1">
                <span class="small text-black me-1"><i class="ti ti-sparkles text-warning me-1"></i>Temas frecuentes:</span>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill btn-tema-biblia" data-tema="sanidad">🩺 Sanidad</button>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill btn-tema-biblia" data-tema="paz">🕊️ Paz</button>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill btn-tema-biblia" data-tema="fortaleza">🛡️ Fortaleza</button>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill btn-tema-biblia" data-tema="consuelo">💔 Consuelo</button>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill btn-tema-biblia" data-tema="perdon">✝️ Perdón</button>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill btn-tema-biblia" data-tema="provision">💼 Provisión</button>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill btn-tema-biblia" data-tema="familia">🏠 Familia</button>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill btn-tema-biblia" data-tema="esperanza">✨ Esperanza</button>
              </div>
            </div>

            <!-- Input de Búsqueda por Palabra Clave -->
            <div class="col-12 mb-2">
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="ti ti-search text-muted"></i></span>
                <input id="palabras-claves" name="palabras-claves" type="text" value="" class="form-control" placeholder="Escribe una o más palabras (ej: amor, fe y esperanza)..." aria-label="Palabras clave">
                <button class="btn btn-primary px-3" type="button" id="buscar-biblia-palabra-clave">Buscar</button>
              </div>
            </div>

            <!-- Listado de Versículos -->
            <div class="col-12">
              <div class="border rounded-3 p-2 bg-light bg-opacity-50" style="max-height: 380px; overflow-y: auto;">
                <div id="listado-versiculos">{!! $listadoVersiculos !!}</div>
              </div>
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
                    @php
                      $cantidadAsignar = count($peticionesAsignarIds);
                    @endphp
                    <div class="text-center mb-4">
                      <h3 class="mb-2 fw-semibold">
                        <i class="ti ti-user-check ti-lg me-1"></i>
                        {{ $cantidadAsignar > 1 ? "Asignar {$cantidadAsignar} peticiones a intercesor" : "Asignar a intercesor" }}
                      </h3>
                      <p class="text-black">
                        {{ $cantidadAsignar > 1 ? "Busca y selecciona el intercesor al que deseas asignar las {$cantidadAsignar} peticiones seleccionadas." : "Busca y selecciona un intercesor activo para asignarle esta petición." }}
                      </p>
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
      let bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(el, {
        backdrop: true,
        scroll: false
      });
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

  // Manejo robusto de backdrops cuando conviven Offcanvas y Modal
  document.addEventListener('hidden.bs.offcanvas', function (event) {
    let modalAbierto = document.querySelector('.modal.show');
    if (!modalAbierto) {
      document.querySelectorAll('.offcanvas-backdrop').forEach(el => el.remove());
      document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
      document.body.classList.remove('modal-open');
      document.body.style.overflow = '';
      document.body.style.paddingRight = '';
    }
  });

  document.addEventListener('hidden.bs.modal', function (event) {
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());

    let offcanvasAbierto = document.querySelector('.offcanvas.show');
    if (offcanvasAbierto) {
      // Si el offcanvas sigue abierto al cerrar el modal, asegurar el bloqueo de scroll y el backdrop
      document.body.classList.add('modal-open');
      document.body.style.overflow = 'hidden';

      let offBackdrop = document.querySelector('.offcanvas-backdrop');
      if (!offBackdrop) {
        offBackdrop = document.createElement('div');
        offBackdrop.className = 'offcanvas-backdrop fade show';
        document.body.appendChild(offBackdrop);
        offBackdrop.addEventListener('click', function () {
          let bs = bootstrap.Offcanvas.getInstance(offcanvasAbierto);
          if (bs) bs.hide();
        });
      } else {
        offBackdrop.classList.add('show');
        offBackdrop.style.display = 'block';
      }
    } else {
      document.body.classList.remove('modal-open');
      document.body.style.overflow = '';
      document.body.style.paddingRight = '';
    }
  });

  $wire.on('cargarVersiculosRecomendados', () => {
    $wire.dispatch('versiculosSegunTipoPeticion', { peticionId: event.detail.peticionId });
  });

  $(document).on('click', '.add-versiculo', function (e) {
    let verso = $(this).attr("data-verso");
    let cita =  $(this).attr("data-cita");
    let version = $(this).attr("data-version") || 'RVR60';
    editorResponder.root.innerHTML = editorResponder.root.innerHTML+'<p><i>"'+verso+'"</i> <b>('+cita+', '+version+')</b></p>';
    $wire.set('descripcionRespuesta', editorResponder.root.innerHTML);

    $('#modalBuscarBiblia').modal('hide');
  });

  $(document).on('click', '.openBible', function (e) {
    $('#modalBuscarBiblia').modal('show');
  });

  $(document).on('change', '#select-libro', function (e) {
    var capitulos = $("#select-libro option:selected").attr("data-capitulos");

    $("#select-capitulo").children('option:not(:first)').remove();
    if (capitulos) {
      for (var i = 1; i <= capitulos; i++) {
        $("#select-capitulo").append('<option value="'+i+'">'+i+'</option>');
      }
      $("#select-capitulo").removeAttr('disabled');
      $("#versiculo").removeAttr('disabled');
    } else {
      $("#select-capitulo").attr('disabled', true);
      $("#versiculo").attr('disabled', true);
    }
  });

  $(document).on('click', '#buscar-biblia-versiculo', function (e)
  {
    let libro = $("#select-libro").val();
    let capitulo = $("#select-capitulo").val();
    let versiculo = $("#versiculo").val();
    let versionId = $("#select-version").val();
    $("#palabras-claves").val("");

    if(libro !="" && capitulo !="" && versiculo!="")
    {
      $("#listado-versiculos").html('<center><div class="spinner-border spinner-border-lg text-primary m-3" role="status"><span class="visually-hidden">Buscando...</span></div></center>');
      $wire.dispatch('buscarBibliaCita', { libro: libro, capitulo: capitulo, versiculo: versiculo, versionId: versionId });
    } else {
      if(libro=="")
      {
        $("#select-libro").css("background", "#ffd9d9");
        setTimeout(function(){
          $("#select-libro").css("background", "#ffffff");
        }, 3000);
      } else if(capitulo=="")
      {
        $("#select-capitulo").css("background", "#ffd9d9");
        setTimeout(function(){
          $("#select-capitulo").css("background", "#ffffff");
        }, 3000);
      } else if(versiculo=="")
      {
        $("#versiculo").css("background", "#ffd9d9");
        setTimeout(function(){
          $("#versiculo").css("background", "#ffffff");
        }, 3000);
      }
    }
  });

  $(document).on('click', '#buscar-biblia-palabra-clave', function (e)
  {
    let palabrasClaves = $("#palabras-claves").val();
    let versionId = $("#select-version").val();
    $("#select-libro").val("");
    $("#select-capitulo").val("").attr('disabled', true);
    $("#versiculo").val("").attr('disabled', true);

    if(palabrasClaves.trim() !="")
    {
      $("#listado-versiculos").html('<center><div class="spinner-border spinner-border-lg text-primary m-3" role="status"><span class="visually-hidden">Buscando...</span></div></center>');
      $wire.dispatch('buscarBibliaPalabraClave', { palabrasClaves: palabrasClaves, versionId: versionId });
    } else {
      $("#palabras-claves").css("background", "#ffd9d9");
      setTimeout(function(){
        $("#palabras-claves").css("background", "#ffffff");
      }, 3000);
    }
  });

  // Atajos de búsqueda por Temas Frecuentes
  $(document).on('click', '.btn-tema-biblia', function (e) {
    e.preventDefault();
    let tema = $(this).data('tema');
    $("#palabras-claves").val(tema);
    $("#buscar-biblia-palabra-clave").trigger('click');
  });

  // Búsqueda con tecla Enter
  $(document).on('keypress', '#palabras-claves', function (e) {
    if (e.which === 13) {
      e.preventDefault();
      $("#buscar-biblia-palabra-clave").trigger('click');
    }
  });

  $(document).on('keypress', '#versiculo', function (e) {
    if (e.which === 13) {
      e.preventDefault();
      $("#buscar-biblia-versiculo").trigger('click');
    }
  });

  // Al cambiar de versión bíblica, re-ejecutar la búsqueda si ya había una activa
  $(document).on('change', '#select-version', function () {
    let palabras = $("#palabras-claves").val();
    let libro = $("#select-libro").val();
    let capitulo = $("#select-capitulo").val();
    let versiculo = $("#versiculo").val();

    if (palabras && palabras.trim() !== '') {
      $("#buscar-biblia-palabra-clave").trigger('click');
    } else if (libro !== '' && capitulo !== '' && versiculo !== '') {
      $("#buscar-biblia-versiculo").trigger('click');
    }
  });
</script>
@endscript
