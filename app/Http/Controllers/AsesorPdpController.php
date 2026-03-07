<?php

namespace App\Http\Controllers;

use App\Models\AsesorPdp; // ¡Cambiado!
use App\Models\Configuracion;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use stdClass;

class AsesorPdpController extends Controller
{
    /**
     * Muestra la lista de asesores con filtros.
     */
    public function gestionar(Request $request)
    {
        $configuracion = Configuracion::find(1);

        // OBTENCIÓN DE DATOS PARA FILTROS
        // ¡ASUNCIÓN! Asumo que tienes roles marcados con 'es_cajero' o 'es_encargado'
        // Igual que tenías 'es_maestro' en tu tabla 'roles'.
        $rolesAsesor = Role::where('es_cajero_pdp', true)->orWhere('es_encargado_pdp', true)->get();

        // INICIALIZACIÓN DE FILTROS
        $tagsBusqueda = [];
        $banderaFiltros = false;
        $filtros = $request->only([
            'filtro_busqueda_general',
            'filtro_estado_asesor',
            'filtro_tipo_asesor', // Nuevo filtro (cajero o encargado)
        ]);

        // CONSTRUCCIÓN DE LA CONSULTA BASE
        $queryAsesores = AsesorPdp::query()->with('user'); // ¡Cambiado!

        // APLICACIÓN DE FILTROS

        // Filtro de Búsqueda General
        if (!empty($filtros['filtro_busqueda_general'])) {
            $termino = strtolower($filtros['filtro_busqueda_general']);
            $queryAsesores->whereHas('user', function ($qUser) use ($termino) {
                $qUser->where(
                    fn($q) => $q->whereRaw('LOWER(primer_nombre) LIKE ?', ["%{$termino}%"])
                        ->orWhereRaw('LOWER(primer_apellido) LIKE ?', ["%{$termino}%"])
                        ->orWhereRaw('LOWER(identificacion) LIKE ?', ["%{$termino}%"])
                        ->orWhereRaw('LOWER(email) LIKE ?', ["%{$termino}%"])
                );
            });
            $tagsBusqueda[] = (object)['label' => $filtros['filtro_busqueda_general'], 'field' => 'filtro_busqueda_general'];
            $banderaFiltros = true;
        }

        // Filtro por Estado
        if (isset($filtros['filtro_estado_asesor']) && $filtros['filtro_estado_asesor'] !== '') {
            $queryAsesores->where('activo', (bool)$filtros['filtro_estado_asesor']);
            $label = 'Estado: ' . ((bool)$filtros['filtro_estado_asesor'] ? 'Activo' : 'Inactivo');
            $tagsBusqueda[] = (object)['label' => $label, 'field' => 'filtro_estado_asesor'];
            $banderaFiltros = true;
        }

        // ¡NUEVO FILTRO! Por tipo de asesor
        if (!empty($filtros['filtro_tipo_asesor'])) {
            if ($filtros['filtro_tipo_asesor'] == 'cajero') {
                $queryAsesores->where('es_cajero', true);
                $tagsBusqueda[] = (object)['label' => 'Tipo: Cajero', 'field' => 'filtro_tipo_asesor'];
            } elseif ($filtros['filtro_tipo_asesor'] == 'encargado') {
                $queryAsesores->where('es_encargado', true);
                $tagsBusqueda[] = (object)['label' => 'Tipo: Encargado', 'field' => 'filtro_tipo_asesor'];
            }
            $banderaFiltros = true;
        }

        // EJECUCIÓN FINAL DE LA CONSULTA
        $asesores = $queryAsesores->latest('created_at')->paginate(16); // ¡Cambiado!

        // DEVOLVER LA VISTA
        return view('contenido.paginas.puntos-de-pago.gestionar-asesores', [ // ¡Ruta cambiada!
            'asesores' => $asesores, // ¡Cambiado!
            'configuracion' => $configuracion,
            'tagsBusqueda' => $tagsBusqueda,
            'banderaFiltros' => $banderaFiltros,
            'rolesAsesor' => $rolesAsesor, // ¡Cambiado!
            'filtrosActuales' => $filtros,
        ]);
    }

    /**
     * Guarda un nuevo asesor.
     */
    public function guardar(Request $request)
    {
        $validados = $request->validate([
            'buscador-usuario' => 'required|integer',
            'descripcion' => 'nullable|string|max:1000',
            'activo' => 'required|boolean',
            'role_id' => 'required|integer',
            // ¡NUEVOS CAMPOS!
            'es_cajero' => 'nullable|boolean',
            'es_encargado' => 'nullable|boolean',
        ], [
            'buscador-usuario.required' => 'Debes seleccionar un usuario.',

            'role_id.required' => 'Debes seleccionar un rol para el asesor.',
        ]);

        try {
            $usuario = User::find($request->input('buscador-usuario'));

            // Asigna el ROL al usuario (idéntico al flujo de maestro)
            $usuario->roles()->attach($request->role_id, [
                'activo' => 0,
                'dependiente' => 0,
                'model_type' => 'App\Models\User'
            ]);

            // Crea el registro en la tabla 'asesores_pdp'
            AsesorPdp::create([
                'user_id' => $request->input('buscador-usuario'),
                'descripcion' => $request->descripcion,
                'activo' => $request->activo,
                // ¡NUEVOS CAMPOS!
                'es_cajero' => $request->boolean('es_cajero'), // .boolean() convierte 'on'/1 a true, null a false
                'es_encargado' => $request->boolean('es_encargado'),
            ]);

            return redirect()->route('asesores_pdp.gestionar')
                ->with('mensaje_exito', 'Asesor creado correctamente.');
        } catch (\Exception $e) {
            Log::error("Error al crear asesor: " . $e->getMessage());
            return back()->with('mensaje_error', 'Ocurrió un error al crear el asesor. Inténtalo de nuevo.')
                ->withInput();
        }
    }

    /**
     * Elimina un asesor y desvincula su rol.
     * NOTA: Se realiza una eliminación física (forceDelete) y se quitan los roles asociados.
     */
    public function eliminar(Request $request)
    {
        $request->validate(['asesor_id' => 'required|integer|exists:asesores_pdp,id']);
        $asesorId = $request->input('asesor_id');

        DB::beginTransaction();
        try {
            $asesor = AsesorPdp::with('user')->findOrFail($asesorId);
            $usuario = $asesor->user;
            $nombreUsuario = optional($usuario)->nombre(3) ?? 'Asesor ID ' . $asesor->id;

            if ($usuario) {
                // 1. Identificar roles de 'cajero' o 'encargado' que tiene el usuario
                // Se buscan roles con los flags 'es_cajero_pdp' o 'es_encargado_pdp'
                $rolesAsesorParaQuitar = $usuario->roles()
                    ->where(function ($q) {
                        $q->where('es_cajero_pdp', true)
                            ->orWhere('es_encargado_pdp', true);
                    })
                    ->pluck('id');

                // 2. Desvincular los roles encontrados
                // Esto retira los permisos de asesor al usuario
                if ($rolesAsesorParaQuitar->isNotEmpty()) {
                    $usuario->roles()->detach($rolesAsesorParaQuitar);
                }
            }

            // 3. Eliminar físicamente el registro de la tabla 'asesores_pdp'
            // Se usa forceDelete() para borrarlo definitivamente de la BD, no solo SoftDelete.
            $asesor->forceDelete(); 
            
            DB::commit();

            return redirect()->route('asesores_pdp.gestionar')
                ->with('mensaje_success', "Asesor '{$nombreUsuario}' eliminado y rol desvinculado correctamente.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al eliminar asesor ID {$asesorId}: " . $e->getMessage());
            return back()->with('mensaje_error', 'Ocurrió un error al eliminar el asesor.');
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
            return redirect()->route('asesores_pdp.gestionar')
                ->with('mensaje_exito', "El asesor '{$asesor->user->nombre(3)}' ha sido activado.");
        } catch (\Exception $e) {
            Log::error("Error al activar asesor ID {$asesor->id}: " . $e->getMessage());
            return back()->with('mensaje_error', 'Ocurrió un error al activar el asesor.');
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
            return redirect()->route('asesores_pdp.gestionar')
                ->with('mensaje_exito', "El asesor '{$asesor->user->nombre(3)}' ha sido desactivado.");
        } catch (\Exception $e) {
            Log::error("Error al desactivar asesor ID {$asesor->id}: " . $e->getMessage());
            return back()->with('mensaje_error', 'Ocurrió un error al desactivar el asesor.');
        }
    }
}
