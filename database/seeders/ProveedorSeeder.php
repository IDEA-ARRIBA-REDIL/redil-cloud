<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // PROVEEDORES
    $proveedorx = Proveedor::firstOrCreate(['identificacion' => '1234567890'], [
      'nombre' => 'Proveedor X',
      'tipo_identificacion' => 'NIT',
      'telefono' => '3111234567',
      'direccion' => 'Calle 123 #45-67',
      'correo' => 'proveedorx@example.com'
    ]);
    $proveedory = Proveedor::firstOrCreate(['identificacion' => '3214567890'], [
      'nombre' => 'Proveedor Y',
      'tipo_identificacion' => 'NIT',
      'telefono' => '3111234567',
      'direccion' => 'Calle 321 #45-67',
      'correo' => 'proveedory@example.com'
    ]);
    $proveedorz = Proveedor::firstOrCreate(['identificacion' => '4324567890'], [
      'nombre' => 'Proveedor Z',
      'tipo_identificacion' => 'NIT',
      'telefono' => '3111234567',
      'direccion' => 'Calle 321 #45-67',
      'correo' => 'proveedorz@example.com'
    ]);
  }
}
