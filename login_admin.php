<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in() && (auth_user()['rol'] ?? null) === 'admin') {
    header('Location: admin.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    try {
        $stmt = db()->prepare("SELECT id_cuenta, email, password_hash, rol, activo FROM cuentas WHERE email = :email AND rol = 'admin' LIMIT 1");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();

        if (!$row || !(bool)$row['activo'] || !password_verify($password, (string)$row['password_hash'])) {
            $error = 'Credenciales inválidas.';
        } else {
            $_SESSION['auth'] = [
                'id' => (int)$row['id_cuenta'],
                'email' => (string)$row['email'],
                'rol' => (string)$row['rol'],
            ];
            header('Location: admin.php');
            exit;
        }
    } catch (Throwable $e) {
        $error = 'Error conectando a la base de datos.';
    }
}

$title = 'Login administrador';
$cssFile = ASSETS_BASE . '/css/formulariocss.css';
require __DIR__ . '/includes/header.php';
?>
<main class="container py-4">
  <div class="card p-4 shadow-sm mx-auto" style="max-width: 520px;">
    <h2 class="mb-3">Login de administrador</h2>
    <p class="text-muted mb-4">Solo para administración.</p>

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
      <button class="btn btn-dark" type="submit">Entrar</button>
      <small class="text-muted">Tip: ejecuta primero <a href="install.php">install.php</a> para crear admin demo.</small>
    </form>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>

