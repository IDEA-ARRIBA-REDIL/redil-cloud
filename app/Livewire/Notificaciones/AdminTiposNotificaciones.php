<?php

namespace App\Livewire\Notificaciones;

use App\Models\Sede;
use App\Models\TipoNotificacion;
use App\Models\TipoUsuario;
use Livewire\Component;
use Livewire\WithPagination;

class AdminTiposNotificaciones extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $readyToLoad = false;

    // Formulario compartido crear/editar
    public ?int $editandoId = null;

    public string $slug = '';

    public string $modulo = '';

    public string $titulo = '';

    public string $descripcion = '';

    /** @var array<string> */
    public array $alcance = [TipoNotificacion::ALCANCE_INDIVIDUAL];

    public ?int $diasVigencia = null;

    /** @var array<int> IDs de sedes destino. Vacío = todas las sedes. */
    public array $sedesIds = [];

    /** @var array<int> IDs de tipos de usuario destino. Vacío = todos los tipos. */
    public array $tiposUsuarioIds = [];

    public bool $activo = true;

    protected string $paginationTheme = 'bootstrap';

    protected function rules(): array
    {
        $uniqueSlug = $this->editandoId
            ? 'unique:tipos_notificaciones,slug,'.$this->editandoId
            : 'unique:tipos_notificaciones,slug';

        return [
            'slug' => ['required', 'string', 'max:100', $uniqueSlug],
            'modulo' => ['required', 'string', 'max:100'],
            'titulo' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'alcance' => ['required', 'array', 'min:1'],
            'alcance.*' => ['in:global,individual,escala_ministerial,ministerio_directo'],
            'diasVigencia' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'sedesIds' => ['nullable', 'array'],
            'sedesIds.*' => ['integer', 'exists:sedes,id'],
            'tiposUsuarioIds' => ['nullable', 'array'],
            'tiposUsuarioIds.*' => ['integer', 'exists:tipo_usuarios,id'],
            'activo' => ['boolean'],
        ];
    }

    protected array $messages = [
        'slug.required' => 'El slug es obligatorio.',
        'slug.unique' => 'Este slug ya está en uso.',
        'modulo.required' => 'El módulo es obligatorio.',
        'titulo.required' => 'El título es obligatorio.',
        'alcance.required' => 'Selecciona al menos un alcance.',
        'alcance.min' => 'Selecciona al menos un alcance.',
        'alcance.*.in' => 'Alcance inválido.',
        'diasVigencia.min' => 'Mínimo 1 día de vigencia.',
        'diasVigencia.max' => 'Máximo 3650 días (10 años).',
    ];

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function abrirModalCrear(): void
    {
        $this->resetForm();
        $this->dispatch('abrir-modal-tipo-notificacion');
    }

    public function abrirModalEditar(int $id): void
    {
        $tipo = TipoNotificacion::findOrFail($id);
        $this->editandoId = $tipo->id;
        $this->slug = $tipo->slug;
        $this->modulo = $tipo->modulo;
        $this->titulo = $tipo->titulo;
        $this->descripcion = $tipo->descripcion ?? '';
        $this->alcance = is_array($tipo->alcance) ? $tipo->alcance : [$tipo->alcance];
        $this->diasVigencia = $tipo->dias_vigencia;
        $this->sedesIds = is_array($tipo->sedes_ids) ? array_map('intval', $tipo->sedes_ids) : [];
        $this->tiposUsuarioIds = is_array($tipo->tipos_usuario_ids) ? array_map('intval', $tipo->tipos_usuario_ids) : [];
        $this->activo = $tipo->activo;
        $this->dispatch('abrir-modal-tipo-notificacion');
    }

    public function guardar(): void
    {
        $this->validate();

        TipoNotificacion::updateOrCreate(
            ['id' => $this->editandoId],
            [
                'slug' => $this->slug,
                'modulo' => $this->modulo,
                'titulo' => $this->titulo,
                'descripcion' => $this->descripcion ?: null,
                'alcance' => $this->alcance,
                'dias_vigencia' => $this->diasVigencia ?: null,
                'sedes_ids' => empty($this->sedesIds) ? null : array_map('intval', $this->sedesIds),
                'tipos_usuario_ids' => empty($this->tiposUsuarioIds) ? null : array_map('intval', $this->tiposUsuarioIds),
                'activo' => $this->activo,
            ]
        );

        $this->dispatch('cerrar-modal-tipo-notificacion');
        session()->flash('success', $this->editandoId ? 'Tipo de notificación actualizado.' : 'Tipo de notificación creado.');
        $this->resetForm();
        $this->resetPage();
    }

    public function toggleActivo(int $id): void
    {
        $tipo = TipoNotificacion::findOrFail($id);
        $tipo->activo = ! $tipo->activo;
        $tipo->save();
    }

    public function eliminar(int $id): void
    {
        TipoNotificacion::findOrFail($id)->delete();
        session()->flash('success', 'Tipo de notificación eliminado.');
    }

    private function resetForm(): void
    {
        $this->reset(['editandoId', 'slug', 'modulo', 'titulo', 'descripcion', 'diasVigencia', 'sedesIds', 'tiposUsuarioIds', 'activo']);
        $this->alcance = [TipoNotificacion::ALCANCE_INDIVIDUAL];
        $this->resetValidation();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $tipos = $this->readyToLoad
            ? TipoNotificacion::where('titulo', 'like', '%'.$this->search.'%')
                ->orWhere('modulo', 'like', '%'.$this->search.'%')
                ->orWhere('slug', 'like', '%'.$this->search.'%')
                ->orderBy('modulo')
                ->orderBy('id')
                ->paginate(15)
            : collect();

        $sedes = Sede::orderBy('nombre')->get(['id', 'nombre']);
        $tiposUsuario = TipoUsuario::orderBy('orden')->where('visible', true)->get(['id', 'nombre', 'nombre_plural']);

        return view('livewire.notificaciones.admin-tipos-notificaciones', [
            'tipos' => $tipos,
            'sedes' => $sedes,
            'tiposUsuario' => $tiposUsuario,
        ]);
    }
}
