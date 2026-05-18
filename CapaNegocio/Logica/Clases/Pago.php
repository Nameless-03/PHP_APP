<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'pagos';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'monto',
        'fecha',
        'metodo',
        'estado',
        'referencia_externa',
        'id_reserva',
        'id_compra',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'datetime',
        'id_reserva' => 'integer',
        'id_compra' => 'integer',
    ];

    /**
     * Valores por defecto de los atributos.
     *
     * @var array
     */
    protected $attributes = [
        'estado' => 'pendiente',
    ];

    /**
     * Relación muchos a uno con Reserva.
     */
    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'id_reserva');
    }

    /**
     * Relación muchos a uno con CompraPaquete.
     */
    public function compraPaquete()
    {
        return $this->belongsTo(CompraPaquete::class, 'id_compra');
    }

    /**
     * Scope para filtrar por estado.
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para filtrar pagos completados.
     */
    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado');
    }

    /**
     * Scope para filtrar por método de pago.
     */
    public function scopePorMetodo($query, $metodo)
    {
        return $query->where('metodo', $metodo);
    }
}
