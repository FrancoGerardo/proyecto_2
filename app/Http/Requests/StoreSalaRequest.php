<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('crear-salas');
    }

    public function rules(): array
    {
        return [
            'numero' => 'required|string|max:50|unique:salas,numero',
            'categoria' => 'required|string|max:100',
            'estado' => 'required|in:DISPONIBLE,OCUPADA,MANTENIMIENTO,INACTIVA',
            'capacidad' => 'required|integer|min:1',
            'equipamiento' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'numero.required' => 'El número de sala es obligatorio.',
            'numero.unique' => 'Este número de sala ya existe.',
            'numero.max' => 'El número de sala no puede exceder 50 caracteres.',
            'categoria.required' => 'La categoría es obligatoria.',
            'categoria.max' => 'La categoría no puede exceder 100 caracteres.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'capacidad.required' => 'La capacidad es obligatoria.',
            'capacidad.integer' => 'La capacidad debe ser un número entero.',
            'capacidad.min' => 'La capacidad debe ser al menos 1.',
            'equipamiento.max' => 'El equipamiento no puede exceder 500 caracteres.',
        ];
    }
}

