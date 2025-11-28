<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Especialidad;
use Inertia\Inertia;
use App\Http\Requests\StoreEspecialidadRequest;
use App\Http\Requests\UpdateEspecialidadRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class EspecialidadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function paginaPrincipalEspecialidad()
    {
        $this->authorize('gestionar-especialidades');

        $especialidades = Especialidad::paginate(10);
        $contadorVisitas = DB::table('visitas_paginas')
            ->where('ruta', 'especialidades')
            ->count();

        return Inertia::render('Especialidades/Index', [
            'especialidades' => $especialidades,
            'contadorVisitas' => $contadorVisitas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function crearEspecialidad()
    {
        $this->authorize('crear-especialidades');

        return response()->json([
            'exito' => true,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function guardarEspecialidad(StoreEspecialidadRequest $datos)
    {
        $especialidadId = Str::uuid()->toString();
        Especialidad::create([
            'id' => $especialidadId,
            'nombre' => $datos->nombre,
            'descripcion' => $datos->descripcion,
            'estado' => $datos->estado ?? 'ACTIVA',
        ]);

        return redirect()->route('especialidades.index')
            ->with('success', 'Especialidad creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function mostrarEspecialidad(string $id)
    {
        $this->authorize('mostrar-especialidades');

        $especialidad = Especialidad::with(['medicos.usuario.persona', 'servicios'])->findOrFail($id);

        return response()->json([
            'especialidad' => $especialidad,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editarEspecialidad(string $id)
    {
        $this->authorize('editar-especialidades');

        $especialidad = Especialidad::findOrFail($id);

        return response()->json([
            'especialidad' => $especialidad,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function actualizarEspecialidad(UpdateEspecialidadRequest $datos, string $id)
    {
        $especialidad = Especialidad::findOrFail($id);

        $especialidad->update([
            'nombre' => $datos->nombre,
            'descripcion' => $datos->descripcion,
            'estado' => $datos->estado,
        ]);

        return redirect()->route('especialidades.index')
            ->with('success', 'Especialidad actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function eliminarEspecialidad(string $id)
    {
        $this->authorize('eliminar-especialidades');

        $especialidad = Especialidad::findOrFail($id);
        $especialidad->delete();

        return redirect()->route('especialidades.index')
            ->with('success', 'Especialidad eliminada exitosamente.');
    }
}
