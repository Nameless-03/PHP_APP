<?php

namespace App\Policies;

use App\Models\Servicio;
use App\Models\Usuario;

class ServicioPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(Usuario $usuario, Servicio $servicio): bool
    {
        return $usuario->id === $servicio->id_profesional || $usuario->esAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Usuario $usuario, Servicio $servicio): bool
    {
        return $usuario->id === $servicio->id_profesional || $usuario->esAdmin();
    }
}
