<?php
namespace JAM\VisitaSegura\Controller;

class NotificationController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    $this->ensureNotificationsTable();
    }

    private function requireLogin()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . ($GLOBALS['basePath'] ?? '') . '/login');
            exit;
        }
    }

    public function index()
    {
        $this->requireLogin();
        $userId = $_SESSION['user']['id'];

        // Traer no leídas primero y luego leídas
        $stmt = $this->pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND read_at IS NULL ORDER BY created_at DESC LIMIT 50");
        $stmt->execute([$userId]);
        $unread = $stmt->fetchAll();

        $stmt2 = $this->pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND read_at IS NOT NULL ORDER BY created_at DESC LIMIT 200");
        $stmt2->execute([$userId]);
        $read = $stmt2->fetchAll();

        ob_start();
        include __DIR__ . '/../../public/views/notifications.php';
        return ob_get_clean();
    }

    public function markRead($id)
    {
        $this->requireLogin();
        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || empty($csrf) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            http_response_code(400);
            echo 'CSRF inválido';
            exit;
        }
        $userId = $_SESSION['user']['id'];
        $st = $this->pdo->prepare("UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ?");
        $st->execute([$id, $userId]);

        $_SESSION['flashes'][] = ['type' => 'success', 'msg' => 'Notificación marcada como leída.'];
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        header('Location: ' . ($referer ?: ($GLOBALS['basePath'] ?? '') . '/notificaciones'));
        exit;
    }

    public function markAllRead()
    {
        $this->requireLogin();
        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || empty($csrf) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            http_response_code(400);
            echo 'CSRF inválido';
            exit;
        }
        $userId = $_SESSION['user']['id'];
        $st = $this->pdo->prepare("UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL");
        $st->execute([$userId]);

        $_SESSION['flashes'][] = ['type' => 'success', 'msg' => 'Todas las notificaciones marcadas como leídas.'];
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        header('Location: ' . ($referer ?: ($GLOBALS['basePath'] ?? '') . '/notificaciones'));
        exit;
    }

    private function ensureNotificationsTable(): void
    {
        try {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id VARCHAR(64) NOT NULL,
                title VARCHAR(255) NOT NULL,
                body TEXT,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                read_at DATETIME NULL,
                INDEX idx_user_read (user_id, read_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Throwable $e) {}
    }
}
