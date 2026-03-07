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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('moneda_id');
            $table->date('fecha');
            $table->double('valor');
            $table->integer('estado')->default(1);  /* 1=INICIADA 2=EN PASARELA DE PAGO 3=PAGADA 4=ABANDONADA/ ERROR / NO PAGADO/ RECHAZO*/
            $table->integer('pariente_usuario_id')->nullable();
            $table->integer('metodo_pago_id')->default(0);
            $table->integer('destinatario_id')->nullable();
            $table->string('nombre_completo_comprador', 200);
            $table->string('identificacion_comprador', 200);
            $table->string('telefono_comprador', 200);
            $table->string('email_comprador');
            $table->string('listado_carrito', 500)->nullable();
            $table->string('actividad_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
