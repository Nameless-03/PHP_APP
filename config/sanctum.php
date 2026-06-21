<?php

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Laravel\Sanctum\Http\Middleware\AuthenticateSession;
use Laravel\Sanctum\Sanctum;

return [

    /*
    |--------------------------------------------------------------------------
    | Dominios con Estado (Stateful Domains)
    |--------------------------------------------------------------------------
    |
    | Las solicitudes desde los siguientes dominios / hosts recibirán cookies
    | de autenticación API con estado. Típicamente, estos deben incluir tus
    | dominios locales y de producción que acceden a tu API vía frontend SPA.
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        Sanctum::currentApplicationUrlWithPort(),
        // Sanctum::currentRequestHost(),
    ))),

    /*
    |--------------------------------------------------------------------------
    | Guards de Sanctum
    |--------------------------------------------------------------------------
    |
    | Este arreglo contiene los guards de autenticación que serán verificados
    | cuando Sanctum intente autenticar una solicitud. Si ninguno de estos
    | guards puede autenticar la solicitud, Sanctum utilizará el token bearer
    | que esté presente en la solicitud entrante para la autenticación.
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Minutos de Expiración
    |--------------------------------------------------------------------------
    |
    | Este valor controla el número de minutos hasta que un token emitido sea
    | considerado como expirado. Esto anulará cualquier valor establecido en
    | el atributo "expires_at" del token, pero no afectará sesiones first-party.
    |
    */

    'expiration' => null,

    /*
    |--------------------------------------------------------------------------
    | Prefijo del Token
    |--------------------------------------------------------------------------
    |
    | Sanctum puede agregar un prefijo a los nuevos tokens para aprovechar
    | las numerosas iniciativas de escaneo de seguridad mantenidas por
    | plataformas de código abierto que notifican a los desarrolladores
    | si comprometen tokens en los repositorios.
    |
    */

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Middleware de Sanctum
    |--------------------------------------------------------------------------
    |
    | Al autenticar tu SPA de primera parte con Sanctum, es posible que
    | necesites personalizar algunos de los middleware que Sanctum utiliza
    | al procesar la solicitud. Puedes cambiarlos a continuación según sea necesario.
    |
    */

    'middleware' => [
        'authenticate_session' => AuthenticateSession::class,
        'encrypt_cookies' => EncryptCookies::class,
        'validate_csrf_token' => ValidateCsrfToken::class,
    ],

];
