<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sesion extends Model
{
    use HasFactory;

    protected $table = 'sesiones';

    protected $fillable = [
        'usuario_id',
        'token',
        'ip',
        'user_agent',
        'login_at',
        'logout_at',
        'activa',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'activa' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'activa' => true,
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    // Scope para sesiones activas
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    // Scope para sesiones de un usuario
    public function scopeDeUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }
}