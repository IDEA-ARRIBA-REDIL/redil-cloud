<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\Configuracion;
use App\Models\Intercesor;
use App\Models\Role;
use App\Models\Sede;
use App\Models\TipoPeticion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use stdClass;

class IntercesorController extends Controller
{
    /**
     * Muestra la vista de gestión de intercesores con filtros y paginación.
     */
    public function gestionarIntercesores(Request $request): View
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('peticiones.subitem_gestionar_intercesores');

        $configuracion = Configuracion::find(1);
        $tagsBusqueda = [];
        $bandera = 0;
        $buscar = $request->input('buscar');

        $query = Intercesor::query();

        if ($buscar) {
            $buscarSaneado = htmlspecialchars($buscar);
            $buscarSaneado = Helpers::sanearStringConEspacios($buscar);
            $buscar = str_replace(["'"], '', $buscar);

            $query->leftJoin('users', 'intercesores.user_id', '=', 'users.id');

            $query->where(function ($q) use ($buscarSaneado, $buscar) {
                $q->whereRaw("LOWER( translate( CONCAT_WS(' ', users.primer_nombre, users.segundo_nombre, users.primer_apellido, users.segundo_apellido ) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%'.$buscarSaneado.'%'])
                    ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', users.primer_nombre, users.primer_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%'.$buscarSaneado.'%'])
                    ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', users.primer_nombre, users.segundo_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%'.$buscarSaneado.'%'])
                    ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', users.segundo_apellido, users.segundo_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%'.$buscarSaneado.'%'])
                    ->orWhereRaw('LOWER(users.email) LIKE LOWER(?)', ['%'.$buscar.'%'])
                    ->orWhereRaw('LOWER(users.identificacion) LIKE LOWER(?)', [$buscar.'%']);
            });

            // Crear una tag de búsqueda
            $tag = new stdClass;
            $tag->label = $buscar;
            $tag->field = 'buscar';
            $tag->value = $buscar;
            $tag->fieldAux = '';
            $tagsBusqueda[] = $tag;

            $bandera = 1;
        }

        $intercesores = $query->with(['usuario', 'sedes', 'tipoPeticiones'])
            ->select('intercesores.*')
            ->orderBy('intercesores.id', 'desc')
            ->paginate(9);

        $sedes = Sede::orderBy('nombre', 'asc')->select('id', 'nombre')->get();
        $tiposPeticion = TipoPeticion::orderBy('nombre', 'asc')->select('id', 'nombre')->get();

        return view('contenido.paginas.peticiones.gestionar-intercesores', [
            'intercesores' => $intercesores,
            'configuracion' => $configuracion,
            'tagsBusqueda' => $tagsBusqueda,
            'bandera' => $bandera,
            'buscar' => $buscar,
            'sedes' => $sedes,
            'tiposPeticion' => $tiposPeticion,
            'rolActivo' => $rolActivo,
        ]);
    }

    /**
     * Registra un nuevo intercesor en el sistema.
     */
    public function crearIntercesor(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::unique('intercesores', 'user_id'),
            ],
            'descripcion' => 'nullable|string|max:2000',
            'sedes' => 'nullable|array',
            'sedes.*' => 'integer|exists:sedes,id',
            'tiposPeticion' => 'nullable|array',
            'tiposPeticion.*' => 'integer|exists:tipo_peticiones,id',
            'solo_peticiones_asignadas' => 'nullable|boolean',
            'ver_peticiones_de_invitados' => 'nullable|boolean',
        ], [
            'user_id.required' => 'Debe seleccionar un usuario.',
            'user_id.unique' => 'Este usuario ya ha sido registrado como intercesor.',
        ]);

        DB::beginTransaction();

        try {
            $soloPeticionesAsignadas = $request->has('solo_peticiones_asignadas');
            $verPeticionesDeInvitados = $request->has('ver_peticiones_de_invitados');

            $intercesor = Intercesor::create([
                'user_id' => $validatedData['user_id'],
                'descripcion' => $validatedData['descripcion'] ?? null,
                'solo_peticiones_asignadas' => $soloPeticionesAsignadas,
                'ver_peticiones_de_invitados' => $soloPeticionesAsignadas ? false : $verPeticionesDeInvitados,
                'activo' => true,
            ]);

            if (!$soloPeticionesAsignadas) {
                if (!empty($validatedData['sedes'])) {
                    $intercesor->sedes()->sync($validatedData['sedes']);
                }

                if (!empty($validatedData['tiposPeticion'])) {
                    $intercesor->tipoPeticiones()->sync($validatedData['tiposPeticion']);
                }
            }

            // Asignar el rol de intercesor
            $rolIntercesor = Role::where('es_intercesor', true)->first();
            $usuario = User::find($validatedData['user_id']);

            if ($usuario && $rolIntercesor) {
                $usuario->roles()->attach($rolIntercesor->id, [
                    'activo' => false,
                    'dependiente' => false,
                    'model_type' => 'App\Models\User'
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'El intercesor fue registrado con éxito.');
        } catch (\Exception $e) {
            DB::rollBack();

            dd($e->getMessage());
            return redirect()->back()
                ->with('error', 'Hubo un problema al guardar el intercesor. Por favor, intente de nuevo.')
                ->withInput();
        }
    }

    /**
     * Actualiza los datos de un intercesor.
     */
    public function actualizarIntercesor(Request $request, Intercesor $intercesor): RedirectResponse
    {
        $validatedData = $request->validate([
            'descripcion' => 'nullable|string|max:2000',
            'sedes' => 'nullable|array',
            'sedes.*' => 'integer|exists:sedes,id',
            'tiposPeticion' => 'nullable|array',
            'tiposPeticion.*' => 'integer|exists:tipo_peticiones,id',
            'solo_peticiones_asignadas' => 'nullable|boolean',
            'ver_peticiones_de_invitados' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $soloPeticionesAsignadas = $request->has('solo_peticiones_asignadas');
            $verPeticionesDeInvitados = $request->has('ver_peticiones_de_invitados');

            $intercesor->update([
                'descripcion' => $validatedData['descripcion'] ?? null,
                'solo_peticiones_asignadas' => $soloPeticionesAsignadas,
                'ver_peticiones_de_invitados' => $soloPeticionesAsignadas ? false : $verPeticionesDeInvitados,
            ]);

            if ($soloPeticionesAsignadas) {
                // Si es solo peticiones asignadas, desasociamos sedes y tipos de peticiones
                $intercesor->sedes()->sync([]);
                $intercesor->tipoPeticiones()->sync([]);
            } else {
                $intercesor->sedes()->sync($request->input('sedes', []));
                $intercesor->tipoPeticiones()->sync($request->input('tiposPeticion', []));
            }

            DB::commit();

            return redirect()->back()->with('success', 'El intercesor fue actualizado con éxito.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar intercesor: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'Hubo un problema al actualizar el intercesor. Por favor, intente de nuevo.')
                ->withInput()
                ->with('origen_error', 'editar');
        }
    }

    /**
     * Activa un intercesor y le reasigna su rol.
     */
    public function activar(Intercesor $intercesor): RedirectResponse
    {
        $intercesor->activo = true;
        $intercesor->save();

        $rolIntercesor = Role::where('es_intercesor', true)->first();
        $usuario = User::find($intercesor->user_id);

        if ($usuario && $rolIntercesor) {
            $usuario->roles()->attach($rolIntercesor->id, [
                'activo' => false,
                'dependiente' => false,
                'model_type' => 'App\Models\User'
            ]);
        }

        return redirect()->back()->with('success', 'Intercesor activado exitosamente.');
    }

    /**
     * Desactiva un intercesor y le retira su rol.
     */
    public function desactivar(Intercesor $intercesor): RedirectResponse
    {
        $intercesor->activo = false;
        $intercesor->save();

        $usuario = $intercesor->usuario;
        $rolIntercesor = Role::where('es_intercesor', true)->first();

        if ($usuario && $rolIntercesor) {
            $usuario->roles()->detach($rolIntercesor->id);
        }

        return redirect()->back()->with('success', 'Intercesor desactivado exitosamente.');
    }

    /**
     * Elimina un intercesor y le retira su rol.
     */
    public function eliminarIntercesor(Intercesor $intercesor): RedirectResponse
    {
        try {
            $usuario = $intercesor->usuario;
            $rolIntercesor = Role::where('es_intercesor', true)->first();

            if ($usuario && $rolIntercesor) {
                $usuario->roles()->detach($rolIntercesor->id);
            }

            $intercesor->delete();

            return redirect()->back()->with('success', 'Intercesor eliminado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar intercesor: '.$e->getMessage());

            return redirect()->back()->with('error', 'No se pudo eliminar el intercesor. Es posible que tenga datos relacionados.');
        }
    }
}
