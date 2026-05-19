<?php

namespace App\Policies;

use App\Models\Disponibilidad;
use App\Models\Usuario;

class DisponibilidadPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(Usuario $usuario, Disponibilidad $disponibilidad): bool
    {
        return $usuario->id === $disponibilidad->id_profesional;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Usuario $usuario, Disponibilidad $disponibilidad): bool
    {
        return $usuario->id === $disponibilidad->id_profesional;
    }
}
