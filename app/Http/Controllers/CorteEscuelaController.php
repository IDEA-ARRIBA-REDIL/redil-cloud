<?php

namespace App\Http\Controllers;

use App\Models\CorteEscuela;
use App\Models\Escuela; // Necesario para la validación de porcentaje
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule; // Para reglas de validación avanzadas

class CorteEscuelaController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CorteEscuela  $corte // Route Model Binding
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, CorteEscuela $corte)
    {
      
        // Obtener la escuela a la que pertenece el corte
        $escuela = $corte->escuela;
        if (!$escuela) {
             return redirect()->back()->with('error', 'No se pudo encontrar la escuela asociada a este corte.');
        }

        // Validación de los datos de entrada
        $validatedData = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100'
            ],
            'orden' => [
                'required',
                'integer',
                'min:1'
               
            ],
            'porcentaje' => [
                'required',
                'integer',
                'min:0',
                'max:100',
                // Modificación: Validación personalizada para asegurar que la suma total NO SEA MAYOR a 100
                function ($attribute, $value, $fail) use ($escuela, $corte) {
                    // Obtener la suma de los porcentajes de los OTROS cortes de la misma escuela
                    $sumaOtrosPorcentajes = CorteEscuela::where('escuela_id', $escuela->id)
                                                        ->where('id', '!=', $corte->id) // Excluir el corte actual
                                                        ->sum('porcentaje');

                    // Calcular la suma total propuesta
                    $sumaTotalPropuesta = $sumaOtrosPorcentajes + (int)$value;

                    // Fallar solo si la suma supera 100
                    if ($sumaTotalPropuesta > 100) {
                        $fail("La suma total de los porcentajes de todos los cortes para esta escuela no puede superar el 100%. La suma actual sería {$sumaTotalPropuesta}%.");
                    }
                },
            ],
        ]);

        // Iniciar transacción por si acaso (aunque aquí es simple)
        DB::beginTransaction();
        try {
       
            // Actualizar el corte
            $corte->porcentaje=$request->porcentaje;
            $corte->nombre=$request->nombre;
            $corte->orden=$request->orden;
            $corte->save();
         
            DB::commit(); // Confirmar la transacción ANTES de calcular la suma final

            // Calcular la suma final de porcentajes DESPUÉS de la actualización
            $sumaFinal = CorteEscuela::where('escuela_id', $escuela->id)->sum('porcentaje');
            $mensajeAdvertencia = '';
            if ($sumaFinal < 100) {
                 $faltante = 100 - $sumaFinal;
                 // Mensaje de advertencia si la suma es menor a 100
                 $mensajeAdvertencia = " ADVERTENCIA: La suma actual de los porcentajes es {$sumaFinal}%. Falta un {$faltante}% para alcanzar el 100%.";
            }


            // Redirigir de vuelta a la vista de actualizar escuela con mensaje de éxito y posible advertencia
            return redirect()->route('escuelas.actualizar', ['escuela' => $corte->escuela_id])
                             ->with('success', "Corte '{$corte->nombre}' actualizado exitosamente." . $mensajeAdvertencia); // Añadir advertencia si existe

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar CorteEscuela ID {$corte->id}: " . $e->getMessage());
            return redirect()->route('escuelas.actualizar', ['escuela' => $corte->escuela_id])
                             ->with('error', 'Error al actualizar el corte: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CorteEscuela  $corte // Route Model Binding
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(CorteEscuela $corte)
    {
        $escuelaId = $corte->escuela_id; // Guardar ID antes de borrar
        $corteNombre = $corte->nombre;

        // **Validación Importante:** Verificar si el corte está en uso antes de eliminar.
        if ($corte->cortesPeriodo()->exists()) {
             return redirect()->route('escuelas.actualizar', ['escuela' => $escuelaId])
                              ->with('error', "No se puede eliminar el corte '{$corteNombre}' porque ya está siendo utilizado en uno o más periodos.");
        }
        if ($corte->itemPlantillas()->exists()) {
             return redirect()->route('escuelas.actualizar', ['escuela' => $escuelaId])
                              ->with('error', "No se puede eliminar el corte '{$corteNombre}' porque tiene ítems de calificación asociados.");
        }

        // **Validación Adicional:** Verificar si es el último corte
        $totalCortesEscuela = CorteEscuela::where('escuela_id', $escuelaId)->count();
        if ($totalCortesEscuela <= 1) {
             return redirect()->route('escuelas.actualizar', ['escuela' => $escuelaId])
                              ->with('error', "No se puede eliminar el corte '{$corteNombre}' porque es el último corte definido para esta escuela.");
        }

        // Iniciar transacción
        DB::beginTransaction();
        try {
            // Eliminar el corte
            $corte->delete();

            DB::commit(); // Confirmar ANTES de calcular suma restante

            // Calcular la suma de porcentajes restantes para mostrar advertencia
            $sumaRestante = CorteEscuela::where('escuela_id', $escuelaId)->sum('porcentaje');
            $mensajeAdvertencia = '';
            if ($sumaRestante !== 100) {
                 $diferencia = abs(100 - $sumaRestante); // Calcular diferencia absoluta
                 $mensajeAdvertencia = " ADVERTENCIA: La suma de los porcentajes de los cortes restantes es {$sumaRestante}%. " . ($sumaRestante < 100 ? "Falta un {$diferencia}%" : "Sobra un {$diferencia}%") . " para el 100%. Ajústalos manualmente.";
            }

            // Redirigir con mensaje de éxito y posible advertencia
            return redirect()->route('escuelas.actualizar', ['escuela' => $escuelaId])
                             ->with('success', "Corte '{$corteNombre}' eliminado exitosamente." . $mensajeAdvertencia);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al eliminar CorteEscuela ID {$corte->id}: " . $e->getMessage());
            return redirect()->route('escuelas.actualizar', ['escuela' => $escuelaId])
                             ->with('error', 'Error al eliminar el corte: ' . $e->getMessage());
        }
    }
}
