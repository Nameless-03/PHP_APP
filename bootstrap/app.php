<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (\Throwable $e, $request) {
            try {
                // Registrar solo peticiones a la API para evitar spam de rutas web no encontradas
                if ($request->is('api/*')) {
                    $tipoError = 'error';
                    $accion = 'Acción Fallida (Error de Sistema)';
                    $detalles = [
                        'url' => $request->url(),
                        'method' => $request->method(),
                        'mensaje' => $e->getMessage(),
                    ];

                    $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                    if ($e instanceof \Illuminate\Validation\ValidationException) {
                        $tipoError = 'warning';
                        $accion = 'Acción Fallida (Error de Validación)';
                        $statusCode = 422;
                        $detalles['errores'] = $e->errors();
                        $detalles['inputs'] = $request->except(['password', 'password_confirmation', 'token']);
                    } elseif ($e instanceof \Illuminate\Auth\Access\AuthorizationException || $e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
                        $tipoError = 'warning';
                        $accion = 'Acción Fallida (No Autorizado)';
                        $statusCode = 403;
                    } elseif ($e instanceof \Illuminate\Auth\AuthenticationException) {
                        $tipoError = 'warning';
                        $accion = 'Acción Fallida (No Autenticado)';
                        $statusCode = 401;
                    } elseif ($statusCode === 404) {
                        $tipoError = 'warning';
                        $accion = 'Acción Fallida (Recurso no encontrado)';
                    }

                    // Registrar si es un error generado por el cliente (4xx) o de servidor (5xx)
                    if ($statusCode >= 400) {
                        $logger = app(\App\Services\NoSqlLoggerService::class);
                        $logger->log($accion, $tipoError, $detalles);
                    }
                }
            } catch (\Throwable $th) {
                // Ignorar si falla el logger para no afectar el flujo original
            }
        });
    })->create();
