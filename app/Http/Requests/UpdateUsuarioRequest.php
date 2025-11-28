<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('editar-usuarios');
    }

    public function rules(): array
    {
        $usuarioId = $this->route('id');
        $usuario = \App\Models\User::with('medico')->findOrFail($usuarioId);
        $personaId = $usuario->persona_id;
        $medicoId = $usuario->medico ? $usuario->medico->usuario_id : 'NULL';
        
        return [
            // Datos de Persona
            'dni' => 'required|string|max:20|unique:personas,dni,' . $personaId,
            'nombre' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
            'fecha_nacimiento' => 'nullable|date',
            
            // Datos de Usuario
            'email' => 'required|string|email|max:255|unique:usuarios,email,' . $usuarioId,
            'password' => 'nullable|string|min:8|confirmed',
            'tipo_usuario' => 'required|in:PROPIETARIO,SECRETARIA,MEDICO,CLIENTE',
            
            // Datos específicos según tipo
            'nivel_acceso' => 'required_if:tipo_usuario,PROPIETARIO|string|max:50',
            'turno' => 'nullable|string|max:50',
            'especialidades' => 'nullable|required_if:tipo_usuario,MEDICO|array',
            'especialidades.*' => 'exists:especialidades,id',
            'horarios' => 'nullable|array',
            'horarios.*.dia_semana' => 'required_with:horarios|in:LUNES,MARTES,MIERCOLES,JUEVES,VIERNES,SABADO,DOMINGO',
            'horarios.*.hora_inicio' => 'required_with:horarios|date_format:H:i',
            'horarios.*.hora_fin' => 'required_with:horarios|date_format:H:i',
            'codigo_cmp' => 'nullable|string|max:50|unique:medicos,codigo_cmp,' . $medicoId . ',usuario_id',
            'horario_atencion' => 'nullable|string',
            'antecedentes' => 'nullable|string',
            
            // Roles
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ];
    }

    public function messages(): array
    {
        return [
            // Persona
            'dni.required' => 'El DNI es obligatorio.',
            'dni.unique' => 'Este DNI ya está registrado.',
            'nombre.required' => 'El nombre es obligatorio.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser válida.',
            
            // Usuario
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'tipo_usuario.required' => 'El tipo de usuario es obligatorio.',
            'tipo_usuario.in' => 'El tipo de usuario seleccionado no es válido.',
            
            // Propietario
            'nivel_acceso.required_if' => 'El nivel de acceso es obligatorio para propietarios.',
            
            // Médico
            'especialidades.required_if' => 'Debe seleccionar al menos una especialidad para médicos.',
            'especialidades.array' => 'Las especialidades deben ser un array.',
            'especialidades.*.exists' => 'Una o más especialidades seleccionadas no son válidas.',
            'horarios.array' => 'Los horarios deben ser un conjunto de registros.',
            'horarios.*.dia_semana.required_with' => 'Debe seleccionar el día para cada horario.',
            'horarios.*.dia_semana.in' => 'El día seleccionado no es válido.',
            'horarios.*.hora_inicio.required_with' => 'La hora de inicio es obligatoria.',
            'horarios.*.hora_inicio.date_format' => 'La hora de inicio debe tener el formato HH:MM.',
            'horarios.*.hora_fin.required_with' => 'La hora de fin es obligatoria.',
            'horarios.*.hora_fin.date_format' => 'La hora de fin debe tener el formato HH:MM.',
            'codigo_cmp.unique' => 'Este código CMP ya está registrado.',
            
            // Roles
            'roles.array' => 'Los roles deben ser un array.',
            'roles.*.exists' => 'Uno o más roles seleccionados no son válidos.',
        ];
    }
}

