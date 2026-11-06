<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $permiso = Permission::query()->firstOrCreate(
            ['name' => 'finanzas.consultar_transacciones', 'guard_name' => 'web'],
            ['titulo' => 'Consultar transacciones', 'descripcion' => 'Consultar pagos de actividades y ofrendas desde Finanzas']
        );

        Role::query()
            ->whereHas('permissions', fn ($query) => $query->where('name', 'finanzas.item_finanzas'))
            ->get()
            ->each(fn (Role $rol) => $rol->givePermissionTo($permiso));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::query()->where('name', 'finanzas.consultar_transacciones')->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
