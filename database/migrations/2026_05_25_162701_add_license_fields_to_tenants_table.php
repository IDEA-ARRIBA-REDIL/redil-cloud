<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Relación con planes
            $table->unsignedBigInteger('plan_id')->nullable()->after('id');
            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();

            // Licencia
            $table->date('license_starts_at')->nullable()->after('plan_id');
            $table->date('license_ends_at')->nullable()->after('license_starts_at');
            $table->date('grace_ends_at')->nullable()->after('license_ends_at')->comment('Siempre = license_ends_at + 7 días');

            // Estado del tenant
            $table->enum('status', [
                'pending_review',
                'active',
                'setup_failed',
                'suspended',
                'expired',
                'cancelled',
            ])->default('pending_review')->after('grace_ends_at');

            // Suspensión (columna real — sacada del JSON data)
            $table->boolean('is_suspended')->default(false)->after('status');
            $table->string('suspension_reason')->nullable()->after('is_suspended')
                ->comment('no_pago, vencimiento, solicitud, abuso');

            // Cuota de miembros
            $table->unsignedInteger('miembros_count_cache')->default(0)->after('suspension_reason');

            // Control de notificaciones (evita correos duplicados por ciclo)
            $table->boolean('notified_30_days')->default(false)->after('miembros_count_cache');
            $table->boolean('notified_7_days')->default(false)->after('notified_30_days');
            $table->boolean('notified_grace')->default(false)->after('notified_7_days');

            // Aprobación
            $table->timestamp('approved_at')->nullable()->after('notified_grace');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
            $table->foreign('approved_by')->references('id')->on('users_admins_redil')->nullOnDelete();

            // Notas internas del Super Admin
            $table->text('notes')->nullable()->after('approved_by');
        });

        // Migrar is_suspended del JSON data a la nueva columna real
        // Esto asegura que los tenants existentes no pierdan su estado
        $tenants = DB::table('tenants')->get();
        foreach ($tenants as $tenant) {
            $data = $tenant->data;

            if (is_string($data)) {
                $data = json_decode($data, true);
            } elseif (is_array($data)) {
                // ya es array, ok
            } else {
                $data = [];
            }

            $isSuspended = isset($data['is_suspended']) && $data['is_suspended'] ? true : false;

            DB::table('tenants')
                ->where('id', $tenant->id)
                ->update([
                    'is_suspended' => $isSuspended,
                    'status' => $isSuspended ? 'suspended' : 'active',
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'plan_id',
                'license_starts_at',
                'license_ends_at',
                'grace_ends_at',
                'status',
                'is_suspended',
                'suspension_reason',
                'miembros_count_cache',
                'notified_30_days',
                'notified_7_days',
                'notified_grace',
                'approved_at',
                'approved_by',
                'notes',
            ]);
        });
    }
};
