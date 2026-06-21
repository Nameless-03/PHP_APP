<?php

namespace App\Policies;

use App\Models\Disponibilidad;
use App\Models\Usuario;

class DisponibilidadPolicy
{
    /**
     * Determina si el usuario puede actualizar el modelo.
     */
    public function update(Usuario $usuario, Disponibilidad $disponibilidad): bool
    {
        return $usuario->id === $disponibilidad->id_profesional;
    }

    /**
     * Determina si el usuario puede eliminar el modelo.
     */
    public function delete(Usuario $usuario, Disponibilidad $disponibilidad): bool
    {
        return $usuario->id === $disponibilidad->id_profesional;
    }
}
