@if($grupo->tipoGrupo && $grupo->tipoGrupo->tiene_icono)
  <div class="card rounded-circle icon-card text-center mb-0 mx-3 p-1 d-flex align-items-center justify-content-center shadow-sm overflow-hidden"
       style="background-color: {{ $grupo->tipoGrupo->color ?? '#ffffff' }}; width: 100px; height: 100px; min-width: 100px;">
    <img src="{{ $grupo->tipoGrupo->imagen_url }}"
         alt="{{ $grupo->tipoGrupo->nombre }}"
         class="rounded-circle"
         style="width: 100%; height: 100%; object-fit: cover;">
  </div>
@else
  <div class="card rounded-circle icon-card text-center mb-0 mx-3 p-2"
       style="background-color: {{ $grupo->tipoGrupo->color ?? '#6c757d' }}">
    <div class="card-body text-white">
      <i class="ti ti-users-group ti-xl"></i>
    </div>
  </div>
@endif
