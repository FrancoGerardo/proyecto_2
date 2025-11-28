<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHistorialClinicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyPermission(['crear-historiales-clinicos']);
    }

    public function rules(): array
    {
        return [
            'cliente_id' => 'required|string|exists:clientes,usuario_id|unique:historiales_clinicos,cliente_id',
            'alergias' => 'nullable|string',
            'enfermedades_cronicas' => 'nullable|string',
            'medicamentos_habituales' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'El cliente es obligatorio.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'cliente_id.unique' => 'Este cliente ya tiene un historial clínico registrado.',
        ];
    }
}

