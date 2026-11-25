<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Database\Seeders\CuentaInicialTenantSeeder;
use Illuminate\Console\Command;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Jobs\CreateDatabase;

class PrepararCuentaLocal extends Command
{
    protected $signature = 'tenant:preparar-cuenta-local {--tenant=cuenta_inicial : Identificador exclusivo del ensayo} {--nombre=Iglesia de prueba : Nombre de la iglesia} {--database=m : Base local m o redil_integracion_test_YYYYMMDD}';

    protected $description = 'Construye una cuenta vacía exclusivamente en una base PostgreSQL local permitida.';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('Este comando solo funciona en local/testing.');

            return self::FAILURE;
        }
        $database = (string) $this->option('database');
        if ($database !== 'm' && ! preg_match('/^redil_integracion_test_[0-9]{8}$/', $database)) {
            $this->error('Base rechazada: utiliza m o una base aislada redil_integracion_test_YYYYMMDD.');

            return self::FAILURE;
        }
        foreach (['central', 'pgsql'] as $name) {
            if (config('database.connections.'.$name.'.driver') !== 'pgsql'
                || ! in_array(config('database.connections.'.$name.'.host'), ['127.0.0.1', 'localhost', '::1'], true)) {
                $this->error('Todas las conexiones deben apuntar a PostgreSQL local.');

                return self::FAILURE;
            }
            config(['database.connections.'.$name.'.database' => $database, 'database.connections.'.$name.'.url' => null]);
            app('db')->purge($name);
        }
        $connection = (new Tenant)->getConnection();
        if (! app()->environment(['local', 'testing']) || $connection->getDriverName() !== 'pgsql'
            || ! in_array($connection->getConfig('host'), ['127.0.0.1', 'localhost', '::1'], true)
            || $connection->getConfig('database') !== $database) {
            $this->error('Este comando solo admite el entorno local/testing y una base local permitida.');

            return self::FAILURE;
        }
        $identity = $connection->selectOne('select current_database() as database, host(inet_server_addr()) as host');
        if ($identity->database !== $database || ! in_array($identity->host, ['127.0.0.1', '::1'], true)) {
            $this->error('La conexión real no corresponde a la base local autorizada.');

            return self::FAILURE;
        }
        $tenantId = (string) $this->option('tenant');
        if (! preg_match('/^cuenta_inicial(?:_[a-z0-9]+)*$/', $tenantId)) {
            $this->error('Usa cuenta_inicial o un identificador que comience por cuenta_inicial_.');

            return self::FAILURE;
        }
        config(['cache.default' => 'array', 'mail.default' => 'array', 'queue.default' => 'sync']);
        $tenant = Tenant::find($tenantId);
        if ($tenant && ! $tenant->cuenta_inicial_local) {
            $this->error('El tenant existente no pertenece a este ensayo.');

            return self::FAILURE;
        }
        $tenant ??= Tenant::withoutEvents(fn () => Tenant::create([
            'id' => $tenantId, 'onboarding_version' => 1, 'cuenta_inicial_local' => true,
            'church_name' => (string) $this->option('nombre'), 'admin_email' => 'admin@example.invalid',
            'status' => 'pending_review', 'is_suspended' => false,
        ]));
        if (! $tenant->database()->manager()->databaseExists($tenant->database()->getName())) {
            (new CreateDatabase($tenant))->handle(app(DatabaseManager::class));
        }
        if ($this->call('tenants:migrate', ['--tenants' => [$tenantId], '--no-interaction' => true]) !== 0) {
            return self::FAILURE;
        }
        $tenant->run(fn () => app(CuentaInicialTenantSeeder::class)->setCommand($this)->__invoke());
        $this->info('Cuenta preparada en '.$database.' / '.$tenant->database()->getName().'. No se registraron dominios ni se enviaron accesos.');

        return self::SUCCESS;
    }
}
