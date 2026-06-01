<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Configuracion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configuracion = Configuracion::first();
        if (!$configuracion) {
            $this->command->error("No se encontró la configuración del sistema.");
            return;
        }

        // Buscamos un usuario administrador para asignar las publicaciones
        $admin = User::whereHas('roles', function($q) {
            $q->where('name', 'Super Administrador')->orWhere('name', 'Pastor');
        })->first() ?? User::first();

        if (!$admin) {
            $this->command->error("No se encontró un usuario válido para asignar las publicaciones.");
            return;
        }

        $imagenes = [
            'publicacion1.jpeg',
            'publicacion2.jpeg',
            'publicacion3.jpeg',
        ];

        $tenantPath = 'img/publicaciones/';

        // Asegurar que el directorio de destino existe en el disco public
        if (!Storage::disk('public')->exists($tenantPath)) {
            Storage::disk('public')->makeDirectory($tenantPath);
        }

        $posts = [
            [
                'descripcion' => 'Bienvenidos a nuestra comunidad. Estamos felices de tenerte con nosotros. #Redil #Familia',
                'image_path' => 'publicacion1.jpeg',
            ],
            [
                'descripcion' => 'No te pierdas nuestras próximas actividades. Revisa el calendario para más detalles.',
                'image_path' => 'publicacion2.jpeg',
            ],
            [
                'descripcion' => 'Recuerda que estamos para servirte. Si necesitas oración o consejería, no dudes en contactarnos.',
                'image_path' => 'publicacion3.jpeg',
            ],
        ];

        foreach ($posts as $index => $postData) {
            $imageName = $postData['image_path'];

            // Intentar copiar la imagen desde global_media al tenant si existe
            if (Storage::disk('global_media')->exists($imageName)) {
                $content = Storage::disk('global_media')->get($imageName);
                Storage::disk('public')->put($tenantPath . $imageName, $content);
                $this->command->info("Imagen {$imageName} copiada al tenant.");
            } else {
                $this->command->warn("Imagen {$imageName} no encontrada en global_media. El post se creará pero podría no mostrar imagen.");
            }

            Post::create([
                'user_id' => $admin->id,
                'descripcion' => $postData['descripcion'],
                'image_path' => $imageName,
                'fecha_inicio' => now()->subDays($index),
                'fecha_fin' => now()->addMonths(6),
                'visualizar_siempre' => true,
                'visible_todos' => true,
                'genero' => 3, // Ambos
            ]);
        }

        $this->command->info("PostSeeder ejecutado con éxito.");
    }
}
