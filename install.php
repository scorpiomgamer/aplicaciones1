<?php
// Instalador rápido: crea tabla de cuentas y usuarios demo
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

try {
    $pdo = db();

    // Tabla simple para autenticación (separada de la tabla "usuarios" del formulario)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cuentas (
            id_cuenta INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            rol ENUM('usuario', 'admin') NOT NULL DEFAULT 'usuario',
            activo BOOLEAN DEFAULT TRUE,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_rol (rol)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Seed básico (si no existen)
    $seed = [
        ['admin@panthera.com', 'admin123', 'admin'],
        ['usuario@panthera.com', 'usuario123', 'usuario'],
    ];

    $stmtSel = $pdo->prepare("SELECT 1 FROM cuentas WHERE email = :email LIMIT 1");
    $stmtIns = $pdo->prepare("INSERT INTO cuentas (email, password_hash, rol) VALUES (:email, :hash, :rol)");

    foreach ($seed as [$email, $pass, $rol]) {
        $stmtSel->execute([':email' => $email]);
        if (!$stmtSel->fetchColumn()) {
            $stmtIns->execute([
                ':email' => $email,
                ':hash' => password_hash($pass, PASSWORD_DEFAULT),
                ':rol' => $rol,
            ]);
        }
    }

    echo "<h2>Listo</h2>";
    echo "<p>Se creó (o verificó) la tabla <b>cuentas</b> y usuarios demo.</p>";
    echo "<ul>";
    echo "<li>Admin: <b>admin@panthera.com</b> / <b>admin123</b></li>";
    echo "<li>Usuario: <b>usuario@panthera.com</b> / <b>usuario123</b></li>";
    echo "</ul>";
    echo "<p>Ahora puedes ir a <a href='login_admin.php'>login_admin.php</a> o <a href='login_usuario.php'>login_usuario.php</a>.</p>";
} catch (Throwable $e) {
    http_response_code(500);
    echo "<h2>Error</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}

