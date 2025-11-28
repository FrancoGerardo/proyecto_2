<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;
use App\Http\Requests\StorePermisoRequest;
use App\Http\Requests\UpdatePermisoRequest;

class PermisoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function paginaPrincipalPermiso()
    {
        $this->authorize('gestionar-permisos');

        $permisos = Permission::paginate(10);
        $contadorVisitas = \Illuminate\Support\Facades\DB::table('visitas_paginas')
            ->where('ruta', 'permisos')
            ->count();

        return Inertia::render('Permisos/Index', [
            'permisos' => $permisos,
            'contadorVisitas' => $contadorVisitas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function crearPermiso()
    {
        $this->authorize('crear-permisos');

        return response()->json([
            'exito'=> true,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function guardarPermiso(StorePermisoRequest $datos)
    {

        Permission::create([
            'name' => $datos->name,
            'guard_name' => 'web',
        ]);
        return redirect()->route('permisos.index')
            ->with('success', 'Permiso creado exitosamente.');

    }

    /**
     * Display the specified resource.
     */
    public function mostrarPermiso(string $id)
    {
        $this->authorize('mostrar-permisos');

        $permiso = Permission::findOrFail($id);
        return response()->json([
            'permiso' => $permiso,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editarPermiso(string $id)
    {
        $this->authorize('editar-permisos');

        $permiso = Permission::findOrFail($id);
        return response()->json([
            'permiso' => $permiso,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function actualizarPermiso(UpdatePermisoRequest $datos, string $id)
    {
        $permiso = Permission::findOrFail($id);
        $permiso->update([
            'name' => $datos->name,
        ]);

        return redirect()->route('permisos.index')
            ->with('success', 'Permiso actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function eliminarPermiso(string $id)
    {
        $this->authorize('eliminar-permisos');
        $permiso = Permission::findOrFail($id);
         // Verificar si el permiso está siendo usado por algún rol
        if ($permiso->roles()->count() > 0) {
            return redirect()->route('permisos.index')
                ->with('error', 'No se puede eliminar este permiso porque está asignado a uno o más roles.');
        }
        $permiso->delete();

        return redirect()->route('permisos.index')
            ->with('success', 'Permiso eliminado exitosamente.');
    }
}
