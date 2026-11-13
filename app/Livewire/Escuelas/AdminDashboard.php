<?php

namespace App\Livewire\Escuelas;

use App\Models\HorarioMateriaPeriodo;
use App\Models\MateriaPeriodo;
use App\Models\Periodo;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Component;

class AdminDashboard extends Component
{
    public $periodos;

    public $sedes = [];

    public $materiasPeriodo = [];

    public $user;

    #[Rule('required', message: 'Debe seleccionar un período.')]
    public $selectedPeriodoId = null;

    #[Rule('required', message: 'Debe seleccionar una sede.')]
    public $selectedSedeId = null;

    public $selectedMateriaPeriodoId = null;

    public $horarios = null; // Inicia en null para saber que no se ha buscado

    public function mount()
    {
        $this->periodos = Periodo::visiblesEnPanel(Auth::user())->orderBy('nombre', 'desc')->get();
        $this->user = Auth::user();
    }

    // Se ejecuta cuando cambia el Período seleccionado
    public function updatedSelectedPeriodoId($periodoId)
    {
        $this->reset(['selectedSedeId', 'selectedMateriaPeriodoId', 'sedes', 'materiasPeriodo', 'horarios']);
        if ($periodoId) {
            $periodo = Periodo::visiblesEnPanel(Auth::user())->findOrFail($periodoId);
            $this->sedes = $periodo->sedes()->orderBy('nombre')->get();
            $this->materiasPeriodo = MateriaPeriodo::where('periodo_id', $periodoId)->with('materia')->get();
        }
    }

    // Se ejecuta al presionar el botón "Buscar"
    public function buscarHorarios()
    {
        $this->validate();

        Periodo::visiblesEnPanel(Auth::user())->findOrFail($this->selectedPeriodoId);

        $query = HorarioMateriaPeriodo::query()
            ->whereHas('materiaPeriodo', fn ($q) => $q->where('periodo_id', $this->selectedPeriodoId))
            ->whereHas('horarioBase.aula.sede', fn ($q) => $q->where('sedes.id', $this->selectedSedeId));

        if ($this->selectedMateriaPeriodoId) {
            $query->where('materia_periodo_id', $this->selectedMateriaPeriodoId);
        }

        $this->horarios = $query->with([
            'materiaPeriodo.materia:id,nombre',
            'horarioBase.aula.sede:id,nombre',
            'maestros.user:id,primer_nombre,primer_apellido', // Importante para el botón "Acceder"
        ])->withCount([
            'matriculasDeAlumnos as matriculas_count',
            'instanciasMatriculaAlumnos as estados_academicos_count' => function ($q) {
                $q->whereHas('matricula');
            },
        ])->get();
    }

    public function render()
    {
        return view('livewire.escuelas.admin-dashboard', [
            'banners' => \App\Models\BannerEscuela::where('activo', true)->latest()->get(),
        ])
            ->extends('layouts.layoutMaster')
            ->section('content');
    }
}
