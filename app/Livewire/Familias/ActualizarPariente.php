<?php

namespace App\Livewire\Familias;

use App\Models\ParienteUsuario;
use App\Models\TipoParentesco;
use App\Services\RelacionesFamiliares;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class ActualizarPariente extends Component
{
    #[Locked]
    public int $usuarioId;

    #[Locked]
    public ?int $relacionIdGeneral = null;

    public string $nombreUsuario = '';

    public $actualizarTipoParentesco;

    public $actualizarResponsabilidad;

    #[On('abrirModalActualizarPariente')]
    public function abrirModalActualizarPariente(int $relacionId, int $usuarioId): void
    {
        abort_unless($usuarioId === $this->usuarioId, 403);
        $relacion = $this->relacionAutorizada($relacionId);
        $inversa = ParienteUsuario::where('user_id', $relacion->pariente_user_id)
            ->where('pariente_user_id', $relacion->user_id)->first();
        $this->resetValidation();
        $this->relacionIdGeneral = $relacion->id;
        $this->nombreUsuario = $relacion->usuario->nombre(3);
        $this->actualizarTipoParentesco = $relacion->tipo_pariente_id;
        $this->actualizarResponsabilidad = $relacion->es_el_responsable ? 2 : ($inversa?->es_el_responsable ? 3 : 1);
        if (! $inversa) {
            $this->addError('pariente', 'Falta el vínculo inverso. Revisa ambas fichas antes de modificar esta relación.');
        }
        $this->dispatch('abrirModal', nombreModal: 'modalActualizarPariente');
    }

    public function updateParentesco(RelacionesFamiliares $relaciones): void
    {
        $relacion = $this->relacionAutorizada($this->relacionIdGeneral);
        $this->validate([
            'actualizarTipoParentesco' => ['required', 'integer', 'exists:tipos_parentesco,id'],
            'actualizarResponsabilidad' => ['required', 'integer', 'in:1,2,3'],
        ], [
            'actualizarTipoParentesco.*' => 'Selecciona un tipo de parentesco válido.',
            'actualizarResponsabilidad.*' => 'Selecciona una opción de responsabilidad válida.',
        ]);
        $relaciones->guardar($this->usuarioId, $relacion->pariente_user_id, $this->actualizarTipoParentesco, $this->actualizarResponsabilidad, $relacion->id);

        session()->flash('success', 'La relación familiar fue actualizada.');
        $this->dispatch('parentesco-actualizado');
    }

    private function relacionAutorizada(?int $id): ParienteUsuario
    {
        $relacion = ParienteUsuario::where('user_id', $this->usuarioId)->whereKey($id)->firstOrFail();
        $this->authorize('gestionarParentesco', [$relacion->usuario()->firstOrFail(), 'familiar.opcion_modificar_relacion_familiar']);

        return $relacion;
    }

    public function render(): View
    {
        return view('livewire.familias.actualizar-pariente', ['tiposParentesco' => TipoParentesco::all()]);
    }
}
