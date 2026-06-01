<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use App\Notifications\NotificacionGeneral;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class NotifyTestPush extends Command
{
    protected $signature = 'notify:test-push {--email=}';

    protected $description = 'Envía una notificación push de prueba a un usuario (por email) o al primer usuario con suscripciones push';

    public function handle(): int
    {
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->error('No se encontraron tenants en la base de datos central.');

            return self::FAILURE;
        }

        $email = $this->option('email');

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);

            if ($email) {
                $user = User::where('email', $email)->first();
            } else {
                $user = User::has('pushSubscriptions')->first();
            }

            if ($user) {
                $this->info("Tenant: {$tenant->id} | Usuario: {$user->email}");

                $subCount = $user->pushSubscriptions()->count();
                $this->info("Suscripciones push activas: {$subCount}");

                Notification::sendNow($user, new NotificacionGeneral([
                    'titulo' => 'Prueba Push - REDIL CLOUD',
                    'mensaje' => 'La notificación push se envió correctamente desde el backend.',
                    'icono' => 'ti-bell',
                    'color' => 'primary',
                    'url' => '/dashboard',
                ]));

                $this->info('Push enviada.');

                return self::SUCCESS;
            }
        }

        $this->warn('No se encontró ningún usuario con suscripciones push activas.');

        return self::FAILURE;
    }
}
