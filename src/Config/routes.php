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
        '/visits/create' => [VisitController::class, 'showCreateForm'],
    ],
    'POST' => [
        '/login'    => [AuthController::class, 'login'],
        '/register' => [AuthController::class, 'register'],
        '/visits'   => [VisitController::class, 'store'],
    ],
    // Rutas con parámetros
    'GET_PARAM' => [
        '/visits/{id}'        => [VisitController::class, 'show'],
        '/visits/{id}/edit'   => [VisitController::class, 'showEditForm'],
    ],
    'POST_PARAM' => [
        '/visits/{id}/edit'   => [VisitController::class, 'update'],
        '/visits/{id}/delete' => [VisitController::class, 'delete'],
    ],
];
