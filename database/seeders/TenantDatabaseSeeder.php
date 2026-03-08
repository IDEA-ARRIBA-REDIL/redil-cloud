<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\HorarioHabitual;
use App\Models\Informe;
use App\Models\ReporteReunion;
use App\Models\SemanaDeshabilitadas;
use App\Models\TipoEgreso;
use App\Models\TipoInasistencia;
use App\Models\TipoInforme;
use Illuminate\Database\Seeder;


class TenantDatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // Solo ejecutamos los seeders base si estamos en entorno local.
    // En el servidor del cliente (producción), solo se ejecutarán los seeders incrementales.
    if (app()->environment('local')) {
      $this->baseSeeders();
    }

    $this->incrementalSeeders();
  }

  /**
   * Seeders base iniciales (Estado actual de la DB).
   */
  protected function baseSeeders(): void
  {
    $this->call(TipoUsuarioSeeder::class);
    $this->call(EstadoCivilSeeder::class);
    $this->call(SedeSeeder::class);
    $this->call([
      RoleSeeder::class,
      PermisoSeeder::class,
      UserSeeder::class,
    ]);
    $this->call(ConfiguracionSeeder::class);
    $this->call(ReporteBajaAltaSeeder::class);
    $this->call(TipoBajaAltaSeeder::class);
    $this->call(TipoGrupoSeeder::class);
    $this->call(GrupoSeeder::class);
    $this->call(TipoServicioGrupoSeeder::class);
    $this->call(ServidorGrupoSeeder::class);
    $this->call(ServicioServidorGrupoSeeder::class);
    $this->call(FormularioUsuarioSeeder::class);
    $this->call(IglesiaSeeder::class);
    $this->call(TipoSedeSeeder::class);
    $this->call(GrupoExcluidoSeeder::class);
    $this->call(RangoEdadSeeder::class);
    $this->call(TipoVinculacionSeeder::class);
    $this->call(PasoCrecimientoSeeder::class);
    $this->call(CrecimientoUsuarioSeeder::class);
    $this->call(OcupacionSeeder::class);
    $this->call(NivelAcademicoSeeder::class);
    $this->call(EstadoNivelAcademicoSeeder::class);
    $this->call(ProfesionSeeder::class);
    $this->call(CampoInformeExcelSeeder::class);
    $this->call(CampoExtraSeeder::class);
    $this->call(TipoIdentificacionSeeder::class);
    $this->call(TipoSangreSeeder::class);
    $this->call(SectorEconomicoSeeder::class);
    $this->call(TipoViviendaSeeder::class);
    $this->call(PaisSeeder::class);
    $this->call(TipoParentescoSeeder::class);
    $this->call(ParienteUsuarioSeeder::class);
    $this->call(ClasificacionAsistenteSeeder::class);
    $this->call(ReunionesSeeder::class);
    $this->call(ReporteReunionSeeder::class);
    $this->call(ReporteGrupoSeeder::class);
    $this->call(PeticionSeeder::class);
    $this->call(TipoPeticionSeeder::class);
    $this->call(ContinenteSeeder::class);
    $this->call(DepartamentoSeeder::class);
    $this->call(RegionSeeder::class);
    $this->call(MunicipioSeeder::class);
    $this->call(LocalidadSeeder::class);
    $this->call(BarrioSeeder::class);
    $this->call(TipoFormatoDireccionSeeder::class);
    $this->call(EstadoPasoCrecimientoUsuarioSeeder::class);
    $this->call(TipoAsignacionSeeder::class);
    $this->call(TemaSeeder::class);
    $this->call(CategoriaTemaSeeder::class);
    $this->call(CampoExtraGrupoSeeder::class);
    $this->call(ReporteGrupoBajaAltaSeeder::class);
    $this->call(TemaCategoriaSeeder::class);
    $this->call(SeccionPasoCrecimientoSeeder::class);
    $this->call(DestinatarioSeeder::class);
    $this->call(TipoPagoSeeder::class);
    $this->call(TipoActividadSeeder::class);
    $this->call(ActividadSeeder::class);

    $this->call(MonedaSeeder::class);
    $this->call(CamposAdicionalesActividadSeeder::class);

    ////BLOUE MIGRATIONS MANANTIAL
    //$this->call(GruposYMiembrosSeeder::class);
    //$this->call(UserFromTxtSeeder::class);
    //$this->call(TipoUsuarioManantialSeeder::class);
    //$this->call(GruposYMiembrosSeeder::class);
    //$this->call(EncargadosGrupoSeeder::class);
    //$this->call(IntegrantesGrupoSeeder::class);
    ////

    $this->call(ActividadCategoriaMonedaSeeder::class);
    $this->call(ThemeSettingSeeder::class);
    $this->call(CampoPerfilUsuarioSeeder::class);
    $this->call(AbonoSeeder::class);
    $this->call(AbonoCategoriaSeeder::class);
    $this->call(SeccionFormularioUsuarioSeeder::class);
    $this->call(CampoFormularioUsuarioSeeder::class);
    $this->call(TipoElementoFormularioActividadSeeder::class);
    $this->call(ElementoFormularioActividadSeeder::class);
    $this->call(TipoCargoActividadSeeder::class);
    $this->call(TipoFormularioUsuarioSeeder::class);
    $this->call(ActividadEncargadoSeeder::class);

    $this->call(ProductoSeeder::class);
    $this->call(SeccionRvSeeder::class);
    $this->call(TipoSeccionRvSeeder::class);
    $this->call(CampoSeccionRvSeeder::class);
    $this->call(ConfiguracionRvSeeder::class);
    $this->call(MetasSeeder::class);
    $this->call(HabitosRvSeeder::class);
    $this->call(TipoOfrendaSeeder::class);
    $this->call(RuedaDeLaVidaUserSeeder::class);
    $this->call(TagGeneralSeeder::class);
    $this->call(TiempoConDiosSeeder::class);
    $this->call(CancionSeeder::class);
    $this->call(AlbumSeeder::class);
    $this->call(SeccionTiempoConDiosSeeder::class);
    $this->call(CampoTiempoConDiosSeeder::class);
    $this->call(TipoCampoTiempoConDiosSeeder::class);
    $this->call(CompraSeeder::class);
    $this->call(CategoriaActividadCompraSeeder::class);
    $this->call(PagoSeeder::class);
    $this->call(ActividadCarritoCompraSeeder::class);
    $this->call(SedeDestinatarioSeeder::class);
    $this->call(NivelEscuelaSeeder::class);

    ///escuelas
    $this->call([
      EscuelaSeeder::class,
      MateriaSeeder::class,
      PeriodoSeeder::class,
      MateriaPeriodoSeeder::class,
      CortePeriodoSeeder::class,
      TipoAulaSeeder::class,
      AulaSeeder::class,
      HorarioBaseSeeder::class,
      PrerequisitoSeeder::class,
      PrerequisitoPasoSeeder::class,
      PrerequisitoNivelSeeder::class,
      PrerequisitoMateriasSeeder::class,
      MateriasAprobadasUsuarioSeeder::class,
      MateriasAprobadasUsuarioSeeder::class,
      CortesEscuelaSeeder::class,
      EscuelaConsolidacionSeeder::class

    ]);
    $this->call(HorarioMateriaPeriodoSeeder::class);
    $this->call(ActividadCategoriaSeeder::class);
    $this->call(OfrendaSeeder::class);
    $this->call(DocumentoEquivalenteSeeder::class);
    $this->call(TipoEgresoSeeder::class);
    $this->call(ProveedorSeeder::class);
    $this->call(CajaFinanzasSeeder::class);
    $this->call(IngresoSeeder::class);
    $this->call(EgresoSeeder::class);
    $this->call(PuntoDePagoSeeder::class);
    $this->call(CajaSeeder::class);
    $this->call(TipoEgresoSeeder::class);
    $this->call(MotivoNoReporteGrupoSeeder::class);
    $this->call(CalificacionesSeeder::class);
    $this->call(SistemaCalificacionSeeder::class);
    $this->call(TipoItemSeeder::class);
    $this->call(GeneralEscuelaSeeder::class);
    $this->call(MotivoInasistenciaSeeder::class);
    $this->call(MotivoDesaprobacionReporteGrupoSeeder::class);
    $this->call(TipoInasistenciaSeeder::class);

    $this->call(TipoInformeSeeder::class);
    $this->call(InformeSeeder::class);
    $this->call(SemanaDeshabilitadaSeeder::class);
    $this->call(EstadoPagoSeeder::class);
    $this->call(InscripcionSeeder::class);
    $this->call(ReservaReunionSeeder::class);
    $this->call(CentroDeCostosIngresosSeeder::class);
    $this->call(CentroDeCostosEgresosSeeder::class);
    //$this->call(PerformanceTestSeeder::class);

    $this->call(ZonaSeeder::class);

    $this->call(TareaConsolidacionSeeder::class);
    $this->call(EstadoTareaConsolidacionSeeder::class);
    $this->call(TareaConsolidacionUsuarioSeeder::class);
    $this->call(HistorialTareaConsolidacionUsuarioSeeder::class);
    $this->call(FiltroConsolidacionSeeder::class);
    $this->call(ConsejeroSeeder::class);
    $this->call(TipoConsejeriaSeeder::class);
    $this->call(HorarioHabitualSeeder::class);
    $this->call(HorarioBloqueadoConsejeroSeeder::class);
    $this->call(HorarioAdicionalConsejeroSeeder::class);
    //$this->call(PruebasPdpCajaSeeder::class);
    $this->call(ActividadesManantialSeeder::class);
    $this->call(TareaConsolidacionTipoConsejeriaSeeder::class);
    $this->call(CitaConsejeriaSeeder::class);
    $this->call(PasoCrecimientoTipoConsejeriaSeeder::class);
    $this->call(BannerGeneralSeeder::class);
    $this->call(InformeEvidenciasGrupoSeeder::class);
    $this->call(BitacoraTipoUsuarioSeeder::class);
    $this->call(BitacoraEstadoCivilSeeder::class);
    $this->call(BloquesDashboardConsolidacionSeeder::class);
  }

  /**
   * Seeders incrementales (Nuevos a partir de hoy). 2026
   * Agrega aquí los nuevos seeders para que se ejecuten en todos los entornos. nuevos no alterar los de arriba
   */
  protected function incrementalSeeders(): void
  {
    // $this->call(NuevoSeederEjemplo::class); TipoCargoCursoSeeder
    $this->call(BloqueClasificacionAsistenteSeeder::class);
    $this->call(EstructuraGruposPruebaSeeder::class);
    $this->call(CursoItemTipoSeeder::class);
    $this->call(TipoCargoCursoSeeder::class);
    $this->call(CursoDemoSeeder::class);
    $this->call(CarreraSeeder::class);
    $this->call(CategoriaCursoSeeder::class);
    $this->call(VersiculoDiarioSeeder::class);
  }
}
