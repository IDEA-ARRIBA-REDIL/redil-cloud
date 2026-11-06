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
        Schema::table('ofrendas', function (Blueprint $table): void {
            if (! Schema::hasColumn('ofrendas', 'nombre_donante')) {
                $table->string('nombre_donante', 150)->nullable();
            }
            if (! Schema::hasColumn('ofrendas', 'identificacion_donante')) {
                $table->string('identificacion_donante', 50)->nullable();
            }
            if (! Schema::hasColumn('ofrendas', 'tipo_identificacion_donante_id')) {
                $table->unsignedBigInteger('tipo_identificacion_donante_id')->nullable();
            }
            if (! Schema::hasColumn('ofrendas', 'telefono_donante')) {
                $table->string('telefono_donante', 50)->nullable();
            }
            if (! Schema::hasColumn('ofrendas', 'email_donante')) {
                $table->string('email_donante', 150)->nullable();
            }
            if (! Schema::hasColumn('ofrendas', 'pais_donante_id')) {
                $table->unsignedBigInteger('pais_donante_id')->nullable();
            }
            if (! Schema::hasColumn('ofrendas', 'direccion_donante')) {
                $table->string('direccion_donante', 255)->nullable();
            }
            if (! Schema::hasColumn('ofrendas', 'correo_confirmacion_enviado')) {
                $table->boolean('correo_confirmacion_enviado')->default(false);
            }
            if (! Schema::hasColumn('ofrendas', 'gateway_response')) {
                $table->jsonb('gateway_response')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ofrendas', function (Blueprint $table): void {
            $columns = [
                'nombre_donante',
                'identificacion_donante',
                'tipo_identificacion_donante_id',
                'telefono_donante',
                'email_donante',
                'pais_donante_id',
                'direccion_donante',
                'correo_confirmacion_enviado',
                'gateway_response',
            ];

            $existing = array_filter($columns, fn (string $col): bool => Schema::hasColumn('ofrendas', $col));
            if (! empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
