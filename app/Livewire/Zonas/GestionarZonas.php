<?php

namespace App\Livewire\Zonas;

use App\Models\Localidad;
use App\Models\Sede;
use App\Models\Zona;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\On;

use Livewire\WithPagination; // 1. Importa el trait de paginación

class GestionarZonas extends Component
{

    // PROPIEDADES CON REGLAS DE VALIDACIÓN
    #[Rule('required|string|max:100')]
    public $nombre = '';

    #[Rule('nullable|string|max:255')]
    public $descripcion = '';

    #[Rule('required', message: 'Debes seleccionar al menos una sede.')]
    #[Rule('array')]
    #[Rule('min:1', message: 'Debes seleccionar al menos una sede.')]
    #[Rule('exists:sedes,id', message: 'Una de las sedes seleccionadas no es válida.')]
    public $sedesSeleccionadas = [];
    public $localidadesSeleccionadas = [];
    public $zona_id; //Para guardar el ID de la zona a editar

    public $sedes, $localidades;
    public $busqueda;
    public $modoEdicion = false;

    public $collapsedStates = [];


    public function mount()
    {
        // Asumiendo que el modelo Sede tiene un atributo 'nombre'
        $this->sedes = Sede::orderBy('nombre','asc')->get();
        $this->localidades = Localidad::orderBy('nombre','asc')->get();

    }

    // esta funcion prepara las variables para abrir el modal de crearZona
    public function crearZona()
    {
      $this->resetErrorBag();
      $this->modoEdicion = false;
      $this->reset(['nombre', 'descripcion', 'sedesSeleccionadas', 'localidadesSeleccionadas', 'zona_id']);

      $this->dispatch('abrirModal', nombreModal: 'modalcrearEditarZona');
    }

    public function editarZona($id)
    {
        $this->resetErrorBag();
        $zona = Zona::findOrFail($id);

        $this->zona_id = $zona->id;
        $this->nombre = $zona->nombre;
        $this->descripcion = $zona->descripcion;
        // pluck('id') obtiene solo los IDs de las sedes relacionadas en un formato de array
        $this->sedesSeleccionadas = $zona->sedes->pluck('id')->toArray();
        $this->localidadesSeleccionadas = $zona->localidades->pluck('id')->toArray();
        $this->modoEdicion = true; // <-- Activa el modo edición
        $this->dispatch('abrirModal', nombreModal: 'modalcrearEditarZona');
    }

     // === MÉTODO NUEVO PARA GUARDAR NUEVOS O EDITAR  ===
    public function guardarZona()
    {
        $this->validate();
        $mensaje = '';
        DB::transaction(function () use (&$mensaje)  {
            if ($this->zona_id) {
                // MODO EDICIÓN: Actualizamos el registro existente
                $zona = Zona::findOrFail($this->zona_id);
                $zona->update([
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                ]);
                // sync() es perfecto para actualizar relaciones muchos-a-muchos
                $zona->sedes()->sync($this->sedesSeleccionadas);
                $zona->localidades()->sync($this->localidadesSeleccionadas);
                $mensaje = 'La zona fue editada con éxito.';

            } else {
                // MODO CREACIÓN: Creamos un nuevo registro
                $zona = Zona::create([
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                ]);
                $zona->sedes()->attach($this->sedesSeleccionadas);
                $zona->localidades()->attach($this->localidadesSeleccionadas);
                $mensaje = 'La zona fue creadá con éxito.';
            }
        });


        $this->dispatch('cerrarModal', nombreModal: 'modalcrearEditarZona');
        $this->modoEdicion = false;
        $this->zona_id = null;
        $this->dispatch(
          'msn',
          msnIcono: 'success',
          msnTitulo: '¡Muy bien!',
          msnTexto: $mensaje,
        );
    }

    public function duplicarZona($id)
    {
        DB::transaction(function () use ($id) {
          $zonaOriginal = Zona::with('sedes', 'localidades')->findOrFail($id);

          // Determina el "nombre base" del rol, eliminando sufijos como " copia1", " copia2", etc.
          // Esto asegura que si duplicas "pepito copia1", el nuevo rol sea "pepito copia2" y no "pepito copia1 copia1".
          $nombreBase = trim(preg_replace('/ copia\d*$/', '', $zonaOriginal->nombre));

          // Cuenta cuántos roles ya existen con ese nombre base + " copia"
          $copyCount = Zona::where('nombre', 'LIKE', $nombreBase . ' copia%')->count();

          // Genera el nuevo nombre para la copia
          $nombreBase = $nombreBase . ' copia' . ($copyCount + 1);

          // --- FIN DE LA LÓGICA MEJORADA ---
          $nuevaZona = $zonaOriginal->replicate();
          $nuevaZona->nombre = $nombreBase; // Asignamos el nombre único que encontramos.
          $nuevaZona->save();

          $sedesIds = $zonaOriginal->sedes->pluck('id');
          $localidadesIds = $zonaOriginal->localidades->pluck('id');

          $nuevaZona->sedes()->attach($sedesIds);
          $nuevaZona->localidades()->attach($localidadesIds);
        });

        $this->dispatch(
            'msn',
            msnIcono: 'success',
            msnTitulo: '¡Zona Duplicada!',
            msnTexto: 'La zona se ha duplicado exitosamente.',
        );
    }

    #[On('eliminarZona')]
    public function eliminarZona($id)
    {
        $zona = Zona::findOrFail($id);
        $zona->delete();

        $this->dispatch(
            'msn',
            msnIcono: 'success',
            msnTitulo: '¡Zona Eliminada!',
            msnTexto: 'La zona ha sido eliminada permanentemente.',
        );
    }

    public function toggleCollapse($zonaId)
    {
        // Obtenemos el estado actual. Si no existe, asumimos que está colapsado (true).
        $currentState = $this->collapsedStates[$zonaId] ?? true;

        // Invertimos el estado y lo guardamos.
        $this->collapsedStates[$zonaId] = !$currentState;
    }

    public function render()
    {
      $zonas = Zona::with('sedes','localidades') // Carga la relación 'sedes' para evitar consultas N+1
            ->when($this->busqueda, function ($query) {
                // Si hay algo en $busqueda, aplica el filtro
                $query->whereRaw("LOWER( translate( nombre ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ["%{$this->busqueda}%"]);
            })
            ->latest() // Ordena las zonas de la más nueva a la más antigua
            ->paginate(8); // Pagina los resultados, muestra 8 por página

        return view('livewire.zonas.gestionar-zonas', [
            'zonas' => $zonas, // Pasa las zonas a la vista
        ]);
    }

}
