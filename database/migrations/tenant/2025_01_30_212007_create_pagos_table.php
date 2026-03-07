<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->integer('compra_id')->nullable();
            $table->integer('tipo_pago_id')->nullable();
            $table->integer('estado_pago_id')->nullable();
            $table->integer('moneda_id')->nullable();
            $table->double('valor');
            $table->date('fecha');
            $table->integer('actividad_categoria_id')->nullable();
            $table->string('referencia_pago', 100)->nullable();
            $table->string('codigo_vaucher', 100)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->integer('int_id_forma_pago')->nullable();
            $table->integer('registro_caja_id')->nullable();
            $table->integer('historial_carga_de_archivo_id')->nullable();
            $table->double('comision')->nullable();
            $table->double('valor_neto')->nullable();
            $table->boolean('anulado_pdp')->default(false);
            // URL única de la transacción a la que se redirige al usuario para pagar.
            $table->text('payment_url')->nullable()->after('valor_neto');

            // ID de la transacción que nos entrega ZonaPagos (CUS, TicketID, etc.).
            $table->string('gateway_transaction_id', 255)->nullable()->after('payment_url');

            // Guarda la respuesta completa de la API para depuración y auditoría.
            $table->json('gateway_response')->nullable()->after('gateway_transaction_id');
            

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
