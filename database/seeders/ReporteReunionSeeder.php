<?php

namespace Database\Seeders;

use App\Models\ReporteReunion;
use App\Models\Reunion; // Asegúrate de importar Reunion si vas a interactuar mucho con él
use App\Models\Sede;   // Y Sede si necesitas verificar existencias, etc.
use Illuminate\Database\Seeder;

class ReporteReunionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {

    $reporte = ReporteReunion::firstOrCreate([
      'reunion_id' => 1, // Este ID debe corresponder a una reunión existente en tu tabla 'reuniones'
      'fecha' => '2025-09-01',
      "hora" => "18:00:00",
      'predicador' => rand(1, 10), // Asegúrate que existan usuarios con estos IDs
      'observaciones' => '',
      'invitados' => 0,
      //'sumatoria_adicional_clasificacion' => 'Clasificación ' . rand(1, 3),
      //'clasificacion_asistentes' => 'Tipo ' . rand(1, 3),
      'cantidad_asistencias' => 0,
      'total_ofrendas' => 0,
      'autor_creacion' => 1, // Asegúrate que existan usuarios con estos IDs
      'conteo_preliminar' => 0,
      'habilitar_reserva' => TRUE,
      'dias_plazo_reserva' => 10,
      'aforo' => 10,
      'aforo_ocupado' => 0,
      'habilitar_reserva_invitados' => TRUE,
      'habilitar_reserva_familiares' => TRUE,
      'cantidad_maxima_reserva_invitados' => 3,
      'solo_reservados_pueden_asistir' => TRUE,
      'url' => 'https://example.com/reunion-' . '2025-04-02',
      'iframe' => '<iframe src="https://example.com/embed/' . '2025-04-02' . '"></iframe>',
      'visualizaciones' => 0,
      'habilitar_preregistro_iglesia_infantil' => FALSE,
    ]);

    // Primero accedes al modelo Reunion relacionado ($reporte->reunion)
    // y luego a su relación sedes() para hacer el attach.
    if ($reporte->reunion) { // Verifica que la reunión exista
      $reporte->reunion->sedes()->attach([1, 2]); // syncWithoutDetaching es más seguro si el seeder se corre múltiples veces
      $reporte->reunion->rangosEdades()->attach([1, 2, 3, 4, 5, 6]);
    }

    // $reporte->ofrendas()->attach([2, 3, 4, 5]); // Asegúrate que estas ofrendas existan
    $reporte->clasificacionesAsistentes()->attach(6, ['cantidad' => 0]); // Asegúrate que esta clasificación exista
    $reporte->clasificacionesAsistentes()->attach(7, ['cantidad' => 0]);


  }
}
