<?php

namespace App\Livewire\Actividades;

use App\Models\TipoCargoActividad;
use App\Models\ActividadEncargado;
use App\Models\User;
use Livewire\Component;
use App\Models\Actividad;
use App\Models\EncargadoActividad;
use App\Models\Usuario;
use Illuminate\Support\Facades\Mail;
use App\Mail\DefaultMail;


use \stdClass;

use Illuminate\Support\Facades\Log;

// Componentes de Livewire

use Livewire\Attributes\Validate;
use Livewire\Attributes\On;
use Illuminate\Validation\Rule;




class CargosActividad extends Component
{

    /// ESTO ES PARA CARGAR EL MODAL EN INICIO
    public $tiposCargos = [];
    public $tiposCargoUsuario = [];
    public $actividad;
    public $cargosAsignados;


    /// variables para modal gestionar cargos

    public $selectTipoCargo;
    public $descripcionCargo;
    public $usuarioSeleccionado;


    //variable
    public $variable;

    public function mount()
    {
        $this->tiposCargos = TipoCargoActividad::get();
        $this->cargosAsignados = ActividadEncargado::select('tipo_cargo_id')
            ->selectRaw('COUNT(*) as total')
            ->with('tipoCargo')
            ->where('actividad_id', $this->actividad->id)
            ->groupBy('tipo_cargo_id')
            ->get();
    }


    #[On('abrirModalNuevoCargo')]
    public function abrirModalNuevoCargo($usuarioId)
    {
        $this->variable = 'inicio';
        $this->usuarioSeleccionado = $usuarioId;
        $this->tiposCargos;
        $this->tiposCargoUsuario =  ActividadEncargado::where('user_id', $usuarioId)->where('actividad_id', $this->actividad->id)->get();
        $this->selectTipoCargo;
        $this->descripcionCargo;
        // Puedes agregar lógica adicional si es necesario
        $this->dispatch('abrirModal', nombreModal: 'modalNuevoCargo');
    }

    public function nuevoCargo()
    {
        $nuevoCargo = new ActividadEncargado();
        $nuevoCargo->user_id = $this->usuarioSeleccionado;
        $nuevoCargo->actividad_id = $this->actividad->id;
        $nuevoCargo->tipo_cargo_id = $this->selectTipoCargo;
        $nuevoCargo->descripcion = $this->descripcionCargo;
        $nuevoCargo->save();

        $this->selectTipoCargo = 0;
        $this->descripcionCargo = '';
        $this->mount();
    }

    public function eliminarCargo($cargoId)
    {
        $cargoActividad = ActividadEncargado::find($cargoId);
        $cargoActividad->delete();


        $this->abrirModalNuevoCargo($this->usuarioSeleccionado);
        $this->mount();
    }

    public function notificarPorEmail()
    {
        $tiposCargoUsuarioActuales =   ActividadEncargado::where('user_id', $this->usuarioSeleccionado)->where('actividad_id', $this->actividad->id)->get();
        $mensaje = '<table> 
                        <thead>
                            <th> CARGO </th>
                            <th> DETALLES DEL CARGO </th>
                        </thead>
        <tbody>
        ';

        foreach ($tiposCargoUsuarioActuales as $cargoActual) {
            $mensaje .= '<td> 
                            <tr> ' . $cargoActual->tipoCargo->nombre . '</tr>
                            <tr> ' . $cargoActual->descripcion . '</tr>
                      </td>
            ';
        }


        $mensaje .= '
        </tbody>
        
        </table>';



        $usuario = User::find($this->usuarioSeleccionado);
        $mailData = new stdClass();
        $mailData->subject = 'Asiganación cargos actividad';
        $mailData->nombre = $usuario->nombre(3);
        $mailData->mensaje = $mensaje;
        Mail::to('elcorreodedarwin90@gmail.com')->send(new DefaultMail($mailData));
        $this->mount();

        $this->dispatch(
            'msn',
            msnIcono: 'success',
            msnTitulo: '¡Muy bien!',
            msnTexto: 'se envio correctamente el correo.'
        );
    }




    public function render()
    {
        $this->tiposCargos;
        $this->tiposCargoUsuario =  ActividadEncargado::where('user_id', $this->usuarioSeleccionado)->where('actividad_id', $this->actividad->id)->get();
        return view('livewire.actividades.cargos-actividad');
    }
}
