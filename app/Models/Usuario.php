<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\RoleEnum;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * El nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'fecha_registro',
        'role',
    ];

    /**
     * Los atributos que deben estar ocultos para la serialización.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Los atributos que deben ser casteados.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_registro' => 'datetime',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => RoleEnum::class,
    ];

    /**
     * Relación uno a uno con Cliente.
     */
    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'id_usuario');
    }

    /**
     * Relación uno a uno con Profesional.
     */
    public function profesional()
    {
        return $this->hasOne(Profesional::class, 'id_usuario');
    }

    /**
     * Relación uno a uno con Admin.
     */
    public function admin()
    {
        return $this->hasOne(Admin::class, 'id_usuario');
    }

    /**
     * Relación uno a muchos con Notificacion.
     */
    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'id_usuario');
    }

    /**
     * Scope para filtrar usuarios por rol.
     */
    public function scopePorRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Verifica si el usuario es cliente.
     */
    public function esCliente()
    {
        return $this->role === RoleEnum::CLIENTE;
    }

    /**
     * Verifica si el usuario es profesional.
     */
    public function esProfesional()
    {
        return $this->role === RoleEnum::PROFESIONAL;
    }

    /**
     * Verifica si el usuario es admin.
     */
    public function esAdmin()
    {
        return $this->role === RoleEnum::ADMIN;
    }
}
