<?php

namespace App\Models;

use App\Enums\TipoContactoEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contacto extends Model
{
    use HasFactory;

    protected $table = 'contactos';

    protected $fillable = [
        'entidad_type',
        'entidad_id',
        'tipo',
        'valor',
    ];

    protected $casts = [
        'tipo' => TipoContactoEnum::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'tipo' => 'OTRO',
    ];

    // Relación polimórfica
    public function entidad()
    {
        return $this->morphTo();
    }

    // Scope por tipo
    public function scopeDeTipo($query, TipoContactoEnum $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    // Scope por valor (búsqueda)
    public function scopeConValor($query, $valor)
    {
        return $query->where('valor', 'LIKE', "%{$valor}%");
    }
}