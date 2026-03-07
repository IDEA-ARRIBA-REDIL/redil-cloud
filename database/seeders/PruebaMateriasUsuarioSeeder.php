<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MateriaAprobadaUsuario;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PruebaMateriasUsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
        $userId = 11;

        // Limpiamos registros previos de este usuario para evitar duplicadados al probar múltiples veces
        MateriaAprobadaUsuario::where('user_id', $userId)->delete();

        $datos = [
            // 1. Materia Aprobada con nota alta
            [
                'user_id' => $userId,
                'materia_id' => 1, // Asumiendo que materia 1 existe (ej: "Liderazgo I")
                'materia_periodo_id' => 1,
                'periodo_id' => 1,
                'aprobado' => true,
                'nota_final' => 4.8,
                'total_asistencias' => 10,
                'motivo_reprobacion' => null,
                'es_homologacion' => false,
                'observacion_homologacion' => null,
                'sede_id' => 1,
                'fecha_homologacion' => null,
                'created_at' => Carbon::now()->subMonths(6),
                'updated_at' => Carbon::now()->subMonths(6),
            ],
            // 2. Materia Aprobada con nota justa
            [
                'user_id' => $userId,
                'materia_id' => 2, // Asumiendo materia 2 (ej: "Doctrina Básica")
                'materia_periodo_id' => 2,
                'periodo_id' => 1,
                'aprobado' => true,
                'nota_final' => 3.5,
                'total_asistencias' => 8,
                'motivo_reprobacion' => null,
                'es_homologacion' => false,
                'observacion_homologacion' => null,
                'sede_id' => 1,
                'fecha_homologacion' => null,
                'created_at' => Carbon::now()->subMonths(5),
                'updated_at' => Carbon::now()->subMonths(5),
            ],
            // 3. Materia Reprobada (Nota baja)
            [
                'user_id' => $userId,
                'materia_id' => 3, // Asumiendo materia 3 (ej: "Historia de la Iglesia")
                'materia_periodo_id' => 3,
                'periodo_id' => 2,
                'aprobado' => false,
                'nota_final' => 2.1,
                'total_asistencias' => 4,
                'motivo_reprobacion' => 'Bajo rendimiento académico',
                'es_homologacion' => false,
                'observacion_homologacion' => null,
                'sede_id' => 1,
                'fecha_homologacion' => null,
                'created_at' => Carbon::now()->subMonths(4),
                'updated_at' => Carbon::now()->subMonths(4),
            ],
            // 4. Materia Reprobada (Fallas de asistencia)
            [
                'user_id' => $userId,
                'materia_id' => 4, // Asumiendo materia 4
                'materia_periodo_id' => 4,
                'periodo_id' => 2,
                'aprobado' => false,
                'nota_final' => 4.0, // Nota aprobatoria pero reprobó por fallas
                'total_asistencias' => 2,
                'motivo_reprobacion' => 'Inasistencia superior al límite permitido',
                'es_homologacion' => false,
                'observacion_homologacion' => null,
                'sede_id' => 1,
                'fecha_homologacion' => null,
                'created_at' => Carbon::now()->subMonths(3),
                'updated_at' => Carbon::now()->subMonths(3),
            ],
            // 5. Materia Homologada
            [
                'user_id' => $userId,
                'materia_id' => 5, // Asumiendo materia 5
                'materia_periodo_id' => null, // Puede ser null en homologaciones si no se cursó en un periodo interno
                'periodo_id' => null,
                'aprobado' => true,
                'nota_final' => 5.0,
                'total_asistencias' => 0,
                'motivo_reprobacion' => null,
                'es_homologacion' => true,
                'observacion_homologacion' => 'Homologación por estudios externos en Seminario Bíblico.',
                'sede_id' => 1,
                'fecha_homologacion' => Carbon::now()->subYear(),
                'homologado_por_user_id' => 1, // Asumiendo admin id 1
                'created_at' => Carbon::now()->subYear(),
                'updated_at' => Carbon::now()->subYear(),
            ],
             // 6. Otra Materia pendiente (sin aprobar y sin reprobar explicito reciente, simulando en curso o retirado)
             // Nota: En esta tabla tabla 'materias_aprobada_usuario' normalmente van los historicos finales.
             // Si quieres simular algo "pendiente" que aparece en el historial pero no está aprobado aún:
            [
                'user_id' => $userId,
                'materia_id' => 6,
                'materia_periodo_id' => 6,
                'periodo_id' => 3,
                'aprobado' => false,
                'nota_final' => 1.5,
                'total_asistencias' => 5,
                'motivo_reprobacion' => 'Retiro voluntario',
                'es_homologacion' => false,
                'observacion_homologacion' => null,
                'sede_id' => 1,
                'fecha_homologacion' => null,
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now()->subMonth(),
            ],
        ];

        foreach ($datos as $dato) {
            MateriaAprobadaUsuario::create($dato);
        }

        $this->command->info('Se han generado registros de prueba de materias para el usuario ' . $userId);
        */
    }
}
