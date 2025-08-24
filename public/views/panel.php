<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$user = $_SESSION['user'] ?? null;
if (!$user) {
    header('Location: ' . ($GLOBALS['basePath'] ?? '') . '/login');
    exit;
}
$rol = $user['rol'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel principal</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .sidebar {
            min-height: 100vh;
            background: #212529;
            color: #fff;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
        }
        .sidebar a.active, .sidebar a:hover {
            background: #0d6efd;
            color: #fff;
        }
    </style>
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
                <li class="nav-item mb-2">
                    <a class="nav-link<?= ($_GET['section'] ?? '') === 'dashboard' ? ' active' : '' ?>" href="?section=dashboard">Dashboard</a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link<?= ($_GET['section'] ?? '') === 'usuarios' ? ' active' : '' ?>" href="?section=usuarios">Usuarios</a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link<?= ($_GET['section'] ?? '') === 'visitantes' ? ' active' : '' ?>" href="?section=visitantes">Visitantes</a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link<?= ($_GET['section'] ?? '') === 'perfil' ? ' active' : '' ?>" href="?section=perfil">Perfil</a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link<?= ($_GET['section'] ?? '') === 'cambiar' ? ' active' : '' ?>" href="?section=cambiar">Cambiar contraseña</a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link text-danger" href="<?= $GLOBALS['basePath'] ?>/logout">Cerrar sesión</a>
                </li>
            </ul>
        </nav>
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <?php
            $section = $_GET['section'] ?? 'dashboard';
            if ($rol === 'administrador') {
                if ($section === 'dashboard') {
                    // Dashboard principal
                    echo '<h2>Dashboard</h2><p>Bienvenido al panel de administración.</p>';
                } elseif ($section === 'usuarios') {
                    // Gestión de usuarios y roles (placeholder)
                    echo '<h2>Gestión de usuarios</h2>';
                    echo '<p>Aquí podrás ver, editar y asignar permisos a empleados y recepcionistas. (Funcionalidad próximamente)</p>';
                } elseif ($section === 'visitantes') {
                    // Listado de visitantes con columnas solicitadas (ejemplo estático)
                    echo '<h2>Visitantes</h2>';
                    echo '<div class="table-responsive"><table class="table table-bordered table-hover align-middle bg-white">';
                    echo '<thead class="table-light"><tr>
                        <th>Nombre visitante</th>
                        <th>Nombre a quien visita</th>
                        <th>Departamento/Piso</th>
                        <th>Tiempo</th>
                        <th>Fuera de tiempo</th>
                        <th>Estado</th>
                        <th>Enter by</th>
                        <th>Acciones</th>
                    </tr></thead><tbody>';
                    // Aquí deberías consultar la base de datos y recorrer los visitantes reales
                    // Ejemplo de fila estática:
                    echo '<tr>
                        <td>Juan Pérez</td>
                        <td>Lic. Ramírez</td>
                        <td>5to Piso</td>
                        <td>10:00 - 11:00</td>
                        <td>No</td>
                        <td>Autorizado</td>
                        <td>Recepcionista</td>
                        <td>
                            <a href="#" class="btn btn-sm btn-outline-primary">Ver</a>
                            <a href="#" class="btn btn-sm btn-outline-warning">Editar</a>
                            <a href="#" class="btn btn-sm btn-outline-danger">Eliminar</a>
                        </td>
                    </tr>';
                    echo '</tbody></table></div>';
                } elseif ($section === 'perfil') {
                    echo '<h2>Perfil</h2><p>Nombre: ' . htmlspecialchars($user['nombre']) . '<br>Correo: ' . htmlspecialchars($user['correo']) . '</p>';
                } elseif ($section === 'cambiar') {
                    echo '<h2>Cambiar contraseña</h2><p>Funcionalidad próximamente.</p>';
                } else {
                    echo '<h2>Bienvenido al panel</h2>';
                }
            } else {
                echo '<h2>Acceso restringido</h2><p>No tienes permisos para ver este panel.</p>';
            }
            ?>
        </main>
    </div>
</div>
</body>
</html>
