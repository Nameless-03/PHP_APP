<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcepcionAgenda extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'excepciones_agenda';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fecha',
        'motivo',
        'disponible',
        'id_profesional',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha' => 'date',
        'disponible' => 'boolean',
        'id_profesional' => 'integer',
    ];

    /**
     * Relación muchos a uno con Profesional.
     */
    public function profesional()
    {
        return $this->belongsTo(Profesional::class, 'id_profesional');
    }

    /**
     * Scope para filtrar por fecha.
     */
    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha', $fecha);
    }

    /**
     * Scope para filtrar excepciones no disponibles.
     */
    public function scopeNoDisponibles($query)
    {
        return $query->where('disponible', false);
    }
}
