<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraPaquete extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'compras_paquete';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sesiones_disponibles',
        'fecha_compra',
        'estado',
        'id_cliente',
        'id_paquete',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sesiones_disponibles' => 'integer',
        'fecha_compra' => 'datetime',
        'id_cliente' => 'integer',
        'id_paquete' => 'integer',
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
     * Relación muchos a uno con Cliente.
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    /**
     * Relación muchos a uno con Paquete.
     */
    public function paquete()
    {
        return $this->belongsTo(Paquete::class, 'id_paquete');
    }

    /**
     * Relación uno a muchos con Pago.
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_compra');
    }

    /**
     * Relación uno a muchos con Reserva.
     */
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'id_compra_paquete');
    }

    /**
     * Scope para filtrar por estado.
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para filtrar paquetes activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Scope para filtrar paquetes con sesiones disponibles.
     */
    public function scopeConSesiones($query)
    {
        return $query->where('sesiones_disponibles', '>', 0);
    }
}
