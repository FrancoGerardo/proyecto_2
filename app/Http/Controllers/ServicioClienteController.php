<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Servicio;
use App\Models\Medico;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ServicioClienteController extends Controller
{
    /**
     * Mostrar lista de servicios para cliente (en cards)
     */
    public function listarServicios()
    {
        $servicios = Servicio::where('estado', true)
            ->with(['especialidad', 'medicos.usuario.persona'])
            ->get();

        return Inertia::render('ServiciosCliente/Index', [
            'servicios' => $servicios,
        ]);
    }

    /**
     * Mostrar detalle de un servicio con médicos disponibles
     */
    public function mostrarServicio(string $id)
    {
        $servicio = Servicio::with(['especialidad', 'medicos.usuario.persona', 'medicos.especialidades'])
            ->findOrFail($id);

        // Obtener médicos que ofrecen este servicio
        $medicos = $servicio->medicos()->with(['usuario.persona', 'especialidades'])->get();

        return Inertia::render('ServiciosCliente/Detalle', [
            'servicio' => $servicio,
            'medicos' => $medicos,
        ]);
    }
}

