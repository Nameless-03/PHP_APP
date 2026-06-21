<?php

namespace App\Policies;

use App\Models\ExcepcionAgenda;
use App\Models\Usuario;

class ExcepcionAgendaPolicy
{
    /**
     * Determina si el usuario puede eliminar el modelo.
     */
    public function delete(Usuario $usuario, ExcepcionAgenda $excepcion): bool
    {
        return $usuario->id === $excepcion->id_profesional;
    }
}
