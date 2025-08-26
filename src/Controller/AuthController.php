<?php
namespace JAM\VisitaSegura\Controller;

class AuthController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Mostrar formulario de login.
     * Genera token CSRF y pasa variables a la vista.
     */
    public function showLogin(): string
    {
        // iniciar sesión para gestionar CSRF y retener email si hace falta
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Si ya hay sesión iniciada, redirigir al panel según rol
        if (isset($_SESSION['user'])) {
            $bp = isset($GLOBALS['basePath']) ? rtrim($GLOBALS['basePath'], '/') : '';
            $rol = $_SESSION['user']['rol'] ?? '';
            if ($rol === 'empleado') {
                header('Location: ' . ($bp === '' ? '/panel/empleado' : $bp . '/panel/empleado'));
            } elseif ($rol === 'recepcionista') {
                header('Location: ' . ($bp === '' ? '/panel/recepcionista' : $bp . '/panel/recepcionista'));
            } else {
                header('Location: ' . ($bp === '' ? '/panel' : $bp . '/panel'));
            }
            exit;
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }

        // Variables que la vista espera: $error, $email (si hubo post previo)
        $error = $GLOBALS['login_error'] ?? null;
        $email = $GLOBALS['login_email'] ?? '';

        // incluir vista
        ob_start();
        include __DIR__ . '/../../public/views/login.php';
        return ob_get_clean();
    }

    /**
     * Procesar login (POST)
     * - Validaciones básicas
     * - Verifica CSRF si existe token en sesión
     * - password_verify + session_regenerate_id
     */
    public function login()
    {
        // Aseguramos que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            echo 'Método no permitido';
            exit;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Lectura y saneamiento
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $csrf = $_POST['csrf_token'] ?? '';

        // Comprobación CSRF (si se usa)
        if (!empty($_SESSION['csrf_token'])) {
            if (empty($csrf) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
                $error = "Solicitud inválida (CSRF). Intente de nuevo.";
                $GLOBALS['login_error'] = $error;
                $GLOBALS['login_email'] = $email;
                return $this->showLogin();
            }
        }

        // Validaciones
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $error = "Ingrese un correo válido y la contraseña.";
            $GLOBALS['login_error'] = $error;
            $GLOBALS['login_email'] = $email;
            return $this->showLogin();
        }

        try {
            $stmt = $this->pdo->prepare("SELECT id, nombre, correo, hash_contrasena, rol FROM usuarios WHERE correo = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['hash_contrasena'])) {
                // Login correcto
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id'     => $user['id'],
                    'nombre' => $user['nombre'],
                    'rol'    => $user['rol'],
                    'correo' => $user['correo']
                ];

                // Redirigir a /panel respetando basePath
                $bp = isset($GLOBALS['basePath']) ? rtrim($GLOBALS['basePath'], '/') : '';
                $location = ($bp === '') ? '/panel' : $bp . '/panel';
                header('Location: ' . $location);
                exit;
            }

            // Credenciales inválidas
            $error = "Correo o contraseña incorrectos.";
            $GLOBALS['login_error'] = $error;
            $GLOBALS['login_email'] = $email;
            return $this->showLogin();

        } catch (\Throwable $e) {
            // Registro en log y mensaje genérico
            error_log("[AuthController] Error login: " . $e->getMessage());
            $error = "Error interno. Intente más tarde.";
            $GLOBALS['login_error'] = $error;
            $GLOBALS['login_email'] = $email;
            return $this->showLogin();
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        $bp = isset($GLOBALS['basePath']) ? rtrim($GLOBALS['basePath'], '/') : '';
        $location = ($bp === '') ? '/login' : $bp . '/login';
        header('Location: ' . $location);
        exit;
    }

    /**
     * Panel (ejemplo protegido)
     */
    public function panel(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            $bp = isset($GLOBALS['basePath']) ? rtrim($GLOBALS['basePath'], '/') : '';
            $location = ($bp === '') ? '/login' : $bp . '/login';
            header('Location: ' . $location);
            exit;
        }
        // Mostrar el panel real con menú y secciones
        ob_start();
        include __DIR__ . '/../../public/views/panel.php';
        return ob_get_clean();
    }

    /**
     * Mostrar registro
     */
    public function showRegister(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        // Si ya hay sesión iniciada, redirigir al panel según rol
        if (isset($_SESSION['user'])) {
            $bp = isset($GLOBALS['basePath']) ? rtrim($GLOBALS['basePath'], '/') : '';
            $rol = $_SESSION['user']['rol'] ?? '';
            if ($rol === 'empleado') {
                header('Location: ' . ($bp === '' ? '/panel/empleado' : $bp . '/panel/empleado'));
            } elseif ($rol === 'recepcionista') {
                header('Location: ' . ($bp === '' ? '/panel/recepcionista' : $bp . '/panel/recepcionista'));
            } else {
                header('Location: ' . ($bp === '' ? '/panel' : $bp . '/panel'));
            }
            exit;
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }

        $error = $GLOBALS['register_error'] ?? null;
        $old = $GLOBALS['register_old'] ?? [];

        ob_start();
        include __DIR__ . '/../../public/views/register.php';
        return ob_get_clean();
    }

    /**
     * Registrar nuevo usuario
     */
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            echo 'Método no permitido';
            exit;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $csrf = $_POST['csrf_token'] ?? '';

        // CSRF
        if (!empty($_SESSION['csrf_token'])) {
            if (empty($csrf) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
                $error = "Solicitud inválida (CSRF).";
                $GLOBALS['register_error'] = $error;
                $GLOBALS['register_old'] = ['nombre'=>$nombre, 'email'=>$correo];
                return $this->showRegister();
            }
        }

        // Validaciones básicas
        if ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL) || $password === '') {
            $error = "Todos los campos son obligatorios y el correo debe ser válido.";
            $GLOBALS['register_error'] = $error;
            $GLOBALS['register_old'] = ['nombre'=>$nombre, 'email'=>$correo];
            return $this->showRegister();
        }

        try {
            // Verificar existencia
            $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
            $stmt->execute([$correo]);
            if ($stmt->fetch()) {
                $error = "El correo ya está registrado.";
                $GLOBALS['register_error'] = $error;
                $GLOBALS['register_old'] = ['nombre'=>$nombre, 'email'=>$correo];
                return $this->showRegister();
            }

            // Insertar (incluyendo id)
            $id = bin2hex(random_bytes(16));
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO usuarios (id, nombre, correo, hash_contrasena, rol) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id, $nombre, $correo, $hash, 'usuario']);

            // Redirigir al login
            $bp = isset($GLOBALS['basePath']) ? rtrim($GLOBALS['basePath'], '/') : '';
            $location = ($bp === '') ? '/login' : $bp . '/login';
            header('Location: ' . $location);
            exit;

        } catch (\Throwable $e) {
            error_log("[AuthController] Error register: " . $e->getMessage());
            $error = "Error interno. Intente más tarde.";
            $GLOBALS['register_error'] = $error;
            $GLOBALS['register_old'] = ['nombre'=>$nombre, 'email'=>$correo];
            return $this->showRegister();
        }
    }

    /**
     * Panel Empleado
     */
    public function panelEmpleado(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['user'])) {
            $bp = isset($GLOBALS['basePath']) ? rtrim($GLOBALS['basePath'], '/') : '';
            header('Location: ' . ($bp === '' ? '/login' : $bp . '/login'));
            exit;
        }
        if (($_SESSION['user']['rol'] ?? '') !== 'empleado') {
            header('Location: ' . ($GLOBALS['basePath'] ?? '') . '/panel');
            exit;
        }
        ob_start();
        include __DIR__ . '/../../public/views/panel_empleado.php';
        return ob_get_clean();
    }

    /**
     * Panel Recepcionista
     */
    public function panelRecepcionista(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['user'])) {
            $bp = isset($GLOBALS['basePath']) ? rtrim($GLOBALS['basePath'], '/') : '';
            header('Location: ' . ($bp === '' ? '/login' : $bp . '/login'));
            exit;
        }
        if (($_SESSION['user']['rol'] ?? '') !== 'recepcionista') {
            header('Location: ' . ($GLOBALS['basePath'] ?? '') . '/panel');
            exit;
        }
        ob_start();
        include __DIR__ . '/../../public/views/panel_recepcionista.php';
        return ob_get_clean();
    }
}
