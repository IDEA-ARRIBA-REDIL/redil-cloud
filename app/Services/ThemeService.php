<?php

namespace App\Services;

use App\Models\ThemeSetting;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class ThemeService
{

  public function generateScssVariables(): string
  {
    // Cachea los colores por 24 horas o hasta que se actualicen
    return Cache::remember('theme_scss_variables', 1, function () {
      $settings = ThemeSetting::where('is_active', true)->get();

      $scss = "// Generado automáticamente - No editar directamente\n\n";


      // cambios de colores para el primary

      $themeSettingColorPrimary = ThemeSetting::where('class', 'primary')->first();
      if (isset($themeSettingColorPrimary->id)) {
        $scss .= 'nav-pills .nav-link.active, .nav-pills .nav-link.active:hover, .nav-pills .nav-link.active:focus
             {
              background-color: ' . $themeSettingColorPrimary->value . ' !important;
              color: #fff;
             }
              body .text-primary {
                color:' . $themeSettingColorPrimary->value . ' !important;
                }
            .bg-footer-theme .footer-link {
             color:' . $themeSettingColorPrimary->value . ' !important;
                }
            .btn-outline-primary {
              color:#f8f8ff;!important;
              border-color:' . $themeSettingColorPrimary->value . ' !important;
              background:' . $themeSettingColorPrimary->value . '11 !important;}

              .nav-pills .nav-item .nav-link:not(.active):hover
              {
                border-bottom: none;
                padding-bottom: .5435rem;
                background-color:#e4e4e4 !important;
               color:#444!important;
              }

              .nav-pills .nav-link
              {
              text-transform: inherit !important;
              }



              .custom-option.checked {
                  border: 2px solid ' . $themeSettingColorPrimary->value . ' !important;
                  margin: 0;
              }

              .form-check-input:checked {
                  background-color: ' . $themeSettingColorPrimary->value . ' !important;
                  border-color: ' . $themeSettingColorPrimary->value . ' !important;
                  box-shadow: 0 2px 6px' . $themeSettingColorPrimary->value . '4d !important;
              }

              .bs-stepper .step.crossed .step-trigger .bs-stepper-icon svg {
                  fill:' . $themeSettingColorPrimary->value . ' !important;
              }

              .bs-stepper .step.crossed .step-trigger .bs-stepper-icon i, .bs-stepper.wizard-icons .step.crossed .bs-stepper-label {
                  color:' . $themeSettingColorPrimary->value . ' !important;
              }

             body .border-primary{
                 border-color:' . $themeSettingColorPrimary->value . '!important;
              }

               body .border-primary:focus-visible{
                 border-color:' . $themeSettingColorPrimary->value . '!important;
              }

              body .border-primary:focus{
                 border-color:' . $themeSettingColorPrimary->value . '!important;
              }


              .select2-container--default .select2-results__option--highlighted[aria-selected]
              {
               background-color:' . $themeSettingColorPrimary->value . ' !important;
                color: #fff !important;
              }

                .select2-container--default.select2-container--focus .select2-selection, .select2-container--default.select2-container--open .select2-selection {
                  border-width: 2px;
                  border-color: ' . $themeSettingColorPrimary->value . ' !important;
                }

              .badge-center{
              color:' . $themeSettingColorPrimary->value . ' !important;
              }

              .badge{
              color:' . $themeSettingColorPrimary->value . ' !important;
              }

              .badge i{
              color:#fff !important;
              }

              .bg-label-primary{
                  background-color:' . $themeSettingColorPrimary->value . '!important;
              color: #fff !important;
              border: solid 1px;
              }



              .nav-pills .nav-link.active, .nav-pills .nav-link.active:hover, .nav-pills .nav-link.active:focus
              {
                      background-color:' . $themeSettingColorPrimary->value . '!important;
                      color: #fff !important;
                }

                .btn-outline-primary {
                    color: ' . $themeSettingColorPrimary->value . ' !important;
                    border-color:' . $themeSettingColorPrimary->value . ' !important;
                    background: transparent;
                }

                .titulo-primary{
                      color: ' . $themeSettingColorPrimary->value . ' !important;
                  }

                  input-group:focus-within .form-control, .input-group:focus-within .input-group-text {
                     border-color: ' . $themeSettingColorPrimary->value . ' !important;
                }

                .form-control:focus, .form-select:focus
                {
                 border-color: ' . $themeSettingColorPrimary->value . ' !important;
                }
                 #container-footer a{
                        color: ' . $themeSettingColorPrimary->value . ' !important;
                 }

                 .html5-qrcode-element{
                  background-color:' . $themeSettingColorPrimary->value . ' !important;
                  color: white;
                  padding: 12px 24px;
                  border: none;
                  border-radius: 8px;
                  cursor: pointer;
                  font-size: 16px;
                  font-weight: 600;
                  transition: all 0.3s ease;
                  margin: 10px 0;
                  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
              }

              ';

        //// esto es para los botones del full calendar es una configuracion especifica pero se une con los label

        $scss .= ".fc .fc-button-primary:not(.fc-prev-button):not(.fc-next-button).fc-button-active, .fc .fc-button-primary:not(.fc-prev-button):not(.fc-next-button):hover {
              background-color: " . $themeSettingColorPrimary->value . "3d !important;
                color: " . $themeSettingColorPrimary->value . " !important;
            }";

        $scss .= ".fc .fc-button-primary:not(.fc-prev-button):not(.fc-next-button){
            background-color: #e4e4e43d !important;
                color:  #545454 !important;
            }";
      }

      $scss .= " .btn-outline-danger
                {
                    color: #B71C1C !important;
                    background-color: #fff1f1 !important;;
                    border-color: #B71C1C !important;;
                }";


      /// algunas configuraciones individual
      $scss .= ".texto-danger{color:#AA1A1E !important}

       .boxShadow{
          padding: 19px;
          box-shadow: 0px 3px 7px #d4d4d4;
          border-radius: 9px;
          min-width: 350px;
          margin-bottom: 7px;
        }

      ;

      ";

      $scss .= '/// tema de los colores del texto';

      $themeSettingColorText = ThemeSetting::where('category', 'colors')->get();

      foreach ($themeSettingColorText as $setting) {
        $scss .= '.' . $setting->class . '{color:' . $setting->value . ' !important;}';
      }


      // cambios para los label

      $themeSettingColorLabel = ThemeSetting::where('category', 'label')->get(); {
        foreach ($themeSettingColorLabel as $setting) {
          $scss .= '.' . $setting->class . '{color:#fff !important;background:' . $setting->value . ' !important;}';
          if ($setting->class == 'bg-label-secondary') {
            $scss .= '.form-control::file-selector-button {
                      padding: .426rem .9375rem;
                      margin: -.426rem -.9375rem;
                      margin-inline-end: .9375rem;
                      color: #ffffff !important;
                      background-color:' . $setting->value . ' !important;
                      pointer-events: none;
                      border-color: inherit;
                      border-style: solid;
                      border-width: 0;
                      border-inline-end-width: var(--bs-border-width);
                      border-radius: 0;
                      transition: all .2s ease-in-out;
                  }';
          }
          $class = 'bg-label-secondary';
          if ($setting->class == $class) {
            $scss .=
              '.light-style .flatpickr-prev-month svg, .light-style .flatpickr-next-month svg
                {
                      fill: ' . $setting->value . ' !important;
                      stroke:' . $setting->value . ' !important;
                  }
                  .light-style .flatpickr-monthDropdown-months
                  {
                      color:' . $setting->value . ' !important;
                   }

                   .light-style .flatpickr-current-month .cur-year
                    {
                      font-size: .9375rem;
                      font-weight: 400;
                      color:' . $setting->value . ' !important;
                    }

                    .btn-outline-secondary
                    {
                          color:' . $setting->value . ' !important;
                          border-color:' . $setting->value . ' !important;
                          background: transparent;
                      }

                      .bs-stepper.wizard-icons .step.crossed+.line i
                      {
                            color:' . $setting->value . ' !important;
                        }
                  ';
          }
        }
      }

      // cambios para los label

      $themeSettingColorLabel = ThemeSetting::where('category', 'label-claro')->get(); {
        foreach ($themeSettingColorLabel as $setting) {
          $scss .= '.' . $setting->class . '{color:#fff;background:' . $setting->value . ' !important;}';
        }
      }




      /// codigo para buttons
      $settingsButtons = ThemeSetting::where('category', 'button')->where('is_active', true)->get();
      foreach ($settingsButtons as $setting) {


        $scss .= '.' . $setting->class . '{color:#fff !important;background:' . $setting->value . ' !important;border-color:' . $setting->value . '!important;}';
        $settingHoverButtons = ThemeSetting::where('category', 'hover')->where('is_active', true)->get();
        foreach ($settingHoverButtons as $settingHover) {
          if ($settingHover->class == $setting->class) {
            $scss .= '.' . $setting->class . ':hover{color:#fff !important;background:' . $settingHover->value . ' !important;border-color:' . $settingHover->value . '!important;}';
          }
        }
      }


       /// codigo para buttons
      $settingsButtons = ThemeSetting::where('category', 'background')->where('is_active', true)->get();
      foreach ($settingsButtons as $setting) {

        $scss .= '.' . $setting->class . '{color:#fff !important;background-color:' . $setting->value . ' !important;border-color:' . $setting->value . '!important;}';
        $settingHoverButtons = ThemeSetting::where('category', 'hover')->where('is_active', true)->get();
        foreach ($settingHoverButtons as $settingHover) {
          if ($settingHover->class == $setting->class) {
            $scss .= '.' . $setting->class . ':hover{color:#fff !important;background:' . $settingHover->value . ' !important;border-color:' . $settingHover->value . '!important;}';
          }
        }
      }

      //// codigo para buttons text

      $settingsButtonsText = ThemeSetting::where('category', 'button-text')->where('is_active', true)->get();
      foreach ($settingsButtonsText as $setting) {

        $scss .= '.' . $setting->class . '{color:' . $setting->value . ' !important;!important;}';
        $scss .= '.' . $setting->class . ':hover{color: ' . $setting->value . ' ;!important; }';
      }

       //// codigo para alert

      $settingsAlert= ThemeSetting::where('category', 'alert alert-text')->where('is_active', true)->get();
      foreach ($settingsAlert as $setting) {

        $scss .= '.' . $setting->class . '{color:' . $setting->value . ' !important;!important; background-color:' . $setting->value . '30 !important;}';
        $scss .= '.' . $setting->class . ':hover{color: ' . $setting->value . ' ;!important; }';
      }



      // codigo para login

      $settingsButtons = ThemeSetting::where('category', 'login')->where('is_active', true)->get();
      foreach ($settingsButtons as $setting) {
        if ($setting->class == 'bg-login-left' && $setting->gradient === 'false') {
          $scss .= '.' . $setting->class . '{background:' . $setting->value . ' !important;}';
        } else {
          $scss .= '.' . $setting->class . '{background:linear-gradient(180deg,' . $setting->value . ' 0%,' . $setting->value2 . ' 100%)!important;}';
        }

        if ($setting->class == 'color-dark-texto') {

          $scss .= '  .bg-login-left #email::placeholder{
                            color:' . $setting->value . ' !important;
                          }
                          .bg-login-left input{
                            color:' . $setting->value . ' !important;
                          }

                           .bg-login-left .titulo-descripcion{
                            color:' . $setting->value . ' !important;
                          }

                           .bg-login-left .titulo-login{
                            color:' . $setting->value . ' !important;
                          }

                         .bg-login-left  input:-internal-autofill-selected
                          {
                            color:#fff !important;
                            background-color:' . $setting->value . ' !important;
                          }

                          .bg-login-left input:-webkit-autofill {
                              -webkit-box-shadow:0 0 0 50px #141621!important inset; /* Change the color to your own background color */
                              -webkit-text-fill-color: ' . $setting->value . ';
                          }

                         .bg-login-left  input:-webkit-autofill:focus {
                              -webkit-box-shadow: 0 0 0 50px #141621!important inset;/*your box-shadow*/
                              -webkit-text-fill-color: ' . $setting->value . ';
                          }
                           .bg-login-left .input-login{
                              border: none !important;
                              border-bottom: solid 2px #acaab1 !important ;
                              border-radius: 0 !important;

                            }';
        }

        if ($setting->class == 'color-white-texto') {

          $scss .= '   #email::placeholder{
                            color:' . $setting->value . ' !important;
                          }
                         input{
                            color:' . $setting->value . ' !important;
                          }

                          input:-internal-autofill-selected
                          {
                            color:#fff !important;
                            background-color:' . $setting->value . ' !important;
                          }

                          input:-webkit-autofill {
                              -webkit-box-shadow:0 0 0 50px #141621!important inset; /* Change the color to your own background color */
                              -webkit-text-fill-color: ' . $setting->value . ';
                          }

                         input:-webkit-autofill:focus {
                              -webkit-box-shadow: 0 0 0 50px #141621!important inset;/*your box-shadow*/
                              -webkit-text-fill-color: ' . $setting->value . ';
                          }
                           .input-login{
                              border: none !important;
                              border-bottom: solid 2px #acaab1!important;
                              border-radius: 0 !important;

                            }';
        }
      }

      // codigo para el menu
      $settingsButtons = ThemeSetting::where('category', 'menu')->where('is_active', true)->get();
      foreach ($settingsButtons as $setting) {
        if ($setting->class === 'bg-menu-theme' && $setting->gradient  == false) {
          $scss .= '.' . $setting->class . '{background:' . $setting->value . ' !important;}';
        }

        if ($setting->class === 'bg-menu-theme' &&  $setting->gradient  == true) {
          $scss .= '.' . $setting->class . '{background:linear-gradient(180deg,' . $setting->value . ' 0% ,' . $setting->value2 . ' 100%) !important;}';
        }

        if ($setting->class == 'bg-menu-theme .menu-item') {
          $scss .= '.' . $setting->class . ' a{color:' . $setting->value . ' !important;}';
        }

        if ($setting->class == 'bg-menu-theme-escuelas .menu-item') {
          $scss .= '.' . $setting->class . ' a{color:' . $setting->value . ' !important;}';
        }

        if ($setting->class === 'bg-menu-theme-escuelas' && $setting->gradient  == false) {
          $scss .= '.' . $setting->class . '{background:' . $setting->value . ' !important;}';
        }
      }
      // active
      $settingsButtons = ThemeSetting::where('category', 'active')->where('is_active', true)->first(); {
        $scss .= '.bg-menu-theme .menu-sub .active a{background:#32700A !important}';
      }

      $baseColorPrimary=ThemeSetting::where('category', 'colors')->where('class', 'primary')->first();
      $baseColorSecondary=ThemeSetting::where('category', 'colors')->where('class', 'secondary')->first();
      $baseColorSuccess=ThemeSetting::where('category', 'colors')->where('class', 'success')->first();
      $baseColorInfo=ThemeSetting::where('category', 'colors')->where('class', 'info')->first();
      $baseColorWarning=ThemeSetting::where('category', 'colors')->where('class', 'warning')->first();
      $baseColorDanger=ThemeSetting::where('category', 'colors')->where('class', 'danger')->first();
      $baseColorLight=ThemeSetting::where('category', 'colors')->where('class', 'light')->first();
      $baseColorDark=ThemeSetting::where('category', 'colors')->where('class', 'dark')->first();
      $baseColorGray=ThemeSetting::where('category', 'colors')->where('class', 'gray')->first();


      /// independientes
      $scss .= 'body {
        font-family:"poppins" !important;
        }
        .btn-primary.btn[class*=btn-]:not([class*=btn-label-]):not([class*=btn-outline-]):not([class*=btn-text-]):not(.btn-icon):not(:disabled):not(.disabled)
        {
             box-shadow: none !important;
        }
        .avatar-xxl {
          width: 7.5rem !important;
          height: 7.5rem !important;
        }

        .avatar-xxl .avatar-initial {
          font-size: 2.0rem !important;
        }

        .light-style .flatpickr-prev-month, .light-style .flatpickr-next-month {
         background-color: #ffffff00 !important;
        }
         .form-control:disabled {
          color: #acaab1;
          background-color: #dedede;
          border-color: #8c8c8c;
          opacity: 1;
        }

        .error-input-form
        {
          background: #E8A4A6 !important;
         -webkit-text-fill-color: #AA1A1E !important;
          border: solid 1px #AA1A1E !important;
        }

        .page-item.active .page-link{
            border-color: '.$baseColorPrimary->value.' !important;
            background-color: '.$baseColorPrimary->value.' !important;
            color: #fff !important;
          }

         .error-input-form-label
        {

         -webkit-text-fill-color: #AA1A1E !important;

        }

         .error-input-form::placeholder
        {
          background: #E8A4A6 !important;

        }

        .rounded {
                  border-radius: 15px !important;
              }

        .shadow {
              box-shadow: 0 .15rem .5rem #2f2b3d5c !important;
          }

        .bg-menu-theme-escuelas .menu-sub>.menu-item>.menu-link:before {
          content: "";
          font-family: tabler-icons;
          position: absolute;
          font-size: .75rem;
          font-weight: 700;
      }

      .bg-menu-theme-escuelas.menu-vertical .menu-sub>.menu-item>.menu-link:before
       {
            left: 1.1rem;
        }

        .cuadroInfoGeneral{color: black;
                            font-size: 15px;
                            padding: 24px !important;
                            border: solid 2px #95CDDF;
                            border-radius: 14px;}
        ';

      //// confiiguracion menu moviles

      $scss .= '
      @media (max-width: 1199.98px)
        {
            .layout-menu {
                width: 100% !important;
            }

            .menu-vertical, .menu-vertical .menu-block, .menu-vertical .menu-inner>.menu-item, .menu-vertical .menu-inner>.menu-header
            {
              width: 100% !important;
            }

            /* --- Mobile Bottom Navigation --- */
            .mobile-nav {
              position: fixed;
              bottom: 0;
              left: 0;
              width: 100%;
              height: 70px;
              background: #1e2130;
              display: flex;
              justify-content: space-around;
              align-items: center;
              z-index: 1095;
              padding-bottom: 5px;
              box-shadow: 0 -2px 10px rgba(0,0,0,0.5);
            }

            .mobile-nav-item {
              text-decoration: none;
              color: #ffffffff !important;
              display: flex;
              flex-direction: column;
              align-items: center;
              justify-content: center;
              transition: all 0.2s ease;
            }

            .mobile-nav-item.active {
              color: '.$baseColorPrimary->value.' !important;
            }

            .mobile-nav-item i {
              font-size: 24px;
            }

            .mobile-nav-item span {
              font-size: 11px;
              margin-top: 2px;
              text-transform: lowercase;
              font-weight: 500;
            }

            .mobile-nav-center {
              position: relative;
              top: -20px;
              width: 65px;
              height: 65px;
              border-radius: 50% !important;
              display: flex;
              justify-content: center;
              align-items: center;
              border: 5px solid #1e2130 !important;
              z-index: 1100;
            }

            .mobile-nav-center i {
              font-size: 28px !important;
              color: white !important;
              margin-bottom: 0 !important;
            }

            .mobile-nav-center:active {
              transform: scale(0.95);
            }

            /* Push content up to avoid being hidden by navbar */
            body.layout-navbar-fixed .layout-page {
               padding-bottom: 70px !important;
            }

            #offcanvasBirthday {
              z-index: 2000 !important;
            }

            /* Responsive Offcanvas: Top on XS, End on others */
            @media (max-width: 575.98px) {
              #offcanvasBirthday.offcanvas-end {
                top: 0 !important;
                right: 0 !important;
                left: 0 !important;
                height: 100vh !important;
                width: 100% !important;
                border-left: none !important;
                transform: translateY(-100%) !important;
                z-index: 2000 !important; /* Added z-index */
              }
              #offcanvasBirthday.offcanvas-end.show {
                transform: translateY(0) !important;
              }
            }

            @media (min-width: 576px) {
               #offcanvasBirthday {
                 width: 400px !important;
               }
            }

            /* Main Menu Responsive: Top on XS, Left on others */
            @media (max-width: 575.98px) {
              .layout-menu {
                transform: translateY(-100%) !important;
                visibility: hidden;
                transition: transform 0.3s ease-in-out, visibility 0.3s ease-in-out !important;
                left: 0 !important;
                top: 0 !important;
                height: 100vh !important;
                width: 100% !important;
              }
              .layout-menu-expanded .layout-menu {
                transform: translateY(0) !important;
                visibility: visible;
              }
            }
        }
      ';


      return $scss;
    });
  }
  public function updateScssFile(): bool
  {
    // Clear the cache to ensure fresh generation
    Cache::forget('theme_scss_variables');

    $variables = $this->generateScssVariables();


    $tenantId = tenant('id') ?? 'global';
    $path = public_path('storage/' . $tenantId . '/theme/');
    !is_dir($path) && mkdir($path, 0777, true);

    // Ensure directory exists
    File::ensureDirectoryExists(dirname($path));

    try {
      $result = File::put($path . '_custom-variables.css', $variables);
      return $result !== false;
    } catch (\Exception $e) {
      // Log the error if write fails

      return false;
    }
  }
}
