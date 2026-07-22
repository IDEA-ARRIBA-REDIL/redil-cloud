<?php

namespace App\Http\Controllers;

use App\Models\AsesorPdp; // ¡Cambiado!
use App\Models\Configuracion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AsesorPdpController extends Controller
{
    /**
     * Muestra la lista de asesores con filtros.
     */
    public function gestionar(Request $request)
    {
        $configuracion = Configuracion::find(1);

        // OBTENCIÓN DE DATOS PARA FILTROS
        // Se buscan roles marcados para PDP, con fallback a todos los roles si no hay ninguno marcado.
        $rolesAsesor = Role::where('es_cajero_pdp', true)->orWhere('es_encargado_pdp', true)->get();
        if ($rolesAsesor->isEmpty()) {
            $rolesAsesor = Role::all();
        }

        // INICIALIZACIÓN DE FILTROS
        $tagsBusqueda = [];
        $banderaFiltros = false;
        $filtros = $request->only([
            'filtro_busqueda_general',
            'filtro_estado_asesor',
            'filtro_tipo_asesor', // Nuevo filtro (cajero o encargado)
        ]);

        // CONSTRUCCIÓN DE LA CONSULTA BASE
        $queryAsesores = AsesorPdp::query()->with('user');

        // APLICACIÓN DE FILTROS

        // Filtro de Búsqueda General
        if (! empty($filtros['filtro_busqueda_general'])) {
            $termino = strtolower($filtros['filtro_busqueda_general']);
            $queryAsesores->whereHas('user', function ($qUser) use ($termino) {
                $qUser->where(
                    fn ($q) => $q->whereRaw('LOWER(primer_nombre) LIKE ?', ["%{$termino}%"])
                        ->orWhereRaw('LOWER(primer_apellido) LIKE ?', ["%{$termino}%"])
                        ->orWhereRaw('LOWER(identificacion) LIKE ?', ["%{$termino}%"])
                        ->orWhereRaw('LOWER(email) LIKE ?', ["%{$termino}%"])
                );
            });
            $tagsBusqueda[] = (object) ['label' => $filtros['filtro_busqueda_general'], 'field' => 'filtro_busqueda_general'];
            $banderaFiltros = true;
        }

        // Filtro por Estado
        if (isset($filtros['filtro_estado_asesor']) && $filtros['filtro_estado_asesor'] !== '') {
            $queryAsesores->where('activo', (bool) $filtros['filtro_estado_asesor']);
            $label = 'Estado: '.((bool) $filtros['filtro_estado_asesor'] ? 'Activo' : 'Inactivo');
            $tagsBusqueda[] = (object) ['label' => $label, 'field' => 'filtro_estado_asesor'];
            $banderaFiltros = true;
        }

        // Por tipo de asesor
        if (! empty($filtros['filtro_tipo_asesor'])) {
            if ($filtros['filtro_tipo_asesor'] == 'cajero') {
                $queryAsesores->where('es_cajero', true);
                $tagsBusqueda[] = (object) ['label' => 'Tipo: Cajero', 'field' => 'filtro_tipo_asesor'];
            } elseif ($filtros['filtro_tipo_asesor'] == 'encargado') {
                $queryAsesores->where('es_encargado', true);
                $tagsBusqueda[] = (object) ['label' => 'Tipo: Encargado', 'field' => 'filtro_tipo_asesor'];
            }
            $banderaFiltros = true;
        }

        // EJECUCIÓN FINAL DE LA CONSULTA
        $asesores = $queryAsesores->latest('created_at')->paginate(16);

        // DEVOLVER LA VISTA
        return view('contenido.paginas.puntos-de-pago.gestionar-asesores', [
            'asesores' => $asesores,
            'configuracion' => $configuracion,
            'tagsBusqueda' => $tagsBusqueda,
            'banderaFiltros' => $banderaFiltros,
            'rolesAsesor' => $rolesAsesor,
            'filtrosActuales' => $filtros,
        ]);
    }

    /**
     * Guarda o actualiza un asesor.
     */
    public function guardar(Request $request)
    {
        $validados = $request->validate([
            'buscador-usuario' => 'required|integer|exists:users,id',
            'descripcion' => 'nullable|string|max:1000',
            'activo' => 'required|boolean',
            'role_id' => 'required|integer|exists:roles,id',
            'es_cajero' => 'nullable|boolean',
            'es_encargado' => 'nullable|boolean',
        ], [
            'buscador-usuario.required' => 'Debes seleccionar un usuario de la lista.',
            'buscador-usuario.exists' => 'El usuario seleccionado no existe en el sistema.',
            'role_id.required' => 'Debes seleccionar un rol para el asesor.',
            'role_id.exists' => 'El rol seleccionado no es válido.',
        ]);

        try {
            $userId = (int) $request->input('buscador-usuario');
            $usuario = User::findOrFail($userId);

            // Asigna el ROL al usuario (previniendo duplicados)
            $usuario->roles()->syncWithoutDetaching([
                $request->role_id => [
                    'activo' => 0,
                    'dependiente' => 0,
                    'model_type' => 'App\Models\User',
                ],
            ]);

            // Determinar banderas de tipo
            $esCajero = $request->boolean('es_cajero');
            $esEncargado = $request->boolean('es_encargado');

            // Si no se marcó ninguno explícitamente, asignamos cajero por defecto
            if (! $esCajero && ! $esEncargado) {
                $esCajero = true;
            }

            // Buscar si ya existe el registro (incluyendo trashed) para evitar violaciones a la clave única user_id
            $asesorExistente = AsesorPdp::withTrashed()->where('user_id', $userId)->first();

            if ($asesorExistente) {
                if ($asesorExistente->trashed()) {
                    $asesorExistente->restore();
                }
                $asesorExistente->update([
                    'descripcion' => $request->descripcion,
                    'activo' => (bool) $request->activo,
                    'es_cajero' => $esCajero || $asesorExistente->es_cajero,
                    'es_encargado' => $esEncargado || $asesorExistente->es_encargado,
                ]);
                $mensaje = "El asesor '{$usuario->nombre(3)}' ya estaba registrado y sus permisos/datos fueron actualizados correctamente.";
            } else {
                AsesorPdp::create([
                    'user_id' => $userId,
                    'descripcion' => $request->descripcion,
                    'activo' => (bool) $request->activo,
                    'es_cajero' => $esCajero,
                    'es_encargado' => $esEncargado,
                ]);
                $mensaje = "Asesor '{$usuario->nombre(3)}' creado correctamente.";
            }

            return redirect()->route('asesores_pdp.gestionar')
                ->with('mensaje_exito', $mensaje)
                ->with('success', $mensaje);
        } catch (\Exception $e) {
            Log::error('Error al crear/actualizar asesor: '.$e->getMessage());

            $errorMsg = 'No se pudo guardar el asesor: '.$e->getMessage();

            return back()
                ->with('mensaje_error', $errorMsg)
                ->with('error', $errorMsg)
                ->withInput();
        }
    }

    /**
     * Elimina un asesor y desvincula su rol.
     */
    public function eliminar(Request $request)
    {
        $request->validate(['asesor_id' => 'required|integer|exists:asesores_pdp,id']);
        $asesorId = $request->input('asesor_id');

        DB::beginTransaction();
        try {
            $asesor = AsesorPdp::with('user')->findOrFail($asesorId);
            $usuario = $asesor->user;
            $nombreUsuario = optional($usuario)->nombre(3) ?? 'Asesor ID '.$asesorId;

            if ($usuario) {
                $rolesAsesorParaQuitar = $usuario->roles()
                    ->where(function ($q) {
                        $q->where('es_cajero_pdp', true)
                            ->orWhere('es_encargado_pdp', true);
                    })
                    ->pluck('id');

                if ($rolesAsesorParaQuitar->isNotEmpty()) {
                    $usuario->roles()->detach($rolesAsesorParaQuitar);
                }
            }

            $asesor->forceDelete();

            DB::commit();

            $msg = "Asesor '{$nombreUsuario}' eliminado y rol desvinculado correctamente.";

            return redirect()->route('asesores_pdp.gestionar')
                ->with('mensaje_success', $msg)
                ->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al eliminar asesor ID {$asesorId}: ".$e->getMessage());

            $errorMsg = 'Error al eliminar el asesor: '.$e->getMessage();

            return back()
                ->with('mensaje_error', $errorMsg)
                ->with('error', $errorMsg);
        }
    }

    /**
     * Activa el perfil de un asesor.
     */
    public function activar(AsesorPdp $asesor)
    {
        try {
            $asesor->activo = true;
            $asesor->save();

            $msg = "El asesor '{$asesor->user->nombre(3)}' ha sido activado.";

            return redirect()->route('asesores_pdp.gestionar')
                ->with('mensaje_exito', $msg)
                ->with('success', $msg);
        } catch (\Exception $e) {
            Log::error("Error al activar asesor ID {$asesor->id}: ".$e->getMessage());

            $errorMsg = 'Ocurrió un error al activar el asesor: '.$e->getMessage();

            return back()
                ->with('mensaje_error', $errorMsg)
                ->with('error', $errorMsg);
        }
    }

    /**
     * Desactiva el perfil de un asesor.
     */
    public function desactivar(AsesorPdp $asesor)
    {
        try {
            $asesor->activo = false;
            $asesor->save();

            $msg = "El asesor '{$asesor->user->nombre(3)}' ha sido desactivado.";

            return redirect()->route('asesores_pdp.gestionar')
                ->with('mensaje_exito', $msg)
                ->with('success', $msg);
        } catch (\Exception $e) {
            Log::error("Error al desactivar asesor ID {$asesor->id}: ".$e->getMessage());

            $errorMsg = 'Ocurrió un error al desactivar el asesor: '.$e->getMessage();

            return back()
                ->with('mensaje_error', $errorMsg)
                ->with('error', $errorMsg);
        }
    }
}
