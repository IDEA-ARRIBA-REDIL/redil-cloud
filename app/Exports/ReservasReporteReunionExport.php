<?php

namespace App\Exports;

use App\Models\ReservaReunion;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReservasReporteReunionExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $reporteReunionId;

    public function __construct(int $reporteReunionId)
    {
        $this->reporteReunionId = $reporteReunionId;
    }

    /**
    * Aquí definimos la consulta a la base de datos.
    * Usamos eager loading con with('user') para optimizar y no hacer
    * una consulta por cada fila.
    */
    public function query()
    {
      return ReservaReunion::query()
          ->with('usuario') // Carga la relación con el usuario
          ->where('reporte_reunion_id', $this->reporteReunionId);
    }

     /**
    * Define los encabezados de las columnas en el archivo Excel.
    */
    public function headings(): array
    {
        return [
            'Nombre Completo',
            'Email',
            'Tipo de Asistente',
            'Tipo de identificación',
            'N° identificación',
            'Registrado por',
            '¿Marco asistencia?',
        ];
    }


    /**
    * Este es el método más importante.
    * Transforma cada fila de la consulta en el formato que queremos para el Excel.
    * Aquí manejamos la lógica de si es un usuario de la plataforma o un invitado.
    *
    * @param ReservaReunion $reserva
    */
    public function map($reserva): array
    {
        // Si la reserva tiene un user_id, obtenemos los datos del usuario relacionado.
        if ($reserva->usuario) {
            $nombre = $reserva->usuario->nombre(4);
            $email = $reserva->usuario->email;
            $tipo = $reserva->usuario->tipoUsuario ? $reserva->usuario->tipoUsuario->nombre : 'No indicado';
            $tipoId = $reserva->tipoIdentificacion ? $reserva->tipoIdentificacion->nombre : 'No indicado';
            $numeroId = $reserva->usuario->identificacion ? $reserva->usuario->identificacion :'No indicado';
        }
        // Si no, es un invitado y usamos los datos de la misma tabla de reservas.
        else {
            $nombre = $reserva->nombre_invitado;
            $email = $reserva->email_invitado;
            $tipo = 'Invitado';
            $tipoId = 'No indicado';
            $numeroId = 'No indicado';
        }

        // Opcional: Para saber quién registró al invitado
        $responsable = $reserva->responsable_id ? $reserva->responsable->nombre(4) : 'N/A';
        $asistio = $reserva->registrada ? 'Si' : 'No';
        return [
            $nombre,
            $email,
            $tipo,
            $tipoId,
            $numeroId,
            $responsable,
            $asistio
        ];
    }
}
