<?php
namespace JAM\VisitaSegura\Controller;

// Controlador para gestionar las visitas (CRUD)
class VisitController
{
    // Propiedad para la conexión PDO
    private $pdo;

    // Constructor: recibe la conexión PDO
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    private function requireAdmin()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login'); exit;
        }
        if (($_SESSION['user']['rol'] ?? '') !== 'administrador') {
            http_response_code(403);
            echo 'Acceso restringido: se requiere rol administrador.';
            exit;
        }
    }

    // Muestra el listado de visitas
    public function index()
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login'); exit;
        }
        // Restringir a administradores
        $this->requireAdmin();

        // Filtros
        $fd  = trim($_GET['desde'] ?? ''); // formato esperado: YYYY-MM-DD
        $fh  = trim($_GET['hasta'] ?? ''); // formato esperado: YYYY-MM-DD
        $dep = trim($_GET['dep']   ?? '');

        $where  = [];
        $params = [];
        if ($fd !== '') { $where[] = 'fecha >= ?'; $params[] = $fd . ' 00:00:00'; }
        if ($fh !== '') { $where[] = 'fecha <= ?'; $params[] = $fh . ' 23:59:59'; }
        if ($dep !== '') { $where[] = 'departamento LIKE ?'; $params[] = '%' . $dep . '%'; }

        $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

        // Paginación
        $perPage = 10;
        $page = max(1, (int)($_GET['p'] ?? 1));
        $offset = ($page - 1) * $perPage;

        // Total
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM visitas{$whereSql}");
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        // Datos
        $sql = "SELECT * FROM visitas{$whereSql} ORDER BY fecha DESC LIMIT ? OFFSET ?";
        $stmt = $this->pdo->prepare($sql);
        $bind = $params;
        $bind[] = $perPage;
        $bind[] = $offset;
        // Vincular para forzar enteros en LIMIT/OFFSET
        foreach ($bind as $i => $val) {
            $type = PDO::PARAM_STR;
            if ($i >= count($params)) $type = PDO::PARAM_INT;
            $stmt->bindValue($i + 1, $val, $type);
        }
        $stmt->execute();
        $visitas = $stmt->fetchAll();

        // Variables para la vista
        $filters = ['desde' => $fd, 'hasta' => $fh, 'dep' => $dep];

        ob_start();
        include __DIR__ . '/../../public/views/visits.php';
        return ob_get_clean();
    }

    // Muestra el formulario para crear una nueva visita
    public function showCreateForm()
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
        $this->requireAdmin();
        // Obtiene todos los visitantes para el select del formulario
        $stmt = $this->pdo->query("SELECT id, nombre FROM visitantes ORDER BY nombre");
        $visitantes = $stmt->fetchAll();

        // Incluye la vista del formulario de creación
        ob_start();
        include __DIR__ . '/../../public/views/visits_create.php';
        return ob_get_clean();
    }

    // Procesa el guardado de una nueva visita (POST)
    public function store()
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
        $this->requireAdmin();
        // Validación CSRF
        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || empty($csrf) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            $error = "Solicitud inválida (CSRF).";
            // ...recarga visitantes...
            $stmt = $this->pdo->query("SELECT id, nombre FROM visitantes ORDER BY nombre");
            $visitantes = $stmt->fetchAll();
            ob_start();
            include __DIR__ . '/../../public/views/visits_create.php';
            return ob_get_clean();
        }

        $motivo = $_POST['motivo'] ?? '';
        $fecha = $_POST['fecha'] ?? '';
        $visitante = $_POST['visitante'] ?? '';
        $nuevo_nombre = trim($_POST['nuevo_nombre'] ?? '');
        $nuevo_documento = trim($_POST['nuevo_documento'] ?? '');
        $nuevo_empresa = trim($_POST['nuevo_empresa'] ?? '');
        $departamento = trim($_POST['departamento'] ?? ''); // nuevo

        // Validación simple de campos obligatorios (departamento opcional para compatibilidad)
        if (!$motivo || !$fecha || (!$visitante && !$nuevo_nombre)) {
            $error = "Todos los campos son obligatorios (elige o crea un visitante)";
            $stmt = $this->pdo->query("SELECT id, nombre FROM visitantes ORDER BY nombre");
            $visitantes = $stmt->fetchAll();
            ob_start();
            include __DIR__ . '/../../public/views/visits_create.php';
            return ob_get_clean();
        }

        // Si se eligió crear un nuevo visitante
        if (!$visitante && $nuevo_nombre) {
            $stmt = $this->pdo->prepare("INSERT INTO visitantes (nombre, documento, empresa) VALUES (?, ?, ?)");
            $stmt->execute([$nuevo_nombre, $nuevo_documento, $nuevo_empresa]);
            $visitante = $this->pdo->lastInsertId();
        }

        // Inserta la visita (incluye departamento si existe)
        $stmt = $this->pdo->prepare("INSERT INTO visitas (visitante_id, fecha, motivo, departamento) VALUES (?, ?, ?, ?)");
        $stmt->execute([$visitante, $fecha, $motivo, $departamento !== '' ? $departamento : null]);

        // Redirigir al listado de visitas después de crear
        header('Location: ' . $GLOBALS['basePath'] . '/visits');
        exit;
    }

    // Muestra el detalle de una visita
    public function show($id)
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
        $this->requireAdmin();

        // Busca la visita por su ID
        $stmt = $this->pdo->prepare("SELECT * FROM visitas WHERE id = ?");
        $stmt->execute([$id]);
        $visita = $stmt->fetch();

        // Incluye la vista de detalle
        ob_start();
        include __DIR__ . '/../../public/views/visits_show.php';
        return ob_get_clean();
    }

    // Muestra el formulario para editar una visita
    public function showEditForm($id)
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
        $this->requireAdmin();
        // Busca la visita a editar
        $stmt = $this->pdo->prepare("SELECT * FROM visitas WHERE id = ?");
        $stmt->execute([$id]);
        $visita = $stmt->fetch();

        // Obtiene todos los visitantes para el select
        $stmt2 = $this->pdo->query("SELECT id, nombre FROM visitantes ORDER BY nombre");
        $visitantes = $stmt2->fetchAll();

        // Incluye la vista de edición
        ob_start();
        include __DIR__ . '/../../public/views/visits_edit.php';
        return ob_get_clean();
    }

    // Procesa la actualización de una visita (POST)
    public function update($id)
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
        $this->requireAdmin();
        // Validación CSRF
        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || empty($csrf) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            $error = "Solicitud inválida (CSRF).";
            $stmt = $this->pdo->prepare("SELECT * FROM visitas WHERE id = ?");
            $stmt->execute([$id]);
            $visita = $stmt->fetch();
            ob_start();
            include __DIR__ . '/../../public/views/visits_edit.php';
            return ob_get_clean();
        }

        // Recoge los datos del formulario
        $motivo = $_POST['motivo'] ?? '';
        $fecha = $_POST['fecha'] ?? '';
        $visitante = $_POST['visitante'] ?? '';
        $departamento = trim($_POST['departamento'] ?? ''); // nuevo

        // Validación de campos obligatorios (departamento opcional para compatibilidad)
        if (!$motivo || !$fecha || !$visitante) {
            $error = "Todos los campos son obligatorios";
            $stmt = $this->pdo->prepare("SELECT * FROM visitas WHERE id = ?");
            $stmt->execute([$id]);
            $visita = $stmt->fetch();
            ob_start();
            include __DIR__ . '/../../public/views/visits_edit.php';
            return ob_get_clean();
        }

        // Actualiza incluyendo departamento
        $stmt = $this->pdo->prepare("UPDATE visitas SET visitante_id = ?, fecha = ?, motivo = ?, departamento = ? WHERE id = ?");
        $stmt->execute([$visitante, $fecha, $motivo, $departamento !== '' ? $departamento : null, $id]);

        // Redirigir al listado de visitas después de editar
        header('Location: ' . $GLOBALS['basePath'] . '/visits');
        exit;
    }

    // Elimina una visita
    public function delete($id)
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
        $this->requireAdmin();
        // Validación CSRF
        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || empty($csrf) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            http_response_code(400);
            echo "Solicitud inválida (CSRF).";
            exit;
        }
        // Elimina la visita
        $stmt = $this->pdo->prepare("DELETE FROM visitas WHERE id = ?");
        $stmt->execute([$id]);
        // Redirigir al listado de visitas después de eliminar
        header('Location: ' . $GLOBALS['basePath'] . '/visits');
        exit;
    }
}
