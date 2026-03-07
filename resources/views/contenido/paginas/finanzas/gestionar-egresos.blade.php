@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Iglesia')

<!-- Page -->
@section('page-style')
@vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.scss'])
@endsection

@section('vendor-script')
@vite(['resources/js/app.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('page-script')

<script type="module">
  $(document).ready(function() {
    $('.select2').select2({
      width: '100px',
      allowClear: true,
      placeholder: 'Ninguno'
    });

    $(".fecha-picker").flatpickr({
      dateFormat: "Y-m-d"
    });

    $('#btn-limpiar').on('click', function() {
      window.location.href = "{{ route('finanzas.limpiarFiltrosEgresos') }}";
    });
  });

  document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('input', () => {
      if (input.value < 0) input.value = 0;
    });
  });

  $('#tablaEgresos').DataTable({
    // --- Opciones Recomendadas ---
    // Opción 1: Controles básicos (Selector de longitud, Tabla, Info, Paginación)
    // dom: 'lrtip', // 'l'ength, 'r'processing, 't'able, 'i'nfo, 'p'agination
    // Opción 2: Estructura común con Bootstrap 5 (más control de layout)
    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
      '<"row dt-row"<"col-sm-12"tr>>' +
      '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
    // Opción 3: Solo Tabla y Paginación (como lo tenías, pero con pageLength corregido)
    // dom: 'tp',

    paging: true, // Habilitar paginación
    pageLength: 15, // O 25, 50, etc. ¡Cambia este valor!
    // displayStart: 0, // No suele ser necesario especificarlo, por defecto es 0

    // --- Habilita/Deshabilita según necesites ---
    searching: false, // Habilita la búsqueda global (si usas la opción dom con 'f')
    // Si solo quieres tus filtros de formulario, déjalo en false y usa un 'dom' sin 'f'
    info: true, // Muestra la información "Mostrando X a Y de Z registros" (si usas 'dom' con 'i')
    ordering: true, // Permite ordenar por columnas

    // --- Idioma ---
    language: {
      url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
    },

    // --- Considera añadir Responsive si tu tabla tiene muchas columnas ---
    // responsive: true, // Necesitarías incluir el JS/CSS de DataTables Responsive

    // --- Otras opciones si las necesitas ---
    // lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, "Todos"] ], // Si usas 'dom' con 'l'
  });
</script>

<script>
  $('#btn-limpiar').on('click', function() {
    window.location.href = "{{ route('finanzas.limpiarFiltrosEgresos') }}";
  });

  // --- Lógica del Modal (CORREGIDO) ---
  // Función para mostrar el modal
  function mostrarModalAnulacion(egresoId) {
    // ID del input corregido
    document.getElementById('egresoIdInput').value = egresoId;
    document.getElementById('justificacion').value = '';
    let modal = new bootstrap.Modal(document.getElementById('modalAnularEgreso'));
    modal.show();
  }

  // Evento click delegado en la tabla de egresos
  $('#tablaEgresos').on('click', '.btn-anular', function() {
    const egresoId = $(this).data('egreso-id');
    mostrarModalAnulacion(egresoId);
  });
</script>

<style>
</style>
@endsection

@section('content')

  <h4 class=" mb-1 fw-semibold text-primary">Gestionar egresos</h4>

  @include('layouts.status-msn')

  <form role="form" method="GET" id="formulario" class="forms-sample" enctype="multipart/form-data" action="{{ route('finanzas.gestionarEgresos') }}">
      @csrf

      <div class="row">
        <div class="col-4 mb-3">
          <label class="form-label">Fecha inicio: </label>
          <input type="text" value="{{ $fechaInicio }}" name="fechaInicio" placeholder="Fecha inicio: "
            class="fecha form-control fecha-picker">
          @if ($errors->has('fechaInicio'))
          <div class="text-danger form-label">{{ $errors->first('fechaInicio') }}</div>
          @endif
        </div>
        <div class="col-4 mb-3">
          <label class="form-label">Fecha fin: </label>
          <input type="text" value="{{ $fechaFin }}" name="fechaFin" placeholder="Fecha fin"
            class="fecha form-control fecha-picker">
          @if ($errors->has('fechaFin'))
          <div class="text-danger form-label">{{ $errors->first('fechaFin') }}</div>
          @endif
        </div>
        <div class="col-4 mb-3">
          <label class="form-label">Tipo egreso</label>
          <select id="tipoEgreso" name="tipoEgreso" class="grupoSelect select2 form-select"
            data-placeholder="Seleccione un tipo de egreso" data-allow-clear="true">
            <option value="">Seleccione el tipo de egreso</option>
            @foreach ($tipoEgresos as $tipoEgreso)
            <option value="{{ $tipoEgreso->id }}" @if ($tipoEgreso->id == $tipoEgresoId) selected @endif>
              {{ $tipoEgreso->nombre }}
            </option>
            @endforeach
          </select>
          @if ($errors->has('tipoEgreso'))
          <div class="text-danger form-label">{{ $errors->first('tipoEgreso') }}</div>
          @endif
        </div>
        <div class="col-4 mb-3">
          <label class="form-label">Proveedor</label>
          <select id="proveedor" name="proveedor" class="grupoSelect select2 form-select"
            data-placeholder="Seleccione un proveedor" data-allow-clear="true">
            <option value="">Seleccione un proveedor</option>
            @foreach ($proveedores as $proveedor)
            <option value="{{ $proveedor->id }}" {{ $proveedorId == $proveedor->id ? 'selected' : '' }}>
              {{ $proveedor->nombre }}
            </option>
            @endforeach
          </select>
          @if ($errors->has('proveedor'))
          <div class="text-danger form-label">{{ $errors->first('proveedor') }}</div>
          @endif
        </div>
        <div class="col-4 mb-3">
          <label class="form-label">Cajas </label>
          <select id="caja" name="caja" class="grupoSelect select2 form-select"
            data-placeholder="Seleccione una caja" data-allow-clear="true">
            <option value="">Seleccione una caja</option>
            @foreach ($cajas as $caja)
            <option value="{{ $caja->id }}" {{ $cajaFinanzasId == $caja->id ? 'selected' : '' }}>
              {{ $caja->nombre }}
            </option>
            @endforeach
          </select>
          @if ($errors->has('caja'))
          <div class="text-danger form-label">{{ $errors->first('caja') }}</div>
          @endif
        </div>
        <div class="col-4 mb-3">
          <label class="form-label">Centro de costos </label>
          <select id="centro_de_costos_egresos" name="centro_de_costos_egresos" class="grupoSelect select2 form-select"
            data-placeholder="Seleccione un centro de costos" data-allow-clear="true">
            <option value="">Seleccione un centro de costos</option>
            @foreach ($centroDeCostosEgresos as $centro)
            <option value="{{ $centro->id }}" {{ $centroDeCostosEgresosId == $centro->id ? 'selected' : '' }}>
              {{ $centro->nombre }}
            </option>
            @endforeach
          </select>
          @if ($errors->has('centro_de_costos_egresos'))
          <div class="text-danger form-label">{{ $errors->first('centro_de_costos_egresos') }}</div>
          @endif
        </div>
      </div>

      <div class="row mt-5">
        <div class="col-9 col-md-4">
            <button type="submit" class="btn btn-primary">
              <i class="ti ti-search me-2"></i>
              Buscar
            </button>
            <button type="button" id="btn-limpiar" class="btn btn-outline-secondary waves-effect">
              <i class="ti ti-eraser me-2"></i>
              Borrar filtros
            </button>
        </div>
        <div class="col-3 col-md-8 d-flex justify-content-end">
          <button type="submit" formaction="{{ route('finanzas.exportarEgresosExcel') }}" class="btn btn-outline-secondary waves-effect px-2 px-md-3"><span class="d-none d-md-block fw-semibold">Descargar excel</span><i class="ti ti-file-download ms-1"></i> </button>
        </div>
      </div>
  </form>

  <!-- Listado de egresos -->
  <div class="row equal-height-row mt-10  g-2 mt-1">
    @if(count($egresos)>0)
      @foreach($egresos as $egreso)
        <div class="col equal-height-col col-12" id="egresos-card-{{ $egreso->id }}">
            <div class="card rounded-3 shadow">
                <div class="card-header border-bottom d-flex" style="background-color:#F9F9F9!important">
                  <div class="flex-fill row">
                    <div class="col-12 col-md-4">
                      <div class="d-flex flex-row">
                        <div class="d-flex flex-column">
                          <small class="fw-normal ms-1 text-black">
                            <b>Valor:</b> {{ Number::currency($egreso->valor) }} {{ $egreso->moneda->nombre_corto ?? 'No indicado' }}
                          </small>
                          <small class="fw-normal ms-1 text-black">
                              <b>Estado:</b> {{ $egreso->anulado == false ? 'Aprobado' : 'Anulado' }}
                          </small>
                          <small class="fw-normal ms-1 text-black">
                            <b>Proveedor:</b> {{ $egreso->proveedor->nombre }}
                          </small>
                        </div>
                      </div>
                    </div>

                    <div class="col-12 col-md-4">
                      <div class="d-flex flex-row">
                        <div class="d-flex flex-column">
                          <small class="fw-normal ms-1 text-black">
                            <b>Fecha:</b> {{ $egreso->fecha }}
                          </small>

                          <small class="fw-normal ms-1 text-black">
                              <b>Tipo:</b> {{ $egreso->tipoEgreso->nombre }}
                          </small>

                          <small class="fw-normal ms-1 text-black">
                            <b>Caja:</b> {{ $egreso->cajaFinanzas->nombre }}
                          </small>

                        </div>
                      </div>
                    </div>

                    <div class="col-12 col-md-4">
                      <div class="d-flex flex-row">
                        <div class="d-flex flex-column">

                          <small class="fw-normal ms-1 text-black">
                            <b>Centro de costo:</b> {{ $egreso->centroDeCostosEgresos->nombre ?? 'No indicado' }}
                          </small>

                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="">
                    <div class="ms-auto">
                      <div class="dropdown zindex-2 p-1 float-end">
                        <button type="button" class="btn btn-sm rounded-pill btn-icon btn-outline-secondary waves-effect"  data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i> </button>
                        <ul class="dropdown-menu dropdown-menu-end">

                          @if ($egreso->anulado == false)
                          <li><a href="{{ route('finanzas.imprimirEgreso', $egreso) }}" target="_blank" class="dropdown-item">Imprimir comprobante</a></li>
                          <hr class="dropdown-divider">
                          <li><a class="dropdown-item text-danger" href="javascript:;" onclick="mostrarModalAnulacion({{ $egreso->id }})">Anular</a></li>
                          @else
                          <li><a href="javascript:;"  class="dropdown-item" disabled>Imprimir comprobante</a></li>
                          <hr class="dropdown-divider">
                          <li><a class="dropdown-item text-danger" href="javascript:;"  disabled>Anular</a></li>
                          @endif

                        </ul>
                      </div>
                    </div>
                  </div>

                </div>

            </div>
        </div>
      @endforeach
    @else
    <div class="mt-5 mb-5 py-5">
      <center>
      <i class="ti ti-user ti-xl"></i>
      <p>La busqueda no arrojo ningun resultado.</p>
      </center>
    </div>
    @endif
  </div>
  <!--/ Listado de ingresos -->

  <div class="row my-3">
    @if($egresos)
    <p> {{$egresos->lastItem()}} <b>de</b> {{$egresos->total()}} <b>personas - Página</b> {{ $egresos->currentPage() }} </p>
    {!! $egresos->appends(request()->input())->links() !!}
    @endif
  </div>

  <!-- Modal para anulación -->
  <div class="modal fade" id="modalAnularEgreso" tabindex="-1" aria-labelledby="modalAnularLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form id="formAnularIngreso" method="POST" action="{{ route('finanzas.anularEgreso') }}">
        @csrf
        <input type="hidden" name="egreso_id" id="egresoIdInput">

        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalAnularLabel">Anular egreso</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <label for="justificacion">Motivo de anulación:</label>
            <textarea name="justificacion" id="justificacion" class="form-control" rows="4" required></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Anular</button>
          </div>
        </div>
      </form>
    </div>
  </div>

@endsection
