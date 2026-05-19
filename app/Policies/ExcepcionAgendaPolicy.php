<?php

namespace App\Policies;

use App\Models\ExcepcionAgenda;
use App\Models\Usuario;

class ExcepcionAgendaPolicy
{
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Usuario $usuario, ExcepcionAgenda $excepcion): bool
    {
        return $usuario->id === $excepcion->id_profesional;
    }
}
