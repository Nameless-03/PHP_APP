<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Controlador de Sesión Predeterminado
    |--------------------------------------------------------------------------
    |
    | Esta opción determina el controlador de sesión predeterminado que se
    | utiliza para las solicitudes entrantes. Laravel soporta una variedad
    | de opciones de almacenamiento para persistir los datos de sesión.
    |
    | Soportados: "file", "cookie", "database", "memcached",
    |             "redis", "dynamodb", "array"
    |
    */

    'driver' => env('SESSION_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Tiempo de Vida de la Sesión
    |--------------------------------------------------------------------------
    |
    | Aquí puedes especificar el número de minutos que deseas que la sesión
    | pueda permanecer inactiva antes de que expire. Si deseas que expiren
    | inmediatamente cuando se cierra el navegador, puedes indicarlo
    | a través de la opción "expire_on_close".
    |
    */

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    /*
    |--------------------------------------------------------------------------
    | Encriptación de Sesiones
    |--------------------------------------------------------------------------
    |
    | Esta opción te permite especificar fácilmente que todos tus datos de
    | sesión deben ser encriptados antes de almacenarse. Toda la encriptación
    | es realizada automáticamente por Laravel y puedes usar la sesión normal.
    |
    */

    'encrypt' => env('SESSION_ENCRYPT', false),

    /*
    |--------------------------------------------------------------------------
    | Ubicación de los Archivos de Sesión
    |--------------------------------------------------------------------------
    |
    | Al usar el controlador de sesión nativo "file", necesitamos un lugar
    | para almacenar los archivos. Ya se ha definido un predeterminado
    | para ti, pero puedes especificar una ruta diferente aquí.
    |
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Conexión de Base de Datos para Sesiones
    |--------------------------------------------------------------------------
    |
    | Al usar los controladores "database" o "redis", puedes especificar la
    | conexión que se debe utilizar para gestionar las sesiones. De lo
    | contrario, se usará la conexión de base de datos predeterminada.
    |
    */

    'connection' => env('SESSION_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Tabla de Base de Datos para Sesiones
    |--------------------------------------------------------------------------
    |
    | Al usar el controlador de sesión "database", necesitas saber en qué
    | tabla almacenar las sesiones. Un nombre predeterminado se proporciona
    | abajo, pero eres libre de cambiar esto dentro de este archivo.
    |
    */

    'table' => env('SESSION_TABLE', 'sessions'),

    /*
    |--------------------------------------------------------------------------
    | Almacén de Sesiones en Caché
    |--------------------------------------------------------------------------
    |
    | Al usar uno de los back-ends de sesión impulsados por caché como
    | APC, memcached o Redis, los datos de la sesión se almacenarán en
    | el almacén de caché designado. Usa uno de los almacenes configurados.
    |
    */

    'store' => env('SESSION_STORE'),

    /*
    |--------------------------------------------------------------------------
    | Lotería de Barrido de Sesiones
    |--------------------------------------------------------------------------
    |
    | Algunos controladores de sesión deben vaciar manualmente sus ubicaciones
    | de almacenamiento para deshacerse de las sesiones antiguas. Aquí
    | están las probabilidades de que esto ocurra en una solicitud dada.
    | Por defecto, las probabilidades son de 2 sobre 100.
    |
    */

    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Nombre de la Cookie de Sesión
    |--------------------------------------------------------------------------
    |
    | Aquí puedes cambiar el nombre de la cookie utilizada para identificar un
    | ID de sesión de instancia por el framework. El nombre determinado
    | aquí es seguro para que la mayoría de aplicaciones lo utilicen.
    |
    */

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'laravel')).'-session'
    ),

    /*
    |--------------------------------------------------------------------------
    | Ruta de la Cookie de Sesión
    |--------------------------------------------------------------------------
    |
    | La ruta de la cookie de sesión determina el "path" para el cual
    | se considerará válida y disponible la cookie. Típicamente, esta
    | será la ruta principal, pero eres libre de cambiarlo cuando sea necesario.
    |
    */

    'path' => env('SESSION_PATH', '/'),

    /*
    |--------------------------------------------------------------------------
    | Dominio de la Cookie de Sesión
    |--------------------------------------------------------------------------
    |
    | Aquí puedes cambiar el dominio en el que se encuentra tu cookie
    | para que soporte subdominios o configuraciones similares.
    |
    */

    'domain' => env('SESSION_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Cookies de Sesión Seguras (HTTPS)
    |--------------------------------------------------------------------------
    |
    | Al configurar esta opción en true, las cookies de sesión solo se enviarán
    | al servidor si el navegador tiene una conexión HTTPS válida. Esto
    | evitará que se envíen cookies sobre conexiones inseguras (HTTP).
    |
    */

    'secure' => env('SESSION_SECURE_COOKIE'),

    /*
    |--------------------------------------------------------------------------
    | Acceso Solo HTTP (HttpOnly)
    |--------------------------------------------------------------------------
    |
    | Si estableces este valor en true, prevendrás que JavaScript obtenga
    | el valor de la cookie, lo que reduce efectivamente ciertos tipos de
    | ataques XSS para proteger contra robo de cookies de sesión.
    |
    */

    'http_only' => env('SESSION_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Cookies Same-Site
    |--------------------------------------------------------------------------
    |
    | Esta opción determina cómo se comportarán tus cookies cuando las
    | solicitudes provengan de otros sitios. (lax, strict, o none)
    |
    */

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    /*
    |--------------------------------------------------------------------------
    | Cookies Particionadas (Partitioned)
    |--------------------------------------------------------------------------
    |
    | Esta opción determina si las cookies de sesión están vinculadas a
    | un contexto superior. Muy útil al usar la cookie de sesión en un
    | contexto de un tercero, como incrustado en un iframe.
    |
    */

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];
