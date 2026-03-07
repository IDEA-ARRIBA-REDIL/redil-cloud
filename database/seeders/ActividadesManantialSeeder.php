<?php

namespace Database\Seeders;

use App\Models\Abono;
use App\Models\Actividad;
use App\Models\ActividadCategoria;
use App\Models\Compra;
use App\Models\Pago;
use App\Models\RangoEdad;
use App\Models\User;
use App\Models\Inscripcion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Caja; // Importamos el modelo Caja

class ActividadesManantialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Iniciando ActividadesManantialSeeder...');

        try {
          /*
            // --- ACTIVIDAD 1: CAMPAMENTO WARRIORS ---
            $this->command->info('Creando Actividad 1: CAMPAMENTO WARRIORS...');

            $actividadWarriors = Actividad::firstOrCreate(
                ['nombre' => 'CAMPAMENTO WARRIORS'],
                [
                'descripcion' => 'Un campamento diseñado para fortalecer la fe y el liderazgo en jóvenes y adultos cristianos. A través de talleres, prédicas y actividades de compañerismo, buscamos equipar a cada participante para ser un guerrero espiritual en su comunidad. Será un tiempo de renovación, desafío y crecimiento profundo en un entorno natural inspirador.',
                'instrucciones_finales' => '<p>¡Gracias por inscribirte! Prepárate para una experiencia transformadora.</p>',
                'color' => '#8B0000',
                'fondo' => '#F5F5DC',
                'tipo_actividad_id' => 2, // Evento Interno con registro obligatorio con abonos
                'totalmente_publica' => false,
                'fecha_visualizacion' => '2026-01-01',
                'fecha_inicio' => '2026-03-01',
                'fecha_finalizacion' => '2026-03-31',
                'fecha_cierre' => '2026-02-28',
                'restriccion_por_categoria' => true,
                'activa' => false,
                'punto_de_pago' => true
            ]);

            $this->command->info('Actividad 1 ID: ' . $actividadWarriors->id);

            if($actividadWarriors->wasRecentlyCreated){
                 // Lógica antigua movida
            }
             // NUEVA LÓGICA REQUERIDA
             if ($actividadWarriors->tipo && $actividadWarriors->tipo->requiere_inicio_sesion) {
                 $actividadWarriors->monedas()->sync([1]);
                 $actividadWarriors->tiposPago()->sync([1]);
             } else {
                 if($actividadWarriors->wasRecentlyCreated){
                     $actividadWarriors->monedas()->attach(1);
                     $actividadWarriors->tiposPago()->attach([1, 2, 3, 4]);
                 }
             }

            $todosLosRangosEdad = RangoEdad::pluck('id')->toArray();

            $this->command->info('Creando Categorías para Actividad 1...');

            $categoriaNacionalesW = ActividadCategoria::firstOrCreate(
                ['actividad_id' => $actividadWarriors->id, 'nombre' => 'Sedes Nacionales'],
                [
                'aforo' => 200,
                'limite_compras' => 1,
                'genero' => 3,
                'vinculacion_grupo' => 2,
                'actividad_grupo' => 2,
            ]);
            if($categoriaNacionalesW->wasRecentlyCreated){
                $categoriaNacionalesW->monedas()->attach(1, ['valor' => 410000]);
                $categoriaNacionalesW->rangosEdad()->attach($todosLosRangosEdad);
            }

            $categoriaBogotaW = ActividadCategoria::firstOrCreate(
                ['actividad_id' => $actividadWarriors->id, 'nombre' => 'Sedes Bogotá'],
                [
                'aforo' => 200,
                'limite_compras' => 1,
                'genero' => 3,
                'vinculacion_grupo' => 2,
                'actividad_grupo' => 2,
            ]);
            if($categoriaBogotaW->wasRecentlyCreated){
                $categoriaBogotaW->monedas()->attach(1, ['valor' => 440000]);
                $categoriaBogotaW->rangosEdad()->attach($todosLosRangosEdad);
            }

            $this->command->info('Creando Abonos para Actividad 1...');
            $abono1 = Abono::firstOrCreate(['fecha_inicio' => '2025-07-01', 'fecha_fin' => '2025-07-10']);
            $abono2 = Abono::firstOrCreate(['fecha_inicio' => '2025-07-11', 'fecha_fin' => '2025-07-20']);
            $abono3 = Abono::firstOrCreate(['fecha_inicio' => '2025-07-21', 'fecha_fin' => '2025-07-31']);
              */

            /// ACTIVDADES PARA EJEMPLO DE AYUNO Y INSCRIPCION PASTORES
            $this->command->info('Creando Actividad: AYUNO 2026 INSCRIPCIÓN GENERAL...');

            $ayuno2026 = Actividad::firstOrCreate(
                ['nombre' => 'AYUNO 2026 INSCRIPCIÓN GENERAL'],
                [
                'id' => 11,
                'descripcion' => 'Un campamento diseñado para fortalecer la fe y el liderazgo en jóvenes y adultos cristianos. A través de talleres, prédicas y actividades de compañerismo, buscamos equipar a cada participante para ser un guerrero espiritual en su comunidad. Será un tiempo de renovación, desafío y crecimiento profundo en un entorno natural inspirador.',
                'instrucciones_finales' => '<p>¡Gracias por inscribirte! Prepárate para una experiencia transformadora.</p>',
                'color' => '#8B0000',
                'fondo' => '#F5F5DC',
                'tipo_actividad_id' => 3, // Evento Interno con registro obligatorio con abonos
                'totalmente_publica' => true,
                'fecha_visualizacion' => '2026-01-01',
                'fecha_inicio' => '2026-03-01',
                'fecha_finalizacion' => '2026-03-31',
                'fecha_cierre' => '2026-02-28',
                'restriccion_por_categoria' => false,
                'activa' => true,
                 'mostrar_en_proximas_actividades' => true,
                'punto_de_pago' => true

            ]);

             // NUEVA LÓGICA REQUERIDA
             if ($ayuno2026->tipo && $ayuno2026->tipo->requiere_inicio_sesion) {
                 $ayuno2026->monedas()->sync([1]);
                 $ayuno2026->tiposPago()->sync([1]);
             }

            $categoriaGeneral = ActividadCategoria::firstOrCreate(
                ['actividad_id' => $ayuno2026->id, 'nombre' => 'General'],
                [
                'aforo' => 4000,
                'limite_compras' => 1,
                'es_gratuita' => true
            ]);
            /*
            $this->command->info('Creando Actividad: AYUNO 2026 INSCRIPCIÓN PASTORES...');

            $ayuno2026Pastores = Actividad::firstOrCreate(
                ['nombre' => 'AYUNO 2026 INSCRIPCIÓN PASTORES'],
                [
                'id' => 12,
                'descripcion' => 'Un campamento diseñado para fortalecer la fe y el liderazgo en jóvenes y adultos cristianos. A través de talleres, prédicas y actividades de compañerismo, buscamos equipar a cada participante para ser un guerrero espiritual en su comunidad. Será un tiempo de renovación, desafío y crecimiento profundo en un entorno natural inspirador.',
                'instrucciones_finales' => '<p>¡Gracias por inscribirte! Prepárate para una experiencia transformadora.</p>',
                'color' => '#8B0000',
                'fondo' => '#F5F5DC',
                'tipo_actividad_id' => 1, // Evento Interno con registro obligatorio con abonos
                'totalmente_publica' => false,
                 'fecha_visualizacion' => '2025-07-01',
                'fecha_inicio' => '2026-01-15',
                'fecha_finalizacion' => '2026-01-20',
                'fecha_cierre' => '2025-12-31',
                'restriccion_por_categoria' => false,
                'activa' => false,
                'estado_inscripcion_defecto' => 1,
                'tiene_invitados'=>true
            ]);

            $categoriaGeneralPastores = ActividadCategoria::firstOrCreate(
                ['actividad_id' => $ayuno2026Pastores->id, 'nombre' => 'General'],
                [
                'aforo' => 4000,
                'limite_compras' => 1,
                'es_gratuita' => true
            ]);




            // --- INICIO DE LA CORRECCIÓN ---
            // Asignación de una única cuota por categoría para cada período de abono.
            $this->command->info('Asignando abonos a categorías de Actividad 1...');

            if($categoriaNacionalesW->wasRecentlyCreated) {
                // Cuotas para "Sedes Nacionales" (Total: 410.000)
                $categoriaNacionalesW->abonos()->attach($abono1->id, ['valor' => 150000, 'moneda_id' => 1]); // Cuota 1
                $categoriaNacionalesW->abonos()->attach($abono2->id, ['valor' => 150000, 'moneda_id' => 1]); // Cuota 2
                $categoriaNacionalesW->abonos()->attach($abono3->id, ['valor' => 110000, 'moneda_id' => 1]); // Cuota 3
            }

            if($categoriaBogotaW->wasRecentlyCreated) {
                // Cuotas para "Sedes Bogotá" (Total: 440.000)
                $categoriaBogotaW->abonos()->attach($abono1->id, ['valor' => 150000, 'moneda_id' => 1]); // Cuota 1
                $categoriaBogotaW->abonos()->attach($abono2->id, ['valor' => 150000, 'moneda_id' => 1]); // Cuota 2
                $categoriaBogotaW->abonos()->attach($abono3->id, ['valor' => 140000, 'moneda_id' => 1]); // Cuota 3
            }
            // --- FIN DE LA CORRECCIÓN ---


            // --- Creación de Compras y Pagos de Ejemplo (Sin cambios) ---
            $this->command->info('Creando compras y pagos de ejemplo...');
            $user11 = User::find(11);
            if ($user11) {
                // Check if compra already exists to avoid duplicates in seeder re-runs
                // Assuming one purchase per activity per user for this example
                $compraUser11 = Compra::firstOrCreate(
                    [
                        'user_id' => $user11->id,
                        'actividad_id' => $actividadWarriors->id,
                    ],
                    [
                    'moneda_id' => 1,
                    'fecha' => Carbon::now(),
                    'valor' => 410000,
                    'estado' => 2,
                    'metodo_pago_id' => 1,
                    'nombre_completo_comprador' => $user11->primer_nombre . ' ' . $user11->primer_apellido,
                    'identificacion_comprador' => $user11->identificacion,
                    'telefono_comprador' => $user11->telefono_movil ?? '3000000000',
                    'email_comprador' => $user11->email,
                ]);

                if($compraUser11->wasRecentlyCreated) {
                    Pago::firstOrCreate([
                        'compra_id' => $compraUser11->id,
                        'tipo_pago_id' => 1,
                        'estado_pago_id' => 1,
                        'moneda_id' => 1,
                        'valor' => 180000,
                        'fecha' => Carbon::now(),
                    ]);
                }
            }

            $user6 = User::find(6);
            if ($user6) {
                $compraUser6 = Compra::firstOrCreate(
                    [
                        'user_id' => $user6->id,
                        'actividad_id' => $actividadWarriors->id,
                    ],
                    [
                    'moneda_id' => 1,
                    'fecha' => Carbon::now(),
                    'valor' => 440000,
                    'estado' => 2,
                    'metodo_pago_id' => 1,
                    'nombre_completo_comprador' => $user6->primer_nombre . ' ' . $user6->primer_apellido,
                    'identificacion_comprador' => $user6->identificacion,
                    'telefono_comprador' => $user6->telefono_movil ?? '3000000000',
                    'email_comprador' => $user6->email,
                ]);

                if($compraUser6->wasRecentlyCreated) {
                    Pago::firstOrCreate([
                        'compra_id' => $compraUser6->id,
                        'tipo_pago_id' => 1,
                        'estado_pago_id' => 1,
                        'moneda_id' => 1,
                        'valor' => 100000,
                        'fecha' => Carbon::now(),
                    ]);
                }
            }

            // ===================================================================
            // ¡INICIO DE LOS CAMBIOS SOLICITADOS!
            // ===================================================================

            // --- ACTIVIDAD 2: CAMPAMENTO DE NIÑOS (MODIFICADO) ---
            $this->command->info('Creando Actividad 2: Campamento de Niños...');

            $actividadNinos = Actividad::firstOrCreate(
                ['nombre' => 'Campamento de Niños'],
                [
                'descripcion' => '¡El campamento más divertido del año! Un espacio seguro y lleno de alegría...',
                'instrucciones_finales' => '<p>Los padres recibirán un cronograma detallado por correo electrónico.</p>',
                'color' => '#3498DB',
                'fondo' => '#FDFEFE',
                'tipo_actividad_id' => 2, // Asumimos que ID 2 permite abonos
                'totalmente_publica' => false,

                // --- ¡FECHAS MODIFICADAS! ---
                 'fecha_visualizacion' => '2026-01-01',
                'fecha_inicio' => '2026-03-01',
                'fecha_finalizacion' => '2026-03-31',
                'fecha_cierre' => '2026-02-28',
                // --- FIN DE FECHAS MODIFICADAS ---

                'restriccion_por_categoria' => true,
                'activa' => true,
                'punto_de_pago' => true
            ]);

            if($actividadNinos->wasRecentlyCreated){
                // Logica antigua...
            }
             // NUEVA LÓGICA REQUERIDA
             if ($actividadNinos->tipo && $actividadNinos->tipo->requiere_inicio_sesion) {
                 $actividadNinos->monedas()->sync([1]);
                 $actividadNinos->tiposPago()->sync([1]);
             } else {
                 if($actividadNinos->wasRecentlyCreated){
                     $actividadNinos->monedas()->attach(1);
                     $actividadNinos->tiposPago()->attach([1, 2, 3, 4]);
                 }
             }

            // Categorías (Sin cambios en su creación)
            $rangosEdadNinos = RangoEdad::where('edad_maxima', '<', 18)->pluck('id')->toArray();
            $categoriaNacionalesN = ActividadCategoria::firstOrCreate(
                ['actividad_id' => $actividadNinos->id, 'nombre' => 'Sedes Nacionales'],
                [
                'aforo' => 200,
                'limite_compras' => 1,
            ]);
            if($categoriaNacionalesN->wasRecentlyCreated){
                $categoriaNacionalesN->monedas()->attach(1, ['valor' => 300000]); // Total 300k
                $categoriaNacionalesN->rangosEdad()->attach($rangosEdadNinos);
            }

            $categoriaBogotaN = ActividadCategoria::firstOrCreate(
                ['actividad_id' => $actividadNinos->id, 'nombre' => 'Sedes Bogotá'],
                [
                'aforo' => 200,
                'limite_compras' => 1,
            ]);
            if($categoriaBogotaN->wasRecentlyCreated){
                $categoriaBogotaN->monedas()->attach(1, ['valor' => 330000]); // Total 330k
                $categoriaBogotaN->rangosEdad()->attach($rangosEdadNinos);
            }

            // --- ¡NUEVOS ABONOS AÑADIDOS! ---
            $this->command->info('Creando y asignando abonos para Actividad 2...');
            // 1. Creamos los 3 períodos de abono (Nov y Dic)
            $abonoNinos1 = Abono::firstOrCreate(['fecha_inicio' => '2025-11-01', 'fecha_fin' => '2025-11-15']);
            $abonoNinos2 = Abono::firstOrCreate(['fecha_inicio' => '2025-11-16', 'fecha_fin' => '2025-11-30']);
            $abonoNinos3 = Abono::firstOrCreate(['fecha_inicio' => '2025-12-01', 'fecha_fin' => '2025-12-31']);

            // 2. Asignamos las cuotas para "Sedes Nacionales" (Total: 300k)
            //
            if($categoriaNacionalesN->wasRecentlyCreated) {
                $categoriaNacionalesN->abonos()->attach($abonoNinos1->id, ['valor' => 120000, 'moneda_id' => 1]);
                $categoriaNacionalesN->abonos()->attach($abonoNinos2->id, ['valor' => 100000, 'moneda_id' => 1]);
                $categoriaNacionalesN->abonos()->attach($abonoNinos3->id, ['valor' => 80000, 'moneda_id' => 1]);
            }

            // 3. Asignamos cuotas proporcionales para "Sedes Bogotá" (Total: 330k)
            if($categoriaBogotaN->wasRecentlyCreated) {
                $categoriaBogotaN->abonos()->attach($abonoNinos1->id, ['valor' => 130000, 'moneda_id' => 1]); // (130k)
                $categoriaBogotaN->abonos()->attach($abonoNinos2->id, ['valor' => 100000, 'moneda_id' => 1]); // (100k)
                $categoriaBogotaN->abonos()->attach($abonoNinos3->id, ['valor' => 100000, 'moneda_id' => 1]); // (100k)
            }
            // --- FIN DE ABONOS AÑADIDOS ---

            // ===================================================================
            // ¡FIN DE LOS CAMBIOS SOLICITADOS!
            // ===================================================================


            */
            // --- ACTIVIDAD 3: ENCUENTRO (Sin cambios) ---
            $this->command->info('Creando Actividad 3: Encuentro de Parejas...');

            $actividadEncuentro = Actividad::firstOrCreate(
                ['nombre' => 'Encuentro de Parejas'],
                [
                'descripcion' => 'Un fin de semana para reconectar, sanar y fortalecer tu matrimonio a la luz de la palabra de Dios. A través de charlas, dinámicas y tiempos de ministración, las parejas encontrarán herramientas prácticas para construir una relación más sólida y amorosa. Es una inversión invaluable para su futuro juntos.',
                'instrucciones_finales' => '<p>Traer ropa cómoda y corazón dispuesto.</p>',
                'color' => '#9B59B6',
                'fondo' => '#EAECEE',
                'tipo_actividad_id' => 5,
                'totalmente_publica' => false,
                 'fecha_visualizacion' => '2026-01-01',
                'fecha_inicio' => '2026-03-01',
                'fecha_finalizacion' => '2026-03-31',
                'fecha_cierre' => '2026-02-28',
                'restriccion_por_categoria' => true,
                'activa' => true,
                'mostrar_en_proximas_actividades' => true,
                'punto_de_pago' => true
            ]);
            if($actividadEncuentro->wasRecentlyCreated){
               // Logica antigua...
            }
             // NUEVA LÓGICA REQUERIDA
             if ($actividadEncuentro->tipo && $actividadEncuentro->tipo->requiere_inicio_sesion) {
                 $actividadEncuentro->monedas()->sync([1]);
                 $actividadEncuentro->tiposPago()->sync([1]);
             } else {
                 if($actividadEncuentro->wasRecentlyCreated){
                     $actividadEncuentro->monedas()->attach(1);
                     $actividadEncuentro->tiposPago()->attach([1, 2, 3, 4]);
                 }
             }

            $categoriaIndividualE = ActividadCategoria::firstOrCreate(
                ['actividad_id' => $actividadEncuentro->id, 'nombre' => 'Individual'],
                [
                'aforo' => 100,
                'limite_compras' => 1,
            ]);
            if($categoriaIndividualE->wasRecentlyCreated){
                $categoriaIndividualE->monedas()->attach(1, ['valor' => 80000]);
            }

            $categoriaParejaE = ActividadCategoria::firstOrCreate(
                ['actividad_id' => $actividadEncuentro->id, 'nombre' => 'Pareja'],
                [
                'aforo' => 50,
                'limite_compras' => 1,
            ]);
            if($categoriaParejaE->wasRecentlyCreated){
                $categoriaParejaE->monedas()->attach(1, ['valor' => 150000]);
            }
                /*

            // ===================================================================
            // SECCIÓN GENERACIÓN DE DATOS DE PRUEBA (COMPRAS, PAGOS, INSCRIPCIONES)
            // ===================================================================
            $this->command->info('Generando datos de prueba masivos (Compras, Pagos, Inscripciones)...');

            // 1. Obtener actividades válidas (excluyendo tipo_escuelas)
            $actividadesValidas = Actividad::with(['tipo', 'categorias.monedas'])
                ->whereHas('tipo', function($q) {
                    $q->where('tipo_escuelas', false); // Excluir escuelas
                })
                ->get();

            if ($actividadesValidas->isEmpty()) {
                $this->command->warn('No hay actividades válidas (no escuelas) para generar datos de prueba.');
            } else {
                // Obtener usuarios para asignar las compras (asumimos que existen)
                $usuariosPrueba = User::limit(50)->get();

                // Obtener cajas disponibles
                $cajasIds = Caja::pluck('id');

                if ($usuariosPrueba->isEmpty()) {
                     $this->command->warn('No hay usuarios en la base de datos para asignar compras.');
                } else {
                    $contadorCompras = 0;
                    $metaCompras = 30;

                    // Generar registros hasta completar la meta
                    while ($contadorCompras < $metaCompras) {
                        try {
                            $actividad = $actividadesValidas->random();

                            // Verificar si tiene categorías
                            if ($actividad->categorias->isEmpty()) continue;

                            $categoria = $actividad->categorias->random();

                            // Obtener precio y moneda
                            $monedaPivot = $categoria->monedas->first();
                            $valor = $monedaPivot ? $monedaPivot->pivot->valor : 0;
                            $monedaId = $monedaPivot ? $monedaPivot->id : 1;

                            $usuario = $usuariosPrueba->random();

                            // Definir escenario aleatorio
                            $escenario = collect(['aprobada', 'pendiente', 'abonada', 'anulada'])->random();

                            // Estado de la Compra (FK a estados_pago)
                            // ID 9: Pago Finalizado OK (Tipo 1)
                            // ID 5: Pendiente por Finalizar (Tipo 1)
                            // ID 4: Pago Rechazado (Tipo 1)

                            $estadoCompraId = 9; // Default
                            if ($escenario === 'aprobada') $estadoCompraId = 9;
                            if ($escenario === 'pendiente') $estadoCompraId = 5;
                            if ($escenario === 'abonada') $estadoCompraId = 5; // Sigue pendiente de pago total
                            if ($escenario === 'anulada') $estadoCompraId = 4;

                            // Randomizar Método de Pago (1: Internet, 3: Exito, 4: Efecty, 6: Tarjeta Crédito)
                            $metodoPagoId = collect([1, 3, 4, 6])->random();

                            // Seleccionar Caja Aleatoria si existen cajas
                            $cajaId = $cajasIds->isNotEmpty() ? $cajasIds->random() : null;

                            // --- Crear Compra ---
                            $compra = Compra::firstOrCreate(
                                [
                                    'user_id' => $usuario->id,
                                    'actividad_id' => $actividad->id,
                                ],
                                [
                                'moneda_id' => $monedaId,
                                'fecha' => Carbon::now()->subDays(rand(1, 60)),
                                'valor' => $valor,
                                'estado' => $estadoCompraId,
                                'metodo_pago_id' => $metodoPagoId,
                                'nombre_completo_comprador' => $usuario->primer_nombre . ' ' . $usuario->primer_apellido,
                                'identificacion_comprador' => $usuario->identificacion,
                                'telefono_comprador' => $usuario->telefono_movil ?? '3000000000',
                                'email_comprador' => $usuario->email,
                            ]);

                            // Solo crear inscripción y pagos si la compra es nueva
                            if($compra->wasRecentlyCreated) {
                                // --- Crear Inscripción ---
                                Inscripcion::firstOrCreate(
                                    [
                                        'user_id' => $usuario->id,
                                        'actividad_categoria_id' => $categoria->id,
                                    ],
                                    [
                                    'compra_id' => $compra->id,
                                    'fecha' => Carbon::now(),
                                    'estado' => 1, // Activa / Confirmada
                                    'email' => $usuario->email,
                                    'nombre_inscrito' => $usuario->primer_nombre . ' ' . $usuario->primer_apellido,
                                ]);

                                // --- Crear Pagos según escenario ---
                                if ($escenario === 'aprobada') {
                                    // Un solo pago por el total, aprobado
                                    Pago::firstOrCreate([
                                        'compra_id' => $compra->id,
                                        'tipo_pago_id' => $metodoPagoId,
                                        'estado_pago_id' => 9, // Finalizado OK
                                        'moneda_id' => $monedaId,
                                        'valor' => $valor,
                                        'fecha' => Carbon::now(),
                                        'registro_caja_id' => $cajaId, // Asignar Caja
                                    ]);
                                } elseif ($escenario === 'abonada') {
                                    // Pago parcial (abono) aprobado
                                    $valorAbono = floor($valor * 0.3);
                                    Pago::firstOrCreate([
                                        'compra_id' => $compra->id,
                                        'tipo_pago_id' => $metodoPagoId,
                                        'estado_pago_id' => 9, // Finalizado OK
                                        'moneda_id' => $monedaId,
                                        'valor' => $valorAbono,
                                        'fecha' => Carbon::now()->subDays(rand(1, 5)),
                                        'registro_caja_id' => $cajaId, // Asignar Caja
                                    ]);
                                } elseif ($escenario === 'pendiente') {
                                    // Pago registrado pero en estado pendiente
                                    Pago::firstOrCreate([
                                        'compra_id' => $compra->id,
                                        'tipo_pago_id' => $metodoPagoId,
                                        'estado_pago_id' => 5, // Pendiente por finalizar
                                        'moneda_id' => $monedaId,
                                        'valor' => $valor,
                                        'fecha' => Carbon::now(),
                                        'registro_caja_id' => $cajaId, // Asignar Caja
                                    ]);
                                } elseif ($escenario === 'anulada') {
                                    // Pago rechazado
                                    Pago::firstOrCreate([
                                        'compra_id' => $compra->id,
                                        'tipo_pago_id' => $metodoPagoId,
                                        'estado_pago_id' => 4, // Rechazado
                                        'moneda_id' => $monedaId,
                                        'valor' => $valor,
                                        'fecha' => Carbon::now(),
                                        'registro_caja_id' => $cajaId, // Asignar Caja
                                    ]);
                                }
                            }

                            $contadorCompras++;

                        } catch (\Exception $e) {
                            // Ignorar error en iteración individual y continuar
                            $this->command->warn('Error generando una compra dummy: ' . $e->getMessage());
                        }
                    }
                    $this->command->info("Se generaron $contadorCompras compras de prueba adicionales.");
                }
            }
           */
            $this->command->info('¡ActividadesManantialSeeder completado exitosamente!');

        } catch (\Exception $e) {
            $this->command->error('Error en ActividadesManantialSeeder: ' . $e->getMessage());
            $this->command->error('Archivo: ' . $e->getFile() . ' Línea: ' . $e->getLine());
            $this->command->error('Trace: ' . $e->getTraceAsString());
        }
    }
}
