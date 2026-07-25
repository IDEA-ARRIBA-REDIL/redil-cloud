<?php

namespace App\Livewire\Carrito;

use App\Models\Actividad;
use App\Models\Compra;
use App\Models\Matricula;
use App\Models\Pago;
use App\Models\SedeDestinatario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

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
            'lng' => -74.08175, // Longitud de Bogotá
        ];

        if (Auth::check()) {
            $user = auth()->user();
            $this->rolActivo = $user?->roles()->wherePivot('activo', true)->first() ?? $user?->roles()->first();
            $this->usuario = $user;
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
        if (! $this->sedeSeleccionadaId) {
            $this->dispatch('mostrarMensaje', [
                'titulo' => 'Selección Requerida',
                'mensaje' => 'Por favor selecciona una sede en el mapa o en la lista.',
                'tipo' => 'warning',
            ]);

            return;
        }

        // Buscar o crear la compra
        $compra = Compra::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'actividad_id' => $this->actividad->id,
                'estado' => 1, // Pendiente
            ],
            [
                'fecha' => now(),
                'valor' => 0, // Se calculará después o ya viene calculado
                'moneda_id' => 1, // Default
            ]
        );

        // Guardar la selección en la Compra para consistencia
        $compra->destinatario_id = $this->sedeSeleccionadaId;
        $compra->save();

        if ($this->actividad->tipo && $this->actividad->tipo->tipo_escuelas) {
            $matricula = null;

            // Opción 1: Buscar mediante la relación Pago -> Matricula de la compra
            $pago = $compra->pagos()->latest('id')->first();
            if ($pago) {
                $matricula = $pago->matricula ?? Matricula::where('referencia_pago', $pago->id)->first();
            }

            // Opción 2: Buscar si hay pagos asociados a la compra en la tabla matriculas
            if (! $matricula) {
                $pagoIds = $compra->pagos()->pluck('id');
                if ($pagoIds->isNotEmpty()) {
                    $matricula = Matricula::whereIn('referencia_pago', $pagoIds)->latest('id')->first();
                }
            }

            // Opción 3 (Fallback): Buscar por usuario y periodo de la actividad
            if (! $matricula) {
                $matricula = Matricula::where('user_id', auth()->id())
                    ->when($this->actividad->periodo_id, function ($q) {
                        $q->where('periodo_id', $this->actividad->periodo_id);
                    })
                    ->latest('id')
                    ->first();
            }

            if ($matricula) {
                $matricula->update(['material_sede_id' => $this->sedeSeleccionadaId]);
                Log::info("Destinatario: Sede de material guardada exitosamente en Matricula ID {$matricula->id} -> material_sede_id: {$this->sedeSeleccionadaId}");
            } else {
                Log::warning('Destinatario: No se encontró matrícula para actualizar sede material. User: '.auth()->id().', Actividad: '.$this->actividad->id);
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
