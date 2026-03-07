<?php
// Variables
use App\Services\ThemeService;
use App\Models\ThemeSetting;
use App\Models\Configuracion;

//$settingColor = ThemeSetting::where('class', 'bg-menu-theme .menu-item')->first();
//$configuracion = Configuracion::find(1);


return [
  "creatorName" => "IDEARRIBA",
  "creatorUrl" => "https://redil.co",
  "templateName" => 'Crecer',
  "templateNameColor" => 'white',
  "templateDescriptionLogin" => "Descubre lo que Dios tiene para ti",
  "templateSuffix" => "Crecer, descubre lo que Dios tiene para ti",
  "templateVersion" => "2.0.0",
  "templateFree" => false,
  "templateDescription" => "Plataforma para la administración de iglesias, pastores, eventos y crecimiento espiritual del creyente.",
  "templateKeyword" => "Iglesias, pastoreo, administración de iglesias,crecimiento espiritual, evangelismo",
  "licenseUrl" => "https://themeforest.net/licenses/standard",
  "livePreview" => "https://demos.pixinvent.com/vuexy-html-admin-template/landing/",
  "productPage" => "https://1.envato.market/vuexy_admin",
  "support" => "https://pixinvent.ticksy.com/",
  "moreThemes" => "https://1.envato.market/pixinvent_portfolio",
  "documentation" => "https://demos.pixinvent.com/vuexy-html-admin-template/documentation",
  "generator" => "",
  "changelog" => "https://demos.pixinvent.com/vuexy/changelog.html",
  "repository" => "https://github.com/pixinvent/vuexy-html-admin-template",
  "gitRepo" => "pixinvent",
  "gitRepoAccess" => "vuexy-html-admin-template",
  "githubFreeUrl" => "https://tools.pixinvent.com/github/github-access",
  "facebookUrl" => "https://www.facebook.com/pixinvents/",
  "twitterUrl" => "https://twitter.com/pixinvents",
  "githubUrl" => "https://github.com/pixinvent",
  "dribbbleUrl" => "https://dribbble.com/pixinvent",
  "instagramUrl" => "https://www.instagram.com/pixinvents/",
  "biblia_key" => env('BIBLIA_KEY')
];
