<?php

namespace App\Exports;

use App\Models\Caja;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class CajasExport implements FromQuery, WithHeadings, WithMapping
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
        $query = Caja::query()->with(['puntoDePago.sede', 'usuario']);

        // 1. Filtro por Tipo (Activos / Dados de Baja)
        if ($this->tipo === 'dados-de-baja') {
            $query->onlyTrashed();
        }

        // 2. Filtro de Búsqueda General
        if (!empty($this->filtros['buscar'])) {
            $termino = strtolower($this->filtros['buscar']);
            $query->where(function ($q) use ($termino) {
                $q->whereRaw('LOWER(nombre) LIKE ?', ["%{$termino}%"])
                  ->orWhereHas('puntoDePago', function ($qPdp) use ($termino) {
                      $qPdp->whereRaw('LOWER(nombre) LIKE ?', ["%{$termino}%"]);
                  })
                  ->orWhereHas('usuario', function ($qUser) use ($termino) { // Cajero
                      $qUser->whereRaw('LOWER(primer_nombre) LIKE ?', ["%{$termino}%"])
                            ->orWhereRaw('LOWER(primer_apellido) LIKE ?', ["%{$termino}%"]);
                  });
            });
        }

        // 3. Filtro por Sede (Array)
        if (!empty($this->filtros['filtroSede'])) {
            $query->whereHas('puntoDePago', function ($q) {
                $q->whereIn('sede_id', $this->filtros['filtroSede']);
            });
        }

        // 4. Filtro por Punto de Pago (Array)
        if (!empty($this->filtros['filtroPuntoDePago'])) {
            $query->whereIn('punto_de_pago_id', $this->filtros['filtroPuntoDePago']);
        }

        // 5. Verificación de Permisos (Rol Activo)
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if (!$rolActivo || !$rolActivo->hasPermissionTo('pdp.opcion_listar_todos_las_cajas')) {
            // Si no tiene permiso, solo ve las cajas de los PDP que encarga
            $query->whereHas('puntoDePago', function ($q) {
                $q->where('encargado_id', auth()->id());
            });
        }

        return $query->orderBy('nombre', 'asc');
    }

    public function headings(): array
    {
        return [
            'Nombre Caja',
            'Punto de Pago',
            'Sede',
            'Cajero Asignado',
            'Apertura',
            'Cierre',
            'Estado',
            'Fecha Creación',
        ];
    }

    public function map($caja): array
    {
        $nombreCajero = 'No asignado';
        if ($caja->usuario) {
            $nombreCajero = $caja->usuario->primer_nombre . ' ' . $caja->usuario->primer_apellido;
        }

        $nombrePunto = $caja->puntoDePago ? $caja->puntoDePago->nombre : 'N/A';
        $nombreSede = ($caja->puntoDePago && $caja->puntoDePago->sede) ? $caja->puntoDePago->sede->nombre : 'N/A';

        return [
            $caja->nombre,
            $nombrePunto,
            $nombreSede,
            $nombreCajero,
            $caja->hora_apertura ?? '',
            $caja->hora_cierre ?? '',
            $caja->estado ? 'Activa' : 'Inactiva',
            $caja->created_at ? $caja->created_at->format('d/m/Y H:i') : '',
        ];
    }
}
