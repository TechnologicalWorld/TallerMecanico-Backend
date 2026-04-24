<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'persona_id',
        'email',
        'username',
        'password',
        'current_branch_id',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $guard_name = 'api';
    

    protected $casts = [
        'activo' => 'boolean',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'activo' => true,
    ];

    // Relaciones
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function currentBranch()
    {
        return $this->belongsTo(Sucursal::class, 'current_branch_id');
    }

    public function sucursales()
    {
        return $this->belongsToMany(Sucursal::class, 'usuario_sucursal',"usuario_id","sucursal_id")
            ->withPivot('es_administrador', 'activo')
            ->withTimestamps();
    }

    public function sesiones()
    {
        return $this->hasMany(Sesion::class, 'usuario_id');
    }

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class, 'usuario_id');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Métodos útiles
    public function esAdministradorDeSucursal(Sucursal $sucursal): bool
    {
        return $this->sucursales()
            ->where('sucursal_id', $sucursal->id)
            ->wherePivot('es_administrador', true)
            ->exists();
    }

    public function tieneSucursalActiva(Sucursal $sucursal): bool
    {
        return $this->sucursales()
            ->where('sucursal_id', $sucursal->id)
            ->wherePivot('activo', true)
            ->exists();
    }
}
