<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\TipoIdentificacion;

class TipoIdentificacionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {

      $tiposDeIdentificaciones = '[
        {"id":"1","abreviatura":"RC","nombre":"Registro civil","formularioDonacion":"si"},
        {"id":"2","abreviatura":"TI","nombre":"Tarjeta de identificación","formularioDonacion":"si"},
        {"id":"3","abreviatura":"CC","nombre":"Cédula de ciudadanía","formularioDonacion":"si"},
        {"id":"4","abreviatura":"CE","nombre":"Cédula extranjería","formularioDonacion":"si"},
        {"id":"5","abreviatura":"LM","nombre":"Licencia de manejo","formularioDonacion":"no"},
        {"id":"6","abreviatura":"PP","nombre":"Pasaporte","formularioDonacion":"si"},
        {"id":"7","abreviatura":"MC","nombre":"Matrícula consular","formularioDonacion":"no"},
        {"id":"8","abreviatura":"NIE","nombre":"NIE","formularioDonacion":"no"},
        {"id":"9","abreviatura":"DNI","nombre":"DNI","formularioDonacion":"si"},
        {"id":"10","abreviatura":"PP","nombre":"Pasaporte","formularioDonacion":"no"},
        {"id":"11","abreviatura":"CURP","nombre":"CURP","formularioDonacion":"no"},
        {"id":"12","abreviatura":"DPI","nombre":"DPI","formularioDonacion":"no"},
        {"id":"13","abreviatura":"INE","nombre":"INE","formularioDonacion":"no"},
        {"id":"14","abreviatura":"RUT","nombre":"RUT","formularioDonacion":"no"},
        {"id":"15","abreviatura":"FOLIO","nombre":"FOLIO","formularioDonacion":"no"},
        {"id":"16","abreviatura":"CI","nombre":"Cédula de identidad","formularioDonacion":"no"},
        {"id":"17","abreviatura":"NIT","nombre":"Nit Empresa","formularioDonacion":"si"},
        {"id":"18","abreviatura":"DIE","nombre":"Documento de identificación extranjero","formularioDonacion":"si"},
        {"id":"19","abreviatura":"CUIT","nombre":"DNI CUIT","formularioDonacion":"no"},
        {"id":"20","abreviatura":"PEP","nombre":"Permiso especial de permanencia","formularioDonacion":"no"},
        {"id":"21","abreviatura":"DUI","nombre":"DUI","formularioDonacion":"no"},
        {"id":"22","abreviatura":"IDK","nombre":"Id-Kort","formularioDonacion":"no"},
        {"id":"23","abreviatura":"RUC","nombre":"RUC","formularioDonacion":"no"},
        {"id":"24","abreviatura":"PPT","nombre":"Permiso de Protección Temporal","formularioDonacion":"no"}
      ]';

      $items = json_decode($tiposDeIdentificaciones);
      foreach ($items as $item) {
        TipoIdentificacion::firstOrCreate([
          'nombre' => $item->nombre,
          'formulario_donacion' => $item->formularioDonacion == 'si' ? true : false,
          'abreviatura' => $item->abreviatura
        ]);
      }
  }
}
