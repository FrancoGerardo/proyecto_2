<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    use HasFactory;

    protected $table = 'usuarios';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'persona_id',
        'email',
        'password_hash',
        'foto_url',
        'tipo_usuario',
        'estado',
        'fecha_registro',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
            'fecha_registro' => 'datetime',
        ];
    }

    /**
     * Relación con Persona
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id');
    }

    /**
     * Relaciones polimórficas según tipo de usuario
     */
    public function propietario()
    {
        return $this->hasOne(Propietario::class, 'usuario_id', 'id');
    }

    public function secretaria()
    {
        return $this->hasOne(Secretaria::class, 'usuario_id', 'id');
    }

    public function medico()
    {
        return $this->hasOne(Medico::class, 'usuario_id', 'id');
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'usuario_id', 'id');
    }

    /**
     * Obtener el perfil especializado según el tipo
     */
    public function getPerfilAttribute()
    {
        return match($this->tipo_usuario) {
            'PROPIETARIO' => $this->propietario,
            'SECRETARIA' => $this->secretaria,
            'MEDICO' => $this->medico,
            'CLIENTE' => $this->cliente,
            default => null,
        };
    }
}

