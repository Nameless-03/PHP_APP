<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disponibilidad extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'disponibilidades';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'pausa_minutos',
        'buffer_minutos',
        'id_profesional',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'pausa_minutos' => 'integer',
        'buffer_minutos' => 'integer',
        'id_profesional' => 'integer',
    ];

    /**
     * Valores por defecto de los atributos.
     *
     * @var array
     */
    protected $attributes = [
        'pausa_minutos' => 0,
        'buffer_minutos' => 0,
    ];

    /**
     * Relación muchos a uno con Profesional.
     */
    public function profesional()
    {
        return $this->belongsTo(Profesional::class, 'id_profesional');
    }

    /**
     * Scope para filtrar por día de la semana.
     */
    public function scopePorDia($query, $dia)
    {
        return $query->where('dia_semana', $dia);
    }
}
