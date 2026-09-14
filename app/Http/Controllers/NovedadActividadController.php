<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Configuracion;
use App\Models\Materia;
use App\Models\NovedadActividad;
use App\Models\TipoNovedad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NovedadActividadController extends Controller
{
    /**
     * Muestra el formulario público para registrar una novedad sobre la actividad.
     */
    public function crear(Actividad $actividad)
    {
        $usuario = Auth::user();
        $configuracion = Configuracion::find(1);
        $tiposNovedad = TipoNovedad::activos()->orderBy('id')->get();

        // Si es actividad de escuelas, obtenemos las materias vinculadas a sus categorías
        $materiasEscuela = collect();
        if ($actividad->tipo && $actividad->tipo->tipo_escuelas) {
            $categoriasConMateria = $actividad->categorias()
                ->with('materiaPeriodo.materia')
                ->get();

            $materiasEscuela = $categoriasConMateria
                ->map(function ($cat) {
                    return $cat->materiaPeriodo?->materia;
                })
                ->filter()
                ->unique('id')
                ->values();
        }

        return view('contenido.paginas.actividades.novedades.registrar-novedad', [
            'actividad' => $actividad,
            'usuario' => $usuario,
            'configuracion' => $configuracion,
            'tiposNovedad' => $tiposNovedad,
            'materiasEscuela' => $materiasEscuela,
        ]);
    }

    /**
     * Procesa y guarda la novedad reportada por el usuario.
     */
    public function store(Request $request, Actividad $actividad)
    {
        $reglas = [
            'nombre' => 'required|string|max:150',
            'identificacion' => 'required|string|max:50',
            'telefono' => 'required|string|max:50',
            'email' => 'required|email|max:150',
            'tipo_novedad_id' => 'required|exists:tipos_novedad,id',
            'asunto' => 'required|string|max:200',
            'descripcion' => 'required|string|max:500',
            'materia_id' => 'nullable|exists:materias,id',
            'materia_nombre' => 'nullable|string|max:150',
        ];

        $mensajes = [
            'nombre.required' => 'El nombre completo es obligatorio.',
            'identificacion.required' => 'El número de identificación es obligatorio.',
            'telefono.required' => 'El teléfono de contacto es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debes ingresar un correo electrónico válido.',
            'tipo_novedad_id.required' => 'Debes seleccionar el tipo de novedad.',
            'tipo_novedad_id.exists' => 'El tipo de novedad seleccionado no es válido.',
            'asunto.required' => 'El asunto es obligatorio.',
            'descripcion.required' => 'La descripción del inconveniente es obligatoria.',
            'descripcion.max' => 'La descripción no puede superar los 500 caracteres.',
        ];

        $datosValidados = $request->validate($reglas, $mensajes);

        // Si se seleccionó materia por ID, obtenemos su nombre
        $materiaNombre = $datosValidados['materia_nombre'] ?? null;
        if (! empty($datosValidados['materia_id'])) {
            $materia = Materia::find($datosValidados['materia_id']);
            if ($materia) {
                $materiaNombre = $materia->nombre;
            }
        }

        NovedadActividad::create([
            'actividad_id' => $actividad->id,
            'user_id' => Auth::id(),
            'tipo_novedad_id' => $datosValidados['tipo_novedad_id'],
            'materia_id' => $datosValidados['materia_id'] ?? null,
            'materia_nombre' => $materiaNombre,
            'nombre' => trim($datosValidados['nombre']),
            'identificacion' => trim($datosValidados['identificacion']),
            'telefono' => trim($datosValidados['telefono']),
            'email' => trim($datosValidados['email']),
            'asunto' => trim($datosValidados['asunto']),
            'descripcion' => trim($datosValidados['descripcion']),
            'estado' => NovedadActividad::ESTADO_NO_REVISADO,
        ]);

        return redirect()->route('actividades.novedades.crear', $actividad)
            ->with('novedad_registrada', true);
    }

    /**
     * Muestra la vista de gestión administrativa de novedades.
     */
    public function gestion()
    {
        $usuario = Auth::user();
        $rolActivo = $usuario ? $usuario->roles()->wherePivot('activo', true)->first() : null;

        $tienePermiso = $usuario && (
            $usuario->can('actividades.ver_novedades') ||
            $usuario->can('actividades.ver_todas_las_actividades') ||
            ($rolActivo && ($rolActivo->hasPermissionTo('actividades.ver_novedades') || $rolActivo->hasPermissionTo('actividades.ver_todas_las_actividades')))
        );

        if (! $tienePermiso) {
            abort(403, 'No tienes permisos para ver la gestión de novedades.');
        }

        return view('contenido.paginas.actividades.novedades.gestion');
    }
}
