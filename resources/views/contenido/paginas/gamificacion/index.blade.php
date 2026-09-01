@extends('layouts/layoutMaster')

@section('title', 'Gamificación y Logros')

@section('page-style')
<style>
  .gamification-header-card {
    background: linear-gradient(135deg, #696cff 0%, #393b7f 100%);
    color: #ffffff;
    border: none;
    border-radius: 1rem;
    box-shadow: 0 10px 25px -5px rgba(105, 108, 255, 0.4);
  }

  .gamification-avatar {
    width: 68px;
    height: 68px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    object-fit: cover;
  }

  .puntos-pill {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #ffda6a;
    font-weight: 700;
    font-size: 1rem;
    padding: 0.5rem 1.25rem;
    border-radius: 50rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
  }

  .insignia-card {
    border: 1px solid #e7e7e8;
    border-radius: 0.75rem;
    transition: all 0.25s ease-in-out;
    background: #ffffff;
    height: 100%;
  }

  .insignia-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
  }

  .insignia-card.bloqueada {
    background: #f8f9fa;
    border-style: dashed;
    opacity: 0.75;
  }

  .insignia-icon-wrapper {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: justify;
    margin: 0 auto 1rem;
    font-size: 2rem;
  }

  .insignia-icon-wrapper.bloqueada-icon {
    background-color: #e9ecef !important;
    color: #6c757d !important;
    filter: grayscale(100%);
  }

  .nav-tabs .nav-link {
    font-weight: 600;
    padding: 0.75rem 1.25rem;
    border-radius: 0.5rem 0.5rem 0 0;
  }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Header Card Principal -->
  <div class="card gamification-header-card mb-4">
    <div class="card-body p-4">
      <div class="d-flex flex-column flex-md-row align-items-center justify-content-between text-center text-md-start gap-3">
        <div class="d-flex flex-column flex-md-row align-items-center gap-3">
          <img src="{{ $usuario->foto_url }}" alt="{{ $usuario->nombre(2) }}" class="rounded-circle gamification-avatar shadow">
          <div>
            <h3 class="text-white mb-1 fw-bold">¡Hola, {{ $usuario->nombre(2) }}!</h3>
            <p class="text-white-50 mb-0">Sigue participando y acumulando logros en tu camino de crecimiento.</p>
          </div>
        </div>
        <div class="puntos-pill shadow-sm">
          <i class="ti ti-coins fs-4 text-warning"></i>
          <span>{{ number_format($usuario->puntos) }} Puntos</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Pestañas de Navegación -->
  <div class="card">
    <div class="card-header pb-0 border-bottom-0">
      <ul class="nav nav-tabs card-header-tabs" id="gamificacionTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active d-flex align-items-center gap-2" id="insignias-tab" data-bs-toggle="tab" data-bs-target="#tab-insignias" type="button" role="tab" aria-controls="tab-insignias" aria-selected="true">
            <i class="ti ti-award fs-5"></i>
            <span>Mis Insignias</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link d-flex align-items-center gap-2" id="misiones-tab" data-bs-toggle="tab" data-bs-target="#tab-misiones" type="button" role="tab" aria-controls="tab-misiones" aria-selected="false">
            <i class="ti ti-list-check fs-5"></i>
            <span>Misiones</span>
            <span class="badge bg-label-secondary rounded-pill ms-1 fs-tiny">Próximamente</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link d-flex align-items-center gap-2" id="tienda-tab" data-bs-toggle="tab" data-bs-target="#tab-tienda" type="button" role="tab" aria-controls="tab-tienda" aria-selected="false">
            <i class="ti ti-shopping-cart fs-5"></i>
            <span>Tienda de Canje</span>
            <span class="badge bg-label-secondary rounded-pill ms-1 fs-tiny">Próximamente</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link d-flex align-items-center gap-2" id="historial-tab" data-bs-toggle="tab" data-bs-target="#tab-historial" type="button" role="tab" aria-controls="tab-historial" aria-selected="false">
            <i class="ti ti-history fs-5"></i>
            <span>Historial</span>
            <span class="badge bg-label-secondary rounded-pill ms-1 fs-tiny">Próximamente</span>
          </button>
        </li>
      </ul>
    </div>

    <div class="card-body pt-4">
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
                  <div class="insignia-card p-4 text-center {{ $esBloqueada ? 'bloqueada' : '' }}">
                    
                    <!-- Ícono de la Insignia -->
                    @if($esBloqueada)
                      <div class="insignia-icon-wrapper bloqueada-icon shadow-sm d-flex justify-content-center align-items-center">
                        <i class="ti {{ $insignia->icono_clase ?: 'ti-lock' }}"></i>
                      </div>
                    @else
                      <div class="insignia-icon-wrapper shadow-sm d-flex justify-content-center align-items-center" style="background-color: {{ $insignia->icono_color ?: '#696cff' }}22; color: {{ $insignia->icono_color ?: '#696cff' }};">
                        <i class="ti {{ $insignia->icono_clase ?: 'ti-award' }}"></i>
                      </div>
                    @endif

                    <!-- Nombre y Descripción -->
                    <h5 class="fw-bold mb-1 {{ $esBloqueada ? 'text-muted' : '' }}">{{ $insignia->nombre }}</h5>
                    <p class="text-muted small mb-3">{{ $insignia->descripcion ?: 'Completa los objetivos para desbloquear este logro.' }}</p>

                    <!-- Estado y Progreso -->
                    @if($esCompletada)
                      <span class="badge bg-label-success rounded-pill px-3 py-2">
                        <i class="ti ti-circle-check-filled me-1"></i> Obtenida
                      </span>
                      @if($progreso->obtenida_el)
                        <div class="text-muted fs-tiny mt-2">
                          Conseguida el {{ \Carbon\Carbon::parse($progreso->obtenida_el)->format('d/m/Y') }}
                        </div>
                      @endif
                    @elseif($esEnProgreso)
                      <div class="mt-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                          <span class="text-muted small fw-semibold">Progreso</span>
                          <span class="fw-bold small text-primary">{{ $progreso->progreso_actual }} / {{ $metaCantidad }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                          <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $porcentaje }}%" aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                      </div>
                    @else
                      <span class="badge bg-label-secondary rounded-pill px-3 py-2">
                        <i class="ti ti-lock me-1"></i> Bloqueada
                      </span>
                    @endif

                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>

        <!-- Contenido Tab 2: Misiones (Indicada) -->
        <div class="tab-pane fade" id="tab-misiones" role="tabpanel" aria-labelledby="misiones-tab">
          <div class="text-center py-5">
            <div class="mb-3">
              <i class="ti ti-list-check fs-1 text-primary p-3 bg-label-primary rounded-circle"></i>
            </div>
            <h4 class="fw-bold mb-2">Misiones Diarias y Semanales</h4>
            <p class="text-muted max-w-500 mx-auto">Próximamente podrás realizar actividades clave como devocionales, asistencias y actualización de datos para acumular puntos adicionales.</p>
          </div>
        </div>

        <!-- Contenido Tab 3: Tienda de Canje (Indicada) -->
        <div class="tab-pane fade" id="tab-tienda" role="tabpanel" aria-labelledby="tienda-tab">
          <div class="text-center py-5">
            <div class="mb-3">
              <i class="ti ti-shopping-cart fs-1 text-primary p-3 bg-label-primary rounded-circle"></i>
            </div>
            <h4 class="fw-bold mb-2">Tienda de Canjes</h4>
            <p class="text-muted max-w-500 mx-auto">Próximamente tendrás acceso al catálogo de premios físicos y digitales que podrás solicitar usando tus puntos acumulados.</p>
          </div>
        </div>

        <!-- Contenido Tab 4: Historial (Indicada) -->
        <div class="tab-pane fade" id="tab-historial" role="tabpanel" aria-labelledby="historial-tab">
          <div class="text-center py-5">
            <div class="mb-3">
              <i class="ti ti-history fs-1 text-primary p-3 bg-label-primary rounded-circle"></i>
            </div>
            <h4 class="fw-bold mb-2">Historial de Puntos</h4>
            <p class="text-muted max-w-500 mx-auto">Próximamente podrás auditar tu estado de cuenta completo con cada punto ganado o canjeado en la plataforma.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
