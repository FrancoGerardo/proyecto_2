<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    protected $table = 'usuarios';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'persona_id',
        'email',
        'password_hash',
        'foto_url',
        'profile_photo_path',
        'tipo_usuario',
        'estado',
        'fecha_registro',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
        'name',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'estado' => 'boolean',
            'fecha_registro' => 'datetime',
        ];
    }

    /**
     * Get the password attribute (alias for password_hash)
     * Laravel Auth espera 'password'
     */
    public function getPasswordAttribute()
    {
        return $this->password_hash;
    }

    /**
     * Set the password attribute (alias for password_hash)
     * Laravel Auth usa 'password'
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password_hash'] = $value;
    }

    /**
     * Get the password for authentication (required by Laravel Auth)
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Get the name attribute from persona
     */
    public function getNameAttribute(): string
    {
        return $this->persona ? $this->persona->nombre_completo : '';
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
