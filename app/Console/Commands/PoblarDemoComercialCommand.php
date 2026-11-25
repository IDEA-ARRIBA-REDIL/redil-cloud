<?php

namespace App\Console\Commands;

use App\Models\Actividad;
use App\Models\Curso;
use App\Models\Escuela;
use App\Models\Grupo;
use App\Models\Hito;
use App\Models\HorarioBase;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\Plan;
use App\Models\PlanLector;
use App\Models\ReporteGrupo;
use App\Models\Sede;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VersiculoDiario;
use Carbon\CarbonImmutable;
use Database\Seeders\CuentaInicialTenantSeeder;
use Database\Seeders\DemoComercialTenantSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Jobs\CreateDatabase;

class PoblarDemoComercialCommand extends Command
{
    protected $signature = 'tenant:poblar-demo-comercial
        {--dominio=comercial.redil.cloud : Dominio completo del tenant comercial}
        {--tenant= : Identificador opcional del tenant si se resuelve por ID}
        {--base= : Nombre exacto de la base central PostgreSQL autorizada}
        {--usuarios=2000 : Total objetivo de usuarios en la iglesia demo}
        {--iglesia=Iglesia Vida Plena : Nombre visible de la congregación comercial}
        {--crear-si-no-existe : Crear y aprovisionar la cuenta comercial si aún no existe}
        {--vence=2027-12-31 : Fecha de vencimiento YYYY-MM-DD al crear o activar la cuenta}';

    protected $description = 'Puebla de forma segura y aislada la cuenta demo comercial (comercial.redil.cloud) con ~2.000 usuarios y contenido completo.';

    public function handle(): int
    {
        // 1. Validar parámetros obligatorios y que el destino sea exclusivamente una cuenta demo comercial.
        $dominio = Str::lower(trim((string) $this->option('dominio')));
        $tenantIdOpcion = trim((string) $this->option('tenant'));
        $baseEsperada = trim((string) $this->option('base'));
        $totalUsuarios = (int) $this->option('usuarios');
        $nombreIglesia = trim((string) $this->option('iglesia')) ?: 'Iglesia Vida Plena';
        $vencimiento = trim((string) $this->option('vence'));

        if ($baseEsperada === '') {
            $this->error('Debes indicar explícitamente --base=NOMBRE_BD para confirmar el entorno objetivo.');

            return self::FAILURE;
        }

        if (! $this->esDestinoComercialValido($dominio, $tenantIdOpcion)) {
            $this->error('Por seguridad, este comando solo admite el subdominio comercial.* (ej. comercial.redil.cloud).');

            return self::FAILURE;
        }

        if ($totalUsuarios < 300 || $totalUsuarios > 10000) {
            $this->error('El parámetro --usuarios debe estar entre 300 y 10000.');

            return self::FAILURE;
        }

        $fechaVence = \DateTimeImmutable::createFromFormat('!Y-m-d', $vencimiento);
        if (! $fechaVence || $fechaVence->format('Y-m-d') !== $vencimiento || CarbonImmutable::parse($vencimiento)->endOfDay()->isPast()) {
            $this->error('Indica una fecha --vence válida en formato YYYY-MM-DD futura.');

            return self::FAILURE;
        }

        // 2. Comprobar que las conexiones central y pgsql apunten exactamente a la base PostgreSQL indicada.
        $conexionCentral = (new Tenant)->getConnection();
        if ($conexionCentral->getDriverName() !== 'pgsql'
            || (string) $conexionCentral->selectOne('select current_database() as name')->name !== $baseEsperada
            || (string) app('db')->connection('pgsql')->selectOne('select current_database() as name')->name !== $baseEsperada) {
            $this->error('Las conexiones central y pgsql no coinciden con la base indicada en --base.');

            return self::FAILURE;
        }

        if (strlen((string) config('cuenta_inicial.soporte_password')) < 12) {
            $this->error('Configura CUENTA_INICIAL_SOPORTE_PASSWORD (mínimo 12 caracteres) antes de ejecutar el comando.');

            return self::FAILURE;
        }

        // 3. Adquirir bloqueo exclusivo para impedir ejecuciones concurrentes.
        $claveLock = 'poblar-demo-comercial:'.($tenantIdOpcion !== '' ? $tenantIdOpcion : $dominio);
        $lock = Cache::lock($claveLock, 1200);
        if (! $lock->get()) {
            $this->error('Ya existe una operación en curso para esta cuenta demo comercial.');

            return self::FAILURE;
        }

        try {
            $registroDominio = Domain::query()->where('domain', $dominio)->first();
            $tenant = $tenantIdOpcion !== ''
                ? Tenant::find($tenantIdOpcion)
                : ($registroDominio ? Tenant::find($registroDominio->tenant_id) : null);

            if ($tenant && $registroDominio && $registroDominio->tenant_id !== $tenant->id) {
                $this->error('El dominio y el --tenant indicados pertenecen a registros distintos.');

                return self::FAILURE;
            }

            // 4. Si no existe y se autorizó --crear-si-no-existe, crear el registro central del tenant comercial.
            if (! $tenant) {
                if (! $this->option('crear-si-no-existe')) {
                    $this->error("No existe un tenant asociado a '{$dominio}'. Créalo desde el panel central o pasa --crear-si-no-existe.");

                    return self::FAILURE;
                }

                $nuevoId = $tenantIdOpcion !== '' ? $tenantIdOpcion : 'comercial_cloud';
                if (! preg_match('/\A[a-z0-9_]{3,40}\z/', $nuevoId)) {
                    $this->error('El identificador del tenant nuevo solo admite minúsculas, números y guion bajo.');

                    return self::FAILURE;
                }

                $esquemaNuevo = config('tenancy.database.prefix').$nuevoId.config('tenancy.database.suffix');
                $esquemaExiste = (bool) $conexionCentral
                    ->selectOne('select exists(select 1 from pg_namespace where nspname = ?) as present', [$esquemaNuevo])
                    ->present;
                if ($esquemaExiste) {
                    $this->error("El esquema PostgreSQL '{$esquemaNuevo}' ya existe; se aborta para no sobrescribirlo.");

                    return self::FAILURE;
                }

                $tenant = $conexionCentral->transaction(function () use ($nuevoId, $dominio, $nombreIglesia): Tenant {
                    $plan = Plan::query()->where('slug', 'premium')->where('activo', true)->first()
                        ?? Plan::query()->firstOrCreate(
                            ['slug' => 'redil-inicial'],
                            ['nombre' => 'REDIL inicial', 'incluye_logo' => true, 'incluye_marca_blanca' => true, 'activo' => true]
                        );

                    $creado = Tenant::create([
                        'id' => $nuevoId,
                        'onboarding_version' => 1,
                        'demo_comercial_cloud' => true,
                        'church_name' => $nombreIglesia,
                        'admin_email' => 'comercial@redil.co',
                        'plan_id' => $plan->id,
                        'status' => 'pending_review',
                        'is_suspended' => false,
                    ]);
                    $creado->domains()->create(['domain' => $dominio]);

                    return $creado;
                });

                $this->info("Tenant comercial '{$tenant->id}' registrado con dominio '{$dominio}'.");
            }

            if ((int) $tenant->demo_comercial_version >= DemoComercialTenantSeeder::VERSION) {
                $this->info("La cuenta '{$tenant->id}' ({$dominio}) ya tiene instalada la versión ".DemoComercialTenantSeeder::VERSION.' de la demo comercial.');

                return self::SUCCESS;
            }

            // 5. Si la cuenta aún está pendiente de aprovisionamiento base, crear esquema, migrar y sembrar CuentaInicialTenantSeeder.
            if (! $tenant->provisioned_at && in_array($tenant->status, ['pending_review', 'setup_failed'], true)) {
                $this->info("Ejecutando migraciones y plantilla inicial para '{$tenant->id}'...");
                if (! $tenant->database()->manager()->databaseExists($tenant->database()->getName())) {
                    (new CreateDatabase($tenant))->handle(app(DatabaseManager::class));
                }
                if ($this->call('tenants:migrate', ['--tenants' => [$tenant->id], '--force' => true, '--no-interaction' => true]) !== 0) {
                    $this->error('La migración del esquema comercial no terminó correctamente.');

                    return self::FAILURE;
                }
                $tenant->run(fn () => app(CuentaInicialTenantSeeder::class)->setCommand($this)->__invoke());
                $tenant->refresh();
                $tenant->update(['provisioned_at' => now()]);
            }

            $tenant->update(['demo_comercial_cloud' => true]);

            // 6. Ejecutar DemoComercialTenantSeeder exclusivamente dentro del contexto del tenant comercial.
            $this->info("Sembrando estructura comercial de {$totalUsuarios} usuarios y módulos completos en '{$tenant->id}'...");
            $resumen = [];
            $tenant->run(function () use ($totalUsuarios, $nombreIglesia, &$resumen): void {
                $seeder = app(DemoComercialTenantSeeder::class);
                $seeder->setCommand($this);
                $seeder->run($totalUsuarios, $nombreIglesia);

                $resumen = [
                    ['Usuarios totales', User::count()],
                    ['Sedes activas', Sede::count()],
                    ['Grupos (6 niveles jerárquicos)', Grupo::count()],
                    ['Escuelas (Manantial de Vida Eterna)', Escuela::count()],
                    ['Materias con plantillas y prerrequisitos', Materia::count()],
                    ['Horarios base configurados', HorarioBase::count()],
                    ['Periodos académicos (sin periodos)', Periodo::count()],
                    ['Actividades publicadas', Actividad::count()],
                    ['Planes lectores activos', PlanLector::count()],
                    ['Versículos diarios programados', VersiculoDiario::count()],
                    ['Reportes de grupos celulares', ReporteGrupo::count()],
                    ['Cursos LMS publicados', Curso::count()],
                    ['Hitos congregacionales', Hito::count()],
                ];
            });

            if ($tenant->status !== 'active') {
                $tenant->update([
                    'status' => 'active',
                    'is_suspended' => false,
                    'license_starts_at' => now(),
                    'license_ends_at' => $vencimiento,
                    'grace_ends_at' => CarbonImmutable::parse($vencimiento)->addDays(7),
                    'approved_at' => now(),
                ]);
            }

            $this->table(['Módulo / Entidad', 'Registros Creados'], $resumen);
            $this->info("Demo comercial lista y activa en {$dominio} (tenant: {$tenant->id}).");

            return self::SUCCESS;
        } finally {
            $lock->release();
        }
    }

    /**
     * Garantiza que el comando nunca pueda apuntar a una iglesia real distinta de comercial.*.
     */
    private function esDestinoComercialValido(string $dominio, string $tenantId): bool
    {
        $dominioValido = $dominio === 'comercial' || str_starts_with($dominio, 'comercial.');
        if (! $dominioValido) {
            return false;
        }

        if ($tenantId !== '' && ! str_starts_with(Str::lower($tenantId), 'comercial')
            && ! str_starts_with(Str::lower($tenantId), 'cuenta_inicial_demo')
            && ! preg_match('/\A[0-9a-f-]{36}\z/i', $tenantId)) {
            return false;
        }

        return true;
    }
}
