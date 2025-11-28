<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pago;
use App\Models\MetodoPago;
use App\Models\PlanCuota;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PagoController extends Controller
{
    /**
     * Página principal de pagos
     */
    public function paginaPrincipalPago(Request $solicitud)
    {
        $usuario = $solicitud->user();
        
        $pagos = Pago::obtenerPorUsuario($usuario->id)
            ->paginate(10);

        $metodosPago = MetodoPago::obtenerActivosPorUsuario($usuario->id);

        return Inertia::render('Pagos/Index', [
            'pagos' => $pagos,
            'metodosPago' => $metodosPago,
        ]);
    }

    /**
     * Guardar nuevo pago único
     */
    public function guardarPagoUnico(Request $datos)
    {
        $usuario = $datos->user();

        $datos->validate([
            'ficha_id' => 'required|string|exists:fichas,id',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago_id' => 'nullable|exists:metodos_pago,id',
            'metodo_pago' => 'required|in:EFECTIVO,TARJETA,TRANSFERENCIA',
            'comprobante_url' => 'nullable|string|max:255',
        ], [
            'ficha_id.required' => 'La ficha es obligatoria.',
            'ficha_id.exists' => 'La ficha seleccionada no existe.',
            'monto.required' => 'El monto es obligatorio.',
            'monto.numeric' => 'El monto debe ser un número válido.',
            'monto.min' => 'El monto debe ser mayor a 0.',
            'metodo_pago_id.exists' => 'El método de pago seleccionado no existe.',
            'metodo_pago.required' => 'El método de pago es obligatorio.',
            'metodo_pago.in' => 'El método de pago seleccionado no es válido.',
        ]);

        // Verificar que el método de pago pertenezca al usuario si se proporciona
        if ($datos->metodo_pago_id) {
            $metodoPago = MetodoPago::where('usuario_id', $usuario->id)
                ->findOrFail($datos->metodo_pago_id);
        }

        $pago = Pago::create([
            'id' => Str::uuid()->toString(),
            'ficha_id' => $datos->ficha_id,
            'metodo_pago_id' => $datos->metodo_pago_id,
            'monto' => $datos->monto,
            'tipo' => 'CONTADO',
            'fecha_pago' => now(),
            'metodo_pago' => $datos->metodo_pago,
            'comprobante_url' => $datos->comprobante_url,
            'estado' => 'PAGADO',
        ]);

        return redirect()->route('pagos.index')
            ->with('success', 'Pago registrado exitosamente.');
    }

    /**
     * Guardar pago de cuota
     */
    public function guardarPagoCuota(Request $datos)
    {
        $usuario = $datos->user();

        $datos->validate([
            'plan_cuota_id' => 'required|string|exists:planes_cuota,id',
            'numero_cuota' => 'required|integer|min:1',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago_id' => 'nullable|exists:metodos_pago,id',
            'metodo_pago' => 'required|in:EFECTIVO,TARJETA,TRANSFERENCIA',
            'comprobante_url' => 'nullable|string|max:255',
        ], [
            'plan_cuota_id.required' => 'El plan de cuota es obligatorio.',
            'plan_cuota_id.exists' => 'El plan de cuota seleccionado no existe.',
            'numero_cuota.required' => 'El número de cuota es obligatorio.',
            'numero_cuota.integer' => 'El número de cuota debe ser un número entero.',
            'numero_cuota.min' => 'El número de cuota debe ser mayor a 0.',
            'monto.required' => 'El monto es obligatorio.',
            'monto.numeric' => 'El monto debe ser un número válido.',
            'monto.min' => 'El monto debe ser mayor a 0.',
            'metodo_pago_id.exists' => 'El método de pago seleccionado no existe.',
            'metodo_pago.required' => 'El método de pago es obligatorio.',
            'metodo_pago.in' => 'El método de pago seleccionado no es válido.',
        ]);

        $planCuota = PlanCuota::findOrFail($datos->plan_cuota_id);

        // Verificar que el método de pago pertenezca al usuario si se proporciona
        if ($datos->metodo_pago_id) {
            $metodoPago = MetodoPago::where('usuario_id', $usuario->id)
                ->findOrFail($datos->metodo_pago_id);
        }

        $pago = Pago::create([
            'id' => Str::uuid()->toString(),
            'plan_cuota_id' => $datos->plan_cuota_id,
            'ficha_id' => $planCuota->ficha_id,
            'metodo_pago_id' => $datos->metodo_pago_id,
            'monto' => $datos->monto,
            'tipo' => 'CUOTA',
            'numero_cuota' => $datos->numero_cuota,
            'fecha_pago' => now(),
            'metodo_pago' => $datos->metodo_pago,
            'comprobante_url' => $datos->comprobante_url,
            'estado' => 'PAGADO',
        ]);

        return redirect()->route('pagos.index')
            ->with('success', 'Pago de cuota registrado exitosamente.');
    }

    /**
     * Mostrar detalles de un pago
     */
    public function mostrarPago(string $id)
    {
        $usuario = auth()->user();
        $pago = Pago::with(['metodoPago', 'planCuota'])
            ->whereHas('metodoPago', function ($query) use ($usuario) {
                $query->where('usuario_id', $usuario->id);
            })
            ->findOrFail($id);

        return response()->json(['pago' => $pago]);
    }
}
