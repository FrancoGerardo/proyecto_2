<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PreferenciaTema;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TemaController extends Controller
{
    /**
     * Guardar o actualizar preferencias de tema del usuario
     */
    public function guardarPreferencias(Request $datos)
    {
        $usuario = $datos->user();
        
        if (!$usuario) {
            return back()->withErrors(['error' => 'Usuario no autenticado.']);
        }

        $datos->validate([
            'tema' => 'required|in:ninos,jovenes,adultos',
            'modo' => 'required|in:dia,noche,auto',
            'tamaño_fuente' => 'required|in:pequeño,normal,grande,muy-grande',
            'contraste' => 'required|in:normal,alto',
            'modo_auto' => 'boolean',
        ], [
            'tema.required' => 'El tema es obligatorio.',
            'tema.in' => 'El tema seleccionado no es válido.',
            'modo.required' => 'El modo es obligatorio.',
            'modo.in' => 'El modo seleccionado no es válido.',
            'tamaño_fuente.required' => 'El tamaño de fuente es obligatorio.',
            'tamaño_fuente.in' => 'El tamaño de fuente seleccionado no es válido.',
            'contraste.required' => 'El contraste es obligatorio.',
            'contraste.in' => 'El contraste seleccionado no es válido.',
        ]);

        $preferencia = PreferenciaTema::obtenerOPCrear($usuario->id);
        
        $preferencia->update([
            'tema' => $datos->tema,
            'modo' => $datos->modo,
            'tamaño_fuente' => $datos->tamaño_fuente,
            'contraste' => $datos->contraste ?? 'normal',
            'modo_auto' => $datos->modo_auto ?? false,
        ]);

        // Devolver redirect back compatible con Inertia
        return back();
    }

    /**
     * Obtener preferencias de tema del usuario actual
     */
    public function obtenerPreferencias(Request $solicitud)
    {
        $usuario = $solicitud->user();
        
        if (!$usuario) {
            return response()->json([
                'tema' => 'adultos',
                'modo' => 'dia',
                'tamaño_fuente' => 'normal',
                'contraste' => 'normal',
                'modo_auto' => false,
            ]);
        }

        $preferencia = PreferenciaTema::obtenerOPCrear($usuario->id);

        // Si está en modo automático, determinar modo según horario
        $modo = $preferencia->modo;
        if ($preferencia->modo_auto || $preferencia->modo === 'auto') {
            $hora = (int) date('H');
            $modo = ($hora >= 6 && $hora < 20) ? 'dia' : 'noche';
        }

        return response()->json([
            'tema' => $preferencia->tema,
            'modo' => $modo,
            'tamaño_fuente' => $preferencia->tamaño_fuente,
            'contraste' => $preferencia->contraste,
            'modo_auto' => $preferencia->modo_auto,
        ]);
    }
}
