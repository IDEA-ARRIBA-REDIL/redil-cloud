<?php

namespace App\Livewire\Central;

use App\Services\SeguridadAdminService;
use Livewire\Component;

abstract class ComponenteAdminCentral extends Component
{
    public function boot(): void
    {
        app(SeguridadAdminService::class)->exigir();
    }
}
