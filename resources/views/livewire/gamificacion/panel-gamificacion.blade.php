<div>
  <!-- ======================================================================= -->
  <!-- ESTILOS ESPECÍFICOS DEL PANEL DE GAMIFICACIÓN -->
  <!-- ======================================================================= -->
  <style>
    .gamificacion-hero-card {
      background: #14532d;
      background: linear-gradient(135deg, #3b762b 0%, #1f3d1a 100%);
      border-radius: 20px;
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

    .gamificacion-tabs-scroll-wrapper {
      width: 100%;
      overflow-x: auto;
      scrollbar-width: none;
      -ms-overflow-style: none;
      -webkit-overflow-scrolling: touch;
      padding-bottom: 6px;
      padding-top: 2px;
    }

    .gamificacion-tabs-scroll-wrapper::-webkit-scrollbar {
      display: none;
    }

    .gamificacion-pill-container {
      border: 1px solid #e2e8f0 !important;
      border-radius: 50rem !important;
      padding: 5px 6px !important;
      background-color: #ffffff !important;
      display: inline-flex !important;
      flex-wrap: nowrap !important;
      align-items: center !important;
      gap: 4px !important;
      margin: 0 !important;
      white-space: nowrap !important;
    }

    .gamificacion-pill-container .nav-item {
      margin: 0 !important;
      padding: 0 !important;
      flex-shrink: 0 !important;
    }

    .gamificacion-pill-container .nav-link {
      color: #2f2b3d !important;
      font-weight: 600 !important;
      font-size: 0.9rem !important;
      padding: 0.42rem 1.15rem !important;
      border: none !important;
      margin: 0 !important;
      background: transparent !important;
      box-shadow: none !important;
      white-space: nowrap !important;
      border-radius: 50rem !important;
      transition: all 0.2s ease-in-out !important;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
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

    /* Tarjetas de Insignias - Card Vertical Estándar (Íconos) */
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

    /* Tarjetas de Insignias - Card Horizontal (Imágenes) */
    .insignia-horizontal-card {
      background: #f0f4f9;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      padding: 1.2rem;
      min-height: 140px;
      transition: all 0.25s ease-in-out;
      display: flex;
      align-items: center;
      gap: 1.15rem;
    }

    .insignia-horizontal-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
    }

    .insignia-horizontal-card.is-locked-card {
      background: #f8fafc;
      border: 1px solid #f1f5f9;
      opacity: 0.75;
    }

    .insignia-img-thumb {
      width: 100px;
      height: 100px;
      border-radius: 14px;
      object-fit: cover;
      flex-shrink: 0;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
      transition: filter 0.2s, opacity 0.2s;
    }

    .insignia-img-thumb.is-locked {
      filter: grayscale(100%);
      opacity: 0.55;
    }

    .insignia-icon-circle {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
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

    /* ======================================================================= */
    /* TARJETAS DE LA TIENDA DE CANJES (DISEÑO CLEAN CARDS) */
    /* ======================================================================= */
    .tienda-producto-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 18px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      height: 100%;
      transition: all 0.25s ease-in-out;
    }

    .tienda-producto-card.is-canjeable {
      border-color: #86efac;
    }

    .tienda-producto-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    }

    .tienda-producto-header {
      background-color: #f8fafc;
      min-height: 150px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.25rem;
      position: relative;
    }

    .tienda-producto-img-wrapper {
      position: relative;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      border-radius: 12px;
      padding: 4px;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
      transition: all 0.25s ease;
    }

    .tienda-producto-img-wrapper:hover {
      transform: scale(1.05);
      border-color: #cbd5e1;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    }

    .tienda-producto-img-wrapper:hover .tienda-img-zoom-badge {
      opacity: 1;
      transform: scale(1);
    }

    .tienda-producto-header img {
      max-height: 110px;
      max-width: 130px;
      object-fit: contain;
      border-radius: 8px;
    }

    .tienda-img-zoom-badge {
      position: absolute;
      bottom: 6px;
      right: 6px;
      background: rgba(15, 23, 42, 0.75);
      color: #ffffff;
      width: 26px;
      height: 26px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.75rem;
      opacity: 0;
      transform: scale(0.85);
      transition: all 0.2s ease;
      backdrop-filter: blur(4px);
    }

    .tienda-producto-placeholder-box {
      width: 80px;
      height: 80px;
      border-radius: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
      border: 1px solid rgba(0, 0, 0, 0.06);
      transition: transform 0.25s ease;
    }

    .tienda-producto-card:hover .tienda-producto-placeholder-box {
      transform: scale(1.06);
    }

    .tienda-producto-body {
      padding: 1.25rem 1.25rem 1.25rem;
      display: flex;
      flex-direction: column;
      flex-grow: 1;
    }

    .tienda-puntos-tag {
      background-color: #fef9c3;
      color: #854d0e;
      font-weight: 700;
      font-size: 0.85rem;
      padding: 0.25rem 0.65rem;
      border-radius: 50rem;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      white-space: nowrap;
    }

    .btn-disabled-outline {
      border: 1px solid #cbd5e1 !important;
      color: #64748b !important;
      background-color: transparent !important;
      cursor: not-allowed !important;
      opacity: 0.85;
    }

    /* ======================================================================= */
    /* TARJETAS DE MISIONES (FORMATO FILA ALARGADA) */
    /* ======================================================================= */
    .mision-list-group {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .mision-row-card {
      background: #ffffff;
      border: 1px solid #eef2f6;
      border-radius: 14px;
      padding: 1rem 1.25rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      transition: all 0.2s ease-in-out;
      text-decoration: none !important;
    }

    .mision-row-card:hover {
      border-color: #cbd5e1;
      background-color: #f8fafc;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
    }

    .mision-row-card.is-completada {
      background-color: #ffffff;
      border-color: #e2e8f0;
      opacity: 0.95;
    }

    .mision-icon-circle {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background-color: #e6f7ec;
      color: #166534;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
      flex-shrink: 0;
    }


    .mision-points-pill {
      background-color: #15803d;
      color: #ffffff;
      font-weight: 700;
      font-size: 0.86rem;
      padding: 0.38rem 1rem;
      border-radius: 50rem;
      white-space: nowrap;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      flex-shrink: 0;
      transition: transform 0.2s;
    }

    .mision-points-pill:hover {
      transform: scale(1.03);
    }

    .mision-insignia-pill {
      background-color: #166534;
      color: #ffffff;
      font-weight: 600;
      font-size: 0.82rem;
      padding: 0.35rem 0.9rem;
      border-radius: 50rem;
      white-space: nowrap;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      flex-shrink: 0;
    }

    .mision-completed-badge {
      background-color: #f0fdf4;
      border: 1px solid #bbf7d0;
      color: #16a34a;
      font-weight: 700;
      font-size: 0.86rem;
      padding: 0.38rem 1.15rem;
      border-radius: 50rem;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      white-space: nowrap;
      flex-shrink: 0;
    }

    .mision-subtabs-pill {
      background-color: #f1f5f9;
      border-radius: 50rem;
      padding: 3px;
      display: inline-flex;
      gap: 3px;
    }

    .mision-subtab-btn {
      border: none;
      background: transparent;
      padding: 0.35rem 0.95rem;
      border-radius: 50rem;
      font-size: 0.84rem;
      font-weight: 600;
      color: #64748b;
      transition: all 0.2s;
    }

    .mision-subtab-btn.active {
      background: #ffffff;
      color: #166534;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
    }

    /* ======================================================================= */
    /* TABLA DE MOVIMIENTOS HISTORIAL (CLEAN STYLE) */
    /* ======================================================================= */
    .table-historial-movimientos {
      margin-bottom: 0;
      width: 100%;
    }

    .table-historial-movimientos thead th {
      font-size: 0.82rem;
      font-weight: 600;
      color: #000000ff;
      border-bottom: 1px solid #e2e8f0;
      padding: 0.85rem 1rem;
      background-color: transparent;
      text-transform: none;
      letter-spacing: normal;
    }

    .table-historial-movimientos tbody td {
      padding: 1rem 1rem;
      vertical-align: middle;
      border-bottom: 1px solid #f1f5f9;
      font-size: 0.88rem;
    }

    .table-historial-movimientos tbody tr:hover {
      background-color: #f8fafc;
    }

    .historial-fecha-text {
      color: #64748b;
      font-size: 0.84rem;
      font-weight: 500;
      white-space: nowrap;
    }


    .historial-puntos-pos {
      color: #16a34a;
      font-weight: 700;
      font-size: 0.92rem;
      white-space: nowrap;
    }

    .historial-puntos-neg {
      color: #dc2626;
      font-weight: 700;
      font-size: 0.92rem;
      white-space: nowrap;
    }
  </style>

  <!-- ======================================================================= -->
  <!-- HEADER CARD PRINCIPAL (HERO BANNER) -->
  <!-- ======================================================================= -->
  <div class="card gamificacion-hero-card mb-4 border-0 shadow-sm">
    <div class="card-body p-4 p-md-5">
      <div class="d-flex flex-column flex-md-row align-items-center justify-content-center justify-content-md-start text-center text-md-start gap-3">
        @if($usuario->foto == "default-m.png" || $usuario->foto == "default-f.png" || !$usuario->foto)
          <div class="avatar avatar-xl">
            <span class="avatar-initial rounded-circle border border-2 border-white bg-danger text-white fw-bold fs-4">
              {{ $usuario->inicialesNombre() }}
            </span>
          </div>
        @else
          <div class="avatar avatar-xl">
            <img src="{{ $usuario->foto_url }}" alt="{{ $usuario->nombre(2) }}" class="avatar-initial rounded-circle border border-2 border-white object-fit-cover">
          </div>
        @endif

        <div class="d-flex flex-column align-items-center align-items-md-start gap-1">
          <h5 class="text-white mb-0 fw-semibold">¡Hola, {{ $usuario->nombre(2) }}!</h5>
          <div class="gamificacion-puntos-badge">
            <i class="ti ti-coins text-warning fs-5"></i>
            <span>{{ number_format($usuario->puntos) }} Puntos</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ======================================================================= -->
  <!-- CONTENEDOR PRINCIPAL BLANCO -->
  <!-- ======================================================================= -->
  <div class="card border-0 shadow-sm rounded-4 p-4" style="border-radius: 20px;">
    <!-- Pestañas de Navegación en Cápsula (Con Scroll Horizontal en Móvil) -->
    <div class="gamificacion-tabs-scroll-wrapper mb-4">
      <ul class="nav nav-pills gamificacion-pill-container" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link {{ $tabActivo === 'insignias' ? 'active' : '' }}" type="button" wire:click="cambiarTab('insignias')">
            <i class="ti ti-award fs-5"></i>
            <span>Mis Insignias</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link {{ $tabActivo === 'misiones' ? 'active' : '' }}" type="button" wire:click="cambiarTab('misiones')">
            <i class="ti ti-list-check fs-5"></i>
            <span>Misiones</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link {{ $tabActivo === 'tienda' ? 'active' : '' }}" type="button" wire:click="cambiarTab('tienda')">
            <i class="ti ti-shopping-cart fs-5"></i>
            <span>Tienda de canje</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link {{ $tabActivo === 'historial' ? 'active' : '' }}" type="button" wire:click="cambiarTab('historial')">
            <i class="ti ti-history fs-5"></i>
            <span>Historial</span>
          </button>
        </li>
      </ul>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 1: MIS INSIGNIAS -->
    <!-- ===================================================================== -->
    @if($tabActivo === 'insignias')
      <div>
        @if($insignias->isEmpty())
          <div class="text-center py-5">
            <i class="ti ti-award-off fs-1 text-black mb-2"></i>
            <h5 class="text-black">No hay insignias registradas en el catálogo.</h5>
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

              @if($insignia->es_imagen && $insignia->imagen_url)
                <!-- Card Horizontal para Insignia con Imagen -->
                <div class="col-12 col-md-6 col-lg-4">
                  <div class="insignia-horizontal-card h-100 {{ $esBloqueada ? 'is-locked-card' : '' }}">
                    <img src="{{ $insignia->imagen_url }}" alt="{{ $insignia->nombre }}" class="insignia-img-thumb {{ $esBloqueada ? 'is-locked' : '' }}">
                    <div class="flex-grow-1 d-flex flex-column justify-content-between h-100">
                      <div>
                        <h6 class="insignia-title fw-semibold {{ $esBloqueada ? 'text-muted' : '' }} mb-1">{{ $insignia->nombre }}</h6>
                        <p class="insignia-desc text-black mb-2">{{ $insignia->descripcion ?: 'Completa los objetivos para desbloquear este logro.' }}</p>
                      </div>

                      @if($esCompletada)
                        <div class="text-primary insignia-status-badge d-flex align-items-center gap-1 mt-auto">
                          <i class="ti ti-circle-check-filled fs-6"></i>
                          <span>Obtenida</span>
                        </div>
                      @elseif($esEnProgreso)
                        <div class="w-100 mt-auto">
                          <div class="progress gamificacion-progress mb-1">
                            <div class="progress-bar gamificacion-progress-bar" role="progressbar" style="width: {{ $porcentaje }}%" aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                          <div class="text-black fw-semibold" style="font-size: 0.8rem;">{{ $progreso->progreso_actual }} / {{ $metaCantidad }}</div>
                        </div>
                      @else
                        <div class="text-black insignia-status-badge d-flex align-items-center gap-1 mt-auto">
                          <i class="ti ti-lock fs-6"></i>
                          <span>Bloqueada</span>
                        </div>
                      @endif
                    </div>
                  </div>
                </div>
              @else
                <!-- Card Vertical para Insignia con Ícono -->
                <div class="col-12 col-md-6 col-lg-4">
                  <div class="insignia-clean-card text-center d-flex flex-column align-items-center justify-content-center h-100 {{ $esBloqueada ? 'is-locked-card' : '' }}">

                    <!-- Ícono de la Insignia -->
                    @if($esBloqueada)
                      <div class="insignia-icon-circle is-locked mb-2">
                        <i class="{{ $insignia->icono_completo }}"></i>
                      </div>
                    @else
                      <div class="insignia-icon-circle mb-2" style="background-color: {{ ($insignia->icono_color ?: '#166534') . '20' }}; color: {{ $insignia->icono_color ?: '#166534' }};">
                        <i class="{{ $insignia->icono_completo }}"></i>
                      </div>
                    @endif

                    <!-- Nombre y Descripción -->
                    <h6 class="insignia-title fw-semibold {{ $esBloqueada ? 'text-muted' : '' }}">{{ $insignia->nombre }}</h6>
                    <p class="insignia-desc text-black">{{ $insignia->descripcion ?: 'Completa los objetivos para desbloquear este logro.' }}</p>

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
              @endif
            @endforeach
          </div>
        @endif
      </div>
    @endif

    <!-- ===================================================================== -->
    <!-- TAB 2: MISIONES -->
    <!-- ===================================================================== -->
    @if($tabActivo === 'misiones')
      <div>
        <!-- Cabecera de Misiones y Sub-Filtros -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
          <div>
            <h5 class="fw-semibold text-black mb-1 d-flex align-items-center gap-2">
              <i class="ti ti-target-arrow text-primary"></i> Misiones y retos disponibles
            </h5>
            <p class="text-black small mb-0">Cumple estos objetivos para ganar puntos y desbloquear insignias exclusivas.</p>
          </div>

          <!-- Píldoras de Sub-Filtro -->
          <div class="mision-subtabs-pill">
            <button type="button" class="mision-subtab-btn {{ $filtroMision === 'todas' ? 'active' : '' }}" wire:click="filtrarMisiones('todas')">
              Todas ({{ $totalMisiones }})
            </button>
            <button type="button" class="mision-subtab-btn {{ $filtroMision === 'diarias' ? 'active' : '' }}" wire:click="filtrarMisiones('diarias')">
              Recurrentes
            </button>
            <button type="button" class="mision-subtab-btn {{ $filtroMision === 'pendientes' ? 'active' : '' }}" wire:click="filtrarMisiones('pendientes')">
              Pendientes ({{ $totalPendientes }})
            </button>
            <button type="button" class="mision-subtab-btn {{ $filtroMision === 'completadas' ? 'active' : '' }}" wire:click="filtrarMisiones('completadas')">
              Completadas ({{ $totalCompletadas }})
            </button>
          </div>
        </div>

        @if($misiones->isEmpty())
          <div class="text-center py-5">
            <div class="mb-3">
              <i class="ti ti-clipboard-check fs-1 text-black"></i>
            </div>
            <h5 class="text-black fw-semibold">No se encontraron misiones en esta categoría.</h5>
            <p class="text-black small">Cambia de filtro o vuelve a consultar más adelante.</p>
          </div>
        @else
          <div class="mision-list-group">
            @foreach($misiones as $mision)
              @php
                $meta = $mision['metaData'];
                $esCompletada = in_array($mision['estado'], ['completada', 'completada_hoy']);
                $esMeta = $mision['frecuencia'] === 'meta';
                $esRecurrente = $mision['frecuencia'] === 'cada_vez';
                $esUnica = $mision['frecuencia'] === 'unica_vez';
                $tienePuntos = $mision['puntos_premio'] > 0;
                $tieneInsignia = !empty($mision['insignia']);
              @endphp

              <div class="mision-row-card {{ $esCompletada ? 'is-completada' : '' }}" wire:key="mision-{{ $mision['id'] }}">

                <!-- Columna Izquierda: Ícono y Textos de la Misión -->
                <div class="d-flex align-items-start gap-3 flex-grow-1">
                  <!-- Ícono Circular Verde Suave -->
                  <div class="mision-icon-circle">
                    <i class="ti {{ $meta['icono'] }}"></i>
                  </div>

                  <!-- Información y Títulos -->
                  <div class="flex-grow-1">
                    <div class="fw-semibold text-black">{{ $mision['nombre'] }}</div>
                    <div class="small text-black">
                      {{ $meta['titulo'] !== $mision['nombre'] ? $meta['titulo'] . ' — ' : '' }}
                      @if($esMeta)
                        Repite esta acción hasta completar la meta.
                      @elseif($esRecurrente)
                        Gana recompensas cada vez que participes.
                      @else
                        Completa esta acción para ganar tu recompensa.
                      @endif
                    </div>

                    <!-- Sub-info: Frecuencia y Progreso -->
                    <div class="d-flex flex-wrap align-items-center gap-3 mt-1">
                      @if($esUnica)
                        <span class="small text-info">
                          <i class="ti ti-rotate-clockwise fs-6"></i> Única vez
                        </span>
                      @elseif($esRecurrente)
                        <span class="small text-info">
                          <i class="ti ti-infinity fs-6"></i> Cada vez que lo hagas
                        </span>
                      @elseif($esMeta)
                        <span class="small text-info">
                          <i class="ti ti-target fs-6"></i> Meta: {{ $mision['progreso_actual'] }} / {{ $mision['meta_cantidad'] }}
                        </span>
                        @if(!$esCompletada)
                          <div class="progress gamificacion-progress d-inline-flex align-middle" style="width: 100px; height: 6px;">
                            <div class="progress-bar gamificacion-progress-bar" role="progressbar" style="width: {{ $mision['porcentaje'] }}%" aria-valuenow="{{ $mision['porcentaje'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        @endif
                      @endif
                    </div>
                  </div>
                </div>

                <!-- Columna Derecha: Recompensas (Puntos / Insignias) o Check de Cumplida -->
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                  @if($esCompletada)
                    <!-- Badge Verde con Check cuando está Cumplida -->
                    <div class="mision-completed-badge">
                      <i class="ti ti-check fs-5"></i>
                      <span>{{ $mision['estado'] === 'completada_hoy' ? 'Completada hoy' : 'Completada' }}</span>
                    </div>
                  @else
                    <!-- Recompensa de Insignia si aplica -->
                    @if($tieneInsignia)
                      <a href="{{ $meta['ruta'] && $meta['ruta'] !== '#' ? $meta['ruta'] : 'javascript:void(0)' }}" class="mision-insignia-pill text-decoration-none shadow-sm" title="Otorga la insignia: {{ $mision['insignia']->nombre }}">
                        @if($mision['insignia']->es_imagen && $mision['insignia']->imagen_url)
                          <img src="{{ $mision['insignia']->imagen_url }}" alt="{{ $mision['insignia']->nombre }}" style="width: 18px; height: 18px; border-radius: 4px; object-fit: cover;">
                        @else
                          <i class="{{ $mision['insignia']->icono_completo }}" style="color: {{ $mision['insignia']->icono_color ?: '#166534' }};"></i>
                        @endif
                        <span>{{ $mision['insignia']->nombre }}</span>
                      </a>
                    @endif

                    <!-- Recompensa de Puntos si aplica (Botón Verde Tipo Pill) -->
                    @if($tienePuntos)
                      <a href="{{ $meta['ruta'] && $meta['ruta'] !== '#' ? $meta['ruta'] : 'javascript:void(0)' }}" class="mision-points-pill text-decoration-none shadow-sm" title="{{ $meta['btnTexto'] }}">
                        <span>+ {{ number_format($mision['puntos_premio']) }} pts</span>
                      </a>
                    @elseif(!$tieneInsignia)
                      <!-- Fallback de Acción si no da puntos ni insignia visible -->
                      <a href="{{ $meta['ruta'] && $meta['ruta'] !== '#' ? $meta['ruta'] : 'javascript:void(0)' }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                        <span>{{ $meta['btnTexto'] }}</span>
                      </a>
                    @endif
                  @endif
                </div>

              </div>
            @endforeach
          </div>
        @endif
      </div>
    @endif

    <!-- ===================================================================== -->
    <!-- TAB 3: TIENDA DE CANJE (CATÁLOGO DE PRODUCTOS) -->
    <!-- ===================================================================== -->
    @if($tabActivo === 'tienda')
      <div>
        @if($productos->isEmpty())
          <div class="text-center py-5">
            <i class="ti ti-packages-off fs-1 text-black mb-2"></i>
            <h5 class="text-black">No hay artículos disponibles para canje en este momento.</h5>
            <p class="text-black small">Vuelve a consultar pronto o participa en más actividades para desbloquear recompensas exclusivas.</p>
          </div>
        @else
          <div class="row g-4">
            @foreach($productos as $producto)
              @php
                $costo = $producto->costo_puntos;
                $misPuntos = $usuario->puntos;
                $diferencia = max(0, $costo - $misPuntos);

                $conteoCanjes = $canjesPorProducto[$producto->id] ?? 0;
                $alcanzoLimite = ($producto->limite_por_usuario !== null) && ($conteoCanjes >= $producto->limite_por_usuario);
                $sinStock = ($producto->tipo === 'fisico') && ($producto->stock !== null) && ($producto->stock <= 0);

                $esCanjeable = ($misPuntos >= $costo) && !$sinStock && !$alcanzoLimite;
              @endphp

              <div class="col-12 col-md-6" wire:key="prod-canje-{{ $producto->id }}">
                <div class="tienda-producto-card {{ $esCanjeable ? 'is-canjeable' : '' }}">

                  <!-- Cabecera de la Tarjeta (Imagen o Ícono Centrado) -->
                  <div class="tienda-producto-header">
                    @if($producto->imagen_ruta)
                      <div class="tienda-producto-img-wrapper" role="button" onclick="window.mostrarModalImagen('{{ $producto->imagen_url }}', '{{ addslashes($producto->nombre) }}')" title="Clic para ampliar imagen">
                        <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}">
                        <span class="tienda-img-zoom-badge" title="Ampliar imagen">
                          <i class="ti ti-zoom-in"></i>
                        </span>
                      </div>
                    @else
                      <div class="tienda-producto-placeholder-box" style="background-color: #e6f7ec; color: #166534;" title="Artículo oficial">
                        <i class="ti ti-gift fs-1"></i>
                      </div>
                    @endif
                  </div>

                  <!-- Cuerpo de la Tarjeta -->
                  <div class="tienda-producto-body">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                      <h6 class="fw-bold text-dark mb-0 fs-6">{{ $producto->nombre }}</h6>
                      <span class="tienda-puntos-tag">
                        <i class="ti ti-coins text-warning"></i>
                        <span>{{ number_format($costo) }}</span>
                      </span>
                    </div>

                    <p class="text-black small mb-4 flex-grow-1" style="line-height: 1.4;">
                      {{ $producto->descripcion ?: 'Canjea tus puntos por este artículo oficial de la comunidad.' }}
                    </p>

                    <!-- Botón de Acción -->
                    <div class="mt-auto">
                      @if($sinStock)
                        <button type="button" class="btn btn-disabled-outline rounded-pill w-100 fw-semibold py-2" disabled>
                          Agotado
                        </button>
                      @elseif($alcanzoLimite)
                        <button type="button" class="btn btn-disabled-outline rounded-pill w-100 fw-semibold py-2" disabled>
                          Límite alcanzado
                        </button>
                      @elseif($esCanjeable)
                        <button type="button" class="btn btn-primary rounded-pill w-100 fw-bold py-2 shadow-sm waves-effect waves-light" wire:click="solicitarCanje({{ $producto->id }})" wire:loading.attr="disabled">
                          <span wire:loading.remove wire:target="solicitarCanje({{ $producto->id }})">¡Canjear Ahora!</span>
                          <span wire:loading wire:target="solicitarCanje({{ $producto->id }})" class="spinner-border spinner-border-sm me-1"></span>
                        </button>
                      @else
                        <button type="button" class="btn btn-disabled-outline rounded-pill w-100 fw-medium py-2" disabled>
                          @if($misPuntos == 0)
                            Saldo insuficiente
                          @else
                            Te faltan {{ number_format($diferencia) }} pts
                          @endif
                        </button>
                      @endif
                    </div>

                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    @endif

    <!-- ===================================================================== -->
    <!-- TAB 4: HISTORIAL DE CANJES Y PUNTOS -->
    <!-- ===================================================================== -->
    @if($tabActivo === 'historial')
      <div>
        <!-- Cabecera de Historial con Sub-Pestañas y Filtro de Fecha -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">

          <!-- Filtro de Rango de Fechas (Flatpickr: por defecto 90 días) -->
          <div class="d-flex align-items-center gap-2">
            <div class="input-group input-group-sm" style="max-width: 250px;" wire:ignore>
              <span class="input-group-text bg-white text-muted border-end-0">
                <i class="ti ti-calendar fs-6"></i>
              </span>
              <input type="text" id="filtro_historial_fechas" class="form-control bg-white border-start-0 ps-0" placeholder="Últimos 90 días" readonly style="cursor: pointer;"
                x-data="{
                  initFlatpickr() {
                    const el = this.$el;
                    const initFp = () => {
                      if (typeof flatpickr !== 'undefined') {
                        const fpH = flatpickr(el, {
                          mode: 'range',
                          dateFormat: 'Y-m-d',
                          defaultDate: [$wire.historialFechaInicio, $wire.historialFechaFin],
                          locale: { rangeSeparator: ' a ' },
                          onClose: function(selectedDates, dateStr, instance) {
                            if (selectedDates.length === 2) {
                              $wire.set('historialFechaInicio', instance.formatDate(selectedDates[0], 'Y-m-d'));
                              $wire.set('historialFechaFin', instance.formatDate(selectedDates[1], 'Y-m-d'));
                            } else if (selectedDates.length === 0) {
                              $wire.limpiarFiltroFechasHistorial();
                            }
                          }
                        });

                        Livewire.on('resetFlatpickrHistorial', (data) => {
                          const payload = Array.isArray(data) ? data[0] : data;
                          if (payload.fechaInicio && payload.fechaFin) {
                            fpH.setDate([payload.fechaInicio, payload.fechaFin]);
                          } else {
                            fpH.clear();
                          }
                        });
                      } else {
                        setTimeout(initFp, 150);
                      }
                    };
                    initFp();
                  }
                }"
                x-init="initFlatpickr()">
            </div>

            @if(!empty($historialFechaInicio) || !empty($historialFechaFin))
              <button class="btn btn-outline-secondary " type="button" wire:click="limpiarFiltroFechasHistorial" title="Restablecer a 90 días">
                <i class="ti ti-rotate-clockwise fs-6"></i>
              </button>
            @endif
          </div>
          <!-- Sub-pestañas en Cápsula (Movimientos y Mis Canjes) -->
          <div class="mision-subtabs-pill">
            <button type="button" class="mision-subtab-btn {{ $subtabHistorial === 'movimientos' ? 'active' : '' }}" wire:click="cambiarSubtabHistorial('movimientos')">
              Movimientos
            </button>
            <button type="button" class="mision-subtab-btn {{ $subtabHistorial === 'canjes' ? 'active' : '' }}" wire:click="cambiarSubtabHistorial('canjes')">
              Mis canjes ({{ $misSolicitudes->total() }})
            </button>
          </div>

        </div>

        <!-- =================================================================== -->
        <!-- SUB-TAB 1: MOVIMIENTOS DE PUNTOS (DISEÑO CLEAN) -->
        <!-- =================================================================== -->
        @if($subtabHistorial === 'movimientos')
          @if($misTransacciones->isEmpty())
            <div class="text-center py-5">
              <i class="ti ti-coin-off fs-1 text-black mb-2"></i>
              <h6 class="text-black fw-semibold mb-1">No hay movimientos registrados en este período.</h6>
              <p class="text-black small mb-0">Completa misiones o realiza actividades para empezar a sumar puntos.</p>
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-historial-movimientos align-middle">
                <thead>
                  <tr>
                    <th style="width: 25%;">Fecha</th>
                    <th style="width: 55%;">Actividad</th>
                    <th class="text-end" style="width: 20%;">Puntos</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($misTransacciones as $tx)
                    @php
                      $esHoy = $tx->created_at->isToday();
                      $esAyer = $tx->created_at->isYesterday();

                      if ($esHoy) {
                          $fechaFormato = 'Hoy, ' . $tx->created_at->format('h:i A');
                      } elseif ($esAyer) {
                          $fechaFormato = 'Ayer, ' . $tx->created_at->format('h:i A');
                      } else {
                          // Formato: 03 Ago, 10:00 AM
                          $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                          $mesCorto = $meses[$tx->created_at->month - 1] ?? '';
                          $fechaFormato = $tx->created_at->format('d') . ' ' . $mesCorto . ', ' . $tx->created_at->format('h:i A');
                      }

                      // Procesar título y subtítulo de la actividad
                      $tituloActividad = $tx->motivo;
                      $subtituloActividad = null;

                      if (str_starts_with($tx->motivo, 'Canje de producto:')) {
                          $tituloActividad = 'Canje en Tienda';
                          $subtituloActividad = trim(str_replace('Canje de producto:', '', $tx->motivo));
                      } elseif (str_starts_with($tx->motivo, 'Recompensa por')) {
                          $tituloActividad = trim(str_replace('Recompensa por', '', $tx->motivo));
                      } elseif (str_starts_with($tx->motivo, 'Recompensa única por')) {
                          $tituloActividad = trim(str_replace('Recompensa única por', '', $tx->motivo));
                      } elseif (str_starts_with($tx->motivo, 'Meta completada:')) {
                          $tituloActividad = trim(str_replace('Meta completada:', '', $tx->motivo));
                      }
                    @endphp
                    <tr>
                      <td>
                        <span class="historial-fecha-text text-black">{{ $fechaFormato }}</span>
                      </td>
                      <td>
                        <div class="historial-actividad-title text-black fw-semibold">{{ $tituloActividad }}</div>
                        @if($subtituloActividad)
                          <div class="small text-black">{{ $subtituloActividad }}</div>
                        @endif
                      </td>
                      <td class="text-end">
                        @if($tx->monto > 0)
                          <span class="historial-puntos-pos">+ {{ number_format($tx->monto) }}</span>
                        @else
                          <span class="historial-puntos-neg">- {{ number_format(abs($tx->monto)) }}</span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <!-- Paginación de Movimientos -->
            <div class="mt-4 d-flex justify-content-center">
              {{ $misTransacciones->links() }}
            </div>
          @endif
        @endif

        <!-- =================================================================== -->
        <!-- SUB-TAB 2: MIS CANJES REALIZADOS -->
        <!-- =================================================================== -->
        @if($subtabHistorial === 'canjes')
          @if($misSolicitudes->isEmpty())
            <div class="text-center py-5 border rounded-3 bg-light">
              <i class="ti ti-gift-off fs-1 text-muted mb-2"></i>
              <h6 class="text-dark fw-semibold mb-1">No has realizado canjes en este período.</h6>
              <p class="text-muted small mb-0">Visita la tienda de canjes para redimir tus puntos por premios exclusivos.</p>
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-historial-movimientos align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Código</th>
                    <th>Producto</th>
                    <th>Puntos</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th class="text-end">Entrega</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($misSolicitudes as $solicitud)
                    <tr>
                      <td>
                        <span class="fw-semibold font-monospace">{{ $solicitud->codigo_canje }}</span>
                      </td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          @if($solicitud->producto && $solicitud->producto->imagen_ruta)
                            <img src="{{ $solicitud->producto->imagen_url }}" alt="Prod" style="width: 34px; height: 34px; border-radius: 6px; object-fit: cover;">
                          @else
                            <div style="width: 34px; height: 34px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                              <i class="ti ti-package text-black"></i>
                            </div>
                          @endif
                          <div>
                            <span class="fw-semibold text-black d-block">{{ $solicitud->producto ? $solicitud->producto->nombre : 'Artículo eliminado' }}</span>
                            <span class="badge {{ ($solicitud->producto && $solicitud->producto->tipo === 'digital') ? 'bg-label-info' : 'bg-label-primary' }}" style="font-size: 0.68rem;">
                              {{ ($solicitud->producto && $solicitud->producto->tipo === 'digital') ? 'Digital' : 'Físico' }}
                            </span>
                          </div>
                        </div>
                      </td>
                      <td>
                        <span class="tienda-puntos-tag">
                          🪙 {{ number_format($solicitud->puntos_gastados) }}
                        </span>
                      </td>
                      <td>
                        <small class="text-black">{{ $solicitud->created_at->format('Y-m-d h:i A') }}</small>
                      </td>
                      <td>
                        @if($solicitud->estado === 'pendiente')
                          <span class="badge bg-label-warning">Pendiente</span>
                        @elseif($solicitud->estado === 'aprobado')
                          <span class="badge bg-label-success">Aprobado</span>
                        @elseif($solicitud->estado === 'rechazado')
                          <span class="badge bg-label-danger" title="{{ $solicitud->notas_admin }}">Rechazado (Reembolsado)</span>
                        @endif
                      </td>
                      <td class="text-end">
                        @if($solicitud->producto && $solicitud->producto->tipo === 'digital' && $solicitud->estado === 'aprobado' && $solicitud->producto->enlace_digital)
                          <a href="{{ $solicitud->producto->enlace_digital }}" target="_blank" class="btn btn-sm btn-primary rounded-pill">
                            <i class="ti ti-external-link me-1"></i> Abrir enlace
                          </a>
                        @elseif($solicitud->estado === 'pendiente')
                          <span class="text-muted small">Presentar código</span>
                        @else
                          <span class="text-muted small">-</span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <!-- Paginación de Canjes -->
            <div class="mt-4 d-flex justify-content-center">
              {{ $misSolicitudes->links() }}
            </div>
          @endif
        @endif

      </div>
    @endif

  </div>

  <!-- ======================================================================= -->
  <!-- JAVASCRIPT Y SWEETALERT2 PARA EL PANEL DE GAMIFICACIÓN -->
  <!-- ======================================================================= -->
  @script
  <script>
    // SweetAlert2: Modal para Confirmar Canje
    $wire.on('confirmarCanjeSwal', (data) => {
      const payload = Array.isArray(data) ? data[0] : data;

      let htmlContent = `
        <div class="text-start py-2">
          <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded bg-light border">
            <div style="font-size: 1.8rem;">🪙</div>
            <div>
              <div class="small text-muted">Costo del artículo:</div>
              <strong class="text-dark fs-6">${payload.costo} Puntos</strong>
            </div>
            <div class="ms-auto text-end">
              <div class="small text-muted">Saldo restante:</div>
              <strong class="text-success fs-6">${payload.puntosRestantes} pts</strong>
            </div>
          </div>
      `;

      if (payload.instrucciones && payload.instrucciones.trim() !== '') {
        htmlContent += `
          <div class="alert alert-info py-2 px-3 mb-0" style="font-size: 0.84rem;">
            <div class="fw-bold mb-1"><i class="ti ti-info-circle me-1"></i> Instrucciones de entrega:</div>
            <div>${payload.instrucciones}</div>
          </div>
        `;
      }

      if (payload.esDigital) {
        htmlContent += `
          <div class="alert alert-success py-2 px-3 mt-2 mb-0" style="font-size: 0.84rem;">
            <i class="ti ti-cloud-download me-1"></i> Al confirmar, recibirás tu enlace de descarga de forma inmediata.
          </div>
        `;
      }

      htmlContent += `</div>`;

      Swal.fire({
        title: `¿Canjear "${payload.nombre}"?`,
        html: htmlContent,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="ti ti-check me-1"></i> Sí, confirmar canje',
        cancelButtonText: 'Cancelar',
        customClass: {
          confirmButton: 'btn btn-primary me-3',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then((result) => {
        if (result.isConfirmed) {
          $wire.ejecutarCanje(payload.id);
        }
      });
    });

    // SweetAlert2: Canje Realizado Exitosamente
    $wire.on('canjeRealizadoExitosamente', (data) => {
      const payload = Array.isArray(data) ? data[0] : data;

      let htmlSuccess = `
        <div class="py-2">
          <p class="text-muted mb-3">Tu solicitud de canje por <strong>"${payload.nombre}"</strong> ha sido procesada con éxito.</p>

          <div class="p-3 bg-light rounded-3 border mb-3 text-center">
            <div class="small text-muted mb-1 text-uppercase fw-semibold">Tu Código de Canje</div>
            <div class="fs-3 fw-bold text-primary font-monospace tracking-wide">${payload.codigo}</div>
          </div>
      `;

      if (payload.esDigital && payload.enlaceDigital) {
        htmlSuccess += `
          <div class="mb-3">
            <a href="${payload.enlaceDigital}" target="_blank" class="btn btn-success w-100 rounded-pill py-2 fw-bold">
              <i class="ti ti-download me-1"></i> Abrir Enlace de Descarga
            </a>
          </div>
        `;
      } else if (payload.instrucciones) {
        htmlSuccess += `
          <div class="alert alert-secondary text-start py-2 px-3 small mb-0">
            <strong><i class="ti ti-map-pin me-1"></i> Para reclamar:</strong> ${payload.instrucciones}
          </div>
        `;
      }

      htmlSuccess += `</div>`;

      Swal.fire({
        title: '¡Felicitaciones! 🎉',
        html: htmlSuccess,
        icon: 'success',
        confirmButtonText: 'Entendido',
        customClass: {
          confirmButton: 'btn btn-primary'
        },
        buttonsStyling: false
      });
    });

    // SweetAlert2: Notificaciones Generales
    $wire.on('msn', (data) => {
      const payload = Array.isArray(data) ? data[0] : data;
      Swal.fire({
        icon: payload.icono || 'info',
        title: payload.titulo || 'Información',
        text: payload.texto || '',
        customClass: {
          confirmButton: 'btn btn-primary'
        },
        buttonsStyling: false
      });
    });

    // Función Global para Ampliar Foto del Producto
    window.mostrarModalImagen = function(url, titulo) {
      if (!url) return;
      const modalImg = document.getElementById('modalImagenSrc');
      const modalTit = document.getElementById('modalImagenTitulo');
      if (modalImg) modalImg.src = url;
      if (modalTit) modalTit.textContent = titulo || '';
      const modalEl = document.getElementById('modalAmpliarImagenProducto');
      if (modalEl && window.bootstrap) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
      }
    };
  </script>
  @endscript

  <!-- ======================================================================= -->
  <!-- MODAL: AMPLIAR IMAGEN DE PRODUCTO -->
  <!-- ======================================================================= -->
  <div class="modal fade" id="modalAmpliarImagenProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg" style="background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border-radius: 20px;">
        <div class="modal-body p-4 position-relative text-center">
          <button type="button" class="btn btn-icon btn-dark rounded-circle position-absolute top-0 end-0 m-3 shadow" data-bs-dismiss="modal" aria-label="Close" style="z-index: 1056; background: rgba(0, 0, 0, 0.6); border: 1px solid rgba(255,255,255,0.2);">
            <i class="ti ti-x text-white fs-5"></i>
          </button>

          <div class="d-flex align-items-center justify-content-center p-2 mb-2" style="min-height: 250px;">
            <img id="modalImagenSrc" src="" alt="Producto" class="img-fluid rounded-3 shadow-sm" style="max-height: 70vh; max-width: 100%; object-fit: contain; background: #ffffff;">
          </div>

          <h6 id="modalImagenTitulo" class="text-white fw-bold mb-0 mt-2"></h6>
        </div>
      </div>
    </div>
  </div>
</div>
