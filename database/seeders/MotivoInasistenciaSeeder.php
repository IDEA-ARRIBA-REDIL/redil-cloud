<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MotivoInasistencia; // Asegúrate que el namespace de tu modelo sea correcto
use Illuminate\Support\Facades\DB; // Alternativa si no usas el modelo directamente

class MotivoInasistenciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $motivos = [
            [
                'nombre' => 'Enfermedad',
                'descripcion' => 'Enfermedad.',
                'activo' => true,
            ],
            [
                'nombre' => 'Cita Médica',
                'descripcion' => ' Cita médica programada.',
                'activo' => true,
            ],
            [
                'nombre' => 'Asuntos Familiares',
                'descripcion' => 'Inasistencia debido a asuntos familiares urgentes o importantes.',
                'activo' => true,
            ],
            [
                'nombre' => 'Problemas de Transporte',
                'descripcion' => 'Dificultades con el transporte para llegar a la institución.',
                'activo' => true,
            ],
            [
                'nombre' => 'Viaje Familiar',
                'descripcion' => 'Ausencia programada por viaje familiar.',
                'activo' => true,
            ],
            [
                'nombre' => 'Condiciones Climáticas Adversas',
                'descripcion' => 'Inasistencia debido a mal tiempo que impide el desplazamiento seguro.',
                'activo' => true,
            ],
            [
                'nombre' => 'Participación en Evento Externo',
                'descripcion' => 'Representación de la institución o participación en eventos académicos/deportivos externos.',
                'activo' => true,
            ],
            [
                'nombre' => 'Diligencia Personal Importante',
                'descripcion' => 'Atención de trámites o asuntos personales inaplazables.',
                'activo' => true,
            ],
            [
                'nombre' => 'Malestar General (Sin especificar enfermedad)',
                'descripcion' => 'El alumno reportó sentirse mal, sin diagnóstico específico.',
                'activo' => true,
            ],
            [
                'nombre' => 'Otro (Con Justificación)',
                'descripcion' => 'Motivo no listado pero debidamente justificado por el acudiente o alumno.',
                'activo' => true,
            ],
            [
                'nombre' => 'Inasistencia No Justificada',
                'descripcion' => 'El alumno no asistió y no presentó justificación válida.',
                'activo' => false, // Podrías tener este como inactivo si solo quieres motivos "válidos"
            ]
        ];

        // Opción 1: Usando el Modelo (Recomendado si tienes casts, eventos, etc.)
        foreach ($motivos as $motivo) {
            MotivoInasistencia::firstOrCreate(['nombre' => $motivo['nombre']], $motivo);
        }

        // Opción 2: Usando DB Facade (Más directo para inserción masiva simple)
        // DB::table('motivos_inasistencias_reporte_escuelas')->insert($motivos);
        // Si usas esta opción, asegúrate de añadir created_at y updated_at manualmente si no son nulos por defecto
        // o si no tienes timestamps() en el modelo que se encargue de ello.
        // Para Laravel 11, con `timestamps()` en la migración, el modelo se encargará de esto incluso con DB::table si no se especifican.
        // No obstante, MotivoInasistencia::create() es más idiomático de Eloquent.
    }
}
