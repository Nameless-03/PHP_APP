<?php

namespace App\Manejadores;

use App\Models\Notificacion;
use App\DataTypes\DtNotificacion;
use App\Enums\TipoNotificacionEnum;
use Exception;

class ManejadorNotificacion
{
    /**
     * Crea una nueva notificación.
     */
    public function crear(
        int $idUsuario,
        string $titulo,
        string $mensaje,
        string $tipo
    ): DtNotificacion {
        // Validar tipo de notificación
        if (!TipoNotificacionEnum::esValido($tipo)) {
            throw new Exception("Tipo de notificación inválido: {$tipo}");
        }

        $notificacion = Notificacion::create([
            'id_usuario' => $idUsuario,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'tipo' => $tipo,
            'leida' => false,
            'fecha' => now(),
        ]);

        return DtNotificacion::desdeModelo($notificacion);
    }

    /**
     * Obtiene una notificación por ID.
     */
    public function obtenerPorId(int $id): ?DtNotificacion
    {
        $notificacion = Notificacion::find($id);

        return $notificacion ? DtNotificacion::desdeModelo($notificacion) : null;
    }

    /**
     * Lista todas las notificaciones de un usuario.
     */
    public function listarPorUsuario(int $idUsuario): array
    {
        return Notificacion::where('id_usuario', $idUsuario)
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(fn($notificacion) => DtNotificacion::desdeModelo($notificacion))
            ->toArray();
    }

    /**
     * Lista notificaciones no leídas de un usuario.
     */
    public function listarNoLeidasPorUsuario(int $idUsuario): array
    {
        return Notificacion::where('id_usuario', $idUsuario)
            ->noLeidas()
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(fn($notificacion) => DtNotificacion::desdeModelo($notificacion))
            ->toArray();
    }

    /**
     * Lista notificaciones por tipo.
     */
    public function listarPorTipo(int $idUsuario, string $tipo): array
    {
        if (!TipoNotificacionEnum::esValido($tipo)) {
            throw new Exception("Tipo de notificación inválido: {$tipo}");
        }

        return Notificacion::where('id_usuario', $idUsuario)
            ->porTipo($tipo)
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(fn($notificacion) => DtNotificacion::desdeModelo($notificacion))
            ->toArray();
    }

    /**
     * Marca una notificación como leída.
     */
    public function marcarComoLeida(int $id): DtNotificacion
    {
        $notificacion = Notificacion::findOrFail($id);
        $notificacion->marcarComoLeida();

        return DtNotificacion::desdeModelo($notificacion->fresh());
    }

    /**
     * Marca todas las notificaciones de un usuario como leídas.
     */
    public function marcarTodasComoLeidas(int $idUsuario): int
    {
        return Notificacion::where('id_usuario', $idUsuario)
            ->noLeidas()
            ->update(['leida' => true]);
    }

    /**
     * Cuenta las notificaciones no leídas de un usuario.
     */
    public function contarNoLeidas(int $idUsuario): int
    {
        return Notificacion::where('id_usuario', $idUsuario)
            ->noLeidas()
            ->count();
    }

    /**
     * Elimina una notificación.
     */
    public function eliminar(int $id): bool
    {
        $notificacion = Notificacion::findOrFail($id);

        return $notificacion->delete();
    }

    /**
     * Elimina todas las notificaciones leídas de un usuario.
     */
    public function eliminarLeidasDelUsuario(int $idUsuario): int
    {
        return Notificacion::where('id_usuario', $idUsuario)
            ->where('leida', true)
            ->delete();
    }

    /**
     * Crea notificación de confirmación de reserva.
     */
    public function notificarConfirmacionReserva(int $idUsuario, int $idReserva, string $fechaHora): DtNotificacion
    {
        return $this->crear(
            $idUsuario,
            'Reserva Confirmada',
            "Tu reserva para el {$fechaHora} ha sido confirmada.",
            TipoNotificacionEnum::CONFIRMACION->value
        );
    }

    /**
     * Crea notificación de recordatorio de reserva.
     */
    public function notificarRecordatorioReserva(int $idUsuario, int $idReserva, string $fechaHora): DtNotificacion
    {
        return $this->crear(
            $idUsuario,
            'Recordatorio de Reserva',
            "Tienes una reserva programada para el {$fechaHora}.",
            TipoNotificacionEnum::RECORDATORIO->value
        );
    }

    /**
     * Crea notificación de cancelación de reserva.
     */
    public function notificarCancelacionReserva(int $idUsuario, int $idReserva, string $fechaHora): DtNotificacion
    {
        return $this->crear(
            $idUsuario,
            'Reserva Cancelada',
            "Tu reserva para el {$fechaHora} ha sido cancelada.",
            TipoNotificacionEnum::CANCELACION->value
        );
    }

    /**
     * Crea notificación de modificación de reserva.
     */
    public function notificarModificacionReserva(int $idUsuario, int $idReserva, string $fechaHoraAnterior, string $fechaHoraNueva): DtNotificacion
    {
        return $this->crear(
            $idUsuario,
            'Reserva Modificada',
            "Tu reserva del {$fechaHoraAnterior} ha sido reprogramada para el {$fechaHoraNueva}.",
            TipoNotificacionEnum::MODIFICACION->value
        );
    }

    /**
     * Envía notificaciones a múltiples usuarios.
     */
    public function enviarMasiva(array $idsUsuarios, string $titulo, string $mensaje, string $tipo): array
    {
        $notificaciones = [];

        foreach ($idsUsuarios as $idUsuario) {
            try {
                $notificaciones[] = $this->crear($idUsuario, $titulo, $mensaje, $tipo);
            } catch (Exception $e) {
                // Continuar con el siguiente usuario si hay error
                continue;
            }
        }

        return $notificaciones;
    }
}
