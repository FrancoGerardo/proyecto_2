<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ficha;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\Medico;
use App\Models\Sala;
use App\Models\HorarioMedico;
use Inertia\Inertia;
use App\Http\Requests\StoreFichaRequest;
use App\Http\Requests\UpdateFichaRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FichaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function paginaPrincipalFicha()
    {
        $this->authorize('gestionar-fichas');

        $fichas = Ficha::with(['cliente.usuario.persona', 'servicio', 'medico.usuario.persona', 'sala'])
            ->paginate(10);
        $contadorVisitas = DB::table('visitas_paginas')
            ->where('ruta', 'fichas')
            ->count();

        return Inertia::render('Fichas/Index', [
            'fichas' => $fichas,
            'contadorVisitas' => $contadorVisitas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function crearFicha()
    {
        $this->authorize('crear-fichas');

        return response()->json($this->obtenerDatosFormularioFicha());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function guardarFicha(StoreFichaRequest $datos)
    {
        $fichaId = Str::uuid()->toString();
        Ficha::create([
            'id' => $fichaId,
            'cliente_id' => $datos->cliente_id,
            'servicio_id' => $datos->servicio_id,
            'medico_id' => $datos->medico_id,
            'sala_id' => $datos->sala_id,
            'fecha' => $datos->fecha,
            'hora' => $datos->hora,
            'estado' => $datos->estado ?? 'PENDIENTE',
            'motivo_consulta' => $datos->motivo_consulta,
        ]);

        return redirect()->route('fichas.index')
            ->with('success', 'Ficha creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function mostrarFicha(string $id)
    {
        $this->authorize('mostrar-fichas');

        $ficha = Ficha::with(['cliente.usuario.persona', 'servicio', 'medico.usuario.persona', 'sala'])
            ->findOrFail($id);

        return response()->json([
            'ficha' => $ficha,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editarFicha(string $id)
    {
        $this->authorize('editar-fichas');

        $ficha = Ficha::with([
                'cliente.usuario.persona',
                'servicio.especialidad',
                'servicio.medicos.usuario.persona',
                'medico.usuario.persona',
                'sala',
            ])
            ->findOrFail($id);

        return response()->json(array_merge(
            ['ficha' => $ficha],
            $this->obtenerDatosFormularioFicha()
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function actualizarFicha(UpdateFichaRequest $datos, string $id)
    {
        $ficha = Ficha::findOrFail($id);

        $ficha->update([
            'cliente_id' => $datos->cliente_id,
            'servicio_id' => $datos->servicio_id,
            'medico_id' => $datos->medico_id,
            'sala_id' => $datos->sala_id,
            'fecha' => $datos->fecha,
            'hora' => $datos->hora,
            'estado' => $datos->estado,
            'motivo_consulta' => $datos->motivo_consulta,
        ]);

        return redirect()->route('fichas.index')
            ->with('success', 'Ficha actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function eliminarFicha(string $id)
    {
        $this->authorize('eliminar-fichas');

        $ficha = Ficha::findOrFail($id);

        $ficha->delete();

        return redirect()->route('fichas.index')
            ->with('success', 'Ficha eliminada exitosamente.');
    }

    /**
     * Devuelve los horarios disponibles para un médico/servicio en una fecha concreta.
     */
    public function horariosDisponibles(Request $request)
    {
        $this->authorize('crear-fichas');

        $datos = $request->validate([
            'medico_id' => 'required|string|exists:medicos,usuario_id',
            'servicio_id' => 'required|string|exists:servicios,id',
            'fecha' => 'required|date',
        ]);

        $servicio = Servicio::findOrFail($datos['servicio_id']);
        $duracion = $servicio->duracion_minutos ?? 30;

        $fecha = Carbon::parse($datos['fecha']);
        $diaSemana = $this->obtenerDiaSemana($fecha);

        $horariosConfigurados = HorarioMedico::where('medico_id', $datos['medico_id'])
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->get();

        if ($horariosConfigurados->isEmpty()) {
            return response()->json(['horas' => []]);
        }

        $horasOcupadas = Ficha::where('medico_id', $datos['medico_id'])
            ->where('fecha', $fecha->toDateString())
            ->pluck('hora')
            ->map(fn ($hora) => substr($hora, 0, 5))
            ->toArray();

        $slots = [];

        foreach ($horariosConfigurados as $configuracion) {
            $inicio = Carbon::createFromFormat('H:i:s', $configuracion->hora_inicio);
            $fin = Carbon::createFromFormat('H:i:s', $configuracion->hora_fin);

            $cursor = $inicio->copy();
            while ($cursor->copy()->addMinutes($duracion)->lte($fin)) {
                $horaTexto = $cursor->format('H:i');
                if (!in_array($horaTexto, $horasOcupadas)) {
                    $slots[] = $horaTexto;
                }
                $cursor->addMinutes($duracion);
            }
        }

        $slots = array_values(array_unique($slots));

        return response()->json([
            'horas' => $slots,
        ]);
    }

    /**
     * Obtiene la data común que necesitan los formularios de fichas.
     */
    private function obtenerDatosFormularioFicha(): array
    {
        $clientes = Cliente::with('usuario.persona')
            ->orderBy('usuario_id')
            ->get();

        $servicios = Servicio::with([
                'especialidad',
                'medicos.usuario.persona',
            ])
            ->where('estado', true)
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get();

        $medicos = Medico::with(['usuario.persona', 'especialidades'])
            ->get();

        $salas = Sala::where('estado', 'DISPONIBLE')
            ->orderBy('numero')
            ->get();

        return [
            'clientes' => $clientes,
            'servicios' => $servicios,
            'medicos' => $medicos,
            'salas' => $salas,
        ];
    }

    /**
     * Convierte una fecha en el formato textual usado en horarios_medicos.
     */
    private function obtenerDiaSemana(Carbon $fecha): string
    {
        $mapa = [
            1 => 'LUNES',
            2 => 'MARTES',
            3 => 'MIERCOLES',
            4 => 'JUEVES',
            5 => 'VIERNES',
            6 => 'SABADO',
            7 => 'DOMINGO',
        ];

        return $mapa[$fecha->dayOfWeekIso] ?? 'LUNES';
    }
}

