<?php

namespace App\Exports;

use App\Models\PuntoDePago;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Http\Request;

class PuntosDePagoExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $filtros;
    protected $tipo;

    public function __construct($filtros = [], $tipo = 'todos')
    {
        $this->filtros = $filtros;
        $this->tipo = $tipo;
    }

    public function query()
    {
        $query = PuntoDePago::query()->with(['sede', 'encargado']);

        // 1. Filtro por Tipo (Activos / Dados de Baja)
        if ($this->tipo === 'dados-de-baja') {
            $query->onlyTrashed();
        }

        // 2. Filtro de Búsqueda General
        if (!empty($this->filtros['buscar'])) {
            $termino = strtolower($this->filtros['buscar']);
            $query->where(function ($q) use ($termino) {
                $q->whereRaw('LOWER(nombre) LIKE ?', ["%{$termino}%"])
                  ->orWhereHas('sede', function ($qSede) use ($termino) {
                      $qSede->whereRaw('LOWER(nombre) LIKE ?', ["%{$termino}%"]);
                  })
                  ->orWhereHas('encargado', function ($qEnc) use ($termino) {
                      $qEnc->whereRaw('LOWER(primer_nombre) LIKE ?', ["%{$termino}%"])
                           ->orWhereRaw('LOWER(primer_apellido) LIKE ?', ["%{$termino}%"]);
                  });
            });
        }

        // 3. Filtro por Sede (Array)
        if (!empty($this->filtros['filtroSede'])) {
            $query->whereIn('sede_id', $this->filtros['filtroSede']);
        }

        // 4. Verificación de Permisos (Rol Activo)
        // Importante: Replicar la lógica de permisos del componente Livewire
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if (!$rolActivo || !$rolActivo->hasPermissionTo('pdp.opcion_listar_todos_los_pdp')) {
            $query->where('encargado_id', auth()->id());
        }

        return $query->orderBy('nombre', 'asc');
    }

    public function headings(): array
    {
        return [
            'Nombre Punto de Pago',
            'Sede',
            'Encargado',
            'Estado',
            'Fecha Creación',
        ];
    }

    public function map($pdp): array
    {
        $nombreEncargado = 'No asignado';
        if ($pdp->encargado) {
            $nombreEncargado = $pdp->encargado->primer_nombre . ' ' . $pdp->encargado->primer_apellido;
        }

        return [
            $pdp->nombre,
            $pdp->sede->nombre ?? 'N/A',
            $nombreEncargado,
            $pdp->estado ? 'Activo' : 'Inactivo',
            $pdp->created_at ? $pdp->created_at->format('d/m/Y H:i') : '',
        ];
    }
}
