<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlanCuota;
use App\Models\Pago;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PlanPagoController extends Controller
{
    /**
     * Página principal de planes de pago
     */
    public function paginaPrincipalPlanPago(Request $solicitud)
    {
        $usuario = $solicitud->user();
        
        // Obtener planes de cuota relacionados con fichas del usuario
        // Nota: Asumiendo que hay una relación entre fichas y usuarios
        $planesPago = PlanCuota::with(['pagos'])
            ->orderBy('fecha_inicio', 'desc')
            ->paginate(10);

        return Inertia::render('PlanesPago/Index', [
            'planesPago' => $planesPago,
        ]);
    }

    /**
     * Crear nuevo plan de pago
     */
    public function crearPlanPago(Request $datos)
    {
        $datos->validate([
            'ficha_id' => 'required|string|exists:fichas,id',
            'numero_cuotas' => 'required|integer|min:1|max:60',
            'monto_total' => 'required|numeric|min:0.01',
            'fecha_inicio' => 'required|date',
            'intervalo_dias' => 'nullable|integer|min:1|max:365',
        ], [
            'ficha_id.required' => 'La ficha es obligatoria.',
            'ficha_id.exists' => 'La ficha seleccionada no existe.',
            'numero_cuotas.required' => 'El número de cuotas es obligatorio.',
            'numero_cuotas.integer' => 'El número de cuotas debe ser un número entero.',
            'numero_cuotas.min' => 'El número de cuotas debe ser al menos 1.',
            'numero_cuotas.max' => 'El número de cuotas no puede exceder 60.',
            'monto_total.required' => 'El monto total es obligatorio.',
            'monto_total.numeric' => 'El monto total debe ser un número válido.',
            'monto_total.min' => 'El monto total debe ser mayor a 0.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'intervalo_dias.integer' => 'El intervalo de días debe ser un número entero.',
            'intervalo_dias.min' => 'El intervalo de días debe ser al menos 1.',
            'intervalo_dias.max' => 'El intervalo de días no puede exceder 365.',
        ]);

        $montoCuota = $datos->monto_total / $datos->numero_cuotas;

        $planPago = PlanCuota::create([
            'id' => Str::uuid()->toString(),
            'ficha_id' => $datos->ficha_id,
            'numero_cuotas' => $datos->numero_cuotas,
            'monto_total' => $datos->monto_total,
            'monto_cuota' => round($montoCuota, 2),
            'fecha_inicio' => $datos->fecha_inicio,
            'intervalo_dias' => $datos->intervalo_dias ?? 30,
            'estado' => 'ACTIVO',
        ]);

        return redirect()->route('planes-pago.index')
            ->with('success', 'Plan de pago creado exitosamente.');
    }

    /**
     * Mostrar detalles de un plan de pago
     */
    public function mostrarPlanPago(string $id)
    {
        $planPago = PlanCuota::with(['pagos'])
            ->findOrFail($id);

        return response()->json(['planPago' => $planPago]);
    }

    /**
     * Actualizar estado de plan de pago
     */
    public function actualizarEstadoPlanPago(Request $datos, string $id)
    {
        $datos->validate([
            'estado' => 'required|in:ACTIVO,PAGADO,MOROSO,CANCELADO',
        ], [
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ]);

        $planPago = PlanCuota::findOrFail($id);
        $planPago->update(['estado' => $datos->estado]);

        return redirect()->route('planes-pago.index')
            ->with('success', 'Estado del plan de pago actualizado exitosamente.');
    }
}
