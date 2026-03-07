<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoPagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {



        $estados = [
            ['id' => 9, 'nombre' => 'Pago Finalizado OK', 'color' => '#00a65a', 'tipo_pago_id' => 1, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => true, 'id_codigo_externo' => 1, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => false],
            ['id' => 12, 'nombre' => 'Finalizado', 'color' => '#00a65a', 'tipo_pago_id' => 4, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => true, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => false],
            ['id' => 3, 'nombre' => 'Finalizado', 'color' => '#00a65a', 'tipo_pago_id' => 3, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => true, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => false],
            ['id' => 4, 'nombre' => 'Pago rechazado', 'color' => '#dd4b39', 'tipo_pago_id' => 1, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => 1000, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 2, 'nombre' => 'Anulado', 'color' => '#dd4b39', 'tipo_pago_id' => 3, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 11, 'nombre' => 'Anulado', 'color' => '#dd4b39', 'tipo_pago_id' => 4, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 1, 'nombre' => 'Pendiente', 'color' => '#f39c12', 'tipo_pago_id' => 3, 'estado_inicial_defecto' => true, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => true, 'modificar' => true, 'eliminar' => true, 'estado_pendiente' => true],
            ['id' => 5, 'nombre' => 'Pago Pendiente por Finalizar', 'color' => '#f39c12', 'tipo_pago_id' => 1, 'estado_inicial_defecto' => true, 'estado_final_inscripcion' => false, 'id_codigo_externo' => 999, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => true],
            ['id' => 6, 'nombre' => 'Pendiente por CR', 'color' => '#f39c12', 'tipo_pago_id' => 1, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => 4001, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => true],
            ['id' => 7, 'nombre' => 'Rechazado por CR', 'color' => '#dd4b39', 'tipo_pago_id' => 1, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => 4000, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 8, 'nombre' => 'Error CR', 'color' => '#dd4b39', 'tipo_pago_id' => 1, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => 4003, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 10, 'nombre' => 'Pendiente', 'color' => '#f39c12', 'tipo_pago_id' => 4, 'estado_inicial_defecto' => true, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => true, 'modificar' => true, 'eliminar' => true, 'estado_pendiente' => true],
            ['id' => 14, 'nombre' => 'Finalizado', 'color' => '#00a65a', 'tipo_pago_id' => 5, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => true, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => false],
            ['id' => 15, 'nombre' => 'Anulado', 'color' => '#dd4b39', 'tipo_pago_id' => 5, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => false],
             ['id' => 45, 'nombre' => 'Pendiente ', 'color' => '#f39c12', 'tipo_pago_id' => 5, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => true],
            ['id' => 16, 'nombre' => 'Transacción abandonada', 'color' => '#dd4d39', 'tipo_pago_id' => 1, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 17, 'nombre' => 'Anulado', 'color' => '#dd4b39', 'tipo_pago_id' => 6, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => false],
            ['id' => 18, 'nombre' => 'Anulado', 'color' => '#dd4b39', 'tipo_pago_id' => 7, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => false],
            ['id' => 19, 'nombre' => 'Finalizado', 'color' => '#00a65a', 'tipo_pago_id' => 6, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => true, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => false],
            ['id' => 20, 'nombre' => 'Finalizado', 'color' => '#00a65a', 'tipo_pago_id' => 7, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => true, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => false],
            ['id' => 21, 'nombre' => 'Pendiente 1001', 'color' => '#dd4b39', 'tipo_pago_id' => 1, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => 1001, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => true],
            ['id' => 22, 'nombre' => 'Canceled_Reversal', 'color' => '#C40C0C', 'tipo_pago_id' => 8, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 23, 'nombre' => 'Completed', 'color' => '#121481', 'tipo_pago_id' => 8, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => true, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 24, 'nombre' => 'Created', 'color' => '#DC6B19', 'tipo_pago_id' => 8, 'estado_inicial_defecto' => true, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => true, 'eliminar' => true, 'estado_pendiente' => true],
            ['id' => 28, 'nombre' => 'Pending', 'color' => '#B3C8CF', 'tipo_pago_id' => 8, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 29, 'nombre' => 'Refunded', 'color' => '#1679AB', 'tipo_pago_id' => 8, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => null, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 30, 'nombre' => 'Reversed', 'color' => '#1679AB', 'tipo_pago_id' => 8, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => null, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 31, 'nombre' => 'Processed', 'color' => '#121481', 'tipo_pago_id' => 8, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true, 'estado_pendiente' => true],
            ['id' => 32, 'nombre' => 'Voided', 'color' => '#C40C0C', 'tipo_pago_id' => 8, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 34, 'nombre' => 'DECLINED', 'color' => '#C40C0C', 'tipo_pago_id' => 9, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => true, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 25, 'nombre' => 'Denied', 'color' => '#C40C0C', 'tipo_pago_id' => 8, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => true, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 27, 'nombre' => 'ERROR', 'color' => '#C40C0C', 'tipo_pago_id' => 9, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 26, 'nombre' => 'EXPIRED', 'color' => '#B3C8CF', 'tipo_pago_id' => 9, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => true, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 36, 'nombre' => 'SUBMITTED', 'color' => '#1679AB', 'tipo_pago_id' => 9, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => null, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 35, 'nombre' => 'PENDING', 'color' => '#B3C8CF', 'tipo_pago_id' => 9, 'estado_inicial_defecto' => true, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 33, 'nombre' => 'APPROVED', 'color' => '#021d1f', 'tipo_pago_id' => 9, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => true, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 37, 'nombre' => 'Pago Iniciado', 'color' => '#f39c12', 'tipo_pago_id' => 1, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => 200, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => true],
            ['id' => 38, 'nombre' => 'Pago Declinado', 'color' => '#dd4b39', 'tipo_pago_id' => 1, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => 777, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 39, 'nombre' => 'Pendiente por Iniciar', 'color' => '#f39c12', 'tipo_pago_id' => 1, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => 888, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => true],
            ['id' => 40, 'nombre' => 'Pago Rechazado (1002)', 'color' => '#dd4b39', 'tipo_pago_id' => 1, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => 1002, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true, 'estado_pendiente' => false],
            ['id' => 41, 'nombre' => 'Pago Fallido', 'color' => '#dd4b39', 'tipo_pago_id' => 1, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => 1003, 'estado_anulado_inscripcion' => true, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true, 'estado_pendiente' => false],
        ];

        foreach ($estados as $estado) {
            DB::table('estados_pago')->updateOrInsert(
                ['id' => $estado['id']],
                $estado
            );
        }
    }
}
