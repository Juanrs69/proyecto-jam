<?php

use JAM\VisitaSegura\Controller\AuthController;
use JAM\VisitaSegura\Controller\VisitController;
use JAM\VisitaSegura\Controller\VisitorController;

return [
    'GET' => [
        '/login'         => [AuthController::class, 'showLogin'],
        '/logout'        => [AuthController::class, 'logout'],
        '/panel'         => [AuthController::class, 'panel'],
        '/register'      => [AuthController::class, 'showRegister'],
        '/visits'        => [VisitController::class, 'index'],
        '/visits/create' => [VisitController::class, 'showCreateForm'],
        '/visitantes'           => [VisitorController::class, 'index'],
        '/visitantes/create'    => [VisitorController::class, 'showCreateForm'],
        '/visitantes/{id}/edit' => [VisitorController::class, 'showEditForm'],
    ],
    'POST' => [
        '/login'    => [AuthController::class, 'login'],
        '/register' => [AuthController::class, 'register'],
        '/visits'   => [VisitController::class, 'store'],
        '/visitantes'           => [VisitorController::class, 'store'],
    ],
    // Rutas con parámetros
    'GET_PARAM' => [
        '/visits/{id}'        => [VisitController::class, 'show'],
        '/visits/{id}/edit'   => [VisitController::class, 'showEditForm'],
        '/visitantes/{id}'      => [VisitorController::class, 'show'],
    ],
    'POST_PARAM' => [
        '/visits/{id}/edit'   => [VisitController::class, 'update'],
        '/visits/{id}/delete' => [VisitController::class, 'delete'],
        '/visitantes/{id}/edit'   => [VisitorController::class, 'update'],
        '/visitantes/{id}/delete' => [VisitorController::class, 'delete'],
    ],
];
