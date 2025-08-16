<?php
namespace JAM\VisitaSegura\Controller;

class VisitController
{
    private $pdo;

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

        // Consulta básica de visitas (ajusta según tu modelo)
        $stmt = $this->pdo->query("SELECT * FROM visitas ORDER BY fecha DESC");
        $visitas = $stmt->fetchAll();

        ob_start();
        include __DIR__ . '/../../public/views/visits.php';
        return ob_get_clean();
    }

    public function showCreateForm()
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
        ob_start();
        include __DIR__ . '/../../public/views/visits_create.php';
        return ob_get_clean();
    }

    public function store()
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }

        $motivo = $_POST['motivo'] ?? '';
        $fecha = $_POST['fecha'] ?? '';
        $visitante = $_POST['visitante'] ?? '';

        // Validación simple
        if (!$motivo || !$fecha || !$visitante) {
            $error = "Todos los campos son obligatorios";
            ob_start();
            include __DIR__ . '/../../public/views/visits_create.php';
            return ob_get_clean();
        }

        // Guardar en la base de datos (ajusta según tu modelo)
        $stmt = $this->pdo->prepare("INSERT INTO visitas (visitante_id, fecha, motivo) VALUES (?, ?, ?)");
        $stmt->execute([$visitante, $fecha, $motivo]);

        header('Location: ' . $GLOBALS['basePath'] . '/visits');
        exit;
    }

    public function show($id)
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }

        $stmt = $this->pdo->prepare("SELECT * FROM visitas WHERE id = ?");
        $stmt->execute([$id]);
        $visita = $stmt->fetch();

        ob_start();
        include __DIR__ . '/../../public/views/visits_show.php';
        return ob_get_clean();
    }

    public function showEditForm($id)
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
        $stmt = $this->pdo->prepare("SELECT * FROM visitas WHERE id = ?");
        $stmt->execute([$id]);
        $visita = $stmt->fetch();

        ob_start();
        include __DIR__ . '/../../public/views/visits_edit.php';
        return ob_get_clean();
    }

    public function update($id)
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }

        $motivo = $_POST['motivo'] ?? '';
        $fecha = $_POST['fecha'] ?? '';
        $visitante = $_POST['visitante'] ?? '';

        if (!$motivo || !$fecha || !$visitante) {
            $error = "Todos los campos son obligatorios";
            $stmt = $this->pdo->prepare("SELECT * FROM visitas WHERE id = ?");
            $stmt->execute([$id]);
            $visita = $stmt->fetch();
            ob_start();
            include __DIR__ . '/../../public/views/visits_edit.php';
            return ob_get_clean();
        }

        $stmt = $this->pdo->prepare("UPDATE visitas SET visitante_id = ?, fecha = ?, motivo = ? WHERE id = ?");
        $stmt->execute([$visitante, $fecha, $motivo, $id]);

        header('Location: ' . $GLOBALS['basePath'] . '/visits/' . $id);
        exit;
    }

    public function delete($id)
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
        $stmt = $this->pdo->prepare("DELETE FROM visitas WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: ' . $GLOBALS['basePath'] . '/visits');
        exit;
    }
}
