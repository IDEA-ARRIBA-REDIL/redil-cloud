<div class="mx-10 mx-0">
  
    <!-- El listado se implementará pronto -->
    <div class="pb-5 ">
      <h5 id="offcanvasBirthdayLabel" class="offcanvas-title text-white fw-semibold">
        Próximos cumpleaños
      </h5> 
    </div>

  @if(isset($proximosCumpleanos) && $proximosCumpleanos->isNotEmpty())
    @foreach($proximosCumpleanos as $usuario)
    <div class="d-flex align-items-center mb-4">
      <div class="me-3">
        @if($usuario->foto && $usuario->foto != "default-m.png" && $usuario->foto != "default-f.png")
          <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$usuario->foto) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$usuario->foto }}" 
               alt="{{ $usuario->nombre(3) }}" 
               class="rounded-3" 
               style="width: 55px; height: 55px; object-fit: cover;">
        @else
          <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 55px; height: 55px; background-color: #EBE9FA;">
            <i class="ti ti-cake text-primary fs-3"></i>
          </div>
        @endif
      </div>
      <div class="d-flex flex-column ">
        <h6 class="mb-0 text-white text-uppercase lh-sm">{{ $usuario->nombre(3) }}</h6>
        <small class="text-white fw-light">
          @if($usuario->fecha_nacimiento)
            {{ $usuario->fecha_nacimiento->locale('es')->translatedFormat('d \d\e F') }}
          @else
            --
          @endif
        </small>
      </div>
    </div>
    @endforeach
  @else
    <p class="text-white-50 text-center py-5">No hay próximos cumpleaños para mostrar.</p>
  @endif

  <a href="{{ route('cumpleanos.listarCumpleanos') }}" class="btn btn-link waves-effect text-white p-0 fw-semibold mt-2 mb-10 pb-10"><u>Ver más cumpleaños</u></a>
 
</div>
