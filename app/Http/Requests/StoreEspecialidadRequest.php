<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEspecialidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyPermission(['crear-especialidades']);
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:256',
            'estado' => 'nullable|in:ACTIVA,INACTIVA',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la especialidad es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 50 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder 256 caracteres.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];
    }
}
