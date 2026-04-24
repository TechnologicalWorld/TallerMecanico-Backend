<?php

namespace App\Models;

use App\Enums\TipoArchivoEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Archivo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'archivos';

    protected $fillable = [
        'entidad_type',
        'entidad_id',
        'nombre',
        'ruta',
        'tipo',
        'fecha_expiracion',
    ];

    protected $casts = [
        'tipo' => TipoArchivoEnum::class,
        'fecha_expiracion' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relación polimórfica
    public function entidad()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeDeTipo($query, TipoArchivoEnum $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeProximosAExpiracion($query, $dias = 30)
    {
        return $query->whereNotNull('fecha_expiracion')
            ->where('fecha_expiracion', '<=', now()->addDays($dias))
            ->where('fecha_expiracion', '>=', now());
    }

    public function scopeExpirados($query)
    {
        return $query->whereNotNull('fecha_expiracion')
            ->where('fecha_expiracion', '<', now());
    }

    public function scopeVigentes($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('fecha_expiracion')
                ->orWhere('fecha_expiracion', '>=', now());
        });
    }
}
