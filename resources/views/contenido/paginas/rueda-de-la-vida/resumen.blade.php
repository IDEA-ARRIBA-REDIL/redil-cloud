@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
use Carbon\Carbon;
@endphp


@extends('layouts/blankLayout')

@section('title', 'Resumen RV')

@section('vendor-style')

<style>
  .boxShadow {
    padding: 19px;
    box-shadow: 0px 3px 7px #d4d4d4;
    border-radius: 9px;
    margin-bottom: 7px;
  }

  .texto-danger {
    color: #AA1A1E !important;
  }

  .card-area-promedio {
    border-radius: 10px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
    border: 1px solid #e0e0e0;
    background: #fff;
  }

  /* ── Estilos de impresión / PDF ─────────────────────────── */
  @media print {
    @page {
      size: A4 portrait;
      margin: 18mm 15mm 18mm 15mm;
    }

    /* Ocultar elementos de navegación y acciones */
    .navbar-resumen,
    .btn-descargar-pdf,
    .waves-effect {
      display: none !important;
    }

    /* Quitar sombras y ajustar fondo */
    body, .layout-wrapper, .layout-container,
    .content-wrapper, .container-xxl {
      background: #fff !important;
    }

    .boxShadow {
      box-shadow: none !important;
      border: 1px solid #e0e0e0 !important;
      break-inside: avoid;
    }

    .card-area-promedio {
      break-inside: avoid;
    }

    /* Asegurar que el gráfico no se parta */
    #polarChartResumen {
      break-inside: avoid;
    }

    /* Padding del contenedor principal */
    .contenido-resumen {
      padding: 0 !important;
    }
  }
</style>

@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/apex-charts/apexcharts.js',
])
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
@endsection


@section('page-script')
@vite([
'resources/assets/js/form-basic-inputs.js',
])

<script type="module">
  document.addEventListener('DOMContentLoaded', function () {
    @php
      $seriesResumen = [];
      $coloresResumen = [];
      $labelsResumen = [];
      foreach ($seccionesContadorPromedios as $seccion) {
          $promedio = $seccion->promedio($rueda->id);
          $seriesResumen[] = round($promedio ?? 0, 1);
          $labelsResumen[] = $seccion->nombre_seccion;
          // Usamos el color propio de la sección (definido en secciones_rv.color)
          $coloresResumen[] = $seccion->color ?? '#6777ef';
      }
    @endphp

    const opciones = {
      series: @json($seriesResumen),
      labels: @json($labelsResumen),
      colors: @json($coloresResumen),
      chart: {
        type: 'polarArea',
        height: 340,
        toolbar: { show: false },
      },
      fill: {
        opacity: 0.85,
        colors: @json($coloresResumen),
      },
      stroke: {
        colors: ['#fff'],
        width: 1,
      },
      yaxis: {
        min: 0,
        max: 10,
        tickAmount: 5,
        labels: { show: false },
      },
      plotOptions: {
        polarArea: {
          rings: { strokeWidth: 1 },
          spokes: { strokeWidth: 1 },
        },
      },
      dataLabels: {
        enabled: true,
        formatter: function (val, opts) {
          // val es el porcentaje; usamos el valor real de la serie
          return opts.w.config.series[opts.seriesIndex].toFixed(1);
        },
        style: {
          fontSize: '13px',
          fontWeight: '600',
        },
      },
      tooltip: {
        y: {
          formatter: function (val) {
            return val.toFixed(1) + ' / 10';
          },
        },
      },
      legend: {
        position: 'bottom',
        fontSize: '13px',
      },
      responsive: [{
        breakpoint: 768,
        options: {
          chart: { height: 300 },
          legend: { position: 'bottom' },
        },
      }],
    };

    const grafico = new ApexCharts(document.querySelector('#polarChartResumen'), opciones);
    grafico.render();
  });
</script>

<script>
  /**
   * Genera una imagen del resumen usando html2canvas y la descarga.
   */
  function imprimirPDF() {
    const btn = document.querySelector('.btn-descargar-pdf');
    const elemento = document.querySelector('.contenido-resumen');
    
    // Ocultar botón temporalmente para que no salga en la imagen
    btn.style.display = 'none';

    html2canvas(elemento, {
      scale: 2,
      useCORS: true,
      backgroundColor: '#ffffff'
    }).then(canvas => {
      // Restaurar el botón
      btn.style.display = '';

      // Crear enlace de descarga
      const enlace = document.createElement('a');
      enlace.download = 'resumen-rueda-vida.png';
      enlace.href = canvas.toDataURL('image/png');
      enlace.click();
    }).catch(err => {
      console.error('Error al generar la imagen', err);
      btn.style.display = '';
    });
  }
</script>

@endsection

@section('content')

<div class="col-12">
  <nav class="navbar navbar-expand-lg navbar-light bg-menu-theme p-3 row justify-content-md-center navbar-resumen mx-0">
    <div class="col-3 text-start">
      <button type="button" class="btn rounded-pill waves-effect waves-light text-white prev-step d-none">
        <span class="ti-xs ti ti-arrow-left me-2"></span>
        <span class="d-none d-md-block fw-normal">Volver</span>
      </button>
    </div>
    <div class="col-6 pl-5 text-center">
      <h5 id="tituloPrincipal" class="text-white my-auto fw-normal">{{$configuracionRv->nombre_general}} - Historial</h5>
    </div>
    <div class="col-3 text-end">
      <a href="{{ route('dashboard')}}" type="button" class="btn rounded-pill waves-effect waves-light text-white">
        <span class="d-none d-md-block fw-normal">Salir</span>
        <span class="ti-xs ti ti-x mx-2"></span>
      </a>
    </div>
  </nav>

  <div class="col-12 col-sm-8 offset-sm-2 col-lg-8 offset-lg-2 px-3 px-sm-7 contenido-resumen" style="padding-bottom: 100px;">

    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between mt-5 mb-1 gap-3">
      <h3 class="fw-semibold mb-0">{{$configuracionRv->nombre_general}}</h3>
      <button
        type="button"
        class="btn btn-outline-primary rounded-pill px-4 py-2 btn-descargar-pdf  w-sm-auto"
        onclick="imprimirPDF()">
        <i class="ti ti-download me-1"></i>
        Descargar Resumen
      </button>
    </div>

    {{-- Sección de promedios: gráfico + lista de áreas --}}
    <div class="row mt-3 mb-2">

      {{-- Gráfico polar con los promedios reales --}}
      <div style="border-color: #c7d2fe;" class="bg-white rounded col-lg-6 col-md-6 col-sm-12 d-flex align-items-center justify-content-center">
        <div id="polarChartResumen"></div>
      </div>

      {{-- Lista de áreas con promedio --}}
      <div class="col-lg-6 col-md-6 col-sm-12">
        @foreach ($seccionesContadorPromedios as $seccion)
        <div class="card-area-promedio">
          <div class="d-flex align-items-center">
            <i class="{{ $seccion->icono ?? 'ti ti-circle' }} me-2 text-dark"></i>
            <span class="fw-semibold text-dark" style="font-size: 0.9rem;">{{ $seccion->nombre_seccion }}</span>
          </div>
          <span class="fw-bold @if($seccion->promedio($rueda->id) >= $configuracionRv->promedio_general) text-success @else texto-danger @endif"
            style="font-size: 1rem;">
            {{ number_format($seccion->promedio($rueda->id), 1, ',', ' ') }}
          </span>
        </div>
        @endforeach

        {{-- Promedio general --}}
        <div class="card-area-promedio mt-2" style="background: #f0f4ff; border-color: #c7d2fe;">
          <span class="fw-bold text-dark">{{ $configuracionRv->label_promedio_general }} total</span>
          <span class="fw-bold fs-5 text-dark @if($rueda->promedio_general >= $configuracionRv->promedio_general) text-success @else texto-danger @endif">
            {{ number_format($rueda->promedio_general, 1, ',', ' ') }}
          </span>
        </div>
      </div>

    </div>

    <hr style="margin-top: 20px; margin-bottom: 30px">

    {{-- Sección de metas y hábitos del usuario --}}
    <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12">

        @if($metasUsuario->isNotEmpty())
          <h4 class="fw-semibold mb-4">Mis metas y hábitos</h4>

          @foreach($metasUsuario as $meta)
          <div class="boxShadow mb-4">
            <div class="d-flex align-items-start align-items-sm-center justify-content-between flex-column flex-sm-row gap-2 mb-3">
              <div>
                <h5 class="fw-semibold mb-0">{{ $meta->nombre }}</h5>
                @if($meta->seccion)
                  <span class="badge rounded-pill" style="font-size: 0.75rem;">
                    {{ $meta->seccion->nombre_seccion }}
                  </span>
                @endif
              </div>
              @if($meta->habitos->isNotEmpty())
              <button
                type="button"
                class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-ver-avance"
                data-meta-id="{{ $meta->id }}"
                data-meta-nombre="{{ $meta->nombre }}">
                <i class="ti ti-chart-line me-1"></i> Ver avance
              </button>
              @endif
            </div>

            @if($meta->habitos->isNotEmpty())
              <p class="text-dark mb-2 fw-semibold" style="font-size: 0.85rem;">
                {{ $configuracionRv->nombre_habitos }}:
              </p>
              <div class="d-flex flex-column gap-2">
                @foreach($meta->habitos as $habito)
                <div class="d-flex align-items-center justify-content-between p-2"
                  style="background: #f8f7fa; border-radius: 8px; border: 1px solid #e0e0e0;">
                  <div class="d-flex align-items-center">
                    <i class="ti ti-point-filled me-2 text-dark"></i>
                    <span style="font-size: 0.9rem;">{{ $habito->nombre }}</span>
                  </div>
                  <button
                    type="button"
                    class="btn btn-xs btn-outline-secondary rounded-pill btn-registrar-avance"
                    style="font-size: 0.78rem; padding: 3px 12px; white-space: nowrap;"
                    data-habito-id="{{ $habito->id }}"
                    data-habito-nombre="{{ $habito->nombre }}">
                    <i class="ti ti-plus me-1"></i> Registrar avance
                  </button>
                </div>
                @endforeach
              </div>
            @else
              <p class="text-muted" style="font-size: 0.85rem;">Sin hábitos registrados.</p>
            @endif
          </div>
          @endforeach

        @else
          <div class="text-center py-5 text-muted">
            <i class="ti ti-target ti-lg mb-2" style="font-size: 2rem;"></i>
            <p>No se registraron metas en esta rueda.</p>
          </div>
        @endif

      </div>
    </div>

  </div>
</div>

{{-- ============================================================
     Modal: Registrar Avance por Hábito Individual
     ============================================================ --}}
<div class="modal fade" id="modalRegistrarAvanceHabito" tabindex="-1"
  aria-labelledby="modalHabitoTitulo" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <div>
          <h5 class="modal-title fw-semibold" id="modalHabitoTitulo"></h5>
          <small class="text-muted" id="modalHabitoSubtitulo"></small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      {{-- Skeleton --}}
      <div id="habitoSkeleton" class="modal-body text-center py-5">
        <div class="spinner-border text-dark" role="status"></div>
        <p class="mt-2 text-muted">Cargando...</p>
      </div>

      {{-- Contenido --}}
      <div id="habitoContenido" class="d-none">
        <div class="modal-body pb-0">
          <ul class="nav nav-pills mb-3" role="tablist">
            <li class="nav-item">
              <button class="nav-link active" data-bs-toggle="pill"
                data-bs-target="#habitoTabActual" type="button">Período actual</button>
            </li>
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="pill"
                data-bs-target="#habitoTabHistorial" type="button">Historial</button>
            </li>
          </ul>
        </div>

        <div class="modal-body pt-0">
          <div class="tab-content">

            {{-- TAB: Período actual --}}
            <div class="tab-pane fade show active" id="habitoTabActual" role="tabpanel">
              <div id="habitoAvisoRegistrado" class="alert alert-success d-none">
                <i class="ti ti-circle-check me-1"></i>
                <strong>Período ya registrado.</strong>
                <span id="habitoProximoPeriodo"></span>
              </div>

              <form id="formHabitoAvance">
                <div id="habitoInputArea" class="d-flex align-items-center justify-content-between py-3">
                  <span class="fw-semibold text-muted" style="font-size:0.9rem;">Puntaje (0 a 10):</span>
                  <div class="input-number d-flex align-items-center">
                    <button type="button" class="minus rounded-pill" id="habitoBtnMinus">-</button>
                    <input type="number" id="habitoInputPuntaje" name="puntaje"
                      min="0" max="10" step="1" value="0"
                      class="text-center mx-1"
                      style="width:60px;border:1px solid #d8d8d8;border-radius:8px;padding:6px;">
                    <button type="button" class="plus" id="habitoBtnPlus">+</button>
                  </div>
                </div>

                <div class="d-flex justify-content-end mt-2">
                  <button type="submit" id="habitoBtnGuardar"
                    class="btn btn-primary rounded-pill px-5">
                    <i class="ti ti-device-floppy me-1"></i> Guardar avance
                  </button>
                </div>
              </form>
            </div>

            {{-- TAB: Historial --}}
            <div class="tab-pane fade" id="habitoTabHistorial" role="tabpanel">
              <div id="habitoTablaHistorial"></div>
              <div id="habitoGraficoHistorial" class="mt-3"></div>
            </div>

          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary rounded-pill"
          data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  'use strict';

  const urlHabito = '{{ url("/rueda-vida/habito") }}';
  const csrf      = '{{ csrf_token() }}';
  let habitoIdActual  = null;
  let chartHabito     = null;

  // ── Abrir modal al pulsar "Registrar avance" ─────────────────────────────
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-registrar-avance');
    if (!btn) { return; }

    habitoIdActual = btn.dataset.habitoId;
    const nombre   = btn.dataset.habitoNombre;

    document.getElementById('modalHabitoTitulo').textContent    = nombre;
    document.getElementById('modalHabitoSubtitulo').textContent = '';
    document.getElementById('habitoSkeleton').classList.remove('d-none');
    document.getElementById('habitoContenido').classList.add('d-none');
    document.getElementById('habitoAvisoRegistrado').classList.add('d-none');
    document.getElementById('habitoBtnGuardar').classList.remove('d-none');
    document.getElementById('habitoBtnGuardar').disabled = false;
    document.getElementById('habitoBtnGuardar').innerHTML = '<i class="ti ti-device-floppy me-1"></i> Guardar avance';
    document.getElementById('habitoGraficoHistorial').innerHTML = '';

    if (chartHabito) { chartHabito.destroy(); chartHabito = null; }

    new bootstrap.Modal(document.getElementById('modalRegistrarAvanceHabito')).show();
    cargarHabito(habitoIdActual);
  });

  // ── Fetch de datos del hábito ─────────────────────────────────────────────
  function cargarHabito(id) {
    fetch(`${urlHabito}/${id}/avances`, {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
    })
    .then(r => r.json())
    .then(renderizar)
    .catch(() => {
      document.getElementById('habitoSkeleton').innerHTML =
        '<p class="text-danger">Error al cargar. Intenta de nuevo.</p>';
    });
  }

  // ── Renderizar contenido del modal ─────────────────────────────────────────
  function renderizar(data) {
    document.getElementById('habitoSkeleton').classList.add('d-none');
    document.getElementById('habitoContenido').classList.remove('d-none');

    // Subtítulo con la fecha del período actual
    const fechaPeriodo = new Date(data.periodo_actual_inicio + 'T00:00:00')
      .toLocaleDateString('es-CO', { day: '2-digit', month: 'long', year: 'numeric' });
    document.getElementById('modalHabitoSubtitulo').textContent = `Período: ${fechaPeriodo}`;

    const inputPuntaje = document.getElementById('habitoInputPuntaje');
    const btnMinus     = document.getElementById('habitoBtnMinus');
    const btnPlus      = document.getElementById('habitoBtnPlus');
    const btnGuardar   = document.getElementById('habitoBtnGuardar');
    const aviso        = document.getElementById('habitoAvisoRegistrado');

    if (data.periodo_registrado) {
      // Ya registrado: mostrar valor bloqueado
      inputPuntaje.value    = data.puntaje_actual ?? 0;
      inputPuntaje.disabled = true;
      btnMinus.disabled     = true;
      btnPlus.disabled      = true;
      btnGuardar.classList.add('d-none');
      aviso.classList.remove('d-none');
      document.getElementById('habitoProximoPeriodo').textContent =
        ' Podrás registrar un nuevo avance en el siguiente período.';
    } else {
      // Disponible: prellenar con el último puntaje si existe
      inputPuntaje.value    = data.puntaje_anterior ?? 0;
      inputPuntaje.disabled = false;
      btnMinus.disabled     = false;
      btnPlus.disabled      = false;
      // Restaurar botón siempre (puede venir de un submit previo)
      btnGuardar.disabled   = false;
      btnGuardar.innerHTML  = '<i class="ti ti-device-floppy me-1"></i> Guardar avance';
      btnGuardar.classList.remove('d-none');
      aviso.classList.add('d-none');
    }

    // Historial
    renderizarHistorial(data.avances);
  }

  // ── Historial: tabla + gráfica ────────────────────────────────────────────
  function renderizarHistorial(avances) {
    const contenedor = document.getElementById('habitoTablaHistorial');
    const grafico    = document.getElementById('habitoGraficoHistorial');
    grafico.innerHTML = '';

    if (!avances || avances.length === 0) {
      contenedor.innerHTML = '<p class="text-muted text-center py-3">Aún no hay registros anteriores.</p>';
      return;
    }

    // Tabla
    let tabla = '<div class="table-responsive"><table class="table table-sm table-bordered">';
    tabla += '<thead class="table-light"><tr><th>Período</th><th class="text-center">Puntaje</th></tr></thead><tbody>';
    avances.forEach(a => {
      const fecha = new Date(a.periodo_inicio + 'T00:00:00')
        .toLocaleDateString('es-CO', { day: '2-digit', month: 'short', year: 'numeric' });
      tabla += `<tr><td>${fecha}</td><td class="text-center fw-semibold">${a.puntaje}</td></tr>`;
    });
    tabla += '</tbody></table></div>';
    contenedor.innerHTML = tabla;

    // Gráfica de línea
    const categorias = avances.map(a =>
      new Date(a.periodo_inicio + 'T00:00:00')
        .toLocaleDateString('es-CO', { day: '2-digit', month: 'short' })
    );
    const valores = avances.map(a => a.puntaje);

    chartHabito = new ApexCharts(grafico, {
      series: [{ name: 'Puntaje', data: valores }],
      chart: { type: 'line', height: 200, toolbar: { show: false }, zoom: { enabled: false } },
      stroke: { curve: 'smooth', width: 2 },
      markers: { size: 5 },
      xaxis: { categories: categorias },
      yaxis: { min: 0, max: 10, tickAmount: 5 },
      colors: ['#696cff'],
      tooltip: { y: { formatter: val => val + ' / 10' } },
    });
    chartHabito.render();
  }

  // ── Botones +/- del modal ─────────────────────────────────────────────────
  document.getElementById('habitoBtnMinus').addEventListener('click', function () {
    const inp = document.getElementById('habitoInputPuntaje');
    inp.value = Math.max(0, parseInt(inp.value || 0) - 1);
  });
  document.getElementById('habitoBtnPlus').addEventListener('click', function () {
    const inp = document.getElementById('habitoInputPuntaje');
    inp.value = Math.min(10, parseInt(inp.value || 0) + 1);
  });

  // ── Guardar avance del hábito ────────────────────────────────────────────
  document.getElementById('formHabitoAvance').addEventListener('submit', function (e) {
    e.preventDefault();

    const btnGuardar = document.getElementById('habitoBtnGuardar');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

    const puntaje = parseInt(document.getElementById('habitoInputPuntaje').value) || 0;

    fetch(`${urlHabito}/${habitoIdActual}/avance`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      body: JSON.stringify({ puntaje }),
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        // Recargar el modal para mostrar estado bloqueado y nueva gráfica
        document.getElementById('habitoSkeleton').classList.remove('d-none');
        document.getElementById('habitoContenido').classList.add('d-none');
        if (chartHabito) { chartHabito.destroy(); chartHabito = null; }
        cargarHabito(habitoIdActual);
      } else {
        alert(data.error || 'Error al guardar.');
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="ti ti-device-floppy me-1"></i> Guardar avance';
      }
    })
    .catch(() => {
      alert('Error de conexión. Intenta de nuevo.');
      btnGuardar.disabled = false;
      btnGuardar.innerHTML = '<i class="ti ti-device-floppy me-1"></i> Guardar avance';
    });
  });
})();
</script>

{{-- ============================================================
     Modal: Ver Avance de Meta (Gráfico resumen de hábitos)
     ============================================================ --}}
<div class="modal fade" id="modalVerAvanceMeta" tabindex="-1"
  aria-labelledby="modalVerAvanceTitulo" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <div>
          <h5 class="modal-title fw-semibold" id="modalVerAvanceTitulo"></h5>
          <small class="text-muted" id="modalVerAvanceSubtitulo"></small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      {{-- Skeleton --}}
      <div id="verAvanceSkeleton" class="modal-body text-center py-5">
        <div class="spinner-border text-dark" role="status"></div>
        <p class="mt-2 text-muted">Cargando historial...</p>
      </div>

      {{-- Contenido --}}
      <div id="verAvanceContenido" class="d-none">
        <div class="modal-body">
          {{-- Sin datos --}}
          <div id="verAvanceSinDatos" class="text-center py-4 d-none">
            <i class="ti ti-chart-line" style="font-size: 2.5rem; color: #b0b0b0;"></i>
            <p class="text-muted mt-2">Aún no hay avances registrados para esta meta.<br>
              <small>Usa el botón <strong>"Registrar avance"</strong> en cada hábito para empezar.</small>
            </p>
          </div>

          {{-- Gráfico --}}
          <div id="verAvanceGrafico"></div>

          {{-- Leyenda / tabla de valores --}}
          <div id="verAvanceTabla" class="mt-3"></div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary rounded-pill"
          data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  'use strict';

  const urlMeta  = '{{ url("/rueda-vida/meta") }}';
  const csrf     = '{{ csrf_token() }}';
  let chartMeta  = null;

  // ── Abrir modal al pulsar "Ver avance" ───────────────────────────────────
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-ver-avance');
    if (!btn) { return; }

    const metaId = btn.dataset.metaId;
    const nombre = btn.dataset.metaNombre;

    document.getElementById('modalVerAvanceTitulo').textContent    = nombre;
    document.getElementById('modalVerAvanceSubtitulo').textContent = '';
    document.getElementById('verAvanceSkeleton').classList.remove('d-none');
    document.getElementById('verAvanceContenido').classList.add('d-none');
    document.getElementById('verAvanceSinDatos').classList.add('d-none');
    document.getElementById('verAvanceGrafico').innerHTML = '';
    document.getElementById('verAvanceTabla').innerHTML   = '';

    if (chartMeta) { chartMeta.destroy(); chartMeta = null; }

    new bootstrap.Modal(document.getElementById('modalVerAvanceMeta')).show();

    cargarAvancesMeta(metaId, nombre);
  });

  // ── Fetch ────────────────────────────────────────────────────────────────
  function cargarAvancesMeta(metaId, nombre) {
    fetch(`${urlMeta}/${metaId}/avances`, {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
    })
    .then(r => r.json())
    .then(data => renderizarMeta(data, nombre))
    .catch(() => {
      document.getElementById('verAvanceSkeleton').innerHTML =
        '<p class="text-danger">Error al cargar. Intenta de nuevo.</p>';
    });
  }

  // ── Renderizar ────────────────────────────────────────────────────────────
  function renderizarMeta(data, nombre) {
    document.getElementById('verAvanceSkeleton').classList.add('d-none');
    document.getElementById('verAvanceContenido').classList.remove('d-none');

    // Recopilar períodos únicos de todos los hábitos
    const periodosSet = new Set();
    data.habitos.forEach(h => h.avances.forEach(a => periodosSet.add(a.periodo_inicio)));
    const periodos = [...periodosSet].sort();

    if (periodos.length === 0) {
      document.getElementById('verAvanceSinDatos').classList.remove('d-none');
      return;
    }

    // Subtítulo con rango de fechas
    const fechaInicio = new Date(periodos[0] + 'T00:00:00')
      .toLocaleDateString('es-CO', { day: '2-digit', month: 'short', year: 'numeric' });
    const fechaFin = new Date(periodos[periodos.length - 1] + 'T00:00:00')
      .toLocaleDateString('es-CO', { day: '2-digit', month: 'short', year: 'numeric' });
    document.getElementById('modalVerAvanceSubtitulo').textContent =
      periodos.length === 1 ? `${periodos.length} período registrado` : `${fechaInicio} → ${fechaFin}`;

    // Series: un hábito = una línea
    const series = data.habitos
      .filter(h => h.avances.length > 0)  // Solo hábitos con al menos un avance
      .map(h => ({
        name: h.nombre,
        data: periodos.map(p => {
          const a = h.avances.find(av => av.periodo_inicio === p);
          return a !== undefined ? a.puntaje : null;
        }),
      }));

    const categorias = periodos.map(p =>
      new Date(p + 'T00:00:00').toLocaleDateString('es-CO', { day: '2-digit', month: 'short', year: '2-digit' })
    );

    chartMeta = new ApexCharts(document.getElementById('verAvanceGrafico'), {
      series: series,
      chart: {
        type: 'line',
        height: periodos.length === 1 ? 200 : 280,
        toolbar: { show: false },
        zoom: { enabled: false },
        animations: { enabled: true, speed: 400 },
      },
      stroke: { curve: 'smooth', width: 2.5 },
      markers: { size: 5, hover: { size: 7 } },
      xaxis: {
        categories: categorias,
        labels: { style: { fontSize: '12px' } },
      },
      yaxis: {
        min: 0,
        max: 10,
        tickAmount: 5,
        labels: { formatter: val => val.toFixed(0) },
      },
      legend: { position: 'bottom', fontSize: '12px', offsetY: 4 },
      tooltip: {
        y: { formatter: val => val !== null ? val + ' / 10' : 'Sin dato' },
      },
      grid: { borderColor: '#f0f0f0' },
      noData: { text: 'Sin datos aún' },
    });
    chartMeta.render();

    // Tabla resumen debajo del gráfico
    let tabla = '<div class="table-responsive"><table class="table table-sm table-bordered mb-0">';
    tabla += '<thead class="table-light"><tr><th>Hábito</th>';
    periodos.forEach(p => {
      const f = new Date(p + 'T00:00:00')
        .toLocaleDateString('es-CO', { day: '2-digit', month: 'short' });
      tabla += `<th class="text-center">${f}</th>`;
    });
    tabla += '</tr></thead><tbody>';

    data.habitos.forEach(h => {
      tabla += `<tr><td style="font-size:0.85rem">${h.nombre}</td>`;
      periodos.forEach(p => {
        const a = h.avances.find(av => av.periodo_inicio === p);
        tabla += `<td class="text-center fw-semibold">${a !== undefined ? a.puntaje : '<span class="text-muted">—</span>'}</td>`;
      });
      tabla += '</tr>';
    });

    tabla += '</tbody></table></div>';
    document.getElementById('verAvanceTabla').innerHTML = tabla;
  }
})();
</script>

@endsection
