<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VersiculoDiarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $versiculos = [
            [
                'fecha_publicacion' => now()->format('Y-m-d'),
                'version_uri' => 'rv1960',
                'libro_nombre' => 'Juan',
                'cita_referencia' => 'Juan 3:16',
                'texto_versiculo' => [
                    [
                        'versiculos' => [
                            ['texto' => 'Porque de tal manera amó Dios al mundo, que ha dado a su Hijo unigénito, para que todo aquel que en él cree, no se pierda, mas tenga vida eterna.', 'numero' => '16']
                        ]
                    ]
                ],
                'usuario_id' => 1,
            ],
            [
                'fecha_publicacion' => now()->addDay()->format('Y-m-d'),
                'version_uri' => 'rv1960',
                'libro_nombre' => 'Filipenses',
                'cita_referencia' => 'Filipenses 4:13',
                'texto_versiculo' => [
                    [
                        'versiculos' => [
                            ['texto' => 'Todo lo puedo en Cristo que me fortalece.', 'numero' => '13']
                        ]
                    ]
                ],
                'usuario_id' => 1,
            ],
            [
                'fecha_publicacion' => now()->addDays(2)->format('Y-m-d'),
                'version_uri' => 'rv1960',
                'libro_nombre' => 'Salmos',
                'cita_referencia' => 'Salmos 23:1',
                'texto_versiculo' => [
                    [
                        'versiculos' => [
                            ['texto' => 'Jehová es mi pastor; nada me faltará.', 'numero' => '1']
                        ]
                    ]
                ],
                'usuario_id' => 1,
            ],
        ];

        foreach ($versiculos as $versiculo) {
            \App\Models\VersiculoDiario::updateOrCreate(
                ['fecha_publicacion' => $versiculo['fecha_publicacion']],
                $versiculo
            );
        }
    }
}
