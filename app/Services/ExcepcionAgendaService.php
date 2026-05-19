<?php

namespace App\Services;

use App\Models\ExcepcionAgenda;
use Illuminate\Database\Eloquent\Collection;
use Exception;

class ExcepcionAgendaService
{
    /**
     * Crear una nueva regla de excepción.
     */
    public function crear(array $data): ExcepcionAgenda
    {
        // Validar si ya existe una regla para esa fecha y ese profesional
        $existe = ExcepcionAgenda::where('id_profesional', $data['id_profesional'])
            ->whereDate('fecha', $data['fecha'])
            ->exists();

        if ($existe) {
            throw new Exception("Ya existe una regla de agenda para la fecha seleccionada.");
        }

        return ExcepcionAgenda::create($data);
    }

    /**
     * Listar reglas de un profesional.
     */
    public function listarPorProfesional(int $idProfesional): Collection
    {
        return ExcepcionAgenda::where('id_profesional', $idProfesional)
            ->orderBy('fecha', 'asc')
            ->get();
    }

    /**
     * Eliminar regla.
     */
    public function eliminar(ExcepcionAgenda $excepcion): bool
    {
        return $excepcion->delete();
    }
}
