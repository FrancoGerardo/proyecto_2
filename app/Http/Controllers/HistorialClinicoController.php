<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HistorialClinico;
use App\Models\Cliente;
use Inertia\Inertia;
use App\Http\Requests\StoreHistorialClinicoRequest;
use App\Http\Requests\UpdateHistorialClinicoRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class HistorialClinicoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function paginaPrincipalHistorialClinico()
    {
        $this->authorize('gestionar-historiales-clinicos');

        $historiales = HistorialClinico::with('cliente.usuario.persona')
            ->paginate(10);
        $contadorVisitas = DB::table('visitas_paginas')
            ->where('ruta', 'historiales-clinicos')
            ->count();

        return Inertia::render('HistorialesClinicos/Index', [
            'historiales' => $historiales,
            'contadorVisitas' => $contadorVisitas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function crearHistorialClinico()
    {
        $this->authorize('crear-historiales-clinicos');

        $clientes = Cliente::with('usuario.persona')
            ->whereDoesntHave('historialClinico')
            ->get();

        return response()->json([
            'clientes' => $clientes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function guardarHistorialClinico(StoreHistorialClinicoRequest $datos)
    {
        $historialId = Str::uuid()->toString();
        HistorialClinico::create([
            'id' => $historialId,
            'cliente_id' => $datos->cliente_id,
            'alergias' => $datos->alergias,
            'enfermedades_cronicas' => $datos->enfermedades_cronicas,
            'medicamentos_habituales' => $datos->medicamentos_habituales,
        ]);

        return redirect()->route('historiales-clinicos.index')
            ->with('success', 'Historial clínico creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function mostrarHistorialClinico(string $id)
    {
        $this->authorize('mostrar-historiales-clinicos');

        $historial = HistorialClinico::with('cliente.usuario.persona')
            ->findOrFail($id);

        return response()->json([
            'historial' => $historial,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editarHistorialClinico(string $id)
    {
        $this->authorize('editar-historiales-clinicos');

        $historial = HistorialClinico::with('cliente.usuario.persona')
            ->findOrFail($id);

        return response()->json([
            'historial' => $historial,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function actualizarHistorialClinico(UpdateHistorialClinicoRequest $datos, string $id)
    {
        $historial = HistorialClinico::findOrFail($id);

        $historial->update([
            'alergias' => $datos->alergias,
            'enfermedades_cronicas' => $datos->enfermedades_cronicas,
            'medicamentos_habituales' => $datos->medicamentos_habituales,
        ]);

        return redirect()->route('historiales-clinicos.index')
            ->with('success', 'Historial clínico actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function eliminarHistorialClinico(string $id)
    {
        $this->authorize('eliminar-historiales-clinicos');

        $historial = HistorialClinico::findOrFail($id);

        $historial->delete();

        return redirect()->route('historiales-clinicos.index')
            ->with('success', 'Historial clínico eliminado exitosamente.');
    }
}

