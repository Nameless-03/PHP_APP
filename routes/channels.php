<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.Usuario.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['api']]);

Broadcast::channel('admin-logs', function ($user) {
    return $user->esAdmin();
}, ['guards' => ['api']]);
