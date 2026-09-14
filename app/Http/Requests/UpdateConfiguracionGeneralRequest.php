<?php

namespace App\Http\Requests;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Foundation\Http\FormRequest;

class UpdateConfiguracionGeneralRequest extends FormRequest
{
    public function authorize(): bool
    {
        $rolActivo = $this->user()?->roles()->wherePivot('activo', true)->first();

        return $rolActivo?->hasAnyPermission('configuraciones.subitem_general') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $usaDiaCorte = $this->input('habilitarDiasCorte') === 'on';

        return [
            'version' => ['required', 'integer', 'min:1'],
            'LimiteMenorEdad' => ['required', 'integer', 'min:0'],
            'nombreAppPersonalizada' => ['nullable', 'string', 'max:255'],
            'labelSeccionCamposExtra' => ['nullable', 'string', 'max:100'],

            'diaCorteReporteGrupos' => [$usaDiaCorte ? 'required' : 'nullable', 'integer', 'between:1,7'],
            'diaRecordatorioParaReporteGrupos' => ['nullable', 'integer', 'between:1,7'],
            'horaRecordatorioParaReporteGrupos' => ['nullable', 'date_format:H:i'],
            'diaPlazoReporteGrupo' => [$usaDiaCorte ? 'nullable' : 'required', 'integer', 'min:0'],
            'maximosNivelesGraficoMinisterio' => ['required', 'integer', 'min:1'],
            'tituloSeccionReunionGrupo' => ['nullable', 'string', 'max:50'],
            'labelCampoHoraReunionGrupo' => ['nullable', 'string', 'max:50'],
            'labelCampoDiaReunionGrupo' => ['nullable', 'string', 'max:50'],
            'labelDireccionGrupo' => ['nullable', 'string', 'max:100'],
            'labelCreacionGrupo' => ['nullable', 'string', 'max:100'],
            'labelCampoOpcional1' => ['nullable', 'string', 'max:50'],

            'labelCampo1InformeEvidenciasGrupo' => ['nullable', 'string', 'max:50'],
            'labelCampo2InformeEvidenciasGrupo' => ['nullable', 'string', 'max:50'],
            'labelCampo3InformeEvidenciasGrupo' => ['nullable', 'string', 'max:50'],

            'tiempoParaDefinirInactivoGrupo' => ['nullable', 'integer', 'min:0'],
            'tiempoParaDefinirInactivoReunion' => ['nullable', 'integer', 'min:0'],
            'edadMinimaLogueo' => ['required', 'integer', 'min:0'],
            'tituloMensajeBienvenida' => ['nullable', 'string', 'max:100'],
            'mensajeBienvenida' => ['nullable', 'string', 'max:50000'],

            'nombreResaltadorInformeMensualReportesGrupo' => ['required', 'string', 'max:50'],
            'valorMinimoResaltadorInformeMensualReportesGrupo' => ['required', 'integer'],
            'valorMaximoResaltadorInformeMensualReportesGrupo' => ['required', 'integer', 'gte:valorMinimoResaltadorInformeMensualReportesGrupo'],

            'labelInvitadoReuniones' => ['nullable', 'string', 'max:50'],
            'labelObservacionInvitadosModal' => ['nullable', 'string'],
            'textDefaultObservacionInvitadosModal' => ['nullable', 'string'],

            'mensajeCorreoPuntoPago' => ['nullable', 'string'],
            'monedaPredeterminadaPuntoPago' => ['nullable', 'integer', 'min:1'],

            'labelCampoadicional1Ingresos' => ['required', 'string', 'max:50'],
            'labelCampoadicional2Ingresos' => ['required', 'string', 'max:50'],
            'labelCampoadicional1Egresos' => ['required', 'string', 'max:50'],
            'labelCampoadicional2Egresos' => ['required', 'string', 'max:50'],

            'cantidadDiasAlertaNotasMaestro' => ['nullable', 'integer', 'min:0'],
            'cantidadIntentosAutoMatricula' => ['nullable', 'integer', 'min:1'],
            'cantidadIntentosTraslados' => ['required', 'integer', 'min:1'],
            'diasPlazoMaximoActualizacionAutomatricula' => ['nullable', 'integer', 'min:0'],
            'mensajeExitoAutoMatricula' => ['nullable', 'string'],
            'mensajeErrorAutoMatricula' => ['nullable', 'string'],
            'mensajeExisteAutoMatricula' => ['nullable', 'string'],

            'edadMinimaConsolidacion' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'version.required' => 'La versión del sistema es obligatoria.',
            'LimiteMenorEdad.required' => 'Debe especificar un límite mínimo de edad.',
            'maximosNivelesGraficoMinisterio.required' => 'Debe indicar los niveles máximos del gráfico del ministerio.',
            'diaCorteReporteGrupos.required' => 'Día corte reporte grupo es un campo obligatorio.',
            'diaPlazoReporteGrupo.required' => 'Día plazo reporte grupo es un campo obligatorio.',
            'edadMinimaLogueo.required' => 'Debe indicar una edad mínima para el inicio de sesión.',
            'labelCampoadicional1Ingresos.required' => 'Debe ingresar el nombre del campo adicional 1 en ingresos.',
            'labelCampoadicional2Ingresos.required' => 'Debe ingresar el nombre del campo adicional 2 en ingresos.',
            'labelCampoadicional1Egresos.required' => 'Debe ingresar el nombre del campo adicional 1 en egresos.',
            'labelCampoadicional2Egresos.required' => 'Debe ingresar el nombre del campo adicional 2 en egresos.',
            'nombreResaltadorInformeMensualReportesGrupo.required' => 'Debe indicar el nombre del resaltador para los informes mensuales.',
            'valorMinimoResaltadorInformeMensualReportesGrupo.required' => 'Debe indicar el valor mínimo del resaltador.',
            'valorMaximoResaltadorInformeMensualReportesGrupo.required' => 'Debe indicar el valor máximo del resaltador.',
            'valorMaximoResaltadorInformeMensualReportesGrupo.gte' => 'El valor máximo debe ser mayor o igual al valor mínimo.',
            'cantidadIntentosTraslados.required' => 'Debe indicar la cantidad de intentos permitidos para traslados.',
            'edadMinimaConsolidacion.required' => 'Debe especificar un límite mínimo de edad.',
        ];
    }

    protected function passedValidation(): void
    {
        if (! is_string($this->input('mensajeBienvenida'))) {
            return;
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p[class|style],br,strong,b,em,i,u,s,h1,h2,h3,h4,h5,h6,span[class|style],ol,ul,li,a[href|target|rel],blockquote,img[src|alt|width|height]');
        $config->set('CSS.AllowedProperties', ['color', 'background-color', 'text-align', 'font-size', 'font-family']);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);

        $this->merge([
            'mensajeBienvenida' => (new HTMLPurifier($config))->purify($this->input('mensajeBienvenida')),
        ]);
    }
}
