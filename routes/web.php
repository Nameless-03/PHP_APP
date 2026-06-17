<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Estas rutas sirven el index.html del build de Vue para todas las rutas
| del frontend (incluyendo /login, /forgot-password, /reset-password, etc).
| Esto permite que Vue Router funcione en modo history correctamente.
*/

$serveVue = function () {
    $distIndex = public_path('index.html');
    if (!file_exists($distIndex)) {
        $distIndex = base_path('vue-frontend/dist/index.html');
    }
    if (file_exists($distIndex)) {
        return response(file_get_contents($distIndex), 200)
            ->header('Content-Type', 'text/html');
    }
    abort(404, 'Frontend no encontrado. Ejecuta: npm run build en vue-frontend/');
};

Route::get('/', $serveVue);
Route::get('/{any}', $serveVue)->where('any', '^(?!api|storage|sanctum|broadcasting).*$');
