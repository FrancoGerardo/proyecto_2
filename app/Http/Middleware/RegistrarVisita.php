<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RegistrarVisita
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo registrar visitas a rutas autenticadas
        if ($request->user() && $request->isMethod('GET')) {
            $ruta = $request->path();
            $nombrePagina = $this->obtenerNombrePagina($ruta);
            
            DB::table('visitas_paginas')->insert([
                'ruta' => $ruta,
                'nombre_pagina' => $nombrePagina,
                'usuario_id' => $request->user()->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'fecha_visita' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $response;
    }

    private function obtenerNombrePagina(string $ruta): ?string
    {
        $nombres = [
            'dashboard' => 'Dashboard',
            'roles' => 'Gestión de Roles',
            'permisos' => 'Gestión de Permisos',
            'salas' => 'Gestión de Salas',
            'usuarios' => 'Gestión de Usuarios',
        ];

        foreach ($nombres as $key => $nombre) {
            if (str_starts_with($ruta, $key)) {
                return $nombre;
            }
        }

        return null;
    }
}

