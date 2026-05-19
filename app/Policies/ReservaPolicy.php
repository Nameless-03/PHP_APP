<?php

namespace App\Policies;

use App\Models\Reserva;
use App\Models\Usuario;

class ReservaPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(Usuario $usuario, Reserva $reserva): bool
    {
        return $usuario->id === $reserva->id_cliente || 
               $usuario->id === $reserva->servicio->id_profesional ||
               $usuario->esAdmin();
    }

    /**
     * Determine whether the user can update the state of the model.
     */
    public function updateEstado(Usuario $usuario, Reserva $reserva): bool
    {
        // El cliente puede cancelarla
        // El profesional puede confirmarla, finalizarla, marcar como no asistida
        return $usuario->id === $reserva->id_cliente || 
               $usuario->id === $reserva->servicio->id_profesional;
    }
}
