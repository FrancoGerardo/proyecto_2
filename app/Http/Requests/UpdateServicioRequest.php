<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyPermission(['editar-servicios']);
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'categoria' => 'required|in:INTERNACION,ESPECIALIDAD,ENFERMERIA',
            'especialidad_id' => 'nullable|required_if:categoria,ESPECIALIDAD|string|exists:especialidades,id',
            'meedico_id' => 'nullable|string|exists:usuarios,id',
            'costo' => 'required|numeric|min:0',
            'duracion_minutos' => 'nullable|integer|min:1',
            'estado' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del servicio es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres.',
            'categoria.required' => 'La categoría es obligatoria.',
            'categoria.in' => 'La categoría seleccionada no es válida.',
            'especialidad_id.required_if' => 'La especialidad es obligatoria cuando la categoría es ESPECIALIDAD.',
            'especialidad_id.exists' => 'La especialidad seleccionada no existe.',
            'costo.required' => 'El costo es obligatorio.',
            'costo.numeric' => 'El costo debe ser un número.',
            'costo.min' => 'El costo debe ser mayor o igual a 0.',
            'duracion_minutos.integer' => 'La duración debe ser un número entero.',
            'duracion_minutos.min' => 'La duración debe ser al menos 1 minuto.',
        ];
    }
}

