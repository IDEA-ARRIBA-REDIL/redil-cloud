<?php

namespace App\Livewire\Generales;

use Livewire\Component;
use App\Models\SemanaDeshabilitada;
use Carbon\Carbon;
use stdClass; // Usaremos stdClass para crear objetos para cada semana

class ConfigurarSemanas extends Component
{
    public $añoSeleccionado;
    public array $semanas = [];
    public $x = "sdf";

    public bool $filtroHabilitadas = true;
    public bool $filtroDeshabilitadas = true;

    public function mount()
    {
        $this->añoSeleccionado = date('Y');
        $this->cargarSemanas();
    }

    public function cargarSemanas()
    {
        $this->semanas = [];
        $anio = (int)$this->añoSeleccionado;

        // 1. Obtenemos todas las semanas deshabilitadas para el año seleccionado DE UNA SOLA VEZ.
        // Esto es mucho más eficiente que hacer una consulta por cada semana en el bucle.
        $semanasDeshabilitadas = SemanaDeshabilitada::where('anio', $anio)
            ->pluck('numero_semana') // Obtenemos solo la columna 'numero_semana'
            ->toArray(); // La convertimos a un array simple [5, 12, 23]

        // 2. Determinamos el número de semanas en el año (usualmente 52).
        $fecha = Carbon::create($anio, 1, 1);
        $numeroDeSemanas = $fecha->weeksInYear;

        // 3. Iteramos para construir el array de todas las semanas.
        for ($i = 1; $i <= $numeroDeSemanas; $i++) {

            $fecha->setISODate($anio, $i);

              // Usamos un array en lugar de un objeto stdClass. Es más limpio para Livewire.
              $this->semanas [$i] = [
                  'numeroSemana' => $i,
                  'fechaInicio' => $fecha->startOfWeek()->format('Y-m-d'),
                  'fechaFin' => $fecha->endOfWeek()->format('Y-m-d'),
                  'habilitada' => !in_array($i, $semanasDeshabilitadas),
              ];
        }

    }

     // Método para sumar un año
    public function sumarAño()
    {
        // Si el año está vacío, inicializa con el año actual. Si no, lo incrementa.
        $this->añoSeleccionado = (int)($this->añoSeleccionado ?? date('Y')) + 1;

        // Validamos y recargamos las semanas inmediatamente
        $this->updatedAñoSeleccionado();
    }

    // Método para restar un año
    public function restarAño()
    {
        // Si el año está vacío, inicializa con el año actual. Si no, lo decrementa.
        $this->añoSeleccionado = (int)($this->añoSeleccionado ?? date('Y')) - 1;

        // Validamos y recargamos las semanas inmediatamente
        $this->updatedAñoSeleccionado();
    }

    public function updatedAñoSeleccionado()
    {
        // Validamos que el año sea razonable para evitar errores.
        if (strlen($this->añoSeleccionado) === 4) {
            $this->cargarSemanas();
        } else {
            // Si el año no es válido, vaciamos el array para que la vista muestre el mensaje.
            $this->semanas = [];
        }
    }

    public function updatedSemanas($value, $key)
    {
        // $key nos llega como "indice.propiedad", por ej: "15.habilitada"
        // $value será `true` o `false` (el nuevo estado del switch)
        $parts = explode('.', $key);
        $index = $parts[0];
        $property = $parts[1];

        // Obtenemos el número de la semana a partir del índice del array.
        $numeroSemana = $this->semanas[$index]['numeroSemana'];
        $this->x = $numeroSemana;
        if ($property === 'habilitada') {
            if ($value) {
               SemanaDeshabilitada::where('anio', $this->añoSeleccionado)
                    ->where('numero_semana', $numeroSemana)
                    ->delete();
            } else {
              SemanaDeshabilitada::firstOrCreate([
                  'anio' => $this->añoSeleccionado,
                  'numero_semana' => $numeroSemana,
              ]);
            }
        }
    }

    public function render()
    {
        // Hacemos una copia del array original de semanas para no modificarlo.
        $semanasParaLaVista = $this->semanas;

        // Usamos la función array_filter de PHP para filtrar el array.
        // Es muy eficiente para este propósito.
        $semanasFiltradas = array_filter($semanasParaLaVista, function ($semana) {

            // La condición para mostrar una semana HABILITADA es:
            // que el filtro de habilitadas esté activo Y que la semana esté habilitada.
            $mostrarHabilitada = $this->filtroHabilitadas && $semana['habilitada'];

            // La condición para mostrar una semana DESHABILITADA es:
            // que el filtro de deshabilitadas esté activo Y que la semana NO esté habilitada.
            $mostrarDeshabilitada = $this->filtroDeshabilitadas && !$semana['habilitada'];

            // La semana se incluirá en el resultado final si CUALQUIERA de las dos condiciones anteriores es verdadera.
            return $mostrarHabilitada || $mostrarDeshabilitada;
        });

        // Pasamos el array ya filtrado a la vista.
        // Es importante pasar este nuevo array y no el original.
        return view('livewire.generales.configurar-semanas', [
            'semanasFiltradas' => $semanasFiltradas,
        ]);
    }



}
