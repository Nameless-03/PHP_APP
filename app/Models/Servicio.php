<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Servicio extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'servicios';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'modalidad',
        'duracion',
        'ubicacion',
        'activo',
        'id_profesional',
        'id_categoria',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'precio' => 'decimal:2',
        'duracion' => 'integer',
        'activo' => 'boolean',
        'id_profesional' => 'integer',
        'id_categoria' => 'integer',
    ];

    /**
     * Valores por defecto de los atributos.
     *
     * @var array
     */
    protected $attributes = [
        'activo' => true,
    ];

    /**
     * Relación muchos a uno con Profesional.
     */
    public function profesional()
    {
        return $this->belongsTo(Profesional::class, 'id_profesional');
    }

    /**
     * Relación muchos a uno con Categoria.
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    /**
     * Relación uno a muchos con Reserva.
     */
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'id_servicio');
    }

    /**
     * Scope para filtrar servicios activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por modalidad.
     */
    public function scopePorModalidad($query, $modalidad)
    {
        return $query->where('modalidad', $modalidad);
    }

    /**
     * Relación muchos a muchos con Paquete.
     */
    public function paquetes()
    {
        return $this->belongsToMany(Paquete::class, 'paquete_servicio', 'id_servicio', 'id_paquete');
    }
}

