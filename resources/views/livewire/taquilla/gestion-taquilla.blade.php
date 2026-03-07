<div>



  {{-- Cabecera de Taquilla Activa --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-0 fw-semibold text-primary">Taquilla de Pago</h4>
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
      <h5 class="card-title mb-0">1. Verificación de Asistente</h5>
      <small class="card-subtitle">Busca al comprador y la actividad para verificar los requisitos.</small>
    </div>
    <div class="card-body">
      
      <form 
        id="form-verificar-requisitos" 
        wire:submit.prevent="verificarRequisitos"
      >
        
        <div class="row g-3">
          
          {{-- 1. Buscador de Usuario (El Comprador) --}}
          <div class="col-md-6">
            <label for="user_id" class="form-label">Buscar al comprador (por nombre o ID)</label>
            @livewire('usuarios.usuarios-para-busqueda', [
                'id' => 'user_id', 
                'tipoBuscador' => 'unico',
                'conDadosDeBaja' => 'no',
                'class' => 'col-12',
                'placeholder' => 'Buscar por nombre o identificación...',
                'queUsuariosCargar' => 'todos',
                'usuarioSeleccionadoId' => $compradorIdActual,
                'obligatorio' => true,
            ])
            @error('compradorIdActual') <span class="text-danger small">{{ $message }}</span> @enderror
          </div>

          {{-- 2. Selector de Actividad --}}
          <div class="col-md-6">
            <label for="actividad_id" class="form-label">Selecciona la actividad</label>
            <div wire:ignore>
              <select 
                id="actividad_id" 
                wire:model.defer="actividadIdActual"
                class="form-select select2-livewire" {{-- La clase es solo un nombre --}}
                required>
                <option value="">Selecciona una actividad...</option>
                @foreach($actividadesDisponibles as $actividad)
                  <option value="{{ $actividad->id }}">{{ $actividad->nombre }}</option>
                @endforeach
              </select>
            </div>
            @error('actividadIdActual') <span class="text-danger small">{{ $message }}</span> @enderror
          </div>

          {{-- LÓGICA DE PARIENTES --}}
          @if($comprador && $parientes->isNotEmpty())
            <div class="col-12">
              <label class="form-label">¿La inscripción es para el comprador?</label>
              <div class="d-flex">
                {{-- Radio "Sí" --}}
                <div class="form-check me-3">
                  <input 
                    class="form-check-input" 
                    type="radio" 
                    name="inscripcion_propia" 
                    id="inscripcion-propia-si" 
                    value="1" 
                    wire:model.live="esInscripcionPropia"
                    >
                  <label class="form-check-label" for="inscripcion-propia-si">
                    Sí, a nombre propio ({{ $comprador->nombre(3) }})
                  </label>
                </div>
                {{-- Radio "No" --}}
                <div class="form-check">
                  <input 
                    class="form-check-input" 
                    type="radio" 
                    name="inscripcion_propia" 
                    id="inscripcion-propia-no" 
                    value="0"
                    wire:model.live="esInscripcionPropia"
                    >
                  <label class="form-check-label" for="inscripcion-propia-no">
                    No, inscribir a un familiar
                  </label>
                </div>
              </div>
            </div>
            
            {{-- El <select> de Parientes --}}
            @if(!$esInscripcionPropia)
              <div class="col-12" x-transition>
                <label for="pariente-id-select" class="form-label">Selecciona el familiar a inscribir*</label>
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
          <button type="button" wire:click="limpiar" class="btn btn-outline-secondary rounded-pill">
            Limpiar
          </button>
        </div>

      </form>
    </div>
  </div>

  {{-- 
    SECCIÓN 2: Resultados de Livewire
  --}}
  @if($verificacionEnviada) 
    @if($usuarioAValidar && $actividadSeleccionada)
      {{-- Cargamos el componente de validación --}}
      @livewire('taquilla.validar-inscripcion', [
          'comprador' => $comprador,
          'usuario' => $usuarioAValidar,
          'actividad' => $actividadSeleccionada,
          'cajaActiva' => $cajaActiva
      ])
    @else
      {{-- Mostramos el error si faltan datos --}}
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
    {{-- 
      ==================================================================
      ¡INICIO DE LA CORRECCIÓN DE JS!
      ==================================================================
    --}}
    document.addEventListener('DOMContentLoaded', function () {
      
      // --- 1. Inicialización de Select2 ---
      const selectActividad = $('#actividad_id');
      
      if (selectActividad.length) {
        selectActividad.select2({
          placeholder: 'Selecciona una actividad...',
          allowClear: true
        });
        
        // --- 2. Listener: Select2 -> Livewire ---
        // (Envía el valor a Livewire CADA VEZ que seleccionas una actividad)
        selectActividad.on('change', function (e) {
          // '@this.set' es una directiva Blade que funciona aquí
          @this.set('actividadIdActual', e.target.value);
        });
      }
      
      // --- 3. Listener: Livewire -> Select2 ---
      // (Escucha el evento 'reset' del método 'limpiar' de Livewire)
      @this.on('reset', () => {
        if (selectActividad.length) {
          selectActividad.val(null).trigger('change');
        }
      });
    });

    // --- 4. Listeners Globales de Livewire ---
    // (Estos se inicializan una vez que Livewire está listo)
    document.addEventListener('livewire:initialized', () => {
        
        {{-- 
          ¡CORRECCIÓN!
          Cambiamos 'Livewire.on' por '@this.on'.
          Esta es la sintaxis correcta para escuchar eventos
          DENTRO de la vista de un componente Livewire.
        --}}
        @this.on('notificacion', (event) => {
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
        
        {{-- 
          ¡CORRECCIÓN!
          Cambiamos 'Livewire.on' por '@this.on'.
          Esto escucha el evento 'usuario-seleccionado'
          emitido desde 'usuarios-para-busqueda'
        --}}
        @this.on('usuario-seleccionado', (event) => {
            // Re-despacha el evento al método 'cargarParientes'
            // en 'GestionTaquilla.php'
            @this.dispatch('usuario-seleccionado', { id: event.id });
        });
        
        // (Este listener ya era correcto)
        @this.on('resetear-buscador-usuario', () => {
            // Emite un evento global para el componente hijo
            Livewire.dispatch('reset-buscador'); 
        });
    });
    {{-- =================================================================== --}}
    {{-- ¡FIN DE LA CORRECCIÓN DE JS! --}}
    {{-- =================================================================== --}}
  </script>
  @endpush

</div>