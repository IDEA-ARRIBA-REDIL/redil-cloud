<?php

namespace App\Livewire\Central;

use App\Models\Plan;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;

class GestionarPlanes extends ComponenteAdminCentral
{
    #[Locked]
    public ?int $plan_id = null;

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
            'slug' => ['required', 'string', 'regex:/^[a-z0-9]+(?:[-_][a-z0-9]+)*$/', 'max:100', Rule::unique(Plan::class, 'slug')->ignore($this->plan_id)],
            'max_miembros' => 'nullable|integer|min:1|max:2147483647',
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
        'slug.regex' => 'Usa letras minúsculas, números y guiones, sin espacios.',
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
        app(\App\Services\SeguridadAdminService::class)->exigir();
        $this->resetInputFields();
        $this->isModalOpen = true;
    }

    public function edit(int $id): void
    {
        app(\App\Services\SeguridadAdminService::class)->exigir();
        $this->resetValidation();
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
        app(\App\Services\SeguridadAdminService::class)->exigir();
        $this->nombre = trim($this->nombre);
        $this->slug = trim($this->slug);
        $this->max_miembros = $this->max_miembros === '' ? null : $this->max_miembros;
        $this->validate();

        if ($this->plan_id !== null) {
            Plan::findOrFail($this->plan_id);
        }

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
        app(\App\Services\SeguridadAdminService::class)->exigir();
        $plan = Plan::findOrFail($id);
        $plan->activo = ! $plan->activo;
        $plan->save();

        session()->flash('message', $plan->activo ? 'Plan activado para nuevas asignaciones.' : 'Plan inactivado. Las iglesias asignadas conservan su plan y su licencia.');
    }

    public function eliminar(int $id): void
    {
        app(\App\Services\SeguridadAdminService::class)->exigir();
        $plan = Plan::findOrFail($id);

        if ($plan->tenants()->exists() || \App\Models\InvitacionTenant::query()->where('plan_id', $id)->exists()) {
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
        $this->resetValidation();
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
