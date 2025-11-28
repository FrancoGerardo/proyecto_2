<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyPermission(['editar-reportes']);
    }

    public function rules(): array
    {
        return [
            'tipo' => 'required|in:FINANCIERO,CLINICO,OPERATIVO',
            'nombre' => 'required|string|max:100',
            'parametros' => 'nullable|array',
            'url_archivo' => 'nullable|string|max:255',
            'estado' => 'required|in:GENERANDO,COMPLETADO,ERROR',
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.required' => 'El tipo de reporte es obligatorio.',
            'tipo.in' => 'El tipo seleccionado no es válido.',
            'nombre.required' => 'El nombre del reporte es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres.',
            'parametros.array' => 'Los parámetros deben ser un array.',
            'url_archivo.max' => 'La URL del archivo no puede exceder 255 caracteres.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];
    }
}

