<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'notificaciones';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'titulo',
        'mensaje',
        'tipo',
        'leida',
        'fecha',
        'id_usuario',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'leida' => 'boolean',
        'fecha' => 'datetime',
        'id_usuario' => 'integer',
    ];

    /**
     * Valores por defecto de los atributos.
     *
     * @var array
     */
    protected $attributes = [
        'leida' => false,
    ];

    /**
     * Relación muchos a uno con Usuario.
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    /**
     * Scope para filtrar notificaciones no leídas.
     */
    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    /**
     * Scope para filtrar por tipo.
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Marcar notificación como leída.
     */
    public function marcarComoLeida()
    {
        $this->update(['leida' => true]);
    }
}
