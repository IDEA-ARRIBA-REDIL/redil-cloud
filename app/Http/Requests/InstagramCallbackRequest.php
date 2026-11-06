<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class InstagramCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->roles()->wherePivot('activo', true)->first()
            ?->hasAnyPermission('configuraciones.subitem_general') ?? false;
    }

    public function rules(): array
    {
        return [
            'state' => ['required', 'string', 'size:64'],
            'code' => ['nullable', 'string', 'max:4096'],
            'error' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'state.required' => 'Falta la verificación de la conexión.',
            'state.string' => 'La verificación de la conexión no es válida.',
            'state.size' => 'La verificación de la conexión no es válida.',
            'code.string' => 'El código de autorización no es válido.',
            'code.max' => 'El código de autorización no es válido.',
            'error.string' => 'La respuesta de Instagram no es válida.',
            'error.max' => 'La respuesta de Instagram no es válida.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        abort(400, 'La respuesta de Instagram no es válida. Inicia la conexión nuevamente.');
    }
}
