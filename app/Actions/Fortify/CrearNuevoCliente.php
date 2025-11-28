<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Persona;
use App\Models\Cliente;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Models\Role;

class CrearNuevoCliente implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validar y crear un nuevo cliente.
     *
     * @param  array<string, string>  $datos
     * @return User
     */
    public function create(array $datos): User
    {
        // Validar datos
        Validator::make($datos, [
            'dni' => ['required', 'string', 'max:20', 'unique:personas,dni'],
            'nombre' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:usuarios,email'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ], [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.unique' => 'Este DNI ya está registrado.',
            'nombre.required' => 'El nombre es obligatorio.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'terms.accepted' => 'Debes aceptar los términos y condiciones.',
        ])->validate();

        // 1. Crear Persona
        $personaId = Str::uuid()->toString();
        $persona = Persona::create([
            'id' => $personaId,
            'dni' => $datos['dni'],
            'nombre' => $datos['nombre'],
            'apellidos' => $datos['apellidos'],
            'telefono' => $datos['telefono'] ?? null,
            'direccion' => $datos['direccion'] ?? null,
            'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
        ]);

        // 2. Crear Usuario como CLIENTE
        $usuarioId = Str::uuid()->toString();
        $usuario = User::create([
            'id' => $usuarioId,
            'persona_id' => $persona->id,
            'email' => $datos['email'],
            'password_hash' => Hash::make($datos['password']),
            'tipo_usuario' => 'CLIENTE',
            'estado' => true,
            'fecha_registro' => now(),
        ]);

        // 3. Crear registro en tabla clientes
        Cliente::create([
            'usuario_id' => $usuario->id,
            'antecedentes' => null,
        ]);

        // 4. Asignar rol Cliente (si existe)
        $rolCliente = Role::firstOrCreate(
            ['name' => 'Cliente'],
            ['guard_name' => 'web']
        );
        
        if (!$usuario->hasRole('Cliente')) {
            $usuario->assignRole('Cliente');
        }

        return $usuario;
    }
}

