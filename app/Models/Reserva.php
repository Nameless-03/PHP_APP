<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\EstadoReservaEnum;

class Reserva extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'reservas';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fecha_hora_inicio',
        'fecha_hora_fin',
        'estado',
        'observaciones',
        'id_cliente',
        'id_servicio',
        'id_compra_paquete',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_hora_inicio' => 'datetime',
        'fecha_hora_fin' => 'datetime',
        'id_cliente' => 'integer',
        'id_servicio' => 'integer',
        'id_compra_paquete' => 'integer',
        'estado' => EstadoReservaEnum::class,
    ];

    /**
     * Valores por defecto de los atributos.
     *
     * @var array
     */
    protected $attributes = [
        'estado' => EstadoReservaEnum::PENDIENTE->value,
    ];

    /**
     * Relación muchos a uno con Cliente.
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    /**
     * Relación muchos a uno con Servicio.
     */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio');
    }

    /**
     * Relación muchos a uno con CompraPaquete.
     */
    public function compraPaquete()
    {
        return $this->belongsTo(CompraPaquete::class, 'id_compra_paquete');
    }

    /**
     * Relación uno a uno con Pago.
     */
    public function pago()
    {
        return $this->hasOne(Pago::class, 'id_reserva');
    }

    /**
     * Relación uno a uno con Videollamada.
     */
    public function videollamada()
    {
        return $this->hasOne(Videollamada::class, 'id_reserva');
    }

    /**
     * Relación uno a uno con Calificacion.
     */
    public function calificacion()
    {
        return $this->hasOne(Calificacion::class, 'id_reserva');
    }

    /**
     * Scope para filtrar por estado.
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para filtrar reservas pendientes.
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    /**
     * Scope para filtrar reservas confirmadas.
     */
    public function scopeConfirmadas($query)
    {
        return $query->where('estado', 'confirmada');
    }

    /**
     * Scope para filtrar reservas pagadas.
     */
    public function scopePagadas($query)
    {
        return $query->where('estado', 'pagada');
    }

    /**
     * Scope para filtrar reservas finalizadas.
     */
    public function scopeFinalizadas($query)
    {
        return $query->where('estado', 'finalizada');
    }

    /**
     * Scope para filtrar por fecha.
     */
    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha_hora_inicio', $fecha);
    }
}
