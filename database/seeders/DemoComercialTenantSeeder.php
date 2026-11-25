<?php

namespace Database\Seeders;

use App\Models\Actividad;
use App\Models\ActividadCategoria;
use App\Models\AsesorPdp;
use App\Models\Aula;
use App\Models\BannerGeneral;
use App\Models\BloqueDashboardConsolidacion;
use App\Models\Caja;
use App\Models\CampoSeccionRv;
use App\Models\CampoTiempoConDios;
use App\Models\Carrera;
use App\Models\CategoriaTema;
use App\Models\ClasificacionAsistente;
use App\Models\Compra;
use App\Models\Configuracion;
use App\Models\Consejero;
use App\Models\CorteEscuela;
use App\Models\Curso;
use App\Models\CursoEvaluacion;
use App\Models\CursoItem;
use App\Models\CursoItemTipo;
use App\Models\CursoLeccion;
use App\Models\CursoModulo;
use App\Models\Destinatario;
use App\Models\ElementoFormularioActividad;
use App\Models\Escuela;
use App\Models\EstadoPago;
use App\Models\Grupo;
use App\Models\HabitosRv;
use App\Models\Hito;
use App\Models\HorarioBase;
use App\Models\Iglesia;
use App\Models\Inscripcion;
use App\Models\Intercesor;
use App\Models\ItemPlantilla;
use App\Models\Maestro;
use App\Models\Materia;
use App\Models\Metas;
use App\Models\Moneda;
use App\Models\Municipio;
use App\Models\Ofrenda;
use App\Models\Pago;
use App\Models\Pais;
use App\Models\PasoCrecimiento;
use App\Models\Peticion;
use App\Models\PlanLector;
use App\Models\PlanLectorCategoria;
use App\Models\PlanLectorContenido;
use App\Models\PlanLectorDia;
use App\Models\PlanLectorTipoContenido;
use App\Models\Post;
use App\Models\PuntoDePago;
use App\Models\RangoEdad;
use App\Models\ReporteGrupo;
use App\Models\ReporteReunion;
use App\Models\Reunion;
use App\Models\Role;
use App\Models\RuedaDeLaVidaUser;
use App\Models\SeccionTiempoConDios;
use App\Models\Sede;
use App\Models\TagGeneral;
use App\Models\TareaConsolidacion;
use App\Models\Tema;
use App\Models\Tenant;
use App\Models\TiempoConDios;
use App\Models\TipoActividad;
use App\Models\TipoAula;
use App\Models\TipoCargoCurso;
use App\Models\TipoConsejeria;
use App\Models\TipoGrupo;
use App\Models\TipoHito;
use App\Models\TipoItem;
use App\Models\TipoOfrenda;
use App\Models\TipoPago;
use App\Models\TipoParentesco;
use App\Models\TipoPeticion;
use App\Models\TipoSede;
use App\Models\TipoUsuario;
use App\Models\User;
use App\Models\VersiculoDiario;
use App\Models\Zona;
use Carbon\CarbonImmutable;
use Illuminate\Database\Connection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class DemoComercialTenantSeeder extends Seeder
{
    use WithoutModelEvents;

    public const VERSION = 1;

    private const SEMILLA_DETERMINISTA = 20261124;

    /**
     * Ejecuta la carga integral de datos de demostración comercial (~2.000 usuarios y ecosistema completo).
     */
    public function run(int $totalUsuarios = 2000, string $nombreIglesia = 'Iglesia Vida Plena'): void
    {
        // 1. Validar aislamiento estricto en esquema PostgreSQL de un tenant autorizado para demo comercial.
        $tenant = tenant();
        $conexion = (new User)->getConnection();
        if (! $tenant instanceof Tenant || ! tenancy()->initialized
            || $conexion->getName() !== 'tenant' || $conexion->getDriverName() !== 'pgsql') {
            throw new \LogicException('El seeder comercial exige un tenant PostgreSQL inicializado.');
        }

        $esquemaActivo = (string) $conexion->selectOne('select current_schema() as name')->name;
        if ($esquemaActivo === 'public' || $esquemaActivo !== $tenant->database()->getName()) {
            throw new \LogicException('El esquema activo no coincide con el esquema aislado del tenant.');
        }

        if (! $this->esTenantDemoPermitido($tenant)) {
            throw new \LogicException('Este seeder solo está autorizado en cuentas demo comerciales (comercial.*).');
        }

        if ((int) $tenant->demo_comercial_version >= self::VERSION) {
            $this->command?->info('La demo comercial ya está instalada en este tenant; se conservan los datos actuales.');

            return;
        }

        if ($totalUsuarios < 300 || $totalUsuarios > 10000) {
            throw new \InvalidArgumentException('La cantidad objetivo de usuarios debe estar entre 300 y 10000.');
        }

        // 2. Si el tenant está recién creado sin plantilla base, instalar primero CuentaInicialTenantSeeder.
        if ((int) $tenant->cuenta_inicial_version === 0 && ! User::withTrashed()->exists() && ! Role::exists()) {
            app(CuentaInicialTenantSeeder::class)->setCommand($this->command)->__invoke();
            $tenant->refresh();
        }

        $conteoPrevioUsuarios = User::withTrashed()->count();
        if ($conteoPrevioUsuarios > 10) {
            throw new \LogicException('El tenant ya contiene '.$conteoPrevioUsuarios.' usuarios; se aborta para no alterar datos existentes.');
        }

        mt_srand(self::SEMILLA_DETERMINISTA);
        $ahora = CarbonImmutable::now();

        // 3. Ejecutar toda la siembra transaccional dentro del esquema del tenant.
        $conexion->transaction(function () use ($conexion, $totalUsuarios, $nombreIglesia, $ahora): void {
            $this->sincronizarSecuencias($conexion);

            $mapaSedes = $this->sembrarIglesiaZonasYSedes($nombreIglesia);
            $estructura = $this->sembrarEstructuraMinisterialYUsuarios($conexion, $mapaSedes, $totalUsuarios, $ahora);

            $this->sembrarRelacionesFamiliares($conexion, $estructura['adultosIds'], $estructura['menoresIds'], $ahora);
            $this->sembrarConsolidacionYPasos($conexion, $mapaSedes, $estructura, $ahora);
            $this->sembrarEscuelaManantialSinPeriodos($conexion, $mapaSedes, $estructura['maestrosUsersIds'], $ahora);
            $this->sembrarActividadesYPuntosDePago($conexion, $mapaSedes, $estructura, $ahora);
            $this->sembrarPlanesLectoresYVersiculos($conexion, $estructura, $ahora);
            $this->sembrarReportesGruposYReuniones($conexion, $mapaSedes, $estructura, $ahora);
            $this->sembrarTiempoConDiosYRuedaDeLaVida($conexion, $estructura, $ahora);
            $this->sembrarCursosHitosMuroPeticionesYConsejeria($conexion, $mapaSedes, $estructura, $ahora);

            $this->sincronizarSecuencias($conexion);
        });

        // 4. Aplanar tabla grupos_de_grupos para consultas jerárquicas instantáneas y marcar versión instalada.
        Artisan::call('grupos:sincronizar-jerarquia');
        $tenant->update([
            'church_name' => $nombreIglesia,
            'demo_comercial_version' => self::VERSION,
        ]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Verifica que el tenant pertenezca explícitamente a un entorno demo comercial o de pruebas locales.
     */
    private function esTenantDemoPermitido(Tenant $tenant): bool
    {
        if ((bool) $tenant->demo_comercial_cloud || (bool) $tenant->cuenta_inicial_local) {
            return true;
        }

        if (str_starts_with(Str::lower($tenant->id), 'comercial') || str_starts_with(Str::lower($tenant->id), 'cuenta_inicial_demo')) {
            return true;
        }

        $dominios = $tenant->domains()->pluck('domain')->map(fn (string $d): string => Str::lower($d))->all();
        foreach ($dominios as $dominio) {
            if ($dominio === 'comercial' || str_starts_with($dominio, 'comercial.')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Configura los metadatos de la iglesia comercial, 4 zonas geográficas y 12 sedes ficticias.
     *
     * @return list<Sede>
     */
    private function sembrarIglesiaZonasYSedes(string $nombreIglesia): array
    {
        $paisColombiaId = Pais::query()->where('nombre', 'ilike', '%Colombia%')->value('id') ?? Pais::query()->value('id');
        $municipioBogotaId = Municipio::query()->where('nombre', 'ilike', '%Bogot%')->value('id') ?? Municipio::query()->value('id');

        Configuracion::query()->whereKey(1)->update([
            'nombre_app_personalizado' => $nombreIglesia,
            'identificacion_obligatoria' => true,
            'usa_listas_geograficas' => true,
            'habilitar_salones_con_estaciones' => true,
            'mensaje_donacion_ofrenda' => '<p>Gracias por sembrar con generosidad en la extensión del Reino y en el cuidado de las familias.</p>',
        ]);

        Iglesia::query()->updateOrCreate(
            ['id' => 1],
            [
                'nombre' => $nombreIglesia,
                'direccion' => 'Av. Paseo Castellana # 94-15, Bogotá',
                'telefono1' => '6017428900',
                'telefono2' => '3158402210',
                'rhema' => 'Familia de Fe',
                'texto_rhema' => 'Edificando hogares firmes en la Palabra, el amor y el servicio.',
                'fecha_apertura' => '2014-03-15',
                'configuracion_id' => 1,
                'pais_id' => $paisColombiaId,
                'municipio_id' => $municipioBogotaId,
                'membresia_estimada' => '2000',
                'website' => 'https://comercial.redil.cloud',
                'latitud' => '4.6812',
                'longitud' => '-74.0584',
                'instagram' => 'https://instagram.com/redilcloud',
                'youtube' => 'https://youtube.com/@redilcloud',
            ]
        );

        $definicionZonas = [
            'Zona Metropolitana Norte' => 'Sedes del norte y eje central de la capital.',
            'Zona Metropolitana Sur y Occidente' => 'Sedes del occidente y sur metropolitano.',
            'Zona Sabana Centro' => 'Sedes regionales de la sabana.',
            'Zona Nacional y Campus Virtual' => 'Sedes en ciudades principales y comunidad conectada en línea.',
        ];

        $zonas = [];
        $zonaInicial = Zona::query()->first();
        $indiceZona = 0;
        foreach ($definicionZonas as $nombreZona => $descripcionZona) {
            if ($indiceZona === 0 && $zonaInicial) {
                $zonaInicial->update(['nombre' => $nombreZona, 'descripcion' => $descripcionZona]);
                $zonas[] = $zonaInicial;
            } else {
                $zonas[] = Zona::query()->create(['nombre' => $nombreZona, 'descripcion' => $descripcionZona]);
            }
            $indiceZona++;
        }

        $tipoPrincipalId = TipoSede::query()->where('nombre', 'ilike', '%Principal%')->value('id') ?? 1;
        $tipoSedeRegularId = TipoSede::query()->where('id', '!=', $tipoPrincipalId)->value('id') ?? $tipoPrincipalId;

        $catalogoSedes = [
            ['nombre' => 'Sede Central Castellana', 'direccion' => 'Av. Suba # 94-15', 'tel' => '6017428901', 'cap' => 900, 'lat' => '4.6812', 'lng' => '-74.0584', 'zona' => 0, 'principal' => true],
            ['nombre' => 'Sede Norte Cedritos', 'direccion' => 'Calle 140 # 16-42', 'tel' => '6017428902', 'cap' => 450, 'lat' => '4.7218', 'lng' => '-74.0421', 'zona' => 0, 'principal' => false],
            ['nombre' => 'Sede Colina Campestre', 'direccion' => 'Av. Boyacá # 138-50', 'tel' => '6017428903', 'cap' => 400, 'lat' => '4.7285', 'lng' => '-74.0692', 'zona' => 0, 'principal' => false],
            ['nombre' => 'Sede Occidente Hayuelos', 'direccion' => 'Calle 20 # 82-52', 'tel' => '6017428904', 'cap' => 380, 'lat' => '4.6631', 'lng' => '-74.1298', 'zona' => 1, 'principal' => false],
            ['nombre' => 'Sede Américas Plaza', 'direccion' => 'Av. Américas # 68B-35', 'tel' => '6017428905', 'cap' => 350, 'lat' => '4.6305', 'lng' => '-74.1180', 'zona' => 1, 'principal' => false],
            ['nombre' => 'Sede Sur del Río', 'direccion' => 'Autopista Sur # 59-18', 'tel' => '6017428906', 'cap' => 360, 'lat' => '4.5948', 'lng' => '-74.1512', 'zona' => 1, 'principal' => false],
            ['nombre' => 'Sede Sabana Chía', 'direccion' => 'Variante Chía - Cota Km 2', 'tel' => '6018631400', 'cap' => 420, 'lat' => '4.8615', 'lng' => '-74.0589', 'zona' => 2, 'principal' => false],
            ['nombre' => 'Sede Sabana Cajicá', 'direccion' => 'Carrera 5 # 3-24 Centro', 'tel' => '6018662210', 'cap' => 260, 'lat' => '4.9186', 'lng' => '-74.0279', 'zona' => 2, 'principal' => false],
            ['nombre' => 'Sede Sabana Mosquera', 'direccion' => 'Calle 3 # 12-40', 'tel' => '6018275120', 'cap' => 280, 'lat' => '4.7059', 'lng' => '-74.2302', 'zona' => 2, 'principal' => false],
            ['nombre' => 'Sede Antioquia Poblado', 'direccion' => 'Carrera 43A # 14-80', 'tel' => '6044489120', 'cap' => 320, 'lat' => '6.2088', 'lng' => '-75.5671', 'zona' => 3, 'principal' => false],
            ['nombre' => 'Sede Oriente Cabecera', 'direccion' => 'Carrera 35 # 48-63', 'tel' => '6076438820', 'cap' => 280, 'lat' => '7.1168', 'lng' => '-73.1127', 'zona' => 3, 'principal' => false],
            ['nombre' => 'Sede Campus Online Global', 'direccion' => 'Transmisión Interactiva en Vivo', 'tel' => '3158402299', 'cap' => 1500, 'lat' => '4.6815', 'lng' => '-74.0580', 'zona' => 3, 'principal' => false],
        ];

        $sedes = [];
        $sedeDefault = Sede::query()->where('default', true)->first();

        foreach ($catalogoSedes as $indice => $item) {
            $atributos = [
                'nombre' => $item['nombre'],
                'telefono' => $item['tel'],
                'direccion' => $item['direccion'],
                'tipo_sede_id' => $item['principal'] ? $tipoPrincipalId : $tipoSedeRegularId,
                'descripcion' => 'Campus congregacional '.$item['nombre'].' con grupos de crecimiento y atención pastoral.',
                'fecha_creacion' => '2016-06-01',
                'foto' => 'default.png',
                'capacidad' => $item['cap'],
                'pais_id' => $paisColombiaId,
                'municipio_id' => $municipioBogotaId,
                'default' => $item['principal'],
                'latitud' => $item['lat'],
                'longitud' => $item['lng'],
            ];

            if ($indice === 0 && $sedeDefault) {
                $sedeDefault->update($atributos);
                $sede = $sedeDefault;
            } else {
                $sede = Sede::query()->create($atributos);
            }

            $zonas[$item['zona']]->sedes()->syncWithoutDetaching([$sede->id]);
            $sedes[] = $sede;
        }

        return $sedes;
    }

    /**
     * Construye los 2.000 usuarios y el árbol ministerial completo de 6 niveles inspirado en Crecer.
     *
     * @param  list<Sede>  $sedes
     * @return array{
     *     pastoresPrincipalesIds: list<int>,
     *     pastoresRegionIds: list<int>,
     *     pastoresDistritalesIds: list<int>,
     *     supervisoresGeneralesIds: list<int>,
     *     supervisoresAuxiliaresIds: list<int>,
     *     lideresGfmIds: list<int>,
     *     hermanosMayoresIds: list<int>,
     *     hermanosMenoresIds: list<int>,
     *     nuevosConGrupoIds: list<int>,
     *     nuevosSinGrupoIds: list<int>,
     *     menoresIds: list<int>,
     *     adultosIds: list<int>,
     *     maestrosUsersIds: list<int>,
     *     consejerosUsersIds: list<int>,
     *     intercesoresUsersIds: list<int>,
     *     cajerosUsersIds: list<int>,
     *     gruposCelulares: list<array{id:int, sede_id:int, lider_id:int, tipo_grupo_id:int, dia:int, nombre:string, codigo:string, direccion:string, telefono:string}>,
     *     integrantesPorGrupo: array<int, list<int>>
     * }
     */
    private function sembrarEstructuraMinisterialYUsuarios(
        Connection $conexion,
        array $sedes,
        int $totalUsuariosObjetivo,
        CarbonImmutable $ahora
    ): array {
        // 1. Personalizar los nombres visibles de los perfiles iniciales conservando intactos soporte@redil.co y admin_email.
        $pastoresIniciales = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'Pastor principal'))
            ->orderBy('id')
            ->get();

        $perfilesPastores = [
            ['primer_nombre' => 'Esteban', 'segundo_nombre' => 'Andrés', 'primer_apellido' => 'Morales', 'segundo_apellido' => 'Ruiz', 'genero' => 0, 'email' => 'esteban.morales@demo.redil.invalid'],
            ['primer_nombre' => 'Carolina', 'segundo_nombre' => 'Andrea', 'primer_apellido' => 'Mejía', 'segundo_apellido' => 'Duarte', 'genero' => 1, 'email' => 'carolina.mejia@demo.redil.invalid'],
        ];

        $pastoresPrincipalesIds = [];
        foreach ($pastoresIniciales as $idx => $pastor) {
            $datos = $perfilesPastores[$idx] ?? $perfilesPastores[0];
            $actualizacion = [
                'primer_nombre' => $datos['primer_nombre'],
                'segundo_nombre' => $datos['segundo_nombre'],
                'primer_apellido' => $datos['primer_apellido'],
                'segundo_apellido' => $datos['segundo_apellido'],
                'genero' => $datos['genero'],
                'foto' => $datos['genero'] === 1 ? 'default-f.png' : 'default-m.png',
                'telefono_movil' => '315700100'.($idx + 1),
                'direccion' => 'Av. Paseo Castellana # 94-15',
                'fecha_nacimiento' => $idx === 0 ? '1978-04-12' : '1981-09-23',
                'fecha_ingreso' => '2014-03-15',
                'email_verified_at' => $ahora->toDateTimeString(),
                'puntos' => 1250,
            ];
            if (str_ends_with($pastor->email, '@example.invalid')) {
                $actualizacion['email'] = $datos['email'];
            }
            $pastor->update($actualizacion);
            $pastoresPrincipalesIds[] = (int) $pastor->id;
        }

        $adminIglesia = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'Administrador iglesia'))
            ->first();
        if ($adminIglesia && $adminIglesia->primer_apellido === 'Por configurar') {
            $adminIglesia->update([
                'primer_nombre' => 'Valeria',
                'segundo_nombre' => 'Sofía',
                'primer_apellido' => 'Mendoza',
                'segundo_apellido' => 'Castro',
                'genero' => 1,
                'foto' => 'default-f.png',
                'telefono_movil' => '3157001009',
            ]);
        }

        // 2. Resolver catálogos de tipos de usuario, roles y tipos de grupo por nombre.
        $mapaTiposUsuario = TipoUsuario::query()->pluck('id', 'nombre')->all();
        $mapaRoles = Role::query()->where('guard_name', 'web')->pluck('id', 'name')->all();

        $tipoPastorRegionId = $mapaTiposUsuario['Pastor De Región'] ?? $mapaTiposUsuario['Pastor principal'];
        $tipoPastorDistritalId = $mapaTiposUsuario['Pastor Distrital'] ?? $mapaTiposUsuario['Pastor principal'];
        $tipoSupGeneralId = $mapaTiposUsuario['Supervisor General'] ?? $mapaTiposUsuario['Líder'];
        $tipoSupAuxiliarId = $mapaTiposUsuario['Supervisor Auxiliar'] ?? $mapaTiposUsuario['Líder'];
        $tipoLiderGfmId = $mapaTiposUsuario['Líder GFM'] ?? $mapaTiposUsuario['Líder'];
        $tipoHermanoMayorId = $mapaTiposUsuario['Hermano Mayor'] ?? $mapaTiposUsuario['Miembro'];
        $tipoHermanoMenorId = $mapaTiposUsuario['Hermano Menor'] ?? $mapaTiposUsuario['Miembro'];
        $tipoNuevoId = $mapaTiposUsuario['Nuevo'];

        $rolDependientePorTipo = TipoUsuario::query()->pluck('id_rol_dependiente', 'id')->map(fn ($v): int => (int) $v)->all();

        // 3. Calcular distribución exacta para llegar al total solicitado (por defecto 2.000 usuarios).
        $existentes = User::withTrashed()->count();
        $usuariosACrear = max(0, $totalUsuariosObjetivo - $existentes);

        $cantPastoresRegion = 4;
        $cantPastoresDistritales = count($sedes); // 12
        $cantSupGenerales = 24;
        $cantSupAuxiliares = 48;
        $cantLideresGfm = 144;
        $liderazgoTotal = $cantPastoresRegion + $cantPastoresDistritales + $cantSupGenerales + $cantSupAuxiliares + $cantLideresGfm; // 232

        $restantesBase = max(0, $usuariosACrear - $liderazgoTotal);
        $cantMenores = (int) round($restantesBase * 0.07);
        $cantNuevosSinGrupo = min(65, (int) round($restantesBase * 0.04));
        $cantNuevosConGrupo = (int) round($restantesBase * 0.14);
        $cantHermanosMenores = (int) round($restantesBase * 0.22);
        $cantHermanosMayores = max(0, $restantesBase - $cantMenores - $cantNuevosSinGrupo - $cantNuevosConGrupo - $cantHermanosMenores);

        $planPerfiles = [];
        for ($i = 0; $i < $cantPastoresRegion; $i++) {
            $planPerfiles[] = ['categoria' => 'pastor_region', 'tipo_id' => $tipoPastorRegionId, 'sede_idx' => ($i * 3) % count($sedes), 'menor' => false];
        }
        for ($i = 0; $i < $cantPastoresDistritales; $i++) {
            $planPerfiles[] = ['categoria' => 'pastor_distrital', 'tipo_id' => $tipoPastorDistritalId, 'sede_idx' => $i % count($sedes), 'menor' => false];
        }
        for ($i = 0; $i < $cantSupGenerales; $i++) {
            $planPerfiles[] = ['categoria' => 'sup_general', 'tipo_id' => $tipoSupGeneralId, 'sede_idx' => intdiv($i, 2) % count($sedes), 'menor' => false];
        }
        for ($i = 0; $i < $cantSupAuxiliares; $i++) {
            $planPerfiles[] = ['categoria' => 'sup_auxiliar', 'tipo_id' => $tipoSupAuxiliarId, 'sede_idx' => intdiv($i, 4) % count($sedes), 'menor' => false];
        }
        for ($i = 0; $i < $cantLideresGfm; $i++) {
            $planPerfiles[] = ['categoria' => 'lider_gfm', 'tipo_id' => $tipoLiderGfmId, 'sede_idx' => intdiv($i, 12) % count($sedes), 'menor' => false];
        }
        for ($i = 0; $i < $cantHermanosMayores; $i++) {
            $planPerfiles[] = ['categoria' => 'hermano_mayor', 'tipo_id' => $tipoHermanoMayorId, 'sede_idx' => $this->sedePonderada($i, count($sedes)), 'menor' => false];
        }
        for ($i = 0; $i < $cantHermanosMenores; $i++) {
            $planPerfiles[] = ['categoria' => 'hermano_menor', 'tipo_id' => $tipoHermanoMenorId, 'sede_idx' => $this->sedePonderada($i + 7, count($sedes)), 'menor' => false];
        }
        for ($i = 0; $i < $cantNuevosConGrupo; $i++) {
            $planPerfiles[] = ['categoria' => 'nuevo_grupo', 'tipo_id' => $tipoNuevoId, 'sede_idx' => $this->sedePonderada($i + 13, count($sedes)), 'menor' => false];
        }
        for ($i = 0; $i < $cantNuevosSinGrupo; $i++) {
            $planPerfiles[] = ['categoria' => 'nuevo_sin_grupo', 'tipo_id' => $tipoNuevoId, 'sede_idx' => $this->sedePonderada($i + 19, count($sedes)), 'menor' => false];
        }
        for ($i = 0; $i < $cantMenores; $i++) {
            $planPerfiles[] = ['categoria' => 'menor', 'tipo_id' => $tipoHermanoMenorId, 'sede_idx' => $this->sedePonderada($i + 23, count($sedes)), 'menor' => true];
        }

        // 4. Generar e insertar en lotes los perfiles con nombres ficticios variados y hash único precalculado.
        $hashDemo = Hash::make(Str::random(40));
        $paisId = Pais::query()->where('nombre', 'ilike', '%Colombia%')->value('id') ?? 1;
        $profesionesIds = $conexion->table('profesiones')->limit(40)->pluck('id')->all() ?: [null];
        $ocupacionesIds = $conexion->table('ocupaciones')->pluck('id')->all() ?: [null];
        $nivelesAcadIds = $conexion->table('niveles_academicos')->pluck('id')->all() ?: [null];
        $estadosCivilesIds = [1, 2, 3, 4];

        $nombresMasculinos = [
            'Alejandro', 'Sebastián', 'Mateo', 'Nicolás', 'Samuel', 'Daniel', 'Santiago', 'Gabriel', 'Tomás', 'Martín',
            'Camilo', 'Andrés', 'Felipe', 'Juan', 'David', 'Julián', 'Carlos', 'Miguel', 'Jorge', 'Ricardo',
            'Fernando', 'Mauricio', 'Óscar', 'Édgar', 'Iván', 'Leonardo', 'Cristian', 'Fabián', 'Hernán', 'Diego',
            'Rodrigo', ' Gustavo', 'Álvaro', 'Sergio', 'Pablo', 'Emilio', 'Lucas', 'Joaquín', 'Benjamín', 'Emmanuel',
        ];
        $nombresFemeninos = [
            'Valentina', 'Mariana', 'Isabella', 'Sofía', 'Camila', 'Gabriela', 'Laura', 'Daniela', 'Natalia', 'Paula',
            'Andrea', 'Catalina', 'Juliana', 'Marcela', 'Adriana', 'Claudia', 'Patricia', 'Mónica', 'Sandra', 'Diana',
            'Lucía', 'Elena', 'Sara', 'Victoria', 'Manuela', 'Alejandra', 'Tatiana', 'Verónica', 'Ángela', 'Lorena',
            'Beatriz', 'Pilar', 'Ximena', 'abril', 'Renata', 'Salomé', 'Antonia', 'Clara', 'Milena', 'Viviana',
        ];
        $apellidos = [
            'Gómez', 'Rodríguez', 'Martínez', 'Hernández', 'López', 'González', 'Pérez', 'Sánchez', 'Ramírez', 'Torres',
            'Flórez', 'Rivera', 'Vargas', 'Castillo', 'Rojas', 'Moreno', 'Jiménez', 'Herrera', 'Medina', 'Castro',
            'Suárez', 'Ortega', 'Silva', 'Delgado', 'Ríos', 'Cabrera', 'Mendoza', 'Guerrero', 'Salazar', 'Vega',
            'Cardona', 'Valencia', 'Ospina', 'Restrepo', 'Giraldo', 'Montoya', 'Quintero', 'Pineda', 'Acosta', 'Navarro',
            'alvarado', 'Benítez', 'Cárdenas', 'Cortés', 'Duarte', 'Fajardo', 'León', 'Lozano', 'Molina', 'Parra',
        ];

        $proximoId = (int) User::withTrashed()->max('id') + 1;
        $loteUsuarios = [];
        $loteRoles = [];
        $idsPorCategoria = [
            'pastor_region' => [],
            'pastor_distrital' => [],
            'sup_general' => [],
            'sup_auxiliar' => [],
            'lider_gfm' => [],
            'hermano_mayor' => [],
            'hermano_menor' => [],
            'nuevo_grupo' => [],
            'nuevo_sin_grupo' => [],
            'menor' => [],
        ];
        $adultosIds = [];

        $timestampBase = $ahora->toDateTimeString();

        foreach ($planPerfiles as $idx => $spec) {
            $idUsuario = $proximoId + $idx;
            $genero = ($idx % 2 === 0) ? 1 : 0; // 1 = Femenino, 0 = Masculino
            $primerNombre = trim($genero === 1
                ? ucfirst($nombresFemeninos[($idx * 7) % count($nombresFemeninos)])
                : ucfirst($nombresMasculinos[($idx * 7) % count($nombresMasculinos)]));
            $segundoNombre = trim($genero === 1
                ? ucfirst($nombresFemeninos[($idx * 11 + 3) % count($nombresFemeninos)])
                : ucfirst($nombresMasculinos[($idx * 11 + 3) % count($nombresMasculinos)]));
            if ($primerNombre === $segundoNombre) {
                $segundoNombre = null;
            }
            $primerApellido = ucfirst($apellidos[($idx * 5) % count($apellidos)]);
            $segundoApellido = ucfirst($apellidos[($idx * 13 + 9) % count($apellidos)]);

            $esMenor = $spec['menor'];
            if ($esMenor) {
                $edad = 6 + ($idx % 11); // 6 a 16 años
                $fechaNacimiento = $ahora->subYears($edad)->subDays(($idx * 17) % 300)->toDateString();
            } elseif ($idx < 35) {
                // Garantizar cumpleañeros hoy y en los próximos 7 días para el widget de Cumpleaños del Dashboard.
                $edad = 22 + ($idx % 36);
                $fechaNacimiento = $ahora->subYears($edad)->addDays($idx % 8)->toDateString();
            } else {
                $edad = 19 + (($idx * 19) % 52);
                $fechaNacimiento = $ahora->subYears($edad)->subDays(($idx * 29) % 350)->toDateString();
            }

            $esNuevo = str_starts_with($spec['categoria'], 'nuevo');
            $diasAntiguedad = $esNuevo ? (($idx % 75) + 2) : (120 + (($idx * 23) % 2400));
            $fechaIngreso = $ahora->subDays($diasAntiguedad)->toDateString();
            $sede = $sedes[$spec['sede_idx']];

            $slugCorreo = Str::slug($primerNombre.'.'.$primerApellido).'.'.$idUsuario;
            $reporteReciente = $esNuevo
                ? $ahora->subDays(45)->toDateTimeString()
                : $ahora->subDays(($idx % 18) + 1)->toDateTimeString();

            $loteUsuarios[] = [
                'id' => $idUsuario,
                'email' => $slugCorreo.'@demo.redil.invalid',
                'email_verified_at' => $timestampBase,
                'password' => $hashDemo,
                'created_at' => $ahora->subDays($diasAntiguedad)->toDateTimeString(),
                'updated_at' => $timestampBase,
                'activo' => true,
                'esta_aprobado' => true,
                'primer_nombre' => $primerNombre,
                'segundo_nombre' => $segundoNombre,
                'primer_apellido' => $primerApellido,
                'segundo_apellido' => $segundoApellido,
                'genero' => $genero,
                'tipo_identificacion_id' => $esMenor ? 2 : 3,
                'identificacion' => (string) ($esMenor ? (1100000000 + $idUsuario) : (1010000000 + $idUsuario)),
                'fecha_nacimiento' => $fechaNacimiento,
                'direccion' => 'Calle '.(10 + ($idx % 160)).' # '.(5 + (($idx * 3) % 90)).'-'.(10 + ($idx % 80)),
                'telefono_movil' => '3'.str_pad((string) (100000000 + (($idx * 7919) % 89999999)), 9, '0', STR_PAD_LEFT),
                'estado_civil_id' => $esMenor ? 1 : $estadosCivilesIds[$idx % count($estadosCivilesIds)],
                'fecha_ingreso' => $fechaIngreso,
                'foto' => $genero === 1 ? 'default-f.png' : 'default-m.png',
                'portada' => 'profile-banner.png',
                'tipo_usuario_id' => $spec['tipo_id'],
                'profesion_id' => $esMenor ? null : $profesionesIds[$idx % count($profesionesIds)],
                'nivel_academico_id' => $nivelesAcadIds[$idx % count($nivelesAcadIds)],
                'ocupacion_id' => $ocupacionesIds[$idx % count($ocupacionesIds)],
                'pais_id' => $paisId,
                'tipo_vinculacion_id' => 1,
                'sede_id' => $sede->id,
                'tipo_sangre_id' => ($idx % 8) + 1,
                'creado_como_menor_edad' => $esMenor,
                'creado_como_mayor_edad' => ! $esMenor,
                'ultimo_reporte_grupo' => $reporteReciente,
                'ultimo_reporte_grupo_auxiliar' => $ahora->subDays(25)->toDateTimeString(),
                'ultimo_reporte_reunion' => $reporteReciente,
                'ultimo_reporte_reunion_auxiliar' => $ahora->subDays(25)->toDateTimeString(),
                'usuario_creacion_id' => $pastoresPrincipalesIds[0] ?? 1,
                'indice_grafico_ministerial' => 1,
                'puntos' => $esNuevo ? (($idx % 5) * 40) : (150 + (($idx * 35) % 950)),
            ];

            $rolId = $rolDependientePorTipo[$spec['tipo_id']] ?? $mapaRoles['Miembro'];
            $loteRoles[] = [
                'role_id' => $rolId,
                'model_type' => User::class,
                'model_id' => $idUsuario,
                'activo' => true,
                'dependiente' => true,
            ];

            $idsPorCategoria[$spec['categoria']][] = $idUsuario;
            if (! $esMenor) {
                $adultosIds[] = $idUsuario;
            }
        }

        foreach (array_chunk($loteUsuarios, 250) as $bloque) {
            $conexion->table('users')->insert($bloque);
        }
        foreach (array_chunk($loteRoles, 500) as $bloque) {
            $conexion->table('model_has_roles')->insert($bloque);
        }

        // 5. Asignar roles funcionales adicionales (Maestros, Consejeros, Intercesores, Consolidador, Punto de Pago).
        $maestrosUsersIds = array_slice($idsPorCategoria['sup_auxiliar'], 0, 18);
        $consejerosUsersIds = array_slice($idsPorCategoria['sup_general'], 0, 6);
        $intercesoresUsersIds = array_slice($idsPorCategoria['sup_general'], 6, 8);
        $consolidadoresUsersIds = array_slice($idsPorCategoria['lider_gfm'], 0, 12);
        $cajerosUsersIds = array_slice($idsPorCategoria['lider_gfm'], 12, 6);

        $rolesFuncionales = [];
        foreach ([
            'Maestro' => $maestrosUsersIds,
            'Consejero' => $consejerosUsersIds,
            'Intercesor' => $intercesoresUsersIds,
            'Consolidador' => $consolidadoresUsersIds,
            'Punto de pago' => array_slice($cajerosUsersIds, 0, 3),
            'Cajero punto de pago' => $cajerosUsersIds,
        ] as $nombreRol => $usuariosRol) {
            $idRolExtra = $mapaRoles[$nombreRol] ?? null;
            if (! $idRolExtra) {
                continue;
            }
            foreach ($usuariosRol as $uid) {
                $rolesFuncionales[] = [
                    'role_id' => $idRolExtra,
                    'model_type' => User::class,
                    'model_id' => $uid,
                    'activo' => false,
                    'dependiente' => false,
                ];
            }
        }
        if ($rolesFuncionales !== []) {
            $conexion->table('model_has_roles')->insert($rolesFuncionales);
        }

        // 6. Construir la red jerárquica de grupos en 6 niveles (igual al modelo ministerial de Crecer).
        $mapaTiposGrupo = TipoGrupo::query()->pluck('id', 'nombre')->all();
        $tgRegion = $mapaTiposGrupo['Grupo de Región'] ?? $mapaTiposGrupo['Zona'];
        $tgPastor = $mapaTiposGrupo['Célula Liderazgo Pastor'] ?? $mapaTiposGrupo['Ministerio'];
        $tgSupGen = $mapaTiposGrupo['Célula Liderazgo Sup General'] ?? $mapaTiposGrupo['Grupo de crecimiento'];
        $tgSupAux = $mapaTiposGrupo['Célula Liderazgo Sup Auxiliar'] ?? $mapaTiposGrupo['Grupo de crecimiento'];

        $tiposCelularesDisponibles = array_values(array_filter([
            $mapaTiposGrupo['Grupo de Crecimiento de Familias'] ?? null,
            $mapaTiposGrupo['Grupo de Crecimiento de Familias'] ?? null,
            $mapaTiposGrupo['Grupo de Crecimiento de Warriors'] ?? null,
            $mapaTiposGrupo['Grupo de Crecimiento de Relevo'] ?? null,
            $mapaTiposGrupo['Grupo de Crecimiento de parejas'] ?? null,
            $mapaTiposGrupo['Grupo de Crecimiento de Mujeres'] ?? null,
            $mapaTiposGrupo['Grupo de Crecimiento de Hombres'] ?? null,
            $mapaTiposGrupo['Grupo de Crecimiento de Empresarios'] ?? null,
            $mapaTiposGrupo['Grupo de Crecimiento de Estudiantes'] ?? null,
            $mapaTiposGrupo['Grupo de Crecimiento de Músicos'] ?? null,
        ])) ?: [$mapaTiposGrupo['Grupo de crecimiento']];

        $grupoPrincipal = Grupo::query()->where('codigo', 'PRINCIPAL')->firstOrFail();
        $grupoPrincipal->update([
            'nombre' => 'Equipo Pastoral Principal',
            'inactivo' => false,
            'dado_baja' => false,
            'fecha_apertura' => '2014-03-15',
            'dia' => 2,
            'hora' => '08:00:00',
            'direccion' => $sedes[0]->direccion,
            'telefono' => $sedes[0]->telefono,
            'latitud' => $sedes[0]->latitud,
            'longitud' => $sedes[0]->longitud,
        ]);

        $filasEncargados = [];
        $filasIntegrantes = [];

        // Los 4 Pastores de Región son integrantes del Grupo Principal para conectar la raíz del árbol.
        foreach ($idsPorCategoria['pastor_region'] as $prId) {
            $filasIntegrantes[] = ['grupo_id' => $grupoPrincipal->id, 'user_id' => $prId, 'created_at' => $timestampBase, 'updated_at' => $timestampBase];
        }

        // Nivel 2: 4 Grupos de Región
        $nombresRegiones = ['Red Metropolitana Norte', 'Red Metropolitana Sur y Occidente', 'Red Sabana Centro', 'Red Nacional y Global'];
        $gruposRegionIds = [];
        foreach ($idsPorCategoria['pastor_region'] as $rIdx => $prId) {
            $sedeRef = $sedes[($rIdx * 3) % count($sedes)];
            $grupoReg = Grupo::query()->create([
                'nombre' => $nombresRegiones[$rIdx],
                'codigo' => 'REG-0'.($rIdx + 1),
                'tipo_grupo_id' => $tgRegion,
                'sede_id' => $sedeRef->id,
                'nivel' => 2,
                'dia' => 2,
                'hora' => '09:00:00',
                'fecha_apertura' => '2016-02-10',
                'direccion' => $sedeRef->direccion,
                'telefono' => $sedeRef->telefono,
                'latitud' => $sedeRef->latitud,
                'longitud' => $sedeRef->longitud,
                'dado_baja' => false,
                'inactivo' => false,
            ]);
            $gruposRegionIds[] = $grupoReg->id;
            $filasEncargados[] = ['grupo_id' => $grupoReg->id, 'user_id' => $prId, 'created_at' => $timestampBase, 'updated_at' => $timestampBase];
        }

        // Nivel 3: 12 Células de Liderazgo Pastoral (1 por Sede)
        $gruposPastorIds = [];
        foreach ($idsPorCategoria['pastor_distrital'] as $dIdx => $pdId) {
            $sede = $sedes[$dIdx];
            $regionGrupoId = $gruposRegionIds[intdiv($dIdx, 3) % count($gruposRegionIds)];
            $filasIntegrantes[] = ['grupo_id' => $regionGrupoId, 'user_id' => $pdId, 'created_at' => $timestampBase, 'updated_at' => $timestampBase];

            $grupoPas = Grupo::query()->create([
                'nombre' => 'Equipo Pastoral '.$sede->nombre,
                'codigo' => 'PAS-'.str_pad((string) ($dIdx + 1), 2, '0', STR_PAD_LEFT),
                'tipo_grupo_id' => $tgPastor,
                'sede_id' => $sede->id,
                'nivel' => 3,
                'dia' => 3,
                'hora' => '18:30:00',
                'fecha_apertura' => '2017-05-01',
                'direccion' => $sede->direccion,
                'telefono' => $sede->telefono,
                'latitud' => $sede->latitud,
                'longitud' => $sede->longitud,
                'dado_baja' => false,
                'inactivo' => false,
            ]);
            $gruposPastorIds[] = $grupoPas->id;
            if (! $sede->default) {
                $sede->update(['grupo_id' => $grupoPas->id]);
            }
            $filasEncargados[] = ['grupo_id' => $grupoPas->id, 'user_id' => $pdId, 'created_at' => $timestampBase, 'updated_at' => $timestampBase];
        }

        // Nivel 4: 24 Células de Supervisores Generales (2 por Sede)
        $gruposSupGenIds = [];
        foreach ($idsPorCategoria['sup_general'] as $sgIdx => $sgId) {
            $sedeIdx = intdiv($sgIdx, 2) % count($sedes);
            $sede = $sedes[$sedeIdx];
            $filasIntegrantes[] = ['grupo_id' => $gruposPastorIds[$sedeIdx], 'user_id' => $sgId, 'created_at' => $timestampBase, 'updated_at' => $timestampBase];

            $grupoSg = Grupo::query()->create([
                'nombre' => 'Red Supervisión General '.($sgIdx + 1).' · '.Str::replaceFirst('Sede ', '', $sede->nombre),
                'codigo' => 'SG-'.str_pad((string) ($sgIdx + 1), 2, '0', STR_PAD_LEFT),
                'tipo_grupo_id' => $tgSupGen,
                'sede_id' => $sede->id,
                'nivel' => 4,
                'dia' => 4,
                'hora' => '19:00:00',
                'fecha_apertura' => '2019-01-20',
                'direccion' => $sede->direccion,
                'telefono' => $sede->telefono,
                'latitud' => $sede->latitud,
                'longitud' => $sede->longitud,
                'dado_baja' => false,
                'inactivo' => false,
            ]);
            $gruposSupGenIds[] = $grupoSg->id;
            $filasEncargados[] = ['grupo_id' => $grupoSg->id, 'user_id' => $sgId, 'created_at' => $timestampBase, 'updated_at' => $timestampBase];
        }

        // Nivel 5: 48 Células de Supervisores Auxiliares (2 por Sup General)
        $gruposSupAuxIds = [];
        foreach ($idsPorCategoria['sup_auxiliar'] as $saIdx => $saId) {
            $sgIdx = intdiv($saIdx, 2) % count($gruposSupGenIds);
            $sedeIdx = intdiv($saIdx, 4) % count($sedes);
            $sede = $sedes[$sedeIdx];
            $filasIntegrantes[] = ['grupo_id' => $gruposSupGenIds[$sgIdx], 'user_id' => $saId, 'created_at' => $timestampBase, 'updated_at' => $timestampBase];

            $grupoSa = Grupo::query()->create([
                'nombre' => 'Célula de Supervisión Auxiliar '.($saIdx + 1),
                'codigo' => 'SA-'.str_pad((string) ($saIdx + 1), 2, '0', STR_PAD_LEFT),
                'tipo_grupo_id' => $tgSupAux,
                'sede_id' => $sede->id,
                'nivel' => 5,
                'dia' => 5,
                'hora' => '19:30:00',
                'fecha_apertura' => '2021-03-10',
                'direccion' => $sede->direccion,
                'telefono' => $sede->telefono,
                'latitud' => $sede->latitud,
                'longitud' => $sede->longitud,
                'dado_baja' => false,
                'inactivo' => false,
            ]);
            $gruposSupAuxIds[] = $grupoSa->id;
            $filasEncargados[] = ['grupo_id' => $grupoSa->id, 'user_id' => $saId, 'created_at' => $timestampBase, 'updated_at' => $timestampBase];
        }

        // Nivel 6: 144 Grupos de Crecimiento (3 por Sup Auxiliar, 12 por Sede)
        $nombresBarrios = [
            'Alhambra', 'Niza', 'Colina', 'Cedritos', 'Chicó', 'Salitre', 'Modelia', 'Castilla',
            'Madelena', 'Teusaquillo', 'Fontanar', 'Centro Histórico', 'El Poblado', 'Cabecera', 'Laureles', 'Villa Campestre',
        ];
        $gruposCelulares = [];
        $gruposPorSede = [];

        foreach ($idsPorCategoria['lider_gfm'] as $lIdx => $liderId) {
            $saIdx = intdiv($lIdx, 3) % count($gruposSupAuxIds);
            $sedeIdx = intdiv($lIdx, 12) % count($sedes);
            $sede = $sedes[$sedeIdx];
            $filasIntegrantes[] = ['grupo_id' => $gruposSupAuxIds[$saIdx], 'user_id' => $liderId, 'created_at' => $timestampBase, 'updated_at' => $timestampBase];

            $tipoCelularId = $tiposCelularesDisponibles[$lIdx % count($tiposCelularesDisponibles)];
            $diaReunion = [3, 4, 5, 6][$lIdx % 4]; // Miércoles a Sábado
            $horaReunion = ['19:00:00', '19:30:00', '20:00:00', '16:30:00'][$lIdx % 4];
            $deltaLat = (($lIdx % 9) - 4) * 0.0045;
            $deltaLng = ((intdiv($lIdx, 9) % 9) - 4) * 0.0045;
            $codigoGrupo = 'GC-'.str_pad((string) ($lIdx + 1), 3, '0', STR_PAD_LEFT);
            $nombreGrupo = 'Grupo Vida '.$nombresBarrios[$lIdx % count($nombresBarrios)].' #'.($lIdx + 1);
            $direccionGrupo = 'Carrera '.(12 + ($lIdx % 85)).' # '.(20 + (($lIdx * 3) % 120)).'-'.(10 + ($lIdx % 60));
            $telefonoGrupo = '315800'.str_pad((string) ($lIdx + 1000), 4, '0', STR_PAD_LEFT);

            $grupoCel = Grupo::query()->create([
                'nombre' => $nombreGrupo,
                'codigo' => $codigoGrupo,
                'tipo_grupo_id' => $tipoCelularId,
                'sede_id' => $sede->id,
                'nivel' => 6,
                'dia' => $diaReunion,
                'hora' => $horaReunion,
                'dia_planeacion' => 2,
                'hora_planeacion' => '19:30:00',
                'fecha_apertura' => '2022-02-01',
                'rhema' => 'Creciendo juntos en fe, comunión y propósito.',
                'direccion' => $direccionGrupo,
                'telefono' => $telefonoGrupo,
                'latitud' => number_format((float) $sede->latitud + $deltaLat, 6, '.', ''),
                'longitud' => number_format((float) $sede->longitud + $deltaLng, 6, '.', ''),
                'ultimo_reporte_grupo' => $ahora->subDays(3)->toDateTimeString(),
                'ultimo_reporte_grupo_auxiliar' => $ahora->subDays(10)->toDateTimeString(),
                'dado_baja' => false,
                'inactivo' => false,
            ]);

            $infoCelular = [
                'id' => (int) $grupoCel->id,
                'sede_id' => (int) $sede->id,
                'lider_id' => $liderId,
                'tipo_grupo_id' => (int) $tipoCelularId,
                'dia' => $diaReunion,
                'nombre' => $nombreGrupo,
                'codigo' => $codigoGrupo,
                'direccion' => $direccionGrupo,
                'telefono' => $telefonoGrupo,
            ];
            $gruposCelulares[] = $infoCelular;
            $gruposPorSede[$sede->id][] = (int) $grupoCel->id;

            $filasEncargados[] = ['grupo_id' => $grupoCel->id, 'user_id' => $liderId, 'created_at' => $timestampBase, 'updated_at' => $timestampBase];
        }

        // 7. Asignar los congregantes (Hermanos Mayores, Hermanos Menores, Nuevos con grupo y Menores) a grupos de su misma sede.
        $miembrosParaAsignar = array_merge(
            $idsPorCategoria['hermano_mayor'],
            $idsPorCategoria['hermano_menor'],
            $idsPorCategoria['nuevo_grupo'],
            $idsPorCategoria['menor']
        );

        $mapaSedePorUsuario = $conexion->table('users')
            ->whereIn('id', $miembrosParaAsignar)
            ->pluck('sede_id', 'id')
            ->all();

        $contadoresSede = [];
        $integrantesPorGrupo = [];
        foreach ($miembrosParaAsignar as $uid) {
            $sedeUsuarioId = (int) ($mapaSedePorUsuario[$uid] ?? $sedes[0]->id);
            $listaGruposSede = $gruposPorSede[$sedeUsuarioId] ?? [$gruposCelulares[0]['id']];
            $cursor = $contadoresSede[$sedeUsuarioId] ?? 0;
            $grupoDestinoId = $listaGruposSede[$cursor % count($listaGruposSede)];
            $contadoresSede[$sedeUsuarioId] = $cursor + 1;

            $filasIntegrantes[] = [
                'grupo_id' => $grupoDestinoId,
                'user_id' => $uid,
                'created_at' => $timestampBase,
                'updated_at' => $timestampBase,
            ];
            $integrantesPorGrupo[$grupoDestinoId][] = $uid;
        }

        foreach (array_chunk($filasEncargados, 500) as $bloque) {
            $conexion->table('encargados_grupo')->insert($bloque);
        }
        foreach (array_chunk($filasIntegrantes, 500) as $bloque) {
            $conexion->table('integrantes_grupo')->insert($bloque);
        }

        return [
            'pastoresPrincipalesIds' => $pastoresPrincipalesIds,
            'pastoresRegionIds' => $idsPorCategoria['pastor_region'],
            'pastoresDistritalesIds' => $idsPorCategoria['pastor_distrital'],
            'supervisoresGeneralesIds' => $idsPorCategoria['sup_general'],
            'supervisoresAuxiliaresIds' => $idsPorCategoria['sup_auxiliar'],
            'lideresGfmIds' => $idsPorCategoria['lider_gfm'],
            'hermanosMayoresIds' => $idsPorCategoria['hermano_mayor'],
            'hermanosMenoresIds' => $idsPorCategoria['hermano_menor'],
            'nuevosConGrupoIds' => $idsPorCategoria['nuevo_grupo'],
            'nuevosSinGrupoIds' => $idsPorCategoria['nuevo_sin_grupo'],
            'menoresIds' => $idsPorCategoria['menor'],
            'adultosIds' => $adultosIds,
            'maestrosUsersIds' => $maestrosUsersIds,
            'consejerosUsersIds' => $consejerosUsersIds,
            'intercesoresUsersIds' => $intercesoresUsersIds,
            'cajerosUsersIds' => $cajerosUsersIds,
            'gruposCelulares' => $gruposCelulares,
            'integrantesPorGrupo' => $integrantesPorGrupo,
        ];
    }

    /**
     * Distribución ponderada realista entre las 12 sedes (mayor concentración en Sede Central y Norte).
     */
    private function sedePonderada(int $semilla, int $totalSedes): int
    {
        $pesos = [0, 0, 0, 1, 1, 2, 2, 3, 4, 5, 6, 6, 7, 8, 9, 10, 11];

        return $pesos[$semilla % count($pesos)] % $totalSedes;
    }

    /**
     * Crea vínculos familiares bidireccionales coherentes (matrimonios y padres con hijos menores).
     *
     * @param  list<int>  $adultosIds
     * @param  list<int>  $menoresIds
     */
    private function sembrarRelacionesFamiliares(
        Connection $conexion,
        array $adultosIds,
        array $menoresIds,
        CarbonImmutable $ahora
    ): void {
        if (count($adultosIds) < 40 || $menoresIds === []) {
            return;
        }

        $tiposParentesco = TipoParentesco::query()->get();
        $conyugeId = $tiposParentesco->first(fn ($t) => Str::contains(Str::lower($t->nombre), ['espos', 'cónyug', 'conyug']))?->id
            ?? $tiposParentesco->first()?->id;
        $padreId = $tiposParentesco->first(fn ($t) => Str::contains(Str::lower($t->nombre), ['padre', 'madre', 'progenitor']))?->id
            ?? $conyugeId;
        $hijoId = $tiposParentesco->first(fn ($t) => Str::contains(Str::lower($t->nombre), ['hij']))?->id
            ?? $padreId;

        $ts = $ahora->toDateTimeString();
        $filas = [];

        // 40 parejas casadas bidireccionales
        for ($i = 0; $i < 40; $i++) {
            $esposa = $adultosIds[$i * 2];
            $esposo = $adultosIds[$i * 2 + 1];
            $filas[] = ['user_id' => $esposa, 'pariente_user_id' => $esposo, 'es_el_responsable' => false, 'acepto_terminos_condiciones' => true, 'tipo_pariente_id' => $conyugeId, 'created_at' => $ts, 'updated_at' => $ts];
            $filas[] = ['user_id' => $esposo, 'pariente_user_id' => $esposa, 'es_el_responsable' => false, 'acepto_terminos_condiciones' => true, 'tipo_pariente_id' => $conyugeId, 'created_at' => $ts, 'updated_at' => $ts];
        }

        // Vincular cada menor con un adulto responsable
        $padresConHijos = [];
        foreach ($menoresIds as $idx => $menorId) {
            $responsableId = $adultosIds[$idx % 80];
            $padresConHijos[] = $responsableId;
            $filas[] = ['user_id' => $responsableId, 'pariente_user_id' => $menorId, 'es_el_responsable' => true, 'acepto_terminos_condiciones' => true, 'tipo_pariente_id' => $hijoId, 'created_at' => $ts, 'updated_at' => $ts];
            $filas[] = ['user_id' => $menorId, 'pariente_user_id' => $responsableId, 'es_el_responsable' => false, 'acepto_terminos_condiciones' => true, 'tipo_pariente_id' => $padreId, 'created_at' => $ts, 'updated_at' => $ts];
        }

        foreach (array_chunk($filas, 300) as $bloque) {
            $conexion->table('parientes_usuarios')->insert($bloque);
        }

        User::query()->whereIn('id', array_unique($padresConHijos))->update(['tiene_hijos' => true]);
    }

    /**
     * Configura bloques del dashboard de Consolidación, tareas de seguimiento y pasos de crecimiento por etapa espiritual.
     *
     * @param  list<Sede>  $sedes
     * @param  array<string, mixed>  $estructura
     */
    private function sembrarConsolidacionYPasos(
        Connection $conexion,
        array $sedes,
        array $estructura,
        CarbonImmutable $ahora
    ): void {
        $bloquesDef = [
            'Sede Central y Norte' => [0, 1, 2],
            'Sedes Sur y Occidente' => [3, 4, 5],
            'Sedes Región Sabana' => [6, 7, 8],
            'Sedes Nacionales y Campus Online' => [9, 10, 11],
        ];

        foreach ($bloquesDef as $nombreBloque => $indicesSedes) {
            $bloque = BloqueDashboardConsolidacion::query()->create(['nombre' => $nombreBloque]);
            $idsSedes = [];
            foreach ($indicesSedes as $idxSede) {
                if (isset($sedes[$idxSede])) {
                    $idsSedes[] = $sedes[$idxSede]->id;
                }
            }
            $bloque->sedes()->sync($idsSedes);
        }

        $tareasDef = [
            ['nombre' => 'Primer Contacto Telefónico', 'descripcion' => "1. Mensaje de bienvenida\n2. Llamada pastoral\n3. Confirmación de datos", 'orden' => 1],
            ['nombre' => 'Asistencia a Reunión Dominical', 'descripcion' => "1. Invitación al servicio\n2. Bienvenida presencial\n3. Acompañamiento", 'orden' => 2],
            ['nombre' => 'Café de Bienvenida', 'descripcion' => "1. Invitación especial\n2. Presentación de la visión congregacional", 'orden' => 3],
            ['nombre' => 'Consejería de Inicio', 'descripcion' => "1. Escucha pastoral\n2. Oración por necesidades familiares", 'orden' => 4],
            ['nombre' => 'Vinculación a Grupo de Crecimiento', 'descripcion' => "1. Ubicar grupo cercano\n2. Presentación con el líder\n3. Primera asistencia", 'orden' => 5],
            ['nombre' => 'Proceso de Fundamentos en la Fe', 'descripcion' => "1. Inscripción al proceso inicial\n2. Seguimiento semanal", 'orden' => 6],
            ['nombre' => 'Encuentro de Identidad', 'descripcion' => 'Participación en el retiro espiritual de sanidad y propósito.', 'orden' => 7],
            ['nombre' => 'Entrevista Pastoral de Membresía', 'descripcion' => 'Conversación previa al paso público de fe y bautismo.', 'orden' => 8],
            ['nombre' => 'Bautismo en Agua', 'descripcion' => 'Celebración pública de fe junto a su familia y grupo.', 'orden' => 9],
            ['nombre' => 'Ingreso a Academia Manantial', 'descripcion' => 'Orientación para cursar Mentor Espiritual en la escuela.', 'orden' => 10],
        ];

        $rolesConsolidacionIds = Role::query()
            ->whereIn('name', ['Super Administrador REDIL', 'Administrador iglesia', 'Pastor principal', 'Líder', 'Consolidador', 'Conéctate'])
            ->pluck('id')
            ->all();

        $tareasIds = [];
        $ts = $ahora->toDateTimeString();
        $filasRolTarea = [];
        foreach ($tareasDef as $t) {
            $tarea = TareaConsolidacion::query()->create([
                'nombre' => $t['nombre'],
                'descripcion' => $t['descripcion'],
                'orden' => $t['orden'],
                'default' => true,
                'todos_los_roles' => false,
            ]);
            $tareasIds[] = $tarea->id;
            foreach ($rolesConsolidacionIds as $rid) {
                $filasRolTarea[] = ['tarea_consolidacion_id' => $tarea->id, 'rol_id' => $rid, 'created_at' => $ts, 'updated_at' => $ts];
            }
        }
        if ($filasRolTarea !== []) {
            $conexion->table('rol_tarea_consolidacion')->insert($filasRolTarea);
        }

        // Asignar progreso de consolidación a los usuarios Nuevos
        $nuevosTodos = array_merge($estructura['nuevosConGrupoIds'], $estructura['nuevosSinGrupoIds']);
        $filasTareasUsuario = [];
        foreach ($nuevosTodos as $idx => $uid) {
            $cantidadTareas = 1 + ($idx % 4);
            for ($t = 0; $t < $cantidadTareas; $t++) {
                $estadoTarea = ($t < $cantidadTareas - 1) ? 3 : (($idx % 2 === 0) ? 2 : 1);
                $filasTareasUsuario[] = [
                    'tarea_consolidacion_id' => $tareasIds[$t],
                    'user_id' => $uid,
                    'estado_tarea_consolidacion_id' => $estadoTarea,
                    'fecha' => $ahora->subDays(30 - ($t * 5))->toDateString(),
                    'created_at' => $ts,
                    'updated_at' => $ts,
                ];
            }
        }
        foreach (array_chunk($filasTareasUsuario, 400) as $bloque) {
            $conexion->table('tarea_consolidacion_usuario')->insert($bloque);
        }

        // Sembrar Pasos de Crecimiento coherentes con la madurez de cada segmento
        $pasosOrdenados = PasoCrecimiento::query()->orderBy('id')->pluck('id')->all();
        if ($pasosOrdenados === []) {
            return;
        }

        $filasCrecimiento = [];
        $liderazgoCompleto = array_merge(
            $estructura['pastoresPrincipalesIds'],
            $estructura['pastoresRegionIds'],
            $estructura['pastoresDistritalesIds'],
            $estructura['supervisoresGeneralesIds'],
            $estructura['supervisoresAuxiliaresIds'],
            $estructura['lideresGfmIds']
        );

        foreach ($liderazgoCompleto as $uid) {
            foreach (array_slice($pasosOrdenados, 0, 12) as $pIdx => $pasoId) {
                $filasCrecimiento[] = [
                    'paso_crecimiento_id' => $pasoId,
                    'user_id' => $uid,
                    'estado_id' => 3, // Finalizado
                    'fecha' => $ahora->subDays(600 - ($pIdx * 30))->toDateString(),
                    'detalle' => 'Paso completado satisfactoriamente.',
                    'created_at' => $ts,
                    'updated_at' => $ts,
                ];
            }
        }

        foreach (array_slice($estructura['hermanosMayoresIds'], 0, 650) as $idx => $uid) {
            $tope = 5 + ($idx % 5);
            foreach (array_slice($pasosOrdenados, 0, $tope) as $pIdx => $pasoId) {
                $filasCrecimiento[] = [
                    'paso_crecimiento_id' => $pasoId,
                    'user_id' => $uid,
                    'estado_id' => ($pIdx === $tope - 1 && $idx % 3 === 0) ? 2 : 3,
                    'fecha' => $ahora->subDays(320 - ($pIdx * 20))->toDateString(),
                    'detalle' => 'Registrado en proceso congregacional.',
                    'created_at' => $ts,
                    'updated_at' => $ts,
                ];
            }
        }

        foreach (array_slice($estructura['hermanosMenoresIds'], 0, 300) as $idx => $uid) {
            foreach (array_slice($pasosOrdenados, 0, 3) as $pIdx => $pasoId) {
                $filasCrecimiento[] = [
                    'paso_crecimiento_id' => $pasoId,
                    'user_id' => $uid,
                    'estado_id' => $pIdx < 2 ? 3 : 2,
                    'fecha' => $ahora->subDays(90 - ($pIdx * 15))->toDateString(),
                    'detalle' => 'Primeros pasos de fe.',
                    'created_at' => $ts,
                    'updated_at' => $ts,
                ];
            }
        }

        foreach (array_chunk($filasCrecimiento, 1000) as $bloque) {
            $conexion->table('crecimiento_usuario')->insert($bloque);
        }
    }

    /**
     * Siembra la Escuela Manantial de Vida Eterna completa (cortes, 18 materias, prerrequisitos,
     * plantillas de calificación, 24 aulas, maestros y 42 horarios base) SIN crear periodos.
     *
     * @param  list<Sede>  $sedes
     * @param  list<int>  $maestrosUsersIds
     */
    private function sembrarEscuelaManantialSinPeriodos(
        Connection $conexion,
        array $sedes,
        array $maestrosUsersIds,
        CarbonImmutable $ahora
    ): void {
        $rutaJson = database_path('seeders/data/demo-comercial/escuela.json');
        $datos = json_decode((string) file_get_contents($rutaJson), true, flags: JSON_THROW_ON_ERROR);

        $escuela = Escuela::query()->create([
            'nombre' => $datos['escuela']['nombre'],
            'descripcion' => $datos['escuela']['descripcion'],
            'tipo_matricula' => $datos['escuela']['tipo_matricula'],
            'habilitada_consolidacion' => $datos['escuela']['habilitada_consolidacion'],
            'portada' => 'default.png',
            'horasDisponiblidadLinkAsistencia' => $datos['escuela']['horasDisponiblidadLinkAsistencia'],
            'dia_inicio_semana' => $datos['escuela']['dia_inicio_semana'],
            'caracter_obligatorio' => $datos['escuela']['caracter_obligatorio'],
        ]);

        // 1. Cortes de la escuela (35% / 35% / 30%)
        $cortesIds = [];
        foreach ($datos['cortes'] as $corte) {
            $modeloCorte = CorteEscuela::query()->create([
                'escuela_id' => $escuela->id,
                'nombre' => $corte['nombre'],
                'porcentaje' => $corte['porcentaje'],
                'orden' => $corte['orden'],
            ]);
            $cortesIds[] = $modeloCorte->id;
        }

        // 2. Aulas físicas y virtuales en las 12 sedes (24 aulas) y registro de 18 maestros
        $tipoPresencialId = TipoAula::query()->where('nombre', 'ilike', '%Templo%')->value('id') ?? 1;
        $tipoVirtualId = TipoAula::query()->where('nombre', 'ilike', '%Virtual%')->value('id') ?? $tipoPresencialId;

        $aulasIds = [];
        foreach ($sedes as $idx => $sede) {
            $esOnline = Str::contains(Str::lower($sede->nombre), 'online');
            $aula1 = Aula::query()->create([
                'nombre' => $esOnline ? 'Sala Interactiva Zoom A' : 'Salón Manantial A · '.Str::replaceFirst('Sede ', '', $sede->nombre),
                'direccion' => $sede->direccion,
                'descripcion' => 'Aula equipada para clases de formación bíblica y ministerial.',
                'sede_id' => $sede->id,
                'tipo_aula_id' => $esOnline ? $tipoVirtualId : $tipoPresencialId,
                'activo' => true,
            ]);
            $aula2 = Aula::query()->create([
                'nombre' => $esOnline ? 'Sala Interactiva Zoom B' : 'Aula Virtual · '.Str::replaceFirst('Sede ', '', $sede->nombre),
                'direccion' => 'Plataforma Virtual Sincrónica',
                'descripcion' => 'Aula virtual de apoyo para horarios nocturnos.',
                'sede_id' => $sede->id,
                'tipo_aula_id' => $tipoVirtualId,
                'activo' => true,
            ]);
            $aulasIds[] = $aula1->id;
            $aulasIds[] = $aula2->id;
        }

        foreach ($maestrosUsersIds as $mUid) {
            Maestro::query()->create([
                'user_id' => $mUid,
                'activo' => true,
                'descripcion' => 'Docente habilitado de la Escuela Manantial de Vida Eterna.',
            ]);
        }

        // 3. Materias, plantillas de evaluación por corte, pasos de crecimiento y horarios base (SIN periodos)
        $mapaTiposItem = TipoItem::query()->pluck('id', 'nombre')->all();
        $primerTipoItemId = reset($mapaTiposItem) ?: null;
        $mapaPasos = PasoCrecimiento::query()->pluck('id', 'nombre')->all();

        $mapaRefMateria = [];
        $ts = $ahora->toDateTimeString();
        $filasPlantillas = [];
        $filasMateriaPaso = [];

        $franjasHorarias = [
            ['dia' => 2, 'inicio' => '19:00:00', 'fin' => '21:00:00'],
            ['dia' => 4, 'inicio' => '19:00:00', 'fin' => '21:00:00'],
            ['dia' => 6, 'inicio' => '08:00:00', 'fin' => '10:00:00'],
            ['dia' => 6, 'inicio' => '10:30:00', 'fin' => '12:30:00'],
            ['dia' => 0, 'inicio' => '09:00:00', 'fin' => '11:00:00'],
        ];

        foreach ($datos['materias'] as $mIdx => $matData) {
            $materia = Materia::query()->create([
                'nombre' => $matData['nombre'],
                'escuela_id' => $escuela->id,
                'descripcion' => $matData['descripcion'],
                'habilitar_calificaciones' => $matData['habilitar_calificaciones'],
                'habilitar_asistencias' => $matData['habilitar_asistencias'],
                'asistencias_minimas' => $matData['asistencias_minimas'],
                'asistencias_minima_alerta' => $matData['asistencias_minima_alerta'],
                'habilitar_inasistencias' => false,
                'habilitar_alerta_inasistencias' => $matData['habilitar_alerta_inasistencias'],
                'habilitar_traslado' => $matData['habilitar_traslado'],
                'caracter_obligatorio' => $matData['caracter_obligatorio'],
                'portada' => 'default.png',
                'tiene_dia_limite' => false,
                'cantidad_limite_reportes_semana' => 1,
                'orden' => $matData['orden'],
                'creditos' => $matData['creditos'],
            ]);

            $mapaRefMateria[$matData['ref']] = $materia->id;

            foreach ($cortesIds as $corteId) {
                foreach ($datos['plantillas_por_corte'] as $tpl) {
                    $filasPlantillas[] = [
                        'materia_id' => $materia->id,
                        'corte_escuela_id' => $corteId,
                        'tipo_item_id' => $mapaTiposItem[$tpl['tipo_item_nombre']] ?? $primerTipoItemId,
                        'nombre' => $tpl['nombre'],
                        'contenido' => 'Actividad evaluativa del corte académico.',
                        'visible_predeterminado' => true,
                        'entregable_predeterminado' => $tpl['orden'] === 1,
                        'porcentaje_sugerido' => $tpl['porcentaje_sugerido'],
                        'orden' => $tpl['orden'],
                        'created_at' => $ts,
                        'updated_at' => $ts,
                    ];
                }
            }

            if (! empty($matData['paso']) && isset($mapaPasos[$matData['paso']])) {
                $pasoId = $mapaPasos[$matData['paso']];
                $filasMateriaPaso[] = [
                    'paso_crecimiento_id' => $pasoId,
                    'materia_id' => $materia->id,
                    'estado' => 2,
                    'al_iniciar' => 1,
                    'indice' => 1,
                    'estado_paso_crecimiento_usuario_id' => 2,
                    'created_at' => $ts,
                    'updated_at' => $ts,
                ];
                $filasMateriaPaso[] = [
                    'paso_crecimiento_id' => $pasoId,
                    'materia_id' => $materia->id,
                    'estado' => 3,
                    'al_iniciar' => 0,
                    'indice' => 1,
                    'estado_paso_crecimiento_usuario_id' => 3,
                    'created_at' => $ts,
                    'updated_at' => $ts,
                ];
            }

            // Crear entre 2 y 3 horarios base por materia (presencial y virtual en distintas sedes)
            $cantidadHorarios = ($mIdx < 6) ? 3 : 2;
            for ($h = 0; $h < $cantidadHorarios; $h++) {
                $franja = $franjasHorarias[($mIdx + $h) % count($franjasHorarias)];
                $aulaId = $aulasIds[(($mIdx * 2) + $h) % count($aulasIds)];
                HorarioBase::query()->create([
                    'materia_id' => $materia->id,
                    'aula_id' => $aulaId,
                    'capacidad' => 45,
                    'capacidad_limite' => 55,
                    'dia' => $franja['dia'],
                    'hora_inicio' => $franja['inicio'],
                    'hora_fin' => $franja['fin'],
                    'activo' => true,
                ]);
            }
        }

        ItemPlantilla::query()->insert($filasPlantillas);
        if ($filasMateriaPaso !== []) {
            $conexion->table('materia_paso_crecimiento')->insert($filasMateriaPaso);
        }

        // 4. Prerrequisitos curriculares entre materias
        $filasPrerreq = [];
        foreach ($datos['prerrequisitos'] as $rel) {
            if (isset($mapaRefMateria[$rel['materia']], $mapaRefMateria[$rel['prerrequisito']])) {
                $filasPrerreq[] = [
                    'materia_id' => $mapaRefMateria[$rel['materia']],
                    'materia_prerrequisito_id' => $mapaRefMateria[$rel['prerrequisito']],
                    'created_at' => $ts,
                    'updated_at' => $ts,
                ];
            }
        }
        if ($filasPrerreq !== []) {
            $conexion->table('materia_prerrequisito')->insert($filasPrerreq);
        }
    }

    /**
     * Siembra puntos de pago, cajas, destinatarios y 4 actividades completas (incluyendo las basadas en Crecer)
     * con categorías, tarifas, formularios e inscripciones/compras reales de muestra.
     *
     * @param  list<Sede>  $sedes
     * @param  array<string, mixed>  $estructura
     */
    private function sembrarActividadesYPuntosDePago(
        Connection $conexion,
        array $sedes,
        array $estructura,
        CarbonImmutable $ahora
    ): void {
        $monedaCopId = Moneda::query()->where('nombre_corto', 'COP')->value('id') ?? 1;
        $tiposPagoIds = TipoPago::query()->where('activo', true)->pluck('id')->all();
        $tipoPagoPrincipalId = $tiposPagoIds[0] ?? 1;
        $estadoPagoFinalizadoId = EstadoPago::query()
            ->where('tipo_pago_id', $tipoPagoPrincipalId)
            ->where('estado_final_inscripcion', true)
            ->value('id') ?? EstadoPago::query()->value('id');

        $destinatario = Destinatario::query()->create([
            'nombre' => 'Eventos y Ministerios Congregacionales',
            'sucursal_sap' => 'SUC-001',
            'centro_costo_sap' => 'CC-EVENTOS-01',
            'encargado_id' => $estructura['pastoresPrincipalesIds'][0] ?? 1,
            'activo' => true,
        ]);

        // Puntos de pago y cajas en las primeras 4 sedes
        foreach (array_slice($sedes, 0, 4) as $idx => $sede) {
            $cajeroUid = $estructura['cajerosUsersIds'][$idx % count($estructura['cajerosUsersIds'])] ?? 1;
            $pdp = PuntoDePago::query()->create([
                'nombre' => 'Taquilla Principal · '.Str::replaceFirst('Sede ', '', $sede->nombre),
                'sede_id' => $sede->id,
                'encargado_id' => $cajeroUid,
                'estado' => true,
            ]);
            Caja::query()->create([
                'nombre' => 'Caja 01 · '.Str::replaceFirst('Sede ', '', $sede->nombre),
                'punto_de_pago_id' => $pdp->id,
                'user_id' => $cajeroUid,
                'estado' => true,
                'hora_apertura' => '07:00:00',
                'hora_cierre' => '21:30:00',
                'limite_dinero_acumulado' => 15000000,
                'dinero_acumulado' => 1850000,
                'permite_modificar_registros' => true,
            ]);
        }

        foreach ($estructura['cajerosUsersIds'] as $idx => $cajeroUid) {
            AsesorPdp::query()->create([
                'user_id' => $cajeroUid,
                'es_cajero' => true,
                'es_encargado' => $idx < 3,
                'descripcion' => 'Asesor habilitado de taquilla y recaudo.',
                'activo' => true,
            ]);
        }

        $tags = [];
        foreach (['Familias', 'Congreso', 'Espiritual', 'Jóvenes', 'Bautismos'] as $nombreTag) {
            $tags[$nombreTag] = TagGeneral::query()->firstOrCreate(['nombre' => $nombreTag])->id;
        }

        $sedesIds = array_map(fn (Sede $s): int => (int) $s->id, $sedes);
        $tiposUsuariosVisiblesIds = TipoUsuario::query()->where('visible', true)->pluck('id')->all();
        $rangosAdultosIds = RangoEdad::query()->pluck('id')->all();

        // Actividad 1 (Basada en ID 2 de Crecer): Encuentro de Parejas
        $actParejas = Actividad::query()->create([
            'nombre' => 'Encuentro de Parejas: Pacto y Propósito',
            'descripcion' => '<p>Un fin de semana diseñado para reconectar, sanar y fortalecer tu matrimonio a la luz de la Palabra de Dios. A través de conferencias, talleres prácticos y tiempos de ministración, las parejas recibirán herramientas para edificar un hogar sólido y lleno de gracia.</p>',
            'descripcion_corta' => 'Fin de semana de renovación matrimonial y familiar.',
            'instrucciones_finales' => '<p>Presenta tu comprobante digital al ingresar al auditorio. Incluye materiales y refrigerios.</p>',
            'fecha_visualizacion' => $ahora->subDays(20)->toDateString(),
            'fecha_inicio' => $ahora->addDays(35)->toDateString(),
            'fecha_cierre' => $ahora->addDays(33)->toDateString(),
            'fecha_finalizacion' => $ahora->addDays(37)->toDateString(),
            'tipo_actividad_id' => TipoActividad::query()->find(5)?->id ?? 1,
            'totalmente_publica' => true,
            'punto_de_pago' => true,
            'color' => '#9B59B6',
            'fondo' => '#EAECEE',
            'activa' => true,
            'restriccion_por_categoria' => false,
            'estado_inscripcion_defecto' => 3,
            'mostrar_en_proximas_actividades' => true,
            'portada' => 'default.png',
        ]);

        $catIndividual = ActividadCategoria::query()->create([
            'nombre' => 'Inscripción Individual',
            'aforo' => 120,
            'aforo_ocupado' => 18,
            'actividad_id' => $actParejas->id,
            'limite_compras' => 1,
            'es_gratuita' => false,
        ]);
        $catPareja = ActividadCategoria::query()->create([
            'nombre' => 'Inscripción Pareja',
            'aforo' => 80,
            'aforo_ocupado' => 24,
            'actividad_id' => $actParejas->id,
            'limite_compras' => 1,
            'es_gratuita' => false,
        ]);

        $ts = $ahora->toDateTimeString();
        $conexion->table('actividad_categoria_monedas')->insert([
            ['actividad_categoria_id' => $catIndividual->id, 'moneda_id' => $monedaCopId, 'valor' => 80000, 'created_at' => $ts, 'updated_at' => $ts],
            ['actividad_categoria_id' => $catPareja->id, 'moneda_id' => $monedaCopId, 'valor' => 150000, 'created_at' => $ts, 'updated_at' => $ts],
        ]);

        ElementoFormularioActividad::query()->create([
            'titulo' => '¿Cuántos años llevan de casados o de relación?',
            'tipo_elemento_id' => 2,
            'actividad_id' => $actParejas->id,
            'required' => true,
            'visible' => true,
            'visible_asistencia' => true,
            'orden' => 1,
        ]);
        ElementoFormularioActividad::query()->create([
            'titulo' => '¿Tienen alguna restricción alimentaria para el almuerzo?',
            'tipo_elemento_id' => 2,
            'actividad_id' => $actParejas->id,
            'required' => false,
            'visible' => true,
            'visible_asistencia' => false,
            'orden' => 2,
        ]);

        // Actividad 2 (Basada en ID 68 de Crecer): Ayuno Congregacional / Un Nuevo Comienzo
        $actAyuno = Actividad::query()->create([
            'nombre' => 'Ayuno Congregacional: Un Nuevo Comienzo',
            'descripcion' => '<p>Tres días unidos como iglesia para buscar la presencia de Dios, renovar nuestras fuerzas espirituales y consagrar nuestros hogares y proyectos a Su propósito eterno.</p>',
            'descripcion_corta' => 'Tres jornadas de oración, adoración y renovación espiritual.',
            'instrucciones_finales' => '<p>Recuerda llegar 20 minutos antes del inicio de cada plenaria con tu código QR.</p>',
            'fecha_visualizacion' => $ahora->subDays(15)->toDateString(),
            'fecha_inicio' => $ahora->addDays(18)->toDateString(),
            'fecha_cierre' => $ahora->addDays(20)->toDateString(),
            'fecha_finalizacion' => $ahora->addDays(20)->toDateString(),
            'tipo_actividad_id' => TipoActividad::query()->find(3)?->id ?? 1,
            'totalmente_publica' => true,
            'permite_personas_externas' => true,
            'tiene_invitados' => true,
            'punto_de_pago' => false,
            'color' => '#2E86C1',
            'fondo' => '#EBF5FB',
            'activa' => true,
            'restriccion_por_categoria' => false,
            'estado_inscripcion_defecto' => 3,
            'mostrar_en_proximas_actividades' => true,
            'portada' => 'default.png',
        ]);

        $catAyuno = ActividadCategoria::query()->create([
            'nombre' => 'Asistente General e Invitados',
            'aforo' => 800,
            'aforo_ocupado' => 45,
            'actividad_id' => $actAyuno->id,
            'limite_compras' => 1,
            'es_gratuita' => true,
            'limite_invitados' => 5,
        ]);

        foreach (['Sede o congregación a la que asistes', 'Ciudad de residencia', 'País'] as $orden => $pregunta) {
            ElementoFormularioActividad::query()->create([
                'titulo' => $pregunta,
                'tipo_elemento_id' => 2,
                'actividad_id' => $actAyuno->id,
                'required' => true,
                'visible' => true,
                'visible_asistencia' => $orden === 0,
                'orden' => $orden + 1,
            ]);
        }

        // Actividad 3: Retiro Encuentro de Identidad (con abonos habilitados)
        $actRetiro = Actividad::query()->create([
            'nombre' => 'Retiro Espiritual: Encuentro de Identidad',
            'descripcion' => '<p>Una experiencia transformadora de fin de semana enfocada en la sanidad del corazón, el perdón, la libertad en Cristo y el descubrimiento del propósito de Dios para tu vida.</p>',
            'descripcion_corta' => 'Retiro campestre con hospedaje, alimentación y ministración.',
            'instrucciones_finales' => '<p>Lleva Biblia, libreta de apuntes y ropa cómoda para clima templado.</p>',
            'fecha_visualizacion' => $ahora->subDays(10)->toDateString(),
            'fecha_inicio' => $ahora->addDays(50)->toDateString(),
            'fecha_cierre' => $ahora->addDays(47)->toDateString(),
            'fecha_finalizacion' => $ahora->addDays(52)->toDateString(),
            'tipo_actividad_id' => TipoActividad::query()->find(2)?->id ?? 1,
            'totalmente_publica' => false,
            'punto_de_pago' => true,
            'color' => '#1ABC9C',
            'fondo' => '#E8F8F5',
            'activa' => true,
            'restriccion_por_categoria' => false,
            'estado_inscripcion_defecto' => 3,
            'mostrar_en_proximas_actividades' => true,
            'portada' => 'default.png',
        ]);

        $catRetiro = ActividadCategoria::query()->create([
            'nombre' => 'Cupo Completo con Hospedaje',
            'aforo' => 150,
            'aforo_ocupado' => 20,
            'actividad_id' => $actRetiro->id,
            'limite_compras' => 1,
            'es_gratuita' => false,
        ]);
        $conexion->table('actividad_categoria_monedas')->insert([
            ['actividad_categoria_id' => $catRetiro->id, 'moneda_id' => $monedaCopId, 'valor' => 220000, 'created_at' => $ts, 'updated_at' => $ts],
        ]);

        // Actividad 4: Jornada de Bautismos
        $actBautismos = Actividad::query()->create([
            'nombre' => 'Celebración de Bautismos: Nueva Vida en Cristo',
            'descripcion' => '<p>Celebración congregacional para quienes dan su paso público de fe e identificación con Jesucristo acompañados de sus familias y líderes de grupo.</p>',
            'descripcion_corta' => 'Inscripción para candidatos a bautismo en agua.',
            'fecha_visualizacion' => $ahora->subDays(12)->toDateString(),
            'fecha_inicio' => $ahora->addDays(28)->toDateString(),
            'fecha_cierre' => $ahora->addDays(25)->toDateString(),
            'fecha_finalizacion' => $ahora->addDays(28)->toDateString(),
            'tipo_actividad_id' => TipoActividad::query()->find(3)?->id ?? 1,
            'totalmente_publica' => false,
            'punto_de_pago' => false,
            'color' => '#F39C12',
            'fondo' => '#FEF9E7',
            'activa' => true,
            'restriccion_por_categoria' => false,
            'estado_inscripcion_defecto' => 3,
            'mostrar_en_proximas_actividades' => true,
            'portada' => 'default.png',
        ]);
        $catBautismo = ActividadCategoria::query()->create([
            'nombre' => 'Candidato a Bautismo',
            'aforo' => 120,
            'aforo_ocupado' => 15,
            'actividad_id' => $actBautismos->id,
            'limite_compras' => 1,
            'es_gratuita' => true,
        ]);

        // Asociar monedas, tipos de pago, sedes, rangos de edad, tipos de usuario y tags a las 4 actividades
        foreach ([
            [$actParejas, [$tags['Familias'], $tags['Congreso']]],
            [$actAyuno, [$tags['Espiritual'], $tags['Congreso']]],
            [$actRetiro, [$tags['Espiritual'], $tags['Jóvenes']]],
            [$actBautismos, [$tags['Bautismos'], $tags['Espiritual']]],
        ] as [$actividad, $tagsActividad]) {
            $conexion->table('actividad_monedas')->insert([
                'actividad_id' => $actividad->id,
                'moneda_id' => $monedaCopId,
                'created_at' => $ts,
                'updated_at' => $ts,
            ]);
            foreach ($tiposPagoIds as $tpId) {
                $conexion->table('actividad_tipos_pago')->insert([
                    'actividad_id' => $actividad->id,
                    'tipo_pago_id' => $tpId,
                    'created_at' => $ts,
                    'updated_at' => $ts,
                ]);
            }
            $conexion->table('actividad_destinatarios')->insert([
                'actividad_id' => $actividad->id,
                'destinatario_id' => $destinatario->id,
                'created_at' => $ts,
                'updated_at' => $ts,
            ]);
            foreach ($sedesIds as $sid) {
                $conexion->table('actividad_sedes')->insert(['actividad_id' => $actividad->id, 'sede_id' => $sid, 'created_at' => $ts, 'updated_at' => $ts]);
            }
            foreach ($tiposUsuariosVisiblesIds as $tuId) {
                $conexion->table('actividad_tipos_usuarios')->insert(['actividad_id' => $actividad->id, 'tipo_usuario_id' => $tuId, 'created_at' => $ts, 'updated_at' => $ts]);
            }
            foreach ($rangosAdultosIds as $reId) {
                $conexion->table('actividad_rangos_edad')->insert(['actividad_id' => $actividad->id, 'rango_edad_id' => $reId, 'created_at' => $ts, 'updated_at' => $ts]);
            }
            foreach ($tagsActividad as $tagId) {
                $conexion->table('actividad_tags')->insert(['actividad_id' => $actividad->id, 'tag_id' => $tagId, 'created_at' => $ts, 'updated_at' => $ts]);
            }
        }

        // Registrar 60 compras, pagos e inscripciones reales de muestra
        $compradores = User::query()
            ->whereIn('id', array_slice($estructura['hermanosMayoresIds'], 0, 60))
            ->get();

        $escenariosCompra = [
            ['act' => $actParejas, 'cat' => $catPareja, 'valor' => 150000],
            ['act' => $actParejas, 'cat' => $catIndividual, 'valor' => 80000],
            ['act' => $actAyuno, 'cat' => $catAyuno, 'valor' => 0],
            ['act' => $actRetiro, 'cat' => $catRetiro, 'valor' => 220000],
            ['act' => $actBautismos, 'cat' => $catBautismo, 'valor' => 0],
        ];

        foreach ($compradores as $idx => $comprador) {
            $esc = $escenariosCompra[$idx % count($escenariosCompra)];
            $fechaCompra = $ahora->subDays(($idx % 14) + 1)->toDateString();
            $nombreCompleto = trim($comprador->primer_nombre.' '.$comprador->primer_apellido);

            $compra = Compra::query()->create([
                'user_id' => $comprador->id,
                'moneda_id' => $monedaCopId,
                'fecha' => $fechaCompra,
                'valor' => $esc['valor'],
                'estado' => 3,
                'metodo_pago_id' => $tipoPagoPrincipalId,
                'destinatario_id' => $destinatario->id,
                'nombre_completo_comprador' => $nombreCompleto,
                'identificacion_comprador' => (string) $comprador->identificacion,
                'telefono_comprador' => (string) ($comprador->telefono_movil ?? '3150000000'),
                'email_comprador' => $comprador->email,
                'actividad_id' => (string) $esc['act']->id,
                'anulacion_solicitada' => false,
                'operacion_id' => (string) Str::uuid(),
            ]);

            Inscripcion::query()->create([
                'user_id' => $comprador->id,
                'actividad_categoria_id' => $esc['cat']->id,
                'compra_id' => $compra->id,
                'fecha' => $fechaCompra,
                'fecha_pago' => $fechaCompra,
                'estado' => 3,
                'email' => $comprador->email,
                'nombre_inscrito' => $nombreCompleto,
            ]);

            if ($esc['valor'] > 0) {
                Pago::query()->create([
                    'compra_id' => $compra->id,
                    'tipo_pago_id' => $tipoPagoPrincipalId,
                    'estado_pago_id' => $estadoPagoFinalizadoId,
                    'moneda_id' => $monedaCopId,
                    'valor' => $esc['valor'],
                    'valor_neto' => $esc['valor'],
                    'fecha' => $fechaCompra,
                    'actividad_categoria_id' => $esc['cat']->id,
                    'referencia_pago' => 'DEMO-'.str_pad((string) $compra->id, 6, '0', STR_PAD_LEFT),
                    'anulado_pdp' => false,
                ]);
            }
        }
    }

    /**
     * Siembra los 30 Planes Lectores completos (con 147 días, contenidos e inscritos calificados)
     * y 120 días continuos de Versículos del Día alrededor de la fecha actual.
     *
     * @param  array<string, mixed>  $estructura
     */
    private function sembrarPlanesLectoresYVersiculos(
        Connection $conexion,
        array $estructura,
        CarbonImmutable $ahora
    ): void {
        $autorId = $estructura['pastoresPrincipalesIds'][0] ?? 1;
        $mapaCategorias = PlanLectorCategoria::query()->pluck('id', 'slug')->all();
        $mapaTiposContenido = PlanLectorTipoContenido::query()->pluck('id', 'slug')->all();
        $tipoReflexionId = $mapaTiposContenido['reflexion'] ?? 1;

        $planesData = json_decode(
            (string) file_get_contents(database_path('seeders/data/demo-comercial/planes-lectores.json')),
            true,
            flags: JSON_THROW_ON_ERROR
        );

        $ts = $ahora->toDateTimeString();
        $lectoresMuestra = array_slice(
            array_merge($estructura['lideresGfmIds'], $estructura['hermanosMayoresIds']),
            0,
            240
        );

        $filasInscripcionesPlan = [];
        $filasDiasCompletados = [];

        foreach ($planesData as $pIdx => $pData) {
            $calificacionPromedio = round(4.5 + (($pIdx % 6) * 0.08), 2);
            $plan = PlanLector::query()->create([
                'titulo' => $pData['titulo'],
                'slug' => $pData['slug'],
                'descripcion' => $pData['descripcion'],
                'autor_id' => $autorId,
                'calificacion' => $calificacionPromedio,
                'imagen_url' => null,
                'estado' => true,
                'visible_todos' => true,
                'genero' => $pData['genero'] ?? 3,
            ]);

            $idsCats = [];
            foreach ($pData['categorias'] as $catSlug) {
                if (isset($mapaCategorias[$catSlug])) {
                    $idsCats[] = $mapaCategorias[$catSlug];
                }
            }
            if ($idsCats !== []) {
                $plan->categorias()->sync($idsCats);
            }

            $diasCreadosIds = [];
            foreach ($pData['dias'] as $diaData) {
                $dia = PlanLectorDia::query()->create([
                    'plan_lector_id' => $plan->id,
                    'dia' => $diaData['dia'],
                    'titulo' => $diaData['titulo'],
                ]);
                $diasCreadosIds[] = $dia->id;

                foreach ($diaData['contenidos'] as $contData) {
                    PlanLectorContenido::query()->create([
                        'plan_lector_dia_id' => $dia->id,
                        'orden' => $contData['orden'],
                        'plan_lector_tipo_contenido_id' => $mapaTiposContenido[$contData['tipo']] ?? $tipoReflexionId,
                        'contenido' => $contData['contenido'],
                    ]);
                }
            }

            // Inscribir lectores con progreso y calificación en los primeros 15 planes
            if ($pIdx < 15 && $diasCreadosIds !== []) {
                $inicioSlice = ($pIdx * 12) % max(1, count($lectoresMuestra) - 18);
                foreach (array_slice($lectoresMuestra, $inicioSlice, 16) as $uIdx => $lectorId) {
                    $completado = ($uIdx % 3 !== 0);
                    $progreso = $completado ? 100 : (40 + (($uIdx * 15) % 50));
                    $filasInscripcionesPlan[] = [
                        'plan_lector_id' => $plan->id,
                        'user_id' => $lectorId,
                        'estado' => $completado ? 'completado' : 'en_progreso',
                        'fecha_inscripcion' => $ahora->subDays(25 - ($uIdx % 15))->toDateTimeString(),
                        'porcentaje_progreso' => $progreso,
                        'calificacion_usuario' => $completado ? (4 + ($uIdx % 2)) : null,
                        'created_at' => $ts,
                        'updated_at' => $ts,
                    ];

                    $diasACompletar = $completado ? count($diasCreadosIds) : max(1, intdiv(count($diasCreadosIds), 2));
                    foreach (array_slice($diasCreadosIds, 0, $diasACompletar) as $dOrden => $diaId) {
                        $filasDiasCompletados[] = [
                            'plan_lector_dia_id' => $diaId,
                            'user_id' => $lectorId,
                            'fecha_completado' => $ahora->subDays(20 - $dOrden)->toDateTimeString(),
                            'created_at' => $ts,
                            'updated_at' => $ts,
                        ];
                    }
                }
            }
        }

        foreach (array_chunk($filasInscripcionesPlan, 300) as $bloque) {
            $conexion->table('plan_lector_users')->insert($bloque);
        }
        foreach (array_chunk($filasDiasCompletados, 400) as $bloque) {
            $conexion->table('plan_lector_dia_users')->insert($bloque);
        }

        // Versículos del Día: 120 días continuos (desde hace 30 días hasta +89 días) para cobertura permanente
        $versiculosBase = json_decode(
            (string) file_get_contents(database_path('seeders/data/demo-comercial/versiculos.json')),
            true,
            flags: JSON_THROW_ON_ERROR
        );

        $totalBase = count($versiculosBase);
        $filasLikesVersiculo = [];
        for ($offset = -30; $offset <= 89; $offset++) {
            $indice = ($offset + 30) % $totalBase;
            $item = $versiculosBase[$indice];
            $fechaPub = $ahora->addDays($offset)->toDateString();

            $versiculo = VersiculoDiario::query()->create([
                'version_uri' => $item['version_uri'],
                'libro_nombre' => $item['libro_nombre'],
                'cita_referencia' => $item['cita_referencia'],
                'texto_versiculo' => $item['texto_versiculo'],
                'ruta_imagen' => null, // Usa las tarjetas de gradiente nativas de Cloud sin depender de disco efímero
                'url_video_reflexion' => $item['url_video_reflexion'],
                'fecha_publicacion' => $fechaPub,
                'usuario_id' => $autorId,
            ]);

            if ($offset <= 0) {
                $cantidadLikes = ($offset === 0) ? 28 : (8 + (abs($offset) % 14));
                foreach (array_slice($lectoresMuestra, abs($offset) % 40, $cantidadLikes) as $likeUid) {
                    $filasLikesVersiculo[] = [
                        'usuario_id' => $likeUid,
                        'versiculo_diario_id' => $versiculo->id,
                        'created_at' => $ts,
                        'updated_at' => $ts,
                    ];
                }
            }
        }

        foreach (array_chunk($filasLikesVersiculo, 400) as $bloque) {
            $conexion->table('versiculo_usuario_like')->insert($bloque);
        }
    }

    /**
     * Siembra cultos/reuniones con sus reportes históricos y 4 semanas de reportes de grupos celulares con ofrendas.
     *
     * @param  list<Sede>  $sedes
     * @param  array<string, mixed>  $estructura
     */
    private function sembrarReportesGruposYReuniones(
        Connection $conexion,
        array $sedes,
        array $estructura,
        CarbonImmutable $ahora
    ): void {
        $monedaCopId = Moneda::query()->where('nombre_corto', 'COP')->value('id') ?? 1;
        $tipoPagoEfectivoId = TipoPago::query()->value('id') ?? 1;
        $estadoPagoFinId = EstadoPago::query()->where('estado_final_inscripcion', true)->value('id') ?? 1;
        $tipoOfrendaGeneralId = TipoOfrenda::query()->where('nombre', 'ilike', '%Ofrenda%')->value('id')
            ?? TipoOfrenda::query()->value('id') ?? 1;
        $clasificacionAdultosId = ClasificacionAsistente::query()->where('nombre', 'ilike', '%Adult%')->value('id')
            ?? ClasificacionAsistente::query()->value('id');
        $tiposUsuariosVisiblesIds = TipoUsuario::query()->where('visible', true)->pluck('id')->all();

        // 1. Reuniones / Cultos y reportes de las últimas 4 semanas
        $reunionesDef = [
            ['nombre' => 'Primer Servicio Dominical', 'hora' => '08:00:00', 'aforo' => 700, 'reserva' => false],
            ['nombre' => 'Segundo Servicio Familiar Dominical', 'hora' => '10:30:00', 'aforo' => 900, 'reserva' => true],
            ['nombre' => 'Reunión de Jóvenes · Generación Viva', 'hora' => '17:30:00', 'aforo' => 450, 'reserva' => false],
            ['nombre' => 'Noche de Adoración y Milagros', 'hora' => '19:00:00', 'aforo' => 650, 'reserva' => false],
        ];

        $ts = $ahora->toDateTimeString();
        $asistentesMuestraReunion = array_slice($estructura['hermanosMayoresIds'], 0, 220);
        $filasAsistenciaReunion = [];

        foreach ($reunionesDef as $rIdx => $rDef) {
            $reunion = Reunion::query()->create([
                'nombre' => $rDef['nombre'],
                'hora' => $rDef['hora'],
                'portada' => 'default.png',
                'descripcion' => 'Servicio congregacional abierto para toda la familia.',
                'sede_id' => $sedes[0]->id,
                'genero' => '["0","1"]',
                'habilitar_reserva' => $rDef['reserva'],
                'dias_plazo_reporte' => 15,
                'dias_plazo_reserva' => 7,
                'aforo' => $rDef['aforo'],
                'habilitar_reserva_invitados' => $rDef['reserva'],
                'cantidad_maxima_reserva_invitados' => 4,
                'habilitar_reserva_familiares' => $rDef['reserva'],
                'solo_reservados_pueden_asistir' => false,
                'hora_maxima_reportar_asistencia' => '23:59:00',
                'habilitar_preregistro_iglesia_infantil' => true,
            ]);

            foreach ($sedes as $sede) {
                $conexion->table('reuniones_sedes')->insert([
                    'reuniones_id' => $reunion->id,
                    'sedes_id' => $sede->id,
                    'created_at' => $ts,
                    'updated_at' => $ts,
                ]);
            }
            foreach ($tiposUsuariosVisiblesIds as $tuId) {
                $conexion->table('reunion_tipo_usuario')->insert([
                    'reunion_id' => $reunion->id,
                    'tipo_usuario_id' => $tuId,
                    'created_at' => $ts,
                    'updated_at' => $ts,
                ]);
            }

            // Crear 4 reportes recientes para los servicios dominicales
            if ($rIdx < 2) {
                for ($sem = 1; $sem <= 4; $sem++) {
                    $fechaCulto = $ahora->previous(CarbonImmutable::SUNDAY)->subWeeks($sem - 1)->toDateString();
                    $cantAsist = 65 + (($sem * 7 + $rIdx * 11) % 35);
                    $valorOfrenda = 1850000 + ($sem * 210000);

                    $repReunion = ReporteReunion::query()->create([
                        'reunion_id' => $reunion->id,
                        'fecha' => $fechaCulto,
                        'hora' => $rDef['hora'],
                        'portada' => 'default.png',
                        'predicador' => $estructura['pastoresPrincipalesIds'][$rIdx % count($estructura['pastoresPrincipalesIds'])] ?? 1,
                        'observaciones' => 'Jornada dominical de edificación, alabanza y recepción de familias nuevas.',
                        'invitados' => 14 + $sem,
                        'cantidad_asistencias' => $cantAsist,
                        'total_ofrendas' => $valorOfrenda,
                        'autor_creacion' => $estructura['pastoresPrincipalesIds'][0] ?? 1,
                        'aforo' => $rDef['aforo'],
                        'estado' => 'finalizado',
                        'finalizado_por' => $estructura['pastoresPrincipalesIds'][0] ?? 1,
                        'finalizado_at' => $fechaCulto.' 13:30:00',
                    ]);

                    foreach (array_slice($asistentesMuestraReunion, ($sem * 15) % 80, $cantAsist) as $uidAsist) {
                        $filasAsistenciaReunion[] = [
                            'user_id' => $uidAsist,
                            'reporte_reunion_id' => $repReunion->id,
                            'autor_creacion_asistencia_id' => $estructura['pastoresPrincipalesIds'][0] ?? 1,
                            'created_at' => $ts,
                            'updated_at' => $ts,
                        ];
                    }
                }
            }
        }

        foreach (array_chunk($filasAsistenciaReunion, 500) as $bloque) {
            $conexion->table('asistencia_reuniones')->insert($bloque);
        }

        // 2. Reportes de las últimas 4 semanas en los grupos de crecimiento (para poblar gráficas de asistencia y ofrendas)
        $temasSemanales = [
            'Edificando sobre la Roca firme',
            'El poder del acuerdo en el hogar',
            'Una fe que vence el temor',
            'Llamados a servir con excelencia',
        ];

        $filasAsistGrupo = [];
        $filasClasifGrupo = [];
        $filasOfrendaGrupo = [];

        foreach (array_slice($estructura['gruposCelulares'], 0, 96) as $gIdx => $infoGrupo) {
            $miembrosGrupo = $estructura['integrantesPorGrupo'][$infoGrupo['id']] ?? [];
            if ($miembrosGrupo === []) {
                continue;
            }

            for ($sem = 1; $sem <= 4; $sem++) {
                $fechaRep = $ahora->subWeeks($sem)->addDays($infoGrupo['dia'] % 5)->toDateString();
                $asistieron = 0;
                $inasistieron = 0;
                $detalleAsist = [];

                foreach ($miembrosGrupo as $mIdx => $uidMiembro) {
                    $presente = (($mIdx + $sem + $gIdx) % 5 !== 0); // ~80% asistencia realista
                    if ($presente) {
                        $asistieron++;
                    } else {
                        $inasistieron++;
                    }
                    $detalleAsist[] = ['uid' => $uidMiembro, 'asistio' => $presente];
                }

                $reporte = ReporteGrupo::query()->create([
                    'fecha' => $fechaRep,
                    'tema' => $temasSemanales[($sem - 1) % count($temasSemanales)],
                    'observacion' => 'Reunión edificante con participación activa de los integrantes.',
                    'grupo_id' => $infoGrupo['id'],
                    'sede_id' => $infoGrupo['sede_id'],
                    'invitados' => ($gIdx + $sem) % 3,
                    'reporte_a_tiempo' => true,
                    'informacion_del_grupo' => [
                        'nombre' => $infoGrupo['nombre'],
                        'codigo' => $infoGrupo['codigo'],
                        'dia' => $infoGrupo['dia'],
                        'tipo_grupo_id' => $infoGrupo['tipo_grupo_id'],
                        'telefono' => $infoGrupo['telefono'],
                        'direccion' => $infoGrupo['direccion'],
                    ],
                    'encargados_ascendentes' => [$infoGrupo['lider_id']],
                    'ids_grupos_ascendentes' => [$infoGrupo['id']],
                    'cantidad_asistencias' => $asistieron,
                    'cantidad_inasistencias' => $inasistieron,
                    'aprobado' => true,
                    'cerrado' => true,
                    'autor_creacion' => $infoGrupo['lider_id'],
                    'autor_aprobacion' => $estructura['pastoresPrincipalesIds'][0] ?? 1,
                    'fecha_aprobacion' => $fechaRep.' 22:00:00',
                    'finalizado' => true,
                    'no_reporte' => false,
                ]);

                foreach ($detalleAsist as $det) {
                    $filasAsistGrupo[] = [
                        'user_id' => $det['uid'],
                        'reporte_grupo_id' => $reporte->id,
                        'asistio' => $det['asistio'],
                        'tipo_inasistencia_id' => $det['asistio'] ? null : 1,
                        'created_at' => $ts,
                        'updated_at' => $ts,
                    ];
                }

                if ($clasificacionAdultosId) {
                    $filasClasifGrupo[] = [
                        'reporte_grupo_id' => $reporte->id,
                        'clasificacion_asistente_id' => $clasificacionAdultosId,
                        'cantidad' => $asistieron,
                        'created_at' => $ts,
                        'updated_at' => $ts,
                    ];
                }

                $valorOfrendaGrupo = 35000 + (($gIdx * 5000 + $sem * 7000) % 85000);
                $ofrenda = Ofrenda::query()->create([
                    'tipo_ofrenda_id' => $tipoOfrendaGeneralId,
                    'valor' => $valorOfrendaGrupo,
                    'valor_real' => $valorOfrendaGrupo,
                    'fecha' => $fechaRep,
                    'ingresada_por' => 0,
                    'observacion' => 'Ofrenda semanal de grupo de crecimiento.',
                    'estado_pago_id' => $estadoPagoFinId,
                    'tipo_pago_id' => $tipoPagoEfectivoId,
                    'moneda_id' => $monedaCopId,
                ]);

                $filasOfrendaGrupo[] = [
                    'ofrenda_id' => $ofrenda->id,
                    'reporte_grupo_id' => $reporte->id,
                    'created_at' => $ts,
                    'updated_at' => $ts,
                ];
            }
        }

        foreach (array_chunk($filasAsistGrupo, 1000) as $bloque) {
            $conexion->table('asistencia_grupos')->insert($bloque);
        }
        foreach (array_chunk($filasClasifGrupo, 500) as $bloque) {
            $conexion->table('clasificacion_asistente_reporte_grupo')->insert($bloque);
        }
        foreach (array_chunk($filasOfrendaGrupo, 500) as $bloque) {
            $conexion->table('ofrenda_grupos')->insert($bloque);
        }
    }

    /**
     * Configura las secciones y campos de Tiempo con Dios (sin depender de imágenes locales ausentes en Cloud)
     * y los campos, metas, hábitos y autoevaluaciones de Rueda de la Vida.
     *
     * @param  array<string, mixed>  $estructura
     */
    private function sembrarTiempoConDiosYRuedaDeLaVida(
        Connection $conexion,
        array $estructura,
        CarbonImmutable $ahora
    ): void {
        if (! SeccionTiempoConDios::query()->exists()) {
            $seccionesTcd = [
                ['nombre' => 'Sección 1', 'titulo_step' => 'Adoración y oración', 'titulo' => 'Conéctate con Dios', 'subtitulo' => 'Prepara tu corazón en adoración y gratitud antes de abrir las Escrituras.', 'orden' => 1, 'icono' => 'ti ti-music-heart'],
                ['nombre' => 'Sección 2', 'titulo_step' => 'Lee y reflexiona', 'titulo' => 'Deja que Dios te hable con Su Palabra', 'subtitulo' => 'Selecciona el pasaje bíblico de tu lectura devocional de hoy.', 'orden' => 2, 'icono' => 'ti ti-notebook'],
                ['nombre' => 'Sección 3', 'titulo_step' => 'PAMPE · Pecados', 'titulo' => 'Examina tu corazón a la luz de la Palabra', 'subtitulo' => 'Paso 1 del método devocional PAMPE.', 'orden' => 3, 'icono' => 'ti ti-writing'],
                ['nombre' => 'Sección 4', 'titulo_step' => 'PAMPE · Actitudes', 'titulo' => 'Renueva tus actitudes diarias', 'subtitulo' => 'Paso 2 del método devocional PAMPE.', 'orden' => 4, 'icono' => 'ti ti-writing'],
                ['nombre' => 'Sección 5', 'titulo_step' => 'PAMPE · Mandamientos', 'titulo' => 'Obediencia práctica a Dios', 'subtitulo' => 'Paso 3 del método devocional PAMPE.', 'orden' => 5, 'icono' => 'ti ti-writing'],
                ['nombre' => 'Sección 6', 'titulo_step' => 'PAMPE · Promesas', 'titulo' => 'Abraza las promesas del Señor', 'subtitulo' => 'Paso 4 del método devocional PAMPE.', 'orden' => 6, 'icono' => 'ti ti-writing'],
                ['nombre' => 'Sección 7', 'titulo_step' => 'PAMPE · Ejemplos', 'titulo' => 'Modelos bíblicos dignos de imitar', 'subtitulo' => 'Paso 5 del método devocional PAMPE.', 'orden' => 7, 'icono' => 'ti ti-writing'],
                ['nombre' => 'Sección 8', 'titulo_step' => 'Habla con Dios', 'titulo' => 'Tiempo íntimo de oración', 'subtitulo' => 'Presenta delante del Padre tu gratitud, confesión y peticiones.', 'orden' => 8, 'icono' => 'ti ti-speakerphone'],
                ['nombre' => 'Sección 9', 'titulo_step' => 'Aplicación diaria', 'titulo' => 'Toma nota y lleva la Palabra a la acción', 'subtitulo' => 'Registra tu compromiso práctico para hoy.', 'orden' => 9, 'icono' => 'ti ti-check'],
            ];

            $mapaSecIds = [];
            foreach ($seccionesTcd as $sec) {
                $creada = SeccionTiempoConDios::query()->create($sec);
                $mapaSecIds[$sec['orden']] = $creada->id;
            }

            CampoTiempoConDios::query()->create([
                'seccion_tiempo_con_dios_id' => $mapaSecIds[1],
                'tipo_campo_tiempo_con_dios_id' => 4,
                'nombre' => 'Reproductor instrumental',
                'class' => 'mb-3 col-12',
                'orden' => 1,
            ]);
            CampoTiempoConDios::query()->create([
                'seccion_tiempo_con_dios_id' => $mapaSecIds[2],
                'tipo_campo_tiempo_con_dios_id' => 5,
                'nombre' => 'Biblia',
                'name_id' => 'biblia_input',
                'class' => 'col-12',
                'orden' => 1,
            ]);

            $pampeCampos = [
                3 => ['name_id' => 'pecados', 'nombre' => 'Pecados', 'etiqueta' => 'Pecados o errores que debo confesar hoy'],
                4 => ['name_id' => 'actitudes', 'nombre' => 'Actitudes', 'etiqueta' => 'Actitudes del carácter de Cristo que debo adoptar'],
                5 => ['name_id' => 'mandamientos', 'nombre' => 'Mandamientos', 'etiqueta' => 'Mandamientos o instrucciones que debo obedecer'],
                6 => ['name_id' => 'promesas', 'nombre' => 'Promesas', 'etiqueta' => 'Promesas bíblicas para declarar sobre mi vida y familia'],
                7 => ['name_id' => 'ejemplos', 'nombre' => 'Ejemplos', 'etiqueta' => 'Ejemplos del pasaje que deseo imitar'],
            ];
            foreach ($pampeCampos as $ordenSec => $cfg) {
                CampoTiempoConDios::query()->create([
                    'seccion_tiempo_con_dios_id' => $mapaSecIds[$ordenSec],
                    'tipo_campo_tiempo_con_dios_id' => 1,
                    'nombre' => $cfg['nombre'],
                    'html' => '<h5 class="mb-2 fw-semibold">'.$cfg['etiqueta'].'</h5>',
                    'name_id' => $cfg['name_id'],
                    'placeholder' => 'Escribe aquí tu reflexión personal...',
                    'requerido' => true,
                    'class' => 'mb-3 col-12',
                    'orden' => 1,
                ]);
            }

            CampoTiempoConDios::query()->create([
                'seccion_tiempo_con_dios_id' => $mapaSecIds[8],
                'tipo_campo_tiempo_con_dios_id' => 2,
                'nombre' => 'Guía de oración',
                'class' => 'mb-3 col-12',
                'html' => '<div class="p-3 rounded-3 bg-light"><p class="mb-2"><strong>1. Adora:</strong> Reconoce quién es Dios y agradece Sus bondades.</p><p class="mb-2"><strong>2. Confiesa:</strong> Entrega tus cargas y recibe Su perdón.</p><p class="mb-0"><strong>3. Intercede:</strong> Ora por tu familia, tu grupo y tu iglesia.</p></div>',
                'orden' => 1,
            ]);
            CampoTiempoConDios::query()->create([
                'seccion_tiempo_con_dios_id' => $mapaSecIds[9],
                'tipo_campo_tiempo_con_dios_id' => 1,
                'nombre' => '¿Cómo aplicaré hoy lo aprendido?',
                'html' => '<h5 class="mb-2 fw-semibold">¿Qué habló Dios a tu corazón y cómo lo aplicarás hoy?</h5>',
                'name_id' => 'aplicacionDiaria',
                'placeholder' => 'Escribe tu compromiso práctico...',
                'requerido' => true,
                'class' => 'mb-3 col-12',
                'orden' => 1,
            ]);
        }

        // Registrar devocionales completados recientes para líderes y pastores
        $usuariosDevocional = array_merge(
            $estructura['pastoresPrincipalesIds'],
            array_slice($estructura['lideresGfmIds'], 0, 35)
        );
        foreach ($usuariosDevocional as $uIdx => $uid) {
            for ($d = 0; $d < 5; $d++) {
                TiempoConDios::query()->create([
                    'user_id' => $uid,
                    'fecha' => $ahora->subDays($d + ($uIdx % 2))->toDateString(),
                    'estado' => 'finalizado',
                    'paso_actual' => 9,
                    'modo' => 'propia',
                ]);
            }
        }

        // Rueda de la Vida: Campos por sección, metas, hábitos y autoevaluaciones de ejemplo
        if (! CampoSeccionRv::query()->exists()) {
            $camposPorSeccion = [
                1 => ['Oración diaria', 'Meditación bíblica', 'Ayuno', 'Adoración', 'Congregarme'],
                2 => ['Alimentación saludable', 'Ejercicio físico', 'Chequeo médico', 'Descanso adecuado', 'Cuidado personal'],
                3 => ['Lectura edificante', 'Formación continua', 'Servicio con mis talentos', 'Aprendizaje práctico', 'Mentoreo'],
                4 => ['Tiempo de calidad en familia', 'Altar familiar', 'Perdón y reconciliación', 'Comunicación asertiva', 'Honra familiar'],
                5 => ['Presupuesto mensual', 'Hábito de ahorro', 'Generosidad y diezmo', 'Libertad de deudas', 'Excelencia laboral'],
                6 => ['Identidad en Cristo', 'Dominio propio', 'Gestión de emociones', 'Paz interior', 'Relaciones sanas'],
            ];
            $colores = ['#008ffb', '#00e396', '#feb019', '#ff4560', '#775dd0'];

            foreach ($camposPorSeccion as $secId => $nombresCampos) {
                if (! $conexion->table('secciones_rv')->where('id', $secId)->exists()) {
                    continue;
                }
                foreach ($nombresCampos as $orden => $nombreCampo) {
                    CampoSeccionRv::query()->create([
                        'nombre' => $nombreCampo,
                        'abierto' => false,
                        'seccion_rv_id' => $secId,
                        'orden' => $orden + 1,
                        'color' => $colores[$orden % count($colores)],
                    ]);
                }
            }
        }

        if (! Metas::query()->exists() && $conexion->table('secciones_rv')->where('id', 8)->exists()) {
            foreach (['Meta Espiritual', 'Meta Física', 'Meta Intelectual', 'Meta Familiar', 'Meta Financiera', 'Meta Emocional'] as $nombreMeta) {
                $meta = Metas::query()->create([
                    'nombre' => $nombreMeta,
                    'descripcion' => 'Objetivo trimestral de crecimiento integral.',
                    'seccion_rv_id' => 8,
                    'requerido' => false,
                ]);
                HabitosRv::query()->create([
                    'nombre' => 'Hábito semanal de constancia',
                    'metas_id' => $meta->id,
                    'requerido' => false,
                ]);
            }
        }

        $todosCamposRvIds = CampoSeccionRv::query()->where('abierto', false)->pluck('id')->all();
        if ($todosCamposRvIds !== []) {
            $ts = $ahora->toDateTimeString();
            $filasValoresRv = [];
            foreach (array_slice($usuariosDevocional, 0, 20) as $idx => $uid) {
                $promedio = round(7.2 + (($idx % 5) * 0.4), 1);
                $rvUser = RuedaDeLaVidaUser::query()->create([
                    'usuario_id' => $uid,
                    'promedio_general' => $promedio,
                    'fecha' => $ahora->subDays($idx + 1)->toDateString(),
                ]);
                foreach ($todosCamposRvIds as $cIdx => $campoRvId) {
                    $filasValoresRv[] = [
                        'campos_seccion_rv_id' => $campoRvId,
                        'valor' => 6 + (($idx + $cIdx) % 5),
                        'rueda_de_la_vida_id' => $rvUser->id,
                        'created_at' => $ts,
                        'updated_at' => $ts,
                    ];
                }
            }
            foreach (array_chunk($filasValoresRv, 400) as $bloque) {
                $conexion->table('campo_rueda_de_la_vida')->insert($bloque);
            }
        }
    }

    /**
     * Siembra Cursos LMS, Hitos 3D, Publicaciones del Muro, Banners Generales, Temas,
     * Peticiones de Oración, Intercesores y Consejería Pastoral.
     *
     * @param  list<Sede>  $sedes
     * @param  array<string, mixed>  $estructura
     */
    private function sembrarCursosHitosMuroPeticionesYConsejeria(
        Connection $conexion,
        array $sedes,
        array $estructura,
        CarbonImmutable $ahora
    ): void {
        $autorId = $estructura['pastoresPrincipalesIds'][0] ?? 1;
        $monedaCopId = Moneda::query()->where('nombre_corto', 'COP')->value('id') ?? 1;
        $ts = $ahora->toDateTimeString();

        // 1. Categorías y 3 Cursos LMS completos con módulos, lecciones, quiz e inscritos
        if ($conexion->table('categorias_cursos')->count() === 0) {
            $conexion->table('categorias_cursos')->insert([
                ['nombre' => 'Biblia y Doctrina', 'descripcion' => 'Estudio sistemático de las Escrituras.', 'color' => '#4CAF50', 'created_at' => $ts, 'updated_at' => $ts],
                ['nombre' => 'Vida Espiritual', 'descripcion' => 'Crecimiento devocional y disciplinas espirituales.', 'color' => '#2196F3', 'created_at' => $ts, 'updated_at' => $ts],
                ['nombre' => 'Matrimonio y Familia', 'descripcion' => 'Herramientas prácticas para el hogar.', 'color' => '#E91E63', 'created_at' => $ts, 'updated_at' => $ts],
                ['nombre' => 'Mayordomía y Finanzas', 'descripcion' => 'Administración sabia de los recursos.', 'color' => '#FF9800', 'created_at' => $ts, 'updated_at' => $ts],
                ['nombre' => 'Liderazgo y Servicio', 'descripcion' => 'Formación de equipos ministeriales.', 'color' => '#9C27B0', 'created_at' => $ts, 'updated_at' => $ts],
            ]);
        }

        $categoriasCursosIds = $conexion->table('categorias_cursos')->pluck('id')->all();
        $carrerasIds = Carrera::query()->pluck('id')->all();
        $tiposPagoIds = TipoPago::query()->pluck('id')->all();
        $tipoCargoCursoId = TipoCargoCurso::query()->value('id') ?? 1;
        $tipoItemLecturaId = CursoItemTipo::query()->whereIn('codigo', ['lectura', 'texto', 'video'])->value('id')
            ?? CursoItemTipo::query()->value('id') ?? 1;
        $tipoItemQuizId = CursoItemTipo::query()->where('codigo', 'quiz')->value('id');

        $cursosDef = [
            ['nombre' => 'Fundamentos del Mentoreo Espiritual', 'nivel' => 'Principiante', 'gratis' => true, 'precio' => 0, 'dias' => 30],
            ['nombre' => 'Liderazgo Celular Transformador', 'nivel' => 'Intermedio', 'gratis' => false, 'precio' => 35000, 'dias' => 45],
            ['nombre' => 'Mayordomía y Finanzas en el Hogar', 'nivel' => 'Todas', 'gratis' => true, 'precio' => 0, 'dias' => 21],
        ];

        $alumnosCursos = array_slice($estructura['hermanosMayoresIds'], 100, 45);
        foreach ($cursosDef as $cIdx => $cDef) {
            $curso = Curso::query()->create([
                'nombre' => $cDef['nombre'],
                'slug' => Str::slug($cDef['nombre']).'-demo-'.($cIdx + 1),
                'descripcion_corta' => 'Programa virtual autogestionado con lecciones interactivas y acompañamiento.',
                'descripcion_larga' => '<p>Curso virtual diseñado para fortalecer el crecimiento bíblico y ministerial a tu propio ritmo desde el Campus Estudiantil.</p>',
                'nivel_dificultad' => $cDef['nivel'],
                'es_obligatorio' => $cIdx === 0,
                'estado' => 'Publicado',
                'orden_destacado' => $cIdx + 1,
                'duracion_estimada_dias' => $cDef['dias'],
                'carrera_id' => $carrerasIds[$cIdx % max(1, count($carrerasIds))] ?? null,
                'es_gratuito' => $cDef['gratis'],
                'precio' => $cDef['precio'],
                'moneda_id' => $monedaCopId,
                'genero' => 3,
                'vinculacion_grupo' => 1,
                'actividad_grupo' => 1,
                'excluyente' => false,
                'limite_reintentos' => 3,
                'dias_castigo' => 0,
            ]);

            if ($categoriasCursosIds !== []) {
                $curso->categorias()->sync([$categoriasCursosIds[$cIdx % count($categoriasCursosIds)]]);
            }
            if ($tiposPagoIds !== []) {
                $curso->tiposPago()->sync($tiposPagoIds);
            }

            for ($m = 1; $m <= 2; $m++) {
                $modulo = CursoModulo::query()->create([
                    'curso_id' => $curso->id,
                    'nombre' => 'Módulo '.$m.': Principios y Aplicación',
                    'descripcion' => 'Contenidos fundamentales y guía práctica del módulo.',
                    'orden' => $m,
                ]);

                for ($l = 1; $l <= 2; $l++) {
                    $leccion = CursoLeccion::query()->create([
                        'contenido_html' => '<p>Bienvenido a la lección '.$l.' del módulo '.$m.'. Estudia los principios bíblicos expuestos y realiza los ejercicios de aplicación personal.</p>',
                        'video_url' => $l === 1 ? 'https://www.youtube.com/watch?v=4thStWhovuw' : null,
                    ]);
                    CursoItem::query()->create([
                        'curso_modulo_id' => $modulo->id,
                        'curso_item_tipo_id' => $tipoItemLecturaId,
                        'titulo' => 'Lección '.$m.'.'.$l.': Desarrollo Temático',
                        'orden' => $l,
                        'itemable_id' => $leccion->id,
                        'itemable_type' => CursoLeccion::class,
                    ]);
                }

                if ($m === 1 && $tipoItemQuizId) {
                    $eval = CursoEvaluacion::query()->create([
                        'minimo_aprobacion' => 70,
                        'limite_tiempo' => 20,
                        'cantidad_repeticiones' => 3,
                        'tiempo_dilatacion' => 0,
                        'mostrar_respuestas_si_aprueba' => true,
                        'mostrar_respuestas_si_pierde' => false,
                    ]);
                    $preg = $eval->preguntas()->create([
                        'pregunta' => '¿Cuál es el propósito central del acompañamiento espiritual bíblico?',
                        'tipo_respuesta' => 'unica',
                        'orden' => 1,
                    ]);
                    $preg->opciones()->createMany([
                        ['opcion' => 'Formar el carácter de Cristo en cada creyente con amor y verdad', 'es_correcta' => true],
                        ['opcion' => 'Acumular únicamente información teórica sin práctica', 'es_correcta' => false],
                    ]);
                    CursoItem::query()->create([
                        'curso_modulo_id' => $modulo->id,
                        'curso_item_tipo_id' => $tipoItemQuizId,
                        'titulo' => 'Evaluación del Módulo 1',
                        'orden' => 3,
                        'itemable_id' => $eval->id,
                        'itemable_type' => CursoEvaluacion::class,
                    ]);
                }
            }

            $conexion->table('curso_usuario_cargo')->insert([
                'curso_id' => $curso->id,
                'usuario_id' => $autorId,
                'tipo_cargo_curso_id' => $tipoCargoCursoId,
                'activo' => true,
                'created_at' => $ts,
                'updated_at' => $ts,
            ]);

            foreach (array_slice($alumnosCursos, $cIdx * 15, 15) as $aIdx => $alumnoId) {
                $conexion->table('curso_users')->insert([
                    'curso_id' => $curso->id,
                    'user_id' => $alumnoId,
                    'estado' => 'activo',
                    'fecha_inscripcion' => $ahora->subDays(20 - $aIdx)->toDateTimeString(),
                    'porcentaje_progreso' => $aIdx === 0 ? 100 : (25 + (($aIdx * 15) % 75)),
                    'numero_reintentos' => 0,
                    'created_at' => $ts,
                    'updated_at' => $ts,
                ]);
            }
        }

        // 2. Hitos Congregacionales (Muro / Túnel del Tiempo)
        $tipoHitoId = TipoHito::query()->value('id');
        if ($tipoHitoId) {
            $hitos = [
                ['titulo' => 'Aniversario Congregacional: Fidelidad de Generación en Generación', 'fecha' => $ahora->subMonths(2)->toDateString()],
                ['titulo' => 'Graduación Academia Manantial de Vida Eterna', 'fecha' => $ahora->subMonths(5)->toDateString()],
                ['titulo' => 'Gran Jornada de Bautismos en Agua', 'fecha' => $ahora->subMonths(8)->toDateString()],
                ['titulo' => 'Apertura de Nuevas Sedes Regionales y Campus Online', 'fecha' => $ahora->subMonths(14)->toDateString()],
                ['titulo' => 'Congreso Anual de Familias con Propósito', 'fecha' => $ahora->subMonths(20)->toDateString()],
                ['titulo' => 'Multiplicación de 100 Grupos de Crecimiento', 'fecha' => $ahora->subMonths(28)->toDateString()],
            ];
            foreach ($hitos as $h) {
                Hito::query()->create([
                    'tipo_hito_id' => $tipoHitoId,
                    'user_id' => $autorId,
                    'titulo' => $h['titulo'],
                    'descripcion' => 'Un momento histórico de fe, unidad y crecimiento para toda nuestra comunidad.',
                    'mensaje_usuario' => '¡Gracias por ser parte viva de lo que Dios está edificando!',
                    'fecha_evento' => $h['fecha'],
                    'requiere_asistencia' => false,
                    'permite_fotos_usuario' => true,
                    'max_fotos_usuario' => 4,
                    'max_peso_kb' => 5120,
                    'requiere_sesion' => false,
                    'activo' => true,
                ]);
            }
        }

        // 3. Banners Generales, Temas de Enseñanza y Publicaciones en el Muro
        foreach ([
            ['nombre' => 'Bienvenidos a nuestra comunidad de fe', 'link' => null],
            ['nombre' => 'Inscripciones abiertas · Próximas Actividades', 'link' => '/actividades/proximas-actividades'],
            ['nombre' => 'Explora nuestros Planes Lectores Devocionales', 'link' => '/planes-lectores'],
        ] as $b) {
            BannerGeneral::query()->create([
                'imagen' => 'banner-default.jpg',
                'nombre' => $b['nombre'],
                'fecha_inicio' => $ahora->subDays(30)->toDateString(),
                'fecha_fin' => $ahora->addMonths(6)->toDateString(),
                'link' => $b['link'],
                'visible' => true,
            ]);
        }

        $catTema = CategoriaTema::query()->create(['nombre' => 'Enseñanza Semanal de Grupos']);
        $sedesIds = array_map(fn (Sede $s): int => (int) $s->id, $sedes);
        $tiposUsuariosVisiblesIds = TipoUsuario::query()->where('visible', true)->pluck('id')->all();

        foreach ([
            'El Poder de un Hogar Unido en Oración',
            'Fidelidad en lo Poco: Mayordomía del Reino',
            'Cómo Discipular con Gracia y Verdad',
            'Venciendo la Ansiedad con las Promesas de Dios',
        ] as $tituloTema) {
            $tema = Tema::query()->create([
                'titulo' => $tituloTema,
                'portada' => 'default.png',
                'estado' => true,
                'contenido' => '<p>Guía pastoral para compartir durante la reunión semanal de grupos de crecimiento. Incluye preguntas de diálogo, lectura bíblica central y aplicación familiar.</p>',
            ]);
            $conexion->table('temas_categorias')->insert(['tema_id' => $tema->id, 'categoria_tema_id' => $catTema->id, 'created_at' => $ts, 'updated_at' => $ts]);
            foreach ($sedesIds as $sid) {
                $conexion->table('sedes_temas')->insert(['tema_id' => $tema->id, 'sede_id' => $sid, 'created_at' => $ts, 'updated_at' => $ts]);
            }
            foreach ($tiposUsuariosVisiblesIds as $tuId) {
                $conexion->table('tipos_usuarios_temas')->insert(['tema_id' => $tema->id, 'tipo_usuario_id' => $tuId, 'created_at' => $ts, 'updated_at' => $ts]);
            }
        }

        $publicaciones = [
            ['desc' => '<p><strong>¡Bienvenidos a casa!</strong> Cada semana cientos de familias se reúnen en nuestros grupos de crecimiento para compartir la Palabra y edificarse mutuamente.</p>', 'video' => null],
            ['desc' => '<p>Prepárate para nuestro próximo <strong>Ayuno Congregacional: Un Nuevo Comienzo</strong>. Tres días buscando juntos la dirección de Dios.</p>', 'video' => 'https://www.youtube.com/watch?v=4thStWhovuw'],
            ['desc' => '<p>Ya están disponibles los <strong>30 Planes Lectores devocionales</strong> para fortalecer tu tiempo diario con Dios.</p>', 'video' => null],
            ['desc' => '<p>La <strong>Escuela Manantial de Vida Eterna</strong> cuenta con su malla curricular completa lista para el próximo ciclo de formación.</p>', 'video' => null],
        ];
        foreach ($publicaciones as $pIdx => $pub) {
            $post = Post::query()->create([
                'user_id' => $autorId,
                'image_path' => null,
                'descripcion' => $pub['desc'],
                'video_url' => $pub['video'],
                'fecha_inicio' => $ahora->subDays(10 - ($pIdx * 2))->toDateString(),
                'fecha_fin' => $ahora->addMonths(6)->toDateString(),
                'visualizar_siempre' => true,
                'visible_todos' => true,
                'genero' => 3,
            ]);
            foreach (array_slice($estructura['lideresGfmIds'], $pIdx * 10, 12) as $uidLike) {
                $conexion->table('post_user_likes')->insert([
                    'post_id' => $post->id,
                    'user_id' => $uidLike,
                    'created_at' => $ts,
                    'updated_at' => $ts,
                ]);
            }
        }

        // 4. Intercesores, Peticiones de Oración, Consejeros y Citas de Consejería
        $tiposPeticionIds = TipoPeticion::query()->pluck('id')->all() ?: [1];
        $paisColombiaId = Pais::query()->where('nombre', 'ilike', '%Colombia%')->value('id') ?? 1;

        foreach ($estructura['intercesoresUsersIds'] as $iUid) {
            $intercesor = Intercesor::query()->create([
                'user_id' => $iUid,
                'solo_peticiones_asignadas' => '0',
                'ver_peticiones_de_invitados' => '1',
                'activo' => true,
                'descripcion' => 'Integrante del equipo ministerial de intercesión.',
            ]);
            foreach (array_slice($sedesIds, 0, 4) as $sid) {
                $conexion->table('intercesor_sede')->insert(['intercesor_id' => $intercesor->id, 'sede_id' => $sid, 'created_at' => $ts, 'updated_at' => $ts]);
            }
            foreach ($tiposPeticionIds as $tpId) {
                $conexion->table('intercesor_tipo_peticion')->insert(['intercesor_id' => $intercesor->id, 'tipo_peticion_id' => $tpId, 'created_at' => $ts, 'updated_at' => $ts]);
            }
        }

        $textosPeticiones = [
            'Oración por restauración y unidad en nuestro matrimonio y sabiduría en la crianza de nuestros hijos.',
            'Petición por provisión laboral y apertura de puertas profesionales en esta nueva etapa.',
            'Clamor por sanidad física completa y fortaleza para nuestra familia durante recuperación médica.',
            'Dirección de Dios para decisiones familiares y crecimiento espiritual constante.',
            'Acción de gracias y cobertura sobre el emprendimiento familiar que iniciamos este mes.',
        ];
        foreach (array_slice($estructura['hermanosMayoresIds'], 200, 45) as $idx => $uidPet) {
            $atendida = ($idx % 3 !== 0);
            Peticion::query()->create([
                'user_id' => $uidPet,
                'tipo_peticion_id' => $tiposPeticionIds[$idx % count($tiposPeticionIds)],
                'estado' => $atendida ? 2 : 1,
                'descripcion' => $textosPeticiones[$idx % count($textosPeticiones)],
                'respuesta' => $atendida ? 'Nuestro equipo de intercesión ha estado orando por tu petición declarando la paz y provisión del Señor.' : null,
                'fecha' => $ahora->subDays(($idx % 25) + 1)->toDateString(),
                'pais_id' => $paisColombiaId,
                'autor_creacion_id' => $uidPet,
                'asignacion_peticion_id' => $estructura['intercesoresUsersIds'][$idx % count($estructura['intercesoresUsersIds'])] ?? null,
            ]);
        }

        $tiposConsejeriaIds = TipoConsejeria::query()->pluck('id')->all() ?: [1];
        $consejerosIds = [];
        foreach ($estructura['consejerosUsersIds'] as $cUid) {
            $consejero = Consejero::query()->create([
                'user_id' => $cUid,
                'activo' => true,
                'descripcion' => 'Consejero pastoral para atención familiar, prematrimonial y espiritual.',
                'atencion_presencial' => true,
                'direccion' => $sedes[0]->direccion,
                'atencion_virtual' => true,
                'duracion_cita_minutos' => 45,
                'buffer_entre_citas_minutos' => 15,
                'dias_minimos_antelacion' => 1,
                'dias_maximos_futuro' => 30,
            ]);
            $consejerosIds[] = $consejero->id;
            $conexion->table('consejero_sede')->insert(['consejero_id' => $consejero->id, 'sede_id' => $sedes[0]->id, 'created_at' => $ts, 'updated_at' => $ts]);
            foreach ($tiposConsejeriaIds as $tcId) {
                $conexion->table('consejero_tipo_consejeria')->insert(['consejero_id' => $consejero->id, 'tipo_consejeria_id' => $tcId, 'created_at' => $ts, 'updated_at' => $ts]);
            }
        }

        if ($consejerosIds !== []) {
            foreach (array_slice($estructura['hermanosMenoresIds'], 0, 12) as $idx => $pacienteId) {
                $esPasada = $idx < 7;
                $inicio = $esPasada
                    ? $ahora->subDays(12 - $idx)->setTime(15, 0)
                    : $ahora->addDays($idx)->setTime(16, 0);
                $conexion->table('citas_consejeria')->insert([
                    'user_id' => $pacienteId,
                    'consejero_id' => $consejerosIds[$idx % count($consejerosIds)],
                    'tipo_consejeria_id' => $tiposConsejeriaIds[$idx % count($tiposConsejeriaIds)],
                    'medio' => ($idx % 2) + 1,
                    'enlace_virtual' => ($idx % 2 === 1) ? 'https://meet.google.com/demo-consejeria' : null,
                    'fecha_hora_inicio' => $inicio->toDateTimeString(),
                    'fecha_hora_fin' => $inicio->addMinutes(45)->toDateTimeString(),
                    'notas_paciente' => 'Solicitud de orientación pastoral y acompañamiento espiritual.',
                    'concluida' => $esPasada,
                    'conclusiones_consejero' => $esPasada ? 'Se brindó orientación bíblica, oración y plan de lectura semanal.' : null,
                    'created_at' => $ts,
                    'updated_at' => $ts,
                ]);
            }
        }
    }

    /**
     * Sincroniza todas las secuencias serial/bigserial del esquema activo en PostgreSQL.
     */
    private function sincronizarSecuencias(Connection $conexion): void
    {
        $columnas = $conexion->select("select table_name, column_name from information_schema.columns where table_schema = current_schema() and column_default like 'nextval(%'");
        $gramatica = $conexion->getQueryGrammar();

        foreach ($columnas as $columna) {
            $tabla = $gramatica->wrapTable($columna->table_name);
            $campo = $gramatica->wrap($columna->column_name);
            $conexion->selectOne(
                "select setval(pg_get_serial_sequence(?, ?), greatest(coalesce(max({$campo}), 1), 1), count(*) > 0) from {$tabla}",
                [$columna->table_name, $columna->column_name]
            );
        }
    }
}
