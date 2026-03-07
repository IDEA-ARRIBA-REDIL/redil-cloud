<?php

namespace Database\Seeders;

use App\Models\TipoFormatoDireccion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoFormatoDireccionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      $tiposDeDirecciones = '[
        {"nombre":"Aeropuerto"},
        {"nombre":"Agrupación"},
        {"nombre":"Anillo"},
        {"nombre":"Apartado"},
        {"nombre":"Aéreo"},
        {"nombre":"Apartamento"},
        {"nombre":"Autopista"},
        {"nombre":"Avenida"},
        {"nombre":"Base"},
        {"nombre":"Batallón"},
        {"nombre":"Bis"},
        {"nombre":"Bloque"},
        {"nombre":"Bodega"},
        {"nombre":"Brigada"},
        {"nombre":"Cabaña"},
        {"nombre":"Calle"},
        {"nombre":"Cantón"},
        {"nombre":"Cárcel"},
        {"nombre":"Carrera"},
        {"nombre":"Carretera"},
        {"nombre":"Casa"},
        {"nombre":"Célula"},
        {"nombre":"Centro"},
        {"nombre":"Circunvalar"},
        {"nombre":"Circular"},
        {"nombre":"Ciudadela"},
        {"nombre":"Colegio"},
        {"nombre":"Comando"},
        {"nombre":"Comercial"},
        {"nombre":"Comunidad"},
        {"nombre":"Conjunto"},
        {"nombre":"Consultorio"},
        {"nombre":"Diagonal"},
        {"nombre":"Edificio"},
        {"nombre":"Entrada"},
        {"nombre":"Esquina"},
        {"nombre":"Establecimiento"},
        {"nombre":"Estación"},
        {"nombre":"Este"},
        {"nombre":"Etapa"},
        {"nombre":"Fabrica"},
        {"nombre":"Ferrocarril"},
        {"nombre":"Finca"},
        {"nombre":"Garaje"},
        {"nombre":"Gobernación"},
        {"nombre":"Hacienda"},
        {"nombre":"Hangar"},
        {"nombre":"Hospital"},
        {"nombre":"Inspección"},
        {"nombre":"Inspección"},
        {"nombre":"Instituto"},
        {"nombre":"Interior"},
        {"nombre":"Islas"},
        {"nombre":"Jardín"},
        {"nombre":"Kilometro"},
        {"nombre":"Local"},
        {"nombre":"Lote"},
        {"nombre":"Manzana"},
        {"nombre":"Mina"},
        {"nombre":"Ministerio"},
        {"nombre":"Modulo"},
        {"nombre":"Muelle"},
        {"nombre":"Multifamiliar"},
        {"nombre":"Nor Este"},
        {"nombre":"Norte"},
        {"nombre":"Oficina"},
        {"nombre":"Palacio"},
        {"nombre":"Parque"},
        {"nombre":"Pasaje"},
        {"nombre":"Peatonal"},
        {"nombre":"Piso"},
        {"nombre":"Plaza"},
        {"nombre":"Plazoleta"},
        {"nombre":"Puente"},
        {"nombre":"Puerto"},
        {"nombre":"Sector"},
        {"nombre":"Sede"},
        {"nombre":"Supermanzana"},
        {"nombre":"Sur"},
        {"nombre":"Sur Este"},
        {"nombre":"Terminal"},
        {"nombre":"Torre"},
        {"nombre":"Transversal"},
        {"nombre":"Unidad"},
        {"nombre":"Universidad"},
        {"nombre":"Urbanización"},
        {"nombre":"Vía"},
        {"nombre":"Zona"},
        {"nombre":"Callejón"}
      ]';

      $items = json_decode($tiposDeDirecciones);
      foreach ($items as $item) {
        TipoFormatoDireccion::firstOrCreate([
          'nombre' => $item->nombre,
        ]);
      }
    }
}
