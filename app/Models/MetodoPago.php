<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    use HasFactory;

    protected $table = 'metodos_pago';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'usuario_id',
        'tipo',
        'titular',
        'numero_tarjeta_enmascarado',
        'banco',
        'numero_cuenta',
        'datos_adicionales',
        'activo',
        'predeterminado',
    ];

    protected function casts(): array
    {
        return [
            'datos_adicionales' => 'array',
            'activo' => 'boolean',
            'predeterminado' => 'boolean',
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
     * Relación con Pagos
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'metodo_pago_id', 'id');
    }

    /**
     * Obtener métodos de pago activos de un usuario
     */
    public static function obtenerActivosPorUsuario(string $usuarioId)
    {
        return self::where('usuario_id', $usuarioId)
            ->where('activo', true)
            ->orderBy('predeterminado', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
