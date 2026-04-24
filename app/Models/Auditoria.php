<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    use HasFactory;

    protected $table = 'auditoria';

    protected $fillable = [
        'usuario_id',
        'accion',
        'entidad_type',
        'entidad_id',
        'valores_anteriores',
        'valores_nuevos',
    ];

    protected $casts = [
        'valores_anteriores' => 'array',
        'valores_nuevos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relación con usuario (puede ser null)
    public function usuario()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    // Relación polimórfica con la entidad afectada
    public function entidad()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeDeEntidad($query, $entidad)
    {
        return $query->where('entidad_type', get_class($entidad))
                     ->where('entidad_id', $entidad->id);
    }

    public function scopeAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }

    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeEnFecha($query, $fecha)
    {
        return $query->whereDate('created_at', $fecha);
    }
}