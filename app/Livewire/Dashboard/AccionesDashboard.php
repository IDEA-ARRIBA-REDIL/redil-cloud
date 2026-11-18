<?php

namespace App\Livewire\Dashboard;

use App\Models\AccionDashboard;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class AccionesDashboard extends Component
{
    /**
     * Evalúa si el usuario actual cumple las condiciones para ver el botón automático de Cumpleaños:
     * 1. Que su rol activo tenga el permiso 'dashboard.ver_cumpleanos'.
     * 2. Que sea encargado de al menos un grupo activo con integrantes (o posea el permiso global 'personas.lista_asistentes_todos').
     */
    protected function debeMostrarCumpleanos(?User $usuario, ?Role $rolActivo): bool
    {
        if (! $usuario || ! $rolActivo) {
            return false;
        }

        // 1. Validar que el rol activo tenga el permiso configurable de Cumpleaños
        $tienePermisoCumpleanos = false;
        try {
            $tienePermisoCumpleanos = $rolActivo->hasPermissionTo('dashboard.ver_cumpleanos');
        } catch (PermissionDoesNotExist) {
            $tienePermisoCumpleanos = false;
        }

        if (! $tienePermisoCumpleanos) {
            return false;
        }

        // 2. Si el rol activo tiene permiso global para ver todos los asistentes, puede ver todos los cumpleaños
        try {
            if ($rolActivo->hasPermissionTo('personas.lista_asistentes_todos')) {
                return true;
            }
        } catch (PermissionDoesNotExist) {
            // Continuar con la validación de grupo encargado
        }

        // 3. Para roles como Líder, Maestro, Punto de Pago o Consejero:
        //    exigir que el usuario sea encargado de al menos un grupo activo que tenga integrantes asignados
        return $usuario->gruposEncargados()
            ->where('grupos.dado_baja', false)
            ->whereHas('asistentes')
            ->exists();
    }

    /**
     * Obtiene las acciones configuradas en base de datos para el usuario actual,
     * con respaldo en caso de que aún no se haya corrido la migración en el entorno.
     *
     * @return Collection<int, AccionDashboard>
     */
    protected function obtenerAccionesConfiguradas(?User $usuario, ?Role $rolActivo): Collection
    {
        if (! $usuario) {
            return collect();
        }

        if (! Schema::hasTable('acciones_dashboard') || AccionDashboard::count() === 0) {
            return collect([
                new AccionDashboard([
                    'nombre' => 'Academia',
                    'url' => '/escuelas/dashboard',
                    'abrir_nueva_pestana' => false,
                    'tipo_visual' => 'imagen',
                    'imagen' => 'Funcionalidad-escuelas.png',
                    'orden' => 1,
                    'activo' => true,
                ]),
                new AccionDashboard([
                    'nombre' => 'Grupos',
                    'url' => '/grupos',
                    'abrir_nueva_pestana' => false,
                    'tipo_visual' => 'imagen',
                    'imagen' => 'Funcionalidad-grupos.png',
                    'orden' => 2,
                    'activo' => true,
                ]),
            ]);
        }

        return AccionDashboard::query()
            ->permitidasParaUsuario($usuario, $rolActivo)
            ->get();
    }

    public function render(): View
    {
        /** @var User|null $usuario */
        $usuario = auth()->user();
        $rolActivo = $usuario?->rolActivoParaPermisos();

        $acciones = $this->obtenerAccionesConfiguradas($usuario, $rolActivo);
        $mostrarCumpleanos = $this->debeMostrarCumpleanos($usuario, $rolActivo);
        $totalElementos = $acciones->count() + ($mostrarCumpleanos ? 1 : 0);

        return view('livewire.dashboard.acciones-dashboard', [
            'acciones' => $acciones,
            'mostrarCumpleanos' => $mostrarCumpleanos,
            'totalElementos' => $totalElementos,
            'iconoCumpleanosUrl' => Storage::disk('global_media')->url('Funcionalidad-cumpleaños.png'),
        ]);
    }
}
