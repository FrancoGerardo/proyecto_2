<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEspecialidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyPermission(['editar-especialidades']);
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:256',
            'estado' => 'required|in:ACTIVA,INACTIVA',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la especialidad es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 50 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder 256 caracteres.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];
    }
}
