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

use App\Manejadores\ManejadorUsuario;
use App\Manejadores\ManejadorCliente;
use App\Manejadores\ManejadorProfesional;
use App\Manejadores\ManejadorServicio;
use App\Manejadores\ManejadorDisponibilidad;
use App\Manejadores\ManejadorReserva;
use App\Manejadores\ManejadorPaquete;
use App\Manejadores\ManejadorPago;
use App\Manejadores\ManejadorNotificacion;
use App\Models\ExcepcionAgenda;
use App\Models\Videollamada;
use App\Models\Calificacion;

class Sistema implements ISistema
{
    private ManejadorUsuario $mUsuario;
    private ManejadorCliente $mCliente;
    private ManejadorProfesional $mProfesional;
    private ManejadorServicio $mServicio;
    private ManejadorDisponibilidad $mDisponibilidad;
    private ManejadorReserva $mReserva;
    private ManejadorPaquete $mPaquete;
    private ManejadorPago $mPago;
    private ManejadorNotificacion $mNotificacion;

    public function __construct()
    {
        $this->mUsuario = new ManejadorUsuario();
        $this->mCliente = new ManejadorCliente();
        $this->mProfesional = new ManejadorProfesional();
        $this->mServicio = new ManejadorServicio();
        $this->mDisponibilidad = new ManejadorDisponibilidad();
        $this->mReserva = new ManejadorReserva();
        $this->mPaquete = new ManejadorPaquete();
        $this->mPago = new ManejadorPago();
        $this->mNotificacion = new ManejadorNotificacion();
    }

    // 1 - Registro Usuario
    public function registrarUsuario(string $nombre, string $email, string $password, string $role, array $datosAdicionales = []): DtUsuario
    {
        $dtUser = $this->mUsuario->crear($nombre, $email, $password, $role);
        
        if ($role === 'cliente') {
            $this->mCliente->registrar(
                $dtUser->id,
                $datosAdicionales['telefono'] ?? null,
                $datosAdicionales['foto_perfil'] ?? null
            );
        } elseif ($role === 'profesional') {
            $this->mProfesional->registrar(
                $dtUser->id,
                $datosAdicionales['descripcion'] ?? null,
                $datosAdicionales['experiencia'] ?? null,
                $datosAdicionales['ubicacion'] ?? null,
                $datosAdicionales['modalidad_preferida'] ?? 'presencial'
            );
        }
        
        return $dtUser;
    }

    // 2 - Iniciar Sesión
    public function iniciarSesion(string $email, string $password): ?DtUsuario
    {
        return $this->mUsuario->verificarCredenciales($email, $password);
    }

    // 3 - Gestión de Roles
    public function cambiarRolUsuario(int $idUsuario, string $nuevoRol): DtUsuario
    {
        return $this->mUsuario->actualizar($idUsuario, ['role' => $nuevoRol]);
    }

    // 4 - Gestión de Servicios
    public function crearServicio(string $nombre, float $precio, string $modalidad, int $duracion, int $idProfesional, int $idCategoria, ?string $descripcion = null, ?string $ubicacion = null, bool $activo = true): DtServicio
    {
        return $this->mServicio->crear($nombre, $precio, $modalidad, $duracion, $idProfesional, $idCategoria, $descripcion, $ubicacion, $activo);
    }

    public function actualizarServicio(int $idServicio, array $datos): DtServicio
    {
        return $this->mServicio->actualizar($idServicio, $datos);
    }

    // 5 - Crear y editar perfil profesional
    public function actualizarPerfilProfesional(int $idUsuario, array $datos): DtProfesional
    {
        return $this->mProfesional->actualizar($idUsuario, $datos);
    }

    // 6 - Configurar disponibilidad
    public function configurarDisponibilidad(int $idProfesional, string $diaSemana, string $horaInicio, string $horaFin, int $pausaMinutos = 0, int $bufferMinutos = 0): DtDisponibilidad
    {
        return $this->mDisponibilidad->crear($idProfesional, $diaSemana, $horaInicio, $horaFin, $pausaMinutos, $bufferMinutos);
    }

    // 7 - Definir reglas de agenda (Excepciones)
    public function definirReglaAgenda(int $idProfesional, string $fecha, bool $disponible, ?string $motivo = null): void
    {
        ExcepcionAgenda::updateOrCreate(
            ['id_profesional' => $idProfesional, 'fecha' => $fecha],
            ['disponible' => $disponible, 'motivo' => $motivo]
        );
    }

    // 8 - Buscar servicios
    public function buscarServicios(string $keyword): array
    {
        return $this->mServicio->buscarPorNombre($keyword);
    }

    // 9 - Filtrar servicios
    public function filtrarServicios(array $filtros): array
    {
        if (isset($filtros['precio_min']) && isset($filtros['precio_max'])) {
            return $this->mServicio->filtrarPorPrecio($filtros['precio_min'], $filtros['precio_max']);
        } elseif (isset($filtros['modalidad'])) {
            return $this->mServicio->listarPorModalidad($filtros['modalidad']);
        }
        return $this->mServicio->listarTodos();
    }

    // 10 - Reservar turno
    public function reservarTurno(int $idCliente, int $idServicio, string $fechaHoraInicio, string $fechaHoraFin, ?string $observaciones = null): DtReserva
    {
        return $this->mReserva->crear($idCliente, $idServicio, $fechaHoraInicio, $fechaHoraFin, $observaciones);
    }

    // 11 - Confirmar reserva
    public function confirmarReserva(int $idReserva): DtReserva
    {
        return $this->mReserva->confirmar($idReserva);
    }

    // 12 - Cancelar reserva
    public function cancelarReserva(int $idReserva): DtReserva
    {
        return $this->mReserva->cancelar($idReserva);
    }

    // 13 - Reprogramar reserva
    public function reprogramarReserva(int $idReserva, string $nuevaFechaHoraInicio, string $nuevaFechaHoraFin): DtReserva
    {
        return $this->mReserva->actualizar($idReserva, [
            'fecha_hora_inicio' => $nuevaFechaHoraInicio, 
            'fecha_hora_fin' => $nuevaFechaHoraFin
        ]);
    }

    // 14 - Visualizar agenda
    public function visualizarAgenda(int $idUsuario, string $rol): array
    {
        if ($rol === 'cliente') {
            return $this->mReserva->listarPorCliente($idUsuario);
        } elseif ($rol === 'profesional') {
            return $this->mReserva->listarPorProfesional($idUsuario);
        }
        return [];
    }

    // 15 - Crear paquetes de sesiones
    public function crearPaquete(string $nombre, int $cantidadSesiones, float $precio, int $idProfesional, ?int $vencimiento = null): DtPaquete
    {
        // Asumiendo que el manejador de paquete tiene un método crear similar
        return $this->mPaquete->crear($nombre, $cantidadSesiones, $precio, $idProfesional, $vencimiento);
    }

    // 16 - Comprar paquetes
    public function comprarPaquete(int $idCliente, int $idPaquete): void
    {
        // Asumiendo un método comprar en el manejador de paquete
        $this->mPaquete->comprar($idCliente, $idPaquete);
    }

    // 17 - Consumir sesiones del paquete
    public function consumirSesionPaquete(int $idCompraPaquete): void
    {
        // Asumiendo un método consumirSesion en el manejador de paquete
        $this->mPaquete->consumirSesion($idCompraPaquete);
    }

    // 18 - Pagar reservas
    public function pagarReserva(int $idReserva, float $monto, string $metodo, ?string $referenciaExterna = null): DtPago
    {
        $pago = $this->mPago->crearParaReserva($idReserva, $monto, $metodo, $referenciaExterna);
        $this->mReserva->cambiarEstado($idReserva, 'pagada');
        return $pago;
    }

    // 19 - Pagar paquetes
    public function pagarPaquete(int $idCompraPaquete, float $monto, string $metodo, ?string $referenciaExterna = null): DtPago
    {
        $pago = $this->mPago->crearParaCompraPaquete($idCompraPaquete, $monto, $metodo, $referenciaExterna);
        // Podría haber una lógica adicional para activar el paquete aquí
        return $pago;
    }

    // 20 - Generar sesión de videollamada
    public function generarSesionVideollamada(int $idReserva, string $enlace, string $token): void
    {
        Videollamada::create([
            'id_reserva' => $idReserva,
            'enlace' => $enlace,
            'token' => $token,
            'fecha_creacion' => now()
        ]);
    }

    // 21 - Unirse a videollamada
    public function unirseAVideollamada(int $idReserva): ?string
    {
        $videollamada = Videollamada::where('id_reserva', $idReserva)->first();
        return $videollamada ? $videollamada->enlace : null;
    }

    // 22 - Calificar servicio
    public function calificarServicio(int $idReserva, int $puntuacion, ?string $comentario = null): void
    {
        Calificacion::create([
            'id_reserva' => $idReserva,
            'puntuacion' => $puntuacion,
            'comentario' => $comentario,
            'fecha' => now()
        ]);
    }

    // 23 - Dejar comentario
    public function dejarComentario(int $idReserva, string $comentario): void
    {
        $this->calificarServicio($idReserva, 0, $comentario);
    }

    // 24 - Recibir confirmación de reserva
    public function enviarConfirmacionReserva(int $idReserva): DtNotificacion
    {
        $reserva = $this->mReserva->obtenerPorId($idReserva);
        return $this->mNotificacion->crear(
            $reserva->id_cliente, 
            'Reserva Confirmada', 
            'Tu reserva ha sido confirmada.', 
            'confirmacion'
        );
    }

    // 25 - Recibir recordatorios
    public function enviarRecordatorio(int $idReserva): DtNotificacion
    {
        $reserva = $this->mReserva->obtenerPorId($idReserva);
        return $this->mNotificacion->crear(
            $reserva->id_cliente, 
            'Recordatorio de Reserva', 
            'Recuerda tu próxima reserva.', 
            'recordatorio'
        );
    }

    // 26 - Notificación de cancelaciones/modificaciones
    public function enviarNotificacionCancelacion(int $idReserva): DtNotificacion
    {
        $reserva = $this->mReserva->obtenerPorId($idReserva);
        return $this->mNotificacion->crear(
            $reserva->id_cliente, 
            'Reserva Cancelada', 
            'Tu reserva ha sido cancelada.', 
            'cancelacion'
        );
    }

    // 27 - Visualizar ubicación en mapa
    public function visualizarUbicacionServicio(int $idServicio): ?string
    {
        $servicio = $this->mServicio->obtenerPorId($idServicio);
        return $servicio ? $servicio->ubicacion : null;
    }

    // 28 - Gestionar usuarios
    public function listarUsuarios(): array
    {
        return $this->mUsuario->listarTodos();
    }
    
    public function eliminarUsuario(int $idUsuario): bool
    {
        return $this->mUsuario->eliminar($idUsuario);
    }

    // 29 - Monitorear actividad del sistema
    public function monitorearActividad(): array
    {
        // Ejemplo simplificado: retornar las últimas notificaciones enviadas
        return $this->mNotificacion->listarRecientes(50);
    }

    // 30 - Ver métricas básicas
    public function verMetricasBasicas(): array
    {
        return [
            'usuarios' => count($this->mUsuario->listarTodos()),
            'servicios' => count($this->mServicio->listarTodos())
        ];
    }

    // 31 - Gestionar estados de reservas
    public function cambiarEstadoReserva(int $idReserva, string $nuevoEstado): DtReserva
    {
        return $this->mReserva->cambiarEstado($idReserva, $nuevoEstado);
    }
}