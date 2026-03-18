<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Si ya inició sesión, lo mandamos al inicio
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    try {
        $stmt = db()->prepare("SELECT id_cuenta, email, password_hash, rol, activo FROM cuentas WHERE email = :email AND rol = 'usuario' LIMIT 1");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();

        if (!$row || !(bool)$row['activo'] || !password_verify($password, (string)$row['password_hash'])) {
            $error = 'Credenciales inválidas.';
        } else {
            // Guardamos lo mínimo en sesión
            $_SESSION['auth'] = [
                'id' => (int)$row['id_cuenta'],
                'email' => (string)$row['email'],
                'rol' => (string)$row['rol'],
            ];
            header('Location: index.php');
            exit;
        }
    } catch (Throwable $e) {
        $error = 'Error conectando a la base de datos.';
    }
}

$title = 'Login usuario';
$cssFile = ASSETS_BASE . '/css/formulariocss.css';
require __DIR__ . '/includes/header.php';
?>
<main class="container py-4">
  <div class="card p-4 shadow-sm mx-auto" style="max-width: 520px;">
    <h2 class="mb-3">Login de usuario</h2>
    <p class="text-muted mb-4">Ingresa con tu correo y contraseña.</p>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="vstack gap-3">
      <div>
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" required>
      </div>
      <div>
        <label class="form-label">Contraseña</label>
        <input name="password" type="password" class="form-control" required>
      </div>
      <button class="btn btn-primary" type="submit">Entrar</button>
      <small class="text-muted">Tip: ejecuta primero <a href="install.php">install.php</a> para crear usuarios demo.</small>
    </form>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>

