<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePermisoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('crear-permisos');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:permisos,name',
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

