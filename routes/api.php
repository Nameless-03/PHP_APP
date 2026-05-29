<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\ServicioController;
use App\Http\Controllers\Api\DisponibilidadController;
use App\Http\Controllers\Api\ExcepcionAgendaController;
use App\Http\Controllers\Api\ReservaController;
use App\Http\Controllers\Api\PagoController;
use App\Http\Controllers\Api\TurnosDisponiblesController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\PaqueteController;
use App\Http\Controllers\Api\CompraPaqueteController;
use App\Http\Controllers\Api\CalificacionController;
use App\Http\Controllers\Api\NotificacionController;
use App\Http\Controllers\Api\VideollamadaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register/cliente', [AuthController::class, 'registerCliente']);
    Route::post('/register/profesional', [AuthController::class, 'registerProfesional']);
    
    // Google OAuth
    Route::get('/google/redirect', [AuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/password', [AuthController::class, 'changePassword']);

        // Notificaciones
        Route::get('/notificaciones', [NotificacionController::class, 'index']);
        Route::patch('/notificaciones/{id}/marcar-leida', [NotificacionController::class, 'marcarLeida']);
    });
});

Route::prefix('usuarios')->middleware('auth:sanctum')->group(function () {
    // Solo admins pueden listar y eliminar usuarios
    Route::middleware('role:admin')->group(function () {
        Route::get('/', [UsuarioController::class, 'index']);
        Route::delete('/{id}', [UsuarioController::class, 'destroy']);
    });
    
    // Un usuario puede ver y actualizar su propio perfil
    Route::get('/{id}', [UsuarioController::class, 'show']);
    Route::put('/{id}', [UsuarioController::class, 'update']);
});

// Categorías
Route::get('/categorias', [CategoriaController::class, 'index']);

// Catálogo de Servicios (Público para ver, protegido para crear/editar)
Route::prefix('servicios')->group(function () {
    Route::get('/', [ServicioController::class, 'index']);
    Route::get('/{servicio}/turnos', [TurnosDisponiblesController::class, 'index']);
    Route::get('/{id}', [ServicioController::class, 'show']);

    Route::middleware(['auth:sanctum', 'role:profesional'])->group(function () {
        Route::post('/', [ServicioController::class, 'store']);
        Route::put('/{servicio}', [ServicioController::class, 'update']);
        Route::delete('/{servicio}', [ServicioController::class, 'destroy']);
    });
});

// Disponibilidad (Protegido)
Route::prefix('disponibilidad')->group(function () {
    Route::get('/{idProfesional}', [DisponibilidadController::class, 'index']);

    Route::middleware(['auth:sanctum', 'role:profesional'])->group(function () {
        Route::post('/', [DisponibilidadController::class, 'store']);
        Route::put('/{disponibilidad}', [DisponibilidadController::class, 'update']);
        Route::delete('/{disponibilidad}', [DisponibilidadController::class, 'destroy']);
    });
});

// Reglas de Agenda / Excepciones (Protegido)
Route::prefix('excepciones-agenda')->group(function () {
    Route::get('/{idProfesional}', [ExcepcionAgendaController::class, 'index']);

    Route::middleware(['auth:sanctum', 'role:profesional'])->group(function () {
        Route::post('/', [ExcepcionAgendaController::class, 'store']);
        Route::delete('/{excepcion_agenda}', [ExcepcionAgendaController::class, 'destroy']);
    });
});

// Reservas
Route::prefix('reservas')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ReservaController::class, 'index']);
    Route::get('/{reserva}', [ReservaController::class, 'show']);
    
    // Solo clientes pueden crear
    Route::middleware('role:cliente')->group(function () {
        Route::post('/', [ReservaController::class, 'store']);
    });

    // Cambiar estado (confirmar, cancelar, etc)
    Route::patch('/{reserva}/estado', [ReservaController::class, 'updateEstado']);
    Route::patch('/{reserva}/reprogramar', [ReservaController::class, 'reprogramar']);

    // Calificar reserva finalizada
    Route::post('/{reserva}/calificar', [CalificacionController::class, 'calificar']);

    // LiveKit Token
    Route::get('/{id}/videollamada/token', [VideollamadaController::class, 'getToken']);
});

// Pagos
Route::prefix('pagos')->middleware('auth:sanctum')->group(function () {
    Route::middleware('role:cliente')->group(function () {
        Route::post('/', [PagoController::class, 'store']);
    });
});

// Paquetes de Sesiones
Route::get('/paquetes', [PaqueteController::class, 'index']);
Route::prefix('paquetes')->middleware('auth:sanctum')->group(function () {
    Route::post('/', [PaqueteController::class, 'store'])->middleware('role:profesional');
    Route::delete('/{paquete}', [PaqueteController::class, 'destroy'])->middleware('role:profesional');
    Route::post('/{paquete}/comprar', [CompraPaqueteController::class, 'comprar'])->middleware('role:cliente');
});
Route::get('/mis-paquetes', [CompraPaqueteController::class, 'misPaquetes'])->middleware(['auth:sanctum', 'role:cliente']);
Route::delete('/mis-paquetes/{compraPaquete}', [CompraPaqueteController::class, 'destroy'])->middleware(['auth:sanctum', 'role:cliente']);

// Admin Dashboard Stats
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/stats', [UsuarioController::class, 'dashboardStats']);
    Route::get('/logs', [UsuarioController::class, 'systemLogs']);
});

// PayPal Configuration Endpoint (Public/Authenticated)
Route::middleware('auth:sanctum')->get('/config/paypal', function () {
    return response()->json([
        'client_id' => config('services.paypal.client_id'),
        'mode' => config('services.paypal.mode'),
    ]);
});
