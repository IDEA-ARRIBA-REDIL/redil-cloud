<?php

namespace App\Livewire\Hitos;

use App\Models\Hito;
use App\Models\HitoUsuario;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class GestionarAsistencias extends Component
{
    use WithPagination;

    public Hito $hito;

    public $search = '';

    protected $paginationTheme = 'bootstrap';

    public function mount(Hito $hito)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if ($rolActivo) {
            $rolActivo->verificacionDelPermiso('hitos.gestionar_asistencia');
        }

        $this->hito = $hito;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function marcarAsistencia($userId, $asistio = true)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if ($rolActivo) {
            $rolActivo->verificacionDelPermiso('hitos.gestionar_asistencia');
        }

        $fechaActividad = $this->hito->actividad?->fecha ? substr((string) $this->hito->actividad->fecha, 0, 10) : now()->toDateString();

        if ($asistio) {
            HitoUsuario::updateOrCreate(
                [
                    'hito_id' => $this->hito->id,
                    'user_id' => $userId,
                    'origen_tipo' => 'actividad',
                    'origen_id' => $this->hito->actividad_id,
                ],
                [
                    'fecha' => $fechaActividad,
                    'asistio' => true,
                    'asignado_por' => auth()->id(),
                ]
            );

            $this->dispatch('msn', [
                'tipo' => 'success',
                'mensaje' => 'Asistencia confirmada. Hito asignado al usuario.',
            ]);
        } else {
            HitoUsuario::where('hito_id', $this->hito->id)
                ->where('user_id', $userId)
                ->where('origen_tipo', 'actividad')
                ->delete();

            $this->dispatch('msn', [
                'tipo' => 'info',
                'mensaje' => 'Asistencia removida.',
            ]);
        }
    }

    public function render()
    {
        $usuarios = User::query()
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(15);

        $asistentesIds = HitoUsuario::where('hito_id', $this->hito->id)
            ->where('asistio', true)
            ->whereIn('user_id', $usuarios->pluck('id'))
            ->pluck('user_id')
            ->toArray();

        return view('livewire.hitos.gestionar-asistencias', [
            'usuarios' => $usuarios,
            'asistentesIds' => $asistentesIds,
        ]);
    }
}
