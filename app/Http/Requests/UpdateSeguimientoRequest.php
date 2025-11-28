<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeguimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyPermission(['editar-seguimientos']);
    }

    public function rules(): array
    {
        return [
            'ficha_id' => 'required|string|exists:fichas,id',
            'tipo' => 'required|in:TRIAGE,CONSULTA,TRATAMIENTO',
            'fecha' => 'required|date',
            'signos_vitales' => 'nullable|array',
            'motivo_consulta' => 'nullable|string',
            'nivel_urgencia' => 'nullable|in:BAJA,MEDIA,ALTA,URGENTE',
            'diagnostico' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'tratamiento_prescrito' => 'nullable|string',
            'medicamentos' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'ficha_id.required' => 'La ficha es obligatoria.',
            'ficha_id.exists' => 'La ficha seleccionada no existe.',
            'tipo.required' => 'El tipo es obligatorio.',
            'tipo.in' => 'El tipo seleccionado no es válido.',
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date' => 'La fecha debe ser válida.',
            'signos_vitales.array' => 'Los signos vitales deben ser un array.',
            'nivel_urgencia.in' => 'El nivel de urgencia seleccionado no es válido.',
            'medicamentos.array' => 'Los medicamentos deben ser un array.',
        ];
    }
}

