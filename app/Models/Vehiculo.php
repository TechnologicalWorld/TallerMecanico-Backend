<?php

namespace App\Models;

use App\Enums\VehiculoEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehiculo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehiculos';

    protected $fillable = [
        'cliente_id',
        'sucursal_id',
        'placa',
        'vin',
        'marca',
        'modelo',
        'anio',
        'color',
        'tipo',
        'kilometraje',
        'estado',
    ];

    protected $casts = [
        'anio'        => 'integer',
        'kilometraje' => 'integer',
        'estado'      => VehiculoEnum::class, 
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    /**
     * Relación con el Cliente.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Relación con la Sucursal (Plantilla)
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Accesor para obtener una descripción rápida del vehículo.
     * Ejemplo: "Toyota Corolla - 123-ABC"
     */
    public function getDescripcionCompletaAttribute(): string
    {
        return "{$this->marca} {$this->modelo} - {$this->placa}";
    }

    /**
     * Scope para filtrar por sucursal activa fácilmente.
     */
    public function scopeDeSucursal($query, $sucursalId)
    {
        return $query->where('sucursal_id', $sucursalId);
    }
}