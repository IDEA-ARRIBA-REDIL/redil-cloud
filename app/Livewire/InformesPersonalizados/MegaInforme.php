<?php

namespace App\Livewire\InformesPersonalizados;

use App\Enums\EstadoInformeCola;
use App\Jobs\GenerarMegaInformeJob;
use App\Models\Grupo;
use App\Models\InformeEnCola;
use App\Models\InformePersonalizado;
use App\Models\TipoGrupo;
use Livewire\Attributes\On;
use Livewire\Component;

class MegaInformeComponent extends Component
{
    public int $informeId;

    public ?InformePersonalizado $informe = null;

    // Campos del formulario
    public ?int $grupo_id = null;

    public ?int $agrupar_por_tipo_grupo_id = null;

    public int $year;

    public string $periodo = '1t';

    public ?string $semana = null;

    public string $email = '';

    public function mount(int $informeId): void
    {
        $this->informeId = $informeId;
        $this->informe = InformePersonalizado::with(['secciones.subsecciones.items', 'bloques'])
            ->findOrFail($informeId);

        $this->year = (int) date('Y');
        $this->email = auth()->user()->email ?? '';
    }

    #[On('grupo-id-anidado')]
    public function setGrupoId(int $grupoId): void
    {
        $this->grupo_id = $grupoId;
    }

    public function rules(): array
    {
        return [
            'grupo_id' => 'required|exists:grupos,id',
            'agrupar_por_tipo_grupo_id' => 'required|exists:tipo_grupos,id',
            'year' => 'required|integer|min:2000|max:'.(date('Y') + 1),
            'periodo' => 'required|string|in:semana,1m,2m,3m,4m,5m,6m,7m,8m,9m,10m,11m,12m,1t,2t,3t,4t,1s,2s,anio',
            'semana' => 'nullable|required_if:periodo,semana|string',
            'email' => 'required|email|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'grupo_id.required' => 'Debes seleccionar un grupo raíz o ministerio.',
            'agrupar_por_tipo_grupo_id.required' => 'Debes seleccionar el tipo de grupo a incluir.',
            'year.required' => 'El año es obligatorio.',
            'periodo.required' => 'El periodo es obligatorio.',
            'semana.required_if' => 'Debes seleccionar una semana.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo es inválido.',
        ];
    }

    public function solicitarInforme(): void
    {
        $this->validate();

        $informeEnCola = InformeEnCola::create([
            'informe_personalizado_id' => $this->informeId,
            'grupo_id' => $this->grupo_id,
            'agrupar_por_tipo_grupo_id' => $this->agrupar_por_tipo_grupo_id,
            'year' => $this->year,
            'periodo' => $this->periodo,
            'semana' => $this->semana,
            'email' => $this->email,
            'usuario_creacion_id' => auth()->id(),
            'estado' => EstadoInformeCola::Pendiente,
        ]);

        // Despachar Job asíncrono a la cola
        GenerarMegaInformeJob::dispatch($informeEnCola->id);

        $this->dispatch('swal:success', [
            'title' => '¡Informe en Cola!',
            'text' => 'Tu informe ha sido puesto en la cola de procesamiento. Recibirás un correo cuando finalice y podrás descargarlo directamente desde esta tabla.',
        ]);
    }

    public function render()
    {
        $tiposDeGrupos = TipoGrupo::select('id', 'nombre')
            ->orderBy('orden', 'asc')
            ->get();

        $ultimosInformes = InformeEnCola::where('informe_personalizado_id', $this->informeId)
            ->where('usuario_creacion_id', auth()->id())
            ->with(['grupo', 'tipoGrupoAgrupacion'])
            ->latest()
            ->take(10)
            ->get();

        return view('livewire.informes-personalizados.mega-informe', [
            'tiposDeGrupos' => $tiposDeGrupos,
            'ultimosInformes' => $ultimosInformes,
        ]);
    }
}
