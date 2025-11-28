<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class MedicoEspecialidad extends Model
{
    use HasFactory;

    protected $table = 'medico_especialidad';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'medico_id',
        'especialidad_id',
    ];

    /**
     * Relación con Usuario (médico)
     */
    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id', 'id');
    }

    /**
     * Relación con Especialidad
     */
    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id', 'id');
    }
}
