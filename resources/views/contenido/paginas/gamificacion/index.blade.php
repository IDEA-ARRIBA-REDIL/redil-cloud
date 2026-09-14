@extends('layouts/layoutMaster')

@section('title', 'Gamificación y Logros')

@section('page-style')
<style>
  .gamificacion-hero-card {
    background: #14532d;
    background: linear-gradient(135deg, #3b762b 0%, #1f3d1a 100%);
    border-radius: 16px;
    color: #ffffff;
  }

  .gamificacion-puntos-badge {
    background: rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    color: #ffffff;
    font-weight: 700;
    font-size: 0.95rem;
    padding: 0.35rem 1rem;
    border-radius: 50rem;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
  }

  .gamificacion-pill-container {
    border: 1px solid #e2e8f0 !important;
    border-radius: 50rem !important;
    padding: 5px 6px !important;
    background-color: #ffffff !important;
    display: inline-flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    gap: 4px !important;
    margin: 0 !important;
  }

  .gamificacion-pill-container .nav-item {
    margin: 0 !important;
    padding: 0 !important;
  }

  .gamificacion-pill-container .nav-link,
  .gamificacion-pill-container .nav-link.active,
  .gamificacion-pill-container .nav-link:hover,
  .gamificacion-pill-container .nav-link:focus {
    border-radius: 50rem !important;
  }

  .gamificacion-pill-container .nav-link {
    color: #475569 !important;
    font-weight: 600 !important;
    font-size: 0.9rem !important;
    padding: 0.42rem 1.15rem !important;
    border: none !important;
    margin: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
    transition: all 0.2s ease-in-out !important;
  }

  .gamificacion-pill-container .nav-link:hover {
    color: #166534 !important;
    background-color: #f1f5f9 !important;
  }

  .gamificacion-pill-container .nav-link.active {
    background-color: #166534 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(22, 101, 52, 0.25) !important;
  }

  .insignia-clean-card {
    background: #f0f4f9;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.5rem 1.25rem 1.25rem;
    min-height: 180px;
    transition: all 0.25s ease-in-out;
  }

  .insignia-clean-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
  }

  .insignia-clean-card.is-locked-card {
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    opacity: 0.75;
  }

  .insignia-icon-circle {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
  }

  .insignia-icon-circle.is-unlocked {
    background-color: #e6f7ec;
    color: #166534;
  }

  .insignia-icon-circle.is-locked {
    background-color: #f1f5f9;
    color: #94a3b8;
  }

  .insignia-title {
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 2px;
  }

  .insignia-desc {
    font-size: 0.82rem;
    color: #64748b;
    margin-bottom: 12px;
    line-height: 1.35;
  }

  .gamificacion-progress {
    height: 6px;
    border-radius: 999px;
    background-color: #e2e8f0;
    overflow: hidden;
  }

  .gamificacion-progress-bar {
    background-color: #166534 !important;
    border-radius: 999px;
  }

  .insignia-status-badge {
    font-size: 0.82rem;
    font-weight: 700;
  }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="mb-1 fw-semibold text-primary">Mi panel de gamificación</h4>
  <p class="mb-4 text-black">Sumá puntos completando acciones y canjealos por premios.</p>

  <!-- Header Card Principal -->
  <div class="card gamificacion-hero-card mb-4 border-0 shadow-sm" style="border-radius: 20px;">
    <div class="card-body p-5">
      <div class="d-flex align-items-center gap-3">
        @if($usuario->foto == "default-m.png" || $usuario->foto == "default-f.png" || !$usuario->foto)
          <div class="avatar avatar-xl">
            <span class="avatar-initial rounded-circle border border-2 border-white bg-danger text-white fw-bold fs-4"> {{ $usuario->inicialesNombre() }} </span>
          </div>
        @else
          <div class="avatar avatar-xl">
            <img src="{{ $usuario->foto_url }}" alt="{{ $usuario->nombre(2) }}" class="avatar-initial rounded-circle border border-2 border-white object-fit-cover">
          </div>
        @endif

        <div class="d-flex flex-column align-items-start gap-1">
          <h5 class="text-white mb-0 fw-semibold">¡Hola, {{ $usuario->nombre(2) }}!</h5>
          <div class="gamificacion-puntos-badge">
            <i class="ti ti-coins text-warning fs-5"></i>
            <span>{{ number_format($usuario->puntos) }} Puntos</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Contenedor Principal Blanco -->
  <div class="card border-0 shadow-sm rounded-4 p-4" style="border-radius: 20px;">
    <!-- Pestañas de Navegación en Cápsula -->
    <div class="mb-4">
      <ul class="nav nav-pills gamificacion-pill-container d-inline-flex flex-wrap" id="gamificacionTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active rounded-pill d-flex align-items-center gap-2" id="insignias-tab" data-bs-toggle="tab" data-bs-target="#tab-insignias" type="button" role="tab" aria-controls="tab-insignias" aria-selected="true">
            <i class="ti ti-award fs-5"></i>
            <span>Mis Insignias</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link rounded-pill d-flex align-items-center gap-2" id="misiones-tab" data-bs-toggle="tab" data-bs-target="#tab-misiones" type="button" role="tab" aria-controls="tab-misiones" aria-selected="false">
            <i class="ti ti-list-check fs-5"></i>
            <span>Misiones</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link rounded-pill d-flex align-items-center gap-2" id="tienda-tab" data-bs-toggle="tab" data-bs-target="#tab-tienda" type="button" role="tab" aria-controls="tab-tienda" aria-selected="false">
            <i class="ti ti-shopping-cart fs-5"></i>
            <span>Tienda de Canje</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link rounded-pill d-flex align-items-center gap-2" id="historial-tab" data-bs-toggle="tab" data-bs-target="#tab-historial" type="button" role="tab" aria-controls="tab-historial" aria-selected="false">
            <i class="ti ti-history fs-5"></i>
            <span>Historial</span>
          </button>
        </li>
      </ul>
    </div>

    <!-- Contenido de las Pestañas -->
    <div class="tab-content p-0" id="gamificacionTabContent">
      <!-- Contenido Tab 1: Mis Insignias -->
      <div class="tab-pane fade show active" id="tab-insignias" role="tabpanel" aria-labelledby="insignias-tab">
        @if($insignias->isEmpty())
          <div class="text-center py-5">
            <i class="ti ti-award-off fs-1 text-muted mb-2"></i>
            <h5 class="text-muted">No hay insignias registradas en el catálogo.</h5>
          </div>
        @else
          <div class="row g-4">
            @foreach($insignias as $insignia)
              @php
                $progreso = $progresos->get($insignia->id);
                $reglaMeta = $reglasMeta->get($insignia->id);
                $metaCantidad = $reglaMeta ? $reglaMeta->meta_cantidad : 20;

                $esCompletada = $progreso && $progreso->completada;
                $esEnProgreso = $progreso && !$progreso->completada;
                $esBloqueada = !$progreso;

                $porcentaje = 0;
                if ($esEnProgreso) {
                    $porcentaje = min(100, round(($progreso->progreso_actual / max(1, $metaCantidad)) * 100));
                }
              @endphp

              <div class="col-12 col-md-6 col-lg-4">
                <div class="insignia-clean-card text-center d-flex flex-column align-items-center justify-content-center h-100 {{ $esBloqueada ? 'is-locked-card' : '' }}">
                  
                  <!-- Ícono de la Insignia -->
                  @if($esBloqueada)
                    <div class="insignia-icon-circle is-locked mb-2">
                      <i class="ti {{ $insignia->icono_clase ?: 'ti-lock' }}"></i>
                    </div>
                  @else
                    <div class="insignia-icon-circle is-unlocked mb-2">
                      <i class="ti {{ $insignia->icono_clase ?: 'ti-award' }}"></i>
                    </div>
                  @endif

                  <!-- Nombre y Descripción -->
                  <h6 class="insignia-title {{ $esBloqueada ? 'text-muted' : '' }}">{{ $insignia->nombre }}</h6>
                  <p class="insignia-desc">{{ $insignia->descripcion ?: 'Completa los objetivos para desbloquear este logro.' }}</p>

                  <!-- Estado y Progreso -->
                  @if($esCompletada)
                    <div class="text-primary insignia-status-badge d-flex align-items-center justify-content-center gap-1 mt-auto">
                      <i class="ti ti-circle-check-filled fs-6"></i>
                      <span>Obtenida</span>
                    </div>
                  @elseif($esEnProgreso)
                    <div class="w-100 mt-auto" style="max-width: 240px;">
                      <div class="progress gamificacion-progress mb-1">
                        <div class="progress-bar gamificacion-progress-bar" role="progressbar" style="width: {{ $porcentaje }}%" aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <div class="text-muted fw-semibold text-center" style="font-size: 0.8rem;">{{ $progreso->progreso_actual }} / {{ $metaCantidad }}</div>
                    </div>
                  @else
                    <div class="text-muted insignia-status-badge d-flex align-items-center justify-content-center gap-1 mt-auto">
                      <i class="ti ti-lock fs-6"></i>
                      <span>Bloqueada</span>
                    </div>
                  @endif

                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>

      <!-- Contenido Tab 2: Misiones -->
      <div class="tab-pane fade" id="tab-misiones" role="tabpanel" aria-labelledby="misiones-tab">
        <div class="text-center py-5">
          <div class="mb-3">
            <i class="ti ti-list-check fs-1 text-success p-3 bg-label-success rounded-circle"></i>
          </div>
          <h4 class="fw-bold mb-2">Misiones Diarias y Semanales</h4>
          <p class="text-muted max-w-500 mx-auto">Próximamente podrás realizar actividades clave como devocionales, asistencias y actualización de datos para acumular puntos adicionales.</p>
        </div>
      </div>

      <!-- Contenido Tab 3: Tienda de Canje -->
      <div class="tab-pane fade" id="tab-tienda" role="tabpanel" aria-labelledby="tienda-tab">
        <div class="text-center py-5">
          <div class="mb-3">
            <i class="ti ti-shopping-cart fs-1 text-success p-3 bg-label-success rounded-circle"></i>
          </div>
          <h4 class="fw-bold mb-2">Tienda de Canjes</h4>
          <p class="text-muted max-w-500 mx-auto">Próximamente tendrás acceso al catálogo de premios físicos y digitales que podrás solicitar usando tus puntos acumulados.</p>
        </div>
      </div>

      <!-- Contenido Tab 4: Historial -->
      <div class="tab-pane fade" id="tab-historial" role="tabpanel" aria-labelledby="historial-tab">
        <div class="text-center py-5">
          <div class="mb-3">
            <i class="ti ti-history fs-1 text-success p-3 bg-label-success rounded-circle"></i>
          </div>
          <h4 class="fw-bold mb-2">Historial de Puntos</h4>
          <p class="text-muted max-w-500 mx-auto">Próximamente podrás auditar tu estado de cuenta completo con cada punto ganado o canjeado en la plataforma.</p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
