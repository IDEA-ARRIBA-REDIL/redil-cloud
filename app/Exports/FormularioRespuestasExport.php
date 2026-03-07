<?php

namespace App\Exports;

use App\Models\Actividad;
use App\Models\Inscripcion;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

// =================================================================
// --- CLASE PRINCIPAL (ORQUESTADOR) ---
// Su única función es definir qué hojas tendrá el Excel.
// =================================================================
class FormularioRespuestasExport implements WithMultipleSheets
{
    use Exportable;
    protected $actividad;

    public function __construct(Actividad $actividad)
    {
        $this->actividad = $actividad;
    }

    /**
     * Define las hojas que se incluirán en el archivo de Excel.
     *
     * @return array
     */
    public function sheets(): array
    {
        return [
            new RespuestasSheet($this->actividad), // Primera hoja
            new InvitadosSheet($this->actividad),  // Segunda hoja
        ];
    }
}


// =================================================================
// --- CLASE PARA LA HOJA 1 (RESPUESTAS) ---
// Contiene la lógica original de tu exportador.
// =================================================================
class RespuestasSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $actividad;
    protected $preguntas;
    protected $fechasActividad;

    public function __construct(Actividad $actividad)
    {
        $this->actividad = $actividad;
        $this->preguntas = $actividad->elementos()
            ->whereNotIn('tipo_elemento_id', [1, 10, 11])->orderBy('orden', 'asc')->get();

        $this->fechasActividad = [];
        if ($actividad->fecha_inicio && $actividad->fecha_finalizacion) {
            $periodo = CarbonPeriod::create($actividad->fecha_inicio, $actividad->fecha_finalizacion);
            foreach ($periodo as $date) {
                $this->fechasActividad[] = $date->format('Y-m-d');
            }
        }
    }

    public function title(): string
    {
        return 'Respuestas Principales';
    }

    public function collection()
    {
        // Se obtienen TODAS las inscripciones (principales e invitados)
        return Inscripcion::whereHas('categoriaActividad', fn($q) => $q->where('actividad_id', $this->actividad->id))
            ->with(['user', 'respuestas.elemento.tipoElemento', 'asistencias'])
            ->get();
    }

    public function headings(): array
    {
        $encabezados = ['Participante', 'Email', 'Teléfono', 'Identificación'];
        foreach ($this->preguntas as $pregunta) {
            $encabezados[] = $pregunta->titulo;
        }
        $encabezados[] = 'Estado Inscripción';
        foreach ($this->fechasActividad as $fecha) {
            $encabezados[] = 'Asistencia ' . Carbon::parse($fecha)->isoFormat('DD-MMM');
        }
        return $encabezados;
    }

    public function map($inscripcion): array
    {
        $nombre = $inscripcion->user?->nombre(3) ?? $inscripcion->nombre_inscrito ?? 'Invitado';
        $email = $inscripcion->user?->email ?? $inscripcion->email ?? 'N/A';
        $telefono = 'N/A';
        $identificacion =  'N/A';
        
        if(!$inscripcion->inscripcion_asociada){
          $telefono = $inscripcion->compra->telefono_comprador ?? 'N/A';
          $identificacion = $inscripcion->compra->identificacion_comprador ?? 'N/A';
        }

        $respuestasMapeadas = [];
        foreach ($inscripcion->respuestas as $respuesta) {
            $respuestasMapeadas[$respuesta->elemento_formulario_actividad_id] = $this->formatarValorRespuesta($respuesta);
        }

        $fila = [$nombre, $email, $telefono, $identificacion];
        foreach ($this->preguntas as $pregunta) {
            $fila[] = $respuestasMapeadas[$pregunta->id] ?? '';
        }

        $fila[] = match ($inscripcion->estado) {
            1 => 'Iniciada',
            2 => 'Pendiente',
            3 => 'Finalizada',
            default => 'Desconocido'
        };

        $asistenciasDelParticipante = $inscripcion->asistencias->pluck('fecha')->map(fn($f) => Carbon::parse($f)->format('Y-m-d'))->flip();
        foreach ($this->fechasActividad as $fecha) {
            $fila[] = isset($asistenciasDelParticipante[$fecha]) ? 'Sí' : 'No';
        }
        return $fila;
    }

    private function formatarValorRespuesta($respuesta): string
    {
        if (!$respuesta) {
            return '';
        }

        // 1. Respuesta Única (Select)
        if (!is_null($respuesta->respuesta_unica)) {
            $opcion = \App\Models\OpcionesElementoFormularioActividad::find($respuesta->respuesta_unica);
            return $opcion ? $opcion->valor_texto : '';
        }

        // 2. Respuesta Múltiple (Checkbox/Multi-Select)
        if (!is_null($respuesta->respuesta_multiple)) {
            // Intentar decodificar si es JSON, sino asumir que es separado por comas
            $ids = json_decode($respuesta->respuesta_multiple);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $ids = explode(',', $respuesta->respuesta_multiple);
            }

            if (is_array($ids) && count($ids) > 0) {
                $opciones = \App\Models\OpcionesElementoFormularioActividad::whereIn('id', $ids)->pluck('valor_texto')->toArray();
                return implode(', ', $opciones);
            }
            return '';
        }

        // 3. Respuesta Si/No
        if (!is_null($respuesta->respuesta_si_no)) {
            return $respuesta->respuesta_si_no == 1 ? 'Sí' : 'No';
        }

        // 4. Texto Corto
        if (!is_null($respuesta->respuesta_texto_corto)) {
            return $respuesta->respuesta_texto_corto;
        }

        // 5. Texto Largo
        if (!is_null($respuesta->respuesta_texto_largo)) {
            return $respuesta->respuesta_texto_largo;
        }

        // 6. Moneda
        if (!is_null($respuesta->respuesta_moneda)) {
            return '$ ' . number_format($respuesta->respuesta_moneda, 2);
        }

        // 7. Número
        if (!is_null($respuesta->respuesta_numero)) {
            return (string) $respuesta->respuesta_numero;
        }

        // 8. Fecha
        if (!is_null($respuesta->respuesta_fecha)) {
            return Carbon::parse($respuesta->respuesta_fecha)->format('Y-m-d');
        }

        // 9. Archivos / Fotos
        if (!is_null($respuesta->url_foto)) {
            return asset($respuesta->url_foto);
        }
        if (!is_null($respuesta->url_archivo)) {
            return asset($respuesta->url_archivo);
        }

        return '';
    }
}


// =================================================================
// --- CLASE PARA LA HOJA 2 (INVITADOS) ---
// Contiene la nueva lógica para listar los invitados.
// =================================================================
class InvitadosSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $actividad;

    public function __construct(Actividad $actividad)
    {
        $this->actividad = $actividad;
    }

    public function title(): string
    {
        return 'Invitados por Participante';
    }

    public function collection()
    {
        return Inscripcion::whereHas('categoriaActividad', fn($q) => $q->where('actividad_id', $this->actividad->id))
            ->whereNotNull('user_id')
            ->whereHas('invitados')
            ->with('invitados', 'user')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Participante principal',
            'Email principal',
            'Nombre del Invitado',
            'Email del Invitado',
        ];
    }

    public function map($inscripcionPrincipal): array
    {
        $filas = [];
        foreach ($inscripcionPrincipal->invitados as $invitado) {
            $filas[] = [
                $inscripcionPrincipal->user->nombre(3),
                $inscripcionPrincipal->user->email,
                $invitado->nombre_inscrito,
                $invitado->email,
            ];
        }
        return $filas;
    }
}
