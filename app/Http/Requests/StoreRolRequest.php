<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('crear-roles');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name',
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permisos,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.unique' => 'Este rol ya existe.',
            'name.max' => 'El nombre del rol no puede exceder 255 caracteres.',
            'permisos.array' => 'Los permisos deben ser un array.',
            'permisos.*.exists' => 'Uno o más permisos seleccionados no son válidos.',
        ];
    }
}

