<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearParienteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'parientePrincipal' => ['required', 'integer', 'exists:users,id,deleted_at,NULL'],
            'buscador_asistente_modal' => ['required', 'integer', 'different:parientePrincipal', 'exists:users,id,deleted_at,NULL'],
            'tipoParentesco' => ['required', 'integer', 'exists:tipos_parentesco,id'],
            'responsabilidad' => ['required', 'integer', 'in:1,2,3'],
        ];
    }

    public function messages(): array
    {
        return [
            'parientePrincipal.*' => 'Selecciona una persona vigente.',
            'buscador_asistente_modal.*' => 'Selecciona un familiar vigente distinto de la persona principal.',
            'tipoParentesco.*' => 'Selecciona un tipo de parentesco válido.',
            'responsabilidad.*' => 'Selecciona una opción de responsabilidad válida.',
        ];
    }
}
