<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Nombre de la Aplicación
    |--------------------------------------------------------------------------
    |
    | Este valor es el nombre de tu aplicación, el cual será utilizado cuando el
    | framework necesite colocar el nombre de la aplicación en una notificación
    | u otros elementos de la interfaz donde deba mostrarse el nombre.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Entorno de la Aplicación
    |--------------------------------------------------------------------------
    |
    | Este valor determina el "entorno" en el que tu aplicación se está
    | ejecutando actualmente. Esto puede determinar cómo prefieres configurar
    | varios servicios que utiliza la aplicación. Configúralo en tu archivo ".env".
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Modo de Depuración de la Aplicación
    |--------------------------------------------------------------------------
    |
    | Cuando tu aplicación está en modo de depuración, se mostrarán mensajes
    | de error detallados con seguimientos de pila en cada error que ocurra.
    | Si está deshabilitado, se mostrará una página de error genérica simple.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | URL de la Aplicación
    |--------------------------------------------------------------------------
    |
    | Esta URL es utilizada por la consola para generar URLs correctamente
    | cuando se usa la herramienta de línea de comandos Artisan. Debes
    | configurarla a la raíz de la aplicación para que esté disponible.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Zona Horaria de la Aplicación
    |--------------------------------------------------------------------------
    |
    | Aquí puedes especificar la zona horaria predeterminada para tu aplicación,
    | la cual será utilizada por las funciones de fecha y hora de PHP. La zona
    | horaria está configurada en "UTC" por defecto ya que es adecuada para la mayoría.
    |
    */

    'timezone' => 'America/Montevideo',

    /*
    |--------------------------------------------------------------------------
    | Configuración de Idioma de la Aplicación
    |--------------------------------------------------------------------------
    |
    | El idioma de la aplicación determina el idioma predeterminado que será
    | utilizado por los métodos de traducción / localización de Laravel. Esta
    | opción puede configurarse a cualquier idioma que planees soportar.
    |
    */

    'locale' => env('APP_LOCALE', 'es'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'es'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Clave de Encriptación
    |--------------------------------------------------------------------------
    |
    | Esta clave es utilizada por los servicios de encriptación de Laravel y
    | debe establecerse en una cadena aleatoria de 32 caracteres para asegurar
    | que todos los valores encriptados estén seguros. Hazlo antes de desplegar.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Controlador del Modo de Mantenimiento
    |--------------------------------------------------------------------------
    |
    | Estas opciones determinan el controlador utilizado para determinar y
    | gestionar el estado de "modo de mantenimiento" de Laravel. El controlador
    | "cache" permite controlar el modo de mantenimiento en múltiples máquinas.
    |
    | Controladores soportados: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
