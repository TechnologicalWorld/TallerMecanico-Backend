<?php

namespace App\Models;

use App\Enums\EstadoPersonaEnum;
use App\Enums\TipoPersonaEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Persona extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'personas';




    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tipo_persona',
        'nombre',
        'apellido',
        'razon_social',
        'identificacion_principal',
        'fecha_nacimiento',
        'genero',
        'foto_path',
        'estado',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_nacimiento' => 'date',
        'tipo_persona' => TipoPersonaEnum::class,
        'estado' => EstadoPersonaEnum::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'estado' => 'ACTIVO', // valor por defecto como string, el cast lo convierte
    ];

    /**
     * Get the user associated with the persona.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'persona_id');
    }

    /**
     * Get all contacts for the persona.
     */
    public function contactos(): MorphMany
    {
        return $this->morphMany(Contacto::class, 'entidad');
    }

    /**
     * Get all domicilios for the persona.
     */
    public function domicilios(): MorphMany
    {
        return $this->morphMany(Domicilio::class, 'entidad');
    }

    /**
     * Get all archivos for the persona.
     */
    public function archivos(): MorphMany
    {
        return $this->morphMany(Archivo::class, 'entidad');
    }

    /**
     * Scope a query to only include active personas.
     */
    public function scopeActivo($query)
    {
        return $query->where('estado', 'ACTIVO');
    }

    /**
     * Scope a query to only include personas of a given type.
     */
    public function scopeOfType($query, string $tipo)
    {
        return $query->where('tipo_persona', $tipo);
    }

    /**
     * Get the person's full name (for physical persons).
     */
    public function getNombreCompletoAttribute(): ?string
    {
        if ($this->tipo_persona === 'FISICA') {
            return trim($this->nombre . ' ' . $this->apellido);
        }
        return null;
    }

    /**
     * Get the display name (razon_social for moral, full name for fisica).
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->tipo_persona === 'MORAL') {
            return $this->razon_social ?? 'Sin razón social';
        }
        return $this->nombre_completo ?? 'Sin nombre';
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Al eliminar (soft delete) una persona, podrías también eliminar relaciones si es necesario
        static::deleting(function ($persona) {
            // Si haces soft delete, las relaciones no se borran automáticamente
            // pero puedes agregar lógica adicional si lo deseas.
        });
    }
}
