<?php

namespace App\Exports;

use App\Models\Aula;
use App\Models\Sede;      // Importar Sede
use App\Models\TipoAula; // Importar TipoAula
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Http\Request; // Importar Request para recibir filtros

class AulasExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    // Propiedades para almacenar los filtros recibidos
    protected $filtroNombre;
    protected $filtroSedeId;
    protected $filtroTipoAulaId;

    /**
     * El constructor recibe los parámetros de filtro del request.
     */
    public function __construct(Request $request)
    {
        $this->filtroNombre = $request->input('filtro_nombre_aula');
        $this->filtroSedeId = $request->input('filtro_sede');
        $this->filtroTipoAulaId = $request->input('filtro_tipo_aula');
    }

    /**
     * Define la consulta base para obtener las aulas, aplicando los filtros.
     * Carga las relaciones necesarias.
     */
    public function query()
    {
        $queryAulas = Aula::query()->with(['sede', 'tipo']); // Precarga relaciones

        // Aplicar filtro por Nombre
        if ($this->filtroNombre) {
            $queryAulas->where('nombre', 'ilike', '%' . $this->filtroNombre . '%');
        }

        // Aplicar filtro por Sede
        if ($this->filtroSedeId) {
            $queryAulas->where('sede_id', $this->filtroSedeId);
        }

        // Aplicar filtro por Tipo de Aula
        if ($this->filtroTipoAulaId) {
            $queryAulas->where('tipo_aula_id', $this->filtroTipoAulaId);
        }

        return $queryAulas->orderBy('nombre', 'asc'); // Ordenar
    }

    /**
     * Define los encabezados de las columnas en el archivo Excel.
     */
    public function headings(): array
    {
        return [
            'Nombre',
            'Sede',
            'Tipo de Aula',
            'Descripción',
            'Estado', // Columna para Activo/Inactivo
        ];
    }

    /**
     * Mapea cada objeto Aula a una fila de datos para el Excel.
     *
     * @param Aula $aula
     */
    public function map($aula): array
    {
        return [
            $aula->nombre,
            $aula->sede->nombre ?? 'N/A',      // Accede al nombre de la sede relacionada
            $aula->tipo->nombre ?? 'N/A',      // Accede al nombre del tipo relacionado
            $aula->descripcion ?? '',         // Descripción (puede ser null)
            $aula->activo ? 'Activo' : 'Inactivo', // Muestra texto legible para el estado
        ];
    }
}
