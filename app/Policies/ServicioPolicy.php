<?php

namespace App\Policies;

use App\Models\Servicio;
use App\Models\Usuario;

class ServicioPolicy
{
    /**
     * Determina si el usuario puede actualizar el modelo.
     */
    public function update(Usuario $usuario, Servicio $servicio): bool
    {
        return $usuario->id === $servicio->id_profesional || $usuario->esAdmin();
    }

    /**
     * Determina si el usuario puede eliminar el modelo.
     */
    public function delete(Usuario $usuario, Servicio $servicio): bool
    {
        return $usuario->id === $servicio->id_profesional || $usuario->esAdmin();
    }
}
