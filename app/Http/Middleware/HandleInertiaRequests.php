<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $usuario = $request->user();

        // Obtener menú dinámico (con verificación de tabla)
        $itemsMenu = [];
        if ($usuario) {
            try {
                // Log: Verificar permisos del usuario
                \Illuminate\Support\Facades\Log::info('🔍 [Menu] Usuario ID: ' . $usuario->id);
                \Illuminate\Support\Facades\Log::info('🔍 [Menu] Permisos del usuario: ' . json_encode($usuario->getAllPermissions()->pluck('name')->toArray()));

                // Verificar si la tabla existe antes de consultar
                if (\Illuminate\Support\Facades\Schema::hasTable('items_menu')) {
                    $itemsMenuPrincipal = \App\Models\ItemMenu::obtenerMenuPrincipal();
                    \Illuminate\Support\Facades\Log::info('🔍 [Menu] Items encontrados en BD: ' . $itemsMenuPrincipal->count());

                    $itemsMenu = $itemsMenuPrincipal
                        ->filter(function ($item) use ($usuario) {
                            if ($item->permiso_requerido) {
                                $tienePermiso = $usuario->hasAnyPermission([$item->permiso_requerido]);
                                \Illuminate\Support\Facades\Log::info("🔍 [Menu] Item: {$item->nombre} - Permiso requerido: {$item->permiso_requerido} - Tiene permiso: " . ($tienePermiso ? 'SI' : 'NO'));
                                return $tienePermiso;
                            }
                            \Illuminate\Support\Facades\Log::info("🔍 [Menu] Item: {$item->nombre} - Sin permiso requerido - Mostrando");
                            return true;
                        })
                        ->map(function ($item) use ($usuario) {
                            // Verificar si el usuario tiene rol "Cliente"
                            $esCliente = $usuario->hasRole('Cliente');

                            // Modificar la ruta si es cliente
                            $rutaItem = $item->ruta;
                            if ($esCliente && $rutaItem !== '#' && $rutaItem !== 'dashboard') {
                                // Si la ruta no tiene el prefijo 'cliente.', intentar agregarlo
                                if (!str_starts_with($rutaItem, 'cliente.')) {
                                    $rutaConPrefijo = 'cliente.' . $rutaItem;
                                    // Verificar que la ruta con prefijo exista
                                    if (\Illuminate\Support\Facades\Route::has($rutaConPrefijo)) {
                                        $rutaItem = $rutaConPrefijo;
                                        \Illuminate\Support\Facades\Log::info("✅ [Menu] Ruta modificada: {$item->ruta} → {$rutaItem}");
                                    } else {
                                        \Illuminate\Support\Facades\Log::warning("⚠️ [Menu] Ruta {$rutaConPrefijo} no existe, usando original: {$rutaItem}");
                                    }
                                }
                            }

                            $itemsHijos = $item->itemsHijos->filter(function ($hijo) use ($usuario) {
                                if ($hijo->permiso_requerido) {
                                    return $usuario->hasAnyPermission([$hijo->permiso_requerido]);
                                }
                                return true;
                            });

                            return [
                                'id' => $item->id,
                                'nombre' => $item->nombre,
                                'ruta' => $rutaItem, // Usar la ruta modificada
                                'icono' => $item->icono,
                                'orden' => $item->orden,
                                'items_hijos' => $itemsHijos->map(function ($hijo) use ($esCliente) {
                                    // Modificar también las rutas de los hijos si es cliente
                                    $rutaHijo = $hijo->ruta;
                                    if ($esCliente && $rutaHijo !== '#') {
                                        if (!str_starts_with($rutaHijo, 'cliente.')) {
                                            $rutaConPrefijo = 'cliente.' . $rutaHijo;
                                            // Verificar que la ruta con prefijo exista
                                            if (\Illuminate\Support\Facades\Route::has($rutaConPrefijo)) {
                                                $rutaHijo = $rutaConPrefijo;
                                            }
                                        }
                                    }

                                    return [
                                        'id' => $hijo->id,
                                        'nombre' => $hijo->nombre,
                                        'ruta' => $rutaHijo, // Usar la ruta modificada
                                        'icono' => $hijo->icono,
                                        'orden' => $hijo->orden,
                                    ];
                                })->values(),
                            ];
                        })->values();

                    \Illuminate\Support\Facades\Log::info('🔍 [Menu] Items filtrados finales: ' . $itemsMenu->count());
                }
            } catch (\Exception $e) {
                // Si hay error, dejar menú vacío
                \Illuminate\Support\Facades\Log::error('❌ [Menu] Error al cargar menú: ' . $e->getMessage());
                $itemsMenu = [];
            }
        }

        // Obtener preferencias de tema (con verificación de tabla)
        $preferenciasTema = null;
        if ($usuario) {
            try {
                // Verificar si la tabla existe antes de consultar
                if (\Illuminate\Support\Facades\Schema::hasTable('preferencias_tema')) {
                    $preferencia = \App\Models\PreferenciaTema::obtenerOPCrear($usuario->id);
                    $modo = $preferencia->modo;
                    if ($preferencia->modo_auto || $preferencia->modo === 'auto') {
                        $hora = (int) date('H');
                        $modo = ($hora >= 6 && $hora < 20) ? 'dia' : 'noche';
                    }

                    $preferenciasTema = [
                        'tema' => $preferencia->tema,
                        'modo' => $modo,
                        'tamaño_fuente' => $preferencia->tamaño_fuente,
                        'contraste' => $preferencia->contraste,
                        'modo_auto' => $preferencia->modo_auto,
                    ];
                } else {
                    // Valores por defecto si la tabla no existe
                    $preferenciasTema = [
                        'tema' => 'adultos',
                        'modo' => 'dia',
                        'tamaño_fuente' => 'normal',
                        'contraste' => 'normal',
                        'modo_auto' => false,
                    ];
                }
            } catch (\Exception $e) {
                // Valores por defecto si hay error
                $preferenciasTema = [
                    'tema' => 'adultos',
                    'modo' => 'dia',
                    'tamaño_fuente' => 'normal',
                    'contraste' => 'normal',
                    'modo_auto' => false,
                ];
            }
        }

        return [
            ...parent::share($request),
            //
            //AQUI TENEMOS LO QUE ES EL AUTH Y LOS ROLES Y PERMISOS
            'auth' => [
                'user' => $usuario ? [
                    'roles' => $usuario->getRoleNames()->toArray(),
                    'permissions' => $usuario->getAllPermissions()->pluck('name')->toArray(),
                ] : null,
            ],
            'menu' => $itemsMenu,
            'preferenciasTema' => $preferenciasTema,
            // Flash messages explícitos
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'qr_data' => $request->session()->get('qr_data'),
            ],
        ];
    }
}
