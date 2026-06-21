<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class NoSqlLoggerService
{
    private string $driver;

    public function __construct()
    {
        $this->driver = config('services.nosql.driver') ?? 'redis';
    }

    /**
     * Registrar la actividad del sistema en NoSQL.
     */
    public function log(string $accion, string $tipo = 'info', array $detalles = [], ?int $usuarioId = null): void
    {
        $logData = [
            'timestamp' => now()->toIso8601String(),
            'accion' => $accion,
            'tipo' => $tipo,
            'detalles' => $detalles,
            'usuario_id' => $usuarioId ?? (auth()->check() ? auth()->id() : null),
            'ip' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'CLI/System',
        ];

        try {
            if ($this->driver === 'redis') {
                // Almacenamiento en Redis NoSQL (usa una lista y se limita a los últimos 1000 elementos)
                Redis::rpush('nosql_system_logs', json_encode($logData));
                Redis::ltrim('nosql_system_logs', -1000, -1);
            } elseif ($this->driver === 'mongodb') {
                $host = config('services.nosql.mongodb.host') ?? 'mongodb';
                $port = config('services.nosql.mongodb.port') ?? '27017';
                $dbName = config('services.nosql.mongodb.database') ?? 'laravel_logs';
                $username = config('services.nosql.mongodb.username') ?? 'admin';
                $password = config('services.nosql.mongodb.password') ?? 'secret';

                if (class_exists(\MongoDB\Client::class)) {
                    $uri = "mongodb://{$username}:{$password}@{$host}:{$port}/?authSource=admin";
                    $client = new \MongoDB\Client($uri);
                    $collection = $client->selectDatabase($dbName)->selectCollection('activity_logs');
                    $collection->insertOne($logData);
                } else {
                    // Fallback a Redis ya que la extensión no está cargada en PHP CLI/Local
                    Log::warning('MongoDB PHP driver not found. Falling back to Redis NoSQL for: ' . json_encode($logData));
                    Redis::rpush('nosql_system_logs', json_encode($logData));
                    Redis::ltrim('nosql_system_logs', -1000, -1);
                }
            } else {
                Log::info('NoSQL Log (Fallback to file): ' . json_encode($logData));
            }
        } catch (\Exception $e) {
            // Nunca interrumpir la ejecución de la petición principal si falla el logger
            Log::error('NoSQL Logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Obtener registros recientes.
     */
    public function getLogs(int $limit = 50): array
    {
        try {
            if ($this->driver === 'redis') {
                $rawLogs = Redis::lrange('nosql_system_logs', -$limit, -1);
                if (empty($rawLogs)) {
                    return [];
                }
                $logs = array_map(fn($item) => json_decode($item, true), $rawLogs);
                return array_reverse($logs); // Más recientes primero
            } elseif ($this->driver === 'mongodb') {
                $host = config('services.nosql.mongodb.host') ?? 'mongodb';
                $port = config('services.nosql.mongodb.port') ?? '27017';
                $dbName = config('services.nosql.mongodb.database') ?? 'laravel_logs';
                $username = config('services.nosql.mongodb.username') ?? 'admin';
                $password = config('services.nosql.mongodb.password') ?? 'secret';

                if (class_exists(\MongoDB\Client::class)) {
                    $uri = "mongodb://{$username}:{$password}@{$host}:{$port}/?authSource=admin";
                    $client = new \MongoDB\Client($uri);
                    $collection = $client->selectDatabase($dbName)->selectCollection('activity_logs');
                    
                    $cursor = $collection->find([], [
                        'limit' => $limit,
                        'sort' => ['timestamp' => -1]
                    ]);
                    
                    $logs = [];
                    foreach ($cursor as $document) {
                        $log = (array)$document;
                        if (isset($log['_id'])) {
                            $log['_id'] = (string)$log['_id'];
                        }
                        $logs[] = $log;
                    }
                    return $logs;
                } else {
                    // Fallback de lectura desde Redis
                    $rawLogs = Redis::lrange('nosql_system_logs', -$limit, -1);
                    if (empty($rawLogs)) {
                        return [];
                    }
                    $logs = array_map(fn($item) => json_decode($item, true), $rawLogs);
                    return array_reverse($logs);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to retrieve NoSQL logs: ' . $e->getMessage());
        }

        return [];
    }
}
