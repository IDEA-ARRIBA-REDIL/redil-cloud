<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Iglesia;
use Stancl\Tenancy\Facades\Tenancy;

class CreateDemoTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-demo-tenants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create iglesia1, iglesia2, iglesia3 demo tenants with names.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenants = ['iglesia2', 'iglesia3'];

        foreach ($tenants as $index => $id) {
            $name = 'Iglesia ' . ($index + 2);
            $this->info("Creating tenant: {$id} ($name)");

            $tenant = Tenant::find($id);

            if (!$tenant) {
                $tenant = Tenant::create(['id' => $id]);
                // Attach main cloud domain
                $tenant->domains()->create(['domain' => $id . '.redilcloud']);
                // Attach localhost fallback
                $tenant->domains()->create(['domain' => $id]);
                $this->info(" -> DB and domains created.");
            } else {
                $this->info(" -> Tenant already exists.");
            }
        }

        $this->info('Running pending migrations and seeders for new tenants...');
        $this->call('tenants:migrate');
        $this->call('tenants:seed', ['--tenants' => $tenants]);

        foreach ($tenants as $index => $id) {
             $name = 'Iglesia ' . ($index + 2);
             $tenant = Tenant::find($id);
             Tenancy::initialize($tenant);

             // Check if Iglesia table exists before updating
             if (\Illuminate\Support\Facades\Schema::hasTable('iglesias')) {
                 Iglesia::updateOrCreate(
                     ['id' => 1],
                     ['nombre' => $name, 'logo' => 'default.png', 'configuracion_id' => 1]
                 );
                 $this->info(" -> Configured {$name} metadata.");
             }

             Tenancy::end();
        }

        $this->info('Demo tenants generated successfully.');
    }
}
