<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

use App\Models\FormularioUsuario;

class FormularioUsuarioSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {

    FormularioUsuario::firstOrCreate([
      'nombre' => 'Formulario nuevo',
      'titulo' => 'Nuevo persona',
      'label' => 'Nuevo persona',
      'descripcion' => 'Aquí puedes ingresar el nuevo usuarios mayores a 18 años.',
      'tipo_formulario_id' => 1,
      'edad_minima' => 18,
      'edad_maxima' => 200,
      'tipo_usuario_default_id' => 4,
    ]);

    FormularioUsuario::firstOrCreate([
      'nombre' => 'Modificar adulto',
      'titulo' => 'Editar usuario',
      'label' => 'Editar',
      'descripcion' => 'Aquí puedes editar usuarios mayores a 18 años.',
      'tipo_formulario_id' => 2,
      'edad_minima' => 18,
      'edad_maxima' => 200,
      'edad_mensaje_error' => 'Lo sentimos, no tienes suficientes privilegios para realizar este cambio de fecha.'
    ]);

    FormularioUsuario::firstOrCreate([
      'nombre' => 'Modificar menores',
      'titulo' => 'Editar usuario',
      'label' => 'Editar',
      'descripcion' => 'Aquí puedes editar usuarios menores de 18 años.',
      'tipo_formulario_id' => 2,
      'edad_minima' => 0,
      'edad_maxima' => 17,
      'edad_mensaje_error' => 'Lo sentimos, no tienes suficientes privilegios para realizar este cambio de fecha.'
    ]);

    FormularioUsuario::firstOrCreate([
      'nombre' => 'Formulario delete',
      'titulo' => 'Editar usuario',
      'label' => 'Editar',
      'descripcion' => 'Este formulario esta dado baja.',
      'tipo_formulario_id' => 2,
      'edad_minima' => 0,
      'edad_maxima' => 1000,
      'edad_mensaje_error' => 'Lo sentimos, no tienes suficientes privilegios para realizar este cambio de fecha.',
      'deleted_at' => '2024-12-21 12:23:28',
      'mensaje_terminos_condiciones_resumen' => 'Aquí va la descripción corta de los <b>T&C</b>',
      'mensaje_terminos_condiciones_detallado' => '<b>Lorem Ipsum </b> es simplemente el texto de relleno de las imprentas y archivos de texto. Lorem Ipsum ha sido el texto de relleno estándar de las industrias desde el año 1500, cuando un impresor (N. del T. persona que se dedica a la imprenta) desconocido usó una galería de textos y los mezcló de tal manera que logró hacer un libro de textos especimen. No sólo sobrevivió 500 años, sino que tambien ingresó como texto de relleno en documentos electrónicos, quedando esencialmente igual al original. Fue popularizado en los 60s con la creación de las hojas "Letraset", las cuales contenian pasajes de Lorem Ipsum, y más recientemente con software de autoedición, como por ejemplo Aldus PageMaker, el cual incluye versiones de Lorem Ipsum.
                                                   <br> <br> Al contrario del pensamiento popular, el texto de Lorem Ipsum no es simplemente texto aleatorio. Tiene sus raices en una pieza cl´sica de la literatura del Latin, que data del año 45 antes de Cristo, haciendo que este adquiera mas de 2000 años de antiguedad. Richard McClintock, un profesor de Latin de la Universidad de Hampden-Sydney en Virginia, encontró una de las palabras más oscuras de la lengua del latín, "consecteur", en un pasaje de Lorem Ipsum, y al seguir leyendo distintos textos del latín, descubrió la fuente indudable. Lorem Ipsum viene de las secciones 1.10.32 y 1.10.33 de "de Finnibus Bonorum et Malorum" (Los Extremos del Bien y El Mal) por Cicero, escrito en el año 45 antes de Cristo. Este libro es un tratado de teoría de éticas, muy popular durante el Renacimiento. La primera linea del Lorem Ipsum, "Lorem ipsum dolor sit amet..", viene de una linea en la sección 1.10.32'
    ]);

    FormularioUsuario::firstOrCreate([
      'nombre' => 'Formulario nuevo externo',
      'titulo' => 'Inscripción',
      'label' => 'Registrarte',
      'descripcion' => 'Es formulario es para agregar los nuevos sin logueo',
      'tipo_formulario_id' => 3,
      'edad_minima' => 18,
      'edad_maxima' => 200,
      'tipo_usuario_default_id' => 4,
      'edad_mensaje_error' => 'Lo sentimos, no tienes suficientes privilegios para realizar este cambio de fecha.',
      'visible_terminos_condiciones' => false,
      'label_terminos_condiciones' => 'Términos y condiciones para menor de edad',
      'mensaje_terminos_condiciones_resumen' => 'Aquí va la descripción corta de los <b>T&C</b>',
      'mensaje_terminos_condiciones_detallado' => '<b>Lorem Ipsum </b> es simplemente el texto de relleno de las imprentas y archivos de texto. Lorem Ipsum ha sido el texto de relleno estándar de las industrias desde el año 1500, cuando un impresor (N. del T. persona que se dedica a la imprenta) desconocido usó una galería de textos y los mezcló de tal manera que logró hacer un libro de textos especimen. No sólo sobrevivió 500 años, sino que tambien ingresó como texto de relleno en documentos electrónicos, quedando esencialmente igual al original. Fue popularizado en los 60s con la creación de las hojas "Letraset", las cuales contenian pasajes de Lorem Ipsum, y más recientemente con software de autoedición, como por ejemplo Aldus PageMaker, el cual incluye versiones de Lorem Ipsum. <br> <br> Al contrario del pensamiento popular, el texto de Lorem Ipsum no es simplemente texto aleatorio. Tiene sus raices en una pieza cl´sica de la literatura del Latin, que data del año 45 antes de Cristo, haciendo que este adquiera mas de 2000 años de antiguedad. Richard McClintock, un profesor de Latin de la Universidad de Hampden-Sydney en Virginia, encontró una de las palabras más oscuras de la lengua del latín, "consecteur", en un pasaje de Lorem Ipsum, y al seguir leyendo distintos textos del latín, descubrió la fuente indudable. Lorem Ipsum viene de las secciones 1.10.32 y 1.10.33 de "de Finnibus Bonorum et Malorum" (Los Extremos del Bien y El Mal) por Cicero, escrito en el año 45 antes de Cristo. Este libro es un tratado de teoría de éticas, muy popular durante el Renacimiento. La primera linea del Lorem Ipsum, "Lorem ipsum dolor sit amet..", viene de una linea en la sección 1.10.32'

    ]);

    //6
    FormularioUsuario::firstOrCreate([
      'nombre' => 'Formulario nuevo menor tipo step',
      'titulo' => 'Crear menor',
      'label' => 'Registrar hijo',
      'descripcion' => 'Es formulario es para agregar los nuevos sin logueo',
      'tipo_formulario_id' => 4,
      'edad_minima' => 0,
      'edad_maxima' => 17,
      'tipo_usuario_default_id' => 4,
      'edad_mensaje_error' => 'Este formulario es sólo para menores de edad.',
      'label_terminos_condiciones' => 'AUTORIZACIÓN TRATAMIENTO DE DATOS PERSONALES Y GRABACIÓN',
      'mensaje_terminos_condiciones_resumen' => 'Aquí va la descripción corta de los <b>T&C</b>',
      'mensaje_terminos_condiciones_detallado' => '
        Acepto que estoy obrando como <b>representante del menor de edad que estoy registrando</b>, de acuerdo con lo dispuesto en las normas vigentes sobre protección de datos personales, en especial la Ley 1581 de 2012 y el Decreto 1074 de 2015, autorizo libre, expresa e inequívocamente a la IGLESIA MANANTIAL DE VIDA ETERNA para captar y divulgar fotos, audios, videos u otros datos personales del menor, e incorporarlos en una base de datos de responsabilidad de la Iglesia, con la finalidad de usarse en actividades asociativas, culturales, recreativas, deportivas y sociales - Gestión de medios de comunicación social y/o contenido editorial, publicaciones y/o fines históricos y estadísticos.
        <br><br>
        Foto
        <br><br>
        La autorización comprende: i. La participación del menor en las actividades de la IGLESIA MANANTIAL DE VIDA ETERNA ii. captar, tomar, almacenar y editar imágenes personales o fotografías, realizar videos y audios según corresponda; iii. Divulgar y publicar las imágenes, audios o datos a través de cualquier medio físico, electrónico, virtual o de cualquier otra naturaleza,pública o privada, con el fin de hacer prevención, promoción de derechos, actividades lúdicas y culturales, etc. de los niños, niñas y adolescentes, para la IGLESIA MANANTIAL DE VIDA ETERNA y sus actuales, y futuros productos, servicios y marcas, garantizando que las actividades que se realizarán durante el desarrollo del proyecto se encuentran enmarcadas en el interés superior de los niños, niñas y adolescentes, y en el respeto de sus derechos fundamentales.
        <br><br>
        Manifiesto que como representante del menor de edad, fui informado que la recolección y tratamiento de los datos se realizará de acuerdo con la política de tratamiento de información y
        protección de datos personales, adoptada por la IGLESIA MANANTIAL DE VIDA ETERNA, así mismo, fui informado de los derechos con que cuento como representante del menor de edad,
        especialmente a: conocer, actualizar y rectificar la información personal, revocar la autorización y solicitar la supresión del dato, los cuales se podrán ejercer a través de los canales presenciales.
        <br><br>
        Con la suscripción del presente documento, dejo constancia de contar con el consentimiento del menor, cuando sus condiciones de madurez lo permitan, entendiendo, que no es obligado que dicho consentimiento sea por escrito. Así mismo, dejo constancia que conozco mi obligación de informar de la presente autorización al representante legal o judicial que no está presente.
        <br><br>
        Finalmente, autorizó a enviar información por parte de la Iglesia vía correo físico y/o electrónico,
        mensajes de texto (SMS y/o MMS), o cualquier otro medio de comunicación que la tecnología y la Ley permitan.
      '
    ]);

    //7
    FormularioUsuario::firstOrCreate([
      'nombre' => 'Formulario autoeditar',
      'titulo' => 'Autoeditar',
      'label' => 'Autoeditar',
      'descripcion' => 'Es formulario autoeditar la información',
      'tipo_formulario_id' => 5,
      'edad_minima' => 0,
      'edad_maxima' => 200,
      'edad_mensaje_error' => 'Este formulario es sólo para menores de edad.',
      'label_terminos_condiciones' => '',
      'mensaje_terminos_condiciones_resumen' => '',
      'mensaje_terminos_condiciones_detallado' => ''
    ]);

    // formulario 1
    DB::table('formulario_usuario_rol')->insert([
      'formulario_usuario_id' => 1,
      'rol_id' => 1,
    ]);

    DB::table('formulario_usuario_rol')->insert([
      'formulario_usuario_id' => 1,
      'rol_id' => 2,
    ]);

    DB::table('formulario_usuario_rol')->insert([
      'formulario_usuario_id' => 1,
      'rol_id' => 3,
    ]);

    DB::table('formulario_usuario_rol')->insert([
      'formulario_usuario_id' => 1,
      'rol_id' => 4,
    ]);

    DB::table('formulario_usuario_rol')->insert([
      'formulario_usuario_id' => 1,
      'rol_id' => 5,
    ]);

    // formulario 2
    DB::table('formulario_usuario_rol')->insert([
      'formulario_usuario_id' => 2,
      'rol_id' => 1,
    ]);

    // formulario 3
    DB::table('formulario_usuario_rol')->insert([
      'formulario_usuario_id' => 3,
      'rol_id' => 1,
    ]);

    // formulario 6
    DB::table('formulario_usuario_rol')->insert([
      'formulario_usuario_id' => 6,
      'rol_id' => 1,
    ]);

    DB::table('formulario_usuario_rol')->insert([
      'formulario_usuario_id' => 6,
      'rol_id' => 2,
    ]);

    DB::table('formulario_usuario_rol')->insert([
      'formulario_usuario_id' => 6,
      'rol_id' => 3,
    ]);

    DB::table('formulario_usuario_rol')->insert([
      'formulario_usuario_id' => 6,
      'rol_id' => 4,
    ]);

    DB::table('formulario_usuario_rol')->insert([
      'formulario_usuario_id' => 6,
      'rol_id' => 5,
    ]);


    // formulario 7
    DB::table('formulario_usuario_rol')->updateOrInsert(
      ['formulario_usuario_id' => 7, 'rol_id' => 1]
    );

    DB::table('formulario_usuario_rol')->updateOrInsert(
      ['formulario_usuario_id' => 7, 'rol_id' => 2]
    );

    DB::table('formulario_usuario_rol')->updateOrInsert(
      ['formulario_usuario_id' => 7, 'rol_id' => 3]
    );

    DB::table('formulario_usuario_rol')->updateOrInsert(
      ['formulario_usuario_id' => 7, 'rol_id' => 4]
    );

    DB::table('formulario_usuario_rol')->updateOrInsert(
      ['formulario_usuario_id' => 7, 'rol_id' => 5]
    );




  }
}
