<?php

namespace App\Models;

use App\Enums\TipoDomicilioEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Domicilio extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'domicilios';

    protected $fillable = [
        'entidad_type',
        'entidad_id',
        'tipo',
        'pais',
        'ciudad',
        'direccion',
        'codigo_postal',
        'principal',
    ];

    protected $casts = [
        'tipo' => TipoDomicilioEnum::class,
        'principal' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'principal' => false,
    ];

    // Relación polimórfica
    public function entidad()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopePrincipales($query)
    {
        return $query->where('principal', true);
    }

    public function scopeDeTipo($query, TipoDomicilioEnum $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeEnCiudad($query, $ciudad)
    {
        return $query->where('ciudad', 'LIKE', "%{$ciudad}%");
    }

    public function scopeConCodigoPostal($query, $codigoPostal)
    {
        return $query->where('codigo_postal', $codigoPostal);
    }
}