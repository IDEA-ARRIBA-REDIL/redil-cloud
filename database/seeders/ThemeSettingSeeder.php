<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ThemeSetting;

class ThemeSettingSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    //este seeder es para la configuración inicial de colores, aqui se debe configurar con estos valores iniciales
    // para el multi-tenancy pero para cada iglesia se cargara una configuración inicial



    $defaultColors = [
      ['nombre' => 'Color texto primario', 'class' => 'primary', 'value' => '#32700A', 'category' => 'colors'],  // $purple
      ['nombre' => 'Color texto secundario', 'class' => 'secondary', 'value' => '#1977E5', 'category' => 'colors'], // #1977E5
      ['nombre' => 'Color texto exitoso', 'class' => 'success', 'value' => '#13964f', 'category' => 'colors'],   // $green
      ['nombre' => 'Color texto informativo', 'class' => 'info', 'value' => '#0099cc', 'category' => 'colors'],      // $cyan
      ['nombre' => 'Color texto precaución', 'class' => 'warning', 'value' => '#f3aa01', 'category' => 'colors'],   // $yellow
      ['nombre' => 'Color texto alerta', 'class' => 'danger', 'value' => '#aa1e1e', 'category' => 'colors'],    // $red
      ['nombre' => 'Color texto claro', 'class' => 'light', 'value' => '#dfdfe3', 'category' => 'colors'],     // #dfdfe3
      ['nombre' => 'Color texto oscuro', 'class' => 'dark', 'value' => '#141621', 'category' => 'colors'],      // #141621
      ['nombre' => 'Color texto gris', 'class' => 'gray', 'value' => '#667799', 'category' => 'colors'],      // $gray-500




      // ACA PARA LOS BOTONES
      ['nombre' => 'Color botón primario', 'class' => 'btn-primary', 'value' => '#32700A', 'category' => 'button'],  // $purple
      ['nombre' => 'Color botón secundario', 'class' => 'btn-secondary', 'value' => '#1977E5', 'category' => 'button'], // #1977E5
      ['nombre' => 'Color botón exitoso', 'class' => 'btn-success', 'value' => '#13964f', 'category' => 'button'],   // $green
      ['nombre' => 'Color botón información', 'class' => 'btn-info', 'value' => '#0099cc', 'category' => 'button'],      // $cyan
      ['nombre' => 'Color botón precaucón', 'class' => 'btn-warning', 'value' => '#f3aa01', 'category' => 'button'],   // $yellow
      ['nombre' => 'Color botón alerta', 'class' => 'btn-danger', 'value' => '#aa1e1e', 'category' => 'button'],    // $red
      ['nombre' => 'Color botón claro', 'class' => 'btn-light', 'value' => '#dfdfe3', 'category' => 'button'],     // #dfdfe3
      ['nombre' => 'Color botón oscuro', 'class' => 'btn-dark', 'value' => '#141621', 'category' => 'button'],      // #141621
      ['nombre' => 'Color botón gris', 'class' => 'btn-gray', 'value' => '#667799', 'category' => 'button'],      // $gray-500


       // ACA PARA el BACKGROUND
      ['nombre' => 'Color backround primario', 'class' => 'theme-bg-primary', 'value' => '#32700A', 'category' => 'background'],  // $purple
      ['nombre' => 'Color backround secundario', 'class' => 'theme-bg-secondary', 'value' => '#1977E5', 'category' => 'background'], // #1977E5
      ['nombre' => 'Color backround exitoso', 'class' => 'theme-bg-success', 'value' => '#13964f', 'category' => 'background'],   // $green
      ['nombre' => 'Color backround información', 'class' => 'theme-bg-info', 'value' => '#0099cc', 'category' => 'background'],      // $cyan
      ['nombre' => 'Color backround precaucón', 'class' => 'theme-bg-warning', 'value' => '#f3aa01', 'category' => 'background'],   // $yellow
      ['nombre' => 'Color backround alerta', 'class' => 'theme-bg-danger', 'value' => '#aa1e1e', 'category' => 'background'],    // $red
      ['nombre' => 'Color backround claro', 'class' => 'theme-bg-light', 'value' => '#dfdfe3', 'category' => 'background'],     // #dfdfe3
      ['nombre' => 'Color backround oscuro', 'class' => 'theme-bg-dark', 'value' => '#141621', 'category' => 'background'],      // #141621
      ['nombre' => 'Color backround gris', 'class' => 'theme-bg-gray', 'value' => '#667799', 'category' => 'background'],      // $gray-500

      //// BTN CON TEXT

      ['nombre' => 'Color botón text primario', 'class' => 'btn-text-primary', 'value' => '#32700A', 'category' => 'button-text'],  // $purple
      ['nombre' => 'Color botón text secundario', 'class' => 'btn-text-secondary', 'value' => '#1977E5', 'category' => 'button-text'], // #1977E5
      ['nombre' => 'Color botón text exitoso', 'class' => 'btn-text-success', 'value' => '#13964f', 'category' => 'button-text'],   // $green
      ['nombre' => 'Color botón text información', 'class' => 'btn-text-info', 'value' => '#0099cc', 'category' => 'button-text'],      // $cyan
      ['nombre' => 'Color botón text precaucón', 'class' => 'btn-text-warning', 'value' => '#f3aa01', 'category' => 'button-text'],   // $yellow
      ['nombre' => 'Color botón text alerta', 'class' => 'btn-text-danger', 'value' => '#aa1e1e', 'category' => 'button-text'],    // $red
      ['nombre' => 'Color botón text claro', 'class' => 'btn-text-light', 'value' => '#dfdfe3', 'category' => 'button-text'],     // #dfdfe3
      ['nombre' => 'Color botón text oscuro', 'class' => 'btn-text-dark', 'value' => '#141621', 'category' => 'button-text'],      // #141621
      ['nombre' => 'Color botón text gris', 'class' => 'btn-text-gray', 'value' => '#667799', 'category' => 'button-text'],      // $gray-500

      // ACA PARA LOS HOVER
      ['nombre' => 'Hover primario', 'class' => 'btn-primary', 'value' => '#32700A', 'category' => 'hover'],  // $purple
      ['nombre' => 'Hover secundario', 'class' => 'btn-secondary', 'value' => '#1977E5', 'category' => 'hover'], // #1977E5
      ['nombre' => 'Hover exitoso', 'class' => 'btn-success', 'value' => '#0e743c', 'category' => 'hover'],   // $green
      ['nombre' => 'Hover informativo', 'class' => 'btn-info', 'value' => '#02779d', 'category' => 'hover'],      // $cyan
      ['nombre' => 'Hover precaución', 'class' => 'btn-warning', 'value' => '#ca8d02', 'category' => 'hover'],   // $yellow
      ['nombre' => 'Hover alerta', 'class' => 'btn-danger', 'value' => '#781414', 'category' => 'hover'],    // $red
      ['nombre' => 'Hover claro', 'class' => 'btn-light', 'value' => '#919193', 'category' => 'hover'],     // #dfdfe3
      ['nombre' => 'Hover oscuro', 'class' => 'btn-dark', 'value' => '#101010', 'category' => 'hover'],      // #141621
      ['nombre' => 'Hover gris', 'class' => 'btn-gray', 'value' => '#2b3342', 'category' => 'hover'],      // $gray-500

      // ACA PARA LOS LABEL
      ['nombre' => 'Label primaria', 'class' => 'bg-label-primary', 'value' => '#32700A', 'category' => 'label'],  // $purple
      ['nombre' => 'Label secundario', 'class' => 'bg-label-secondary', 'value' => '#1977E5', 'category' => 'label'], // #1977E5
      ['nombre' => 'Label exitoso', 'class' => 'bg-label-success', 'value' => '#13964f', 'category' => 'label'],   // $green
      ['nombre' => 'Label informatico', 'class' => 'bg-label-info', 'value' => '#0099cc', 'category' => 'label'],      // $cyan
      ['nombre' => 'Label precaución', 'class' => 'bg-label-warning', 'value' => '#f3aa01', 'category' => 'label'],   // $yellow
      ['nombre' => 'Label alerta', 'class' => 'bg-label-danger', 'value' => '#aa1e1e', 'category' => 'label'],    // $red
      ['nombre' => 'Label claro', 'class' => 'bg-label-light', 'value' => '#dfdfe3', 'category' => 'label'],     // #dfdfe3
      ['nombre' => 'Label oscuro', 'class' => 'bg-label-dark', 'value' => '#141621', 'category' => 'label'],      // #141621
      ['nombre' => 'Label gris', 'class' => 'bg-label-gray', 'value' => '#667799', 'category' => 'label'],      // $gray-500


       // ACA PARA LOS ALERTS
      ['nombre' => 'alert primario', 'class' => 'alert-primary', 'value' => '#32700A', 'category' => 'alert alert-text'],  // $purple
      ['nombre' => 'alert secundario', 'class' => ' alert-secondary', 'value' => '#1977E5', 'category' => 'alert alert-text'], // #1977E5
      ['nombre' => 'alert exitoso', 'class' => 'alert-success', 'value' => '#13964f', 'category' => 'alert alert-text'],   // $green
      ['nombre' => 'alert información', 'class' => 'alert-info', 'value' => '#0099cc', 'category' => 'alert alert-text'],      // $cyan
      ['nombre' => 'alert precaucón', 'class' => 'alert-warning', 'value' => '#f3aa01', 'category' => 'alert alert-text'],   // $yellow
      ['nombre' => 'alert alerta', 'class' => 'alert-danger', 'value' => '#aa1e1e', 'category' => 'alert alert-text'],    // $red
      ['nombre' => 'alert claro', 'class' => 'alert-light', 'value' => '#dfdfe3', 'category' => 'alert alert-text'],     // #dfdfe3
      ['nombre' => 'alert oscuro', 'class' => 'alert-dark', 'value' => '#141621', 'category' => 'alert alert-text'],      // #141621
      ['nombre' => 'alert gris', 'class' => 'alert-gray', 'value' => '#667799', 'category' => 'alert alert-text'],      // $gray-500

      // ACA PARA LOS LABEL LIGTHS
      ['nombre' => 'Label Claro primaria', 'class' => 'bg-label-claro-primary', 'value' => '#32700A8c', 'category' => 'label-claro'],  // $purple
      ['nombre' => 'Label Claro secundario', 'class' => 'bg-label-claro-secondary', 'value' => '#1977E58c', 'category' => 'label-claro'], // #1977E5
      ['nombre' => 'Label Claro exitoso', 'class' => 'bg-label-claro-success', 'value' => '#13964f8c', 'category' => 'label-claro'],   // $green
      ['nombre' => 'Label Claro informatico', 'class' => 'bg-label-claro-info', 'value' => '#0099cc8c', 'category' => 'label-claro'],      // $cyan
      ['nombre' => 'Label Claro precaución', 'class' => 'bg-label-claro-warning', 'value' => '#f3aa018c', 'category' => 'label-claro'],   // $yellow
      ['nombre' => 'Label Claro alerta', 'class' => 'bg-label-claro-danger', 'value' => '#aa1e1e8c', 'category' => 'label-claro'],    // $red
      ['nombre' => 'Label Claro claro', 'class' => 'bg-label-claro-light', 'value' => '#dfdfe38c', 'category' => 'label-claro'],     // #dfdfe3
      ['nombre' => 'Label Claro oscuro', 'class' => 'bg-label-claro-dark', 'value' => '#1416218c', 'category' => 'label-claro'],      // #141621
      ['nombre' => 'Label Claro gris', 'class' => 'bg-label-claro-gray', 'value' => '#6677998c', 'category' => 'label-claro'],      // $gray-500


      // ACTIVO O INACTIVO
      ['nombre' => 'active', 'class' => 'active', 'value' => '#32700A', 'category' => 'active'],
      ['nombre' => 'inactive', 'class' => 'disabled', 'value' => '#c9c9c9', 'category' => 'disabled'],

      // CONFIG LOGIN
      ['nombre' => 'Fondo login', 'class' => 'bg-login-left', 'value' => '#141621', 'category' => 'login', 'gradient' => 'true', 'value2' => '#202020'],
      ['nombre' => 'Color letra para fondo oscuro', 'class' => 'color-dark-texto', 'value' => '#f8f8ff', 'category' => 'login', 'is_active' => 'true'],
      ['nombre' => 'Color letra para fondo claro', 'class' => 'color-white-texto', 'value' => '#141621', 'category' => 'login', 'is_active' => 'false'],

      //MENU GENERAL
      ['nombre' => 'Fondo Menu', 'class' => 'bg-menu-theme', 'value' => '#141621', 'category' => 'menu', 'gradient' => 'true', 'value2' => '#202020'],
      ['nombre' => 'Color Texto Menu', 'class' => 'bg-menu-theme .menu-item', 'value' => '#f8f8ff', 'category' => 'menu'],

      //MENU GENERAL
      ['nombre' => 'Fondo Menu Escuelas', 'class' => 'bg-menu-theme-escuelas', 'value' => '#101029', 'category' => 'menu', 'gradient' => 'false', 'value2' => '#202020'],
      ['nombre' => 'Color Texto Escuelas', 'class' => 'bg-menu-theme-escuelas .menu-item', 'value' => '#f8f8ff', 'category' => 'menu'],


    ];


    foreach ($defaultColors as $color) {
      ThemeSetting::firstOrCreate(
          ['nombre' => $color['nombre'], 'class' => $color['class']], // Keys to check unique
          $color // Attributes to set
      );
    }
    /*
        $themeSettingColorPrimary=ThemeSetting::where('class','primary')->first();
        // Ruta al archivo
        $filePath = base_path('resources/assets/vendor/scss/theme-semi-dark.scss');

         // Leer contenido existente
         $existentContent = file_get_contents($filePath);

         // Nuevo contenido al inicio
         $clase='$primary-color';

         $newContent = $clase.":".$themeSettingColorPrimary->value.';'.$existentContent;

         // Escribir contenido completo
         file_put_contents($filePath, $newContent);


         //$output = shell_exec('npm run build');
         */
  }
}
