<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'calificaciones';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'puntuacion',
        'comentario',
        'fecha',
        'id_reserva',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'puntuacion' => 'integer',
        'fecha' => 'datetime',
        'id_reserva' => 'integer',
    ];

    /**
     * Relación muchos a uno con Reserva.
     */
    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'id_reserva');
    }

    /**
     * Scope para filtrar por puntuación mínima.
     */
    public function scopePorPuntuacionMinima($query, $puntuacion)
    {
        return $query->where('puntuacion', '>=', $puntuacion);
    }

    /**
     * Scope para filtrar calificaciones con comentario.
     */
    public function scopeConComentario($query)
    {
        return $query->whereNotNull('comentario');
    }
}
