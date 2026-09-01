<?php

namespace App\Livewire\Hitos;

use App\Models\HitoDenuncia;
use App\Models\HitoFoto;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class GestionarDenuncias extends Component
{
    use WithPagination;

    public $filtroEstado = 'pendiente';

    public $observaciones = [];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if ($rolActivo) {
            $rolActivo->verificacionDelPermiso('hitos.gestionar_denuncias');
        }
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function marcarResuelta($denunciaId)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if ($rolActivo) {
            $rolActivo->verificacionDelPermiso('hitos.gestionar_denuncias');
        }

        $denuncia = HitoDenuncia::findOrFail($denunciaId);
        $denuncia->estado = 'resuelta';
        $denuncia->resuelto_por = auth()->id();
        $denuncia->observaciones_admin = $this->observaciones[$denunciaId] ?? 'Revisado y resuelto por administración.';
        $denuncia->save();

        $this->dispatch('msn', [
            'tipo' => 'success',
            'mensaje' => 'Denuncia marcada como resuelta.',
        ]);
    }

    public function eliminarFotoReportada($denunciaId, $fotoId)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if ($rolActivo) {
            $rolActivo->verificacionDelPermiso('hitos.gestionar_denuncias');
        }

        $foto = HitoFoto::find($fotoId);
        if ($foto) {
            Storage::disk('public')->delete('img/hitos/fotos/'.$foto->ruta);
            $foto->delete();
        }

        $denuncia = HitoDenuncia::find($denunciaId);
        if ($denuncia) {
            $denuncia->estado = 'resuelta';
            $denuncia->resuelto_por = auth()->id();
            $denuncia->observaciones_admin = 'Foto eliminada por infracción de contenido.';
            $denuncia->save();
        }

        $this->dispatch('msn', [
            'tipo' => 'success',
            'mensaje' => 'Foto eliminada y denuncia resuelta.',
        ]);
    }

    public function render()
    {
        $denuncias = HitoDenuncia::with(['hito', 'foto', 'user', 'resueltoPor'])
            ->when($this->filtroEstado, fn ($q) => $q->where('estado', $this->filtroEstado))
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.hitos.gestionar-denuncias', [
            'denuncias' => $denuncias,
        ]);
    }
}
