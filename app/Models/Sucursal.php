<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';

    protected $fillable = [
        'nombre',
        'codigo',
        'activa',
        'email',
        'logo_path',
        'descripcion',
        'horario_apertura',
        'horario_cierre',
        'direccion',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'horario_apertura' => 'datetime:H:i',
        'horario_cierre' => 'datetime:H:i',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'activa' => true,
    ];

    // Relaciones
    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_sucursal',"sucursal_id","usuario_id")
            ->withPivot('es_administrador', 'activo')
            ->withTimestamps();
    }

    public function usuariosActivos()
    {
        return $this->belongsToMany(User::class, 'usuario_sucursal')
            ->wherePivot('activo', true)
            ->withPivot('es_administrador')
            ->withTimestamps();
    }

    public function administradores()
    {
        return $this->belongsToMany(User::class, 'usuario_sucursal')
            ->wherePivot('es_administrador', true)
            ->wherePivot('activo', true)
            ->withPivot('activo')
            ->withTimestamps();
    }

    public function contactos()
    {
        return $this->morphMany(Contacto::class, 'entidad');
    }

    public function domicilios()
    {
        return $this->morphMany(Domicilio::class, 'entidad');
    }

    public function archivos()
    {
        return $this->morphMany(Archivo::class, 'entidad');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    public function scopeConUsuariosActivos($query)
    {
        return $query->whereHas('usuarios', function ($q) {
            $q->where('usuario_sucursal.activo', true);
        });
    }

    // Accesores
    public function getHorarioFormateadoAttribute(): ?string
    {
        if ($this->horario_apertura && $this->horario_cierre) {
            return $this->horario_apertura->format('H:i').' - '.$this->horario_cierre->format('H:i');
        }

        return null;
    }

    public function getEstaAbiertaAhoraAttribute(): bool
    {
        if (! $this->activa || ! $this->horario_apertura || ! $this->horario_cierre) {
            return false;
        }

        $ahora = now();
        $apertura = now()->setTimeFrom($this->horario_apertura);
        $cierre = now()->setTimeFrom($this->horario_cierre);

        // Manejar horarios que cruzan medianoche
        if ($cierre->lessThan($apertura)) {
            $cierre->addDay();
        }

        return $ahora->between($apertura, $cierre);
    }
}
