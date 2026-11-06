<?php

namespace App\Livewire\Usuarios;

use App\Models\IntegranteGrupo;
use App\Models\ReporteBajaAlta;
use App\Models\TipoBajaAlta;
use App\Models\User;
use App\Services\UserDependencyService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ModalBajaAlta extends Component
{
    public string $titulo = '';

    public ?int $usuarioId = null;

    public ?string $tipo = null;

    public ?int $grupoId = null;

    public ?string $redirect = null;

    public Collection $motivosBajasAltas;

    #[Validate('required', message: 'El motivo es obligatorio.')]
    public ?int $motivo = null;

    public ?string $observacion = null;

    public function mount(): void
    {
        $this->motivosBajasAltas = collect();
    }

    #[On('abrirModalBajaAlta')]
    public function abrirModalBajaAlta(int|string $usuarioId, string $tipo): void
    {
        $usuario = User::withTrashed()->find($usuarioId);

        if (! $usuario) {
            return;
        }

        $this->tipo = $tipo;
        $this->usuarioId = $usuario->id;
        $this->titulo = $tipo === 'alta'
            ? 'Dar de alta a <b>'.$usuario->nombre(3).'</b>'
            : 'Dar de baja a <b>'.$usuario->nombre(3).'</b>';

        if ($this->tipo === 'alta') {
            $this->motivosBajasAltas = TipoBajaAlta::where('dado_alta', true)->get();
        } elseif ($this->tipo === 'baja') {
            $this->motivosBajasAltas = TipoBajaAlta::where('dado_baja', true)->get();
        }

        $this->dispatch('abrirModal', nombreModal: 'modaBajaAlta');
    }

    #[On('comprobarSiTieneRegistros')]
    public function comprobarSiTieneRegistros(int|string $usuarioId, UserDependencyService $dependencyService): void
    {
        $usuario = User::withTrashed()->find($usuarioId);

        if (! $usuario) {
            return;
        }

        $resumen = $dependencyService->obtenerResumenRegistros($usuario);
        $tieneRegistros = $dependencyService->tieneRegistrosCriticos($usuario);

        if ($tieneRegistros) {
            $htmlRegistros = $dependencyService->generarHtmlResumen($resumen, $usuario);

            $this->dispatch(
                'msnTieneRegistros',
                msnIcono: 'warning',
                msnTitulo: '¡Historial activo detectado!',
                msnTexto: $htmlRegistros,
                id: $usuario->id
            );
        } else {
            $this->dispatch(
                'msnConfirmarEliminarSinRegistros',
                msnIcono: 'question',
                msnTitulo: '¿Qué acción deseas realizar con <b>'.$usuario->nombre(3).'</b>?',
                msnTexto: 'Esta persona no registra historial ni dependencias en el sistema. Puedes descartarla eliminándola o registrar su salida dándola de baja.',
                id: $usuario->id
            );
        }
    }

    /**
     * Elimina definitivamente (Purga total) a un usuario que no tiene dependencias activas.
     */
    public function eliminarSinRegistros(int|string $usuarioId, ?UserDependencyService $dependencyService = null): mixed
    {
        $rolActivo = auth()->user()?->roles()->wherePivot('activo', true)->first();

        $tienePermiso = $rolActivo && (
            $rolActivo->hasPermissionTo('personas.eliminar_asistentes_forzadamente') ||
            $rolActivo->hasPermissionTo('personas.opcion_eliminar_asistente')
        );

        if (! $tienePermiso) {
            $this->dispatch('alertaError', mensaje: 'No tienes permisos para eliminar personas.');

            return null;
        }

        $usuario = User::withTrashed()->find($usuarioId);

        if (! $usuario) {
            return null;
        }

        $dependencyService = $dependencyService ?: app(UserDependencyService::class);
        $nombre = $usuario->nombre(3);

        try {
            $dependencyService->ejecutarPurgaTotal($usuario);
        } catch (\Throwable $e) {
            $this->dispatch('alertaError', mensaje: 'Ocurrió un error al intentar eliminar los datos: '.$e->getMessage());

            return null;
        }

        $urlDestino = request()->header('Referer') ?: url('/usuarios');

        return redirect($urlDestino)->with('success', "<b>{$nombre}</b> fue eliminado definitivamente con éxito.");
    }

    #[On('confirmarEliminacion')]
    public function confirmarEliminacion(int|string $usuarioId, ?UserDependencyService $dependencyService = null): void
    {
        $rolActivo = auth()->user()?->roles()->wherePivot('activo', true)->first();

        $tienePermiso = $rolActivo && (
            $rolActivo->hasPermissionTo('personas.eliminar_asistentes_forzadamente') ||
            $rolActivo->hasPermissionTo('personas.opcion_eliminar_asistente')
        );

        if (! $tienePermiso) {
            $this->dispatch('alertaError', mensaje: 'No tienes privilegios para realizar una eliminación forzada.');

            return;
        }

        $usuario = User::withTrashed()->find($usuarioId);

        if (! $usuario) {
            return;
        }

        $dependencyService = $dependencyService ?: app(UserDependencyService::class);
        $resumen = $dependencyService->obtenerResumenRegistros($usuario);
        $htmlResumenPurga = $dependencyService->generarHtmlResumenPurga($resumen, $usuario);

        $this->dispatch(
            'msnConfirmarEliminacion',
            msnIcono: 'warning',
            msnTitulo: '¿Deseas eliminar definitivamente a <b>'.$usuario->nombre(3).'</b>?',
            msnTexto: $htmlResumenPurga,
            id: $usuario->id
        );
    }

    /**
     * Ejecuta la eliminación forzada total tras confirmar la contraseña del usuario logueado.
     */
    public function eliminacionForzada(int|string $usuarioId, ?string $password = null, ?UserDependencyService $dependencyService = null): mixed
    {
        $rolActivo = auth()->user()?->roles()->wherePivot('activo', true)->first();

        $tienePermiso = $rolActivo && (
            $rolActivo->hasPermissionTo('personas.eliminar_asistentes_forzadamente') ||
            $rolActivo->hasPermissionTo('personas.opcion_eliminar_asistente')
        );

        if (! $tienePermiso) {
            $this->dispatch('alertaError', mensaje: 'No tienes privilegios para realizar una eliminación forzada.');

            return null;
        }

        if (empty($password) || ! Hash::check($password, auth()->user()->password)) {
            $this->dispatch('alertaError', mensaje: 'La contraseña ingresada es incorrecta. No se realizó ninguna acción.');

            return null;
        }

        $usuario = User::withTrashed()->find($usuarioId);

        if (! $usuario) {
            $this->dispatch('alertaError', mensaje: 'El usuario ya no existe o no fue encontrado.');

            return null;
        }

        $dependencyService = $dependencyService ?: app(UserDependencyService::class);
        $nombre = $usuario->nombre(3);

        try {
            $dependencyService->ejecutarPurgaTotal($usuario);
        } catch (\Throwable $e) {
            $this->dispatch('alertaError', mensaje: 'Ocurrió un error al intentar eliminar los datos: '.$e->getMessage());

            return null;
        }

        $urlDestino = request()->header('Referer') ?: url('/usuarios/dados-de-baja');

        return redirect($urlDestino)->with('success', "<b>{$nombre}</b> fue eliminado definitivamente del sistema.");
    }

    public function editarBajaAlta(int|string $usuarioId, string $tipo): mixed
    {
        $this->validate();
        $usuario = User::withTrashed()->find($usuarioId);

        if (! $usuario) {
            return null;
        }

        $reporte = new ReporteBajaAlta;
        $reporte->observaciones = $this->observacion;
        $reporte->tipo_baja_alta_id = $this->motivo;
        $reporte->user_id = $usuario->id;
        $reporte->fecha = Carbon::now()->format('Y-m-d');

        if ($tipo === 'baja') {
            $reporte->dado_baja = true;
            $usuario->delete();
            $mensaje = '<b>'.$usuario->nombre(3).'</b> fue dado de baja con éxito.';
        } elseif ($tipo === 'alta') {
            $reporte->dado_baja = false;
            $usuario->restore();

            if (! empty($this->grupoId)) {
                IntegranteGrupo::where('user_id', $usuario->id)->delete();
                $usuario->cambiarGrupo($this->grupoId);
            }

            $mensaje = '<b>'.$usuario->nombre(3).'</b> fue dado de alta con éxito.';
        }

        $reporte->save();

        $urlDestino = request()->header('Referer') ?: url('/usuarios');

        return redirect($urlDestino)->with('success', $mensaje);
    }

    public function render(): View
    {
        return view('livewire.usuarios.modal-baja-alta');
    }
}
