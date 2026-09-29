<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InformeMateriaPorSedeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['sede_id' => ['required', 'integer', Rule::exists('sedes', 'id')]];
    }

    public function messages(): array
    {
        return [
            'sede_id.required' => 'Selecciona la sede para descargar el informe.',
            'sede_id.integer' => 'La sede seleccionada no es válida.',
            'sede_id.exists' => 'La sede seleccionada no existe.',
        ];
    }
}
