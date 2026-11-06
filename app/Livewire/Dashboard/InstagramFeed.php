<?php

namespace App\Livewire\Dashboard;

use App\Models\Configuracion;
use App\Services\InstagramService;
use Illuminate\View\View;
use Livewire\Component;

class InstagramFeed extends Component
{
    public function render(): View
    {
        abort_unless(auth()->check() && tenant('id') !== null, 403);
        $configuracion = Configuracion::first();
        $connected = filled($configuracion?->instagram_user_id);
        $feed = $connected
            ? app(InstagramService::class)->feed($configuracion)
            : ['posts' => [], 'unavailable' => false];

        return view('livewire.dashboard.instagram-feed', [
            'connected' => $connected,
            'username' => $configuracion?->instagram_username,
            'posts' => $feed['posts'],
            'unavailable' => $feed['unavailable'],
        ]);
    }
}
