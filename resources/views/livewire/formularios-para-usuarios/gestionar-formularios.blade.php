<div>
  <div class="d-flex flex-row-reverse">
    <a href="{{ route('formularioUsuario.nuevo') }}" class="btn btn-primary rounded-pill px-7 py-2"><i class="ti ti-plus me-2"></i> Nuevo formulario </a>
  </div>

  <div class="row g-4 pt-10">
    <div class="col-10 col-md-5 offset-md-2 mt-3">
      <div class="input-group">
        <input wire:model.live.debounce.500ms="busqueda" type="text" class="form-control" id="busqueda" name="busqueda" placeholder="Buscar">
      </div>
    </div>

    <div class="col-12 col-md-3">
      <div class="form-check my-auto">
        <input class="form-check-input" type="checkbox" wire:model.live="conEliminados">
        <label class="form-check-label">
          ¿Mostrar ocultos?
        </label>
      </div>
    </div>
  </div>

  <div class="row g-4 mt-5">
    @if($formularios)
      @foreach( $formularios as $formulario )
      <div class="col-xl-4 col-lg-6 col-md-6">
        <div class="card shadow-1">
          <div class="card-body">


            <h5 class="mb-1 text-primary"> {{ $formulario->nombre }}</h5>

            <div class="d-flex flex-column mt-1">
              <div class="role-heading ">
                @if($formulario->validar_edad == true)
                <p class="py-0 text-black">Edades entre {{ $formulario->edad_minima }} a {{ $formulario->edad_maxima }} años </p>
                @else
                <p class="py-0 text-black">No posee validación de edad</p>
                @endif
                <p class="py-0 d-none"><i>{{ $formulario->descripcion }}</i></p>

                <div class="d-flex align-items-center avatar-group mb-2">
                  @if($formulario->roles->count() > 0)
                    @foreach ($formulario->roles()->take(5)->get() as $rol)
                      <div class="avatar">
                        <span class="avatar-initial rounded-circle pull-up text-heading" data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="{{ $rol->name }}">
                          <i class="{{ $rol->icono }}"></i>
                        </span>
                      </div>
                    @endforeach

                    @if($formulario->roles()->select('roles.id')->count() > 5)
                    <div class="avatar">
                      <span class="avatar-initial rounded-circle pull-up text-heading" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="3 more">
                      +{{ ($formulario->roles()->select('roles.id')->count()-5 ) }}
                      </span>
                    </div>
                    @endif
                  @else
                    <div class="avatar">
                      <span class="avatar-initial rounded-circle pull-up text-heading" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Roles no definidos">
                      <i class="ti ti-help-hexagon"></i>
                      </span>
                    </div>
                    Roles no definidos
                  @endif
                </div>

                <span class="text-white badge rounded-pill bg-primary ">Tipo: {{ $formulario->tipo->nombre}}</span>
              </div>
            </div>

            <div class="d-flex justify-content-end mt-3">
              <div>
                @if(!$formulario->trashed())
                <a href="{{ route('formularioUsuario.modificar', $formulario) }}" class="text-muted" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Editar formulario"><i class="ti ti-edit "></i></a>
                @endif
                <a href="javascript:void(0);" wire:click="duplicarFormulario({{ $formulario->id }})" class="text-muted" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Duplicar"><i class="ti ti-copy "></i></a>
                <a href="javascript:void(0);" wire:click="ocultarMostrar({{ $formulario->id }})" class="text-muted" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="{{ $formulario->deleted_at ? 'Mostrar formulario' : 'Ocultar formulario' }}"><i class="ti {{ $formulario->deleted_at ? 'ti-eye' : 'ti-eye-off' }} "></i></a>
                <a href="javascript:void(0);" wire:click="$dispatch('eliminar', {{ $formulario->id }})" class="text-muted" data-bs-toggle="tooltip" data-bs-toggle="tooltip" data-bs-placement="right" title="Eliminar"><i class="ti ti-trash "></i></a>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    @else
    <div class="py-4">
      <center>
        <i class="ti ti-browser fs-1 pb-1"></i>
        <h6 class="text-center">¡Ups! no hay formularios creados. </h6>
      </center>
    </div>
    @endif
  </div>
</div>

@assets
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss']);
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js']);
@endassets


@script
<script>
  $wire.on('msn', data => {
    Swal.fire({
      title: event.detail.msnTitulo,
      html: event.detail.msnTexto,
      icon: event.detail.msnIcono,
      customClass: {
          confirmButton: 'btn btn-primary'
      },
        buttonsStyling: false
    });
  });


  $wire.on('eliminar', formularioId => {

    Swal.fire({
      title: '¿Deseas eliminar este formulario?',
      text: "Esta acción no es reversible.",
      icon: 'warning',
      showCancelButton: true,
      focusConfirm: false,
      confirmButtonText: 'Si, eliminar',
      cancelButtonText: 'No',
      customClass: {
        confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
        cancelButton: 'btn btn-label-secondary waves-effect waves-light'
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
        $wire.eliminarFormulario(formularioId);

        Swal.fire({
          title: '¡Eliminado!',
          text: 'El formulario fue eliminado correctamente.',
          icon:'success',
          showCancelButton: false,
          focusConfirm: false,
          confirmButtonText: 'Aceptar',
          customClass: {
            confirmButton: 'btn btn-primary me-3 waves-effect waves-light'
          },
        })
      }
    })
  });
</script>
@endscript
