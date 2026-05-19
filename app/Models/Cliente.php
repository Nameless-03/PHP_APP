<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'clientes';

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
        'telefono',
        'foto_perfil',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id_usuario' => 'integer',
    ];

    /**
     * Relación muchos a uno con Usuario.
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    /**
     * Relación uno a muchos con Reserva.
     */
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'id_cliente');
    }

    /**
     * Relación uno a muchos con CompraPaquete.
     */
    public function comprasPaquetes()
    {
        return $this->hasMany(CompraPaquete::class, 'id_cliente');
    }
}
