<?php

namespace App\Livewire\Consolidacion;

use App\Models\EstadoTareaConsolidacion;
use App\Models\HistorialTareaConsolidacionUsuario;
use App\Models\TareaConsolidacion;
use App\Models\TareaConsolidacionUsuario;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Carbon\Carbon;
use Livewire\Attributes\On;


class GestionarTareas extends Component
{
   // Propiedad para mantener al usuario actual
  public User $usuario;

  public $historialActivo = [];
  public $tareaActiva;

  public $nuevoDetalleHistorial = '';
  public $historialFecha;

  // Propiedades para los selects del formulario
  public $tareas = [];
  public $estados = [];

  // Propiedades vinculadas al formulario (wire:model)
  public $tareaSeleccionada;
  public $estadoSeleccionado;
  public $fecha_tarea;
  public $detalle_tarea;

  // Propiedad para controlar el título del modal
  public $modoEdicion = false;
  public $tareaAsignadaId;


  public function mount()
  {
      $this->estados = EstadoTareaConsolidacion::orderBy('puntaje', 'asc')->get();
      //$this->tareas = TareaConsolidacion::orderBy('orden')->get();
  }

  #[On('crearTarea')]
  public function crearTarea($personaId)
  {
    $persona = User::find($personaId);
    $this->usuario = $persona;

    $this->resetErrorBag();
    $this->modoEdicion = false;
    $this->reset(['tareaSeleccionada', 'estadoSeleccionado', 'fecha_tarea', 'detalle_tarea', 'modoEdicion', 'tareaAsignadaId']);

   // 1. Obtenemos los IDs de las tareas que el usuario YA tiene asignadas
    $tareasAsignadasIds = $this->usuario->tareasConsolidacion()->pluck('tarea_consolidacion_id');

    // 2. Cargamos en la propiedad $tareas solo aquellas que NO están en la lista de asignadas
    $tareasDisponibles = TareaConsolidacion::where('default', false) // <-- ¡AQUÍ ESTÁ EL NUEVO FILTRO!
                          ->whereNotIn('id', $tareasAsignadasIds)
                          ->orderBy('orden')
                          ->get();

    // ----> AQUÍ ESTÁ LA NUEVA LÓGICA <----
    // 3. Verificamos si la colección de tareas disponibles está vacía
    if ($tareasDisponibles->isEmpty()) {
        // Si no hay tareas, enviamos una alerta y detenemos la ejecución.
        $this->dispatch('msn',
            msnTitulo: '¡Ya estan todas las tareas asignadas!',
            msnTexto: 'Este usuario ya tiene todas las tareas disponibles asignadas.',
            msnIcono: 'info'
        );
        return; // Detenemos la función aquí.
    }

    // 4. Si hay tareas disponibles, continuamos con el flujo normal
    $this->tareas = $tareasDisponibles->sortBy('nombre');

    // Guardamos la fecha de hoy en una variable
    $fechaHoy = now()->format('Y-m-d');

    // Establecemos la propiedad en el backend
    $this->fecha_tarea = $fechaHoy;

    // Despachamos un evento para abrir el modal Y le pasamos la fecha
    $this->dispatch('abrirModal',
        nombreModal: 'modalcrearEditarTarea',
        fecha: $fechaHoy
    );
  }

  #[On('editarTarea')]
  public function editarTarea($tareaAsignadaId)
  {

    $this->resetErrorBag();
    $tarea = TareaConsolidacionUsuario::findOrFail($tareaAsignadaId);

    $this->tareas = TareaConsolidacion::orderBy('orden')->get();

    $this->tareaAsignadaId = $tarea->id;
    $this->modoEdicion = true;

    $this->tareaSeleccionada = $tarea->tarea_consolidacion_id;
    $this->estadoSeleccionado = $tarea->estado_tarea_consolidacion_id;

    $fechaParaEditar = Carbon::parse($tarea->fecha)->format('Y-m-d');
    $this->fecha_tarea = $fechaParaEditar;

    // Abrimos el modal y le PASAMOS la fecha que debe mostrar.
    $this->dispatch('abrirModal',
        nombreModal: 'modalcrearEditarTarea',
        fecha: $fechaParaEditar
    );
  }

  public function guardarTarea()
  {
      if ($this->modoEdicion) {
          // --- LÓGICA PARA ACTUALIZAR ---
          $rules = [
              'estadoSeleccionado' => 'required|exists:estados_tarea_consolidacion,id',
              'fecha_tarea'        => 'required|date',
          ];
          $messages = [
              'estadoSeleccionado.required' => 'El campo estado es obligatorio.',
              'fecha_tarea.required'        => 'El campo fecha es obligatorio.',
          ];

          $validated = $this->validate($rules, $messages);

          // Buscamos la asignación y la actualizamos
          $tareaParaEditar = TareaConsolidacionUsuario::findOrFail($this->tareaAsignadaId);
          $tareaParaEditar->update([
              'estado_tarea_consolidacion_id' => $validated['estadoSeleccionado'],
              'fecha'                         => $validated['fecha_tarea'],
          ]);

          // Enviamos feedback al usuario
          $this->dispatch('cerrarModal', nombreModal: 'modalcrearEditarTarea');
          $this->dispatch('msn', msnTitulo: '¡Actualizado!', msnTexto: 'La tarea ha sido actualizada correctamente.', msnIcono: 'success');

          //$this->dispatch('refrescar-lista');
          $this->dispatch('refrescar-y-enfocar', personaId: $tareaParaEditar->user_id);

      } else {
          // --- LÓGICA PARA CREAR (la que ya tenías) ---
          $rules = [
              'tareaSeleccionada'  => 'required|exists:tareas_consolidacion,id',
              'estadoSeleccionado' => 'required|exists:estados_tarea_consolidacion,id',
              'fecha_tarea'        => 'required|date',
              'detalle_tarea'      => 'nullable|string|min:5',
          ];
          $messages = [
              'tareaSeleccionada.required'  => 'El campo tarea es obligatorio.',
              'estadoSeleccionado.required' => 'El campo estado es obligatorio.',
              'fecha_tarea.required'        => 'El campo fecha es obligatorio.',
          ];

          $validated = $this->validate($rules, $messages);

          DB::transaction(function () use ($validated) {
              $nuevaTarea = TareaConsolidacionUsuario::create([
                  'user_id'                       => $this->usuario->id,
                  'tarea_consolidacion_id'        => $validated['tareaSeleccionada'],
                  'estado_tarea_consolidacion_id' => $validated['estadoSeleccionado'],
                  'fecha'                         => $validated['fecha_tarea'],
              ]);

              if (!empty($validated['detalle_tarea'])) {
                  $nuevaTarea->historial()->create([
                      'detalle'             => $validated['detalle_tarea'],
                      'fecha'               => $validated['fecha_tarea'],
                      'usuario_creacion_id' => auth()->id(),
                  ]);
              }
          });

          $this->dispatch('cerrarModal', nombreModal: 'modalcrearEditarTarea');
          $this->dispatch('msn', msnTitulo: '¡Éxito!', msnTexto: 'La tarea ha sido asignada correctamente.', msnIcono: 'success');
          //$this->dispatch('refrescar-lista');
          $this->dispatch('refrescar-y-enfocar', personaId: $this->usuario->id);
      }

      // Reseteamos las propiedades del formulario al final
      $this->reset(['tareaSeleccionada', 'estadoSeleccionado', 'fecha_tarea', 'detalle_tarea', 'modoEdicion', 'tareaAsignadaId']);
  }

  #[On('eliminarTareaConfirmada')]
  public function eliminarTarea($id)
  {
      try {
          // Usamos una transacción para asegurar que ambas eliminaciones ocurran o ninguna
          $asignacion = TareaConsolidacionUsuario::findOrFail($id);
          $idUser = $asignacion->user_id;

          DB::transaction(function () use ($asignacion ) {

              //Usamos la relación para eliminar todos sus historiales
              $asignacion->historial()->delete();

              //Eliminamos la asignación de la tarea en sí
              $asignacion->delete();
          });

          // 4. Enviamos una notificación de éxito
          $this->dispatch('msn',
              msnTitulo: '¡Eliminada!',
              msnTexto: 'La tarea y todos sus historiales han sido eliminados.',
              msnIcono: 'success'
          );
          // $this->dispatch('refrescar-lista');
           $this->dispatch('refrescar-y-enfocar', personaId: $idUser);

      } catch (\Exception $e) {
          // Si algo falla, notificamos al usuario
          $this->dispatch('msn',
              msnTitulo: 'Error',
              msnTexto: 'Ocurrió un error al eliminar la tarea.',
              msnIcono: 'error'
          );
      }
  }

  #[On('actualizarEstado')]
  public function actualizarEstado($tareaAsignadaId, $nuevoEstadoId)
  {
    // 1. Buscamos la asignación de tarea que vamos a actualizar
    $asignacion = TareaConsolidacionUsuario::findOrFail($tareaAsignadaId);

    // 2. Actualizamos el estado y la fecha (a la fecha actual)
    $asignacion->update([
        'estado_tarea_consolidacion_id' => $nuevoEstadoId,
        'fecha'                         => now(),
    ]);

    // 3. (Buena práctica) Añadimos un registro al historial sobre este cambio
    $nuevoEstadoNombre = EstadoTareaConsolidacion::find($nuevoEstadoId)->nombre;
    $asignacion->historial()->create([
        'detalle' => 'El estado fue cambiado a: "' . $nuevoEstadoNombre . '"',
        'fecha'   => now(),
        'usuario_creacion_id' => auth()->id(),
    ]);

    // 4. Enviamos una notificación de éxito
    $this->dispatch('msn',
        msnTitulo: '¡Estado Actualizado!',
        msnTexto: 'La tarea se ha actualizado correctamente.',
        msnIcono: 'success'
    );

     //$this->dispatch('refrescar-lista');
     $this->dispatch('refrescar-y-enfocar', personaId: $asignacion->user_id);
  }

  #[On('crearTareaDefault')]
  public function crearTareaDefault ($personaId, $tareaId, $nuevoEstadoId)
  {

    TareaConsolidacionUsuario::create([
        'user_id'                       => $personaId,
        'tarea_consolidacion_id'        => $tareaId,
        'estado_tarea_consolidacion_id' => $nuevoEstadoId,
        'fecha'                         => now(),
    ]);

    $this->dispatch('cerrarModal', nombreModal: 'modalcrearEditarTarea');
    $this->dispatch('msn', msnTitulo: '¡Éxito!', msnTexto: 'La tarea ha sido asignada correctamente.', msnIcono: 'success');
    //$this->dispatch('refrescar-lista');
    $this->dispatch('refrescar-y-enfocar', personaId: $personaId);
  }

  #[On('ver-historial-tarea')]
  public function verHistorial($tareaAsignadaId)
  {
      // Buscamos la asignación y cargamos sus relaciones para ser eficientes
      $tareaUsuario = TareaConsolidacionUsuario::with([
                          'historial' => fn($query) => $query->orderBy('fecha', 'desc'),
                          'historial.creador',
                          'tareaConsolidacion'
                      ])
                      ->findOrFail($tareaAsignadaId);

      // Asignamos los datos a las propiedades públicas
      $this->tareaActiva = $tareaUsuario->tareaConsolidacion;
      $this->historialActivo = $tareaUsuario->historial;

      $this->tareaAsignadaId = $tareaAsignadaId;

      $fechaHoy = Carbon::now()->format('Y-m-d');
      $this->historialFecha = $fechaHoy;

      $this->dispatch('abrirModal', nombreModal: 'modalVerHistorial', fecha: $fechaHoy);
  }

  public function agregarNuevoHistorial()
  {
      // 1. Validamos que el campo no esté vacío
      $this->validate([
        'nuevoDetalleHistorial' => 'required|string|min:3',
        'historialFecha'         => 'required|date',
      ]);

      // 2. Nos aseguramos de tener una tarea activa seleccionada
      if (!$this->tareaAsignadaId) { return; }

      // 3. Buscamos la asignación "padre"
      $asignacion = TareaConsolidacionUsuario::findOrFail($this->tareaAsignadaId);

      // 4. Creamos el nuevo registro de historial
      $asignacion->historial()->create([
          'detalle'             => $this->nuevoDetalleHistorial,
          'fecha'               => $this->historialFecha,
          'usuario_creacion_id' => auth()->id(),
      ]);

      // 5. ---- LA MAGIA DEL CHAT ----
      // En lugar de cerrar el modal, reseteamos el campo de texto...
      $this->reset('nuevoDetalleHistorial');

      // ...y refrescamos la lista de historiales para que aparezca el nuevo registro al instante.
      $this->historialActivo = $asignacion->historial()->orderBy('fecha', 'desc')->get();
      $this->historialFecha = now()->format('Y-m-d');
  }

  public function cerrarHistorial()
  {
      $this->dispatch('cerrarModal', nombreModal: 'modalVerHistorial');

      // Añade 'tareaAsignadaId' al reset para limpiar el estado completamente.
      $this->reset(['historialActivo', 'tareaActiva', 'tareaAsignadaId', 'nuevoDetalleHistorial']);
  }

  #[On('eliminarHistorialConfirmado')]
  public function eliminarHistorial($id)
  {
      $historial = HistorialTareaConsolidacionUsuario::findOrFail($id);

      // Guardamos el ID de la asignación "padre" antes de borrar
      $asignacionId = $historial->tarea_consolidacion_usuario_id;

      // Eliminamos el registro
      $historial->delete();

      // ----> LÓGICA DE REFRESCO <----
      // Si el offcanvas de historial está abierto para esta misma tarea,
      // volvemos a cargar la colección de historiales.
      if ($this->tareaAsignadaId == $asignacionId) {
          $this->historialActivo = TareaConsolidacionUsuario::find($asignacionId)
                                      ->historial()
                                      ->orderBy('fecha', 'desc')
                                      ->get();
      }

      // Enviamos la notificación de éxito
      $this->dispatch('msn',
          msnTitulo: '¡Eliminado!',
          msnTexto: 'El registro del historial ha sido eliminado.',
          msnIcono: 'success'
      );
  }


  public function render()
  {
      return view('livewire.consolidacion.gestionar-tareas');
  }
}
