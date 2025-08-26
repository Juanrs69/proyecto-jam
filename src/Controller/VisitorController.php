<?php
// Controlador CRUD para visitantes: listar, crear, ver, editar y eliminar visitantes.
namespace JAM\VisitaSegura\Controller;

class VisitorController
{
    private $pdo;
    private function requireRoles(array $roles)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . ($GLOBALS['basePath'] ?? '') . '/login'); exit;
        }
        $rol = $_SESSION['user']['rol'] ?? '';
        if (!in_array($rol, $roles, true)) {
            http_response_code(403);
            echo 'Acceso restringido.'; exit;
        }
    }

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function index()
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
        $stmt = $this->pdo->query("SELECT * FROM visitantes ORDER BY nombre");
        $visitantes = $stmt->fetchAll();

        ob_start();
        include __DIR__ . '/../../public/views/visitantes.php';
        return ob_get_clean();
    }

    public function showCreateForm()
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
    // Admin, empleado y recepcionista pueden crear
    $this->requireRoles(['administrador','empleado','recepcionista']);
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        ob_start();
        include __DIR__ . '/../../public/views/visitantes_create.php';
        return ob_get_clean();
    }

    public function store()
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
    // Admin, empleado y recepcionista pueden crear
    $this->requireRoles(['administrador','empleado','recepcionista']);
        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || empty($csrf) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            $error = "Solicitud inválida (CSRF).";
            ob_start();
            include __DIR__ . '/../../public/views/visitantes_create.php';
            return ob_get_clean();
        }
        $nombre = trim($_POST['nombre'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $empresa = trim($_POST['empresa'] ?? '');

        if ($nombre === '' || $documento === '') {
            $error = "Nombre y documento son obligatorios";
            ob_start();
            include __DIR__ . '/../../public/views/visitantes_create.php';
            return ob_get_clean();
        }

    $stmt = $this->pdo->prepare("INSERT INTO visitantes (nombre, documento, empresa) VALUES (?, ?, ?)");
    $stmt->execute([$nombre, $documento, $empresa]);
    $_SESSION['flashes'][] = ['type' => 'success', 'msg' => 'Visitante creado correctamente.'];
    header('Location: ' . $GLOBALS['basePath'] . '/visitantes');
        exit;
    }

    public function show($id)
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
        $stmt = $this->pdo->prepare("SELECT * FROM visitantes WHERE id = ?");
        $stmt->execute([$id]);
        $visitante = $stmt->fetch();

        ob_start();
        include __DIR__ . '/../../public/views/visitantes_show.php';
        return ob_get_clean();
    }

    public function showEditForm($id)
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
    // Solo administrador edita
    $this->requireRoles(['administrador']);
        $stmt = $this->pdo->prepare("SELECT * FROM visitantes WHERE id = ?");
        $stmt->execute([$id]);
        $visitante = $stmt->fetch();

        ob_start();
        include __DIR__ . '/../../public/views/visitantes_edit.php';
        return ob_get_clean();
    }

    public function update($id)
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
        // Solo administrador
        $this->requireRoles(['administrador']);
        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || empty($csrf) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            $error = "Solicitud inválida (CSRF).";
            $stmt = $this->pdo->prepare("SELECT * FROM visitantes WHERE id = ?");
            $stmt->execute([$id]);
            $visitante = $stmt->fetch();
            ob_start();
            include __DIR__ . '/../../public/views/visitantes_edit.php';
            return ob_get_clean();
        }
        $nombre = trim($_POST['nombre'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $empresa = trim($_POST['empresa'] ?? '');

        if ($nombre === '' || $documento === '') {
            $error = "Nombre y documento son obligatorios";
            $stmt = $this->pdo->prepare("SELECT * FROM visitantes WHERE id = ?");
            $stmt->execute([$id]);
            $visitante = $stmt->fetch();
            ob_start();
            include __DIR__ . '/../../public/views/visitantes_edit.php';
            return ob_get_clean();
        }

    $stmt = $this->pdo->prepare("UPDATE visitantes SET nombre = ?, documento = ?, empresa = ? WHERE id = ?");
    $stmt->execute([$nombre, $documento, $empresa, $id]);
    $_SESSION['flashes'][] = ['type' => 'success', 'msg' => 'Visitante actualizado.'];
    header('Location: ' . $GLOBALS['basePath'] . '/visitantes');
        exit;
    }

    public function delete($id)
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
    // Solo administrador
    $this->requireRoles(['administrador']);
        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || empty($csrf) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            http_response_code(400);
            echo "Solicitud inválida (CSRF).";
            exit;
        }
        try {
            $stmt = $this->pdo->prepare("DELETE FROM visitantes WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['flashes'][] = ['type' => 'success', 'msg' => 'Visitante eliminado.'];
            header('Location: ' . $GLOBALS['basePath'] . '/visitantes');
            exit;
        } catch (\PDOException $e) {
            $error = "No se puede eliminar: el visitante tiene visitas asociadas.";
            $stmt = $this->pdo->prepare("SELECT * FROM visitantes WHERE id = ?");
            $stmt->execute([$id]);
            $visitante = $stmt->fetch();
            ob_start();
            include __DIR__ . '/../../public/views/visitantes_show.php';
            return ob_get_clean();
        }
    }
}
