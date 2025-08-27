<?php
namespace JAM\VisitaSegura\Controller;

/**
 * Controlador para gestionar visitas (CRUD + acciones) y utilidades relacionadas.
 *
 * Responsabilidades principales:
 * - Listar visitas con filtros y paginación.
 * - Crear, editar, eliminar visitas.
 * - Autorizar/Rechazar visitas; Marcar salida.
 * - Exportar CSV.
 * - Asegurar columnas requeridas en la tabla 'visitas' y la tabla de notificaciones.
 *
 * Notas de seguridad:
 * - Todas las mutaciones usan validación CSRF y control de roles.
 * - Redirecciones post-acción preservan el contexto del panel (referer cuando aplica).
 */
class VisitController
{
    // Propiedad para la conexión PDO
    private $pdo;

    // Constructor: recibe la conexión PDO
    /**
     * Constructor.
     * @param \PDO $pdo Conexión a base de datos.
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        // Garantiza que las columnas requeridas existen (idempotente)
        $this->ensureVisitasColumns();
        // Crea la tabla de notificaciones si no existe
        $this->ensureNotificationsTable();
    }

    /**
     * Restringe acceso a rol Administrador.
     */
    private function requireAdmin()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login'); exit;
        }
        $u = $_SESSION['user'] ?? null;
        $rolSafe = is_array($u) ? ($u['rol'] ?? '') : '';
        if ($rolSafe !== 'administrador') {
            http_response_code(403);
            echo 'Acceso restringido: se requiere rol administrador.';
            exit;
        }
    }

    /**
     * Restringe acceso a un conjunto de roles válidos.
     * @param string[] $roles
     */
    private function requireRoles(array $roles)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login'); exit;
        }
        $u = $_SESSION['user'] ?? null;
        $rol = is_array($u) ? ($u['rol'] ?? '') : '';
        if (!in_array($rol, $roles, true)) {
            http_response_code(403);
            echo 'Acceso restringido.'; exit;
        }
    }

    /**
     * Listado de visitas con filtros y paginación.
     * @return string HTML renderizado
     */
    public function index()
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login'); exit;
        }
        // Administrador/Empleado/Recepcionista pueden ver el listado
        $this->requireRoles(['administrador','empleado','recepcionista']);

        // Filtros
        $fd  = trim($_GET['desde'] ?? ''); // formato esperado: YYYY-MM-DD
        $fh  = trim($_GET['hasta'] ?? ''); // formato esperado: YYYY-MM-DD
        $dep = trim($_GET['dep']   ?? '');
        $doc = trim($_GET['doc']   ?? '');
        $est = trim($_GET['estado']?? '');
        $emp = trim($_GET['emp']   ?? ''); // búsqueda por empleado anfitrión (nombre o id)

        $where  = [];
        $params = [];
        if ($fd !== '') { $where[] = 'v.fecha >= ?'; $params[] = $fd . ' 00:00:00'; }
        if ($fh !== '') { $where[] = 'v.fecha <= ?'; $params[] = $fh . ' 23:59:59'; }
        if ($dep !== '') { $where[] = 'v.departamento LIKE ?'; $params[] = '%' . $dep . '%'; }
        if ($doc !== '') { $where[] = 'vi.documento LIKE ?'; $params[] = '%' . $doc . '%'; }
        if ($est !== '') { $where[] = 'v.estado = ?'; $params[] = $est; }
        if ($emp !== '') {
            // permitir buscar por nombre de empleado (LIKE) o por id exacto
            $where[] = '(u.nombre LIKE ? OR u.id = ?)';
            $params[] = '%' . $emp . '%';
            $params[] = $emp;
        }

        $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

        // Paginación
        $perPage = 10;
        $page = max(1, (int)($_GET['p'] ?? 1));
        $offset = ($page - 1) * $perPage;

    // Total (con join para filtro por doc)
    $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM visitas v 
                      LEFT JOIN visitantes vi ON vi.id = v.visitante_id
                      LEFT JOIN usuarios u ON u.id = v.anfitrion_id
                      {$whereSql}");
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

    // Datos
    $sql = "SELECT v.*, u.nombre AS empleado_nombre FROM visitas v
        LEFT JOIN visitantes vi ON vi.id = v.visitante_id
        LEFT JOIN usuarios u ON u.id = v.anfitrion_id
                {$whereSql}
                ORDER BY v.fecha DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->pdo->prepare($sql);
        $bind = $params;
        $bind[] = $perPage;
        $bind[] = $offset;
        foreach ($bind as $i => $val) {
            $type = ($i >= count($params)) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $stmt->bindValue($i + 1, $val, $type);
        }
        $stmt->execute();
        $visitas = $stmt->fetchAll();

        // Variables para la vista
    $filters = ['desde' => $fd, 'hasta' => $fh, 'dep' => $dep, 'doc' => $doc, 'estado' => $est, 'emp' => $emp];

        ob_start();
        include __DIR__ . '/../../public/views/visits.php';
        return ob_get_clean();
    }

    /**
     * Formulario de creación de visita.
     * @return string HTML
     */
    public function showCreateForm()
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
        $this->requireRoles(['administrador','empleado','recepcionista']);
        // Obtiene todos los visitantes para el select del formulario
        $stmt = $this->pdo->query("SELECT id, nombre FROM visitantes ORDER BY nombre");
        $visitantes = $stmt->fetchAll();
        // Empleados para asignar como anfitrión (visible para admin/recepcionista)
        $empleados = $this->pdo->query("SELECT id, nombre FROM usuarios WHERE rol='empleado' ORDER BY nombre")->fetchAll();

        // Incluye la vista del formulario de creación
        ob_start();
        include __DIR__ . '/../../public/views/visits_create.php';
        return ob_get_clean();
    }

    /**
     * Guardado de nueva visita (POST).
     * - Valida CSRF, crea visitante si se indicó uno nuevo, asigna anfitrión.
     * - Notifica al anfitrión.
     */
    public function store()
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
        $this->requireRoles(['administrador','empleado','recepcionista']);
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
    $anfitrion_id = trim($_POST['anfitrion_id'] ?? ''); // nuevo

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

    // Resolver anfitrión: si rol empleado y no viene, usar el actual
        $u = $_SESSION['user'] ?? null;
        $rol = is_array($u) ? ($u['rol'] ?? '') : '';
        if ($rol === 'empleado' && $anfitrion_id === '') {
            $anfitrion_id = is_array($u) ? ($u['id'] ?? '') : '';
        }
        if ($anfitrion_id === '') { $anfitrion_id = null; }

        // Inserta la visita (incluye departamento y anfitrión si existen)
        $stmt = $this->pdo->prepare("INSERT INTO visitas (visitante_id, fecha, motivo, departamento, anfitrion_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$visitante, $fecha, $motivo, $departamento !== '' ? $departamento : null, $anfitrion_id]);
        $visitaId = $this->pdo->lastInsertId();

    // Notificar al anfitrión (si aplica)
        if (!empty($anfitrion_id)) {
            $this->notifyUser($anfitrion_id, 'Nuevo visitante', 'Se ha registrado una visita que te tiene como anfitrión. ID visita: ' . $visitaId);
        }

    // Redirigir al listado de visitas después de crear
    $_SESSION['flashes'][] = ['type' => 'success', 'msg' => 'Visita creada correctamente.'];
    header('Location: ' . $GLOBALS['basePath'] . '/visits');
        exit;
    }

    /**
     * Detalle de una visita.
     * @param mixed $id
     * @return string HTML
     */
    public function show($id)
    {
        // Permitir a admin/empleado/recepcionista ver detalle
        $this->requireRoles(['administrador','empleado','recepcionista']);

        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
        // $this->requireAdmin(); // NO requerido para ver detalle

        // Busca la visita por su ID
    $stmt = $this->pdo->prepare("SELECT v.*, u.nombre AS empleado_nombre FROM visitas v LEFT JOIN usuarios u ON u.id=v.anfitrion_id WHERE v.id = ?");
        $stmt->execute([$id]);
        $visita = $stmt->fetch();

        // Incluye la vista de detalle
        ob_start();
        include __DIR__ . '/../../public/views/visits_show.php';
        return ob_get_clean();
    }

    /**
     * Formulario de edición (solo admin).
     */
    public function showEditForm($id)
    {
        $this->requireAdmin();
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
        // Busca la visita a editar
    $stmt = $this->pdo->prepare("SELECT * FROM visitas WHERE id = ?");
        $stmt->execute([$id]);
        $visita = $stmt->fetch();

        // Obtiene todos los visitantes para el select
    $stmt2 = $this->pdo->query("SELECT id, nombre FROM visitantes ORDER BY nombre");
    $visitantes = $stmt2->fetchAll();
    // Empleados (solo para admin)
    $empleados = $this->pdo->query("SELECT id, nombre FROM usuarios WHERE rol='empleado' ORDER BY nombre")->fetchAll();

        // Incluye la vista de edición
        ob_start();
        include __DIR__ . '/../../public/views/visits_edit.php';
        return ob_get_clean();
    }

    /**
     * Actualización de visita (POST) (solo admin).
     */
    public function update($id)
    {
        $this->requireAdmin();
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['basePath'] . '/login');
            exit;
        }
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
    $salida = trim($_POST['salida'] ?? ''); // nuevo
    $anfitrion_id = trim($_POST['anfitrion_id'] ?? ''); // nuevo

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
    $stmt = $this->pdo->prepare("UPDATE visitas
                     SET visitante_id = ?, fecha = ?, motivo = ?, departamento = ?, salida = ?, anfitrion_id = ?
                     WHERE id = ?");
        $stmt->execute([
            $visitante,
            $fecha,
            $motivo,
            $departamento !== '' ? $departamento : null,
        $salida !== '' ? $salida : null,
        $anfitrion_id !== '' ? $anfitrion_id : null,
        $id
        ]);

    // Redirigir al listado de visitas después de editar
    $_SESSION['flashes'][] = ['type' => 'success', 'msg' => 'Visita actualizada correctamente.'];
    header('Location: ' . $GLOBALS['basePath'] . '/visits');
        exit;
    }

    /**
     * Página de confirmación para Autorizar/Rechazar (admin/empleado).
     */
    public function showAuthorizeForm($id)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $this->requireRoles(['administrador','empleado']);

        $decision = $_GET['decision'] ?? 'autorizar';
        if (!in_array($decision, ['autorizar','rechazar'], true)) {
            $decision = 'autorizar';
        }
        // Cargar visita con datos del visitante
        $stmt = $this->pdo->prepare("SELECT v.*, vi.documento, vi.nombre AS visitante_nombre FROM visitas v LEFT JOIN visitantes vi ON vi.id=v.visitante_id WHERE v.id = ?");
        $stmt->execute([$id]);
        $visita = $stmt->fetch();

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title><?= $decision==='autorizar' ? 'Autorizar visita' : 'Rechazar visita' ?></title>
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
        <div class="container py-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-3"><?= $decision==='autorizar' ? 'Autorizar visita' : 'Rechazar visita' ?></h4>
                    <?php if (!$visita): ?>
                        <div class="alert alert-danger">Visita no encontrada.</div>
                    <?php else: ?>
                        <?php if (!empty($_GET['ok'])): ?>
                            <div class="alert alert-success">Estado actualizado correctamente.</div>
                        <?php endif; ?>
                        <dl class="row">
                            <dt class="col-sm-3">ID</dt><dd class="col-sm-9"><?= htmlspecialchars($visita['id']) ?></dd>
                            <dt class="col-sm-3">Documento</dt><dd class="col-sm-9"><?= htmlspecialchars($visita['documento'] ?? '-') ?></dd>
                            <dt class="col-sm-3">Visitante</dt><dd class="col-sm-9"><?= htmlspecialchars($visita['visitante_nombre'] ?? '-') ?></dd>
                            <dt class="col-sm-3">Fecha</dt><dd class="col-sm-9"><?= htmlspecialchars($visita['fecha']) ?></dd>
                            <dt class="col-sm-3">Motivo</dt><dd class="col-sm-9"><?= htmlspecialchars($visita['motivo']) ?></dd>
                            <dt class="col-sm-3">Departamento</dt><dd class="col-sm-9"><?= htmlspecialchars($visita['departamento'] ?? '-') ?></dd>
                            <dt class="col-sm-3">Salida</dt><dd class="col-sm-9"><?= htmlspecialchars($visita['salida'] ?? '-') ?></dd>
                            <dt class="col-sm-3">Estado actual</dt><dd class="col-sm-9"><?= htmlspecialchars($visita['estado'] ?? 'pendiente') ?></dd>
                        </dl>
                        <form method="post" action="<?= htmlspecialchars(($GLOBALS['basePath'] ?? '') . '/visits/' . urlencode((string)$visita['id']) . '/authorize') ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <input type="hidden" name="decision" value="<?= htmlspecialchars($decision) ?>">
                            <div class="d-flex gap-2">
                                <a href="<?= htmlspecialchars(($GLOBALS['basePath'] ?? '') . '/visits') ?>" class="btn btn-secondary">Volver</a>
                                <button type="submit" class="btn <?= $decision==='autorizar' ? 'btn-success' : 'btn-warning' ?>">
                                    <?= $decision==='autorizar' ? 'Confirmar autorización' : 'Confirmar rechazo' ?>
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Página de confirmación para Marcar salida (admin/empleado).
     */
    public function showExitForm($id)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $this->requireRoles(['administrador','empleado']);

        $stmt = $this->pdo->prepare("SELECT v.*, vi.documento, vi.nombre AS visitante_nombre FROM visitas v LEFT JOIN visitantes vi ON vi.id=v.visitante_id WHERE v.id = ?");
        $stmt->execute([$id]);
        $visita = $stmt->fetch();

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Marcar salida</title>
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
        <div class="container py-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-3">Marcar salida</h4>
                    <?php if (!$visita): ?>
                        <div class="alert alert-danger">Visita no encontrada.</div>
                    <?php else: ?>
                        <?php if (!empty($_GET['ok'])): ?>
                            <div class="alert alert-success">Salida registrada correctamente.</div>
                        <?php endif; ?>
                        <dl class="row">
                            <dt class="col-sm-3">ID</dt><dd class="col-sm-9"><?= htmlspecialchars($visita['id']) ?></dd>
                            <dt class="col-sm-3">Documento</dt><dd class="col-sm-9"><?= htmlspecialchars($visita['documento'] ?? '-') ?></dd>
                            <dt class="col-sm-3">Visitante</dt><dd class="col-sm-9"><?= htmlspecialchars($visita['visitante_nombre'] ?? '-') ?></dd>
                            <dt class="col-sm-3">Fecha entrada</dt><dd class="col-sm-9"><?= htmlspecialchars($visita['fecha']) ?></dd>
                            <dt class="col-sm-3">Salida</dt><dd class="col-sm-9"><?= htmlspecialchars($visita['salida'] ?? '-') ?></dd>
                            <dt class="col-sm-3">Estado</dt><dd class="col-sm-9"><?= htmlspecialchars($visita['estado'] ?? 'pendiente') ?></dd>
                        </dl>
                        <?php if (!empty($visita['salida'])): ?>
                            <div class="alert alert-info">Esta visita ya tiene salida registrada.</div>
                            <a href="<?= htmlspecialchars(($GLOBALS['basePath'] ?? '') . '/visits') ?>" class="btn btn-secondary">Volver</a>
                        <?php else: ?>
                            <form method="post" action="<?= htmlspecialchars(($GLOBALS['basePath'] ?? '') . '/visits/' . urlencode((string)$visita['id']) . '/exit') ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <div class="d-flex gap-2">
                                    <a href="<?= htmlspecialchars(($GLOBALS['basePath'] ?? '') . '/visits') ?>" class="btn btn-secondary">Volver</a>
                                    <button type="submit" class="btn btn-outline-secondary">Marcar salida ahora</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Acción: Autorizar o Rechazar visita (empleado/admin)
     */
    public function authorize($id)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $this->requireRoles(['administrador','empleado']);
        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || empty($csrf) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            http_response_code(400); echo "CSRF inválido"; exit;
        }
        $decision = $_POST['decision'] ?? '';
        if (!in_array($decision, ['autorizar','rechazar'], true)) {
            http_response_code(400); echo "Decisión inválida"; exit;
        }
        $estado = $decision === 'autorizar' ? 'autorizada' : 'rechazada';
    $u = $_SESSION['user'] ?? null;
    $userId = is_array($u) ? ($u['id'] ?? null) : null;
    if (!$userId) { http_response_code(403); echo 'Acceso restringido.'; exit; }
        $stmt = $this->pdo->prepare("UPDATE visitas SET estado = ?, autorizado_por = ? WHERE id = ?");
        $stmt->execute([$estado, $userId, $id]);

        // Flashes y redirección a la página anterior (flujo modal)
        $_SESSION['flashes'][] = ['type' => $decision==='autorizar' ? 'success' : 'warning', 'msg' => ($decision==='autorizar' ? 'Autorización exitosa.' : 'Visita rechazada.')];
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        if ($referer) {
            header('Location: ' . $referer);
        } else {
            $bp = $GLOBALS['basePath'] ?? '';
            header('Location: ' . $bp . '/visits');
        }
        exit;
    }

    /**
     * Acción: Marcar salida (empleado/admin)
     */
    public function markExit($id)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $this->requireRoles(['administrador','empleado']);
        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || empty($csrf) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            http_response_code(400); echo "CSRF inválido"; exit;
        }
        // Si estaba pendiente, pásalo a autorizada al marcar la salida
    $stmt = $this->pdo->prepare("UPDATE visitas SET salida = NOW(), estado = CASE WHEN estado='pendiente' THEN 'autorizada' ELSE estado END WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['flashes'][] = ['type' => 'info', 'msg' => 'Salida registrada.'];
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        if ($referer) {
            header('Location: ' . $referer);
        } else {
            $bp = $GLOBALS['basePath'] ?? '';
            header('Location: ' . $bp . '/visits');
        }
        exit;
    }

    /**
     * Exportar CSV (solo admin). Reutiliza filtros de index().
     */
    public function export()
    {
        $this->requireAdmin();

        // Reutiliza mismos filtros que index()
        $fd  = trim($_GET['desde'] ?? '');
        $fh  = trim($_GET['hasta'] ?? '');
    $dep = trim($_GET['dep']   ?? '');
        $doc = trim($_GET['doc']   ?? '');
    $est = trim($_GET['estado']?? '');
    $emp = trim($_GET['emp']   ?? '');

        $where  = [];
        $params = [];
        if ($fd !== '') { $where[] = 'v.fecha >= ?'; $params[] = $fd . ' 00:00:00'; }
        if ($fh !== '') { $where[] = 'v.fecha <= ?'; $params[] = $fh . ' 23:59:59'; }
        if ($dep !== '') { $where[] = 'v.departamento LIKE ?'; $params[] = '%' . $dep . '%'; }
        if ($doc !== '') { $where[] = 'vi.documento LIKE ?'; $params[] = '%' . $doc . '%'; }
    if ($est !== '') { $where[] = 'v.estado = ?'; $params[] = $est; }
    if ($emp !== '') { $where[] = '(u.nombre LIKE ? OR u.id = ?)'; $params[] = '%' . $emp . '%'; $params[] = $emp; }
        $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

    $sql = "SELECT v.id, vi.documento, v.departamento, v.fecha, v.salida, v.motivo, v.estado, u.nombre AS empleado
        FROM visitas v
        LEFT JOIN visitantes vi ON vi.id = v.visitante_id
        LEFT JOIN usuarios u ON u.id = v.anfitrion_id
                {$whereSql}
                ORDER BY v.fecha DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="visitas.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID','Documento','Departamento','Fecha entrada','Fecha salida','Motivo','Estado','Empleado']);
        foreach ($rows as $r) {
            fputcsv($out, [$r['id'],$r['documento'],$r['departamento'],$r['fecha'],$r['salida'],$r['motivo'],$r['estado'],$r['empleado']]);
        }
        fclose($out);
        exit;
    }

    /**
     * Eliminar visita (solo admin).
     */
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
    $_SESSION['flashes'][] = ['type' => 'success', 'msg' => 'Visita eliminada.'];
    header('Location: ' . $GLOBALS['basePath'] . '/visits');
        exit;
    }

    /**
     * Asegura que la tabla 'visitas' tenga las columnas usadas por la app (idempotente).
     */
    private function ensureVisitasColumns(): void
    {
        try { $this->pdo->exec("ALTER TABLE visitas ADD COLUMN salida DATETIME NULL AFTER fecha"); } catch (\Throwable $e) {}
        try { $this->pdo->exec("ALTER TABLE visitas ADD COLUMN estado ENUM('pendiente','autorizada','rechazada') NOT NULL DEFAULT 'pendiente' AFTER departamento"); } catch (\Throwable $e) {}
        try { $this->pdo->exec("ALTER TABLE visitas ADD COLUMN autorizado_por VARCHAR(64) NULL AFTER estado"); } catch (\Throwable $e) {}
        try { $this->pdo->exec("ALTER TABLE visitas ADD COLUMN anfitrion_id VARCHAR(64) NULL AFTER visitante_id"); } catch (\Throwable $e) {}
    }

    /**
     * Crea la tabla de notificaciones si no existe.
     */
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

    /**
     * Inserta una notificación para un usuario.
     */
    private function notifyUser(string $userId, string $title, string $body): void
    {
        try {
            $st = $this->pdo->prepare("INSERT INTO notifications (user_id, title, body) VALUES (?, ?, ?)");
            $st->execute([$userId, $title, $body]);
        } catch (\Throwable $e) {}
    }
}
