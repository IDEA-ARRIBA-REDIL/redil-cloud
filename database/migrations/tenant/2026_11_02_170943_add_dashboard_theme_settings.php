<?php

use App\Models\ThemeSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (config('dashboard_theme') as $setting) {
            ThemeSetting::query()->firstOrCreate(
                ['category' => 'inicio', 'class' => $setting['class']],
                [...$setting, 'is_active' => true],
            );
        }
    }

    /** Conserva las preferencias del usuario al revertir una migración de datos. */
    public function down(): void {}
};
