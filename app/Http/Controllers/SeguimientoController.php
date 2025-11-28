<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seguimiento;
use App\Models\Ficha;
use Inertia\Inertia;
use App\Http\Requests\StoreSeguimientoRequest;
use App\Http\Requests\UpdateSeguimientoRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SeguimientoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function paginaPrincipalSeguimiento()
    {
        $this->authorize('gestionar-seguimientos');

        $seguimientos = Seguimiento::with('ficha.cliente.usuario.persona')
            ->paginate(10);
        $contadorVisitas = DB::table('visitas_paginas')
            ->where('ruta', 'seguimientos')
            ->count();

        return Inertia::render('Seguimientos/Index', [
            'seguimientos' => $seguimientos,
            'contadorVisitas' => $contadorVisitas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function crearSeguimiento()
    {
        $this->authorize('crear-seguimientos');

        $fichas = Ficha::with('cliente.usuario.persona')->get();

        return response()->json([
            'fichas' => $fichas,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function guardarSeguimiento(StoreSeguimientoRequest $datos)
    {
        $seguimientoId = Str::uuid()->toString();
        Seguimiento::create([
            'id' => $seguimientoId,
            'ficha_id' => $datos->ficha_id,
            'tipo' => $datos->tipo,
            'fecha' => $datos->fecha ?? now(),
            'signos_vitales' => $datos->signos_vitales,
            'motivo_consulta' => $datos->motivo_consulta,
            'nivel_urgencia' => $datos->nivel_urgencia,
            'diagnostico' => $datos->diagnostico,
            'observaciones' => $datos->observaciones,
            'tratamiento_prescrito' => $datos->tratamiento_prescrito,
            'medicamentos' => $datos->medicamentos,
        ]);

        return redirect()->route('seguimientos.index')
            ->with('success', 'Seguimiento creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function mostrarSeguimiento(string $id)
    {
        $this->authorize('mostrar-seguimientos');

        $seguimiento = Seguimiento::with('ficha.cliente.usuario.persona')
            ->findOrFail($id);

        return response()->json([
            'seguimiento' => $seguimiento,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editarSeguimiento(string $id)
    {
        $this->authorize('editar-seguimientos');

        $seguimiento = Seguimiento::with('ficha.cliente.usuario.persona')
            ->findOrFail($id);
        $fichas = Ficha::with('cliente.usuario.persona')->get();

        return response()->json([
            'seguimiento' => $seguimiento,
            'fichas' => $fichas,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function actualizarSeguimiento(UpdateSeguimientoRequest $datos, string $id)
    {
        $seguimiento = Seguimiento::findOrFail($id);

        $seguimiento->update([
            'ficha_id' => $datos->ficha_id,
            'tipo' => $datos->tipo,
            'fecha' => $datos->fecha,
            'signos_vitales' => $datos->signos_vitales,
            'motivo_consulta' => $datos->motivo_consulta,
            'nivel_urgencia' => $datos->nivel_urgencia,
            'diagnostico' => $datos->diagnostico,
            'observaciones' => $datos->observaciones,
            'tratamiento_prescrito' => $datos->tratamiento_prescrito,
            'medicamentos' => $datos->medicamentos,
        ]);

        return redirect()->route('seguimientos.index')
            ->with('success', 'Seguimiento actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function eliminarSeguimiento(string $id)
    {
        $this->authorize('eliminar-seguimientos');

        $seguimiento = Seguimiento::findOrFail($id);

        $seguimiento->delete();

        return redirect()->route('seguimientos.index')
            ->with('success', 'Seguimiento eliminado exitosamente.');
    }
}

