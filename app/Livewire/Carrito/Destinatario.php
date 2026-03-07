<?php

namespace App\Livewire\Carrito;

use Livewire\Component;
use App\Models\Actividad;
use App\Models\SedeDestinatario;
use App\Models\Compra;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Matricula;
use App\Models\Pago;
use App\Models\Sede;

class Destinatario extends Component
{
    public Actividad $actividad;
    public $sedes;
    public $centro;
    public $contador;
    public $totalSecciones;
    public $rolActivo;
    public $usuario;
    public $sedeSeleccionadaId = null;

    public function mount(Actividad $actividad)
    {
        $this->actividad = $actividad;
        $this->sedes = SedeDestinatario::all();
        $this->centro = [
            'lat' => 4.60971, // Latitud de Bogotá
            'lng' => -74.08175 // Longitud de Bogotá
        ];

        if (Auth::check()) {
            $this->rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
            $this->usuario = auth()->user();
        }

        if (count($actividad->destinatarios) > 0) {
            $this->contador = 1;
            $this->totalSecciones = 4;
        } else {
            $this->contador = 1;
            $this->totalSecciones = 3;
        }

        // Intentar recuperar selección previa si existe compra
        $compra = Compra::where('user_id', auth()->id())
            ->where('actividad_id', $actividad->id)
            ->where('estado', 1) // Pendiente
            ->latest()
            ->first();

        if ($compra && $compra->destinatario_id) {
            $this->sedeSeleccionadaId = $compra->destinatario_id;
        }
    }

    public function seleccionarSede($id)
    {
        $this->sedeSeleccionadaId = $id;
        // Opcional: Notificar al usuario o actualizar mapa
        $this->dispatch('sede-seleccionada', id: $id);
    }

    public function procesarPago()
    {
        if (!$this->sedeSeleccionadaId) {
            $this->dispatch('mostrarMensaje', [
                'titulo' => 'Selección Requerida',
                'mensaje' => 'Por favor selecciona una sede en el mapa o en la lista.',
                'tipo' => 'warning'
            ]);
            return;
        }

        // Buscar o crear la compra
        $compra = Compra::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'actividad_id' => $this->actividad->id,
                'estado' => 1 // Pendiente
            ],
            [
                'fecha' => now(),
                'valor' => 0, // Se calculará después o ya viene calculado
                'moneda_id' => 1 // Default, debería venir de config
            ]
        );

        // Guardar la selección en la Compra para consistencia
        $compra->destinatario_id = $this->sedeSeleccionadaId;
        $compra->save();

        if ($this->actividad->tipo && $this->actividad->tipo->tipo_escuelas) {
            $matricula = null;

            // Opción 1: Buscar por referencia de pago (si existe pago)
            $pago = $compra->pagos()->latest()->first();
            if ($pago) {
                $matricula = Matricula::where('referencia_pago', $pago->id)->first();
            }

            // Opción 2 (Fallback): Buscar por usuario y periodo de la actividad
            if (!$matricula && $this->actividad->periodo_id) {
                // Buscamos la matrícula más reciente para este periodo
                $matricula = Matricula::where('user_id', auth()->id())
                    ->where('periodo_id', $this->actividad->periodo_id)
                    ->latest()
                    ->first();
            }

            if ($matricula) {
                $matricula->update(['material_sede_id' => $this->sedeSeleccionadaId]);
                // Log::info("Sede material actualizada para matrícula ID {$matricula->id} a Sede: {$this->sedeSeleccionadaId}");
            } else {
                Log::warning("No se encontró matrícula para actualizar sede material. User: " . auth()->id() . ", Actividad: " . $this->actividad->id);
            }
        }

        // Redirigir al checkout
        return redirect()->route('carrito.checkout', ['compra' => $compra, 'actividad' => $this->actividad]);
    }

    public function render()
    {
        return view('livewire.carrito.destinatario');
    }
}
