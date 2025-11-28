<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'nombre',
        'descripcion',
        'categoria',
        'especialidad_id',
        'meedico_id',
        'costo',
        'duracion_minutos',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'costo' => 'decimal:2',
            'estado' => 'boolean',
            'duracion_minutos' => 'integer',
        ];
    }

    /**
     * Relación con Especialidad
     */
    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id', 'id');
    }

    /**
     * Relación con Usuario (médico) - campo meedico_id
     */
    public function medicoPrincipal()
    {
        return $this->belongsTo(User::class, 'meedico_id', 'id');
    }

    /**
     * Relación con Médicos (muchos a muchos)
     */
    public function medicos()
    {
        return $this->belongsToMany(Medico::class, 'medico_servicios', 'servicio_id', 'medico_id', 'id', 'usuario_id');
    }

    /**
     * Relación con Fichas
     */
    public function fichas()
    {
        return $this->hasMany(Ficha::class, 'servicio_id', 'id');
    }
}

