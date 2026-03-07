<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class DeployApplication extends Command
{
    protected $signature = 'app:deploy';
    protected $description = 'Despliega la aplicación desde el repositorio';

    public function handle()
    {
        $this->info('Iniciando despliegue...');

        // Funciona en L11. Para L10 e inferiores, usa shell_exec o el componente Process
        $run = fn($command) => Process::run($command, fn($type, $b) => $this->line($b));

        $run('php artisan down');
        $run('git pull origin main');
        $run('composer install --no-dev --optimize-autoloader');
        //$run('npm install');
        //$run('npm run build');
        //$run('php artisan migrate --force');
        $run('php artisan optimize');
        $run('php artisan up');

        $this->info('¡Despliegue completado!');
        return 0;
    }
}
