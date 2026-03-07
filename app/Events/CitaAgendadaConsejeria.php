<?php

namespace App\Events;

use App\Models\CitaConsejeria;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Log;


class CitaAgendadaConsejeria
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CitaConsejeria $cita;

    /**
     * Create a new event instance.
     */
    public function __construct(CitaConsejeria $cita)
    {
      //Log::info('¡LISTENER EJECUTADO! Sí está entrando a CitaAgendadaConsejeria. 1');
      $this->cita = $cita;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
