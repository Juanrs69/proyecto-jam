<?php
// public/scripts/crear_usuarios.php
$pdo = require __DIR__ . '/../../src/Config/database.php';

// Asegurar columnas nuevas si no existen
try {
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN documento VARCHAR(50) NULL AFTER nombre");
} catch (\Throwable $e) {
    // columna ya existe, ignorar
}
try {
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER rol");
} catch (\Throwable $e) {
    // columna ya existe, ignorar
}

function upsertUser($pdo, $correo, $nombre, $rol, $documento = null) {
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    if ($stmt->fetch()) return;
    $id = bin2hex(random_bytes(16));
    $hash = password_hash('123456', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO usuarios (id, nombre, documento, correo, hash_contrasena, rol, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())")
        ->execute([$id, $nombre, $documento, $correo, $hash, $rol]);
}

upsertUser($pdo, 'admin@local.test', 'Administrador Prueba', 'administrador', '1001');
upsertUser($pdo, 'empleado@local.test', 'Empleado Prueba', 'empleado', '1002');
upsertUser($pdo, 'recepcionista@local.test', 'Recepcionista Prueba', 'recepcionista', '1003');
echo "Usuarios creados/asegurados (pass = 123456)";
