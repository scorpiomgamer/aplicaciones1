<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/cart.php';
require_once __DIR__ . '/includes/checkout_usuario.php';

require_login();

// CSRF básico
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = (string)$_SESSION['csrf'];

$mensajeOk = null;
$mensajeError = null;

$cart = cart_get_detailed();
if (!$cart['items']) {
    header('Location: cart.php');
    exit;
}

// Cuenta (sesión) -> fila en usuarios (crea perfil mínimo si falta, p. ej. admin)
$emailCuenta = normalize_login_email((string)(auth_user()['email'] ?? ''));
$usuario = checkout_ensure_usuario(db(), $emailCuenta);
$checkoutPerfilError = $usuario === null;

$direccionDefault = $usuario ? (string)($usuario['direccion'] ?? '') : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'place_order') {
    if (!hash_equals($csrf, (string)($_POST['csrf'] ?? ''))) {
        $mensajeError = 'Token inválido, recarga la página.';
    } else {
        $metodoPago = trim((string)($_POST['metodo_pago'] ?? ''));
        $direccionEnvio = trim((string)($_POST['direccion_envio'] ?? $direccionDefault));
        $notas = trim((string)($_POST['notas_envio'] ?? ''));

        $metodosValidos = ['Contraentrega', 'Transferencia', 'Tarjeta (simulado)'];
        if (!in_array($metodoPago, $metodosValidos, true)) {
            $mensajeError = 'Selecciona un método de pago válido.';
        } elseif ($direccionEnvio === '') {
            $mensajeError = 'La dirección de envío es obligatoria.';
        } else {
            // Validar stock antes de crear pedido
            foreach ($cart['items'] as $it) {
                if ($it['cantidad'] > $it['stock']) {
                    $mensajeError = 'Stock insuficiente para: ' . $it['nombre'];
                    break;
                }
            }
        }

        if ($checkoutPerfilError && $mensajeError === null) {
            $mensajeError = 'No se pudo preparar tu perfil de cliente para el pedido.';
        }

        if (!$mensajeError) {
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $stmtP = $pdo->prepare(
                    "INSERT INTO pedidos (id_usuario, estado, total, direccion_envio, notas_envio)
                     VALUES (:id_usuario, 'pendiente', :total, :direccion_envio, :notas_envio)"
                );

                $notaCompleta = "Método de pago: {$metodoPago}";
                if ($notas !== '') {
                    $notaCompleta .= "\nNotas: " . $notas;
                }

                $stmtP->execute([
                    ':id_usuario' => (int)$usuario['id_usuario'],
                    ':total' => (float)$cart['total'],
                    ':direccion_envio' => $direccionEnvio,
                    ':notas_envio' => $notaCompleta,
                ]);

                $idPedido = (int)$pdo->lastInsertId();

                $stmtDP = $pdo->prepare(
                    "INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario, subtotal)
                     VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario, :subtotal)"
                );
                $stmtStock = $pdo->prepare("UPDATE productos SET stock = stock - :cantidad WHERE id_producto = :id_producto AND stock >= :cantidad");

                foreach ($cart['items'] as $it) {
                    $qty = (int)$it['cantidad'];
                    $precio = (float)$it['precio'];
                    $subtotal = $precio * $qty;

                    // Descontar stock de forma segura
                    $stmtStock->execute([
                        ':cantidad' => $qty,
                        ':id_producto' => (int)$it['id_producto'],
                    ]);
                    if ($stmtStock->rowCount() !== 1) {
                        throw new RuntimeException('Stock insuficiente (actualizado) para ' . (string)$it['nombre']);
                    }

                    $stmtDP->execute([
                        ':id_pedido' => $idPedido,
                        ':id_producto' => (int)$it['id_producto'],
                        ':cantidad' => $qty,
                        ':precio_unitario' => $precio,
                        ':subtotal' => $subtotal,
                    ]);
                }

                $pdo->commit();
                cart_clear();
                $mensajeOk = 'Pedido creado correctamente. Tu pedido está en estado pendiente.';
            } catch (Throwable $e) {
                $pdo->rollBack();
                $mensajeError = 'No se pudo crear el pedido. Intenta de nuevo.';
            }
        }
    }
}

$title = 'Pago';
$cssFile = ASSETS_BASE . '/css/formulariocss.css';
require __DIR__ . '/includes/header.php';
?>

<main class="container py-4">
  <h1 class="mb-3">Método de pago</h1>
  <p class="text-muted">Este es un mini checkout: selecciona método y confirma tu pedido.</p>

  <?php if ($mensajeOk): ?>
    <div class="alert alert-success"><?= htmlspecialchars($mensajeOk) ?></div>
    <a class="btn btn-dark" href="productoshtml.php">Volver al catálogo</a>
  <?php else: ?>
    <?php if ($mensajeError): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($mensajeError) ?></div>
    <?php endif; ?>
    <?php if ($checkoutPerfilError): ?>
      <div class="alert alert-danger">No se pudo preparar el perfil de cliente para el pedido. Si tu base está recién importada, ejecuta <a href="install.php">install.php</a>. También puedes <a href="registro.php">registrarte</a> o contactar al administrador.</div>
    <?php endif; ?>

    <div class="row g-4">
      <div class="col-lg-7">
        <div class="card p-4 shadow-sm">
          <h2 class="h5 mb-3">Datos de envío</h2>
          <form method="POST" class="vstack gap-3">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="action" value="place_order">

            <div>
              <label class="form-label">Dirección de envío</label>
              <input class="form-control" name="direccion_envio" value="<?= htmlspecialchars($direccionDefault) ?>" required>
            </div>

            <div>
              <label class="form-label">Método de pago</label>
              <select class="form-select" name="metodo_pago" required>
                <option value="">Seleccione...</option>
                <option value="Contraentrega">Contraentrega</option>
                <option value="Transferencia">Transferencia</option>
                <option value="Tarjeta (simulado)">Tarjeta (simulado)</option>
              </select>
              <small class="text-muted">Nota: por ahora solo registramos el método; no hay pasarela real.</small>
            </div>

            <div>
              <label class="form-label">Notas (opcional)</label>
              <textarea class="form-control" name="notas_envio" rows="2"></textarea>
            </div>

            <button class="btn btn-dark" type="submit" onclick="return confirm('¿Confirmar pedido?')" <?= $checkoutPerfilError ? 'disabled' : '' ?>>Confirmar pedido</button>
            <a class="btn btn-outline-secondary" href="cart.php"><span aria-hidden="true">🛒</span> Volver al carrito</a>
          </form>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card p-4 shadow-sm">
          <h2 class="h5 mb-3">Resumen</h2>
          <ul class="list-group list-group-flush mb-3">
            <?php foreach ($cart['items'] as $it): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <div class="fw-semibold"><?= htmlspecialchars((string)$it['nombre']) ?></div>
                  <small class="text-muted"><?= (int)$it['cantidad'] ?> × $<?= number_format((float)$it['precio'], 2) ?></small>
                </div>
                <div class="fw-semibold">$<?= number_format((float)$it['subtotal'], 2) ?></div>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="d-flex justify-content-between">
            <div class="text-muted">Total</div>
            <div class="fs-5 fw-semibold">$<?= number_format((float)$cart['total'], 2) ?></div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

