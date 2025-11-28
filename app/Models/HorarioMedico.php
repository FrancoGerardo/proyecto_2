<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioMedico extends Model
{
    use HasFactory;

    protected $table = 'horarios_medicos';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'medico_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'hora_inicio' => 'datetime',
            'hora_fin' => 'datetime',
        ];
    }

    /**
     * Relación con Médico
     */
    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id', 'usuario_id');
    }
}
