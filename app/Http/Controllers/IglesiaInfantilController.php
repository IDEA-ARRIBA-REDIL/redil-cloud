<?php

namespace App\Http\Controllers;

use App\Exports\IglesiaInfantilExport;
use App\Models\EstacionSalonInfantil;
use App\Models\RegistroIglesiaInfantil;
use App\Models\ReporteReunion;
use App\Models\SalonInfantil;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class IglesiaInfantilController extends Controller
{
    // =========================================================================
    // ADMINISTRACIÓN — Salones y Estaciones
    // =========================================================================

    /** 1. Vista principal de administración */
    public function administracion()
    {
        $salones = SalonInfantil::with('estaciones')->orderBy('nombre')->get();
        $estaciones = EstacionSalonInfantil::orderBy('nombre')->get();

        return view('contenido.paginas.iglesia-infantil.administracion', compact('salones', 'estaciones'));
    }

    /** 2. Crear salón */
    public function crearSalon(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        SalonInfantil::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'activo' => $request->boolean('activo', true),
        ]);

        return redirect()->route('iglesiaInfantil.administracion')
            ->with('success', 'Salón creado correctamente.');
    }

    /** 3. Actualizar salón */
    public function actualizarSalon(Request $request, SalonInfantil $salon)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $salon->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'activo' => $request->boolean('activo', true),
        ]);

        return redirect()->route('iglesiaInfantil.administracion')
            ->with('success', 'Salón actualizado correctamente.');
    }

    /** 4. Eliminar salón (solo si no tiene registros asociados) */
    public function eliminarSalon(SalonInfantil $salon)
    {
        if ($salon->registros()->exists()) {
            return redirect()->route('iglesiaInfantil.administracion')
                ->with('error', 'No se puede eliminar el salón porque tiene registros de menores asociados.');
        }

        $salon->estaciones()->detach();
        $salon->delete();

        return redirect()->route('iglesiaInfantil.administracion')
            ->with('success', 'Salón eliminado correctamente.');
    }

    /** 5. Sincronizar estaciones de un salón */
    public function asignarEstacionesSalon(Request $request, SalonInfantil $salon)
    {
        $request->validate([
            'estaciones' => 'nullable|array',
            'estaciones.*' => 'integer|exists:estaciones_salon_infantil,id',
        ]);

        $salon->estaciones()->sync($request->estaciones ?? []);

        return redirect()->route('iglesiaInfantil.administracion')
            ->with('success', 'Estaciones del salón actualizadas correctamente.');
    }

    /** 6. Crear estación */
    public function crearEstacion(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
        ]);

        EstacionSalonInfantil::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('iglesiaInfantil.administracion')
            ->with('success', 'Estación creada correctamente.');
    }

    /** 7. Actualizar estación */
    public function actualizarEstacion(Request $request, EstacionSalonInfantil $estacion)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
        ]);

        $estacion->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('iglesiaInfantil.administracion')
            ->with('success', 'Estación actualizada correctamente.');
    }

    // =========================================================================
    // CHECK-IN — Vista operativa
    // =========================================================================

    /** 8. Vista de check-in */
    public function checkin()
    {
        $salones = SalonInfantil::with('estaciones')->activos()->orderBy('nombre')->get();

        return view('contenido.paginas.iglesia-infantil.checkin', compact('salones'));
    }

    /** 8.1. Obtiene los menores a cargo de un adulto (vía AJAX) */
    public function datosCheckinAdulto(Request $request, $user_id)
    {
        try {
            $user = User::findOrFail($user_id);
            $nombreCompleto = trim("{$user->primer_nombre} {$user->segundo_nombre} {$user->primer_apellido} {$user->segundo_apellido}");

            $reporteReunionId = $request->get('reporte_reunion_id');

            // Buscamos parientes menores de 18 años
            $menores = $user->parientesDelUsuario()->get()->filter(function ($pariente) {
                // Validación de edad segura
                if (! $pariente->fecha_nacimiento) {
                    return false;
                }

                try {
                    return $pariente->edad() < 18;
                } catch (\Exception $e) {
                    return false;
                }
            })->map(function ($menor) use ($reporteReunionId) {
                // Verificar si ya está registrado en este reporte (si se proporcionó el ID)
                $yaRegistrado = false;
                if ($reporteReunionId) {
                    $yaRegistrado = RegistroIglesiaInfantil::where('reporte_reunion_id', $reporteReunionId)
                        ->where('menor_user_id', $menor->id)
                        ->exists();
                }

                return [
                    'id' => $menor->id,
                    'nombre_completo' => trim("{$menor->primer_nombre} {$menor->segundo_nombre} {$menor->primer_apellido} {$menor->segundo_apellido}"),
                    'edad' => $menor->fecha_nacimiento ? $menor->edad() : 'N/A',
                    'identificacion' => $menor->identificacion,
                    'ya_registrado' => $yaRegistrado,
                ];
            })->values();

            return response()->json([
                'adulto' => [
                    'id' => $user->id,
                    'nombre_completo' => $nombreCompleto ?: $user->name,
                    'identificacion' => $user->identificacion,
                ],
                'menores' => $menores,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al procesar los datos: '.$e->getMessage(),
            ], 500);
        }
    }

    /** 9. Registrar un menor (check-in) */
    public function registrar(Request $request)
    {
        $request->validate([
            'reporte_reunion_id' => 'required|integer|exists:reporte_reuniones,id',
            'menor_user_id' => 'required|integer|exists:users,id',
            'adulto_ingreso_user_id' => 'required|integer|exists:users,id',
            'salon_infantil_id' => 'required|integer|exists:salones_infantil,id',
            'estacion_salon_infantil_id' => 'required|integer|exists:estaciones_salon_infantil,id',
            'indicaciones_medicas' => 'nullable|string',
        ]);

        // 1. Verificar que el menor no tenga ya un registro activo en este reporte
        $registroExistente = RegistroIglesiaInfantil::where('reporte_reunion_id', $request->reporte_reunion_id)
            ->where('menor_user_id', $request->menor_user_id)
            ->where('estado', 'en_custodia')
            ->exists();

        if ($registroExistente) {
            return redirect()->route('iglesiaInfantil.checkin')
                ->with('error', 'Este menor ya tiene un registro activo en el reporte de reunión seleccionado.');
        }

        // 2. Generar código único de retiro
        $codigoRetiro = RegistroIglesiaInfantil::generarCodigoRetiro($request->reporte_reunion_id);

        // 3. Crear el registro
        $registro = RegistroIglesiaInfantil::create([
            'reporte_reunion_id' => $request->reporte_reunion_id,
            'menor_user_id' => $request->menor_user_id,
            'adulto_ingreso_user_id' => $request->adulto_ingreso_user_id,
            'servidor_ingreso_user_id' => auth()->id(),
            'salon_infantil_id' => $request->salon_infantil_id,
            'estacion_salon_infantil_id' => $request->estacion_salon_infantil_id,
            'indicaciones_medicas' => $request->indicaciones_medicas,
            'codigo_retiro' => $codigoRetiro,
            'estado' => 'en_custodia',
            'fecha' => now()->toDateString(),
            'hora_entrada' => now()->format('H:i:s'),
        ]);

        return redirect()->route('iglesiaInfantil.registro.ticket', $registro)
            ->with('success', 'Menor registrado correctamente. Código de retiro: '.$codigoRetiro);
    }

    /** 10. Procesar retiro del menor */
    public function procesarRetiro(Request $request)
    {
        $request->validate([
            'codigo_retiro' => 'required|string',
            'adulto_retiro_user_id' => 'required|integer|exists:users,id',
        ]);

        // 1. Buscar registro activo con ese código
        $registro = RegistroIglesiaInfantil::where('codigo_retiro', strtoupper($request->codigo_retiro))
            ->where('estado', 'en_custodia')
            ->first();

        if (! $registro) {
            return redirect()->route('iglesiaInfantil.listaTurno')
                ->with('error', 'Código de retiro no encontrado o el menor ya fue entregado.');
        }

        // 2. Verificar que el adulto que retira sea el mismo que hizo el ingreso
        if ($registro->adulto_ingreso_user_id !== (int) $request->adulto_retiro_user_id) {
            return redirect()->route('iglesiaInfantil.listaTurno')
                ->with('error', 'El adulto que intenta retirar al menor no coincide con quien lo registró.');
        }

        // 3. Marcar como entregado
        $registro->update([
            'estado' => 'entregado',
            'adulto_retiro_user_id' => $request->adulto_retiro_user_id,
            'servidor_retiro_user_id' => auth()->id(),
            'hora_entrega' => now()->format('H:i:s'),
        ]);

        return redirect()->route('iglesiaInfantil.listaTurno')
            ->with('success', 'Menor entregado correctamente a las '.now()->format('H:i').'.');
    }

    /** 10b. Retiro rápido via QR — sin validación de adulto, responde JSON */
    public function retirarConQr(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'codigo_retiro' => 'required|string|max:20',
        ]);

        $registro = RegistroIglesiaInfantil::where('codigo_retiro', strtoupper($request->codigo_retiro))
            ->where('estado', 'en_custodia')
            ->first();

        if (! $registro) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Código no encontrado o el menor ya fue entregado.',
            ], 404);
        }

        $registro->update([
            'estado' => 'entregado',
            'servidor_retiro_user_id' => auth()->id(),
            'hora_entrega' => now()->format('H:i:s'),
        ]);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Menor entregado correctamente a las '.now()->format('H:i').'.',
            'menor' => $registro->menor?->nombre(3),
            'codigo' => $registro->codigo_retiro,
        ]);
    }

    // =========================================================================
    // LISTA DEL TURNO
    // =========================================================================

    /** 11. Vista de lista del turno activo */
    public function listaTurno(Request $request)
    {
        $reporteReunionId = $request->get('reporte_reunion_id');
        $buscar = $request->get('buscar');

        // Solo cargar registros si se ha seleccionado un reporte
        if (! $reporteReunionId) {
            $registros = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);

            return view('contenido.paginas.iglesia-infantil.lista-turno', compact('registros', 'reporteReunionId', 'buscar'));
        }

        $query = RegistroIglesiaInfantil::with([
            'menor',
            'adultoIngreso',
            'adultoRetiro',
            'servidorIngreso',
            'servidorRetiro',
            'salon',
            'estacion',
            'reporteReunion.reunion',
        ])->where('reporte_reunion_id', $reporteReunionId);

        if ($buscar) {
            $buscar = htmlspecialchars($buscar);
            $query->whereHas('menor', function ($q) use ($buscar) {
                $q->whereRaw(
                    "LOWER(CONCAT_WS(' ', primer_nombre, segundo_nombre, primer_apellido, segundo_apellido)) LIKE LOWER(?)",
                    ['%'.$buscar.'%']
                );
            })->orWhere('codigo_retiro', strtoupper($buscar));
        }

        $registros = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        return view('contenido.paginas.iglesia-infantil.lista-turno', compact('registros', 'reporteReunionId', 'buscar'));
    }

    /** 12. Actualizar salón y estación de un registro (solo si en_custodia) */
    public function actualizarSalonEstacion(Request $request, RegistroIglesiaInfantil $registro)
    {
        if ($registro->fueEntregado()) {
            return redirect()->route('iglesiaInfantil.listaTurno')
                ->with('error', 'No se puede modificar un registro que ya fue entregado.');
        }

        $request->validate([
            'salon_infantil_id' => 'required|integer|exists:salones_infantil,id',
            'estacion_salon_infantil_id' => 'required|integer|exists:estaciones_salon_infantil,id',
        ]);

        $registro->update([
            'salon_infantil_id' => $request->salon_infantil_id,
            'estacion_salon_infantil_id' => $request->estacion_salon_infantil_id,
        ]);

        return redirect()->route('iglesiaInfantil.listaTurno', ['reporte_reunion_id' => $registro->reporte_reunion_id])
            ->with('success', 'Salón y estación actualizados correctamente.');
    }

    /** 13. Eliminar un registro (solo si en_custodia) */
    public function eliminarRegistro(RegistroIglesiaInfantil $registro)
    {
        if ($registro->fueEntregado()) {
            return redirect()->route('iglesiaInfantil.listaTurno')
                ->with('error', 'No se puede eliminar un registro que ya fue entregado.');
        }

        $reporteReunionId = $registro->reporte_reunion_id;
        $registro->delete();

        return redirect()->route('iglesiaInfantil.listaTurno', ['reporte_reunion_id' => $reporteReunionId])
            ->with('success', 'Registro eliminado correctamente.');
    }

    // =========================================================================
    // TICKET DE IMPRESIÓN
    // =========================================================================

    /** 14. Vista de ticket de impresión (tamaño térmico) */
    public function imprimirTicket(RegistroIglesiaInfantil $registro)
    {
        $registro->load(['menor', 'adultoIngreso', 'salon', 'estacion', 'reporteReunion.reunion']);

        return view('contenido.paginas.iglesia-infantil.ticket', compact('registro'));
    }

    // =========================================================================
    // EXPORTAR EXCEL
    // =========================================================================

    /** 15. Exportar reporte Excel por reporte_reunion_id */
    public function exportarExcel(Request $request)
    {
        $request->validate([
            'reporte_reunion_id' => 'required|integer|exists:reporte_reuniones,id',
        ]);

        $reporteReunion = ReporteReunion::with('reunion')->find($request->reporte_reunion_id);
        $nombreArchivo = 'iglesia-infantil-'.$reporteReunion->reunion->nombre.'-'.$reporteReunion->fecha.'.xlsx';

        return Excel::download(
            new IglesiaInfantilExport($request->reporte_reunion_id),
            $nombreArchivo
        );
    }
}
