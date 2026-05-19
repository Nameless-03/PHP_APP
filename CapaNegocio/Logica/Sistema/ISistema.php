<?php

namespace App\Sistema;

use App\DataTypes\DtUsuario;
use App\DataTypes\DtCliente;
use App\DataTypes\DtProfesional;
use App\DataTypes\DtServicio;
use App\DataTypes\DtReserva;
use App\DataTypes\DtPaquete;
use App\DataTypes\DtPago;
use App\DataTypes\DtNotificacion;
use App\DataTypes\DtDisponibilidad;

interface ISistema
{
    // 1 - Registro Usuario
    public function registrarUsuario(string $nombre, string $email, string $password, string $role, array $datosAdicionales = []): DtUsuario;

    // 2 - Iniciar Sesión
    public function iniciarSesion(string $email, string $password): ?DtUsuario;

    // 3 - Gestión de Roles
    public function cambiarRolUsuario(int $idUsuario, string $nuevoRol): DtUsuario;

    // 4 - Gestión de Servicios
    public function crearServicio(string $nombre, float $precio, string $modalidad, int $duracion, int $idProfesional, int $idCategoria, ?string $descripcion = null, ?string $ubicacion = null, bool $activo = true): DtServicio;
    public function actualizarServicio(int $idServicio, array $datos): DtServicio;

    // 5 - Crear y editar perfil profesional
    public function actualizarPerfilProfesional(int $idUsuario, array $datos): DtProfesional;

    // 6 - Configurar disponibilidad
    public function configurarDisponibilidad(int $idProfesional, string $diaSemana, string $horaInicio, string $horaFin, int $pausaMinutos = 0, int $bufferMinutos = 0): DtDisponibilidad;

    // 7 - Definir reglas de agenda (Excepciones)
    public function definirReglaAgenda(int $idProfesional, string $fecha, bool $disponible, ?string $motivo = null): void;

    // 8 - Buscar servicios
    public function buscarServicios(string $keyword): array;

    // 9 - Filtrar servicios
    public function filtrarServicios(array $filtros): array;

    // 10 - Reservar turno
    public function reservarTurno(int $idCliente, int $idServicio, string $fechaHoraInicio, string $fechaHoraFin, ?string $observaciones = null): DtReserva;

    // 11 - Confirmar reserva
    public function confirmarReserva(int $idReserva): DtReserva;

    // 12 - Cancelar reserva
    public function cancelarReserva(int $idReserva): DtReserva;

    // 13 - Reprogramar reserva
    public function reprogramarReserva(int $idReserva, string $nuevaFechaHoraInicio, string $nuevaFechaHoraFin): DtReserva;

    // 14 - Visualizar agenda
    public function visualizarAgenda(int $idUsuario, string $rol): array;

    // 15 - Crear paquetes de sesiones
    public function crearPaquete(string $nombre, int $cantidadSesiones, float $precio, int $idProfesional, ?int $vencimiento = null): DtPaquete;

    // 16 - Comprar paquetes
    public function comprarPaquete(int $idCliente, int $idPaquete): void;

    // 17 - Consumir sesiones del paquete
    public function consumirSesionPaquete(int $idCompraPaquete): void;

    // 18 - Pagar reservas
    public function pagarReserva(int $idReserva, float $monto, string $metodo, ?string $referenciaExterna = null): DtPago;

    // 19 - Pagar paquetes
    public function pagarPaquete(int $idCompraPaquete, float $monto, string $metodo, ?string $referenciaExterna = null): DtPago;

    // 20 - Generar sesión de videollamada
    public function generarSesionVideollamada(int $idReserva, string $enlace, string $token): void;

    // 21 - Unirse a videollamada
    public function unirseAVideollamada(int $idReserva): ?string;

    // 22 - Calificar servicio
    public function calificarServicio(int $idReserva, int $puntuacion, ?string $comentario = null): void;

    // 23 - Dejar comentario
    // Nota: Está cubierto junto con calificarServicio, o puede ser un método separado
    public function dejarComentario(int $idReserva, string $comentario): void;

    // 24 - Recibir confirmación de reserva
    public function enviarConfirmacionReserva(int $idReserva): DtNotificacion;

    // 25 - Recibir recordatorios
    public function enviarRecordatorio(int $idReserva): DtNotificacion;

    // 26 - Notificación de cancelaciones/modificaciones
    public function enviarNotificacionCancelacion(int $idReserva): DtNotificacion;

    // 27 - Visualizar ubicación en mapa
    public function visualizarUbicacionServicio(int $idServicio): ?string;

    // 28 - Gestionar usuarios
    public function listarUsuarios(): array;
    public function eliminarUsuario(int $idUsuario): bool;

    // 29 - Monitorear actividad del sistema
    public function monitorearActividad(): array;

    // 30 - Ver métricas básicas
    public function verMetricasBasicas(): array;

    // 31 - Gestionar estados de reservas
    public function cambiarEstadoReserva(int $idReserva, string $nuevoEstado): DtReserva;
}