<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'usuario_id';

    protected $fillable = [
        'usuario_id',
        'antecedentes',
    ];

    /**
     * Relación con Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }

    /**
     * Relación con Fichas
     */
    public function fichas()
    {
        return $this->hasMany(Ficha::class, 'cliente_id', 'usuario_id');
    }

    /**
     * Relación con Historial Clínico
     */
    public function historialClinico()
    {
        return $this->hasOne(HistorialClinico::class, 'cliente_id', 'usuario_id');
    }
}

