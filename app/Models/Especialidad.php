<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    use HasFactory;

    protected $table = 'especialidades';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'nombre',
        'descripcion',
        'estado',
    ];

    /**
     * Relación con Médicos (muchos a muchos)
     */
    public function medicos()
    {
        return $this->belongsToMany(Medico::class, 'medico_especialidad', 'especialidad_id', 'medico_id', 'id', 'usuario_id');
    }

    /**
     * Relación con Servicios
     */
    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'especialidad_id', 'id');
    }
}
