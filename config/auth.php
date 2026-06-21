<?php

use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Valores Predeterminados de Autenticación
    |--------------------------------------------------------------------------
    |
    | Esta opción define el "guard" de autenticación predeterminado y el
    | "broker" de restablecimiento de contraseña para tu aplicación. Puedes
    | cambiar estos valores según sea necesario, pero son un inicio perfecto
    | para la mayoría de las aplicaciones.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Guards de Autenticación
    |--------------------------------------------------------------------------
    |
    | A continuación, puedes definir todos los guards de autenticación para
    | tu aplicación. Por supuesto, se ha definido una excelente configuración
    | predeterminada para ti, la cual utiliza almacenamiento en sesión junto
    | con el proveedor de usuarios Eloquent.
    |
    | Todos los guards de autenticación tienen un proveedor de usuarios, que
    | define cómo se recuperan realmente los usuarios de tu base de datos u
    | otro sistema de almacenamiento. Típicamente, se utiliza Eloquent.
    |
    | Soportados: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'api' => [
            'driver' => 'sanctum',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Proveedores de Usuarios
    |--------------------------------------------------------------------------
    |
    | Todos los guards de autenticación tienen un proveedor de usuarios.
    | Esto define cómo se obtienen los usuarios de tu base de datos u
    | otro sistema de almacenamiento utilizado por la aplicación.
    |
    | Si tienes múltiples tablas o modelos de usuarios, puedes configurar
    | múltiples proveedores que representen el modelo o la tabla. Estos
    | proveedores pueden asignarse a cualquier guard adicional que definas.
    |
    | Soportados: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\Usuario::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Restablecimiento de Contraseñas
    |--------------------------------------------------------------------------
    |
    | Estas opciones especifican el comportamiento de la función de
    | restablecimiento de contraseña de Laravel, incluyendo la tabla utilizada
    | para almacenar tokens y el proveedor de usuarios invocado para recuperar
    | a los usuarios.
    |
    | El tiempo de expiración es el número de minutos que cada token de
    | restablecimiento será considerado válido. Esta medida de seguridad
    | mantiene los tokens de corta duración para que tengan menos tiempo de
    | ser adivinados. Puedes cambiar esto según sea necesario.
    |
    | La configuración de aceleración (throttle) es el número de segundos que
    | un usuario debe esperar antes de generar más tokens de restablecimiento.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tiempo de Espera para Confirmación de Contraseña
    |--------------------------------------------------------------------------
    |
    | Aquí puedes definir el número de segundos antes de que expire la ventana
    | de confirmación de contraseña y se pida a los usuarios que vuelvan a
    | ingresar su contraseña a través de la pantalla de confirmación.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
