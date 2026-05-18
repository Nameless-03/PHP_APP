<?php

namespace App\Manejadores;

use App\Models\Pago;
use App\Models\Reserva;
use App\Models\CompraPaquete;
use App\DataTypes\DtPago;
use App\Enums\MetodoPagoEnum;
use App\Enums\EstadoPagoEnum;
use App\Enums\EstadoReservaEnum;
use Exception;

class ManejadorPago
{
    /**
     * Crea un pago para una reserva.
     */
    public function crearParaReserva(
        int $idReserva,
        float $monto,
        string $metodo,
        ?string $referenciaExterna = null
    ): DtPago {
        // Validar método de pago
        if (!MetodoPagoEnum::esValido($metodo)) {
            throw new Exception("Método de pago inválido: {$metodo}");
        }

        // Verificar que la reserva existe
        $reserva = Reserva::findOrFail($idReserva);

        // Verificar que no tenga ya un pago
        if ($reserva->pago) {
            throw new Exception("La reserva ya tiene un pago registrado");
        }

        $pago = Pago::create([
            'monto' => $monto,
            'fecha' => now(),
            'metodo' => $metodo,
            'estado' => EstadoPagoEnum::PENDIENTE->value,
            'referencia_externa' => $referenciaExterna,
            'id_reserva' => $idReserva,
            'id_compra' => null,
        ]);

        return DtPago::desdeModelo($pago);
    }

    /**
     * Crea un pago para una compra de paquete.
     */
    public function crearParaCompraPaquete(
        int $idCompra,
        float $monto,
        string $metodo,
        ?string $referenciaExterna = null
    ): DtPago {
        // Validar método de pago
        if (!MetodoPagoEnum::esValido($metodo)) {
            throw new Exception("Método de pago inválido: {$metodo}");
        }

        // Verificar que la compra existe
        CompraPaquete::findOrFail($idCompra);

        $pago = Pago::create([
            'monto' => $monto,
            'fecha' => now(),
            'metodo' => $metodo,
            'estado' => EstadoPagoEnum::PENDIENTE->value,
            'referencia_externa' => $referenciaExterna,
            'id_reserva' => null,
            'id_compra' => $idCompra,
        ]);

        return DtPago::desdeModelo($pago);
    }

    /**
     * Obtiene un pago por ID.
     */
    public function obtenerPorId(int $id): ?DtPago
    {
        $pago = Pago::find($id);

        return $pago ? DtPago::desdeModelo($pago) : null;
    }

    /**
     * Obtiene el pago de una reserva.
     */
    public function obtenerPorReserva(int $idReserva): ?DtPago
    {
        $pago = Pago::where('id_reserva', $idReserva)->first();

        return $pago ? DtPago::desdeModelo($pago) : null;
    }

    /**
     * Lista pagos por método.
     */
    public function listarPorMetodo(string $metodo): array
    {
        if (!MetodoPagoEnum::esValido($metodo)) {
            throw new Exception("Método de pago inválido: {$metodo}");
        }

        return Pago::porMetodo($metodo)
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(fn($pago) => DtPago::desdeModelo($pago))
            ->toArray();
    }

    /**
     * Lista pagos por estado.
     */
    public function listarPorEstado(string $estado): array
    {
        if (!EstadoPagoEnum::esValido($estado)) {
            throw new Exception("Estado de pago inválido: {$estado}");
        }

        return Pago::porEstado($estado)
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(fn($pago) => DtPago::desdeModelo($pago))
            ->toArray();
    }

    /**
     * Marca un pago como completado.
     */
    public function completar(int $id, ?string $referenciaExterna = null): DtPago
    {
        $pago = Pago::findOrFail($id);

        $estadoActual = EstadoPagoEnum::from($pago->estado);

        if ($estadoActual->estaCompletado()) {
            throw new Exception("El pago ya está completado");
        }

        $pago->update([
            'estado' => EstadoPagoEnum::COMPLETADO->value,
            'referencia_externa' => $referenciaExterna ?? $pago->referencia_externa,
        ]);

        // Si es pago de reserva, actualizar estado de la reserva
        if ($pago->id_reserva) {
            $reserva = Reserva::find($pago->id_reserva);
            if ($reserva && $reserva->estado === EstadoReservaEnum::CONFIRMADA->value) {
                $reserva->update(['estado' => EstadoReservaEnum::PAGADA->value]);
            }
        }

        return DtPago::desdeModelo($pago->fresh());
    }

    /**
     * Marca un pago como fallido.
     */
    public function marcarFallido(int $id): DtPago
    {
        $pago = Pago::findOrFail($id);

        $pago->update(['estado' => EstadoPagoEnum::FALLIDO->value]);

        return DtPago::desdeModelo($pago->fresh());
    }

    /**
     * Reembolsa un pago.
     */
    public function reembolsar(int $id): DtPago
    {
        $pago = Pago::findOrFail($id);

        $estadoActual = EstadoPagoEnum::from($pago->estado);

        if (!$estadoActual->puedeReembolsarse()) {
            throw new Exception("Este pago no puede ser reembolsado");
        }

        $pago->update(['estado' => EstadoPagoEnum::REEMBOLSADO->value]);

        // Si es pago de reserva, actualizar estado de la reserva
        if ($pago->id_reserva) {
            $reserva = Reserva::find($pago->id_reserva);
            if ($reserva) {
                $reserva->update(['estado' => EstadoReservaEnum::CANCELADA->value]);
            }
        }

        return DtPago::desdeModelo($pago->fresh());
    }

    /**
     * Actualiza la referencia externa de un pago.
     */
    public function actualizarReferenciaExterna(int $id, string $referenciaExterna): DtPago
    {
        $pago = Pago::findOrFail($id);

        $pago->update(['referencia_externa' => $referenciaExterna]);

        return DtPago::desdeModelo($pago->fresh());
    }

    /**
     * Procesa un pago con PayPal.
     */
    public function procesarConPayPal(int $id, string $paypalOrderId): DtPago
    {
        $pago = Pago::findOrFail($id);

        if ($pago->metodo !== MetodoPagoEnum::PAYPAL->value) {
            throw new Exception("Este pago no es de PayPal");
        }

        // Aquí iría la integración con PayPal SDK
        // Por ahora, simulamos el procesamiento exitoso

        return $this->completar($id, $paypalOrderId);
    }

    /**
     * Calcula el total de pagos completados en un rango de fechas.
     */
    public function calcularTotalPorPeriodo(string $fechaInicio, string $fechaFin): float
    {
        return Pago::where('estado', EstadoPagoEnum::COMPLETADO->value)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->sum('monto');
    }
}
