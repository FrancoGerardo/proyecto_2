<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemMenu;
use Inertia\Inertia;

class MenuController extends Controller
{
    /**
     * Obtener menú dinámico para el usuario actual
     */
    public function obtenerMenu(Request $solicitud)
    {
        $usuario = $solicitud->user();
        
        if (!$usuario) {
            return response()->json(['items' => []]);
        }

        $itemsMenu = ItemMenu::obtenerMenuPrincipal();
        
        // Filtrar items según permisos del usuario
        $itemsFiltrados = $itemsMenu->filter(function ($item) use ($usuario) {
            if ($item->permiso_requerido) {
                return $usuario->can($item->permiso_requerido);
            }
            return true;
        })->map(function ($item) use ($usuario) {
            // Filtrar también los submenús
            $itemsHijos = $item->itemsHijos->filter(function ($hijo) use ($usuario) {
                if ($hijo->permiso_requerido) {
                    return $usuario->can($hijo->permiso_requerido);
                }
                return true;
            });
            
            return [
                'id' => $item->id,
                'nombre' => $item->nombre,
                'ruta' => $item->ruta,
                'icono' => $item->icono,
                'orden' => $item->orden,
                'items_hijos' => $itemsHijos->map(function ($hijo) {
                    return [
                        'id' => $hijo->id,
                        'nombre' => $hijo->nombre,
                        'ruta' => $hijo->ruta,
                        'icono' => $hijo->icono,
                        'orden' => $hijo->orden,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json(['items' => $itemsFiltrados]);
    }

    /**
     * Página principal de gestión de menú (solo administradores)
     */
    public function paginaPrincipalMenu()
    {
        $this->authorize('gestionar-roles'); // Usar permiso de administrador

        $itemsMenu = ItemMenu::with('itemPadre', 'itemsHijos')
            ->orderBy('orden')
            ->get();

        return Inertia::render('Menu/Index', [
            'itemsMenu' => $itemsMenu,
        ]);
    }

    /**
     * Guardar nuevo item de menú
     */
    public function guardarItem(Request $datos)
    {
        $this->authorize('gestionar-roles');

        $datos->validate([
            'nombre' => 'required|string|max:100',
            'ruta' => 'required|string|max:255',
            'icono' => 'nullable|string|max:100',
            'orden' => 'required|integer|min:0',
            'permiso_requerido' => 'nullable|string|max:100',
            'activo' => 'boolean',
            'item_padre_id' => 'nullable|exists:items_menu,id',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'ruta.required' => 'La ruta es obligatoria.',
            'orden.required' => 'El orden es obligatorio.',
            'item_padre_id.exists' => 'El item padre seleccionado no existe.',
        ]);

        $itemMenu = ItemMenu::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'nombre' => $datos->nombre,
            'ruta' => $datos->ruta,
            'icono' => $datos->icono,
            'orden' => $datos->orden,
            'permiso_requerido' => $datos->permiso_requerido,
            'activo' => $datos->activo ?? true,
            'item_padre_id' => $datos->item_padre_id,
        ]);

        return redirect()->route('menu.index')
            ->with('success', 'Item de menú creado exitosamente.');
    }

    /**
     * Actualizar item de menú
     */
    public function actualizarItem(Request $datos, string $id)
    {
        $this->authorize('gestionar-roles');

        $itemMenu = ItemMenu::findOrFail($id);

        $datos->validate([
            'nombre' => 'required|string|max:100',
            'ruta' => 'required|string|max:255',
            'icono' => 'nullable|string|max:100',
            'orden' => 'required|integer|min:0',
            'permiso_requerido' => 'nullable|string|max:100',
            'activo' => 'boolean',
            'item_padre_id' => 'nullable|exists:items_menu,id',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'ruta.required' => 'La ruta es obligatoria.',
            'orden.required' => 'El orden es obligatorio.',
            'item_padre_id.exists' => 'El item padre seleccionado no existe.',
        ]);

        $itemMenu->update([
            'nombre' => $datos->nombre,
            'ruta' => $datos->ruta,
            'icono' => $datos->icono,
            'orden' => $datos->orden,
            'permiso_requerido' => $datos->permiso_requerido,
            'activo' => $datos->activo ?? true,
            'item_padre_id' => $datos->item_padre_id,
        ]);

        return redirect()->route('menu.index')
            ->with('success', 'Item de menú actualizado exitosamente.');
    }

    /**
     * Eliminar item de menú
     */
    public function eliminarItem(string $id)
    {
        $this->authorize('gestionar-roles');

        $itemMenu = ItemMenu::findOrFail($id);
        $itemMenu->delete();

        return redirect()->route('menu.index')
            ->with('success', 'Item de menú eliminado exitosamente.');
    }
}
