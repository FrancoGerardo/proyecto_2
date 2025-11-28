<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanCuota extends Model
{
    use HasFactory;

    protected $table = 'planes_cuota';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'ficha_id',
        'numero_cuotas',
        'monto_total',
        'monto_cuota',
        'fecha_inicio',
        'intervalo_dias',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'numero_cuotas' => 'integer',
            'monto_total' => 'decimal:2',
            'monto_cuota' => 'decimal:2',
            'fecha_inicio' => 'date',
            'intervalo_dias' => 'integer',
        ];
    }

    /**
     * Relación con Pagos
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'plan_cuota_id', 'id');
    }

    /**
     * Obtener planes activos
     */
    public static function obtenerActivos()
    {
        return self::where('estado', 'ACTIVO')
            ->orderBy('fecha_inicio', 'desc')
            ->get();
    }
}
