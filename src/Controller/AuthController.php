<?php
namespace JAM\VisitaSegura\Controller;

class AuthController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function showLogin()
    {
        ob_start();
        include __DIR__ . '/../../public/views/login.php';
        return ob_get_clean();
    }

    public function login()
    {
        session_start();
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Buscar usuario por correo
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE correo = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['hash_contrasena'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'rol' => $user['rol']
            ];
            header('Location: /panel');
            exit;
        }

        // Si falla, vuelve al login con mensaje
        $error = "Credenciales inválidas";
        ob_start();
        include __DIR__ . '/../../public/views/login.php';
        return ob_get_clean();
    }

    public function logout()
    {
        session_start();
        session_destroy();
        header('Location: /login');
        exit;
    }

    public function panel()
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        return "<h2>Bienvenido al panel privado, " . htmlspecialchars($_SESSION['user']['nombre']) . "</h2>";
    }

    public function showRegister()
    {
        ob_start();
        include __DIR__ . '/../../public/views/register.php';
        return ob_get_clean();
    }

    public function register()
    {
        $nombre = $_POST['nombre'] ?? '';
        $correo = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $rol = 'usuario';

        // Validación simple
        if (!$nombre || !$correo || !$password) {
            $error = "Todos los campos son obligatorios";
            ob_start();
            include __DIR__ . '/../../public/views/register.php';
            return ob_get_clean();
        }

        // Verifica si el correo ya existe
        $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        if ($stmt->fetch()) {
            $error = "El correo ya está registrado";
            ob_start();
            include __DIR__ . '/../../public/views/register.php';
            return ob_get_clean();
        }

        // Inserta el usuario
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, correo, hash_contrasena, rol) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $correo, $hash, $rol]);

        // Redirige al login
        header('Location: /login');
        exit;
    }
}
