<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Sala;
use App\Models\Servicio;
use App\Models\Ficha;
use App\Models\Cliente;
use App\Models\Medico;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class BusquedaController extends Controller
{
    /**
     * Realizar búsqueda general en el sistema
     */
    public function buscar(Request $solicitud)
    {
        $termino = $solicitud->get('q', '');
        
        if (empty($termino)) {
            return Inertia::render('Busqueda/Index', [
                'resultados' => [],
                'termino' => '',
            ]);
        }

        $resultados = [
            'usuarios' => [],
            'salas' => [],
            'servicios' => [],
            'fichas' => [],
        ];

        // Búsqueda en Usuarios
        if ($solicitud->user()->can('ver-usuarios')) {
            $resultados['usuarios'] = Usuario::with('persona')
                ->whereHas('persona', function ($query) use ($termino) {
                    $query->where('nombre', 'ilike', "%{$termino}%")
                        ->orWhere('apellidos', 'ilike', "%{$termino}%")
                        ->orWhere('dni', 'ilike', "%{$termino}%");
                })
                ->orWhere('email', 'ilike', "%{$termino}%")
                ->limit(10)
                ->get();
        }

        // Búsqueda en Salas
        if ($solicitud->user()->can('ver-salas')) {
            $resultados['salas'] = Sala::where('numero', 'ilike', "%{$termino}%")
                ->orWhere('categoria', 'ilike', "%{$termino}%")
                ->limit(10)
                ->get();
        }

        // Búsqueda en Servicios
        if ($solicitud->user()->can('ver-servicios')) {
            $resultados['servicios'] = Servicio::where('nombre', 'ilike', "%{$termino}%")
                ->orWhere('descripcion', 'ilike', "%{$termino}%")
                ->orWhere('categoria', 'ilike', "%{$termino}%")
                ->limit(10)
                ->get();
        }

        // Búsqueda en Fichas
        if ($solicitud->user()->can('ver-fichas')) {
            $resultados['fichas'] = Ficha::with(['cliente.usuario.persona', 'servicio', 'medico.usuario.persona'])
                ->where('motivo_consulta', 'ilike', "%{$termino}%")
                ->orWhereHas('cliente.usuario.persona', function ($query) use ($termino) {
                    $query->where('nombre', 'ilike', "%{$termino}%")
                        ->orWhere('apellidos', 'ilike', "%{$termino}%");
                })
                ->limit(10)
                ->get();
        }

        return Inertia::render('Busqueda/Index', [
            'resultados' => $resultados,
            'termino' => $termino,
        ]);
    }
}

