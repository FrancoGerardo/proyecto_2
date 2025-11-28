<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ficha extends Model
{
    use HasFactory;

    protected $table = 'fichas';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'cliente_id',
        'servicio_id',
        'medico_id',
        'sala_id',
        'fecha',
        'hora',
        'estado',
        'motivo_consulta',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'hora' => 'datetime',
        ];
    }

    /**
     * Relación con Cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'usuario_id');
    }

    /**
     * Relación con Servicio
     */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id', 'id');
    }

    /**
     * Relación con Médico
     */
    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id', 'usuario_id');
    }

    /**
     * Relación con Sala
     */
    public function sala()
    {
        return $this->belongsTo(Sala::class, 'sala_id', 'id');
    }

    /**
     * Relación con Seguimientos
     */
    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class, 'ficha_id', 'id');
    }

    /**
     * Relación con Planes de Cuota
     */
    public function planesCuota()
    {
        return $this->hasMany(PlanCuota::class, 'ficha_id', 'id');
    }

    /**
     * Relación con Pagos
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'ficha_id', 'id');
    }
}

