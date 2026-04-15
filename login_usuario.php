<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Si ya inició sesión, continuar compra u otra página según ?redirect=
if (is_logged_in()) {
    header('Location: ' . safe_post_login_redirect($_GET['redirect'] ?? 'index.php'));
    exit;
}

$error = null;
$redirectTarget = safe_post_login_redirect($_GET['redirect'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = normalize_login_email((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $redirectTarget = safe_post_login_redirect($_POST['redirect'] ?? '');

    try {
        // Cualquier cuenta activa (usuario o admin): necesario para checkout tras require_login()
        $stmt = db()->prepare(
            "SELECT id_cuenta, email, password_hash, rol, activo FROM cuentas
             WHERE LOWER(TRIM(email)) = :email LIMIT 1"
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();

        if (!$row || !(bool)$row['activo'] || !password_verify($password, (string)$row['password_hash'])) {
            $error = 'Credenciales inválidas.';
        } else {
            $_SESSION['auth'] = [
                'id' => (int)$row['id_cuenta'],
                'email' => normalize_login_email((string)$row['email']),
                'rol' => (string)$row['rol'],
            ];
            header('Location: ' . $redirectTarget);
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
    <p class="text-muted mb-4">Ingresa con tu correo y contraseña (cuentas de cliente y de administrador).</p>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="vstack gap-3">
      <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectTarget) ?>">
      <div>
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" required autocomplete="username" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div>
        <label class="form-label">Contraseña</label>
        <input name="password" type="password" class="form-control" required>
      </div>
      <button class="btn btn-primary" type="submit">Entrar</button>
      <small class="text-muted">Cualquier correo válido (Gmail, Outlook, etc.). Si es tu primera vez, ejecuta <a href="install.php">install.php</a> para las cuentas demo.</small>
    </form>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
