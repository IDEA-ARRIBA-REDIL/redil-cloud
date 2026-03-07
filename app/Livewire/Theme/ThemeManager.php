<?php

namespace App\Livewire\Theme;

use Livewire\Component;
use App\Models\ThemeSetting;
use App\Services\ThemeService;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Validate;

class ThemeManager extends Component
{
    public $categories = [];
    public $settings = [];
    public $editingId = null;
    public $activeCategory = null; // Nueva propiedad para mantener la categoría activa


 // Alternativamente, puedes usar protected $rules si prefieres
 protected $rules = [
  'editingValue' => 'required|regex:/^#[A-Fa-f0-9]{3}(?:[A-Fa-f0-9]{3})(?:[A-Fa-f0-9]{2})?$/'

];
    public $editingValue = '';

    public $editingValue2 = '';

    public $showSuccessMessage = false;

    public function mount()
    {
        $this->loadSettings();
        // Establecer la primera categoría como activa por defecto
        if (!$this->activeCategory && !empty($this->categories)) {
          $this->activeCategory = $this->categories[0];
      }
    }

    public function loadSettings()
    {
        $allSettings = ThemeSetting::orderBy('category','ASC')->where('is_active','true')->get();
        $this->categories = $allSettings->pluck('category')->unique()->values()->toArray();

        // Agrupar configuraciones por categoría
        $this->settings = [];
        foreach ($this->categories as $category) {
            $this->settings[$category] = $allSettings->where('category', $category)->values()->toArray();
        }
    }

    public function startEditing($id, $value, $value2)
    {
        $setting = ThemeSetting::find($id);
        $this->activeCategory = $setting->category; // Guardar la categoría al iniciar edición
        $this->editingId = $id;
        $this->editingValue = $value;
        $this->editingValue2= $value2;
    }


    public function cancelEditing()
    {
        $this->editingId = null;
        $this->editingValue = '';
        $this->resetValidation();
    }

    public function updateColor()
    {


        $this->validate();

        $setting = ThemeSetting::find($this->editingId);
        $currentSetting = $setting->first(); // Obtener la categoría del color actual
        $this->activeCategory = $currentSetting->category; // Guardar la categoría actual

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

    public function hideMessage()
    {
        $this->showSuccessMessage = false;
    }

    public function setActiveCategory($category)
    {
        $this->activeCategory = $category;
    }

    public function render()
    {
        return view('livewire.theme.theme-manager', [
            'activeCategory' => $this->activeCategory
        ]);
    }
}
