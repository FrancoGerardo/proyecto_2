<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Permission;

class UpdatePermisoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('editar-permisos');
    }

    public function rules(): array
    {
        $permiso = Permission::findOrFail($this->route('id'));
        
        return [
            'name' => 'required|string|max:255|unique:permisos,name,' . $permiso->id,
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del permiso es obligatorio.',
            'name.unique' => 'Este permiso ya existe.',
            'name.max' => 'El nombre del permiso no puede exceder 255 caracteres.',
        ];
    }
}

