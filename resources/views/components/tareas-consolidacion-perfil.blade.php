<section class="card mt-4" aria-labelledby="tareas-consolidacion-perfil-titulo">
  <div class="card-header">
    <h6 id="tareas-consolidacion-perfil-titulo" class="card-title text-uppercase mb-0 fw-bold">
      <i class="ti ti-list-check ms-n1 me-2" aria-hidden="true"></i>Tareas de consolidación
    </h6>
  </div>
  <div class="card-body">
    @if($asignaciones->isEmpty())
      <p class="text-muted mb-0">Esta persona no tiene tareas de consolidación asignadas.</p>
    @else
      <ul class="timeline ms-1 mb-0">
        @foreach($asignaciones as $asignacion)
          @php
            $colorEstado = in_array($asignacion->estado?->color, ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'], true)
                ? $asignacion->estado->color
                : 'secondary';
          @endphp
          <li class="timeline-item timeline-item-transparent ps-4">
            <span class="timeline-point timeline-point-{{ $colorEstado }}"></span>
            <div class="timeline-event">
              <div class="timeline-header gap-2">
                <h6 class="mb-0 fw-bold">{{ $asignacion->tareaConsolidacion?->nombre ?? 'Tarea no disponible' }}</h6>
                <span class="badge rounded-pill bg-label-{{ $colorEstado }}">{{ $asignacion->estado?->nombre ?? 'Sin estado' }}</span>
              </div>
              <small class="text-muted">
                <i class="ti ti-calendar" aria-hidden="true"></i>
                Fecha: {{ $asignacion->fecha ? \Carbon\Carbon::parse($asignacion->fecha)->locale('es')->isoFormat('DD MMMM Y') : 'Sin fecha' }}
              </small>
            </div>
          </li>
        @endforeach
      </ul>
    @endif
  </div>
</section>
