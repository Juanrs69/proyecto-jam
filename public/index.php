<?php declare(strict_types=1);

// public/index.php - Front controller robusto para VisitaSegura
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Helper: escribir errores en el log de Apache también
function log_error($msg) {
    error_log("[VisitaSegura] " . $msg);
}

// 1) Cargar DB y rutas (si existen)
$dbFile = __DIR__ . '/../src/Config/database.php';
$routesFile = __DIR__ . '/../src/Config/routes.php';

if (!file_exists($dbFile)) {
    header('HTTP/1.1 500 Internal Server Error');
    echo "<h1>Error: falta database.php</h1><p>Ruta esperada: {$dbFile}</p>";
    log_error("Falta archivo: {$dbFile}");
    exit;
}
if (!file_exists($routesFile)) {
    header('HTTP/1.1 500 Internal Server Error');
    echo "<h1>Error: falta routes.php</h1><p>Ruta esperada: {$routesFile}</p>";
    log_error("Falta archivo: {$routesFile}");
    exit;
}

$pdo = require $dbFile;
$routes = require $routesFile;
if (!is_array($routes)) {
    header('HTTP/1.1 500 Internal Server Error');
    echo "<h1>Error: routes.php no devolvió un array válido</h1>";
    log_error("routes.php no devolvió array");
    exit;
}

// 2) Autoloader mínimo por convención de nombres
spl_autoload_register(function($class) {
    // Esperamos namespace JAM\VisitaSegura\Controller\NameController
    $prefix = 'JAM\\VisitaSegura\\';
    if (strpos($class, $prefix) === 0) {
        $relative = substr($class, strlen($prefix)); // Controller\AuthController
        $path = __DIR__ . '/../src/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($path)) {
            require_once $path;
            return true;
        }
    }
    // fallback: intentar bajo src/Controller/<ClassName>.php
    $parts = explode('\\', $class);
    $short = end($parts);
    $alt = __DIR__ . '/../src/Controller/' . $short . '.php';
    if (file_exists($alt)) {
        require_once $alt;
        return true;
    }
    return false;
});

// 3) Obtener método y URI
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$rawUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

// 4) Detectar basePath dinámico (soporta /visita-segura/public o /visita-segura)
$scriptDir = str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])); // e.g. /visita-segura/public
$basePath = $scriptDir !== '/' ? rtrim($scriptDir, '/') : '';
$uri = $rawUri;
if ($basePath !== '' && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}
$uri = '/' . ltrim($uri, '/');
$uri = rtrim($uri, '/');
if ($uri === '') $uri = '/';

// Guardar para vistas si hace falta
$GLOBALS['basePath'] = $basePath;

// 5) Dispatcher con validaciones y soporte para rutas con parámetros
$dispatch = function(array $route, array $params = []) use ($pdo) {
    [$class, $action] = $route;
    if (!class_exists($class)) {
        return [
            'code' => 500,
            'body' => "<h1>Error interno</h1><p>Clase no encontrada: " . htmlspecialchars($class) . "</p>"
        ];
    }
    $controller = new $class($pdo);
    if (!method_exists($controller, $action)) {
        return [
            'code' => 404,
            'body' => "<h1>404</h1><p>Método no encontrado: " . htmlspecialchars($action) . "</p>"
        ];
    }
    // Llamar y devolver string o response
    try {
        $result = call_user_func_array([$controller, $action], $params);
        return ['code' => 200, 'body' => $result];
    } catch (Throwable $t) {
        log_error("Excepción en {$class}::{$action} -> " . $t->getMessage());
        return ['code' => 500, 'body' => "<h1>Error interno</h1><p>" . htmlspecialchars($t->getMessage()) . "</p>"];
    }
};

// 6) 1) Intento match exacto (GET/POST)
if (isset($routes[$method]) && isset($routes[$method][$uri])) {
    $response = $dispatch($routes[$method][$uri], []);
    http_response_code($response['code']);
    echo $response['body'];
    exit;
}

// 7) 2) Intento match con parámetros (revisamos GET_PARAM y POST_PARAM si existen)
$paramCollections = [];
if (isset($routes['GET_PARAM'])) $paramCollections['GET'] = $routes['GET_PARAM'];
if (isset($routes['POST_PARAM'])) $paramCollections['POST'] = $routes['POST_PARAM'];
if ($method === 'GET' && isset($routes['GET_PARAM'])) {
    foreach ($routes['GET_PARAM'] as $path => $route) {
        $pattern = '#^' . preg_replace('#\{\w+\}#', '([\w-]+)', rtrim($path, '/')) . '$#';
        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches);
            $response = $dispatch($route, $matches);
            http_response_code($response['code']);
            echo $response['body'];
            exit;
        }
    }
}

// 8) Si nada coincide, mostrar información útil para depurar
http_response_code(404);
echo "<h1>404 - Página no encontrada</h1>";
echo "<p>URI solicitada (raw): " . htmlspecialchars($rawUri) . "</p>";
echo "<p>Ruta procesada: " . htmlspecialchars($uri) . "</p>";
echo "<p>Method: " . htmlspecialchars($method) . "</p>";
echo "<hr><p>Rutas registradas (GET keys):</p><pre>";
if (isset($routes['GET'])) {
    echo htmlspecialchars(implode("\n", array_keys($routes['GET'])));
} else {
    echo "(no hay rutas GET definidas)";
}
echo "</pre>";
log_error("404 en URI: {$rawUri} - procesada como {$uri}");
