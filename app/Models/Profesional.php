<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profesional extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'profesionales';

    /**
     * La clave primaria de la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'id_usuario';

    /**
     * Indica si el ID es autoincrementable.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_usuario',
        'descripcion',
        'experiencia',
        'ubicacion',
        'modalidad_preferida',
        'reputacion',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id_usuario' => 'integer',
        'reputacion' => 'decimal:2',
    ];

    /**
     * Relación muchos a uno con Usuario.
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    /**
     * Relación uno a muchos con Servicio.
     */
    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'id_profesional');
    }

    /**
     * Relación uno a muchos con Disponibilidad.
     */
    public function disponibilidades()
    {
        return $this->hasMany(Disponibilidad::class, 'id_profesional');
    }

    /**
     * Relación uno a muchos con ExcepcionAgenda.
     */
    public function excepcionesAgenda()
    {
        return $this->hasMany(ExcepcionAgenda::class, 'id_profesional');
    }

    /**
     * Relación uno a muchos con Paquete.
     */
    public function paquetes()
    {
        return $this->hasMany(Paquete::class, 'id_profesional');
    }
}
