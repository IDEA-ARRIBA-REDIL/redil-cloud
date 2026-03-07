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
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->comment('Nombre identificador, Ej: Taquilla 1');
            $table->integer('punto_de_pago_id');

            // ¡AQUÍ ESTÁ TU CAMBIO!
            // El cajero (User) asignado a esta caja.
            // Es 'nullable' porque puedes crear la caja y asignarlo después.
            $table->integer('user_id')->nullable();

            $table->boolean('estado')->default(true); // 'activo' renombrado a 'estado'
            $table->time('hora_apertura')->nullable()->after('estado')
                ->comment('Hora de apertura programada para la caja (ej: 07:00)');
            $table->bigInteger('limite_dinero_acumulado')->nullable();
            $table->bigInteger('dinero_acumulado')->nullable();
            // ¡NUEVO CAMPO!
            $table->time('hora_cierre')->nullable()->after('hora_apertura')
                ->comment('Hora de cierre programada para la caja (ej: 16:00)');

            // ¡NUEVO CAMPO!
            // Por defecto, ninguna caja permite modificar registros.
            $table->boolean('permite_modificar_registros')->default(false)->after('hora_cierre')
                ->comment('Permite a los cajeros modificar registros antes del cierre');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
