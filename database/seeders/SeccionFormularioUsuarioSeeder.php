<?php

namespace Database\Seeders;

use App\Models\SeccionFormularioUsuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeccionFormularioUsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      //1
      SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Sección A',
        'titulo' => 'Sección A',
        'orden' => 1,
        'formulario_usuario_id' => 1
      ]);

      //2
      SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Sección B',
        'titulo' => 'Sección B',
        'orden' => 2,
        'formulario_usuario_id' => 1
      ]);

      //3
      SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Sección C',
        'titulo' => 'Sección C',
        'orden' => 3,
        'formulario_usuario_id' => 1
      ]);

      //4
      SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Sección AA',
        'titulo' => 'Sección AA',
        'orden' => 1,
        'formulario_usuario_id' => 2
      ]);

      //5
      SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Sección BB',
        'titulo' => 'Sección BB',
        'orden' => 2,
        'formulario_usuario_id' => 2
      ]);

      //6
      SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Sección CC',
        'titulo' => 'Sección CC',
        'orden' => 3,
        'formulario_usuario_id' => 2
      ]);

      //7
      SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Sección AA NIÑOS',
        'titulo' => 'Sección AA',
        'orden' => 1,
        'formulario_usuario_id' => 3
      ]);

      //8
      SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Datos personales',
        'titulo' => 'Datos personales',
        'orden' => 1,
        'formulario_usuario_id' => 5,
        'icono' => 'ti ti-user'
      ]);

      //9
      SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Información de contacto',
        'titulo' => 'Información de contacto',
        'orden' => 2,
        'formulario_usuario_id' => 5,
        'icono' => 'ti ti-address-book'
      ]);

      //10
      SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Contraseña',
        'titulo' => 'Contraseña',
        'orden' => 4,
        'formulario_usuario_id' => 5,
        'icono' => 'ti ti-square-asterisk'
      ]);

       //11
       SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Relación familiar',
        'titulo' => 'Relación familiar',
        'orden' => 1,
        'formulario_usuario_id' => 6,
        'icono' => 'ti ti-home-heart'
      ]);

      //12
      SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Datos personales',
        'titulo' => 'Datos personales',
        'orden' => 2,
        'formulario_usuario_id' => 6,
        'icono' => 'ti ti-user'
      ]);

      //13
      /*SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Información de contacto',
        'titulo' => 'Información de contacto',
        'orden' => 3,
        'formulario_usuario_id' => 6,
        'icono' => 'ti ti-address-book'
      ]);*/

      //14
     /* SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Contraseña',
        'titulo' => 'Contraseña',
        'orden' => 4,
        'formulario_usuario_id' => 6,
        'icono' => 'ti ti-square-asterisk'
      ]);*/

      // 13
      SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Información personal',
        'titulo' => 'Información personal',
        'orden' => 1,
        'formulario_usuario_id' => 7,
        'icono' => 'ti ti-user'
      ]);

      // 14
      SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Estudios y ocupación',
        'titulo' => 'Estudios y ocupación',
        'orden' => 2,
        'formulario_usuario_id' => 7,
        'icono' => 'ti ti-user'
      ]);

      // 15
      SeccionFormularioUsuario::firstOrCreate([
        'nombre' => ' Información médica',
        'titulo' => ' Información médica',
        'orden' => 3,
        'formulario_usuario_id' => 7,
        'icono' => 'ti ti-user'
      ]);

      // 16
      SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Datos del acudiente',
        'titulo' => 'Datos del acudiente',
        'orden' => 4,
        'formulario_usuario_id' => 7,
        'icono' => 'ti ti-user'
      ]);

      // 17
      SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Archivos adjuntos',
        'titulo' => 'Archivos adjuntos',
        'orden' => 5,
        'formulario_usuario_id' => 7,
        'icono' => 'ti ti-user'
      ]);

       // 18
       SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Más información',
        'titulo' => 'Más información',
        'orden' => 6,
        'formulario_usuario_id' => 7,
        'icono' => 'ti ti-user'
      ]);

      // 19
      SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Campos extras',
        'titulo' => 'Campos extras',
        'orden' => 7,
        'formulario_usuario_id' => 7,
        'icono' => 'ti ti-user'
      ]);


      // 20
      SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Datos de creación',
        'titulo' => 'Datos de creación',
        'orden' => 8,
        'formulario_usuario_id' => 7,
        'icono' => 'ti ti-user'
      ]);

      //21
      SeccionFormularioUsuario::firstOrCreate([
        'nombre' => 'Información congregacional',
        'titulo' => 'Información congregacional',
        'orden' => 3,
        'formulario_usuario_id' => 5,
        'icono' => 'ti ti-building-church'
      ]);







    }
}
