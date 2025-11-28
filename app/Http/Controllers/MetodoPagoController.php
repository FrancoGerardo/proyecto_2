<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MetodoPago;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MetodoPagoController extends Controller
{
    /**
     * Página principal de métodos de pago del usuario
     */
    public function paginaPrincipalMetodoPago(Request $solicitud)
    {
        $usuario = $solicitud->user();
        
        $metodosPago = MetodoPago::obtenerActivosPorUsuario($usuario->id);

        return Inertia::render('MetodosPago/Index', [
            'metodosPago' => $metodosPago,
        ]);
    }

    /**
     * Guardar nuevo método de pago
     */
    public function guardarMetodoPago(Request $datos)
    {
        $usuario = $datos->user();

        $datos->validate([
            'tipo' => 'required|in:TARJETA_CREDITO,TARJETA_DEBITO,TRANSFERENCIA,EFECTIVO',
            'titular' => 'nullable|string|max:100',
            'numero_tarjeta_enmascarado' => 'nullable|string|max:50',
            'banco' => 'nullable|string|max:100',
            'numero_cuenta' => 'nullable|string|max:50',
            'predeterminado' => 'boolean',
        ], [
            'tipo.required' => 'El tipo de método de pago es obligatorio.',
            'tipo.in' => 'El tipo de método de pago seleccionado no es válido.',
        ]);

        // Si se marca como predeterminado, desmarcar los demás
        if ($datos->predeterminado) {
            MetodoPago::where('usuario_id', $usuario->id)
                ->update(['predeterminado' => false]);
        }

        $metodoPago = MetodoPago::create([
            'id' => Str::uuid()->toString(),
            'usuario_id' => $usuario->id,
            'tipo' => $datos->tipo,
            'titular' => $datos->titular,
            'numero_tarjeta_enmascarado' => $datos->numero_tarjeta_enmascarado,
            'banco' => $datos->banco,
            'numero_cuenta' => $datos->numero_cuenta,
            'datos_adicionales' => $datos->datos_adicionales ?? null,
            'activo' => true,
            'predeterminado' => $datos->predeterminado ?? false,
        ]);

        return redirect()->route('metodos-pago.index')
            ->with('success', 'Método de pago registrado exitosamente.');
    }

    /**
     * Actualizar método de pago
     */
    public function actualizarMetodoPago(Request $datos, string $id)
    {
        $usuario = $datos->user();
        $metodoPago = MetodoPago::where('usuario_id', $usuario->id)
            ->findOrFail($id);

        $datos->validate([
            'tipo' => 'required|in:TARJETA_CREDITO,TARJETA_DEBITO,TRANSFERENCIA,EFECTIVO',
            'titular' => 'nullable|string|max:100',
            'numero_tarjeta_enmascarado' => 'nullable|string|max:50',
            'banco' => 'nullable|string|max:100',
            'numero_cuenta' => 'nullable|string|max:50',
            'activo' => 'boolean',
            'predeterminado' => 'boolean',
        ], [
            'tipo.required' => 'El tipo de método de pago es obligatorio.',
            'tipo.in' => 'El tipo de método de pago seleccionado no es válido.',
        ]);

        // Si se marca como predeterminado, desmarcar los demás
        if ($datos->predeterminado) {
            MetodoPago::where('usuario_id', $usuario->id)
                ->where('id', '!=', $id)
                ->update(['predeterminado' => false]);
        }

        $metodoPago->update([
            'tipo' => $datos->tipo,
            'titular' => $datos->titular,
            'numero_tarjeta_enmascarado' => $datos->numero_tarjeta_enmascarado,
            'banco' => $datos->banco,
            'numero_cuenta' => $datos->numero_cuenta,
            'activo' => $datos->activo ?? true,
            'predeterminado' => $datos->predeterminado ?? false,
        ]);

        return redirect()->route('metodos-pago.index')
            ->with('success', 'Método de pago actualizado exitosamente.');
    }

    /**
     * Eliminar método de pago (desactivar)
     */
    public function eliminarMetodoPago(string $id)
    {
        $usuario = auth()->user();
        $metodoPago = MetodoPago::where('usuario_id', $usuario->id)
            ->findOrFail($id);

        // No eliminar físicamente, solo desactivar
        $metodoPago->update(['activo' => false]);

        return redirect()->route('metodos-pago.index')
            ->with('success', 'Método de pago eliminado exitosamente.');
    }
}
