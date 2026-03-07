<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\CitaConsejeria;
use App\Models\Configuracion;
use App\Models\Consejero;
use App\Models\HorarioAdicionalConsejero;
use App\Models\HorarioBloqueadoConsejero;
use App\Models\HorarioHabitual;
use App\Models\Role;
use App\Models\Sede;
use App\Models\TipoConsejeria;
use App\Models\User;
use App\Models\EstadoTareaConsolidacion;
use App\Models\TareaConsolidacionUsuario;
use App\Models\TipoUsuario;
use App\Models\EstadoPasoCrecimientoUsuario;
use App\Models\PasoCrecimiento;
use Illuminate\Http\Request;
use Carbon\Carbon;

use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use stdClass;
use Illuminate\Support\Facades\Mail;
use App\Mail\DefaultMail;
use Illuminate\Support\Facades\Validator; // Importante para la validación
use Illuminate\Http\JsonResponse;

class ConsejeriaController extends Controller
{
    //

    public function misCitas($tipo = 'proximas')
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('consejeria.subitem_mis_citas');
        $usuario = auth()->user();
        $ahora = Carbon::now();

        // Base query for user's appointments
        $baseQuery = CitaConsejeria::where('user_id', $usuario->id);

        // 1. Calculate Indicators
        $indicadoresGenerales = [];

        // Indicator: Próximas Citas
        $proximasCount = (clone $baseQuery)->where('fecha_hora_inicio', '>=', $ahora)->count();
        $item = new stdClass();
        $item->nombre = 'Próximas citas';
        $item->url = 'proximas';
        $item->cantidad = $proximasCount;
        $item->color = '#ffffff'; // Primary color
        $item->icono = 'ti ti-calendar-time';
        $indicadoresGenerales[] = $item;

        // Indicator: Citas Pasadas
        $pasadasCount = (clone $baseQuery)->where('fecha_hora_inicio', '<', $ahora)->count();
        $item = new stdClass();
        $item->nombre = 'Citas anteriores';
        $item->url = 'pasadas';
        $item->cantidad = $pasadasCount;
        $item->color = '#ffffff'; // Secondary color
        $item->icono = 'ti ti-history';
        $indicadoresGenerales[] = $item;

        // Indicator: Citas Canceladas
        $canceladasCount = CitaConsejeria::onlyTrashed()->where('user_id', $usuario->id)->count();
        $item = new stdClass();
        $item->nombre = 'Citas canceladas';
        $item->url = 'canceladas';
        $item->cantidad = $canceladasCount;
        $item->color = '#ffffff'; // Danger color
        $item->icono = 'ti ti-circle-x';
        $indicadoresGenerales[] = $item;

        $indicadoresGenerales = collect($indicadoresGenerales);

        // 2. Filter Main List based on $tipo
        $citasQuery = clone $baseQuery;

        if ($tipo == 'pasadas') {
            $citasQuery->where('fecha_hora_inicio', '<', $ahora)
                       ->orderBy('fecha_hora_inicio', 'desc'); // Most recent past first
        } elseif ($tipo == 'canceladas') {
            $citasQuery = CitaConsejeria::onlyTrashed()->where('user_id', $usuario->id)
                                        ->orderBy('fecha_hora_inicio', 'desc');
        } else {
            // Default to 'proximas'
            $citasQuery->where('fecha_hora_inicio', '>=', $ahora)
                       ->orderBy('fecha_hora_inicio', 'asc'); // Closest future first
        }

        $citas = $citasQuery->with(['consejero.usuario', 'tipoConsejeria'])
                            ->paginate(12);

        return view('contenido.paginas.consejerias.mis-citas', [
            'citas' => $citas,
            'indicadoresGenerales' => $indicadoresGenerales,
            'tipo' => $tipo,
            'rolActivo' => $rolActivo
        ]);
    }

    public function cancelarCita(Request $request, CitaConsejeria $cita)
    {
        // Validate
        $request->validate([
            'notas_cancelacion' => 'nullable|string|max:1000',
        ]);

        // Update and delete
        $cita->update([
            'notas_cancelacion' => $request->notas_cancelacion,
            'cancelado_por' => auth()->id(),
        ]);
        $cita->delete();

        // --- Enviar Correos ---
        try {
            $paciente = $cita->user;
            $consejeroUser = $cita->consejero->usuario; // Relación consejero -> usuario
            $fechaFormateada = $cita->fecha_hora_inicio->format('d/m/Y h:i A');

            // 1. Correo al Paciente
            if ($paciente && $paciente->email) {
                $mailDataPaciente = new stdClass();
                $mailDataPaciente->subject = 'Cancelación de Cita de Consejería';
                $mailDataPaciente->nombre = $paciente->nombre(3);
                $mailDataPaciente->mensaje .= "<p>Te informamos que tu cita de consejería del <b>{$fechaFormateada}</b> con el consejero <b>{$consejeroUser->nombre(3)}</b> ha sido cancelada.</p>";
                
                if ($request->notas_cancelacion) {
                    $mailDataPaciente->mensaje .= "<p><b>Motivo:</b> {$request->notas_cancelacion}</p>";
                }

                $mailDataPaciente->mensaje .= "<p><i>Nota: Si has agregado esta cita a tu calendario personal, te recomendamos eliminarla.</i></p>";

                Mail::to($paciente->email)->send(new DefaultMail($mailDataPaciente));
            }

            // 2. Correo al Consejero
            if ($consejeroUser && $consejeroUser->email) {
                $mailDataConsejero = new stdClass();
                $mailDataConsejero->subject = 'Cita Cancelada por el Paciente';
                $mailDataConsejero->nombre = $consejeroUser->nombre(3);
                $mailDataConsejero->mensaje .= "<p>Te informamos que el paciente <b>{$paciente->nombre(3)}</b> ha cancelado la cita del <b>{$fechaFormateada}</b>.</p>";

                if ($request->notas_cancelacion) {
                    $mailDataConsejero->mensaje .= "<p><b>Motivo indicado:</b> {$request->notas_cancelacion}</p>";
                }

                $mailDataConsejero->mensaje .= "<p><i>Nota: Si has agregado esta cita a tu calendario personal, te recomendamos eliminarla.</i></p>";

                Mail::to($consejeroUser->email)->send(new DefaultMail($mailDataConsejero));
            }

        } catch (\Exception $e) {
            Log::error("Error enviando correos de cancelación de cita ID {$cita->id}: " . $e->getMessage());
            // No detenemos el flujo, solo logueamos el error
        }

        return redirect()->back()->with('success', 'Cita cancelada exitosamente.');
    }

    public function nuevaCita(User $usuario)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('consejeria.subitem_nueva_cita');
      return view('contenido.paginas.consejerias.nueva-cita', [
       'usuario' => $usuario
      ]);
    }

    public function reprogramarCita(Request $request, CitaConsejeria $cita)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('consejeria.opcion_reprogramar_cita'); 
        $origen = $request->input('origen', url()->previous());
        return view('contenido.paginas.consejerias.reprogramar-cita', [
            'cita' => $cita,
            'origen' => $origen,
            'rolActivo' => $rolActivo
        ]);
    }

    public function crearCita()
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('consejeria.subitem_crear_cita');
      return view('contenido.paginas.consejerias.mensaje-cita-exitosa', [
        'rolActivo' => $rolActivo
      ]);
    }

    public function gestionarConsejeros(Request $request)
    {
      $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();     
      $rolActivo->verificacionDelPermiso('consejeria.subitem_gestionar_consejeros');

      $consejeros = Consejero::paginate(12);
      $configuracion = Configuracion::find(1);
      $tagsBusqueda = [];
      $bandera = 0;
      $buscar = $request->input('buscar');

      $query = Consejero::query();

      if ($buscar) {
        $buscarSaneado = htmlspecialchars($buscar);
        $buscarSaneado = Helpers::sanearStringConEspacios($buscar);
        $buscar = str_replace(["'"], '', $buscar);

        $query->leftJoin('users', 'consejeros.user_id', '=', 'users.id');

        $query->where(function ($q) use ($buscarSaneado, $buscar) {
          $q->whereRaw("LOWER( translate( CONCAT_WS(' ', users.primer_nombre, users.segundo_nombre, users.primer_apellido, users.segundo_apellido ) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'] )
          ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', users.primer_nombre, users.primer_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'])
          ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', users.primer_nombre, users.segundo_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'])
          ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', users.segundo_apellido, users.segundo_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'])
          ->orWhereRaw("LOWER(users.email) LIKE LOWER(?)", ['%'. $buscar . '%'])
          ->orWhereRaw("LOWER(users.identificacion) LIKE LOWER(?)", [ $buscar . '%']);
        });

        // Crear una tag
        $tag = new stdClass();
        $tag->label = $buscar;
        $tag->field = 'buscar';
        $tag->value = $buscar;
        $tag->fieldAux = '';
        $tagsBusqueda[] = $tag;

        $bandera = 1;
      }

      $consejeros= $query->orderBy('consejeros.id', 'desc')->paginate(9);

      $tiposConsejeria = TipoConsejeria::orderBy('nombre','asc')->select('id', 'nombre')->get();
      $sedes = Sede::orderBy('nombre','asc')->select('id', 'nombre')->get();

      return view('contenido.paginas.consejerias.gestionar-consejeros', [
        'consejeros' => $consejeros,
        'configuracion' => $configuracion,
        'tagsBusqueda' => $tagsBusqueda,
        'consejeros' => $consejeros,
        'bandera' => $bandera,
        'buscar' => $buscar,
        'tiposConsejeria' => $tiposConsejeria,
        'sedes' => $sedes,
        'rolActivo' => $rolActivo,
      ]);
    }

    public function crearConsejero(Request $request)
    {


        // 1. Validación de todos los campos del formulario
        $validatedData = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                // Valida que el user_id sea único en la tabla 'consejeros'
                Rule::unique('consejeros', 'user_id')
            ],
            'descripción' => 'nullable|string|max:2000',

            // Valida que 'sedes' sea un array y que tenga al menos 1 ítem
            'sedes' => 'required|array|min:1',
            // Valida que CADA ítem en el array 'sedes' exista en la tabla 'sedes'
            'sedes.*' => 'integer|exists:sedes,id',

            // El name="" de tu form es "tiposConsejeria[]"
            'tiposConsejeria' => 'required|array|min:1',
            'tiposConsejeria.*' => 'integer|exists:tipo_consejerias,id',

            'atención_presencial' => 'nullable', // 'on' o null
            'atención_virtual' => 'nullable', // 'on' o null
            'dirección' => 'nullable|string|max:1000|required_if:atención_presencial,on',

            'duracion_cita' => 'required|integer|min:1',
            'tiempo_descanso' => 'required|integer|min:0',
            'antelacion_minima' => 'required|integer|min:0',
            'maximo_futuro' => 'required|integer|min:1',

        ], [
            // Mensajes de error personalizados
            'user_id.required' => 'Debe seleccionar un usuario.',
            'user_id.unique' => 'Este usuario ya ha sido registrado como consejero.',
            'sedes.required' => 'Debe seleccionar al menos una sede.',
            'tiposConsejeria.required' => 'Debe seleccionar al menos un tipo de consejería.',
            'dirección.required_if' => 'La dirección es obligatoria si se habilita la atención presencial.',
            'duracion_cita.required' => 'La duración de la cita es obligatoria.',
            'tiempo_descanso.required' => 'El tiempo de descanso es obligatorio.',
            'antelacion_minima.required' => 'Los días de antelación son obligatorios.',
            'maximo_futuro.required' => 'Los días máximos a futuro son obligatorios.',
        ]);



        // 2. Usar una transacción
        DB::beginTransaction();

        try {
            $consejero = Consejero::create([
                'user_id' => $validatedData['user_id'],
                'descripcion' => $validatedData['descripción'],
                'activo' => true, // O `false` si requiere activación manual
                'atencion_presencial' => isset($validatedData['atención_presencial']) && $validatedData['atención_presencial'] === 'on',
                'atencion_virtual' => isset($validatedData['atención_virtual']) && $validatedData['atención_virtual'] === 'on',
                'direccion' => $validatedData['dirección'] ?? null,
                'duracion_cita_minutos' => $validatedData['duracion_cita'],
                'buffer_entre_citas_minutos' => $validatedData['tiempo_descanso'],
                'dias_minimos_antelacion' => $validatedData['antelacion_minima'],
                'dias_maximos_futuro' => $validatedData['maximo_futuro'],
            ]);


            $consejero->sedes()->sync($validatedData['sedes']);
            $consejero->tipoConsejerias()->sync($validatedData['tiposConsejeria']);

            $rolConsejero =Role::where('es_consejero', true)->first();
            $usuario = User::find($validatedData['user_id']);

            if ($usuario && $rolConsejero) {
              $usuario->roles()->attach($rolConsejero->id, ['activo' => false, 'dependiente' => false, 'model_type' => 'App\Models\User']);
            }

            DB::commit();

            return back()->with('success', "El consejero fue asignado con éxito.");

        } catch (\Exception $e) {
            // 7. Si algo falla, revierte la transacción
            DB::rollBack();

            // Opcional: Registrar el error para depuración
            Log::error('Error al crear nuevo consejero: ' . $e->getMessage());

            // 8. Redireccionar de vuelta al formulario con un mensaje de error
            return redirect()->back()
                             ->with('error', 'Hubo un problema al guardar el consejero. Por favor, intente de nuevo.')
                             ->withInput(); // Devuelve los datos para no perderlos
        }
    }

    public function activar(Consejero $consejero)
    {
        $consejero->activo = true;
        $consejero->save();

        $rolConsejero =Role::where('es_consejero', true)->first();
        $usuario = User::find($consejero->user_id);

        if ($usuario && $rolConsejero) {
          $usuario->roles()->attach($rolConsejero->id, ['activo' => false, 'dependiente' => false, 'model_type' => 'App\Models\User']);
        }

        return redirect()->back()->with('success', 'Consejero activado exitosamente.');
    }

    public function desactivar(Consejero $consejero)
    {
        $consejero->activo = false;
        $consejero->save();

        // 1. Obtener el Usuario asociado al Consejero
        $usuario = $consejero->user; // Accede a la relación con el usuario

        // 2. Encontrar el Rol específico de Consejero
        $rolConsejero = Role::where('es_consejero', true)->first();

        // 3. Verificar si se encontró el usuario y el rol
        if ($usuario && $rolConsejero) {
            $usuario->roles()->detach($rolConsejero->id);
        }

        return redirect()->back()->with('success', 'Consejero desactivado exitosamente.');
    }

    /**
     * Elimina un consejero específico de la base de datos.
     * Ruta: DELETE /consejeria/{consejero}
     */
    public function eliminarConsejero(Consejero $consejero)
    {
        try {


            // 1. Obtener el Usuario asociado al Consejero
            $usuario = $consejero->user; // Accede a la relación con el usuario

            // 2. Encontrar el Rol específico de Consejero
            $rolConsejero = Role::where('es_consejero', true)->first();

            // 3. Verificar si se encontró el usuario y el rol
            if ($usuario && $rolConsejero) {
                $usuario->roles()->detach($rolConsejero->id);
            }

            // Gracias a onDelete('cascade') en tus migraciones,
            // al eliminar el consejero, se deberían borrar
            // sus registros en las tablas pivote automáticamente.
            $consejero->delete();

            // Redirecciona de vuelta (o a la lista principal)
            return redirect()->back()->with('success', 'Consejero eliminado exitosamente.');

            // Si prefieres redireccionar al listado:
            // return redirect()->route('consejeria.index')->with('success', 'Consejero eliminado exitosamente.');

        } catch (\Exception $e) {
            // Captura cualquier error (ej. restricción de BD)
            Log::error('Error al eliminar consejero: ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo eliminar el consejero. Es posible que tenga datos relacionados.');
        }
    }

    public function actualizarConsejero(Request $request, Consejero $consejero)
    {
        // 1. Validación de los campos
        $validatedData = $request->validate([
            'descripción' => 'nullable|string|max:2000',
            'sedes' => 'nullable|array', // Permitimos vacío
            'sedes.*' => 'integer|exists:sedes,id',
            'tiposConsejeria' => 'nullable|array', // Permitimos vacío
            'tiposConsejeria.*' => 'integer|exists:tipo_consejerias,id',
            'atención_presencial' => 'nullable', // 'on' o null
            'atención_virtual' => 'nullable', // 'on' o null
            'dirección' => 'nullable|string|max:1000|required_if:atención_presencial,on',
            'duracion_cita' => 'required|integer|min:1',
            'tiempo_descanso' => 'required|integer|min:0',
            'antelacion_minima' => 'required|integer|min:0',
            'maximo_futuro' => 'required|integer|min:1',
        ], [
            // Mensajes de error personalizados
            'dirección.required_if' => 'La dirección es obligatoria si se habilita la atención presencial.',
            'duracion_cita.required' => 'La duración de la cita es obligatoria.',
            'tiempo_descanso.required' => 'El tiempo de descanso es obligatorio.',
            'antelacion_minima.required' => 'Los días de antelación son obligatorios.',
            'maximo_futuro.required' => 'Los días máximos a futuro son obligatorios.',
        ]);

        // 2. Usar una transacción
        DB::beginTransaction();

        try {
            // 3. Actualizar el registro principal del Consejero
            $consejero->update([
                'descripcion' => $validatedData['descripción'],
                'atencion_presencial' => isset($validatedData['atención_presencial']) && $validatedData['atención_presencial'] === 'on',
                'atencion_virtual' => isset($validatedData['atención_virtual']) && $validatedData['atención_virtual'] === 'on',
                'direccion' => $validatedData['dirección'] ?? null,
                'duracion_cita_minutos' => $validatedData['duracion_cita'],
                'buffer_entre_citas_minutos' => $validatedData['tiempo_descanso'],
                'dias_minimos_antelacion' => $validatedData['antelacion_minima'],
                'dias_maximos_futuro' => $validatedData['maximo_futuro'],
            ]);

            // 4. Sincronizar las relaciones (elimina las viejas, añade las nuevas)
            // Si no viene el campo, asumimos array vacío
            $consejero->sedes()->sync($request->input('sedes', []));
            $consejero->tipoConsejerias()->sync($request->input('tiposConsejeria', []));

            // 5. Si todo salió bien, confirma la transacción
            DB::commit();

            return back()->with('success', "El consejero fue actualizado con éxito.");

        } catch (\Exception $e) {
            // 6. Si algo falla, revierte la transacción
            DB::rollBack();

            // Opcional: Registrar el error
            Log::error('Error al actualizar consejero: ' . $e->getMessage());

            // 7. Redireccionar con error y 'origen_error' para reabrir el offcanvas correcto
            return redirect()->back()
            ->with('error', 'Hubo un problema al actualizar el consejero. Por favor, intente de nuevo.')
            ->withInput()
            ->with('origen_error', 'editar'); // Variable clave para el JS
        }
    }

    public function configurarHorariosCosejero(Request $request, Consejero $consejero)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('consejeria.opcion_configurar_horarios');
        
        // Formateamos los días de la semana
        $diasSemana = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ];

        // Obtenemos los horarios existentes y los agrupamos por día
        $horariosExistentes = $consejero->horariosHabituales()
                                ->orderBy('hora_inicio', 'asc')
                                ->get()
                                ->groupBy('dia_semana');

        return view('contenido.paginas.consejerias.configurar-horarios-cosejero', [
          'consejero' => $consejero,
          'diasSemana' => $diasSemana,
          'horariosExistentes' => $horariosExistentes
        ]);
    }

    public function calendarioDeFechasConsejero(Request $request, Consejero $consejero)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('consejeria.opcion_configurar_horarios');

        // 1. Obtenemos los días que SÍ trabaja (formato Laravel: 1=Lunes..7=Domingo)
        $diasHabituales = $consejero->horariosHabituales()
                            ->select('dia_semana')
                            ->distinct()
                            ->pluck('dia_semana')
                            ->all(); // Ejem: [1, 2, 3, 4, 5]

        // 2. Encontramos los días que NO trabaja
        $todosLosDias = [1, 2, 3, 4, 5, 6, 7]; // Lunes a Domingo
        $diasNoHabituales = array_diff($todosLosDias, $diasHabituales); // Ejem: [6, 7]

        // 3. Mapeamos a formato FullCalendar (0=Domingo..6=Sábado)
        $diasNoHabitualesFC = array_map(function($dia) {
            return $dia == 7 ? 0 : $dia; // Convierte Domingo (7) a (0)
        }, $diasNoHabituales);

        // Ya no necesitamos crear el JSON aquí
        return view('contenido.paginas.consejerias.calendario-de-fechas-consejero', [
            'consejero' => $consejero,
            'diasNoHabitualesFC' => array_values($diasNoHabitualesFC)
        ]);
    }


    /**
     * Actualiza (sincroniza) el horario habitual de un consejero.
     */
    public function actualizarHorarioHabitual(Request $request, Consejero $consejero)
    {
        // 1. VALIDACIÓN BÁSICA (Campos vacíos y Fin > Inicio)
        $validator = Validator::make($request->all(), [
            'horarios' => 'nullable|array',
            'horarios.*.*.inicio' => 'required|date_format:H:i',
            'horarios.*.*.fin' => 'required|date_format:H:i|after:horarios.*.*.inicio',
        ], [
            'horarios.*.*.inicio.required' => 'La hora de inicio es obligatoria.',
            'horarios.*.*.fin.required' => 'La hora de fin es obligatoria.',
            'horarios.*.*.fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $horariosInput = $request->input('horarios', []);
        $nuevosHorarios = [];

        // 2. VALIDACIÓN AVANZADA (Evitar Solapamientos)
        foreach ($horariosInput as $diaSemana => $franjas) {
            // Convertir a objetos Carbon para ordenar
            $slots = [];
            foreach ($franjas as $franja) {
                $slots[] = [
                    'inicio' => Carbon::parse($franja['inicio']),
                    'fin' => Carbon::parse($franja['fin']),
                ];
            }

            // Ordenar por hora de inicio
            usort($slots, fn($a, $b) => $a['inicio'] <=> $b['inicio']);

            // Comprobar solapamiento
            // Compara el FIN de una franja con el INICIO de la siguiente
            for ($i = 0; $i < count($slots) - 1; $i++) {
                if ($slots[$i]['fin'] > $slots[$i+1]['inicio']) {
                    // ¡HAY UN SOLAPAMIENTO!
                    return response()->json([
                        'message' => 'Error de validación: Tienes franjas horarias que se superponen.',
                        'errors' => ['horarios' => ['No puedes tener franjas horarias que se superpongan en el mismo día.']]
                    ], 422);
                }
            }

            // Si no hay solapamientos, preparar para la inserción
            foreach ($slots as $slot) {
                $nuevosHorarios[] = [
                    'consejero_id' => $consejero->id,
                    'dia_semana' => $diaSemana,
                    'hora_inicio' => $slot['inicio']->format('H:i:s'),
                    'hora_fin' => $slot['fin']->format('H:i:s'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // 3. GUARDAR EN BASE DE DATOS (Transacción)
        try {
            DB::transaction(function () use ($consejero, $nuevosHorarios) {
                // Borramos solo los horarios de ESTE consejero
                $consejero->horariosHabituales()->delete();

                // Insertamos los nuevos (si hay alguno)
                if (!empty($nuevosHorarios)) {
                    HorarioHabitual::insert($nuevosHorarios);
                }
            });
        } catch (\Exception $e) {
            Log::error("Error al actualizar horario habitual: " . $e->getMessage());
            return response()->json(['message' => 'Error interno del servidor. No se pudo guardar el horario.'], 500);
        }

        // 4. DEVOLVER RESPUESTA DE ÉXITO
        return response()->json([
            'message' => 'Horario habitual actualizado con éxito.'
        ]);
    }

    /**
     * Almacena un nuevo horario extendido para un consejero.
     */
    public function addHorarioExtendido(Request $request, Consejero $consejero): JsonResponse
    {
        // 1. Definimos las reglas y mensajes de validación
        $reglas = [
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'motivo' => 'nullable|string|max:255',
        ];

        $mensajes = [
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ];

        // 2. Creamos la instancia del validador
        $validator = Validator::make($request->all(), $reglas, $mensajes);

        // 3. Comprobamos si la validación falla
        if ($validator->fails()) {
            // Devolvemos los errores en formato JSON con el código 422
            // El JavaScript está preparado para leer esto
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // 4. Si la validación pasa, obtenemos los datos validados
        $datosValidados = $validator->validated();

        // 5. Procedemos a guardar (tu lógica original)
        try {
            // Usamos la relación para crear el nuevo horario
            $consejero->horariosAdicionales()->create($datosValidados);

            // Respondemos con éxito
            return response()->json([
                'success' => true,
                'message' => 'Horario extendido guardado con éxito.'
            ]);

        } catch (\Exception $e) {
            // Manejo de cualquier error inesperado
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado al guardar el horario.'
            ], 500); // Código de error 500
        }
    }

    /**
     * Almacena un nuevo horario BLOQUEADO para un consejero.
     */
    public function addHorarioBloqueado(Request $request, Consejero $consejero): JsonResponse
    {
        // 1. Definimos las reglas (apuntan a los 'name' del formulario)
        // Son los mismos 'name' que el formulario de horario extendido
        $reglas = [
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'motivo' => 'nullable|string|max:255',
        ];

        $mensajes = [
            'fecha_inicio.required' => 'La fecha de inicio del bloqueo es obligatoria.',
            'fecha_fin.required' => 'La fecha de fin del bloqueo es obligatoria.',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ];

        // 2. Creamos la instancia del validador
        $validator = Validator::make($request->all(), $reglas, $mensajes);

        // 3. Comprobamos si la validación falla
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // 4. Si la validación pasa, obtenemos los datos validados
        $datosValidados = $validator->validated();

        // 5. Procedemos a guardar en la tabla de bloqueados
        try {
            // Usamos la relación 'horariosBloqueados' que definimos en el Paso 1
            $consejero->horariosBloqueados()->create($datosValidados);

            // Respondemos con éxito
            return response()->json([
                'success' => true,
                'message' => 'Horario bloqueado guardado con éxito.'
            ]);

        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado al guardar el bloqueo.'
            ], 500);
        }
    }

    /**
     * Elimina un horario adicional. (VERSIÓN AJAX)
     */
    public function eliminarHorarioAdicional($id): JsonResponse
    {
        try {
            $horario = HorarioAdicionalConsejero::findOrFail($id);
            $horario->delete();

            // ¡CAMBIO! Devolvemos JSON
            return response()->json(['success' => true, 'message' => 'Horario extendido eliminado.']);

        } catch (\Exception $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'No se pudo eliminar el horario.'], 500);
        }
    }

    /**
     * Elimina un horario bloqueado. (VERSIÓN AJAX)
     */
    public function eliminarHorarioBloqueado($id): JsonResponse
    {
        try {
            $horario = HorarioBloqueadoConsejero::findOrFail($id);
            $horario->delete();

            // ¡CAMBIO! Devolvemos JSON
            return response()->json(['success' => true, 'message' => 'Horario bloqueado eliminado.']);

        } catch (\Exception $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'No se pudo eliminar el bloqueo.'], 500);
        }
    }

    /**
     * (¡NUEVO MÉTODO!)
     * Devuelve los eventos en formato JSON para FullCalendar.
     */
    public function obtenerHorariosCalendario(Request $request, Consejero $consejero): JsonResponse
    {
        // 1. OBTENER HORARIOS EXTENDIDOS (VERDES)
        $eventosAdicionales = $consejero->horariosAdicionales()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->motivo ?? 'Horario adiccional',
                    'start' => $item->fecha_inicio,
                    'end' => $item->fecha_fin,
                    'color' => '#28a745',
                    'textColor' => '#FFFFFF',
                    'allDay' => false,
                    'extendedProps' => [ 'tipo_evento' => 'adicional' ]
                ];
            });

        // 2. OBTENER HORARIOS BLOQUEADOS (ROJOS)
        $eventosBloqueados = $consejero->horariosBloqueados()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->motivo ?? 'Bloqueado',
                    'start' => $item->fecha_inicio,
                    'end' => $item->fecha_fin,
                    'color' => '#dc3545',
                    'textColor' => '#FFFFFF',
                    'allDay' => false,
                    'extendedProps' => [ 'tipo_evento' => 'bloqueado' ]
                ];
            });

        $eventos = $eventosAdicionales->merge($eventosBloqueados);

        // 3. Devolver como JSON
        return response()->json($eventos);
    }

    public function actualizarHorarioAdicional(Request $request, $id): JsonResponse
    {

        $reglas = [
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'motivo' => 'nullable|string|max:255',
        ];

        $mensajes = [
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ];

        $validator = Validator::make($request->all(), $reglas, $mensajes);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            $horario = HorarioAdicionalConsejero::findOrFail($id);
            $horario->update($validator->validated());
            return response()->json(['success' => true, 'message' => 'Horario adicional actualizado.']);
        } catch (\Exception $e) {
            // Manejo de cualquier error inesperado
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado al guardar el horario.'
            ], 500); // Código de error 500
        }
    }

    public function actualizarHorarioBloqueado(Request $request, $id): JsonResponse
    {
        $reglas = [
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'motivo' => 'nullable|string|max:255',
        ];

        $mensajes = [
            'fecha_inicio.required' => 'La fecha de inicio del bloqueo es obligatoria.',
            'fecha_fin.required' => 'La fecha de fin del bloqueo es obligatoria.',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ];

        $validator = Validator::make($request->all(), $reglas, $mensajes);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }try {
            $horario = HorarioBloqueadoConsejero::findOrFail($id);
            $horario->update($validator->validated());
            return response()->json(['success' => true, 'message' => 'Horario bloqueado actualizado.']);
        } catch (\Exception $e) {
            // Manejo de cualquier error inesperado
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado al guardar el horario.'
            ], 500); // Código de error 500
        }
    }

    public function mensajeExitoso(CitaConsejeria $cita)
    {
        return view('contenido.paginas.consejerias.mensaje-cita-exitosa',
          [
            'cita' => $cita
          ]
        );
    }



    /*
    Calendario que usa el consejero para ver sus citas
    */
    public function calendarioCitas()
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('consejeria.subitem_calendario_citas');
        
        $consejero = auth()->user()->consejero;  
        $estados = EstadoTareaConsolidacion::orderBy('puntaje', 'asc')->get();
        $estadosPasos = EstadoPasoCrecimientoUsuario::orderBy('puntaje', 'asc')->get();
        $tiposUsuario = TipoUsuario::orderBy('nombre', 'asc')->get();

        return view('contenido.paginas.consejerias.calendario-citas-consejero', [
            'consejero' => $consejero,
            'estados' => $estados,
            'estadosPasos' => $estadosPasos,
            'tiposUsuario' => $tiposUsuario
        ]);
    }

    public function obtenerCitasCalendario(Request $request)
    {
        $consejero = auth()->user()->consejero;

        if (!$consejero) {
            return response()->json([]);
        }

        // Fetch appointments for this counselor
        // We can filter by date range if sent by FullCalendar (start/end params)
        $start = $request->input('start');
        $end = $request->input('end');

        $query = CitaConsejeria::where('consejero_id', $consejero->id)
                ->withTrashed() // Include cancelled appointments
                ->with(['user', 'tipoConsejeria.tareasConsolidacion', 'canceladoPorUser']); // Eager load relationships

        if ($start && $end) {
            $query->whereBetween('fecha_hora_inicio', [$start, $end]);
        }

        $citas = $query->get();

        $eventos = $citas->map(function ($cita) {
            $titulo = $cita->user ? $cita->user->nombre_completo : 'Usuario desconocido';
            
            // You might want to append the type of counseling to the title
            if ($cita->tipoConsejeria) {
                $titulo .= ' - ' . $cita->tipoConsejeria->nombre;
            }

            $isCancelled = $cita->trashed();
            $isConcluida = $cita->concluida;
            
            $color = '#3380f3ff'; // Azul por defecto (Activa)

            if ($isCancelled) {
                $color = '#EA5455'; 
                $titulo .= ' (Cancelada)';
            } elseif ($isConcluida) { 
                $color = '#00C851'; 
                $titulo .= ' (Concluida)';
            }
          
            $telefonos = collect([
                $cita->user->telefono_fijo,
                $cita->user->telefono_movil,
                $cita->user->telefono_otro
            ])->filter();

            $textoTelefonos = $telefonos->isNotEmpty() ? $telefonos->implode(', ') : 'No indicados';

            return [
                'id' => $cita->id,
                'title' => $titulo,
                'start' => $cita->fecha_hora_inicio->toIso8601String(),
                'end' => $cita->fecha_hora_fin->toIso8601String(),
                'color' => $color,
                'textColor' => '#FFFFFF',
                'allDay' => false,
                'extendedProps' => [
                    'tipo_evento' => 'cita',
                    'cita_id' => $cita->id,
                    'paciente' => $cita->user ? $cita->user->nombre(3) : 'N/A',
                    'paciente_nombre' => $cita->user ? $cita->user->name . ' ' . $cita->user->last_name : 'Sin paciente',
                    'paciente_telefono' => $cita->user ? $cita->user->telefono : '',
                    'telefonos' => $textoTelefonos ?? 'N/A',
                    'tipo_consejeria' => $cita->tipoConsejeria ? $cita->tipoConsejeria->nombre : 'N/A',
                    'medio' => $cita->medio_id == 1 ? 'Virtual' : 'Presencial',
                    'ubicacion' => $cita->ubicacion,
                    'enlace_reunion' => $cita->enlace_reunion,
                    'tipo_usuario_id' => $cita->user ? $cita->user->tipo_usuario_id : null,
                    'notas' => $cita->notas_paciente ?? 'Sin notas adicionales',
                    'estado' => $isCancelled ? 'Cancelada' : 'Activa',
                    'is_cancelled' => $isCancelled,
                    'is_concluida' => $isConcluida, 
                    'notas_cancelacion' => $cita->notas_cancelacion,
                    'cancelado_por' => $cita->canceladoPorUser ? $cita->canceladoPorUser->nombre(3) : 'Desconocido',
                    'tareas' => $cita->tipoConsejeria ? $cita->tipoConsejeria->tareasConsolidacion->map(function($tarea) use ($cita) {
                        
                        // Buscar si el usuario tiene esta tarea asignada
                        $tareaAsignada = $cita->user ? $cita->user->tareasConsolidacion->find($tarea->id) : null;
                        
                        $estadoData = null;
                        if ($tareaAsignada && $tareaAsignada->pivot && $tareaAsignada->pivot->estado) {
                             $estadoModel = $tareaAsignada->pivot->estado; 
                             $estadoData = [
                                 'id' => $estadoModel->id,
                                 'nombre' => $estadoModel->nombre,
                                 'color' => $estadoModel->color,
                                 'fecha' => $tareaAsignada->pivot->fecha
                             ];
                        }

                        return [
                            'id' => $tarea->id,
                            'nombre' => $tarea->nombre,
                            'descripcion' => $tarea->descripcion,
                            'estado_actual' => $estadoData
                        ];
                    }) : [],
                    'pasos_crecimiento' => $cita->tipoConsejeria ? $cita->tipoConsejeria->pasosCrecimiento->map(function($paso) use ($cita) {
                        
                        // Buscar si el usuario tiene este paso registrado en crecimiento_usuario
                        // La relación en User es pasosCrecimiento()
                        $pasoUsuario = $cita->user ? $cita->user->pasosCrecimiento()->where('paso_crecimiento_id', $paso->id)->first() : null;

                        $estadoData = null;
                        // En crecimiento_usuario, el estado se guarda en 'estado_id' y hay relación 'estado' en el modelo Pivot si se usara pivot, 
                        // pero aquí usamos el modelo CrecimientoUsuario o la relación belongsToMany con pivot.
                        // Revisando User.php: public function pasosCrecimiento() ... withPivot('estado_id', ...)
                        
                        if ($pasoUsuario && $pasoUsuario->pivot && $pasoUsuario->pivot->estado_id) {
                             // Necesitamos obtener el objeto estado. 
                             // Podemos cargarlo o buscarlo. Como es un ID, lo buscamos.
                             // Para optimizar, podríamos cargar la relación en la consulta principal, pero aquí lo haremos directo por simplicidad o usar el helper si existe.
                             $estadoModel = EstadoPasoCrecimientoUsuario::find($pasoUsuario->pivot->estado_id);
                             
                             if ($estadoModel) {
                                $estadoData = [
                                    'id' => $estadoModel->id,
                                    'nombre' => $estadoModel->nombre,
                                    'color' => $estadoModel->color,
                                    'fecha' => $pasoUsuario->pivot->fecha
                                ];
                             }
                        }

                        return [
                            'id' => $paso->id,
                            'nombre' => $paso->nombre,
                            'descripcion' => $paso->descripcion,
                            'estado_actual' => $estadoData
                        ];
                    }) : []
                ]
            ];
        });

        return response()->json($eventos);
    }


    public function concluirCita(Request $request, CitaConsejeria $cita)
    {
        // Validamos que la cita no esté cancelada (soft deleted)
        if ($cita->trashed()) {
            return redirect()->back()->with('error', 'No se puede concluir una cita que ha sido cancelada.');
        }
        
        // 1. Validación
        $request->validate([
            'conclusiones_consejero' => 'nullable|string|max:2000',
        ]);
        
        // 2. Actualización del estado
        $cita->update([
            'concluida' => true,
            'conclusiones_consejero' => $request->conclusiones_consejero,
        ]);

        // 3. Guardar Tareas (si existen)
        if ($request->has('tareas')) {
            $tareasData = $request->input('tareas'); // Array [tarea_id => estado_id]
            
            foreach ($tareasData as $tareaId => $estadoId) {
                // Verificar si ya existe asignación para no duplicar (opcional, o usar updateOrCreate)
                // Aquí asumimos que se crea una nueva asignación o se actualiza la existente para este usuario
                
                // Opción A: Crear siempre nuevo registro (Historial)
                // TareaConsolidacionUsuario::create([
                //     'user_id' => $cita->user_id,
                //     'tarea_consolidacion_id' => $tareaId,
                //     'estado_tarea_consolidacion_id' => $estadoId,
                //     'fecha' => now(),
                //     'asignado_por' => auth()->id() // Si tuvieras este campo
                // ]);

                // Opción B: updateOrCreate (Mantiene una única asignación activa por tarea/usuario si así lo deseas)
                // En este caso, usaremos create para dejar registro, o updateOrCreate si la lógica de negocio es "estado actual".
                // Basado en "TareaConsolidacionUsuario" que parece ser una tabla pivote con historial aparte,
                // vamos a usar syncWithoutDetaching o create directo.
                // Dado el modelo TareaConsolidacionUsuario, parece ser un Pivot model.
                
                // Vamos a usar la relación del usuario para adjuntar/actualizar
                $cita->user->tareasConsolidacion()->syncWithoutDetaching([
                    $tareaId => [
                        'estado_tarea_consolidacion_id' => $estadoId,
                        'fecha' => now(),
                        // 'cita_consejeria_id' => $cita->id // Si quisieras vincularlo a la cita específica
                    ]
                ]);
            }
        }

        // 4. Guardar Pasos de Crecimiento (si existen)
        if ($request->has('pasos')) {
            $pasosData = $request->input('pasos'); // Array [paso_id => estado_id]
            
            foreach ($pasosData as $pasoId => $estadoId) {
                // Usamos la relación pasosCrecimiento del usuario
                // Tabla pivote: crecimiento_usuario
                // Campos pivot: estado_id, fecha, detalle
                
                $cita->user->pasosCrecimiento()->syncWithoutDetaching([
                    $pasoId => [
                        'estado_id' => $estadoId,
                        'fecha' => now(),
                        // 'detalle' => 'Actualizado desde cita de consejería' 
                    ]
                ]);
            }
        }

        // 5. Actualizar Tipo de Usuario (si se envía y es diferente)
        if ($request->has('tipo_usuario_id') && $cita->user) {
            $nuevoTipoUsuarioId = $request->input('tipo_usuario_id');
            $usuario = $cita->user;

            if ($usuario->tipo_usuario_id != $nuevoTipoUsuarioId) {
                $usuario->tipo_usuario_id = $nuevoTipoUsuarioId;
                
                // Lógica de roles dependientes
                $rolDependiente = $usuario
                    ->roles()
                    ->wherePivot('dependiente', '=', true)
                    ->first();

                $tipoUsuarioActual = TipoUsuario::find($usuario->tipo_usuario_id);

                if ($tipoUsuarioActual && $rolDependiente && $tipoUsuarioActual->id_rol_dependiente != $rolDependiente->id) {
                    // Asignar nuevo rol dependiente
                    if ($tipoUsuarioActual->id_rol_dependiente) {
                         $usuario->roles()->attach($tipoUsuarioActual->id_rol_dependiente, [
                             'activo' => $rolDependiente->pivot->activo, 
                             'dependiente' => true, 
                             'model_type' => 'App\Models\User'
                         ]);
                    }
                    // Remover rol anterior
                    $usuario->removeRole($rolDependiente);
                }

                $usuario->save();
            }
        }

        // Opcional: Notificar al paciente que la cita ha sido marcada como concluida/realizada.
        // ... lógica de envío de correo/notificación aquí ...

        return redirect()->back()->with('success', 'Cita marcada como concluida exitosamente.');
    }

}
