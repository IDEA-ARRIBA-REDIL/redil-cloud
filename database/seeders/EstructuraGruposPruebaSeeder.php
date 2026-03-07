<?php

namespace Database\Seeders;

use App\Models\ClasificacionAsistente;
use App\Models\Grupo;
use App\Models\ReporteGrupo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class EstructuraGruposPruebaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Roles
        $lider = Role::findByName('Lider');
        $oveja = Role::findByName('Oveja');

        // 2. Crear usuario Didier
        $didier = User::firstOrCreate(
            ['email' => 'didier.lider@redil.com'],
            [
                'password' => bcrypt('12345678'),
                'foto' => 'default-m.png',
                'primer_nombre' => 'Didier',
                'primer_apellido' => 'Lider',
                'identificacion' => 'DIDIER123',
                'tipo_usuario_id' => 2, // Lider
                'sede_id' => 309, // Medellin
                'activo' => 1,
                'asistente_id' => 1, // Usuario sistema
                'genero' => 0,
                'fecha_nacimiento' => '1990-01-01',
                'tipo_vinculacion_id' => 1,
                'email_verified_at' => now(),
            ]
        );

        if ($didier->wasRecentlyCreated) {
            $didier->roles()->attach($lider->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);
        }

        // Asistir al Grupo Principal (ID 1)
        DB::table('integrantes_grupo')->updateOrInsert(
            ['grupo_id' => 1, 'user_id' => $didier->id]
        );

        // 3. Crear Grupo de Didier (Célula de liderazgo Sup General)
        // Tipo 6 según TipoGrupoSeeder: 'Célula de liderazgo Sup General'
        $grupoDidier = Grupo::firstOrCreate(
            ['nombre' => 'Célula Didier - Sup General'],
            [
                'id' => 20,
                'tipo_grupo_id' => 6,
                'sede_id' => 309,
                'dia' => 2, // Martes
                'hora' => '19:00:00',
                'fecha_apertura' => '2025-06-01',
                'usuario_creacion_id' => 1,
                'dado_baja' => 0,
            ]
        );

        $grupoDidier->encargados()->attach($didier->id);


        // 4. Crear 5 Líderes (Nivel 1)
        for ($i = 1; $i <= 5; $i++) {
            $liderN1 = User::create([
                'email' => "lider.nivel1.$i@redil.com",
                'password' => bcrypt('12345678'),
                'foto' => 'default-m.png',
                'primer_nombre' => "Lider N1-$i",
                'primer_apellido' => 'Test',
                'identificacion' => "LIDERN1-$i",
                'tipo_usuario_id' => 2,
                'sede_id' => 309,
                'activo' => 1,
                'asistente_id' => 1,
                'genero' => rand(0, 1),
                'fecha_nacimiento' => '1995-01-01',
                'tipo_vinculacion_id' => 1,
                'email_verified_at' => now(),
            ]);

            $liderN1->roles()->attach($lider->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);

            // Asistir a la célula de Didier
            $grupoDidier->asistentes()->attach($liderN1->id);

            // Crear Grupo Evangelístico para este líder
            // Tipo 2: 'Grupo de crecimiento' (Evangelístico)
            $grupoEvangelisticoN1 = Grupo::firstOrCreate(
                ['nombre' => "Grupo Evangelístico N1 - Lider $i"],
                [
                    'id' => 30 + $i,
                    'tipo_grupo_id' => 2,
                    'sede_id' => 309,
                    'dia' => rand(1, 6), // Lunes a Sábado
                    'hora' => '20:00:00',
                    'fecha_apertura' => '2025-11-01',
                    'usuario_creacion_id' => $didier->id,
                    'dado_baja' => 0,
                ]
            );

            $grupoEvangelisticoN1->encargados()->attach($liderN1->id);

            // Generar Reportes para este grupo N1
            $this->generarReportesParaGrupo($grupoEvangelisticoN1);


            // 5. Crear 7-10 Asistentes (Nivel 2) para el grupo de N1
            $cantidadAsistentes = rand(7, 10);
            $asistentesN2 = [];

            for ($j = 1; $j <= $cantidadAsistentes; $j++) {
                $asistenteN2 = User::create([
                    'email' => "asistente.nivel2.$i.$j@redil.com",
                    'password' => bcrypt('12345678'),
                    'foto' => 'default-m.png',
                    'primer_nombre' => "Asistente N2-$i-$j",
                    'primer_apellido' => 'Test',
                    'identificacion' => "ASISTN2-$i-$j",
                    'tipo_usuario_id' => 3, // Oveja
                    'sede_id' => 309,
                    'activo' => 1,
                    'asistente_id' => 1,
                    'genero' => rand(0, 1),
                    'fecha_nacimiento' => '2000-01-01',
                    'tipo_vinculacion_id' => 1,
                    'email_verified_at' => now(),
                ]);

                $asistenteN2->roles()->attach($oveja->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);

                // Asistir al grupo N1
                $grupoEvangelisticoN1->asistentes()->attach($asistenteN2->id);
                $asistentesN2[] = $asistenteN2;
            }

            // 6. Elegir 1 Asistente N2 y crearle Grupo (Nivel 3)
            if (count($asistentesN2) > 0) {
                $elegidoN2 = $asistentesN2[array_rand($asistentesN2)];

                // Convertir a Líder (opcional, pero lógico)
                $elegidoN2->update(['tipo_usuario_id' => 2]);
                $elegidoN2->roles()->syncWithoutDetaching([$lider->id => ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']]);


                $grupoEvangelisticoN3 = Grupo::updateOrCreate(
                    ['nombre' => "Grupo N3 - Nieto de Didier (" . $elegidoN2->primer_nombre . ")"],
                    [
                        'id' => 50 + $i,
                        'tipo_grupo_id' => 3, // Grupo Warriors (Evangelístico)
                        'sede_id' => 309,
                        'dia' => rand(1, 6),
                        'hora' => '18:00:00',
                        'fecha_apertura' => '2025-12-01',
                        'usuario_creacion_id' => $liderN1->id,
                        'dado_baja' => 0,
                    ]
                );

                $grupoEvangelisticoN3->encargados()->attach($elegidoN2->id);

                 // Crear 4 Asistentes (Nivel 3 - Bisnietos)
                 for ($k = 1; $k <= 4; $k++) {
                    $asistenteN3 = User::create([
                        'email' => "asistente.nivel3.$i.$k@reporte.com", // Emails únicos
                        'password' => bcrypt('12345678'),
                        'foto' => 'default-m.png',
                        'primer_nombre' => "Asistente N3-$i-$k",
                        'primer_apellido' => 'Bisnieto',
                        'identificacion' => "ASISTN3-$i-$k",
                        'tipo_usuario_id' => 3,
                        'sede_id' => 309,
                        'activo' => 1,
                        'asistente_id' => 1,
                        'genero' => rand(0, 1),
                        'fecha_nacimiento' => '2005-01-01',
                        'tipo_vinculacion_id' => 1,
                        'email_verified_at' => now(),
                    ]);
                    $asistenteN3->roles()->attach($oveja->id, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);
                    $grupoEvangelisticoN3->asistentes()->attach($asistenteN3->id);
                 }

                // Generar Reportes Grupo N3
                $this->generarReportesParaGrupo($grupoEvangelisticoN3);
            }
        }
    }

    /**
     * Genera reportes semanales desde 2025-12-01 hasta la fecha actual.
     */
    private function generarReportesParaGrupo(Grupo $grupo)
    {
        $fechaInicio = Carbon::create(2025, 12, 1);
        $fechaActual = Carbon::now();

        // Determinar el primer día de reporte que coincida con $grupo->dia
        // Carbon: 0 (Domingo) - 6 (Sábado). Redil: 1 (Domingo) - 7 (Sábado) ??
        // Revisando GrupoSeeder: 'dia' => 1 (Domingo?). Revisemos TipoGrupoSeeder o lógica negocio.
        // En ReporteGrupo.php->varificarProcesoReporte:
        // $diaGrupoUser = $this->dia; (1..7)
        // $diaGrupoCarbon = $diaGrupoUser - 1; // 0=Domingo, 6=Sábado

        $diaGrupoUser = $grupo->dia;
        $diaGrupoCarbon = $diaGrupoUser - 1; 

        // Avanzamos fechaInicio hasta encontrar el día correcto
        while ($fechaInicio->dayOfWeek != $diaGrupoCarbon) {
            $fechaInicio->addDay();
        }

        $fechaIteracion = $fechaInicio->copy();

        while ($fechaIteracion->lte($fechaActual)) {
            
            // Probabilidad de reporte
            $rand = rand(1, 100);

            if ($rand <= 10) {
                // CASO 3: NO REPORTADO (10%)
                // No hacemos nada, simplemente no creamos registro.
            } elseif ($rand <= 20) {
                // CASO 2: NO REALIZADO (10%)
                ReporteGrupo::firstOrCreate(
                    [
                        'grupo_id' => $grupo->id,
                        'fecha' => $fechaIteracion->format('Y-m-d'),
                    ],
                    [
                        'no_reporte' => true,
                        'motivo_no_reporte_grupo_id' => 1, // Asumiendo ID 1 existe, o null
                        'observacion' => 'No se realizó por motivos de fuerza mayor (Seeder).',
                        'autor_creacion' => 1,
                        'finalizado' => 1,
                        'reporte_a_tiempo' => 1,
                        'created_at' => $fechaIteracion->copy()->addHours(2), // Reportado mismo día
                        'updated_at' => $fechaIteracion->copy()->addHours(2),
                    ]
                );

            } else {
                // CASO 1: REPORTE EXITOSO (80%)
                $asistentes = $grupo->asistentes;
                $asistenciaTotal = 0;
                $inasistenciaTotal = 0;

                $reporte = ReporteGrupo::firstOrCreate(
                    [
                        'grupo_id' => $grupo->id,
                        'fecha' => $fechaIteracion->format('Y-m-d'),
                    ],
                    [
                        'tema' => 'Tema del Seeder ' . $fechaIteracion->format('d/m'),
                        'observacion' => 'Reporte exitoso generado automáticamente.',
                        'no_reporte' => false,
                        'autor_creacion' => 1,
                        'finalizado' => 1,
                        'reporte_a_tiempo' => 1,
                        'created_at' => $fechaIteracion->copy()->addHours(2),
                        'updated_at' => $fechaIteracion->copy()->addHours(2),
                    ]
                );

                // Asistencia Individual (Pivote asistencia_grupos)
                foreach ($asistentes as $asistente) {
                    $asistio = (bool)rand(0, 1);
                    if ($asistio) $asistenciaTotal++;
                    else $inasistenciaTotal++;

                    $reporte->usuarios()->attach($asistente->id, [
                        'asistio' => $asistio,
                        'tipo_inasistencia_id' => $asistio ? null : 1, // 1: Injustificada
                        'observaciones' => $asistio ? null : 'No vino',
                    ]);
                }

                // Actualizar contadores
                $reporte->update([
                    'cantidad_asistencias' => $asistenciaTotal,
                    'cantidad_inasistencias' => $inasistenciaTotal
                ]);

                // Clasificaciones Manuales (1-5)
                // 1: Adultos llegados x grupo, 2: Adultos no creados, 3: Niños llegada x grupo, 4: Niños no creados, 5: Conversiones
                // IDs pueden variar según orden de creación, pero en ClasificacionAsistenteSeeder parecen ser fijos si es firstOrCreate secuencial.
                // Asumiendo IDs 1 a 5.
                $clasificacionesIds = [1, 2, 3, 4, 5];
                foreach ($clasificacionesIds as $clasifId) {
                    $cantidad = rand(0, 3);
                    if ($cantidad > 0) {
                        DB::table('clasificacion_asistente_reporte_grupo')->insert([
                            'reporte_grupo_id' => $reporte->id,
                            'clasificacion_asistente_id' => $clasifId,
                            'cantidad' => $cantidad,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Siguiente semana
            $fechaIteracion->addWeek();
        }
    }
}
