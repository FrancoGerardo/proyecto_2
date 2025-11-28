<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    use HasFactory;

    protected $table = 'auditoria';
    public $incrementing = true;
    protected $keyType = 'integer';
    protected $primaryKey = 'id';

    protected $fillable = [
        'tabla_afectada',
        'registro_id',
        'accion',
        'usuario_id',
        'datos_anteriores',
        'datos_nuevos',
        'fecha',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'datos_anteriores' => 'array',
            'datos_nuevos' => 'array',
        ];
    }

    /**
     * Relación con Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }
}

