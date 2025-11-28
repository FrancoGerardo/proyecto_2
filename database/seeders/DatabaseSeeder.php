<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Persona;
use App\Models\Propietario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear permisos para Roles
        Permission::firstOrCreate(['name' => 'gestionar-roles']);
        Permission::firstOrCreate(['name' => 'ver-roles']);
        Permission::firstOrCreate(['name' => 'crear-roles']);
        Permission::firstOrCreate(['name' => 'editar-roles']);
        Permission::firstOrCreate(['name' => 'eliminar-roles']);
        Permission::firstOrCreate(['name' => 'mostrar-roles']);

        // Crear permisos para Permisos
        Permission::firstOrCreate(['name' => 'gestionar-permisos']);
        Permission::firstOrCreate(['name' => 'ver-permisos']);
        Permission::firstOrCreate(['name' => 'crear-permisos']);
        Permission::firstOrCreate(['name' => 'editar-permisos']);
        Permission::firstOrCreate(['name' => 'eliminar-permisos']);
        Permission::firstOrCreate(['name' => 'mostrar-permisos']);

        // Crear permisos para Salas
        Permission::firstOrCreate(['name' => 'gestionar-salas']);
        Permission::firstOrCreate(['name' => 'ver-salas']);
        Permission::firstOrCreate(['name' => 'crear-salas']);
        Permission::firstOrCreate(['name' => 'editar-salas']);
        Permission::firstOrCreate(['name' => 'eliminar-salas']);
        Permission::firstOrCreate(['name' => 'mostrar-salas']);

        // Crear permisos para Usuarios
        Permission::firstOrCreate(['name' => 'gestionar-usuarios']);
        Permission::firstOrCreate(['name' => 'ver-usuarios']);
        Permission::firstOrCreate(['name' => 'crear-usuarios']);
        Permission::firstOrCreate(['name' => 'editar-usuarios']);
        Permission::firstOrCreate(['name' => 'eliminar-usuarios']);
        Permission::firstOrCreate(['name' => 'mostrar-usuarios']);

        // Crear permisos para Especialidades
        Permission::firstOrCreate(['name' => 'gestionar-especialidades']);
        Permission::firstOrCreate(['name' => 'ver-especialidades']);
        Permission::firstOrCreate(['name' => 'crear-especialidades']);
        Permission::firstOrCreate(['name' => 'editar-especialidades']);
        Permission::firstOrCreate(['name' => 'eliminar-especialidades']);
        Permission::firstOrCreate(['name' => 'mostrar-especialidades']);

        // Crear permisos para Servicios
        Permission::firstOrCreate(['name' => 'gestionar-servicios']);
        Permission::firstOrCreate(['name' => 'ver-servicios']);
        Permission::firstOrCreate(['name' => 'crear-servicios']);
        Permission::firstOrCreate(['name' => 'editar-servicios']);
        Permission::firstOrCreate(['name' => 'eliminar-servicios']);
        Permission::firstOrCreate(['name' => 'mostrar-servicios']);

        // Crear permisos para Fichas
        Permission::firstOrCreate(['name' => 'gestionar-fichas']);
        Permission::firstOrCreate(['name' => 'ver-fichas']);
        Permission::firstOrCreate(['name' => 'crear-fichas']);
        Permission::firstOrCreate(['name' => 'editar-fichas']);
        Permission::firstOrCreate(['name' => 'eliminar-fichas']);
        Permission::firstOrCreate(['name' => 'mostrar-fichas']);

        // Crear permisos para Seguimientos
        Permission::firstOrCreate(['name' => 'gestionar-seguimientos']);
        Permission::firstOrCreate(['name' => 'ver-seguimientos']);
        Permission::firstOrCreate(['name' => 'crear-seguimientos']);
        Permission::firstOrCreate(['name' => 'editar-seguimientos']);
        Permission::firstOrCreate(['name' => 'eliminar-seguimientos']);
        Permission::firstOrCreate(['name' => 'mostrar-seguimientos']);

        // Crear permisos para Historiales Clínicos
        Permission::firstOrCreate(['name' => 'gestionar-historiales-clinicos']);
        Permission::firstOrCreate(['name' => 'ver-historiales-clinicos']);
        Permission::firstOrCreate(['name' => 'crear-historiales-clinicos']);
        Permission::firstOrCreate(['name' => 'editar-historiales-clinicos']);
        Permission::firstOrCreate(['name' => 'eliminar-historiales-clinicos']);
        Permission::firstOrCreate(['name' => 'mostrar-historiales-clinicos']);

        // Crear permisos para Reportes
        Permission::firstOrCreate(['name' => 'gestionar-reportes']);
        Permission::firstOrCreate(['name' => 'ver-reportes']);
        Permission::firstOrCreate(['name' => 'crear-reportes']);
        Permission::firstOrCreate(['name' => 'editar-reportes']);
        Permission::firstOrCreate(['name' => 'eliminar-reportes']);
        Permission::firstOrCreate(['name' => 'mostrar-reportes']);

        // Crear rol Administrador
        $rolAdministrador = Role::firstOrCreate(['name' => 'Administrador']);

        // Asignar TODOS los permisos al rol Administrador
        $rolAdministrador->syncPermissions(Permission::all());

        // Crear rol Cliente
        $rolCliente = Role::firstOrCreate(['name' => 'Cliente']);
        
        // Asignar permisos básicos al rol Cliente
        $permisosCliente = Permission::whereIn('name', [
            'ver-fichas',
            'crear-fichas',
            'mostrar-fichas',
            'ver-historiales-clinicos',
            'mostrar-historiales-clinicos',
        ])->get();
        
        $rolCliente->syncPermissions($permisosCliente);

        // Crear Persona para el administrador
        $personaId = Str::uuid()->toString();
        $persona = Persona::firstOrCreate(
            ['dni' => '00000000'],
            [
                'id' => $personaId,
                'nombre' => 'Administrador',
                'apellidos' => 'Sistema',
                'telefono' => null,
                'direccion' => null,
                'fecha_nacimiento' => null,
            ]
        );

        // Crear Usuario administrador
        $usuarioId = Str::uuid()->toString();
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'id' => $usuarioId,
                'persona_id' => $persona->id,
                'password_hash' => Hash::make('123456789'),
                'tipo_usuario' => 'PROPIETARIO',
                'estado' => true,
                'fecha_registro' => now(),
            ]
        );

        // Crear Propietario para el administrador
        Propietario::firstOrCreate(
            ['usuario_id' => $admin->id],
            [
                'nivel_acceso' => 'TOTAL',
            ]
        );

        // Asignar rol Administrador al usuario
        if (!$admin->hasRole('Administrador')) {
            $admin->assignRole('Administrador');
        }
    }
}
