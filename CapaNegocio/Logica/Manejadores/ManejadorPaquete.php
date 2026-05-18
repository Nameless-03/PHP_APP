<?php

namespace App\Manejadores;

use App\Models\Paquete;
use App\Models\CompraPaquete;
use App\DataTypes\DtPaquete;
use App\Enums\EstadoCompraPaqueteEnum;
use Illuminate\Support\Facades\DB;
use Exception;

class ManejadorPaquete
{
    /**
     * Crea un nuevo paquete.
     */
    public function crear(
        int $idProfesional,
        string $nombre,
        int $cantidadSesiones,
        float $precio,
        ?int $vencimiento = null
    ): DtPaquete {
        if ($cantidadSesiones <= 0) {
            throw new Exception("La cantidad de sesiones debe ser mayor a 0");
        }

        if ($precio <= 0) {
            throw new Exception("El precio debe ser mayor a 0");
        }

        $paquete = Paquete::create([
            'nombre' => $nombre,
            'cantidad_sesiones' => $cantidadSesiones,
            'precio' => $precio,
            'vencimiento' => $vencimiento,
            'id_profesional' => $idProfesional,
        ]);

        $paquete->load('profesional.usuario');

        return DtPaquete::desdeModelo($paquete);
    }

    /**
     * Obtiene un paquete por ID.
     */
    public function obtenerPorId(int $id): ?DtPaquete
    {
        $paquete = Paquete::with('profesional.usuario')->find($id);

        return $paquete ? DtPaquete::desdeModelo($paquete) : null;
    }

    /**
     * Lista todos los paquetes.
     */
    public function listarTodos(): array
    {
        return Paquete::with('profesional.usuario')
            ->get()
            ->map(fn($paquete) => DtPaquete::desdeModelo($paquete))
            ->toArray();
    }

    /**
     * Lista paquetes de un profesional.
     */
    public function listarPorProfesional(int $idProfesional): array
    {
        return Paquete::with('profesional.usuario')
            ->where('id_profesional', $idProfesional)
            ->get()
            ->map(fn($paquete) => DtPaquete::desdeModelo($paquete))
            ->toArray();
    }

    /**
     * Actualiza un paquete.
     */
    public function actualizar(int $id, array $datos): DtPaquete
    {
        $paquete = Paquete::with('profesional.usuario')->findOrFail($id);

        if (isset($datos['cantidad_sesiones']) && $datos['cantidad_sesiones'] <= 0) {
            throw new Exception("La cantidad de sesiones debe ser mayor a 0");
        }

        if (isset($datos['precio']) && $datos['precio'] <= 0) {
            throw new Exception("El precio debe ser mayor a 0");
        }

        $paquete->update($datos);

        return DtPaquete::desdeModelo($paquete->fresh('profesional.usuario'));
    }

    /**
     * Elimina un paquete.
     */
    public function eliminar(int $id): bool
    {
        $paquete = Paquete::findOrFail($id);

        // Verificar que no tenga compras activas
        $tieneComprasActivas = CompraPaquete::where('id_paquete', $id)
            ->where('estado', EstadoCompraPaqueteEnum::ACTIVO->value)
            ->exists();

        if ($tieneComprasActivas) {
            throw new Exception("No se puede eliminar un paquete con compras activas");
        }

        return $paquete->delete();
    }

    /**
     * Compra un paquete.
     */
    public function comprar(int $idPaquete, int $idCliente): array
    {
        $paquete = Paquete::findOrFail($idPaquete);

        return DB::transaction(function () use ($paquete, $idCliente) {
            // Crear la compra
            $compra = CompraPaquete::create([
                'id_paquete' => $paquete->id,
                'id_cliente' => $idCliente,
                'sesiones_disponibles' => $paquete->cantidad_sesiones,
                'fecha_compra' => now(),
                'estado' => EstadoCompraPaqueteEnum::ACTIVO->value,
            ]);

            return [
                'compra' => $compra,
                'paquete' => $paquete,
            ];
        });
    }

    /**
     * Usa una sesión de un paquete comprado.
     */
    public function usarSesion(int $idCompra): bool
    {
        $compra = CompraPaquete::findOrFail($idCompra);

        if ($compra->estado !== EstadoCompraPaqueteEnum::ACTIVO->value) {
            throw new Exception("El paquete no está activo");
        }

        if ($compra->sesiones_disponibles <= 0) {
            throw new Exception("No quedan sesiones disponibles");
        }

        return DB::transaction(function () use ($compra) {
            $compra->decrement('sesiones_disponibles');

            // Si se agotaron las sesiones, cambiar estado
            if ($compra->sesiones_disponibles <= 0) {
                $compra->update(['estado' => EstadoCompraPaqueteEnum::AGOTADO->value]);
            }

            return true;
        });
    }

    /**
     * Verifica si un paquete comprado está vencido.
     */
    public function verificarVencimiento(int $idCompra): bool
    {
        $compra = CompraPaquete::with('paquete')->findOrFail($idCompra);

        if (!$compra->paquete->vencimiento) {
            return false; // No tiene vencimiento
        }

        $fechaVencimiento = $compra->fecha_compra->addDays($compra->paquete->vencimiento);
        $estaVencido = now()->greaterThan($fechaVencimiento);

        if ($estaVencido && $compra->estado === EstadoCompraPaqueteEnum::ACTIVO->value) {
            $compra->update(['estado' => EstadoCompraPaqueteEnum::VENCIDO->value]);
        }

        return $estaVencido;
    }

    /**
     * Obtiene estadísticas de un paquete.
     */
    public function obtenerEstadisticas(int $idPaquete): array
    {
        $paquete = Paquete::findOrFail($idPaquete);

        $compras = CompraPaquete::where('id_paquete', $idPaquete);

        return [
            'total_compras' => $compras->count(),
            'compras_activas' => $compras->where('estado', EstadoCompraPaqueteEnum::ACTIVO->value)->count(),
            'compras_agotadas' => $compras->where('estado', EstadoCompraPaqueteEnum::AGOTADO->value)->count(),
            'compras_vencidas' => $compras->where('estado', EstadoCompraPaqueteEnum::VENCIDO->value)->count(),
            'ingreso_total' => $compras->count() * $paquete->precio,
        ];
    }

    /**
     * Calcula el precio por sesión de un paquete.
     */
    public function calcularPrecioPorSesion(int $idPaquete): float
    {
        $paquete = Paquete::findOrFail($idPaquete);

        return $paquete->precio / $paquete->cantidad_sesiones;
    }

    /**
     * Lista paquetes comprados por un cliente.
     */
    public function listarComprasPorCliente(int $idCliente): array
    {
        return CompraPaquete::with('paquete.profesional.usuario')
            ->where('id_cliente', $idCliente)
            ->orderBy('fecha_compra', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Lista paquetes activos de un cliente.
     */
    public function listarComprasActivasPorCliente(int $idCliente): array
    {
        return CompraPaquete::with('paquete.profesional.usuario')
            ->where('id_cliente', $idCliente)
            ->activos()
            ->conSesiones()
            ->orderBy('fecha_compra', 'desc')
            ->get()
            ->toArray();
    }
}
