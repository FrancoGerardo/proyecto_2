<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFichaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyPermission(['crear-fichas']);
    }

    public function rules(): array
    {
        return [
            'cliente_id' => 'required|string|exists:clientes,usuario_id',
            'servicio_id' => 'required|string|exists:servicios,id',
            'medico_id' => 'required|string|exists:medicos,usuario_id',
            'sala_id' => 'nullable|string|exists:salas,id',
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'estado' => 'nullable|in:PENDIENTE,CONFIRMADA,ATENDIDA,CANCELADA',
            'motivo_consulta' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'El cliente es obligatorio.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'servicio_id.required' => 'El servicio es obligatorio.',
            'servicio_id.exists' => 'El servicio seleccionado no existe.',
            'medico_id.required' => 'El médico es obligatorio.',
            'medico_id.exists' => 'El médico seleccionado no existe.',
            'sala_id.exists' => 'La sala seleccionada no existe.',
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date' => 'La fecha debe ser válida.',
            'hora.required' => 'La hora es obligatoria.',
            'hora.date_format' => 'La hora debe tener el formato HH:mm.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];
    }
}

