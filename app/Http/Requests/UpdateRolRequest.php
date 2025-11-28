<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('editar-roles');
    }

    public function rules(): array
    {
        $rolId = $this->route('id');
        
        return [
            'name' => 'required|string|max:255|unique:roles,name,' . $rolId,
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

