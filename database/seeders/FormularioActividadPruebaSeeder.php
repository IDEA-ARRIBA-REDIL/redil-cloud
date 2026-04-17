<?php

namespace Database\Seeders;

use App\Models\ElementoFormularioActividad;
use App\Models\OpcionesElementoFormularioActividad;
use App\Models\TipoElementoFormularioActividad;
use Illuminate\Database\Seeder;

class FormularioActividadPruebaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $actividadId = 11;

        // Limpiamos los elementos previos de esta actividad y sus opciones
        $elementosAnteriores = ElementoFormularioActividad::where('actividad_id', $actividadId)->get();
        foreach ($elementosAnteriores as $elemento) {
            $elemento->opciones()->delete();
            $elemento->delete();
        }

        // Obtenemos los tipos de elementos formdinámico indexados por "clase"
        $tipos = TipoElementoFormularioActividad::all()->keyBy('clase');

        // Helper por si falta alguno, evita fallos.
        $getTipoId = function ($clase) use ($tipos) {
            return $tipos->has($clase) ? $tipos[$clase]->id : 1;
        };

        // Creamos un dummy payload de 15 preguntas
        $elementosBase = [
            [
                'actividad_id' => $actividadId,
                'tipo_elemento_id' => $getTipoId('encabezado'),
                'titulo' => 'Información Médica del Participante',
                'descripcion' => 'Requerimos tus antecedentes para asegurar tu bienestar durante nuestro evento.',
                'required' => false, 'visible' => true, 'visible_asistencia' => false, 'orden' => 0,
            ],
            [
                'actividad_id' => $actividadId,
                'tipo_elemento_id' => $getTipoId('corta'),
                'titulo' => 'Empresa (EPS) de Salud',
                'descripcion' => 'La empresa donde estás afiliado formalmente.',
                'required' => true, 'visible' => true, 'visible_asistencia' => true, 'orden' => 1,
            ],
            [
                'actividad_id' => $actividadId,
                'tipo_elemento_id' => $getTipoId('si_no'),
                'titulo' => '¿Padeces alguna afección de salud cardíaca?',
                'descripcion' => 'Condiciones críticas crónicas y patologías relacionadas al corazón.',
                'required' => true, 'visible' => true, 'visible_asistencia' => true, 'orden' => 2,
            ],
            [
                'actividad_id' => $actividadId,
                'tipo_elemento_id' => $getTipoId('larga'),
                'titulo' => 'Medicamento que estés utilizando hoy',
                'descripcion' => 'Escribe las dosificaciones si aplicara.',
                'required' => false, 'visible' => true, 'visible_asistencia' => true, 'orden' => 3,
            ],
            [
                'actividad_id' => $actividadId,
                'tipo_elemento_id' => $getTipoId('encabezado'),
                'titulo' => 'Congregación y Crecimiento',
                'descripcion' => 'Métricas ministeriales.',
                'required' => false, 'visible' => true, 'visible_asistencia' => false, 'orden' => 4,
            ],
            [
                'actividad_id' => $actividadId,
                'tipo_elemento_id' => $getTipoId('unica_respuesta'),
                'titulo' => '¿En cuál sede de Iglesia Infantil asiste tu hijo/a?',
                'descripcion' => 'Solo selecciona la local habitual.',
                'required' => true, 'visible' => true, 'visible_asistencia' => false, 'orden' => 5,
                'opciones' => ['Sede Central (Suramérica)', 'Sede Norte', 'Sede Este (Laurel)', 'Solo Online'],
            ],
            [
                'actividad_id' => $actividadId,
                'tipo_elemento_id' => $getTipoId('multiple_respuesta'),
                'titulo' => '¿Qué voluntariados te gustaría probar?',
                'descripcion' => 'Servir en equipos durante el evento.',
                'required' => true, 'visible' => true, 'visible_asistencia' => false, 'orden' => 6,
                'opciones' => ['Logística y Ujieres', 'Comunicaciones (Media)', 'Alabanza', 'Liderazgo en Células'],
            ],
            [
                'actividad_id' => $actividadId,
                'tipo_elemento_id' => $getTipoId('unica_respuesta'),
                'titulo' => 'Tamaño de Talla (Sweater Corporal)',
                'descripcion' => 'Te regalaremos indumentario corporativo.',
                'required' => true, 'visible' => true, 'visible_asistencia' => false, 'orden' => 7,
                'opciones' => ['XS - Extra Pequeña', 'S - Pequeña', 'M - Mediana', 'L - Grande', 'XL - Extra Grande'],
            ],
            [
                'actividad_id' => $actividadId,
                'tipo_elemento_id' => $getTipoId('encabezado'),
                'titulo' => 'Información Bancaria',
                'descripcion' => 'Estos datos se guardarán de acuerdo a normas de cifrado y tratamiento.',
                'required' => false, 'visible' => true, 'visible_asistencia' => false, 'orden' => 8,
            ],
            [
                'actividad_id' => $actividadId,
                'tipo_elemento_id' => $getTipoId('numero'),
                'titulo' => 'Años que llevas afiliado',
                'descripcion' => 'Respuesta numérica simple.',
                'required' => true, 'visible' => true, 'visible_asistencia' => false, 'orden' => 9,
            ],
            [
                'actividad_id' => $actividadId,
                'tipo_elemento_id' => $getTipoId('fecha'),
                'titulo' => 'Fecha de Renovación de Carnet',
                'descripcion' => '',
                'required' => false, 'visible' => true, 'visible_asistencia' => false, 'orden' => 10,
            ],
            [
                'actividad_id' => $actividadId,
                'tipo_elemento_id' => $getTipoId('moneda'),
                'titulo' => 'Donación Sugerida (USD)',
                'descripcion' => '',
                'required' => false, 'visible' => true, 'visible_asistencia' => false, 'orden' => 11,
            ],
            [
                'actividad_id' => $actividadId,
                'tipo_elemento_id' => $getTipoId('archivo'),
                'titulo' => 'Póliza de Seguros o Certificado PDF',
                'descripcion' => 'Asegúrate de que no supere un maximo superior a 10MB.',
                'required' => false, 'visible' => true, 'visible_asistencia' => false, 'orden' => 12,
                'peso_maximo' => 10,
            ],
            [
                'actividad_id' => $actividadId,
                'tipo_elemento_id' => $getTipoId('imagen'),
                'titulo' => 'Avatar de Contacto',
                'descripcion' => 'Sube imagen JPG o PNG del rostro, formato 500x500px min.',
                'required' => false, 'visible' => true, 'visible_asistencia' => true, 'orden' => 13,
                'peso_maximo' => 5,
            ],
            [
                'actividad_id' => $actividadId,
                'tipo_elemento_id' => $getTipoId('si_no'),
                'titulo' => '¿Aceptas el Habeas Data?',
                'descripcion' => 'Esta acción es imprescindible e inapelable.',
                'required' => true, 'visible' => true, 'visible_asistencia' => false, 'orden' => 14,
            ],
        ];

        // Insertar en Base de Datos de manera eficiente
        foreach ($elementosBase as $elementoData) {
            $opciones = $elementoData['opciones'] ?? null;
            unset($elementoData['opciones']);

            $elemento = ElementoFormularioActividad::create($elementoData);

            if ($opciones) {
                foreach ($opciones as $valor) {
                    OpcionesElementoFormularioActividad::create([
                        'elemento_formulario_actividad_id' => $elemento->id,
                        'valor_texto' => $valor,
                    ]);
                }
            }
        }
    }
}
