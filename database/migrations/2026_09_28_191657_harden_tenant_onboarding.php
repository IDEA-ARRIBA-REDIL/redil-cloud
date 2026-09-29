<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection('central')->table('tenants', function (Blueprint $table): void {
            $table->timestamp('provisioned_at')->nullable();
        });

        // Reconciliar el JSON histórico antes de usar las columnas físicas.
        DB::connection('central')->table('tenants')->orderBy('id')->chunk(100, function ($tenants): void {
            $columns = array_diff(\App\Models\Tenant::getCustomColumns(), ['id', 'created_at', 'updated_at', 'provisioned_at']);
            foreach ($tenants as $tenant) {
                $data = json_decode($tenant->data ?? '{}', true, 512, JSON_THROW_ON_ERROR);
                $updates = [];
                foreach ($columns as $column) {
                    if (array_key_exists($column, $data)) {
                        $updates[$column] = $data[$column];
                        unset($data[$column]);
                    }
                }
                $updates['data'] = json_encode($data, JSON_THROW_ON_ERROR);
                DB::connection('central')->table('tenants')->where('id', $tenant->id)->update($updates);
            }
        });

        Schema::connection('central')->create('invitaciones_tenant', function (Blueprint $table): void {
            $table->id();
            $table->char('token_hash', 64)->unique();
            $table->string('email');
            $table->foreignId('plan_id')->constrained('plans');
            $table->foreignId('created_by')->constrained('users_admins_redil');
            $table->string('payment_reference', 150)->unique();
            $table->timestamp('paid_at');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('tenant_id')->nullable()->unique();
            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->timestamps();
        });

        Schema::connection('central')->create('invitaciones_acceso_tenant', function (Blueprint $table): void {
            $table->id();
            $table->char('token_hash', 64)->unique();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->string('email');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('invitaciones_acceso_tenant');
        Schema::connection('central')->dropIfExists('invitaciones_tenant');
        Schema::connection('central')->table('tenants', function (Blueprint $table): void {
            $table->dropColumn('provisioned_at');
        });
    }
};
