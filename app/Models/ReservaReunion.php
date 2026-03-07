<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Barryvdh\DomPDF\Facade\Pdf;

class ReservaReunion extends Model
{
    use HasFactory;
    protected $table = 'reservas_reuniones';
    protected $guarded = [];


    public function usuario(): BelongsTo
    {
      return $this->belongsTo(User::class, 'user_id');
    }

    public function reporte(): BelongsTo
    {
      return $this->belongsTo(ReporteReunion::class, 'reporte_reunion_id');
    }

    public function responsable(): BelongsTo
    {
      return $this->belongsTo(User::class, 'responsable_id');
    }

    public function generarPdf(): string
    {
        // Busca la configuración general
        $configuracion = Configuracion::find(1);

        // Accede a las relaciones y propiedades del modelo usando $this
        $reporte = $this->reporte;
        $reunion = $reporte->reunion()->withTrashed()->first();

        // Determina el nombre del asistente
        $nombreAsistente = $this->invitado ? $this->nombre_invitado : $this->usuario->nombre(3);

        // Prepara los datos para el código QR
        $dataQr = json_encode([
            'id' => $this->id,
            'nombre' => $nombreAsistente,
            'tipo' => 'reserva'
        ]);

        // Carga la vista para el PDF con todos los datos necesarios
        $pdf = PDF::loadView('contenido.paginas.reporte-reuniones.codigoQr', [
            'title' => 'Reserva QR - ' . $this->id,
            'configuracion' => $configuracion,
            'dataQr' => $dataQr,
            'reunion' => $reunion,
            'reporte' => $reporte,
            'nombreAsistente' => $nombreAsistente,
            'reserva' => $this, // Pasas la instancia actual a la vista
        ]);

        // Devuelve el contenido del PDF como un string
        return $pdf->output();
    }



}
