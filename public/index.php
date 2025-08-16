<?php declare(strict_types=1);

// Mostrar errores durante desarrollo (quita en producción)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Conexión a la base de datos
$pdo = require __DIR__ . '/../src/Config/database.php';

// Cargar rutas
$routes = require __DIR__ . '/../src/Config/routes.php';

// Incluir controlador manualmente
require_once __DIR__ . '/../src/Controller/AuthController.php';
require_once __DIR__ . '/../src/Controller/VisitController.php';

// Obtener método y URI
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

// Detectar base path dinámicamente
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); // Ej: /visita-segura/public
$basePath = $scriptDir !== '/' ? $scriptDir : '';
if ($basePath !== '' && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
    if ($uri === '') $uri = '/';
}

// Enrutamiento simple
if (isset($routes[$method][$uri])) {
    [$class, $action] = $routes[$method][$uri];
    $controller = new $class($pdo);
    // Pasa $basePath a las vistas usando variable global
    $GLOBALS['basePath'] = $basePath;
    echo $controller->$action();
    exit;
}

echo "<h1>¡Proyecto VisitaSegura iniciado correctamente!</h1>";
echo "<p>Ruta no encontrada: " . htmlspecialchars($uri) . "</p>";
