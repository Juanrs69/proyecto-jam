<?php

use JAM\VisitaSegura\Controller\AuthController;
use JAM\VisitaSegura\Controller\VisitController;

return [
    'GET' => [
        '/login'         => [AuthController::class, 'showLogin'],
        '/logout'        => [AuthController::class, 'logout'],
        '/panel'         => [AuthController::class, 'panel'],
        '/register'      => [AuthController::class, 'showRegister'],
        '/visits'        => [VisitController::class, 'index'],
        '/visits/create' => [VisitController::class, 'showCreateForm'], // NUEVA RUTA
        '/visits/{id}/edit' => [VisitController::class, 'showEditForm'], // NUEVA RUTA
    ],
    'POST' => [
        '/login'    => [AuthController::class, 'login'],
        '/register' => [AuthController::class, 'register'],
        '/visits'        => [VisitController::class, 'store'], // NUEVA RUTA
        '/visits/{id}/delete' => [VisitController::class, 'delete'], // NUEVA RUTA
        '/visits/{id}/edit'   => [VisitController::class, 'update'], // NUEVA RUTA
    ],
    // Rutas con parámetros
    'GET_PARAM' => [
        '/visits/{id}'    => [VisitController::class, 'show'], // NUEVA RUTA
    ],
];
