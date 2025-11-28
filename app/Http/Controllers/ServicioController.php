<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Servicio;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\MedicoServicio;
use Inertia\Inertia;
use App\Http\Requests\StoreServicioRequest;
use App\Http\Requests\UpdateServicioRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ServicioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function paginaPrincipalServicio()
    {
        $this->authorize('gestionar-servicios');

        $servicios = Servicio::paginate(10);
        $contadorVisitas = DB::table('visitas_paginas')
            ->where('ruta', 'servicios')
            ->count();

        return Inertia::render('Servicios/Index', [
            'servicios' => $servicios,
            'contadorVisitas' => $contadorVisitas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function crearServicio()
    {
        $this->authorize('crear-servicios');

        $especialidades = Especialidad::where('estado', 'ACTIVA')
            ->whereHas('medicos')
            ->orderBy('nombre')
            ->get();
        $medicos = Medico::with(['usuario.persona', 'especialidades' => function ($query) {
            $query->select('especialidades.id', 'especialidades.nombre');
        }])->get();

        return response()->json([
            'especialidades' => $especialidades,
            'medicos' => $medicos,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function guardarServicio(StoreServicioRequest $datos)
    {
        $servicioId = Str::uuid()->toString();
        $servicio = Servicio::create([
            'id' => $servicioId,
            'nombre' => $datos->nombre,
            'descripcion' => $datos->descripcion,
            'categoria' => $datos->categoria,
            'especialidad_id' => $datos->especialidad_id,
            'meedico_id' => $datos->meedico_id ?? null,
            'costo' => $datos->costo,
            'duracion_minutos' => $datos->duracion_minutos,
            'estado' => $datos->estado ?? true,
        ]);

        // Si es categoría ESPECIALIDAD y tiene médico, crear relación
        if ($datos->categoria === 'ESPECIALIDAD' && $datos->medico_id) {
            MedicoServicio::create([
                'id' => Str::uuid()->toString(),
                'medico_id' => $datos->medico_id,
                'servicio_id' => $servicio->id,
                'activo' => true,
            ]);
        }

        return redirect()->route('servicios.index')
            ->with('success', 'Servicio creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function mostrarServicio(string $id)
    {
        $this->authorize('mostrar-servicios');

        $servicio = Servicio::findOrFail($id);

        return response()->json([
            'servicio' => $servicio,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editarServicio(string $id)
    {
        $this->authorize('editar-servicios');

        $servicio = Servicio::with('especialidad', 'medicos')->findOrFail($id);
        $especialidades = Especialidad::where('estado', 'ACTIVA')
            ->whereHas('medicos')
            ->orderBy('nombre')
            ->get();
        $medicos = Medico::with(['usuario.persona', 'especialidades' => function ($query) {
            $query->select('especialidades.id', 'especialidades.nombre');
        }])->get();

        return response()->json([
            'servicio' => $servicio,
            'especialidades' => $especialidades,
            'medicos' => $medicos,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function actualizarServicio(UpdateServicioRequest $datos, string $id)
    {
        $servicio = Servicio::findOrFail($id);

        $servicio->update([
            'nombre' => $datos->nombre,
            'descripcion' => $datos->descripcion,
            'categoria' => $datos->categoria,
            'especialidad_id' => $datos->especialidad_id,
            'meedico_id' => $datos->meedico_id ?? null,
            'costo' => $datos->costo,
            'duracion_minutos' => $datos->duracion_minutos,
            'estado' => $datos->estado,
        ]);

        return redirect()->route('servicios.index')
            ->with('success', 'Servicio actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function eliminarServicio(string $id)
    {
        $this->authorize('eliminar-servicios');

        $servicio = Servicio::findOrFail($id);

        $servicio->delete();

        return redirect()->route('servicios.index')
            ->with('success', 'Servicio eliminado exitosamente.');
    }
}

