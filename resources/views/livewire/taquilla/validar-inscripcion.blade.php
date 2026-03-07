<div>
  {{--
    Este es el nuevo archivo 'padre'.
    El 'layoutMaster' se define en el método render() del componente.
  --}}

  {{-- Cabecera de Taquilla Activa --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-0 fw-semibold text-primary">Taquilla de pago</h4>
      <p class="mb-0">
        Operando desde: <strong class="text-dark">{{ $cajaActiva->nombre }}</strong>
        ({{ $cajaActiva->puntoDePago->nombre }})
      </p>
    </div>
    <a href="{{ route('taquilla.mis-cajas') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
      <i class="ti ti-logout me-1"></i>
      Cambiar de caja
    </a>
  </div>

  @include('layouts.status-msn')

  {{--
    SECCIÓN 1: Formulario de Búsqueda (AHORA CON LIVEWIRE)
  --}}
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">1. Verificación de asistente</h5>
      <small class="card-subtitle">Busca al comprador y la actividad para verificar los requisitos.</small>
    </div>
    <div class="card-body">

      {{-- ¡CAMBIO! El formulario ahora usa 'wire:submit.prevent' --}}
      <form
        id="form-verificar-requisitos"
        wire:submit.prevent="verificarRequisitos"
      >

        <div class="row g-3">

          {{-- 1. Buscador de Usuario (El Comprador) --}}
          <div class="col-md-6">
            <label for="user_id" class="form-label">Buscar al comprador (por nombre o ID)</label>
            {{--
              ¡CAMBIO IMPORTANTE!
              Quitamos 'redirect' y 'redirectParams'.
              Ahora este componente emitirá 'usuario-seleccionado'
              y este componente 'padre' lo escuchará.

            --}}
            @livewire('usuarios.usuarios-para-busqueda', [
                'id' => 'user_id',
                'tipoBuscador' => 'unico',
                'conDadosDeBaja' => 'no',
                'class' => 'col-12',
                'placeholder' => 'Buscar por nombre o identificación...',
                'queUsuariosCargar' => 'todos',
                'usuarioSeleccionadoId' => $compradorIdActual,
                // 'redirect' => 'taquilla.gestionar', // <-- ELIMINADO
                // 'redirectParams' => ...          // <-- ELIMINADO
                'obligatorio' => true,
            ])
            @error('compradorIdActual') <span class="text-danger small">{{ $message }}</span> @enderror
          </div>

          {{-- 2. Selector de Actividad --}}
          <div class="col-md-6">
            <label for="actividad_id" class="form-label">Selecciona la actividad</label>
            {{-- ¡CAMBIO! Usamos 'wire:model' para vincular la propiedad --}}
            <div wire:ignore> {{-- wire:ignore sigue siendo necesario para Select2 --}}
              <select
                id="actividad_id"
                wire:model.defer="actividadIdActual" {{-- .defer para que no recargue --}}
                class="form-select select2-livewire"
                required>
                <option value="">Selecciona una actividad...</option>
                @foreach($actividadesDisponibles as $actividad)
                  <option value="{{ $actividad->id }}">{{ $actividad->nombre }}</option>
                @endforeach
              </select>
            </div>
            @error('actividadIdActual') <span class="text-danger small">{{ $message }}</span> @enderror
          </div>

          {{--
            LÓGICA DE PARIENTES
            Este bloque ahora aparecerá instantáneamente
            cuando $parientes se pueble por el listener.
          --}}
          @if($comprador && $parientes->isNotEmpty())
            <div class="col-12">
              <label class="form-label">¿La inscripción es para el comprador?</label>
              <div class="d-flex">
                {{-- Radio "Sí" (Nombre Propio) --}}
                <div class="form-check me-3">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="inscripcion_propia"
                    id="inscripcion-propia-si"
                    value="1"
                    wire:model.live="esInscripcionPropia" {{-- .live para reactividad --}}
                    >
                  <label class="form-check-label" for="inscripcion-propia-si">
                    Sí, a nombre propio ({{ $comprador->nombre(3) }})
                  </label>
                </div>
                {{-- Radio "No" (Pariente) --}}
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="inscripcion_propia"
                    id="inscripcion-propia-no"
                    value="0"
                    wire:model.live="esInscripcionPropia" {{-- .live para reactividad --}}
                    >
                  <label class="form-check-label" for="inscripcion-propia-no">
                    No, inscribir a un familiar
                  </label>
                </div>
              </div>
            </div>

            {{--
              El <select> de Parientes.
              Se muestra solo si 'esInscripcionPropia' es falso (0).
            --}}
            @if(!$esInscripcionPropia)
              <div class="col-12" x-transition>
                <label for="pariente-id-select" class="form-label">Selecciona el familiar a inscribir*</label>
                {{-- ¡CAMBIO! Usamos 'wire:model' --}}
                <select
                  id="pariente-id-select"
                  class="form-select"
                  wire:model="inscritoIdActual"
                  required
                  >
                  <option value="">Selecciona un familiar...</option>
                  @foreach($parientes as $pariente)
                    <option value="{{ $pariente->id }}">
                      {{ $pariente->nombre(3) }}
                      ({{ $comprador->genero == 0 ? $pariente->nombre_masculino : $pariente->nombre_femenino }})
                    </option>
                  @endforeach
                </select>
                @error('inscritoIdActual') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
            @endif
          @endif
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary rounded-pill me-2">
            <i class="ti ti-search me-1"></i>
            Verificar requisitos
          </button>
          {{-- ¡CAMBIO! El botón Limpiar ahora llama a un método de Livewire --}}
          <button type="button" wire:click="limpiar" class="btn btn-outline-secondary rounded-pill">
            Limpiar
          </button>
        </div>

      </form>
    </div>
  </div>

  {{--
    SECCIÓN 2: Resultados de Livewire (¡MODIFICADO!)
  --}}
  {{--
    Usamos la bandera 'verificacionEnviada'.
    Esto solo se muestra si el botón "Verificar" se presionó.
  --}}
  @if($verificacionEnviada)
    @if($usuarioAValidar && $actividadSeleccionada)
      {{--
        CASO A: Verificación enviada Y exitosa.
        (Cargamos Livewire 'ValidarInscripcion')
      --}}
      @livewire('taquilla.validar-inscripcion', [
          'comprador' => $comprador,
          'usuario' => $usuarioAValidar,
          'actividad' => $actividadSeleccionada,
          'cajaActiva' => $cajaActiva
      ])
    @else
      {{--
        CASO B: Verificación enviada Y fallida.
        (Mostramos el error)
      --}}
      <div class="alert alert-danger" role="alert">
        <h5 class="alert-heading">Error en la verificación</h5>
        <p class="mb-0">
          No se pudieron cargar los datos. Asegúrate de que tanto el comprador, el inscrito (si aplica) y la actividad estén seleccionados correctamente.
        </p>
      </div>
    @endif
  @endif


  {{-- Push de scripts --}}
  @push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    $('#actividad_id').select2({
      placeholder: 'Selecciona una actividad...',
      allowClear: true
    });
  });

  // -------------------------------------------------------------
    // ¡NUEVO LISTENER PARA NOTIFICACIONES DE SWEETALERT!
    // -------------------------------------------------------------
    // Este bloque "escucha" cualquier evento 'notificacion' disparado
    // desde CUALQUIER componente Livewire en esta página (en este
    // caso, 'ValidarInscripcion').
    // -------------------------------------------------------------
    // ¡AQUÍ ESTÁ LA CORRECCIÓN!
    // -------------------------------------------------------------
    document.addEventListener('livewire:initialized', () => {

        // ¡CAMBIO!

        // script se encuentra en la vista de Blade padre, no
        // en la vista del componente Livewire.
        Livewire.on('notificacion', (event) => {


             Swal.fire({
                        title: event.titulo,
                        text: event.mensaje,
                        icon: event.tipo,
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',

                        cancelButtonText: 'Cerrar'
                    })
        });

         // ¡NUEVO LISTENER!
          // Esto escucha el evento de 'usuarios-para-busqueda' y
          // lo re-emite al componente padre (GestionTaquilla)
          Livewire.on('usuario-seleccionado', (event) => {
              @this.dispatch('usuario-seleccionado', { id: event.id });
          });

          // ¡NUEVO LISTENER!
          // Escucha el evento de 'limpiar'
          @this.on('resetear-buscador-usuario', () => {
              Livewire.dispatch('reset-buscador'); // Asumiendo que tu buscador escucha 'reset-buscador'
          });

    }); // Fin de livewire:initialized
</script>
@endpush

</div>
