<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seguimiento extends Model
{
    use HasFactory;

    protected $table = 'seguimientos';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'ficha_id',
        'tipo',
        'fecha',
        'signos_vitales',
        'motivo_consulta',
        'nivel_urgencia',
        'diagnostico',
        'observaciones',
        'tratamiento_prescrito',
        'medicamentos',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'signos_vitales' => 'array',
            'medicamentos' => 'array',
        ];
    }

    /**
     * Relación con Ficha
     */
    public function ficha()
    {
        return $this->belongsTo(Ficha::class, 'ficha_id', 'id');
    }
}

