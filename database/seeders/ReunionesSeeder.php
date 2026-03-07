<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Reunion;
use Carbon\Carbon;

class ReunionesSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $reunion_uno = Reunion::firstOrCreate([
      "hora" => "12:30:00",
      //"dia" => 2,
      "nombre" => "TRIMESTRE I",
      "descripcion" => "Evento inicial del año",
      "sede_id" => 1,
      "sedes_asistencia" => null,
      "genero" => json_encode([0, 1]),
      "habilitar_reserva" => true,
      "dias_plazo_reporte" => 30,
      "dias_plazo_reserva" => 12,
      "aforo" => 100,
      "habilitar_reserva_invitados" => true,
      "cantidad_maxima_reserva_invitados" => 5,
      "solo_reservados_pueden_asistir" => true,
      "hora_maxima_reportar_asistencia" => "11:00:00",
      "habilitar_preregistro_iglesia_infantil" => true,
      "created_at" => Carbon::now(),
      "updated_at" => Carbon::now(),
    ]);

    $reunion_dos = Reunion::firstOrCreate(
      ["nombre" => "TRIMESTRE II"],
      [
        "hora" => "15:00:00",
        //"dia" => 3,
        "descripcion" => "Segundo evento importante",
        "sede_id" => 2,
        "sedes_asistencia" => json_encode([1, 2]),
        "genero" => json_encode([0, 1]),
        "habilitar_reserva" => true,
        "dias_plazo_reporte" => 30,
        "dias_plazo_reserva" => 9,
        "aforo" => 150,
        "habilitar_reserva_invitados" => true,
        "cantidad_maxima_reserva_invitados" => 120,
        "solo_reservados_pueden_asistir" => true,
        "hora_maxima_reportar_asistencia" => "14:30:00",
        "habilitar_preregistro_iglesia_infantil" => true,
        "created_at" => Carbon::now(),
        "updated_at" => Carbon::now(),
      ]
    );
    if($reunion_dos->wasRecentlyCreated) {
        $reunion_dos->clasificacionesAsistentes()->attach([8, 9, 10, 11, 12]);
        $reunion_dos->sedes()->attach([1, 2]);
        $reunion_dos->tiposOfrendas()->attach([1, 2, 3]);
        $reunion_dos->rangosEdades()->attach([1, 2, 3, 4]);
        $reunion_dos->tipoUsuarios()->attach([1, 2, 3, 4, 5, 6]);
    }
    $reunion_tres = Reunion::firstOrCreate(
      ["nombre" => "TRIMESTRE III"],
      [
        "hora" => "15:00:00",
        //"dia" => 3,
        "descripcion" => "Segundo evento importante",
        "sede_id" => 2,
        "sedes_asistencia" => json_encode([1, 2]),
        "genero" => json_encode([0, 1]),
        "habilitar_reserva" => false,
        "dias_plazo_reporte" => 30,
        "dias_plazo_reserva" => 14,
        "aforo" => 150,
        "habilitar_reserva_invitados" => false,
        "cantidad_maxima_reserva_invitados" => null,
        "solo_reservados_pueden_asistir" => true,
        "hora_maxima_reportar_asistencia" => "14:30:00",
        "habilitar_preregistro_iglesia_infantil" => false,
        "created_at" => Carbon::now(),
        "updated_at" => Carbon::now(),
      ]
    );
    if($reunion_tres->wasRecentlyCreated) {
        $reunion_tres->clasificacionesAsistentes()->attach([8, 9, 10, 11, 12]);
        $reunion_tres->sedes()->attach([1, 2]);
        $reunion_tres->tiposOfrendas()->attach([1, 2, 3]);
        $reunion_tres->rangosEdades()->attach([1, 2, 3, 4]);
        $reunion_tres->tipoUsuarios()->attach([1, 2, 3, 4, 5, 6]);
    }

    $reunion_uno->clasificacionesAsistentes()->attach([8, 9, 10, 11, 12]);
    $reunion_uno->sedes()->attach([1, 2]);
    $reunion_uno->tiposOfrendas()->attach([1, 2, 3, 4, 5, 6]);
    $reunion_uno->rangosEdades()->attach([1, 2, 3, 4]);
    $reunion_uno->tipoUsuarios()->attach([1, 2, 3, 4, 5, 6]);


  }
}
