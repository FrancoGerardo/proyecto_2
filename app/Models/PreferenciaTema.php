<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreferenciaTema extends Model
{
    use HasFactory;

    protected $table = 'preferencias_tema';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'usuario_id',
        'tema',
        'modo',
        'tamaño_fuente',
        'contraste',
        'modo_auto',
    ];

    protected function casts(): array
    {
        return [
            'modo_auto' => 'boolean',
        ];
    }

    /**
     * Relación con Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }

    /**
     * Obtener o crear preferencias para un usuario
     */
    public static function obtenerOPCrear(string $usuarioId)
    {
        return self::firstOrCreate(
            ['usuario_id' => $usuarioId],
            [
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'tema' => 'adultos',
                'modo' => 'dia',
                'tamaño_fuente' => 'normal',
                'contraste' => 'normal',
                'modo_auto' => false,
            ]
        );
    }
}
