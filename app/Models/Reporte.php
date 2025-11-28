<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    use HasFactory;

    protected $table = 'reportes';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'tipo',
        'nombre',
        'parametros',
        'url_archivo',
        'estado',
        'usuario_generador',
    ];

    protected function casts(): array
    {
        return [
            'parametros' => 'array',
        ];
    }

    /**
     * Relación con Usuario
     */
    public function usuarioGenerador()
    {
        return $this->belongsTo(Usuario::class, 'usuario_generador', 'id');
    }
}

