<div wire:ignore.self class="offcanvas offcanvas-end h-100 d-flex flex-column shadow-lg" tabindex="-1" id="offcanvasUltimaPeticion" aria-labelledby="offcanvasUltimaPeticionLabel" data-bs-backdrop="true" data-bs-scroll="false" style="width: 480px; max-width: 100%;">
  
  <!-- Encabezado del Offcanvas -->
  <div class="offcanvas-header border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-shrink-0">
    <div class="d-flex align-items-center gap-2">
      <span class="avatar avatar-sm rounded-circle bg-label-primary p-2 d-flex align-items-center justify-content-center">
        <i class="ti ti-pray fs-4"></i>
      </span>
      <div>
        <h5 class="offcanvas-title fw-semibold text-black mb-0" id="offcanvasUltimaPeticionLabel">Última petición</h5>
        <small class="text-black">Motivo de oración de la persona</small>
      </div>
    </div>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close" wire:click="cerrar"></button>
  </div>

  <!-- Cuerpo del Offcanvas -->
  <div class="offcanvas-body p-4 overflow-y-auto flex-grow-1">
    
    <!-- Spinner durante la carga -->
    <div wire:loading.flex wire:target="cargarUltimaPeticion" class="h-100 flex-column justify-content-center align-items-center py-5">
      <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="text-black fw-semibold">Consultando última petición...</p>
    </div>

    <!-- Contenido cuando ya cargó -->
    <div wire:loading.remove wire:target="cargarUltimaPeticion">
      @if($persona)
        
        <!-- Ficha Resumen de la Persona -->
        <div class="card border mb-4 rounded-3 p-3">
          <div class="d-flex align-items-center gap-3">

              @if(! $persona->tieneFoto())
              <div class="avatar avatar-xl">
                <span class="avatar-initial rounded-circle border border-3 border-white bg-info"> {{ $persona->inicialesNombre() }} </span>
              </div>
              @else
              <div class="avatar avatar-xl">
                <img src="{{ $persona->foto_url }}" alt="{{ $persona->foto }}" class="avatar-initial rounded-circle border border-3 border-white bg-info">
              </div>
              @endif
            <div class="flex-grow-1 min-w-0">
              <h6 class="fw-bold text-black mb-0 text-truncate">{{ $persona->nombre(3) }}</h6>
              <div class="d-flex flex-wrap gap-2 text-muted small mt-1">
                @if($persona->identificacion)
                  <span><i class="ti ti-id text-black me-1 "></i>{{ $persona->identificacion }}</span>
                @endif
                @if($persona->sede)
                  <span><i class="ti ti-building text-black me-1"></i>{{ $persona->sede->nombre }}</span>
                @endif
              </div>
            </div>
          </div>
        </div>

        @if($peticion)
          
          <!-- Metadatos de la Petición -->
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <span class="badge rounded-pill bg-label-primary fs-7">
                <i class="ti ti-tag me-1"></i>{{ $peticion->tipoPeticion ? $peticion->tipoPeticion->nombre : 'General' }}
              </span>
              
              @if($peticion->estado == 1)
                <span class="badge rounded-pill bg-label-warning fs-7"><i class="ti ti-clock me-1"></i>Pendiente</span>
              @elseif($peticion->estado == 3)
                <span class="badge rounded-pill bg-label-info fs-7"><i class="ti ti-loader me-1"></i>En proceso</span>
              @elseif($peticion->estado == 2)
                <span class="badge rounded-pill bg-label-success fs-7"><i class="ti ti-check me-1"></i>Cerrada</span>
              @endif

              @if($peticion->formularioUsuario)
                <span class="badge rounded-pill bg-label-info fs-7" title="Formulario: {{ $peticion->formularioUsuario->nombre }}">
                  <i class="ti ti-forms me-1"></i>Formulario
                </span>
              @endif
            </div>

            <small class="text-muted" title="{{ $peticion->created_at?->format('Y-m-d H:i') }}">
              <i class="ti ti-calendar me-1"></i>{{ $peticion->created_at?->diffForHumans() }}
            </small>
          </div>

          <!-- Detalles adicionales (Asignado a / Fecha de Registro) -->
          <div class="row g-2 mb-3 text-muted small">
            <div class="col-6">
              <div class="border rounded p-2 bg-white">
                <span class="d-block text-secondary small">Intercesor asignado:</span>
                <span class="fw-semibold text-dark text-truncate d-block" title="{{ $peticion->asignado ? $peticion->asignado->nombre(3) : 'Sin asignar' }}">
                  <i class="ti ti-user-heart me-1 text-primary"></i>{{ $peticion->asignado ? $peticion->asignado->nombre(3) : 'Sin asignar' }}
                </span>
              </div>
            </div>
            <div class="col-6">
              <div class="border rounded p-2 bg-white">
                <span class="d-block text-secondary small">Fecha de registro:</span>
                <span class="fw-semibold text-dark text-truncate d-block">
                  <i class="ti ti-calendar-event me-1 text-primary"></i>{{ $peticion->created_at ? $peticion->created_at->format('d-m-Y') : 'N/A' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Texto / Motivo de la Petición -->
          <div class="mb-4">
            <label class="form-label fw-bold text-dark text-uppercase small mb-2">
              <i class="ti ti-message-2 me-1 text-primary"></i>Motivo de Oración
            </label>
            <div class="p-3 bg-white border rounded-3 shadow-none text-dark" style="font-size: 0.95rem; line-height: 1.6; max-height: 250px; overflow-y: auto;">
              {!! nl2br(e($peticion->descripcion)) !!}
            </div>
          </div>

          <!-- Respuesta pastoral si existe -->
          @if(!empty($peticion->respuesta))
            <div class="mb-4">
              <label class="form-label fw-bold text-dark text-uppercase small mb-2">
                <i class="ti ti-message-share me-1 text-success"></i>Respuesta Pastoral
              </label>
              <div class="p-3 bg-label-success border border-success-subtle rounded-3 text-dark" style="font-size: 0.9rem; line-height: 1.5;">
                {!! $peticion->respuesta !!}
              </div>
            </div>
          @endif

          <!-- Seguimientos si existen -->
          @if($peticion->seguimientos && $peticion->seguimientos->isNotEmpty())
            <div class="mb-3">
              <label class="form-label fw-bold text-dark text-uppercase small mb-2">
                <i class="ti ti-notes me-1 text-info"></i>Historial de Seguimientos ({{ $peticion->seguimientos->count() }})
              </label>
              <div class="d-flex flex-column gap-2" style="max-height: 200px; overflow-y: auto;">
                @foreach($peticion->seguimientos->sortByDesc('created_at') as $seg)
                  <div class="p-2 bg-white border rounded small">
                    <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.75rem;">
                      <span><b>{{ $seg->usuarioCreacion ? $seg->usuarioCreacion->nombre(3) : 'Intercesor' }}</b></span>
                      <span>{{ $seg->created_at?->diffForHumans() }}</span>
                    </div>
                    <div class="text-dark">{!! $seg->descripcion !!}</div>
                  </div>
                @endforeach
              </div>
            </div>
          @endif

        @else
          <!-- Estado Vacío: Sin peticiones -->
          <div class="text-center py-5 px-3">
            <div class="avatar avatar-xl bg-label-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 75px; height: 75px;">
              <i class="ti ti-pray text-secondary" style="font-size: 2.5rem;"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">Sin peticiones de oración</h5>
            <p class="text-muted small mb-0">Esta persona no registra peticiones de oración en el sistema.</p>
          </div>
        @endif

      @endif
    </div>

  </div>

  <!-- Pie del Offcanvas -->
  <div class="offcanvas-footer border-top p-3 bg-white flex-shrink-0 d-flex justify-content-between align-items-center">
    @php
      $telefonoWhatsApp = null;
      if ($persona) {
        $rawTel = $persona->telefono_movil ?: $persona->telefono_otro;
        $telefonoWhatsApp = preg_replace('/[^0-9]/', '', (string)$rawTel);
        if (strlen($telefonoWhatsApp) == 10 && str_starts_with($telefonoWhatsApp, '3')) {
          $telefonoWhatsApp = '57' . $telefonoWhatsApp;
        }
      }
    @endphp

    <div>
      @if($persona && $peticion && $telefonoWhatsApp)
        <a href="https://wa.me/{{ $telefonoWhatsApp }}?text={{ urlencode('Hola ' . $persona->primer_nombre . ', te saludamos de la iglesia. Esperamos que te encuentres muy bien, queríamos comentarte que estamos orando por tu petición.') }}" 
           target="_blank" 
           class="btn btn-sm btn-success rounded-pill px-3">
          <i class="ti ti-brand-whatsapp me-1 fs-5"></i> Contactar por WhatsApp
        </a>
      @endif
    </div>

    <button type="button" class="btn btn-sm btn-label-secondary rounded-pill px-4" data-bs-dismiss="offcanvas" aria-label="Close" wire:click="cerrar">
      Cerrar
    </button>
  </div>
</div>
