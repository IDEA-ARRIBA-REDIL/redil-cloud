<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportMegaInformeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'grupo_id' => 'required|exists:grupos,id',
            'agrupar_por_tipo_grupo_id' => 'required|exists:tipo_grupos,id',
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'periodo' => 'required|string|in:semana,1m,2m,3m,4m,5m,6m,7m,8m,9m,10m,11m,12m,1t,2t,3t,4t,1s,2s,anio',
            'semana' => 'nullable|required_if:periodo,semana|string',
            'email' => 'required|email|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'grupo_id.required' => 'Debes seleccionar un grupo raíz o ministerio.',
            'grupo_id.exists' => 'El grupo seleccionado no es válido.',
            'agrupar_por_tipo_grupo_id.required' => 'Debes seleccionar el tipo de grupo por el cual deseas agrupar.',
            'year.required' => 'El año es obligatorio.',
            'periodo.required' => 'El periodo es obligatorio.',
            'semana.required_if' => 'Debes especificar la semana cuando el periodo seleccionado es Por semana.',
            'email.required' => 'El correo electrónico para recibir el informe es obligatorio.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
        ];
    }
}
