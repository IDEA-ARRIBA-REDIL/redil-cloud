<?php

namespace Database\Seeders;

use App\Models\Role as ModelsRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // RolAdmistrador
    $superAdmin = Role::firstOrCreate(['name' => 'Super Administrador'], ['icono' => 'ti ti-key', 'dependiente' => false]);

    // creo relacion create_privilegios_tipo_grupo_rol
    // Usamos la variable $superAdmin en lugar de find(1)
    if($superAdmin->wasRecentlyCreated) {
        ModelsRole::find($superAdmin->id)->privilegiosTiposGrupo()->attach(3, ['asignar_asistente' => false, 'desvincular_asistente' => true, 'asignar_encargado' => false, 'desvincular_encargado' => true]);
        ModelsRole::find($superAdmin->id)->privilegiosTiposGrupo()->attach(4, ['asignar_asistente' => true, 'desvincular_asistente' => false, 'asignar_encargado' => true, 'desvincular_encargado' => false]);
    }

    // RolPastor
    $pastor = Role::firstOrCreate(['name' => 'Pastor'], ['icono' => 'ti ti-user-shield', 'dependiente' => true]);

    // RolLider
    $lider = Role::firstOrCreate(['name' => 'Lider'], ['icono' => 'ti ti-user-star', 'dependiente' => true]);

    // creo relacion create_privilegios_tipo_grupo_rol
    // Usamos la variable $lider en lugar de find(3)
    if($lider->wasRecentlyCreated) {
        ModelsRole::find($lider->id)->privilegiosTiposGrupo()->attach(2, ['asignar_asistente' => true, 'desvincular_asistente' => true, 'asignar_encargado' => false, 'desvincular_encargado' => false]);
    }

    // RolOveja
    $oveja = Role::firstOrCreate(['name' => 'Oveja'], ['icono' => 'ti ti-mood-heart', 'dependiente' => true]);

    // RolNuevo
    $nuevo = Role::firstOrCreate(['name' => 'Nuevo'], ['icono' => 'ti ti-paper-bag', 'dependiente' => true]);

    // RolEmpleado (Corregí esto, en tu original creabas 'Oveja' dos veces)
    $empleado = Role::firstOrCreate(['name' => 'Empleado'], ['icono' => 'ti ti-brand-ctemplar', 'dependiente' => true]);

    // RolDesarrollador
    $desarrollador = Role::firstOrCreate(['name' => 'Desarrollador'], ['icono' => 'ti ti-anchor', 'dependiente' => true]);

    // RolDP
    $pdp = Role::firstOrCreate(['name' => 'PDP'], ['icono' => 'ti ti-paperclip', 'dependiente' => false, 'es_encargado_pdp' => true]);
    $cajero = Role::firstOrCreate(['name' => 'Cajero PDP'], ['icono' => 'ti ti-paperclip', 'dependiente' => false, 'es_cajero_pdp' => true]);

    /// roles para escuelas
    $alumno = Role::firstOrCreate(['name' => 'Alumno'], ['icono' => 'ti ti-user-square-rounded', 'dependiente' => false]);
    $maestro = Role::firstOrCreate(['name' => 'Maestro'], ['icono' => 'ti ti-user-square', 'dependiente' => false]);
    $coordinador = Role::firstOrCreate(['name' => 'Coordinador'], ['icono' => 'ti ti ti-user-pentagon', 'dependiente' => false]);
    $administrador = Role::firstOrCreate(['name' => 'Administrativo'], ['icono' => 'ti ti ti-user-pentagon', 'dependiente' => false]);

    $consejero = Role::firstOrCreate(['name' => 'Consejero'], ['icono' => 'ti ti ti-message-circle-user', 'dependiente' => false, 'es_consejero' => true]);


    // roles consolidacion
    $consolidadorMedellin = Role::firstOrCreate(['name' => 'Consolidador Medellin'], ['icono' => 'ti ti ti-user', 'dependiente' => false, 'zona_de_consolidacion_id' => 5]);
    $consolidadorBogota = Role::firstOrCreate(['name' => 'Consolidador Bogota'], ['icono' => 'ti ti ti-user', 'dependiente' => false, 'zona_de_consolidacion_id' => 6]);
  }
}
