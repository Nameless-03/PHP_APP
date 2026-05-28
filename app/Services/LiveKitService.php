<?php

namespace App\Services;

class LiveKitService
{
    private string $apiKey;
    private string $apiSecret;
    private string $livekitUrl;

    public function __construct()
    {
        $this->apiKey = env('LIVEKIT_API_KEY', 'devkey');
        $this->apiSecret = env('LIVEKIT_API_SECRET', 'secret');
        $this->livekitUrl = env('LIVEKIT_URL', 'ws://localhost:7880');
    }

    /**
     * Generar un token de acceso JWT firmado nativamente en PHP para unirse a un cuarto de LiveKit.
     */
    public function generateToken(string $room, string $identity, string $name): string
    {
        $header = json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]);

        $now = time();
        $payload = json_encode([
            'iss' => $this->apiKey,
            'sub' => $identity,
            'nbf' => $now - 60, // margen de deriva de reloj
            'exp' => $now + (2 * 3600), // expira en 2 horas
            'video' => [
                'roomJoin' => true,
                'room' => $room,
                'name' => $name,
                'canPublish' => true,
                'canSubscribe' => true,
                'canPublishData' => true
            ]
        ]);

        // Base64Url encode del Header y Payload
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);

        // Generar Firma con HMAC-SHA256
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->apiSecret, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        // Token JWT completo
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Obtener la URL del servidor de LiveKit.
     */
    public function getLiveKitUrl(): string
    {
        return $this->livekitUrl;
    }

    /**
     * Helper para codificar en Base64 compatible con URLs.
     */
    private function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
