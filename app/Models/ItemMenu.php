<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemMenu extends Model
{
    use HasFactory;

    protected $table = 'items_menu';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'nombre',
        'ruta',
        'icono',
        'orden',
        'permiso_requerido',
        'activo',
        'item_padre_id',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'orden' => 'integer',
        ];
    }

    /**
     * Relación con item padre (para submenús)
     */
    public function itemPadre()
    {
        return $this->belongsTo(ItemMenu::class, 'item_padre_id', 'id');
    }

    /**
     * Relación con items hijos (submenús)
     */
    public function itemsHijos()
    {
        return $this->hasMany(ItemMenu::class, 'item_padre_id', 'id')
            ->where('activo', true)
            ->orderBy('orden');
    }

    /**
     * Obtener items de menú activos sin padre (menú principal)
     */
    public static function obtenerMenuPrincipal()
    {
        return self::whereNull('item_padre_id')
            ->where('activo', true)
            ->orderBy('orden')
            ->with('itemsHijos')
            ->get();
    }
}
