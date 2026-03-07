<?php

namespace Database\Seeders;

use App\Models\Pago;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 
        Pago::firstOrCreate([
            'compra_id' => 1,
            'tipo_pago_id' => 1,
            'estado_pago_id' => 3,
            'moneda_id' => 1,
            'valor' => '40000',
            'fecha' => '2024-03-02',
            'referencia_pago' => 'AJSM334WOKK22',
            'fecha_vencimiento' => '2024-03-02',
            'int_id_forma_pago' => 0,
            'registro_caja_id' => 0,
            'historial_carga_de_archivo_id' => 0,
            'comision' => 0,
            'valor_neto' => '40000'
        ]);
    }
}
