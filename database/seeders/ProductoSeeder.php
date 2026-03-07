<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $productos = [
      [
        'nombre' => 'Laptop Gamer',
        'descripcion' => 'Laptop con procesador Intel i7 y 16GB de RAM.',
        'precio' => 1200.50,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'nombre' => 'Teléfono Inteligente',
        'descripcion' => 'Smartphone con pantalla AMOLED y cámara de 108MP.',
        'precio' => 800.99,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'nombre' => 'Auriculares Inalámbricos',
        'descripcion' => 'Auriculares Bluetooth con cancelación de ruido.',
        'precio' => 150.00,
        'created_at' => now(),
        'updated_at' => now(),
      ],
    ];

    foreach ($productos as $producto) {
      Producto::firstOrCreate(
        ['nombre' => $producto['nombre']],
        $producto
      );
    }
  }
}
