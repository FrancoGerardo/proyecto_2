<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\StoreRolRequest;
use App\Http\Requests\UpdateRolRequest;

class RolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function paginaPrincipalRol()
    {
        $this->authorize('gestionar-roles');

        $roles = Role::with('permissions')->paginate(10);
        $permisos = Permission::all();
        $contadorVisitas = \Illuminate\Support\Facades\DB::table('visitas_paginas')
            ->where('ruta', 'roles')
            ->count();

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'permisos' => $permisos,
            'contadorVisitas' => $contadorVisitas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    //AQUI MANDAMOS LOS PERMISOS A LA VISTA
    // PARA ASIGNARLOS AL ROL
    public function crearRol()
    {
        $this->authorize('crear-roles');
        $permisos = Permission::all();
        // Retornar JSON para el modal
        return response()->json([
            'permisos' => $permisos,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function guardarRol(StoreRolRequest $datos)
    {

        $nuevoRol = Role::create([
            'name' => $datos->name,
            'guard_name' => 'web',
        ]);
         if ($datos->has('permisos')) {
            $permisos = Permission::whereIn('id', $datos->permisos)->get();
            $nuevoRol->syncPermissions($permisos);
        }
        return redirect()->route('roles.index')
            ->with('success', 'Rol creado exitosamente.');

    }

    /**
     * Display the specified resource.
     */
    public function mostrarRol(string $id)
    {
        $this->authorize('mostrar-roles');
        $rol = Role::with('permissions')->findOrFail($id);
        // Retornar JSON para el modal
        return response()->json([
            'rol' => $rol,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editarRol(string $id)
    {
        $this->authorize('editar-roles');
        $rol = Role::with('permissions')->findOrFail($id);
        $permisos = Permission::all();
        // Retornar JSON para el modal
        return response()->json([
            'rol' => $rol,
            'permisos' => $permisos,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function actualizarRol(UpdateRolRequest $datos, string $id)
    {
        $rol = Role::findOrFail($id);

        $rol->update([
            'name' => $datos->name,
        ]);

        if ($datos->has('permisos')) {
            $permisos = Permission::whereIn('id', $datos->permisos)->get();
            $rol->syncPermissions($permisos);
        } else {
            $rol->syncPermissions([]);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Rol actualizado exitosamente.');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function eliminarRol(string $id)
    {
        $this->authorize('eliminar-roles');
        $rol = Role::findOrFail($id);

         // No permitir eliminar roles del sistema si es necesario
        if (in_array($rol->name, ['Administrador'])) {
            return redirect()->route('roles.index')
                ->with('error', 'No se puede eliminar este rol del sistema.');
        }

        $rol->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Rol eliminado exitosamente.');
    }
}
