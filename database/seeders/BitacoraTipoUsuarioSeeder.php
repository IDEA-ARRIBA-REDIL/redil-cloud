<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\BitacoraTipoUsuario;
use App\Models\TipoUsuario;

class BitacoraTipoUsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener algunos usuarios para crear bitácora
       /* $usuarios = User::get();
        $tiposUsuarios = TipoUsuario::all();

            foreach ($usuarios as $usuario) {
                // Crear un registro de creación
                BitacoraTipoUsuario::firstOrCreate([
                    'user_id' => $usuario->id,
                    'tipo_usuario_id_anterior' => null,
                    'tipo_usuario_id_nuevo' => $usuario->tipo_usuario_id,
                    'autor_id' => User::first()->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Crear un registro de cambio (si no es el mismo tipo)
                /*$nuevoTipo = $tiposUsuarios->where('id', '!=', $usuario->tipo_usuario_id)->first();
                if ($nuevoTipo) {
                    BitacoraTipoUsuario::firstOrCreate([
                        'user_id' => $usuario->id,
                        'tipo_user_id_anterior' => $usuario->tipo_usuario_id,
                        'tipo_user_id_nuevo' => $nuevoTipo->id,
                        'autor_id' => User::first()->id ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }*/
    }
}
