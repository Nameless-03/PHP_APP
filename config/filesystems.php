<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Disco de Archivos Predeterminado
    |--------------------------------------------------------------------------
    |
    | Aquí puedes especificar el disco de archivos predeterminado que debe
    | ser utilizado por el framework. El disco "local", así como una variedad
    | de discos basados en la nube, están disponibles para almacenar archivos.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Discos de Sistema de Archivos
    |--------------------------------------------------------------------------
    |
    | A continuación puedes configurar tantos discos de sistema de archivos
    | como sean necesarios, e incluso configurar múltiples discos para el mismo
    | controlador. Ejemplos de los más usados están configurados de referencia.
    |
    | Controladores soportados: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Enlaces Simbólicos
    |--------------------------------------------------------------------------
    |
    | Aquí puedes configurar los enlaces simbólicos que serán creados cuando
    | se ejecute el comando de Artisan `storage:link`. Las llaves deben ser las
    | ubicaciones de los enlaces y los valores deben ser sus destinos.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
