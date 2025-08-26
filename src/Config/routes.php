<?php

use JAM\VisitaSegura\Controller\AuthController;
use JAM\VisitaSegura\Controller\VisitController;
use JAM\VisitaSegura\Controller\VisitorController;
use JAM\VisitaSegura\Controller\NotificationController;

return [
    'GET' => [
        '/login'         => [AuthController::class, 'showLogin'],
        '/logout'        => [AuthController::class, 'logout'],
        '/panel'         => [AuthController::class, 'panel'],
        '/panel/empleado'     => [AuthController::class, 'panelEmpleado'],
        '/panel/recepcionista'=> [AuthController::class, 'panelRecepcionista'],
        '/register'      => [AuthController::class, 'showRegister'],
        '/visits'        => [VisitController::class, 'index'],
        '/visits/create' => [VisitController::class, 'showCreateForm'],
        '/visits/export' => [VisitController::class, 'export'],
        '/visitantes'    => [VisitorController::class, 'index'],
        '/visitantes/create' => [VisitorController::class, 'showCreateForm'],
        '/visitantes/{id}/edit' => [VisitorController::class, 'showEditForm'],
    '/notificaciones' => [NotificationController::class, 'index'],
    ],
    'POST' => [
        '/login'         => [AuthController::class, 'login'],
        '/register'      => [AuthController::class, 'register'],
        '/visits'        => [VisitController::class, 'store'],
        '/visitantes'    => [VisitorController::class, 'store'],
        '/panel'         => [AuthController::class, 'panel'], // <- necesario para manejar POST del panel
    '/notificaciones/leer-todas' => [NotificationController::class, 'markAllRead'],
    ],
    // Rutas con parámetros
    'GET_PARAM' => [
        '/visits/{id}'              => [VisitController::class, 'show'],
        '/visits/{id}/edit'         => [VisitController::class, 'showEditForm'],
        '/visits/{id}/authorize'    => [VisitController::class, 'showAuthorizeForm'],
        '/visits/{id}/exit'         => [VisitController::class, 'showExitForm'],
        '/visitantes/{id}'    => [VisitorController::class, 'show'],
    ],
    'POST_PARAM' => [
        '/visits/{id}/edit'      => [VisitController::class, 'update'],
        '/visits/{id}/delete'    => [VisitController::class, 'delete'],
        '/visits/{id}/authorize'  => [VisitController::class, 'authorize'],
        '/visits/{id}/exit'       => [VisitController::class, 'markExit'],
        '/visitantes/{id}/edit'  => [VisitorController::class, 'update'],
        '/visitantes/{id}/delete'=> [VisitorController::class, 'delete'],
    '/notificaciones/{id}/leer' => [NotificationController::class, 'markRead'],
    ],
];

