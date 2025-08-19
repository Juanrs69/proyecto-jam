<?php
// public/scripts/crear_usuarios.php
$pdo = require __DIR__ . '/../../src/Config/database.php';

function upsertUser($pdo, $correo, $nombre, $rol) {
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    if ($stmt->fetch()) return;
    $id = bin2hex(random_bytes(16));
    $hash = password_hash('123456', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO usuarios (id,nombre,correo,hash_contrasena,rol) VALUES (?,?,?,?,?)")
        ->execute([$id,$nombre,$correo,$hash,$rol]);
}

upsertUser($pdo, 'admin@local.test', 'Administrador Prueba', 'administrador');
upsertUser($pdo, 'empleado@local.test', 'Empleado Prueba', 'empleado');
echo "Usuarios creados. Usuario: admin@local.test | empleado@local.test (pass = 123456)";
