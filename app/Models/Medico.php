<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Medico extends Model
{
    use HasFactory;

    protected $table = 'medicos';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'usuario_id';

    protected $fillable = [
        'usuario_id',
        'codigo_cmp',
        'horario_atencion',
    ];

    /**
     * Relación con User (Usuario de Laravel Jetstream)
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }

    /**
     * Relación con Especialidades (muchos a muchos)
     * Nota: medico_id en medico_especialidad ahora referencia Usuarios(id), no Medicos(usuario_id)
     */
    public function especialidades()
    {
        return $this->belongsToMany(Especialidad::class, 'medico_especialidad', 'medico_id', 'especialidad_id', 'usuario_id', 'id');
    }

    /**
     * Relación con Servicios (muchos a muchos)
     */
    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'medico_servicios', 'medico_id', 'servicio_id', 'usuario_id', 'id');
    }

    /**
     * Relación con Horarios
     */
    public function horarios()
    {
        return $this->hasMany(HorarioMedico::class, 'medico_id', 'usuario_id');
    }

    /**
     * Relación con Fichas
     */
    public function fichas()
    {
        return $this->hasMany(Ficha::class, 'medico_id', 'usuario_id');
    }
}

