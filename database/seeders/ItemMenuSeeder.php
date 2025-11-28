<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ItemMenu;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class ItemMenuSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar items existentes (usar delete en lugar de truncate para PostgreSQL)
        ItemMenu::query()->delete();

        // Menú principal - Dashboard (sin permiso)
        ItemMenu::create([
            'id' => Str::uuid()->toString(),
            'nombre' => 'Dashboard',
            'ruta' => 'dashboard',
            'icono' => 'home',
            'orden' => 1,
            'permiso_requerido' => null,
            'activo' => true,
            'item_padre_id' => null,
        ]);

        // Obtener TODOS los permisos existentes
        $todosLosPermisos = Permission::all();
        
        // Mapeo de nombres de permisos a rutas y nombres amigables
        $mapeoRutas = [
            'roles' => 'roles.index',
            'permisos' => 'permisos.index',
            'salas' => 'salas.index',
            'usuarios' => 'usuarios.index',
            'especialidades' => 'especialidades.index',
            'servicios' => 'servicios.index',
            'fichas' => 'fichas.index',
            'seguimientos' => 'seguimientos.index',
            'historiales-clinicos' => 'historiales-clinicos.index',
            'reportes' => 'reportes.index',
        ];

        $mapeoIconos = [
            'roles' => 'shield',
            'permisos' => 'key',
            'salas' => 'building',
            'usuarios' => 'users',
            'especialidades' => 'stethoscope',
            'servicios' => 'briefcase',
            'fichas' => 'file-text',
            'seguimientos' => 'activity',
            'historiales-clinicos' => 'folder',
            'reportes' => 'bar-chart',
        ];

        $orden = 2; // Empezar después del Dashboard

        // Crear items de menú para cada permiso que empiece con "ver-" o "gestionar-"
        foreach ($todosLosPermisos as $permiso) {
            $nombrePermiso = $permiso->name;
            
            // Solo crear menú para permisos de "ver-" o "gestionar-"
            if (str_starts_with($nombrePermiso, 'ver-') || str_starts_with($nombrePermiso, 'gestionar-')) {
                // Extraer el nombre del recurso (ej: "ver-roles" -> "roles")
                $recurso = str_replace(['ver-', 'gestionar-'], '', $nombrePermiso);
                
                // Generar nombre amigable (ej: "roles" -> "Roles", "especialidades" -> "Especialidades")
                // Convertir "historiales-clinicos" -> "Historiales Clinicos" -> "Historiales Clinicos"
                $nombreAmigable = ucwords(str_replace('-', ' ', $recurso));
                
                // Generar ruta (si existe en mapeo, usarla; si no, generar automáticamente en minúsculas)
                $ruta = $mapeoRutas[strtolower($recurso)] ?? strtolower($recurso) . '.index';
                
                // Generar icono (si existe en mapeo, usarlo; si no, usar 'circle')
                $icono = $mapeoIconos[strtolower($recurso)] ?? 'circle';
                
                // Verificar si ya existe un item para este recurso (evitar duplicados)
                $existe = ItemMenu::where('ruta', $ruta)->exists();
                
                if (!$existe) {
                    ItemMenu::create([
                        'id' => Str::uuid()->toString(),
                        'nombre' => $nombreAmigable,
                        'ruta' => $ruta,
                        'icono' => $icono,
                        'orden' => $orden++,
                        'permiso_requerido' => $nombrePermiso,
                        'activo' => true,
                        'item_padre_id' => null,
                    ]);
                }
            }
        }

        // Submenú de Pagos (si no existe ya)
        if (!ItemMenu::where('ruta', '#')->where('nombre', 'Pagos')->exists()) {
            $pagos = ItemMenu::create([
                'id' => Str::uuid()->toString(),
                'nombre' => 'Pagos',
                'ruta' => '#',
                'icono' => 'credit-card',
                'orden' => $orden++,
                'permiso_requerido' => null,
                'activo' => true,
                'item_padre_id' => null,
            ]);

            ItemMenu::create([
                'id' => Str::uuid()->toString(),
                'nombre' => 'Métodos de Pago',
                'ruta' => 'metodos-pago.index',
                'icono' => 'wallet',
                'orden' => 1,
                'permiso_requerido' => null,
                'activo' => true,
                'item_padre_id' => $pagos->id,
            ]);

            ItemMenu::create([
                'id' => Str::uuid()->toString(),
                'nombre' => 'Mis Pagos',
                'ruta' => 'pagos.index',
                'icono' => 'receipt',
                'orden' => 2,
                'permiso_requerido' => null,
                'activo' => true,
                'item_padre_id' => $pagos->id,
            ]);

            ItemMenu::create([
                'id' => Str::uuid()->toString(),
                'nombre' => 'Planes de Pago',
                'ruta' => 'planes-pago.index',
                'icono' => 'calendar',
                'orden' => 3,
                'permiso_requerido' => null,
                'activo' => true,
                'item_padre_id' => $pagos->id,
            ]);
        }
    }
}
