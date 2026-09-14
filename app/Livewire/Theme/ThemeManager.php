<?php

namespace App\Livewire\Theme;

use App\Models\ThemeSetting;
use App\Services\BrandingEntitlementService;
use App\Services\ThemeService;
use Illuminate\View\View;
use Livewire\Component;

class ThemeManager extends Component
{
    public $categories = [];

    public $settings = [];

    public $editingId = null;

    public $activeCategory = null; // Nueva propiedad para mantener la categoría activa

    // Alternativamente, puedes usar protected $rules si prefieres
    protected $rules = [
        'editingValue' => 'required|regex:/^#[A-Fa-f0-9]{3}(?:[A-Fa-f0-9]{3})(?:[A-Fa-f0-9]{2})?$/',

    ];

    public $editingValue = '';

    public $editingValue2 = '';

    public $showSuccessMessage = false;

    public function mount(): void
    {
        $this->authorizeThemeAccess();
        $this->loadSettings();
        // Establecer la primera categoría como activa por defecto
        if (! $this->activeCategory && ! empty($this->categories)) {
            $this->activeCategory = $this->categories[0];
        }
    }

    public function loadSettings(): void
    {
        $allSettings = ThemeSetting::orderBy('category', 'ASC')
            ->orderBy('id', 'ASC')
            ->where('is_active', 'true')
            ->get();
        $this->categories = $allSettings->pluck('category')->unique()->values()->toArray();

        // Agrupar configuraciones por categoría
        $this->settings = [];
        foreach ($this->categories as $category) {
            $this->settings[$category] = $allSettings->where('category', $category)->values()->toArray();
        }
    }

    public function startEditing(int $id, string $value, ?string $value2 = null): void
    {
        $this->authorizeThemeAccess();
        $setting = ThemeSetting::query()->findOrFail($id);
        $this->activeCategory = $setting->category; // Guardar la categoría al iniciar edición
        $this->editingId = $id;
        $this->editingValue = $value;
        $this->editingValue2 = $value2 ?? '';
    }

    public function cancelEditing(): void
    {
        $this->editingId = null;
        $this->editingValue = '';
        $this->resetValidation();
    }

    public function updateColor(): void
    {
        $this->authorizeThemeAccess();
        $this->validate();

        $setting = ThemeSetting::query()->findOrFail($this->editingId);
        $this->activeCategory = $setting->category; // Guardar la categoría actual

        $setting->value = $this->editingValue;
        $setting->value2 = $this->editingValue2;

        $setting->save();

        // Actualizar SCSS
        app(ThemeService::class)->updateScssFile();

        $this->editingId = null;
        $this->editingValue = '';

        $this->loadSettings();

        $this->showSuccessMessage = true;
    }

    public function hideMessage(): void
    {
        $this->showSuccessMessage = false;
    }

    public function setActiveCategory(string $category): void
    {
        $this->activeCategory = $category;
    }

    public function render(): View
    {
        return view('livewire.theme.theme-manager', [
            'activeCategory' => $this->activeCategory,
        ]);
    }

    private function authorizeThemeAccess(): void
    {
        app(BrandingEntitlementService::class)
            ->authorizeUser(auth()->user(), 'configuraciones.subitem_plantilla');
    }
}
