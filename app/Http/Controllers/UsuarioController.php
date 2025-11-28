<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Persona;
use App\Models\Propietario;
use App\Models\Secretaria;
use App\Models\Medico;
use App\Models\Cliente;
use App\Models\Especialidad;
use App\Models\MedicoEspecialidad;
use App\Models\HorarioMedico;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function paginaPrincipalUsuario()
    {
        $this->authorize('gestionar-usuarios');

        $usuarios = User::with(['persona', 'roles', 'propietario', 'secretaria', 'medico.especialidades', 'medico.horarios', 'cliente'])->paginate(10);
        $roles = Role::all();
        $especialidades = Especialidad::where('estado', 'ACTIVA')->get();
        $contadorVisitas = DB::table('visitas_paginas')
            ->where('ruta', 'usuarios')
            ->count();

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles' => $roles,
            'especialidades' => $especialidades,
            'contadorVisitas' => $contadorVisitas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function crearUsuario()
    {
        $this->authorize('crear-usuarios');

        $roles = Role::all();
        $especialidades = Especialidad::where('estado', 'ACTIVA')->get();

        // Retornar JSON para el modal
        return response()->json([
            'roles' => $roles,
            'especialidades' => $especialidades,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function guardarUsuario(StoreUsuarioRequest $datos)
    {
        // 1. Crear Persona
        $personaId = Str::uuid()->toString();
        $persona = Persona::create([
            'id' => $personaId,
            'dni' => $datos->dni,
            'nombre' => $datos->nombre,
            'apellidos' => $datos->apellidos,
            'telefono' => $datos->telefono,
            'direccion' => $datos->direccion,
            'fecha_nacimiento' => $datos->fecha_nacimiento,
        ]);

        // 2. Crear Usuario
        $usuarioId = Str::uuid()->toString();
        $usuario = User::create([
            'id' => $usuarioId,
            'persona_id' => $persona->id,
            'email' => $datos->email,
            'password_hash' => Hash::make($datos->password),
            'tipo_usuario' => $datos->tipo_usuario,
            'estado' => true,
            'fecha_registro' => now(),
        ]);

        // 3. Cargar foto si se envía
        if ($datos->hasFile('foto')) {
            $rutaFoto = $datos->file('foto')->store('usuarios', 'public');
            $usuario->update([
                'foto_url' => $rutaFoto,
                'profile_photo_path' => $rutaFoto,
            ]);
        }

        // 4. Crear perfil especializado según tipo_usuario
        switch ($datos->tipo_usuario) {
            case 'PROPIETARIO':
                Propietario::create([
                    'usuario_id' => $usuario->id,
                    'nivel_acceso' => $datos->nivel_acceso ?? 'TOTAL',
                ]);
                break;

            case 'SECRETARIA':
                Secretaria::create([
                    'usuario_id' => $usuario->id,
                    'turno' => $datos->turno,
                ]);
                break;

            case 'MEDICO':
                $medico = Medico::create([
                    'usuario_id' => $usuario->id,
                    'codigo_cmp' => $datos->codigo_cmp,
                    'horario_atencion' => $datos->horario_atencion,
                ]);

                // Asignar especialidades al médico
                // Nota: medico_id ahora referencia Usuarios(id), no Medicos(usuario_id)
                if ($datos->has('especialidades') && is_array($datos->especialidades)) {
                    foreach ($datos->especialidades as $especialidadId) {
                        MedicoEspecialidad::create([
                            'id' => Str::uuid()->toString(),
                            'medico_id' => $usuario->id, // Referencia a Usuarios(id)
                            'especialidad_id' => $especialidadId,
                        ]);
                    }
                }

                // Registrar horarios del médico
                if ($datos->has('horarios') && is_array($datos->horarios)) {
                    $this->sincronizarHorariosMedico($usuario->id, $datos->horarios);
                }
                break;

            case 'CLIENTE':
                Cliente::create([
                    'usuario_id' => $usuario->id,
                    'antecedentes' => $datos->antecedentes,
                ]);
                break;
        }

        // 5. Asignar roles de Spatie
        if ($datos->has('roles')) {
            $roles = Role::whereIn('id', $datos->roles)->get();
            $usuario->syncRoles($roles);
        }

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function mostrarUsuario(string $id)
    {
        $this->authorize('mostrar-usuarios');

        $usuario = User::with(['persona', 'roles', 'propietario', 'secretaria', 'medico.especialidades', 'medico.horarios', 'cliente'])->findOrFail($id);

        // Retornar JSON para el modal
        return response()->json([
            'usuario' => $usuario,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editarUsuario(string $id)
    {
        $this->authorize('editar-usuarios');

        $usuario = User::with(['persona', 'roles', 'propietario', 'secretaria', 'medico.especialidades', 'medico.horarios', 'cliente'])->findOrFail($id);
        $roles = Role::all();
        $especialidades = Especialidad::where('estado', 'ACTIVA')->get();
        
        // Obtener IDs de especialidades ya asignadas al médico
        $especialidadesAsignadas = [];
        if ($usuario->medico && $usuario->medico->especialidades) {
            $especialidadesAsignadas = $usuario->medico->especialidades->pluck('id')->toArray();
        }

        // Retornar JSON para el modal
        return response()->json([
            'usuario' => $usuario,
            'roles' => $roles,
            'especialidades' => $especialidades,
            'especialidadesAsignadas' => $especialidadesAsignadas,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function actualizarUsuario(UpdateUsuarioRequest $datos, string $id)
    {
        $usuario = User::with(['persona', 'roles', 'propietario', 'secretaria', 'medico.horarios', 'cliente'])->findOrFail($id);

        // 1. Actualizar Persona
        if ($usuario->persona) {
            $usuario->persona->update([
                'dni' => $datos->dni,
                'nombre' => $datos->nombre,
                'apellidos' => $datos->apellidos,
                'telefono' => $datos->telefono,
                'direccion' => $datos->direccion,
                'fecha_nacimiento' => $datos->fecha_nacimiento,
            ]);
        }

        // 2. Actualizar Usuario
        $usuario->update([
            'email' => $datos->email,
            'tipo_usuario' => $datos->tipo_usuario,
        ]);

        // Actualizar contraseña solo si se proporciona
        if ($datos->filled('password')) {
            $usuario->update([
                'password_hash' => Hash::make($datos->password),
            ]);
        }

        // 3. Actualizar o crear perfil especializado
        // Primero eliminar el perfil anterior si cambió el tipo
        $tipoAnterior = $usuario->getOriginal('tipo_usuario') ?? $usuario->tipo_usuario;

        if ($tipoAnterior !== $datos->tipo_usuario) {
            // Eliminar perfil anterior
            $usuario->propietario?->delete();
            $usuario->secretaria?->delete();
            $usuario->medico?->delete();
            $usuario->cliente?->delete();
        }

        // Si se envía una nueva foto
        if ($datos->hasFile('foto')) {
            $rutaFoto = $datos->file('foto')->store('usuarios', 'public');
            $usuario->update([
                'foto_url' => $rutaFoto,
                'profile_photo_path' => $rutaFoto,
            ]);
        }

        // Crear/actualizar nuevo perfil
        switch ($datos->tipo_usuario) {
            case 'PROPIETARIO':
                if ($usuario->propietario) {
                    $usuario->propietario->update([
                        'nivel_acceso' => $datos->nivel_acceso ?? 'TOTAL',
                    ]);
                } else {
                    Propietario::create([
                        'usuario_id' => $usuario->id,
                        'nivel_acceso' => $datos->nivel_acceso ?? 'TOTAL',
                    ]);
                }
                break;

            case 'SECRETARIA':
                if ($usuario->secretaria) {
                    $usuario->secretaria->update([
                        'turno' => $datos->turno,
                    ]);
                } else {
                    Secretaria::create([
                        'usuario_id' => $usuario->id,
                        'turno' => $datos->turno,
                    ]);
                }
                break;

            case 'MEDICO':
                if ($usuario->medico) {
                    $usuario->medico->update([
                        'codigo_cmp' => $datos->codigo_cmp,
                        'horario_atencion' => $datos->horario_atencion,
                    ]);

                    // Sincronizar especialidades
                    // Nota: medico_id ahora referencia Usuarios(id), no Medicos(usuario_id)
                    if ($datos->has('especialidades') && is_array($datos->especialidades)) {
                        // Eliminar relaciones existentes
                        MedicoEspecialidad::where('medico_id', $usuario->id)->delete();
                        
                        // Crear nuevas relaciones
                        foreach ($datos->especialidades as $especialidadId) {
                            MedicoEspecialidad::create([
                                'id' => Str::uuid()->toString(),
                                'medico_id' => $usuario->id, // Referencia a Usuarios(id)
                                'especialidad_id' => $especialidadId,
                            ]);
                        }
                    }

                    // Sincronizar horarios
                    if ($datos->has('horarios') && is_array($datos->horarios)) {
                        $this->sincronizarHorariosMedico($usuario->id, $datos->horarios);
                    } else {
                        HorarioMedico::where('medico_id', $usuario->id)->delete();
                    }
                } else {
                    $medico = Medico::create([
                        'usuario_id' => $usuario->id,
                        'codigo_cmp' => $datos->codigo_cmp,
                        'horario_atencion' => $datos->horario_atencion,
                    ]);

                    // Asignar especialidades al médico
                    // Nota: medico_id ahora referencia Usuarios(id), no Medicos(usuario_id)
                    if ($datos->has('especialidades') && is_array($datos->especialidades)) {
                        foreach ($datos->especialidades as $especialidadId) {
                            MedicoEspecialidad::create([
                                'id' => Str::uuid()->toString(),
                                'medico_id' => $usuario->id, // Referencia a Usuarios(id)
                                'especialidad_id' => $especialidadId,
                            ]);
                        }
                    }

                    // Registrar horarios del nuevo médico
                    if ($datos->has('horarios') && is_array($datos->horarios)) {
                        $this->sincronizarHorariosMedico($usuario->id, $datos->horarios);
                    }
                }
                break;

            case 'CLIENTE':
                if ($usuario->cliente) {
                    $usuario->cliente->update([
                        'antecedentes' => $datos->antecedentes,
                    ]);
                } else {
                    Cliente::create([
                        'usuario_id' => $usuario->id,
                        'antecedentes' => $datos->antecedentes,
                    ]);
                }
                break;
        }

        // 4. Sincronizar roles
        if ($datos->has('roles')) {
            $roles = Role::whereIn('id', $datos->roles)->get();
            $usuario->syncRoles($roles);
        } else {
            $usuario->syncRoles([]);
        }

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function eliminarUsuario(string $id)
    {
        $this->authorize('eliminar-usuarios');

        $usuario = User::findOrFail($id);

        // No permitir eliminar el usuario actual
        if ($usuario->id === auth()->id()) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    /**
     * Sincroniza los horarios del médico con la información proveniente del formulario
     */
    private function sincronizarHorariosMedico(string $medicoId, ?array $horarios): void
    {
        HorarioMedico::where('medico_id', $medicoId)->delete();

        if (!is_array($horarios)) {
            return;
        }

        foreach ($horarios as $horario) {
            if (!$this->horarioEsValido($horario)) {
                continue;
            }

            HorarioMedico::create([
                'id' => Str::uuid()->toString(),
                'medico_id' => $medicoId,
                'dia_semana' => $horario['dia_semana'],
                'hora_inicio' => $horario['hora_inicio'],
                'hora_fin' => $horario['hora_fin'],
                'activo' => true,
            ]);
        }
    }

    /**
     * Valida la estructura básica de un horario
     */
    private function horarioEsValido($horario): bool
    {
        if (!is_array($horario)) {
            return false;
        }

        if (empty($horario['dia_semana']) || empty($horario['hora_inicio']) || empty($horario['hora_fin'])) {
            return false;
        }

        return strtotime($horario['hora_fin']) > strtotime($horario['hora_inicio']);
    }
}
