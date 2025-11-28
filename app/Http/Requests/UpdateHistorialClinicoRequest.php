<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHistorialClinicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyPermission(['editar-historiales-clinicos']);
    }

    public function rules(): array
    {
        return [
            'alergias' => 'nullable|string',
            'enfermedades_cronicas' => 'nullable|string',
            'medicamentos_habituales' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            // No hay mensajes específicos ya que todos los campos son opcionales
        ];
    }
}

