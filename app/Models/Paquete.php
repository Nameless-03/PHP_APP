<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paquete extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'paquetes';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'cantidad_sesiones',
        'precio',
        'vencimiento',
        'id_profesional',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cantidad_sesiones' => 'integer',
        'precio' => 'decimal:2',
        'vencimiento' => 'integer',
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
     * Relación uno a muchos con CompraPaquete.
     */
    public function comprasPaquetes()
    {
        return $this->hasMany(CompraPaquete::class, 'id_paquete');
    }

    /**
     * Relación muchos a muchos con Servicio.
     */
    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'paquete_servicio', 'id_paquete', 'id_servicio');
    }
}

