<?php

namespace App\Livewire\Central;

use App\Models\Plan;
use Illuminate\Support\Str;
use Livewire\Component;

class GestionarPlanes extends Component
{
    public $plan_id;

    public string $nombre = '';

    public string $slug = '';

    public $max_miembros = '';

    public bool $incluye_logo = false;

    public bool $incluye_marca_blanca = false;

    public bool $activo = true;

    public bool $isModalOpen = false;

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:100',
            'slug' => 'required|string|alpha_dash|max:100|unique:plans,slug,'.$this->plan_id,
            'max_miembros' => 'nullable|integer|min:1',
            'incluye_logo' => 'boolean',
            'incluye_marca_blanca' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    protected $messages = [
        'nombre.required' => 'El nombre del plan es obligatorio.',
        'slug.required' => 'El slug es obligatorio.',
        'slug.unique' => 'Ya existe un plan con este slug.',
        'slug.alpha_dash' => 'El slug solo puede contener letras, números, guiones y guiones bajos.',
        'max_miembros.integer' => 'El límite de miembros debe ser un número entero.',
        'max_miembros.min' => 'El límite de miembros debe ser mayor a 0.',
    ];

    /** Genera el slug automáticamente cuando cambia el nombre. */
    public function updatedNombre(string $value): void
    {
        if (! $this->plan_id) {
            $this->slug = Str::slug($value);
        }
    }

    public function create(): void
    {
        $this->resetInputFields();
        $this->isModalOpen = true;
    }

    public function edit(int $id): void
    {
        $plan = Plan::findOrFail($id);
        $this->plan_id = $plan->id;
        $this->nombre = $plan->nombre;
        $this->slug = $plan->slug;
        $this->max_miembros = $plan->max_miembros ?? '';
        $this->incluye_logo = $plan->incluye_logo;
        $this->incluye_marca_blanca = $plan->incluye_marca_blanca;
        $this->activo = $plan->activo;
        $this->isModalOpen = true;
    }

    public function store(): void
    {
        $this->validate();

        Plan::updateOrCreate(
            ['id' => $this->plan_id],
            [
                'nombre' => $this->nombre,
                'slug' => $this->slug,
                'max_miembros' => $this->max_miembros ?: null,
                'incluye_logo' => $this->incluye_logo,
                'incluye_marca_blanca' => $this->incluye_marca_blanca,
                'activo' => $this->activo,
            ]
        );

        session()->flash('message', $this->plan_id ? 'Plan actualizado correctamente.' : 'Plan creado exitosamente.');

        $this->isModalOpen = false;
        $this->resetInputFields();
    }

    public function toggleActivo(int $id): void
    {
        $plan = Plan::findOrFail($id);
        $plan->activo = ! $plan->activo;
        $plan->save();

        session()->flash('message', 'Estado del plan actualizado.');
    }

    public function eliminar(int $id): void
    {
        $plan = Plan::findOrFail($id);

        if ($plan->tenants()->count() > 0) {
            session()->flash('error', "No se puede eliminar el plan \"{$plan->nombre}\" porque tiene iglesias asignadas.");

            return;
        }

        $plan->delete();
        session()->flash('message', "Plan \"{$plan->nombre}\" eliminado correctamente.");
        $this->dispatch('msn');
    }

    public function closeModal(): void
    {
        $this->isModalOpen = false;
        $this->resetInputFields();
    }

    private function resetInputFields(): void
    {
        $this->plan_id = null;
        $this->nombre = '';
        $this->slug = '';
        $this->max_miembros = '';
        $this->incluye_logo = false;
        $this->incluye_marca_blanca = false;
        $this->activo = true;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.central.gestionar-planes', [
            'planes' => Plan::withCount('tenants')->orderBy('nombre')->get(),
        ])->layout('layouts.centralApp');
    }
}
