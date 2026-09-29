<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use App\Models\Grupo;
use App\Models\Iglesia;
use App\Models\Sede;
use App\Models\TipoGrupo;
use App\Models\TipoUsuario;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class NuevoTenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = tenant();
        if (! $tenant || (int) $tenant->onboarding_version !== 1 || $tenant->provisioned_at) {
            throw new \LogicException('Este seeder solo admite altas del formulario pendientes de aprovisionamiento.');
        }

        $this->call([
            TipoSedeSeeder::class, EstadoCivilSeeder::class, TipoIdentificacionSeeder::class,
            TipoVinculacionSeeder::class, TipoParentescoSeeder::class, OcupacionSeeder::class,
            NivelAcademicoSeeder::class, EstadoNivelAcademicoSeeder::class, ProfesionSeeder::class,
            TipoSangreSeeder::class, SectorEconomicoSeeder::class, TipoViviendaSeeder::class,
        ]);

        Configuracion::firstOrCreate(['id' => 1], [
            'nombre_app_personalizado' => $tenant->church_name, 'ruta_almacenamiento' => 'iglesia',
            'maximos_niveles_grafico_ministerio' => 4, 'correo_por_defecto' => false,
            'identificacion_obligatoria' => true, 'edad_minima_logueo' => 14,
            'enviar_correo_bienvenida_nuevo_asistente' => false,
        ]);
        $this->callWith(PermisoSeeder::class, ['importarJson' => false]);
        $superAdmin = Role::findByName('Super Administrador Prueba', 'web');
        $superAdmin->syncPermissions(Permission::query()->where('guard_name', 'web')->get());
        $pastorRole = Role::findByName('Pastor Prueba', 'web');
        $pastorRole->update(['dependiente' => true]);

        $tipoAdmin = TipoUsuario::firstOrCreate(['nombre' => 'Administrador'], [
            'nombre_plural' => 'Administradores', 'icono' => 'ti ti-key',
            'id_rol_dependiente' => $superAdmin->id, 'visible' => false,
        ]);
        $tipoPastor = TipoUsuario::firstOrCreate(['nombre' => 'Pastor principal'], [
            'nombre_plural' => 'Pastores principales', 'icono' => 'ti ti-user-shield',
            'tipo_pastor' => true, 'tipo_pastor_principal' => true, 'id_rol_dependiente' => $pastorRole->id,
        ]);
        TipoUsuario::firstOrCreate(['nombre' => 'Miembro'], [
            'nombre_plural' => 'Miembros', 'icono' => 'ti ti-user', 'default' => true,
            'id_rol_dependiente' => Role::findByName('Oveja Prueba', 'web')->id,
        ]);

        foreach (['Iglesia', 'Ministerio', 'Zona', 'Grupo'] as $orden => $nombre) {
            TipoGrupo::firstOrCreate(['nombre' => $nombre], [
                'orden' => $orden + 1, 'posible_grupo_sede' => $orden === 0,
                'nombre_plural' => $nombre, 'seguimiento_actividad' => true,
            ]);
        }
        $sede = Sede::withoutEvents(fn () => Sede::firstOrCreate(['default' => true], [
            'nombre' => 'Sede principal', 'tipo_sede_id' => 1, 'grupo_id' => null, 'foto' => 'default.png',
        ]));
        $grupo = Grupo::withoutEvents(fn () => Grupo::firstOrCreate(['codigo' => 'PRINCIPAL'], [
            'nombre' => 'Grupo principal', 'tipo_grupo_id' => TipoGrupo::where('nombre', 'Iglesia')->value('id'),
            'nivel' => 1, 'dado_baja' => false, 'inactivo' => false, 'sede_id' => $sede->id,
        ]));
        $sede->update(['grupo_id' => $grupo->id]);
        $iglesia = Iglesia::firstOrCreate(['id' => 1], [
            'nombre' => $tenant->church_name, 'email_soporte' => $tenant->admin_email,
        ]);

        $cuentas = [
            ['email' => 'soporte@example.invalid', 'nombre' => 'Soporte REDIL', 'identificacion' => 'EJEMPLO-REDIL', 'pastor' => false],
            ['email' => 'pastor1@example.invalid', 'nombre' => 'Pastor principal 1', 'identificacion' => 'EJEMPLO-PASTOR-1', 'pastor' => true],
            ['email' => 'pastor2@example.invalid', 'nombre' => 'Pastor principal 2', 'identificacion' => 'EJEMPLO-PASTOR-2', 'pastor' => true],
            ['email' => $tenant->admin_email, 'nombre' => 'Administrador iglesia', 'identificacion' => 'EJEMPLO-ADMIN', 'pastor' => false],
        ];
        $ids = [];
        foreach ($cuentas as $datos) {
            $usuario = User::withoutEvents(fn () => User::firstOrCreate(['email' => $datos['email']], [
                'primer_nombre' => $datos['nombre'], 'primer_apellido' => 'Actualizar en entrega',
                'identificacion' => $datos['identificacion'], 'password' => Hash::make(Str::random(64)),
                'email_verified_at' => null, 'activo' => true, 'esta_aprobado' => true,
                'genero' => 0, 'foto' => 'default-m.png', 'sede_id' => $sede->id,
                'tipo_usuario_id' => $datos['pastor'] ? $tipoPastor->id : $tipoAdmin->id,
                'informacion_opcional' => 'Cuenta inicial: sustituir datos de ejemplo durante la entrega.',
            ]));
            $rol = $datos['pastor'] ? $pastorRole : $superAdmin;
            $usuario->roles()->sync([$rol->id => ['activo' => true, 'dependiente' => $datos['pastor'], 'model_type' => User::class]]);
            if ($datos['pastor']) {
                $grupo->encargados()->syncWithoutDetaching([$usuario->id]);
                $grupo->asistentes()->syncWithoutDetaching([$usuario->id]);
                $iglesia->pastoresEncargados()->syncWithoutDetaching([$usuario->id]);
            }
            $ids[] = $usuario->id;
        }
        $tenant->update(['initial_user_ids' => $ids]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
