<?php

namespace Database\Seeders;

use App\Models\TipoFormularioUsuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoFormularioUsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //1
        TipoFormularioUsuario::firstOrCreate([
          'nombre' => 'Nuevo',
          'action' => 'usuario.crear',
          'es_formulario_nuevo' => true,
          'view' => 'contenido.paginas.usuario.nuevo'
        ]);

        //2
        TipoFormularioUsuario::firstOrCreate([
          'nombre' => 'Modificar',
          'action' => 'usuario.editar',
          'view' => 'contenido.paginas.usuario.modificar'
        ]);

        //3
        TipoFormularioUsuario::firstOrCreate([
          'nombre' => 'Nuevo externo',
          'action' => 'usuario.crearInscripcion',
          'es_formulario_exterior' => true,
          'es_formulario_nuevo' => true,
          'view' => 'contenido.paginas.usuario.nuevo-tipo-next',
          'redirect' => 'ingreso-exitoso',
          'layout' => 'layouts/blankLayout'
        ]);

        //4
        TipoFormularioUsuario::firstOrCreate([
          'nombre' => 'Nuevo menor',
          'action' => 'usuario.crear',
          'es_formulario_exterior' => false,
          'es_formulario_nuevo' => true,
          'view' => 'contenido.paginas.usuario.nuevo-tipo-next',
          'redirect' => 'ingreso-exitoso-hijos',
          'layout' => 'layouts/blankLayout'
        ]);

        //5
        TipoFormularioUsuario::firstOrCreate([
          'nombre' => 'Perfil y Autoeditar',
          'action' => 'usuario.autoeditar',
          'es_formulario_exterior' => false,
          'es_formulario_autoeditar' => true,
          'view' => '',
          'layout' => '',
        ]);
    }
}
