<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$user = $_SESSION['user'] ?? null;
if (!$user) {
    header('Location: ' . ($GLOBALS['basePath'] ?? '') . '/login');
    exit;
}
$rol = $user['rol'] ?? '';
$pdo = $pdo ?? (function() {
    // Carga PDO si no está disponible (para vistas directas)
    return require __DIR__ . '/../../src/Config/database.php';
})();

// Generar CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

// Helpers para optimizar
$bp = $GLOBALS['basePath'] ?? '';
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function csrf_valid($t){ return empty($_SESSION['csrf_token']) || hash_equals($_SESSION['csrf_token'], (string)$t); }
$isAdmin = ($rol === 'administrador');
$isEmp   = ($rol === 'empleado');
$isRecep = ($rol === 'recepcionista');

// Si el rol es empleado o recepcionista, delegar al panel específico (condicional para evitar fatal).
if ($rol === 'empleado') {
    $alt = __DIR__ . '/panel_empleado.php';
    if (file_exists($alt)) { require $alt; return; }
}
if ($rol === 'recepcionista') {
    $alt = __DIR__ . '/panel_recepcionista.php';
    if (file_exists($alt)) { require $alt; return; }
}

// Definir secciones permitidas por rol (equivale a “panel por rol” sin crear archivos nuevos)
$allowedByRole = [
  'administrador'   => ['dashboard','usuarios','visitantes','visitas','perfil','cambiar'],
  'empleado'        => ['dashboard','visitas','perfil','cambiar'],
  'recepcionista'   => ['dashboard','visitantes','visitas','perfil','cambiar'],
];
if (!isset($allowedSections) || !is_array($allowedSections)) {
    $allowedSections = $allowedByRole[$rol] ?? ['perfil'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel principal</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($bp) ?>/assets/css/app.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-gray-100">
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar py-4 px-2">
            <div class="text-center mb-4">
                <span class="fw-bold fs-5">VisitaSegura</span>
                <div class="small mt-1"><?= htmlspecialchars($user['nombre']) ?> <span class="badge bg-info"><?= htmlspecialchars($rol) ?></span></div>
            </div>
            <ul class="nav flex-column">
                <?php
                $menu = [
                  'dashboard' => 'Dashboard',
                  'usuarios'  => 'Usuarios',
                  'visitantes'=> 'Visitantes',
                  'visitas'   => 'Visitas',
                  'perfil'    => 'Perfil',
                  'cambiar'   => 'Cambiar contraseña'
                ];
                $current = $_GET['section'] ?? '';
                foreach ($menu as $key => $label) {
                    if (!in_array($key, $allowedSections, true)) continue;
                    echo '<li class="nav-item mb-2"><a class="nav-link'.($current===$key?' active':'').'" href="?section='.h($key).'">'.h($label).'</a></li>';
                }
                ?>
                <li class="nav-item mt-4">
                    <a class="nav-link text-danger" href="<?= h($bp) ?>/logout">Cerrar sesión</a>
                </li>
            </ul>
        </nav>
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <?php include __DIR__ . '/partials/ui/toasts.php'; ?>
                        <?php
                            // Cabecera reutilizable (breadcrumb + buscador opcional)
                            $section = $_GET['section'] ?? 'dashboard';
                            $labelsHdr = [
                                'dashboard'=>'Dashboard',
                                'usuarios'=>'Usuarios',
                                'visitantes'=>'Visitantes',
                                'visitas'=>'Visitas',
                                'perfil'=>'Perfil',
                                'cambiar'=>'Cambiar contraseña'
                            ];
                            $pageTitle = $labelsHdr[$section] ?? 'Panel';
                            $breadcrumbs = [ ['label' => $pageTitle] ];
                            $showSearch = in_array($section, ['usuarios','visitantes','visitas'], true);
                            include __DIR__ . '/partials/ui/header.php';
                        ?>
            <?php
            $section = $_GET['section'] ?? 'dashboard';
            // Si la sección no está permitida para el rol, restringe y vuelve a dashboard
            if (!in_array($section, $allowedSections, true)) {
                echo '<div class="alert alert-warning">Acceso restringido.</div>';
                $section = 'dashboard';
            }
             if (in_array($rol, ['administrador','empleado','recepcionista'])) {
                switch ($section) {
                    case 'dashboard':
                        // Dashboard real
                        $totalVisitas = $pdo->query("SELECT COUNT(*) FROM visitas")->fetchColumn();
                        $totalVisitantes = $pdo->query("SELECT COUNT(*) FROM visitantes")->fetchColumn();
                        $totalUsuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
                        echo '<h2>Dashboard</h2>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4"><div class="card text-bg-primary"><div class="card-body"><h5 class="card-title">Visitas</h5><p class="card-text fs-3">'.$totalVisitas.'</p></div></div></div>
                            <div class="col-md-4"><div class="card text-bg-success"><div class="card-body"><h5 class="card-title">Visitantes</h5><p class="card-text fs-3">'.$totalVisitantes.'</p></div></div></div>
                            <div class="col-md-4"><div class="card text-bg-info"><div class="card-body"><h5 class="card-title">Usuarios</h5><p class="card-text fs-3">'.$totalUsuarios.'</p></div></div></div>
                        </div>';
                        break;

                    case 'usuarios':
                    // Solo admin puede acceder a gestión de usuarios
                    if (!$isAdmin) {
                        echo '<div class="alert alert-warning">Acceso restringido.</div>';
                        break;
                    }
                        // Procesar POST de modales (editar/eliminar)
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            if (isset($_POST['edit_usuario_id'])) {
                                $csrf = $_POST['csrf_token'] ?? '';
                                if (!csrf_valid($csrf)) {
                                    echo '<div class="alert alert-danger">Solicitud inválida (CSRF).</div>';
                                } else {
                                    $id = $_POST['edit_usuario_id'] ?? '';
                                    $nombre = trim($_POST['edit_usuario_nombre'] ?? '');
                                    $documento = trim($_POST['edit_usuario_documento'] ?? '');
                                    $correo = trim($_POST['edit_usuario_correo'] ?? '');
                                    $rolForm = $_POST['edit_usuario_rol'] ?? '';
                                    if ($id && $nombre && $documento && $correo && $rolForm) {
                                        $pdo->prepare("UPDATE usuarios SET nombre=?, documento=?, correo=?, rol=? WHERE id=?")
                                            ->execute([$nombre, $documento, $correo, $rolForm, $id]);
                                        header('Location: ?section=usuarios'); exit;
                                    } else {
                                        echo '<div class="alert alert-danger">Todos los campos son obligatorios.</div>';
                                    }
                                }
                            } elseif (isset($_POST['delete_usuario_id'])) {
                                $csrf = $_POST['csrf_token'] ?? '';
                                if (!csrf_valid($csrf)) {
                                    echo '<div class="alert alert-danger">Solicitud inválida (CSRF).</div>';
                                } else {
                                    $id = $_POST['delete_usuario_id'] ?? '';
                                    if ($id) {
                                        $pdo->prepare("DELETE FROM usuarios WHERE id=?")->execute([$id]);
                                        header('Location: ?section=usuarios'); exit;
                                    }
                                }
                            }
                        }
                        // Gestión de usuarios real (listado, crear, editar, eliminar)
                        // Crear usuario
                        if (isset($_GET['action']) && $_GET['action'] === 'crear') {
                            // Formulario de creación
                            ?>
                            <h2>Crear usuario</h2>
                            <form method="post" action="?section=usuarios&action=crear">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <div class="mb-3">
                                    <label class="form-label">Nombre completo</label>
                                    <input type="text" name="nombre" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Documento</label>
                                    <input type="text" name="documento" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Correo</label>
                                    <input type="email" name="correo" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contraseña</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Rol</label>
                                    <select name="rol" class="form-select" required>
                                        <option value="administrador">Administrador</option>
                                        <option value="empleado">Empleado</option>
                                        <option value="recepcionista">Recepcionista</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href="?section=usuarios" class="btn btn-secondary">Cancelar</a>
                            </form>
                            <?php
                            // Procesar creación
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                $nombre    = trim($_POST['nombre'] ?? '');
                                $documento = trim($_POST['documento'] ?? '');
                                $correo    = trim($_POST['correo'] ?? '');
                                $password  = $_POST['password'] ?? '';
                                $rolForm   = $_POST['rol'] ?? '';
                                if ($nombre && $documento && $correo && $password && $rolForm) {
                                    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
                                    $stmt->execute([$correo]);
                                    if (!$stmt->fetch()) {
                                        $id   = bin2hex(random_bytes(16));
                                        $hash = password_hash($password, PASSWORD_DEFAULT);
                                        // created_at usa DEFAULT CURRENT_TIMESTAMP en la BD
                                        $pdo->prepare("INSERT INTO usuarios (id, nombre, documento, correo, hash_contrasena, rol) VALUES (?, ?, ?, ?, ?, ?)
")
                                            ->execute([$id, $nombre, $documento, $correo, $hash, $rolForm]);
                                        echo '<div class="alert alert-success mt-3">Usuario creado correctamente.</div>';
                                    } else {
                                        echo '<div class="alert alert-danger mt-3">El correo ya está registrado.</div>';
                                    }
                                } else {
                                    echo '<div class="alert alert-danger mt-3">Todos los campos son obligatorios.</div>';
                                }
                            }
                        } elseif (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'editar') {
                            // Formulario de edición
                            $id = $_GET['id'];
                            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
                            $stmt->execute([$id]);
                            $usuario = $stmt->fetch();
                            if ($usuario) {
                            ?>
                            <h2>Editar usuario</h2>
                            <form method="post" action="?section=usuarios&action=editar&id=<?= urlencode($id) ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <div class="mb-3">
                                    <label class="form-label">Nombre completo</label>
                                    <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($usuario['nombre']) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Documento</label>
                                    <input type="text" name="documento" class="form-control" required value="<?= htmlspecialchars($usuario['documento'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Correo</label>
                                    <input type="email" name="correo" class="form-control" required value="<?= htmlspecialchars($usuario['correo']) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Rol</label>
                                    <select name="rol" class="form-select" required>
                                        <option value="administrador" <?= $usuario['rol']=='administrador'?'selected':'' ?>>Administrador</option>
                                        <option value="empleado" <?= $usuario['rol']=='empleado'?'selected':'' ?>>Empleado</option>
                                        <option value="recepcionista" <?= $usuario['rol']=='recepcionista'?'selected':'' ?>>Recepcionista</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                <a href="?section=usuarios" class="btn btn-secondary">Cancelar</a>
                            </form>
                            <?php
                            // Procesar edición
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                $nombre    = trim($_POST['nombre'] ?? '');
                                $documento = trim($_POST['documento'] ?? '');
                                $correo    = trim($_POST['correo'] ?? '');
                                $rolForm   = $_POST['rol'] ?? '';
                                if ($nombre && $documento && $correo && $rolForm) {
                                    $pdo->prepare("UPDATE usuarios SET nombre=?, documento=?, correo=?, rol=? WHERE id=?")
                                        ->execute([$nombre, $documento, $correo, $rolForm, $id]);
                                    echo '<div class="alert alert-success mt-3">Usuario actualizado correctamente.</div>';
                                } else {
                                    echo '<div class="alert alert-danger mt-3">Todos los campos son obligatorios.</div>';
                                }
                            }
                            } else {
                                echo '<div class="alert alert-danger">Usuario no encontrado.</div>';
                            }
                        } elseif (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'eliminar') {
                            // Eliminar usuario
                            $id = $_GET['id'];
                            $pdo->prepare("DELETE FROM usuarios WHERE id=?")->execute([$id]);
                            echo '<div class="alert alert-success">Usuario eliminado correctamente.</div>';
                            echo '<a href="?section=usuarios" class="btn btn-secondary mt-2">Volver</a>';
                        } else {
                            // Listado de usuarios
                            $usuarios = $pdo->query("SELECT id, nombre, documento, correo, rol, created_at FROM usuarios ORDER BY created_at DESC")->fetchAll();
                            echo '<h2>Gestión de usuarios</h2>';
                            echo '<a href="?section=usuarios&action=crear" class="btn btn-success mb-3"><i class="bi bi-plus-lg me-1"></i>Nuevo usuario</a>';
                            echo '<div class="table-responsive"><table class="table table-striped table-bordered table-hover align-middle bg-white shadow-sm content-card">';
                            echo '<thead class="table-light"><tr>
                                <th>Nombre completo</th>
                                <th>Documento</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Creado</th>
                                <th>Acciones</th>
                            </tr></thead><tbody>';
                            foreach ($usuarios as $u) {
                                echo '<tr>
                                    <td>'.htmlspecialchars($u['nombre']).'</td>
                                    <td>'.htmlspecialchars($u['documento'] ?? '').'</td>
                                    <td>'.htmlspecialchars($u['correo']).'</td>
                                    <td>'.htmlspecialchars($u['rol']).'</td>
                                    <td>'.htmlspecialchars($u['created_at'] ? date('Y-m-d H:i', strtotime($u['created_at'])) : '').'</td>
                                    <td>
                                       <div class="btn-group btn-group-sm" role="group">
                                           <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditarUsuario"
                                               data-id="'.htmlspecialchars($u['id']).'"
                                               data-nombre="'.htmlspecialchars($u['nombre']).'"
                                               data-documento="'.htmlspecialchars($u['documento'] ?? '').'"
                                               data-correo="'.htmlspecialchars($u['correo']).'"
                                               data-rol="'.htmlspecialchars($u['rol']).'">
                                               <i class="bi bi-pencil-square"></i> Editar
                                           </button>
                                           <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalEliminarUsuario"
                                               data-id="'.htmlspecialchars($u['id']).'"
                                               data-nombre="'.htmlspecialchars($u['nombre']).'">
                                               <i class="bi bi-trash"></i> Eliminar
                                           </button>
                                       </div>
                                    </td>
                                </tr>';
                            }
                            echo '</tbody></table></div>';
                            // Modal editar usuario
                            ?>
                            <div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
                              <div class="modal-dialog">
                                <div class="modal-content">
                                  <form method="post" id="formEditarUsuario">
                                    <div class="modal-header">
                                      <h5 class="modal-title" id="modalEditarUsuarioLabel">Editar usuario</h5>
                                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body">
                                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                      <input type="hidden" name="edit_usuario_id" id="editUsuarioId">
                                      <div class="mb-3">
                                        <label class="form-label">Nombre completo</label>
                                        <input type="text" name="edit_usuario_nombre" id="editUsuarioNombre" class="form-control" required>
                                      </div>
                                      <div class="mb-3">
                                        <label class="form-label">Documento</label>
                                        <input type="text" name="edit_usuario_documento" id="editUsuarioDocumento" class="form-control" required>
                                      </div>
                                      <div class="mb-3">
                                        <label class="form-label">Correo</label>
                                        <input type="email" name="edit_usuario_correo" id="editUsuarioCorreo" class="form-control" required>
                                      </div>
                                      <div class="mb-3">
                                        <label class="form-label">Rol</label>
                                        <select name="edit_usuario_rol" id="editUsuarioRol" class="form-select" required>
                                            <option value="administrador">Administrador</option>
                                            <option value="empleado">Empleado</option>
                                            <option value="recepcionista">Recepcionista</option>
                                        </select>
                                      </div>
                                    </div>
                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                      <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                    </div>
                                  </form>
                                </div>
                              </div>
                            </div>
                            <div class="modal fade" id="modalEliminarUsuario" tabindex="-1" aria-labelledby="modalEliminarUsuarioLabel" aria-hidden="true">
                              <div class="modal-dialog">
                                <div class="modal-content">
                                  <form method="post" id="formEliminarUsuario">
                                    <div class="modal-header">
                                      <h5 class="modal-title" id="modalEliminarUsuarioLabel">Eliminar usuario</h5>
                                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body">
                                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                      <input type="hidden" name="delete_usuario_id" id="deleteUsuarioId">
                                      <p>¿Seguro que deseas eliminar a <span id="deleteUsuarioNombre"></span>?</p>
                                    </div>
                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                      <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </div>
                                  </form>
                                </div>
                              </div>
                            </div>
                            <script>
                            var modalEditarUsuario = document.getElementById('modalEditarUsuario');
                            modalEditarUsuario.addEventListener('show.bs.modal', function (event) {
                                var button = event.relatedTarget;
                                document.getElementById('editUsuarioId').value = button.getAttribute('data-id');
                                document.getElementById('editUsuarioNombre').value = button.getAttribute('data-nombre');
                                document.getElementById('editUsuarioDocumento').value = button.getAttribute('data-documento');
                                document.getElementById('editUsuarioCorreo').value = button.getAttribute('data-correo');
                                document.getElementById('editUsuarioRol').value = button.getAttribute('data-rol');
                            });
                            var modalEliminarUsuario = document.getElementById('modalEliminarUsuario');
                            modalEliminarUsuario.addEventListener('show.bs.modal', function (event) {
                                var button = event.relatedTarget;
                                document.getElementById('deleteUsuarioId').value = button.getAttribute('data-id');
                                document.getElementById('deleteUsuarioNombre').textContent = button.getAttribute('data-nombre');
                            });
                            </script>
                            <?php
                        } // cierra el if/elseif/else de la sección 'usuarios'
                        break;

                    case 'visitantes':
                        // Procesar POST de modales (editar/eliminar)
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            if (isset($_POST['edit_visitante_id'])) {
                                if (!$isAdmin) { echo '<div class="alert alert-warning">Acción no permitida.</div>'; }
                                else {
                                    $csrf = $_POST['csrf_token'] ?? '';
                                    if (!csrf_valid($csrf)) {
                                        echo '<div class="alert alert-danger">Solicitud inválida (CSRF).</div>';
                                    } else {
                                        $id = $_POST['edit_visitante_id'] ?? '';
                                        $nombre = trim($_POST['edit_visitante_nombre'] ?? '');
                                        $documento = trim($_POST['edit_visitante_documento'] ?? '');
                                        $empresa = trim($_POST['edit_visitante_empresa'] ?? '');
                                        if ($id && $nombre && $documento) {
                                            $pdo->prepare("UPDATE visitantes SET nombre=?, documento=?, empresa=? WHERE id=?")
                                                ->execute([$nombre, $documento, $empresa, $id]);
                                            header('Location: ?section=visitantes'); exit;
                                        } else {
                                            echo '<div class="alert alert-danger">Nombre y documento son obligatorios.</div>';
                                        }
                                    }
                                }
                            } elseif (isset($_POST['delete_visitante_id'])) {
                                if (!$isAdmin) { echo '<div class="alert alert-warning">Acción no permitida.</div>'; }
                                else {
                                    $csrf = $_POST['csrf_token'] ?? '';
                                    if (!csrf_valid($csrf)) {
                                        echo '<div class="alert alert-danger">Solicitud inválida (CSRF).</div>';
                                    } else {
                                        $id = $_POST['delete_visitante_id'] ?? '';
                                        if ($id) {
                                            try {
                                                $pdo->prepare("DELETE FROM visitantes WHERE id=?")->execute([$id]);
                                                header('Location: ?section=visitantes'); exit;
                                            } catch (\PDOException $e) {
                                                echo '<div class="alert alert-danger">No se puede eliminar: el visitante tiene visitas asociadas.</div>';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        // CRUD de visitantes desde el panel
                        // Crear visitante
                        if (isset($_GET['action']) && $_GET['action'] === 'crear') {
                            if (!($isAdmin || $isRecep)) {
                                echo '<div class="alert alert-warning">No tienes permisos para crear visitantes.</div><a href="?section=visitantes" class="btn btn-secondary mt-2">Volver</a>';
                                break;
                            }
                            // Procesar creación solo si es POST
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                $nombre = trim($_POST['nombre'] ?? '');
                                $documento = trim($_POST['documento'] ?? '');
                                $empresa = trim($_POST['empresa'] ?? '');
                                if ($nombre && $documento) {
                                    $pdo->prepare("INSERT INTO visitantes (nombre, documento, empresa) VALUES (?, ?, ?)")
                                        ->execute([$nombre, $documento, $empresa]);
                                    header('Location: ?section=visitantes');
                                    exit;
                                } else {
                                    echo '<div class="alert alert-danger mt-3">Nombre y documento son obligatorios.</div>';
                                }
                            }
                            ?>
                            <h2>Agregar visitante</h2>
                            <form method="post" action="?section=visitantes&action=crear">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <div class="mb-3">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" name="nombre" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Documento</label>
                                    <input type="text" name="documento" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Empresa</label>
                                    <input type="text" name="empresa" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href="?section=visitantes" class="btn btn-secondary">Cancelar</a>
                            </form>
                            <?php
                        } elseif (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'editar') {
                            // Formulario de edición
                            $id = $_GET['id'];
                            $stmt = $pdo->prepare("SELECT * FROM visitantes WHERE id = ?");
                            $stmt->execute([$id]);
                            $visitante = $stmt->fetch();
                            if ($visitante) {
                                // Procesar edición solo si es POST
                                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                    $nombre = trim($_POST['nombre'] ?? '');
                                    $documento = trim($_POST['documento'] ?? '');
                                    $empresa = trim($_POST['empresa'] ?? '');
                                    if ($nombre && $documento) {
                                        $pdo->prepare("UPDATE visitantes SET nombre=?, documento=?, empresa=? WHERE id=?")
                                            ->execute([$nombre, $documento, $empresa, $id]);
                                        header('Location: ?section=visitantes');
                                        exit;
                                    } else {
                                        echo '<div class="alert alert-danger mt-3">Nombre y documento son obligatorios.</div>';
                                    }
                                }
                            ?>
                            <h2>Editar visitante</h2>
                            <form method="post" action="?section=visitantes&action=editar&id=<?= urlencode($id) ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <div class="mb-3">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($visitante['nombre']) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Documento</label>
                                    <input type="text" name="documento" class="form-control" required value="<?= htmlspecialchars($visitante['documento']) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Empresa</label>
                                    <input type="text" name="empresa" class="form-control" value="<?= htmlspecialchars($visitante['empresa']) ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                <a href="?section=visitantes" class="btn btn-secondary">Cancelar</a>
                            </form>
                            <?php
                            } else {
                                echo '<div class="alert alert-danger">Visitante no encontrado.</div>';
                            }
                        } elseif (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'eliminar') {
                            // Eliminar visitante
                            $id = $_GET['id'];
                            try {
                                $pdo->prepare("DELETE FROM visitantes WHERE id=?")->execute([$id]);
                                header('Location: ?section=visitantes');
                                exit;
                            } catch (\PDOException $e) {
                                echo '<div class="alert alert-danger">No se puede eliminar: el visitante tiene visitas asociadas.</div>';
                                echo '<a href="?section=visitantes" class="btn btn-secondary mt-2">Volver</a>';
                            }
                        } else {
                            // Listado de visitantes
                            $visitantes = $pdo->query("SELECT * FROM visitantes ORDER BY nombre")->fetchAll();
                            echo '<h2>Visitantes</h2>';
                            if ($isAdmin || $isRecep) {
                                echo '<a href="?section=visitantes&action=crear" class="btn btn-success mb-3"><i class="bi bi-person-plus-fill me-1"></i>Nuevo visitante</a>';
                            }
                            echo '<div class="table-responsive"><table class="table table-striped table-bordered table-hover align-middle bg-white shadow-sm content-card">';
                            echo '<thead class="table-light"><tr>
                            <th>Nombre visitante</th>
                            <th>Documento</th>
                            <th>Empresa</th>
                            <th>Acciones</th>
                        </tr></thead><tbody>';
                        foreach ($visitantes as $v) {
                            echo '<tr>';
                            echo '<td>'.htmlspecialchars($v['nombre']).'</td>';
                            echo '<td>'.htmlspecialchars($v['documento']).'</td>';
                            echo '<td>'.htmlspecialchars($v['empresa']).'</td>';
                            echo '<td>';
                            echo '  <div class="btn-group btn-group-sm" role="group">';
                            // Ver visitas
                            echo '    <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalVerVisitas"'
                                .' data-id="'.htmlspecialchars($v['id']).'" data-nombre="'.htmlspecialchars($v['nombre']).'">'
                                .' <i class="bi bi-eye"></i> Ver visitas'
                                .' </button>';
                            // Solo administrador puede editar/eliminar
                            if ($rol === 'administrador') {
                                echo '    <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditarVisitante"'
                                    .' data-id="'.htmlspecialchars($v['id']).'"'
                                    .' data-nombre="'.htmlspecialchars($v['nombre']).'"'
                                    .' data-documento="'.htmlspecialchars($v['documento']).'"'
                                    .' data-empresa="'.htmlspecialchars($v['empresa']).'">'
                                    .' <i class="bi bi-pencil-square"></i> Editar'
                                    .' </button>';
                                echo '    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalEliminarVisitante"'
                                    .' data-id="'.htmlspecialchars($v['id']).'" data-nombre="'.htmlspecialchars($v['nombre']).'">'
                                    .' <i class="bi bi-trash"></i> Eliminar'
                                    .' </button>';
                            }
                            echo '  </div>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table></div>';
                        // Modal editar visitante
                        ?>
                        <div class="modal fade" id="modalEditarVisitante" tabindex="-1" aria-labelledby="modalEditarVisitanteLabel" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <form method="post" id="formEditarVisitante">
                                <div class="modal-header">
                                  <h5 class="modal-title" id="modalEditarVisitanteLabel">Editar visitante</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                  <input type="hidden" name="edit_visitante_id" id="editVisitanteId">
                                  <div class="mb-3">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" name="edit_visitante_nombre" id="editVisitanteNombre" class="form-control" required>
                                  </div>
                                  <div class="mb-3">
                                    <label class="form-label">Documento</label>
                                    <input type="text" name="edit_visitante_documento" id="editVisitanteDocumento" class="form-control" required>
                                  </div>
                                  <div class="mb-3">
                                    <label class="form-label">Empresa</label>
                                    <input type="text" name="edit_visitante_empresa" id="editVisitanteEmpresa" class="form-control">
                                  </div>
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                  <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                        <div class="modal fade" id="modalEliminarVisitante" tabindex="-1" aria-labelledby="modalEliminarVisitanteLabel" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <form method="post" id="formEliminarVisitante">
                                <div class="modal-header">
                                  <h5 class="modal-title" id="modalEliminarVisitanteLabel">Eliminar visitante</h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                  <input type="hidden" name="delete_visitante_id" id="deleteVisitanteId">
                                  <p>¿Seguro que deseas eliminar a <span id="deleteVisitanteNombre"></span>?</p>
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                  <button type="submit" class="btn btn-danger">Eliminar</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                        <!-- Modal Ver visitas (iframe) -->
                        <div class="modal fade" id="modalVerVisitas" tabindex="-1" aria-labelledby="modalVerVisitasLabel" aria-hidden="true">
                          <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="modalVerVisitasLabel">Visitas de <span id="verVisitasNombre"></span></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                              </div>
                              <div class="modal-body p-0">
                                <iframe id="verVisitasIframe" src="" style="border:0;width:100%;height:70vh;"></iframe>
                              </div>
                            </div>
                          </div>
                        </div>
                        <script>
                        var modalEditarVisitante = document.getElementById('modalEditarVisitante');
                        modalEditarVisitante.addEventListener('show.bs.modal', function (event) {
                            var button = event.relatedTarget;
                            document.getElementById('editVisitanteId').value = button.getAttribute('data-id');
                            document.getElementById('editVisitanteNombre').value = button.getAttribute('data-nombre');
                            document.getElementById('editVisitanteDocumento').value = button.getAttribute('data-documento');
                            document.getElementById('editVisitanteEmpresa').value = button.getAttribute('data-empresa');
                        });
                        var modalEliminarVisitante = document.getElementById('modalEliminarVisitante');
                        modalEliminarVisitante.addEventListener('show.bs.modal', function (event) {
                            var button = event.relatedTarget;
                            document.getElementById('deleteVisitanteId').value = button.getAttribute('data-id');
                            document.getElementById('deleteVisitanteNombre').textContent = button.getAttribute('data-nombre');
                        });
                        var modalVerVisitas = document.getElementById('modalVerVisitas');
                        modalVerVisitas.addEventListener('show.bs.modal', function (event) {
                            var button = event.relatedTarget;
                            var id = button.getAttribute('data-id');
                            var nombre = button.getAttribute('data-nombre') || '';
                            document.getElementById('verVisitasNombre').textContent = nombre;
                            var bp = '<?= $GLOBALS['basePath'] ?>';
                            document.getElementById('verVisitasIframe').src = (bp ? bp : '') + '/visitantes/' + encodeURIComponent(id);
                        });
                        </script>
                        <?php
                    } // cierra el if/elseif/else de la sección 'visitantes'
                    break;

                    case 'visitas': // <-- reemplaza el elseif ($section === 'visitas') por este case
                        // Procesar POST de modales (editar/eliminar)
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            if (isset($_POST['edit_visita_id'])) {
                                if (!$isAdmin) { echo '<div class="alert alert-warning">Acción no permitida.</div>'; }
                                else {
                                    $csrf = $_POST['csrf_token'] ?? '';
                                    if (!csrf_valid($csrf)) {
                                        echo '<div class="alert alert-danger">Solicitud inválida (CSRF).</div>';
                                    } else {
                                        $id = $_POST['edit_visita_id'] ?? '';
                                        $motivo = trim($_POST['edit_visita_motivo'] ?? '');
                                        $fecha = trim($_POST['edit_visita_fecha'] ?? '');
                                        $visitante = trim($_POST['edit_visita_visitante'] ?? '');
                                        $departamento = trim($_POST['edit_visita_departamento'] ?? '');
                                        $salida = trim($_POST['edit_visita_salida'] ?? '');
                                        if ($id && $motivo && $fecha && $visitante && $departamento) {
                                            $pdo->prepare("UPDATE visitas SET motivo=?, fecha=?, visitante_id=?, departamento=?, salida=? WHERE id=?")
                                                ->execute([$motivo, $fecha, $visitante, $departamento, ($salida !== '' ? $salida : null), $id]);
                                            header('Location: ?section=visitas'); exit;
                                        } else {
                                            echo '<div class="alert alert-danger">Todos los campos son obligatorios.</div>';
                                        }
                                    }
                                }
                            } elseif (isset($_POST['delete_visita_id'])) {
                                if (!$isAdmin) { echo '<div class="alert alert-warning">Acción no permitida.</div>'; }
                                else {
                                    $csrf = $_POST['csrf_token'] ?? '';
                                    if (!csrf_valid($csrf)) {
                                        echo '<div class="alert alert-danger">Solicitud inválida (CSRF).</div>';
                                    } else {
                                        $id = $_POST['delete_visita_id'] ?? '';
                                        if ($id) {
                                            $pdo->prepare("DELETE FROM visitas WHERE id=?")->execute([$id]);
                                            header('Location: ?section=visitas'); exit;
                                        }
                                    }
                                }
                            }
                        }
                        // CRUD de visitas desde el panel
                        // Crear visita
                        if (isset($_GET['action']) && $_GET['action'] === 'crear') {
                            if (!($isAdmin || $isRecep)) {
                                echo '<div class="alert alert-warning">No tienes permisos para crear visitas.</div><a href="?section=visitas" class="btn btn-secondary mt-2">Volver</a>';
                                break;
                            }
                            // Obtener visitantes para el select
                            $visitantes = $pdo->query("SELECT id, nombre FROM visitantes ORDER BY nombre")->fetchAll();
                            // Procesar creación
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                $motivo = trim($_POST['motivo'] ?? '');
                                $fecha = trim($_POST['fecha'] ?? '');
                                $visitante = trim($_POST['visitante'] ?? '');
                                $departamento = trim($_POST['departamento'] ?? '');
                                if ($motivo && $fecha && $visitante && $departamento) {
                                    $pdo->prepare("INSERT INTO visitas (visitante_id, fecha, motivo, departamento) VALUES (?, ?, ?, ?)")
                                        ->execute([$visitante, $fecha, $motivo, $departamento]);
                                    header('Location: ?section=visitas');
                                    exit;
                                } else {
                                    echo '<div class="alert alert-danger mt-3">Todos los campos son obligatorios.</div>';
                                }
                            }
                            ?>
                            <h2>Crear visita</h2>
                            <form method="post" action="?section=visitas&action=crear">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <div class="mb-3">
                                    <label class="form-label">Motivo</label>
                                    <input type="text" name="motivo" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Fecha</label>
                                    <input type="datetime-local" name="fecha" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Departamento</label>
                                    <input type="text" name="departamento" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Visitante</label>
                                    <select name="visitante" class="form-select" required>
                                        <option value="">Seleccione un visitante</option>
                                        <?php foreach ($visitantes as $v): ?>
                                            <option value="<?= htmlspecialchars($v['id']) ?>">
                                                <?= htmlspecialchars($v['nombre']) ?> (ID: <?= htmlspecialchars($v['id']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href="?section=visitas" class="btn btn-secondary">Cancelar</a>
                            </form>
                            <?php
                        } elseif (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'editar') {
                            // Formulario de edición
                            $id = $_GET['id'];
                            $stmt = $pdo->prepare("SELECT * FROM visitas WHERE id = ?");
                            $stmt->execute([$id]);
                            $visita = $stmt->fetch();
                            $visitantes = $pdo->query("SELECT id, nombre FROM visitantes ORDER BY nombre")->fetchAll();
                            if ($visita) {
                                // Procesar edición
                                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                    $motivo = trim($_POST['motivo'] ?? '');
                                    $fecha = trim($_POST['fecha'] ?? '');
                                    $visitante = trim($_POST['visitante'] ?? '');
                                    $departamento = trim($_POST['departamento'] ?? '');
                                    if ($motivo && $fecha && $visitante && $departamento) {
                                        $pdo->prepare("UPDATE visitas SET motivo=?, fecha=?, visitante_id=?, departamento=? WHERE id=?")
                                            ->execute([$motivo, $fecha, $visitante, $departamento, $id]);
                                        header('Location: ?section=visitas');
                                        exit;
                                    } else {
                                        echo '<div class="alert alert-danger mt-3">Todos los campos son obligatorios.</div>';
                                    }
                                }
                            ?>
                            <h2>Editar visita</h2>
                            <form method="post" action="?section=visitas&action=editar&id=<?= urlencode($id) ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <div class="mb-3">
                                    <label class="form-label">Motivo</label>
                                    <input type="text" name="motivo" class="form-control" required value="<?= htmlspecialchars($visita['motivo']) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Fecha</label>
                                    <input type="datetime-local" name="fecha" class="form-control" required value="<?= date('Y-m-d\TH:i', strtotime($visita['fecha'])) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Departamento</label>
                                    <input type="text" name="departamento" class="form-control" required value="<?= htmlspecialchars($visita['departamento'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Visitante</label>
                                    <select name="visitante" class="form-select" required>
                                        <option value="">Seleccione un visitante</option>
                                        <?php foreach ($visitantes as $v): ?>
                                            <option value="<?= htmlspecialchars($v['id']) ?>" <?= $visita['visitante_id'] == $v['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($v['nombre']) ?> (ID: <?= htmlspecialchars($v['id']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                <a href="?section=visitas" class="btn btn-secondary">Cancelar</a>
                            </form>
                            <?php
                            } else {
                                echo '<div class="alert alert-danger">Visita no encontrada.</div>';
                            }
                        } elseif (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'eliminar') {
                            // Eliminar visita
                            $id = $_GET['id'];
                            $pdo->prepare("DELETE FROM visitas WHERE id=?")->execute([$id]);
                            header('Location: ?section=visitas');
                            exit;
                        } else {
                            // Listado de visitas
                            $visitas = $pdo->query("SELECT * FROM visitas ORDER BY fecha DESC")->fetchAll();
                            // Obtener documentos de visitantes
                            $ids = array_column($visitas, 'visitante_id');
                            $documentos = [];
                            if ($ids) {
                                $in = implode(',', array_fill(0, count($ids), '?'));
                                $stmt = $pdo->prepare("SELECT id, documento FROM visitantes WHERE id IN ($in)");
                                $stmt->execute($ids);
                                foreach ($stmt->fetchAll() as $row) {
                                    $documentos[$row['id']] = $row['documento'];
                                }
                            }
                            // Cargar visitantes para el select del modal de edición
                            $visitantes = $pdo->query("SELECT id, nombre FROM visitantes ORDER BY nombre")->fetchAll();

                            echo '<h2>Visitas</h2>';
                            echo '<div class="d-flex gap-2 mb-3">';
                           if ($isAdmin || $isRecep || $isEmp) {
                                echo '<a href="?section=visitas&action=crear" class="btn btn-success"><i class="bi bi-calendar-plus me-1"></i>Nueva visita</a>';
                            }
                            echo '<a href="'.h($bp).'/visits/export" class="btn btn-outline-secondary">
                                    <i class="bi bi-filetype-csv me-1"></i>Exportar CSV
                                  </a></div>';
                            echo '<div class="table-responsive"><table class="table table-striped table-bordered table-hover align-middle bg-white shadow-sm content-card">';
                            echo '<thead class="table-light"><tr>
                                <th>ID</th>
                                <th>Documento</th>
                                <th>Fecha</th>
                                <th>Salida</th>
                                <th>Motivo</th>
                                <th>Departamento</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr></thead><tbody>';
                            foreach ($visitas as $v) {
                                echo '<tr>
                                    <td>'.htmlspecialchars($v['id']).'</td>
                                    <td>'.htmlspecialchars($documentos[$v['visitante_id']] ?? '-').'</td>
                                    <td>'.htmlspecialchars($v['fecha']).'</td>
                                    <td>'.htmlspecialchars($v['salida'] ?? '-').'</td>
                                    <td>'.htmlspecialchars($v['motivo']).'</td>
                                    <td>'.htmlspecialchars($v['departamento'] ?? '-').'</td>
                                    <td>'.
                                        (($v['estado'] ?? 'pendiente') === 'pendiente' ? '<span class="badge bg-warning text-dark">Pendiente</span>' :
                                        (($v['estado'] ?? '') === 'autorizada' ? '<span class="badge bg-success">Autorizada</span>' :
                                        (($v['estado'] ?? '') === 'rechazada' ? '<span class="badge bg-danger">Rechazada</span>' : '-')))
                                    .'</td>
                                    <td>
                                    <div class="d-flex flex-wrap gap-1">';
                            // Editar solo para admin
                            if ($rol === 'administrador') {
                                echo '<button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditarVisita"
                                    data-id="'.htmlspecialchars($v['id']).'"
                                    data-motivo="'.htmlspecialchars($v['motivo']).'"
                                    data-fecha="'.htmlspecialchars(date('Y-m-d\TH:i', strtotime($v['fecha']))).'" 
                                    data-visitante="'.htmlspecialchars($v['visitante_id']).'"
                                    data-departamento="'.htmlspecialchars($v['departamento'] ?? '').'"
                                    data-salida="'.htmlspecialchars(!empty($v['salida']) ? date('Y-m-d\TH:i', strtotime($v['salida'])) : '').'">
                                    <i class="bi bi-pencil-square"></i> Editar
                                </button>';
                            }
                            // Eliminar solo para admin
                            if ($rol === 'administrador') {
                                echo '<button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalEliminarVisita"
                                    data-id="'.htmlspecialchars($v['id']).'">
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>';
                            }
                            // Autorizar/Rechazar para admin y empleado si pendiente
                            if (($v['estado'] ?? 'pendiente') === 'pendiente' && ($isAdmin || $isEmp)) {
                                echo '<button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalAutorizarVisita"'
                                    .' data-id="'.htmlspecialchars($v['id']).'" data-decision="autorizar">'
                                    .'<i class="bi bi-check2-circle"></i> Autorizar'
                                    .'</button>';
                                echo '<button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalAutorizarVisita"'
                                    .' data-id="'.htmlspecialchars($v['id']).'" data-decision="rechazar">'
                                    .'<i class="bi bi-x-circle"></i> Rechazar'
                                    .'</button>';
                            }
                            // Marcar salida para admin y empleado si no tiene salida
                            if (empty($v['salida']) && ($isAdmin || $isEmp)) {
                                echo '<button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalConfirmSalida"'
                                    .' data-id="'.htmlspecialchars($v['id']).'">'
                                    .' <i class="bi bi-box-arrow-right"></i> Marcar salida'
                                    .'</button>';
                            }
                            echo '</div>
                                    </td>
                                </tr>';
                            }
                            echo '</tbody></table></div>';
                            // Modales de visitas (editar/eliminar)
                            ?>
                            <div class="modal fade" id="modalEditarVisita" tabindex="-1" aria-labelledby="modalEditarVisitaLabel" aria-hidden="true">
                              <div class="modal-dialog">
                                <div class="modal-content">
                                  <form method="post" id="formEditarVisita">
                                    <div class="modal-header">
                                      <h5 class="modal-title" id="modalEditarVisitaLabel">Editar visita</h5>
                                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body">
                                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                      <input type="hidden" name="edit_visita_id" id="editVisitaId">
                                      <div class="mb-3">
                                        <label class="form-label">Motivo</label>
                                        <input type="text" name="edit_visita_motivo" id="editVisitaMotivo" class="form-control" required>
                                      </div>
                                      <div class="mb-3">
                                        <label class="form-label">Fecha</label>
                                        <input type="datetime-local" name="edit_visita_fecha" id="editVisitaFecha" class="form-control" required>
                                      </div>
                                      <div class="mb-3">
                                        <label class="form-label">Departamento</label>
                                        <input type="text" name="edit_visita_departamento" id="editVisitaDepartamento" class="form-control" required>
                                      </div>
                                      <div class="mb-3">
                                        <label class="form-label">Salida (opcional)</label>
                                        <input type="datetime-local" name="edit_visita_salida" id="editVisitaSalida" class="form-control">
                                      </div>
                                      <div class="mb-3">
                                        <label class="form-label">Visitante</label>
                                        <select name="edit_visita_visitante" id="editVisitaVisitante" class="form-select" required>
                                          <option value="">Seleccione un visitante</option>
                                          <?php foreach ($visitantes as $vv): ?>
                                            <option value="<?= htmlspecialchars($vv['id']) ?>"><?= htmlspecialchars($vv['nombre']) ?> (ID: <?= htmlspecialchars($vv['id']) ?>)</option>
                                          <?php endforeach; ?>
                                        </select>
                                      </div>
                                    </div>
                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                      <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                    </div>
                                  </form>
                                </div>
                              </div>
                            </div>

                            <div class="modal fade" id="modalEliminarVisita" tabindex="-1" aria-labelledby="modalEliminarVisitaLabel" aria-hidden="true">
                              <div class="modal-dialog">
                                <div class="modal-content">
                                  <form method="post" id="formEliminarVisita">
                                    <div class="modal-header">
                                      <h5 class="modal-title" id="modalEliminarVisitaLabel">Eliminar visita</h5>
                                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body">
                                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                      <input type="hidden" name="delete_visita_id" id="deleteVisitaId">
                                      <p>¿Seguro que deseas eliminar esta visita?</p>
                                    </div>
                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                      <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </div>
                                  </form>
                                </div>
                              </div>
                            </div>

                            <?php include __DIR__ . '/partials/modals_visita_actions.php'; ?>
                            <script>
                            var modalEditarVisita = document.getElementById('modalEditarVisita');
                            if (modalEditarVisita) {
                              modalEditarVisita.addEventListener('show.bs.modal', function (event) {
                                var button = event.relatedTarget;
                                document.getElementById('editVisitaId').value = button.getAttribute('data-id');
                                document.getElementById('editVisitaMotivo').value = button.getAttribute('data-motivo');
                                document.getElementById('editVisitaFecha').value = button.getAttribute('data-fecha');
                                document.getElementById('editVisitaVisitante').value = button.getAttribute('data-visitante');
                                document.getElementById('editVisitaDepartamento').value = button.getAttribute('data-departamento') || '';
                                document.getElementById('editVisitaSalida').value = button.getAttribute('data-salida') || '';
                              });
                            }
                            var modalEliminarVisita = document.getElementById('modalEliminarVisita');
                            if (modalEliminarVisita) {
                              modalEliminarVisita.addEventListener('show.bs.modal', function (event) {
                                var button = event.relatedTarget;
                                document.getElementById('deleteVisitaId').value = button.getAttribute('data-id');
                              });
                            }
                            // (Los listeners de autorizar/salida están en el parcial incluido)
                            </script>
                            <?php
                        } // <-- cierra el else (listado) del case 'visitas'
                        break;

                    case 'perfil':
                        ?>
                        <h2>Perfil</h2>
                        <div class="card">
                          <div class="card-body">
                            <p><strong>Nombre:</strong> <?= htmlspecialchars($user['nombre']) ?></p>
                            <p><strong>Correo:</strong> <?= htmlspecialchars($user['correo']) ?></p>
                            <p><strong>Rol:</strong> <span class="badge bg-info"><?= htmlspecialchars($rol) ?></span></p>
                          </div>
                        </div>
                        <?php
                        break;

                    case 'cambiar':
                        // Procesar cambio de contraseña
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $csrf = $_POST['csrf_token'] ?? '';
                            $actual = $_POST['actual'] ?? '';
                            $nueva = $_POST['nueva'] ?? '';
                            $confirmar = $_POST['confirmar'] ?? '';
                            if (csrf_valid($csrf)) {
                                if ($nueva === $confirmar && strlen($nueva) >= 6) {
                                    $stmt = $pdo->prepare("SELECT hash_contrasena FROM usuarios WHERE id = ?");
                                    $stmt->execute([$user['id']]);
                                    $row = $stmt->fetch();
                                    if ($row && password_verify($actual, $row['hash_contrasena'])) {
                                        $hash = password_hash($nueva, PASSWORD_DEFAULT);
                                        $pdo->prepare("UPDATE usuarios SET hash_contrasena=? WHERE id=?")->execute([$hash, $user['id']]);
                                        echo '<div class="alert alert-success">Contraseña actualizada.</div>';
                                    } else {
                                        echo '<div class="alert alert-danger">La contraseña actual no es correcta.</div>';
                                    }
                                } else {
                                    echo '<div class="alert alert-danger">Las contraseñas no coinciden o son muy cortas (mínimo 6).</div>';
                                }
                            } else {
                                echo '<div class="alert alert-danger">Solicitud inválida (CSRF).</div>';
                            }
                        }
                        ?>
                        <h2>Cambiar contraseña</h2>
                        <form method="post" action="?section=cambiar" class="col-md-6 col-lg-5">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <div class="mb-3">
                                <label class="form-label">Contraseña actual</label>
                                <input type="password" name="actual" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nueva contraseña</label>
                                <input type="password" name="nueva" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirmar nueva contraseña</label>
                                <input type="password" name="confirmar" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                        </form>
                        <?php
                        break;

                    default:
                        echo '<h2>Bienvenido al panel</h2>';
                        break;
                } // <-- cierre correcto del switch
            } else {
                echo '<h2>Acceso restringido</h2><p>No tienes permisos para ver este panel.</p>';
            }
            ?>
        </main>
    </div>
</div>
<footer class="text-center text-muted small py-3 mt-4">
  &copy; <?= date('Y') ?> VisitaSegura. Todos los derechos reservados.
</footer>
</body>
</html>

