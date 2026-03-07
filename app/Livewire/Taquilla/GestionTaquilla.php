<?php

namespace App\Livewire\Taquilla;

use Livewire\Component;
use App\Models\Caja;
use App\Models\Actividad;
use App\Models\User;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Livewire\Attributes\On; // ¡Importante! Para el listener

class GestionTaquilla extends Component
{
    // --- Propiedades de Estado ---

    // Modelos principales
    public Caja $cajaActiva;
    public ?User $comprador = null;
    public ?User $usuarioAValidar = null; // El inscrito
    public ?Actividad $actividadSeleccionada = null;

    // Colecciones para los <select>
    public $actividadesDisponibles = [];
    public $parientes = []; // ¡Esta es la propiedad clave!

    // Propiedades para "form sticky" y estado de la UI
    public $compradorIdActual;
    public $actividadIdActual;
    public $inscritoIdActual;
    public $esInscripcionPropia = true; // El radio button

    /**
     * Define si el formulario de validación fue enviado.
     */
    public $verificacionEnviada = false;

    /**
     * Define las reglas de validación para el formulario
     */
    protected function rules()
    {
        return [
            'actividadIdActual' => 'required',
            // El 'inscritoIdActual' se valida dinámicamente
        ];
    }
    protected $messages = [
        'actividadIdActual.required' => 'Debes seleccionar una actividad.',
    ];

    /**
     * Método Mount (Constructor)
     * Recibe la caja activa desde la URL.
     */
    public function mount(Caja $cajaActiva)
    {
        $this->cajaActiva = $cajaActiva;

        // 1. Cargar datos para los filtros
        $this->actividadesDisponibles = Actividad::where('activa', true)
            ->where('punto_de_pago', true) //
            ->orderBy('nombre')
            ->get();
    }

    /**
     * ¡ESTA ES LA MAGIA!
     * Este método "escucha" el evento 'usuario-seleccionado'
     * emitido por el componente 'usuarios-para-busqueda'.
     *
     */
    #[On('usuario-seleccionado')]
    public function cargarParientes($id)
    {
        $this->compradorIdActual = $id;
        $this->comprador = User::find($id);

        if ($this->comprador) {
            //
            $this->parientes = $this->comprador->parientesDelUsuario()->get();
        } else {
            $this->parientes = collect();
        }

        // Reseteamos el estado al seleccionar un nuevo comprador
        $this->esInscripcionPropia = true;
        $this->inscritoIdActual = $this->compradorIdActual;
        $this->verificacionEnviada = false;
        $this->usuarioAValidar = null;
        $this->actividadSeleccionada = null;
    }

    /**
     * Se ejecuta cuando se presiona el botón "Verificar requisitos".
     */
    public function verificarRequisitos()
    {
        $this->validate(); // Valida las 'rules()'

        // Determinamos el ID del inscrito basado en el radio button
        if ($this->esInscripcionPropia) {
            $this->inscritoIdActual = $this->compradorIdActual;
        }

        if (empty($this->inscritoIdActual)) {
            $this->addError('inscritoIdActual', 'Debes seleccionar un familiar a inscribir.');
            return;
        }

        // Cargamos los modelos finales
        $this->usuarioAValidar = User::find($this->inscritoIdActual);
        $this->actividadSeleccionada = $this->actividadesDisponibles->find($this->actividadIdActual);

        // ¡Activamos la bandera!
        $this->verificacionEnviada = true;
    }

    /**
     * Limpia la búsqueda y la validación.
     */
    public function limpiar()
    {
        // Resetea todas las propiedades a sus valores iniciales
        $this->reset(['comprador', 'usuarioAValidar', 'actividadSeleccionada', 'parientes', 'compradorIdActual', 'actividadIdActual', 'inscritoIdActual', 'esInscripcionPropia', 'verificacionEnviada']);

        // Dispara un evento para que el componente 'usuarios-para-busqueda' también se limpie
        $this->dispatch('resetear-buscador-usuario');
    }

    /**
     * Renderiza la vista del componente
     */
    public function render()
    {
        // Pasamos las variables públicas a la vista
        return view('livewire.taquilla.gestion-taquilla', [
            'configuracion' => Configuracion::find(1),
        ])->layout('layouts.layoutMaster'); // ¡Le decimos que use tu layout!
    }
}
