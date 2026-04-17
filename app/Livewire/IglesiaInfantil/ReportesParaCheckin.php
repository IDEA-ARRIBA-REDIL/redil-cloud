<?php

namespace App\Livewire\IglesiaInfantil;

use App\Models\ReporteReunion;
use Carbon\Carbon;
use Livewire\Component;

class ReportesParaCheckin extends Component
{
    public string $busqueda = '';

    public bool $verInputBusqueda = true;

    public bool $verListaBusqueda = false;

    public ?ReporteReunion $reporteSeleccionado = null;

    /** 0. Inicializar con el ID de reporte si viene en la petici&oacute;n */
    public function mount(?int $reporteReunionId = null): void
    {
        if ($reporteReunionId) {
            $this->reporteSeleccionado = ReporteReunion::with('reunion')->find($reporteReunionId);
            if ($this->reporteSeleccionado) {
                $this->verInputBusqueda = false;
            }
        }
    }

    /** 1. Desplegar la lista al pasar el mouse */
    public function desplegarListaBusqueda(): void
    {
        if (! $this->reporteSeleccionado) {
            $this->verListaBusqueda = true;
        }
    }

    /** 2. Ocultar la lista al sacar el mouse */
    public function ocultarListaBusqueda(): void
    {
        if (! $this->reporteSeleccionado) {
            $this->verListaBusqueda = false;
        }
    }

    /** 3. Quitar la selección actual */
    public function quitarSeleccion(): void
    {
        $this->reporteSeleccionado = null;
        $this->verInputBusqueda = true;
        $this->verListaBusqueda = true;
        $this->busqueda = '';
        $this->dispatch('reporteIglesiaInfantilAnulado');
    }

    /** 4. Seleccionar un reporte y disparar el evento hacia Alpine.js */
    public function seleccionarReporte(int $reporteId): void
    {
        $this->reporteSeleccionado = ReporteReunion::with('reunion')->find($reporteId);
        $this->verInputBusqueda = false;
        $this->verListaBusqueda = false;

        if ($this->reporteSeleccionado) {
            $this->dispatch('reporteIglesiaInfantilSeleccionado', [
                'reporteId' => $this->reporteSeleccionado->id,
                'nombre' => $this->etiquetaReporte($this->reporteSeleccionado),
            ]);
        }
    }

    /** 5. Render: carga reportes recientes/futuros con iglesia infantil habilitada */
    public function render()
    {
        $query = ReporteReunion::with('reunion')
            ->where('habilitar_preregistro_iglesia_infantil', true)
            ->orderBy('fecha', 'desc')
            ->orderByRaw('(SELECT hora FROM reuniones WHERE reuniones.id = reporte_reuniones.reunion_id LIMIT 1) DESC');

        // Filtro de búsqueda por nombre de la reunión o fecha
        if ($this->busqueda && strlen($this->busqueda) >= 2) {
            $buscar = '%'.mb_strtolower($this->busqueda).'%';
            $query->whereHas('reunion', function ($q) use ($buscar) {
                $q->whereRaw('LOWER(nombre) LIKE ?', [$buscar]);
            })->orWhereRaw('CAST(fecha AS TEXT) LIKE ?', [$buscar]);
        }

        $reportes = $query->take(15)->get();

        return view('livewire.iglesia-infantil.reportes-para-checkin', [
            'reportes' => $reportes,
        ]);
    }

    /** Genera la etiqueta del reporte: NombreReunion — Fecha Hora */
    private function etiquetaReporte(ReporteReunion $reporte): string
    {
        $nombre = $reporte->reunion?->nombre ?? 'Reunión';
        $fecha = Carbon::parse($reporte->fecha)->translatedFormat('D d/m/Y');
        $hora = $reporte->reunion?->hora
            ? Carbon::parse($reporte->reunion->hora)->format('g:i a')
            : '';

        return "{$nombre} — {$fecha} {$hora}";
    }
}
