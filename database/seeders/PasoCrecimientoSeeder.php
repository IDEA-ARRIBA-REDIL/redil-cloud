<?php

namespace Database\Seeders;

use App\Models\PasoCrecimiento;
use Illuminate\Database\Seeder;

class PasoCrecimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $secciones = [
            // BLOQUE A (PRIMEROS PASOS) -> ID 1
            1 => [
                ['id' => 1,    'nombre' => 'Ingreso a la congregación'],
                ['id' => null, 'nombre' => 'Realice mi confesión de fe'],
                ['id' => 27, 'nombre' => 'Pertenezco a un grupo de crecimiento'],
            ],
            // BLOQUE B (MI CAMINO HACIA LA LIBERTAD) -> ID 2
            2 => [
                ['id' => 2,    'nombre' => 'Inicie mi proceso de mentoreo CHL'],
                ['id' => 6,    'nombre' => 'Entrevista para bautismo'],
                ['id' => 5,    'nombre' => 'Bautismo', 'habilitada_consolidacion' => true],
                ['id' => null, 'nombre' => 'Soy miembro'],
            ],
            // BLOQUE C (ENCUENTROS) -> ID 3
            3 => [
                ['id' => 4,    'nombre' => 'Encuentro de identidad'],
                ['id' => 28,   'nombre' => 'Encuentro de parejas union libre'],
            ],
            // BLOQUE D (ESCUELAS EL CAMINO WARRIORS) -> ID 4
            4 => [
                ['id' => 29,   'nombre' => 'Corazones emergentes'],
                ['id' => 30,   'nombre' => 'Corazones activos 1'],
                ['id' => 31,   'nombre' => 'Corazones activos 2'],
            ],
            // BLOQUE E (ESCUELAS EL CAMINO) -> ID 5
            5 => [
                ['id' => 8,    'nombre' => 'Mentor espiritual'],
                ['id' => 9,    'nombre' => 'Carácter y servicio'],
                ['id' => 14,   'nombre' => 'Familia'],
                ['id' => 15,   'nombre' => 'Espíritu santo'],
                ['id' => 135,  'nombre' => 'Graduado formación integra'],
            ],
            // BLOQUE F (ESCUELAS ESPECIALIZACION COSMOVISION BIBLICA) -> ID 6
            6 => [
                ['id' => 11,   'nombre' => 'Especialización cosmovisión bíblica - "Entre lo sagrado y secular"'],
                ['id' => 12,   'nombre' => 'Especialización cosmovisión bíblica - "Plan singular"'],
                ['id' => 13,   'nombre' => 'Especialización cosmovisión bíblica - "Cosmovisión del reino"'],
                ['id' => 34,   'nombre' => 'Especialización cosmovisión bíblica - "Reino inconmovible"'],
            ],
            // BLOQUE G (ESCUELAS ESPECIALIZACION MAESTROS) -> ID 7
            7 => [
                ['id' => 35,   'nombre' => 'Escuela de maestros - "Fundamentos en educación"'],
                ['id' => 36,   'nombre' => 'Escuela de maestros - "Comunicación y oratoria"'],
                ['id' => 134,  'nombre' => 'Práctica ministerial - Escuela de maestros'],
                ['id' => 68,   'nombre' => 'Escuela de maestros 3 "Neurodidáctica"'],
            ],
            // BLOQUE H (ESCUELAS ESPECIALIZACIÓN INTERSECIÓN) -> ID 8
            8 => [
                ['id' => 101,  'nombre' => 'Escuela intercesión'],
            ]
        ];

        // 1. FASE UNO: Insertar SOLO los que tienen ID forzado (Históricos de Visión)
        foreach ($secciones as $seccionId => $pasos) {
            foreach ($pasos as $index => $pasoData) {
                if (!is_null($pasoData['id'])) {
                    PasoCrecimiento::firstOrCreate(
                        ['id' => $pasoData['id']],
                        [
                            'nombre'                      => $pasoData['nombre'],
                            'orden'                       => $index + 1,
                            'seccion_paso_crecimiento_id' => $seccionId,
                            'habilitada_consolidacion'    => $pasoData['habilitada_consolidacion'] ?? false,
                        ]
                    );
                }
            }
        }

        // 2. FASE DOS: Sincronizar la secuencia de PostgreSQL
        // Obtenemos el ID más alto que acabamos de insertar (ej. 135)
        $maxId = PasoCrecimiento::max('id') ?? 1;
        $modelo = new PasoCrecimiento();
        $tabla = $modelo->getTable();
        
        // Ejecutamos la consulta en la misma conexión del modelo (tenant)
        $modelo->getConnection()->statement(
            "SELECT setval(pg_get_serial_sequence('{$tabla}', 'id'), {$maxId})"
        );

        // 3. FASE TRES: Insertar los pasos nuevos (ID null)
        foreach ($secciones as $seccionId => $pasos) {
            foreach ($pasos as $index => $pasoData) {
                if (is_null($pasoData['id'])) {
                    PasoCrecimiento::firstOrCreate(
                        [
                            'nombre'                      => $pasoData['nombre'], 
                            'seccion_paso_crecimiento_id' => $seccionId
                        ],
                        [
                            'orden'                       => $index + 1,
                            'habilitada_consolidacion'    => $pasoData['habilitada_consolidacion'] ?? false,
                        ]
                    );
                }
            }
        }
    }
}