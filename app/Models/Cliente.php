<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'persona_id',
        'codigo_cliente',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relación: Cliente pertenece a una Persona (1:1 en lógica de negocio)
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    /**
     * Relación: Un cliente puede tener muchos vehículos
     */
    public function vehiculos(): HasMany
    {
        return $this->hasMany(Vehiculo::class, 'cliente_id');
    }

    /**
     * Scope para filtrar solo clientes activos.
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Accesor para obtener el nombre legible directamente desde el cliente.
     * Ejemplo: $cliente->nombre_mostrable
     */
    public function getNombreMostrableAttribute(): string
    {
        return $this->persona ? $this->persona->display_name : 'Sin asignar';
    }

    /**
     * Boot del modelo.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cliente) {
            if (empty($cliente->codigo_cliente)) {
                $cliente->codigo_cliente = 'CLI-' . strtoupper(uniqid());
            }
        });
    }
}