<?php

namespace Tests\Feature;

use App\Livewire\Theme\ThemeManager;
use App\Models\ThemeSetting;
use App\Services\BrandingEntitlementService;
use App\Services\DashboardThemeService;
use App\Services\ThemeService;
use Livewire\Livewire;
use Mockery\MockInterface;
use Tests\TestCase;

class DashboardThemeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'central',
            'database.connections.central' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            'cache.default' => 'array', 'logging.default' => 'null',
            'view.compiled' => storage_path('framework/views'),
        ]);
        app('db')->purge('central');
        app('db')->setDefaultConnection('central');
        (require database_path('migrations/tenant/2024_11_26_170440_create_theme_settings_table.php'))->up();
        $this->mock(BrandingEntitlementService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('authorizeUser')->andReturnNull();
        });
    }

    private function installDefaults(): void
    {
        (require database_path('migrations/tenant/2026_11_02_170943_add_dashboard_theme_settings.php'))->up();
    }

    public function test_current_colors_are_preserved_even_before_the_data_migration(): void
    {
        $expected = [
            'dashboard-header-background' => '#2F5D50', 'dashboard-header-text' => '#ffffff',
            'dashboard-points-background' => 'linear-gradient(135deg, #3b762b 0%, #1f3d1a 100%)',
            'dashboard-points-text' => '#ffffff',
            'dashboard-devotional-background' => '#2D5046', 'dashboard-devotional-text' => '#ffffff',
        ];
        $this->assertSame($expected, app(DashboardThemeService::class)->colors());
        $this->installDefaults();
        $this->assertSame(6, ThemeSetting::count());
        $this->assertSame($expected, app(DashboardThemeService::class)->colors());
    }

    public function test_reinstalling_defaults_preserves_customized_values(): void
    {
        $this->installDefaults();
        ThemeSetting::query()->where('class', 'dashboard-header-background')->update(['value' => '#abcdef']);
        $this->installDefaults();
        $this->assertSame(6, ThemeSetting::count());
        $this->assertSame('#abcdef', app(DashboardThemeService::class)->colors()['dashboard-header-background']);
    }

    public function test_background_and_text_are_editable_independently_in_the_theme_editor(): void
    {
        $this->installDefaults();
        $this->mock(ThemeService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('updateScssFile')->twice()->andReturnTrue();
        });
        $background = ThemeSetting::query()->where('class', 'dashboard-devotional-background')->firstOrFail();
        $text = ThemeSetting::query()->where('class', 'dashboard-devotional-text')->firstOrFail();
        $component = Livewire::test(ThemeManager::class)->assertSee('Inicio')
            ->assertSee('Fondo de Mi tiempo con Dios')->assertSee('Texto de los puntos');
        $component->call('startEditing', $background->id, $background->value, $background->value2)
            ->set('editingValue', '#abcdef')->call('updateColor')->assertHasNoErrors();
        $component->call('startEditing', $text->id, $text->value, $text->value2)
            ->set('editingValue', '#123456')->call('updateColor')->assertHasNoErrors();
        $colors = app(DashboardThemeService::class)->colors();
        $this->assertSame('#abcdef', $colors['dashboard-devotional-background']);
        $this->assertSame('#123456', $colors['dashboard-devotional-text']);
        $this->assertSame('#ffffff', $colors['dashboard-points-text']);
        $this->assertSame('#2F5D50', $colors['dashboard-header-background']);
    }

    public function test_the_two_gradient_colors_are_saved_and_used(): void
    {
        $this->installDefaults();
        $this->mock(ThemeService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('updateScssFile')->once()->andReturnTrue();
        });
        $setting = ThemeSetting::query()->where('class', 'dashboard-points-background')->firstOrFail();
        Livewire::test(ThemeManager::class)->call('startEditing', $setting->id, $setting->value, $setting->value2)
            ->set('editingValue', '#123456')->set('editingValue2', '#abcdef')->call('updateColor')->assertHasNoErrors();
        $this->assertSame('linear-gradient(135deg, #123456 0%, #abcdef 100%)', app(DashboardThemeService::class)->colors()['dashboard-points-background']);
    }

    public function test_invalid_gradient_and_text_values_are_rejected(): void
    {
        $this->installDefaults();
        $setting = ThemeSetting::query()->where('class', 'dashboard-points-background')->firstOrFail();
        $component = Livewire::test(ThemeManager::class)->call('startEditing', $setting->id, $setting->value, $setting->value2);
        $component->set('editingValue2', 'red; background: url(x)')->call('updateColor')->assertHasErrors(['editingValue2']);
        $component->set('editingValue2', '')->call('updateColor')->assertHasErrors(['editingValue2']);
        $component->set('editingValue2', '#abcdef')->set('editingValue', 'invalid')->call('updateColor')->assertHasErrors(['editingValue']);
        $this->assertSame('#3b762b', $setting->fresh()->value);
        $this->assertSame('#1f3d1a', $setting->fresh()->value2);
    }

    public function test_invalid_stored_css_falls_back_to_the_original_colors(): void
    {
        $this->installDefaults();
        ThemeSetting::query()->where('class', 'dashboard-points-background')->update(['value' => '</style>', 'value2' => 'url(x)']);
        $this->assertSame('linear-gradient(135deg, #3b762b 0%, #1f3d1a 100%)', app(DashboardThemeService::class)->colors()['dashboard-points-background']);
    }
}
