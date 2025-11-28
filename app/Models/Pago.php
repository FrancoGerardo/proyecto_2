<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'plan_cuota_id',
        'ficha_id',
        'metodo_pago_id',
        'monto',
        'tipo',
        'numero_cuota',
        'fecha_pago',
        'metodo_pago',
        'comprobante_url',
        'estado',
        // Campos PagoFácil
        'pagofacil_transaction_id',
        'company_transaction_id',
        'qr_base64',
        'qr_status',
        'qr_expiration',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'numero_cuota' => 'integer',
            'fecha_pago' => 'datetime',
            'qr_expiration' => 'datetime',
        ];
    }

    /**
     * Relación con PlanCuota
     */
    public function planCuota()
    {
        return $this->belongsTo(PlanCuota::class, 'plan_cuota_id', 'id');
    }

    /**
     * Relación con MetodoPago
     */
    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id', 'id');
    }

    /**
     * Relación con Ficha
     */
    public function ficha()
    {
        return $this->belongsTo(Ficha::class, 'ficha_id', 'id');
    }

    /**
     * Obtener pagos por usuario
     */
    public static function obtenerPorUsuario(string $usuarioId)
    {
        return self::whereHas('metodoPago', function ($query) use ($usuarioId) {
            $query->where('usuario_id', $usuarioId);
        })->orderBy('fecha_pago', 'desc')->get();
    }
}
