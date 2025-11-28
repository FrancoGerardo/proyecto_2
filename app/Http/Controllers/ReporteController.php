<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reporte;
use App\Models\Usuario;
use Inertia\Inertia;
use App\Http\Requests\StoreReporteRequest;
use App\Http\Requests\UpdateReporteRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function paginaPrincipalReporte()
    {
        $this->authorize('gestionar-reportes');

        $reportes = Reporte::with(['usuarioGenerador' => function($query) {
            $query->with('persona');
        }])
            ->paginate(10);
        $contadorVisitas = DB::table('visitas_paginas')
            ->where('ruta', 'reportes')
            ->count();

        return Inertia::render('Reportes/Index', [
            'reportes' => $reportes,
            'contadorVisitas' => $contadorVisitas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function crearReporte()
    {
        $this->authorize('crear-reportes');

        return response()->json([
            'exito' => true,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function guardarReporte(StoreReporteRequest $datos)
    {
        $reporteId = Str::uuid()->toString();
        Reporte::create([
            'id' => $reporteId,
            'tipo' => $datos->tipo,
            'nombre' => $datos->nombre,
            'parametros' => $datos->parametros,
            'url_archivo' => $datos->url_archivo,
            'estado' => $datos->estado ?? 'GENERANDO',
            'usuario_generador' => $datos->user()->id,
        ]);

        return redirect()->route('reportes.index')
            ->with('success', 'Reporte creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function mostrarReporte(string $id)
    {
        $this->authorize('mostrar-reportes');

        $reporte = Reporte::with(['usuarioGenerador' => function($query) {
            $query->with('persona');
        }])
            ->findOrFail($id);

        return response()->json([
            'reporte' => $reporte,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editarReporte(string $id)
    {
        $this->authorize('editar-reportes');

        $reporte = Reporte::with(['usuarioGenerador' => function($query) {
            $query->with('persona');
        }])
            ->findOrFail($id);

        return response()->json([
            'reporte' => $reporte,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function actualizarReporte(UpdateReporteRequest $datos, string $id)
    {
        $reporte = Reporte::findOrFail($id);

        $reporte->update([
            'tipo' => $datos->tipo,
            'nombre' => $datos->nombre,
            'parametros' => $datos->parametros,
            'url_archivo' => $datos->url_archivo,
            'estado' => $datos->estado,
        ]);

        return redirect()->route('reportes.index')
            ->with('success', 'Reporte actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function eliminarReporte(string $id)
    {
        $this->authorize('eliminar-reportes');

        $reporte = Reporte::findOrFail($id);

        $reporte->delete();

        return redirect()->route('reportes.index')
            ->with('success', 'Reporte eliminado exitosamente.');
    }
}

