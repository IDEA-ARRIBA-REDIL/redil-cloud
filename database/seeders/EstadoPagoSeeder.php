<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoPagoSeeder extends Seeder
{
    /**
     * Siembra los estados de pago para todos los métodos de pago del sistema.
     *
     * ─── IMPORTANTE: Estados de ZonaPagos (tipo_pago_id = 1) ────────────────
     * Mapeados según el manual ZonaPagos I-TI-008 v5.0 (mayo 2025).
     * El campo id_codigo_externo corresponde al valor int_estado_pago retornado
     * por la API de VerificacionPago en el campo str_res_pago (posición [4]).
     *
     * Códigos vigentes en v5.0:
     *   888  → Pago pendiente por iniciar (usuario no ha abierto la URL de pago)
     *   999  → Pago pendiente por finalizar (PSE en proceso)
     *   4001 → Pendiente por CR (TC en revisión, hasta ~1 día)
     *   4000 → Rechazado CR (TC rechazada por la franquicia)
     *   4003 → Error CR (equivale a rechazo)
     *   1000 → Pago rechazado
     *   1001 → Error entre ACH y el Banco (equivale a RECHAZO, no a pendiente)
     *   1    → Pago finalizado exitosamente
     *   200  → int_pago_terminado=200 → pago iniciado en pasarela (estado transitorio)
     *
     * Códigos OBSOLETOS eliminados en v5.0 (NO deben usarse):
     *   777  → eliminado
     *   1002 → eliminado
     *   1003 → eliminado
     * ────────────────────────────────────────────────────────────────────────
     */
    public function run(): void
    {
        $estados = [

            // ═══════════════════════════════════════════════════════════════
            // ZONA PAGOS (tipo_pago_id = 1) — Manual v5.0
            // ═══════════════════════════════════════════════════════════════

            [
                // Estado por defecto al redirigir al usuario a ZonaPagos.
                // Checkout.php busca estado_inicial_defecto=true para asignarlo.
                'id' => 5,
                'nombre' => 'Pago Pendiente por Finalizar',
                'color' => '#f39c12',
                'tipo_pago_id' => 1,
                'estado_inicial_defecto' => true,
                'estado_final_inscripcion' => false,
                'id_codigo_externo' => 999,   // PSE en proceso
                'estado_anulado_inscripcion' => false,
                'imprimir_recibo' => false,
                'modificar' => false,
                'eliminar' => false,
                'estado_pendiente' => true,  // La Sonda debe seguir consultando
            ],
            [
                // Pago aprobado. Activa la inscripción/matrícula en cascada.
                'id' => 9,
                'nombre' => 'Pago Finalizado OK',
                'color' => '#00a65a',
                'tipo_pago_id' => 1,
                'estado_inicial_defecto' => false,
                'estado_final_inscripcion' => true,  // ← Activa inscripción
                'id_codigo_externo' => 1,     // Pago exitoso
                'estado_anulado_inscripcion' => false,
                'imprimir_recibo' => false,
                'modificar' => false,
                'eliminar' => false,
                'estado_pendiente' => false,
            ],
            [
                // Pago rechazado por la red (PSE, banco).
                'id' => 4,
                'nombre' => 'Pago Rechazado',
                'color' => '#dd4b39',
                'tipo_pago_id' => 1,
                'estado_inicial_defecto' => false,
                'estado_final_inscripcion' => false,
                'id_codigo_externo' => 1000,  // Rechazado
                'estado_anulado_inscripcion' => true,
                'imprimir_recibo' => false,
                'modificar' => false,
                'eliminar' => true,
                'estado_pendiente' => false,
            ],
            [
                // CORRECCIÓN: 1001 = Error entre ACH y el Banco.
                // El manual v5.0 dice explícitamente: "Equivale a un pago Rechazado".
                // Antes tenía estado_pendiente=true y color naranja → incorrecto.
                'id' => 21,
                'nombre' => 'Error ACH-Banco (Rechazado)',
                'color' => '#dd4b39', // rojo (antes naranja — incorrecto)
                'tipo_pago_id' => 1,
                'estado_inicial_defecto' => false,
                'estado_final_inscripcion' => false,
                'id_codigo_externo' => 1001,  // Error ACH/Banco = RECHAZO
                'estado_anulado_inscripcion' => true, // anula inscripción (antes false)
                'imprimir_recibo' => false,
                'modificar' => false,
                'eliminar' => true,
                'estado_pendiente' => false, // CORRECCIÓN: era true, debe ser false
            ],
            [
                // TC en revisión por la franquicia (~1 día máximo).
                'id' => 6,
                'nombre' => 'Pendiente por CR (TC)',
                'color' => '#f39c12',
                'tipo_pago_id' => 1,
                'estado_inicial_defecto' => false,
                'estado_final_inscripcion' => false,
                'id_codigo_externo' => 4001,  // Pendiente por CR
                'estado_anulado_inscripcion' => false,
                'imprimir_recibo' => false,
                'modificar' => false,
                'eliminar' => false,
                'estado_pendiente' => true,  // La Sonda debe seguir consultando
            ],
            [
                // Rechazo definitivo por la franquicia de TC.
                'id' => 7,
                'nombre' => 'Rechazado por CR (TC)',
                'color' => '#dd4b39',
                'tipo_pago_id' => 1,
                'estado_inicial_defecto' => false,
                'estado_final_inscripcion' => false,
                'id_codigo_externo' => 4000,  // Rechazado CR
                'estado_anulado_inscripcion' => true,
                'imprimir_recibo' => false,
                'modificar' => false,
                'eliminar' => true,
                'estado_pendiente' => false,
            ],
            [
                // Error de la franquicia de TC. Equivale a rechazo.
                'id' => 8,
                'nombre' => 'Error CR (TC)',
                'color' => '#dd4b39',
                'tipo_pago_id' => 1,
                'estado_inicial_defecto' => false,
                'estado_final_inscripcion' => false,
                'id_codigo_externo' => 4003,  // Error CR = rechazo
                'estado_anulado_inscripcion' => true,
                'imprimir_recibo' => false,
                'modificar' => false,
                'eliminar' => true,
                'estado_pendiente' => false,
            ],
            [
                // Pago iniciado en la pasarela pero no procesado aún.
                // NOTA: El código 200 corresponde al campo int_pago_terminado (no int_estado_pago).
                // Existe como referencia, pero la Sonda principal usa int_estado_pago.
                'id' => 37,
                'nombre' => 'Pago Iniciado (en pasarela)',
                'color' => '#f39c12',
                'tipo_pago_id' => 1,
                'estado_inicial_defecto' => false,
                'estado_final_inscripcion' => false,
                'id_codigo_externo' => 200,
                'estado_anulado_inscripcion' => false,
                'imprimir_recibo' => false,
                'modificar' => false,
                'eliminar' => false,
                'estado_pendiente' => true,
            ],
            [
                // Usuario recibió la URL pero aún NO ha abierto la página de pago.
                // El filtro de 7 minutos de la Sonda los excluye naturalmente al inicio.
                'id' => 39,
                'nombre' => 'Pendiente por Iniciar',
                'color' => '#f39c12',
                'tipo_pago_id' => 1,
                'estado_inicial_defecto' => false,
                'estado_final_inscripcion' => false,
                'id_codigo_externo' => 888,   // Pendiente por iniciar
                'estado_anulado_inscripcion' => false,
                'imprimir_recibo' => false,
                'modificar' => false,
                'eliminar' => false,
                'estado_pendiente' => true,
            ],
            [
                // Transacción abandonada: usuario cerró la ventana sin pagar.
                // No tiene código externo de ZonaPagos (se asigna manualmente).
                'id' => 16,
                'nombre' => 'Transacción Abandonada',
                'color' => '#dd4d39',
                'tipo_pago_id' => 1,
                'estado_inicial_defecto' => false,
                'estado_final_inscripcion' => false,
                'id_codigo_externo' => null,
                'estado_anulado_inscripcion' => true,
                'imprimir_recibo' => false,
                'modificar' => false,
                'eliminar' => true,
                'estado_pendiente' => false,
            ],

            // ═══════════════════════════════════════════════════════════════
            // GRUPO ÉXITO (tipo_pago_id = 3)
            // ═══════════════════════════════════════════════════════════════
            ['id' => 3,  'nombre' => 'Finalizado', 'color' => '#00a65a', 'tipo_pago_id' => 3, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => true,  'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => false],
            ['id' => 2,  'nombre' => 'Anulado',    'color' => '#dd4b39', 'tipo_pago_id' => 3, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true,  'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true,  'estado_pendiente' => false],
            ['id' => 1,  'nombre' => 'Pendiente',  'color' => '#f39c12', 'tipo_pago_id' => 3, 'estado_inicial_defecto' => true,  'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => true,  'modificar' => true,  'eliminar' => true,  'estado_pendiente' => true],

            // ═══════════════════════════════════════════════════════════════
            // PUNTOS EFECTY (tipo_pago_id = 4)
            // ═══════════════════════════════════════════════════════════════
            ['id' => 12, 'nombre' => 'Finalizado', 'color' => '#00a65a', 'tipo_pago_id' => 4, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => true,  'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => false],
            ['id' => 11, 'nombre' => 'Anulado',    'color' => '#dd4b39', 'tipo_pago_id' => 4, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true,  'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true,  'estado_pendiente' => false],
            ['id' => 10, 'nombre' => 'Pendiente',  'color' => '#f39c12', 'tipo_pago_id' => 4, 'estado_inicial_defecto' => true,  'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => true,  'modificar' => true,  'eliminar' => true,  'estado_pendiente' => true],

            // ═══════════════════════════════════════════════════════════════
            // EFECTIVO PDP (tipo_pago_id = 5)
            // ═══════════════════════════════════════════════════════════════
            ['id' => 14, 'nombre' => 'Finalizado', 'color' => '#00a65a', 'tipo_pago_id' => 5, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => true,  'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => false],
            ['id' => 15, 'nombre' => 'Anulado',    'color' => '#dd4b39', 'tipo_pago_id' => 5, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true,  'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => false],
            ['id' => 45, 'nombre' => 'Pendiente',  'color' => '#f39c12', 'tipo_pago_id' => 5, 'estado_inicial_defecto' => true,  'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => true],

            // ═══════════════════════════════════════════════════════════════
            // TARJETA DE CRÉDITO PDP (tipo_pago_id = 6)
            // ═══════════════════════════════════════════════════════════════
            ['id' => 19, 'nombre' => 'Finalizado', 'color' => '#00a65a', 'tipo_pago_id' => 6, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => true,  'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => false],
            ['id' => 17, 'nombre' => 'Anulado',    'color' => '#dd4b39', 'tipo_pago_id' => 6, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true,  'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => false],

            // ═══════════════════════════════════════════════════════════════
            // TARJETA DÉBITO PDP (tipo_pago_id = 7)
            // ═══════════════════════════════════════════════════════════════
            ['id' => 20, 'nombre' => 'Finalizado', 'color' => '#00a65a', 'tipo_pago_id' => 7, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => true,  'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => false],
            ['id' => 18, 'nombre' => 'Anulado',    'color' => '#dd4b39', 'tipo_pago_id' => 7, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true,  'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => false, 'estado_pendiente' => false],

            // ═══════════════════════════════════════════════════════════════
            // PAYPAL (tipo_pago_id = 8)
            // ═══════════════════════════════════════════════════════════════
            ['id' => 23, 'nombre' => 'Completed',         'color' => '#121481', 'tipo_pago_id' => 8, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => true,  'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true,  'estado_pendiente' => false],
            ['id' => 22, 'nombre' => 'Canceled_Reversal', 'color' => '#C40C0C', 'tipo_pago_id' => 8, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true,  'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true,  'estado_pendiente' => false],
            ['id' => 24, 'nombre' => 'Created',           'color' => '#DC6B19', 'tipo_pago_id' => 8, 'estado_inicial_defecto' => true,  'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => true,  'eliminar' => true,  'estado_pendiente' => true],
            ['id' => 25, 'nombre' => 'Denied',            'color' => '#C40C0C', 'tipo_pago_id' => 8, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true,  'imprimir_recibo' => false, 'modificar' => true,  'eliminar' => true,  'estado_pendiente' => false],
            ['id' => 28, 'nombre' => 'Pending',           'color' => '#B3C8CF', 'tipo_pago_id' => 8, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true,  'estado_pendiente' => true],
            ['id' => 29, 'nombre' => 'Refunded',          'color' => '#1679AB', 'tipo_pago_id' => 8, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => null,  'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true,  'estado_pendiente' => false],
            ['id' => 30, 'nombre' => 'Reversed',          'color' => '#1679AB', 'tipo_pago_id' => 8, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => null,  'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true,  'estado_pendiente' => false],
            ['id' => 31, 'nombre' => 'Processed',         'color' => '#121481', 'tipo_pago_id' => 8, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true,  'estado_pendiente' => true],
            ['id' => 32, 'nombre' => 'Voided',            'color' => '#C40C0C', 'tipo_pago_id' => 8, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true,  'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true,  'estado_pendiente' => false],

            // ═══════════════════════════════════════════════════════════════
            // PAYU COLOMBIA (tipo_pago_id = 9)
            // ═══════════════════════════════════════════════════════════════
            ['id' => 33, 'nombre' => 'APPROVED',  'color' => '#021d1f', 'tipo_pago_id' => 9, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => true,  'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true,  'estado_pendiente' => false],
            ['id' => 34, 'nombre' => 'DECLINED',  'color' => '#C40C0C', 'tipo_pago_id' => 9, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true,  'imprimir_recibo' => false, 'modificar' => true,  'eliminar' => true,  'estado_pendiente' => false],
            ['id' => 27, 'nombre' => 'ERROR',     'color' => '#C40C0C', 'tipo_pago_id' => 9, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true,  'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true,  'estado_pendiente' => false],
            ['id' => 26, 'nombre' => 'EXPIRED',   'color' => '#B3C8CF', 'tipo_pago_id' => 9, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => true,  'imprimir_recibo' => false, 'modificar' => true,  'eliminar' => true,  'estado_pendiente' => false],
            ['id' => 35, 'nombre' => 'PENDING',   'color' => '#B3C8CF', 'tipo_pago_id' => 9, 'estado_inicial_defecto' => true,  'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => false, 'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true,  'estado_pendiente' => true],
            ['id' => 36, 'nombre' => 'SUBMITTED', 'color' => '#1679AB', 'tipo_pago_id' => 9, 'estado_inicial_defecto' => false, 'estado_final_inscripcion' => false, 'id_codigo_externo' => null, 'estado_anulado_inscripcion' => null,  'imprimir_recibo' => false, 'modificar' => false, 'eliminar' => true,  'estado_pendiente' => false],
        ];

        foreach ($estados as $estado) {
            DB::table('estados_pago')->updateOrInsert(
                ['id' => $estado['id']],
                $estado
            );
        }

        // Marcar como obsoletos en PHP los estados de códigos removidos en ZonaPagos v5.0.
        // IDs afectados: 38 (código 777), 40 (código 1002), 41 (código 1003)
        $obsoletos = DB::table('estados_pago')->whereIn('id', [38, 40, 41])->get();
        foreach ($obsoletos as $estado) {
            $nombreNuevo = str_contains($estado->nombre, '(OBSOLETO v5.0)')
                ? $estado->nombre
                : $estado->nombre.' (OBSOLETO v5.0)';

            DB::table('estados_pago')
                ->where('id', $estado->id)
                ->update([
                    'nombre' => $nombreNuevo,
                    'estado_pendiente' => false,
                    'estado_final_inscripcion' => false,
                    'estado_anulado_inscripcion' => true,
                    'color' => '#888888',
                ]);
        }
    }
}
