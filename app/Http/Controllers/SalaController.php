<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sala;
use Inertia\Inertia;
use App\Http\Requests\StoreSalaRequest;
use App\Http\Requests\UpdateSalaRequest;
use Illuminate\Support\Str;

class SalaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function paginaPrincipalSala()
    {
        $this->authorize('gestionar-salas');

        $salas = Sala::paginate(10);
        $contadorVisitas = \Illuminate\Support\Facades\DB::table('visitas_paginas')
            ->where('ruta', 'salas')
            ->count();

        return Inertia::render('Salas/Index', [
            'salas' => $salas,
            'contadorVisitas' => $contadorVisitas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function crearSala()
    {
        $this->authorize('crear-salas');

        // Retornar JSON para el modal
        return response()->json([
            'exito' => true,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function guardarSala(StoreSalaRequest $datos)
    {
        $salaId = Str::uuid()->toString();
        Sala::create([
            'id' => $salaId,
            'numero' => $datos->numero,
            'categoria' => $datos->categoria,
            'equipamiento' => $datos->equipamiento,
            'estado' => $datos->estado,
            'capacidad' => $datos->capacidad,
        ]);

        return redirect()->route('salas.index')
            ->with('success', 'Sala creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function mostrarSala(string $id)
    {
        $this->authorize('mostrar-salas');

        $sala = Sala::findOrFail($id);

        // Retornar JSON para el modal
        return response()->json([
            'sala' => $sala,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editarSala(string $id)
    {
        $this->authorize('editar-salas');

        $sala = Sala::findOrFail($id);

        // Retornar JSON para el modal
        return response()->json([
            'sala' => $sala,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function actualizarSala(UpdateSalaRequest $datos, string $id)
    {
        $sala = Sala::findOrFail($id);

        $sala->update([
            'numero' => $datos->numero,
            'categoria' => $datos->categoria,
            'equipamiento' => $datos->equipamiento,
            'estado' => $datos->estado,
            'capacidad' => $datos->capacidad,
        ]);

        return redirect()->route('salas.index')
            ->with('success', 'Sala actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function eliminarSala(string $id)
    {
        $this->authorize('eliminar-salas');

        $sala = Sala::findOrFail($id);

        $sala->delete();

        return redirect()->route('salas.index')
            ->with('success', 'Sala eliminada exitosamente.');
    }
}
