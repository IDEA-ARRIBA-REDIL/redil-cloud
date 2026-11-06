<?php

namespace Database\Seeders;

use App\Models\CampoExtra;
use App\Models\CampoExtraGrupo;
use App\Models\CampoFormularioUsuario;
use App\Models\CampoPerfilUsuario;
use App\Models\Configuracion;
use App\Models\EstacionSalonInfantil;
use App\Models\FormularioUsuario;
use App\Models\Grupo;
use App\Models\Iglesia;
use App\Models\Informe;
use App\Models\LoginPersonalizacion;
use App\Models\Role;
use App\Models\SalonInfantil;
use App\Models\SeccionFormularioUsuario;
use App\Models\Sede;
use App\Models\Tenant;
use App\Models\ThemeSetting;
use App\Models\TipoGrupo;
use App\Models\TipoPago;
use App\Models\TipoUsuario;
use App\Models\User;
use App\Models\Zona;
use App\Services\PermisosCuentaInicial;
use App\Services\ThemeService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class CuentaInicialTenantSeeder extends Seeder
{
    use WithoutModelEvents;

    public const VERSION = 4;

    public function run(): void
    {
        $tenant = tenant();
        $connection = (new User)->getConnection();
        if (! $tenant instanceof Tenant || ! tenancy()->initialized
            || $connection->getName() !== 'tenant' || $connection->getDriverName() !== 'pgsql') {
            throw new \LogicException('La plantilla exige un tenant PostgreSQL inicializado.');
        }
        $schema = $connection->selectOne('select current_schema() as name')->name;
        if ($schema === 'public' || $schema !== $tenant->database()->getName()) {
            throw new \LogicException('El esquema activo no corresponde al tenant.');
        }
        if ((int) $tenant->cuenta_inicial_version > 0) {
            $this->command?->info('La plantilla ya está instalada; se conservan los datos y permisos actuales.');

            return;
        }
        if ((int) $tenant->onboarding_version !== 1 || $tenant->provisioned_at
            || User::withTrashed()->exists() || Role::exists() || Sede::exists() || Configuracion::exists()) {
            throw new \LogicException('La plantilla solo admite cuentas nuevas sin datos.');
        }
        $password = config('cuenta_inicial.soporte_password');
        if (! is_string($password) || strlen($password) < 12) {
            throw new \LogicException('Configura CUENTA_INICIAL_SOPORTE_PASSWORD con al menos 12 caracteres antes de crear una cuenta.');
        }
        $ids = $connection->transaction(function () use ($tenant): array {
            $this->sincronizarSecuencias();
            $this->geografia();
            $this->catalogos();
            $this->rolesYTipos();
            $this->estructura($tenant);
            $this->formularios();
            $this->informesEInfantil();
            $this->configuracionVisual();
            $ids = $this->usuarios($tenant);
            $this->sincronizarSecuencias();

            return $ids;
        });
        $tenant->update(['initial_user_ids' => $ids, 'cuenta_inicial_version' => self::VERSION]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function geografia(): void
    {
        $connection = (new User)->getConnection();
        foreach (['continentes', 'paises', 'regiones', 'departamentos', 'municipios', 'localidades', 'barrios', 'tipoPeticiones'] as $name) {
            $path = database_path('seeders/data/cuenta-inicial/'.$name.'.sql');
            if (! is_file($path)) {
                throw new \RuntimeException('Falta el catálogo de la plantilla: '.$name);
            }
            $connection->unprepared(file_get_contents($path));
        }
        $codes = json_decode(file_get_contents(database_path('seeders/data/cuenta-inicial/codigos-paises.json')), true, flags: JSON_THROW_ON_ERROR);
        foreach ($codes as $country) {
            \App\Models\Pais::where('nombre', $country['nombre'])->update(['codigo_alpha' => $country['codigo']]);
        }
    }

    private function catalogos(): void
    {
        $this->call([
            EntidadRelacionadaSeeder::class, TipoSedeSeeder::class, EstadoCivilSeeder::class,
            TipoIdentificacionSeeder::class, TipoVinculacionSeeder::class, TipoParentescoSeeder::class,
            OcupacionSeeder::class, NivelAcademicoSeeder::class, EstadoNivelAcademicoSeeder::class,
            ProfesionSeeder::class, TipoSangreSeeder::class, SectorEconomicoSeeder::class,
            TipoViviendaSeeder::class, TipoFormatoDireccionSeeder::class,
            EstadoPasoCrecimientoUsuarioSeeder::class, TipoAsignacionSeeder::class,
            TipoBajaAltaSeeder::class, TipoServicioGrupoSeeder::class, TipoActividadSeeder::class,
            TipoCargoActividadSeeder::class, TipoElementoFormularioActividadSeeder::class,
            TipoFormularioUsuarioSeeder::class, TipoInformeSeeder::class, TipoItemSeeder::class,
            TipoCampoTiempoConDiosSeeder::class,
            TipoCargoCursoSeeder::class, CursoItemTipoSeeder::class, TipoAulaSeeder::class,
            TipoConsejeriaSeeder::class, TipoNotificacionSeeder::class, TipoHitoSeeder::class,
            TipoServiciosSeeder::class, EstadoTareaConsolidacionSeeder::class,
            MotivoInasistenciaSeeder::class, TipoInasistenciaSeeder::class,
            MotivoNoReporteGrupoSeeder::class, MotivoDesaprobacionReporteGrupoSeeder::class,
            MonedaSeeder::class, SistemaCalificacionSeeder::class, CalificacionesSeeder::class,
            CarreraSeeder::class, SeccionPasoCrecimientoSeeder::class, PasoCrecimientoSeeder::class,
            TipoSeccionRvSeeder::class, SeccionRvSeeder::class, ConfiguracionRvSeeder::class,
            PlanLectorCategoriaSeeder::class, PlanLectorTipoContenidoSeeder::class,
            CampoInformeExcelSeeder::class,
        ]);
        \App\Models\Calificaciones::where('id', 2)->update(['nota_minima' => 40.1]);
        foreach (['Efectivo', 'Transferencia bancaria'] as $index => $name) {
            $payment = TipoPago::create([
                'nombre' => $name, 'activo' => true, 'habilitado_punto_pago' => true,
                'habilitado_donacion' => false, 'key_reservada' => $index === 0 ? 'efectivo' : 'transferencia',
                'subir_archivo_pagos' => $index === 1,
            ]);
            foreach (['Pendiente', 'Finalizado', 'Anulado'] as $state => $label) {
                \App\Models\EstadoPago::create([
                    'nombre' => $label, 'tipo_pago_id' => $payment->id,
                    'color' => ['#f39c12', '#00a65a', '#dd4b39'][$state],
                    'estado_inicial_defecto' => $state === 0, 'estado_final_inscripcion' => $state === 1,
                    'estado_anulado_inscripcion' => $state === 2, 'estado_pendiente' => $state === 0,
                ]);
            }
        }
        foreach (['Diezmo', 'Ofrenda'] as $name) {
            \App\Models\TipoOfrenda::create(['nombre' => $name, 'descripcion' => $name, 'generica' => true]);
        }
        $this->call(TipoEgresoSeeder::class);
    }

    private function rolesYTipos(): void
    {
        $this->callWith(PermisoSeeder::class, ['importarJson' => false]);
        $names = [
            'Super Administrador Prueba' => PermisosCuentaInicial::SUPERADMINISTRADOR,
            'Pastor Prueba' => 'Pastor principal', 'Lider Prueba' => 'Líder',
            'Oveja Prueba' => 'Miembro', 'Nuevo Prueba' => 'Nuevo', 'Alumno Prueba' => 'Alumno',
            'Maestro Prueba' => 'Maestro', 'Administrativo Prueba' => 'Administrativo',
            'Consejero Prueba' => 'Consejero', 'Consolidador Medellin Prueba' => 'Consolidador',
            'Consolidador Bogota Prueba' => 'Conéctate', 'Cajero PDP Prueba' => 'Cajero punto de pago',
            'Intercesor Prueba' => 'Intercesor',
        ];
        foreach ($names as $old => $new) {
            Role::where('name', $old)->update(['name' => $new, 'guard_name' => 'web']);
        }
        foreach (['Punto de pago', PermisosCuentaInicial::ADMINISTRADOR] as $name) {
            Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        Permission::firstOrCreate(['name' => PermisosCuentaInicial::NOTIFICACIONES, 'guard_name' => 'web'], [
            'titulo' => 'subitem_notificaciones', 'descripcion' => 'Administrar tipos de notificaciones.',
        ]);
        foreach (['Nuevo', 'Miembro', 'Líder', 'Pastor principal', 'Administrador iglesia', 'Desarrollador'] as $order => $name) {
            $roleName = $name === 'Desarrollador' ? PermisosCuentaInicial::SUPERADMINISTRADOR : $name;
            $role = Role::findByName($roleName, 'web');
            $role->update(['dependiente' => true]);
            TipoUsuario::create([
                'nombre' => $name, 'nombre_plural' => $name, 'id_rol_dependiente' => $role->id,
                'orden' => $order + 1, 'default' => $name === 'Nuevo',
                'tipo_pastor' => $name === 'Pastor principal', 'tipo_pastor_principal' => $name === 'Pastor principal',
                'visible' => ! in_array($name, ['Desarrollador', 'Administrador iglesia'], true),
                'es_miembro_oficial' => in_array($name, ['Miembro', 'Líder', 'Pastor principal'], true),
                'icono' => 'ti ti-user', 'color' => '#32700A', 'imagen' => 'indicador_general.png',
            ]);
        }
        foreach (['Maestro' => 'es_maestro', 'Consejero' => 'es_consejero', 'Intercesor' => 'es_intercesor',
            'Punto de pago' => 'es_encargado_pdp', 'Cajero punto de pago' => 'es_cajero_pdp'] as $name => $flag) {
            Role::where('name', $name)->update([$flag => true]);
        }
        $this->rolesYTiposImportados();
        $all = Permission::where('guard_name', 'web')->get();
        $this->role(PermisosCuentaInicial::SUPERADMINISTRADOR)->syncPermissions($all);
        $adminPermissions = $all->reject(fn (Permission $p): bool => PermisosCuentaInicial::reservado($p->name)
            || in_array($p->name, ['configuraciones.subitem_roles', 'configuraciones.subitem_tipo_de_usuarios', 'personas.ver_panel_asignar_tipo_usuario'], true));
        $admin = $this->role(PermisosCuentaInicial::ADMINISTRADOR);
        $admin->syncPermissions($adminPermissions);
        $admin->tipoUsuariosBloqueados()->sync(TipoUsuario::where('nombre', 'Desarrollador')->pluck('id'));
    }

    private function rolesYTiposImportados(): void
    {
        $data = json_decode(file_get_contents(database_path('seeders/data/cuenta-inicial/roles-tipos.json')), true, flags: JSON_THROW_ON_ERROR);
        $aliases = ['Súper Administrador' => PermisosCuentaInicial::SUPERADMINISTRADOR,
            'Desarrollador' => PermisosCuentaInicial::SUPERADMINISTRADOR, 'Administrador 2' => PermisosCuentaInicial::ADMINISTRADOR,
            'Punto de Pago' => 'Punto de pago', 'Nuevo y Amigo' => 'Nuevo', 'Apóstol' => 'Pastor principal'];
        $map = [];
        foreach ($data['roles'] as $entry) {
            $name = $aliases[$entry['nombre']] ?? $entry['nombre'];
            $role = Role::firstOrCreate(['name' => $name, 'guard_name' => 'web'], ['dependiente' => $entry['dependiente']]);
            $map[$entry['id_origen']] = $role->id;
            if (! in_array($name, [PermisosCuentaInicial::SUPERADMINISTRADOR, PermisosCuentaInicial::ADMINISTRADOR], true)) {
                $permissions = Permission::whereIn('titulo', $entry['permisos'])->get();
                $role->givePermissionTo($permissions);
            }
        }
        foreach ($data['tipos'] as $entry) {
            $roleId = $map[$entry['id_tipo_usuario_dependiente']] ?? null;
            if (! $roleId) {
                throw new \LogicException('Tipo de usuario sin rol base.');
            }
            unset($entry['id_tipo_usuario_dependiente']);
            $entry['nombre'] = $entry['tipo_pastor_principal'] ? 'Pastor principal' : trim($entry['nombre']);
            TipoUsuario::updateOrCreate(['nombre' => $entry['nombre']], $entry + [
                'id_rol_dependiente' => $roleId, 'icono' => 'ti ti-user', 'imagen' => 'indicador_general.png',
            ]);
        }
    }

    private function estructura(Tenant $tenant): void
    {
        Configuracion::create([
            'id' => 1, 'nombre_app_personalizado' => $tenant->church_name, 'ruta_almacenamiento' => 'iglesia',
            'maximos_niveles_grafico_ministerio' => 4, 'correo_por_defecto' => false,
            'identificacion_obligatoria' => true, 'edad_minima_logueo' => 14,
            'enviar_correo_bienvenida_nuevo_asistente' => false, 'usa_listas_geograficas' => true,
            'limite_menor_edad' => 18, 'moneda_predeterminada_punto_pago' => \App\Models\Moneda::where('nombre_corto', 'COP')->value('id'),
            'habilitar_salones_con_estaciones' => true, 'nombre_creador' => 'REDIL',
        ]);
        $this->call(RangoEdadSeeder::class);
        foreach (['Grupo principal', 'Ministerio', 'Zona', 'Grupo de crecimiento'] as $order => $name) {
            TipoGrupo::create([
                'nombre' => $name, 'nombre_plural' => $name, 'orden' => $order + 1,
                'posible_grupo_sede' => $order === 0, 'seguimiento_actividad' => $order === 3,
                'estado' => true,
            ]);
        }
        $groupTypes = json_decode(file_get_contents(database_path('seeders/data/cuenta-inicial/tipos-grupo.json')), true, flags: JSON_THROW_ON_ERROR);
        foreach ($groupTypes as $type) {
            $type['nombre'] = trim($type['nombre']);
            if (Str::lower($type['nombre']) === 'grupo principal') {
                $type['nombre'] = 'Grupo principal';
            }
            TipoGrupo::updateOrCreate(['nombre' => $type['nombre']], $type + ['estado' => true]);
        }
        $sede = Sede::create(['nombre' => 'Sede principal', 'tipo_sede_id' => 1, 'default' => true, 'foto' => 'default.png']);
        $grupo = Grupo::create([
            'nombre' => 'Grupo principal', 'codigo' => 'PRINCIPAL', 'tipo_grupo_id' => TipoGrupo::where('nombre', 'Grupo principal')->value('id'),
            'nivel' => 1, 'dado_baja' => false, 'inactivo' => false, 'sede_id' => $sede->id,
        ]);
        $sede->update(['grupo_id' => $grupo->id]);
        Iglesia::create(['nombre' => $tenant->church_name, 'email_soporte' => $tenant->admin_email]);
        $zona = Zona::create(['nombre' => 'Zona principal']);
        $zona->sedes()->sync([$sede->id]);
        foreach (['Menores' => [0, 17], 'Adultos' => [18, 120]] as $name => [$min, $max]) {
            $classification = \App\Models\ClasificacionAsistente::create(['nombre' => $name]);
            $classification->tipoUsuarios()->sync(TipoUsuario::where('visible', true)->pluck('id')
                ->mapWithKeys(fn ($id): array => [$id => ['edad_minima' => $min, 'edad_maxima' => $max]])->all());
            $classification->tipoGrupos()->sync(TipoGrupo::pluck('id'));
        }
        foreach (TipoGrupo::all() as $type) {
            $type->tiposOfrendas()->sync(\App\Models\TipoOfrenda::pluck('id'));
            $type->tipoUsuariosPermitidos()->sync(TipoUsuario::where('visible', true)->pluck('id')
                ->mapWithKeys(fn ($id): array => [$id => ['para_encargados' => true, 'para_asistentes' => true]])->all());
        }
        foreach ([PermisosCuentaInicial::SUPERADMINISTRADOR, PermisosCuentaInicial::ADMINISTRADOR, 'Pastor principal', 'Líder'] as $name) {
            $role = $this->role($name);
            $role->privilegiosTiposGrupo()->sync(TipoGrupo::all()->mapWithKeys(fn ($type): array => [$type->id => [
                'asignar_asistente' => true, 'desvincular_asistente' => true,
                'asignar_encargado' => true, 'desvincular_encargado' => true,
            ]])->all());
            $role->pasosCrecimiento()->sync(\App\Models\PasoCrecimiento::pluck('id'));
        }
    }

    private function formularios(): void
    {
        $forms = [
            ['Nuevo adulto', 1, 18, 120], ['Modificar adulto', 2, 18, 120],
            ['Modificar menor', 2, 0, 17], ['Nuevo menor interno', 1, 0, 17],
            ['Inscripción de adultos', 3, 18, 120], ['Registrar hijo', 4, 0, 17],
            ['Mi perfil', 5, 0, 120],
        ];
        foreach ($forms as $index => [$name, $type, $min, $max]) {
            $form = FormularioUsuario::create([
                'id' => $index + 1, 'nombre' => $name, 'titulo' => $name, 'label' => $name,
                'tipo_formulario_id' => $type, 'edad_minima' => $min, 'edad_maxima' => $max,
                'tipo_usuario_default_id' => TipoUsuario::where('default', true)->value('id'),
                'sede_default_id' => Sede::where('default', true)->value('id'),
                'visible_terminos_condiciones' => false,
            ]);
            $form->roles()->sync(Role::pluck('id'));
        }
        $this->call([SeccionFormularioUsuarioSeeder::class, CampoFormularioUsuarioSeeder::class, CampoPerfilUsuarioSeeder::class]);
        foreach (CampoFormularioUsuario::where('es_campo_extra', true)->get() as $field) {
            $field->secciones()->detach();
            $field->forceDelete();
        }
        $section = SeccionFormularioUsuario::create(['nombre' => 'Datos personales del menor', 'titulo' => 'Datos personales', 'orden' => 1, 'formulario_usuario_id' => 4]);
        $source = SeccionFormularioUsuario::where('formulario_usuario_id', 3)->firstOrFail();
        foreach ($source->campos as $field) {
            $section->campos()->syncWithoutDetaching([$field->id => ['requerido' => $field->pivot->requerido, 'class' => $field->pivot->class, 'orden' => $field->pivot->orden]]);
        }
        SeccionFormularioUsuario::where('nombre', 'like', 'Sección%')->update(['nombre' => 'Información personal', 'titulo' => 'Información personal']);
        $extra = CampoExtra::create(['nombre' => 'Observaciones adicionales', 'placeholder' => 'Información adicional', 'tipo_de_campo' => 2,
            'required' => false, 'class_col' => 'col-12', 'class_id' => 'observaciones_adicionales', 'visible' => true]);
        CampoExtraGrupo::create(['nombre' => 'Observaciones adicionales', 'tipo_de_campo' => 2, 'required' => false,
            'class_col' => 'col-12', 'class_id' => 'observaciones_adicionales', 'visible' => true]);
        foreach (Role::all() as $role) {
            $role->camposPerfil()->sync(CampoPerfilUsuario::pluck('id')->mapWithKeys(fn ($id): array => [$id => ['requerido' => false]])->all());
            $role->camposExtraAutogestion()->sync([$extra->id => ['requerido' => false]]);
        }
        foreach (FormularioUsuario::all() as $form) {
            (new User)->getConnection()->table('campos_extras_formularios')->insert([
                'campo_extra_id' => $extra->id, 'formulario_id' => $form->id, 'visible' => true, 'required' => false,
            ]);
        }
    }

    private function informesEInfantil(): void
    {
        $this->call(InformeSeeder::class);
        foreach (Informe::all() as $informe) {
            $roles = [$this->role(PermisosCuentaInicial::SUPERADMINISTRADOR)->id];
            if (! $informe->usa_plantilla) {
                $roles[] = $this->role(PermisosCuentaInicial::ADMINISTRADOR)->id;
                $roles[] = $this->role('Pastor principal')->id;
            }
            $informe->roles()->sync($roles);
        }
        $template = Informe::where('usa_plantilla', true)->firstOrFail();
        $section = $template->secciones()->create(['nombre' => 'Resumen de personas', 'orden' => 1]);
        $subsection = $section->subsecciones()->create(['nombre' => 'Personas activas', 'orden' => 1]);
        $subsection->items()->create(['nombre' => 'Total de personas activas', 'orden' => 1, 'grupo_de_personas' => 2]);
        $template->bloques()->create(['nombre' => 'Sede principal', 'ids_sedes' => (string) Sede::where('default', true)->value('id')]);
        $legacy = \App\Models\InformePersonalizado::create([
            'nombre' => 'Informe de asistencia de encargados', 'tipo_informe_id' => 1,
            'link' => 'informes-personalizados.obreros.show', 'activo' => true, 'add_id_a_la_url' => true,
        ]);
        $legacy->tiposUsuarios()->sync(TipoUsuario::whereIn('nombre', ['Pastor principal', 'Administrador iglesia', 'Desarrollador'])->pluck('id'));
        $general = EstacionSalonInfantil::create(['nombre' => 'General']);
        $panales = EstacionSalonInfantil::create(['nombre' => 'Cambio de pañales']);
        foreach (['Bebés (0–2 años)', 'Párvulos (3–4 años)', 'Preescolar (5–6 años)', 'Primaria (7–12 años)'] as $index => $name) {
            $salon = SalonInfantil::create(['nombre' => $name, 'activo' => true]);
            $salon->estaciones()->sync($index < 2 ? [$general->id, $panales->id] : [$general->id]);
        }
    }

    /** @return list<int> */
    private function usuarios(Tenant $tenant): array
    {
        $accounts = [
            ['Soporte REDIL', config('cuenta_inicial.soporte_email'), 'Desarrollador', PermisosCuentaInicial::SUPERADMINISTRADOR],
            ['Pastor principal 1', 'pastor1@example.invalid', 'Pastor principal', 'Pastor principal'],
            ['Pastor principal 2', 'pastor2@example.invalid', 'Pastor principal', 'Pastor principal'],
            ['Administrador iglesia', $tenant->admin_email, 'Administrador iglesia', PermisosCuentaInicial::ADMINISTRADOR],
        ];
        $ids = [];
        foreach ($accounts as $index => [$name, $email, $type, $roleName]) {
            $role = $this->role($roleName);
            $user = User::create([
                'primer_nombre' => $name, 'primer_apellido' => 'Por configurar', 'email' => $email,
                'identificacion' => (string) (9000000000 + $index),
                'password' => Hash::make($index === 0 ? config('cuenta_inicial.soporte_password') : Str::random(64)),
                'activo' => true, 'esta_aprobado' => true, 'email_verified_at' => $index === 0 ? now() : null, 'genero' => 0,
                'foto' => 'default-m.png', 'sede_id' => Sede::where('default', true)->value('id'),
                'tipo_usuario_id' => TipoUsuario::where('nombre', $type)->value('id'),
            ]);
            $user->roles()->sync([$role->id => ['activo' => true, 'dependiente' => true, 'model_type' => User::class]]);
            if ($type === 'Pastor principal') {
                $grupo = Grupo::where('codigo', 'PRINCIPAL')->firstOrFail();
                $grupo->encargados()->syncWithoutDetaching([$user->id]);
                $grupo->asistentes()->syncWithoutDetaching([$user->id]);
                Iglesia::firstOrFail()->pastoresEncargados()->syncWithoutDetaching([$user->id]);
            }
            $ids[] = $user->id;
        }

        return $ids;
    }

    private function configuracionVisual(): void
    {
        foreach (config('dashboard_theme') as $setting) {
            ThemeSetting::query()->firstOrCreate(['category' => 'inicio', 'class' => $setting['class']], [...$setting, 'is_active' => true]);
        }
        $settings = json_decode(file_get_contents(database_path('seeders/data/cuenta-inicial/tema.json')), true, flags: JSON_THROW_ON_ERROR);
        foreach ($settings as $setting) {
            ThemeSetting::query()->create($setting);
        }

        $configuracion = Configuracion::query()->firstOrFail();
        $configuracion->update(config('cuenta_inicial.marca'));
        $prefix = trim((string) $configuracion->ruta_almacenamiento, '/');
        foreach (array_unique([$configuracion->logo_app, $configuracion->logo_app_negro, $configuracion->favicon_app,
            $configuracion->logo_login, $configuracion->logo_menu_general, $configuracion->logo_menu_escuelas]) as $filename) {
            $path = 'img/branding/'.$filename;
            $this->copiarImagen($filename, $path, config('filesystems.default'));
            $this->copiarImagen($filename, $path, 'public');
            if ($prefix !== '') {
                $this->copiarImagen($filename, $prefix.'/'.$path, 'public');
            }
        }

        $background = config('cuenta_inicial.login.fondo');
        $backgroundPath = 'img/login-branding/left/'.$background;
        $this->copiarImagen($background, $backgroundPath, 'public');
        $login = LoginPersonalizacion::query()->create([
            'left_image_path' => $backgroundPath,
            'carousel_interval_ms' => config('cuenta_inicial.login.intervalo_ms'),
        ]);
        foreach (config('cuenta_inicial.login.banners') as $index => $filename) {
            $path = 'img/login-branding/slides/'.$filename;
            $this->copiarImagen($filename, $path, 'public');
            $login->imagenes()->create([
                'image_path' => $path, 'alt_text' => 'Software REDIL — '.($index + 1),
                'sort_order' => $index + 1, 'is_active' => true,
            ]);
        }
        if (! app(ThemeService::class)->updateScssFile()) {
            throw new \RuntimeException('No fue posible generar el CSS inicial del tenant.');
        }
    }

    private function copiarImagen(string $filename, string $destination, string $disk): void
    {
        $source = database_path('seeders/data/cuenta-inicial/imagenes/'.$filename);
        if (! is_file($source) || ! Storage::disk($disk)->put($destination, file_get_contents($source))) {
            throw new \RuntimeException('No fue posible instalar la imagen inicial: '.$filename);
        }
    }

    private function role(string $name): Role
    {
        return Role::where('name', $name)->where('guard_name', 'web')->firstOrFail();
    }

    private function sincronizarSecuencias(): void
    {
        $connection = (new User)->getConnection();
        $columns = $connection->select("select table_name, column_name from information_schema.columns where table_schema = current_schema() and column_default like 'nextval(%'");
        $grammar = $connection->getQueryGrammar();
        foreach ($columns as $column) {
            $table = $grammar->wrapTable($column->table_name);
            $field = $grammar->wrap($column->column_name);
            $connection->selectOne("select setval(pg_get_serial_sequence(?, ?), greatest(coalesce(max({$field}), 1), 1), count(*) > 0) from {$table}", [$column->table_name, $column->column_name]);
        }
    }
}
