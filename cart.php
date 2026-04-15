<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/cart.php';
require_once __DIR__ . '/includes/auth.php';

// CSRF básico
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = (string)$_SESSION['csrf'];

$mensajeOk = null;
$mensajeError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrf, (string)($_POST['csrf'] ?? ''))) {
        $mensajeError = 'Token inválido, recarga la página.';
    } else {
        $action = (string)($_POST['action'] ?? '');
        if ($action === 'add') {
            $id = (int)($_POST['id_producto'] ?? 0);
            $qty = (int)($_POST['cantidad'] ?? 1);
            cart_add($id, $qty);
            $mensajeOk = 'Producto agregado al carrito.';
        } elseif ($action === 'set') {
            $id = (int)($_POST['id_producto'] ?? 0);
            $qty = (int)($_POST['cantidad'] ?? 0);
            cart_set($id, $qty);
            $mensajeOk = 'Carrito actualizado.';
        } elseif ($action === 'clear') {
            cart_clear();
            $mensajeOk = 'Carrito vaciado.';
        }
    }
}

$return = trim((string)($_GET['return'] ?? ''));
if ($return !== '' && !$mensajeError && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // redirect simple para volver al catálogo/detalle
    header('Location: ' . $return);
    exit;
}

$cart = cart_get_detailed();

$title = 'Tu carrito';
$cssFile = ASSETS_BASE . '/css/productoscss.css';
require __DIR__ . '/includes/header.php';
?>

<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0 d-flex align-items-center gap-2"><span aria-hidden="true" class="cart-emoji">🛒</span> Tu carrito</h1>
    <form method="POST" class="m-0">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="action" value="clear">
      <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('¿Vaciar carrito?')">Vaciar</button>
    </form>
  </div>

  <?php if ($mensajeOk): ?>
    <div class="alert alert-success"><?= htmlspecialchars($mensajeOk) ?></div>
  <?php endif; ?>
  <?php if ($mensajeError): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($mensajeError) ?></div>
  <?php endif; ?>

  <?php if (!$cart['items']): ?>
    <div class="alert alert-info">Aún no has agregado productos. Ve a <a href="productoshtml.php">Productos</a>.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>Producto</th>
            <th class="text-end">Precio</th>
            <th class="text-center" style="width:160px;">Cantidad</th>
            <th class="text-end">Subtotal</th>
            <th class="text-end">Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cart['items'] as $it): ?>
            <tr>
              <td>
                <div class="d-flex gap-3 align-items-center">
                  <?php if ($it['imagen_url']): ?>
                    <?php
                      $img = (string)$it['imagen_url'];
                      if (str_starts_with($img, '../assets/')) {
                          $img = 'frontend/assets/' . substr($img, strlen('../assets/'));
                      }
                    ?>
                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars((string)$it['nombre']) ?>" style="width:64px;height:64px;object-fit:cover;border-radius:10px;">
                  <?php else: ?>
                    <div style="width:64px;height:64px;background:#eee;border-radius:10px;"></div>
                  <?php endif; ?>
                  <div>
                    <div class="fw-semibold"><?= htmlspecialchars((string)$it['nombre']) ?></div>
                    <small class="text-muted">Stock: <?= (int)$it['stock'] ?></small>
                  </div>
                </div>
              </td>
              <td class="text-end">$<?= number_format((float)$it['precio'], 2) ?></td>
              <td class="text-center">
                <form method="POST" class="d-inline-flex gap-2 align-items-center">
                  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                  <input type="hidden" name="action" value="set">
                  <input type="hidden" name="id_producto" value="<?= (int)$it['id_producto'] ?>">
                  <input class="form-control form-control-sm" name="cantidad" type="number" min="0" max="<?= max(0, (int)$it['stock']) ?>" value="<?= (int)$it['cantidad'] ?>" style="width: 90px;">
                  <button class="btn btn-sm btn-outline-dark" type="submit">Actualizar</button>
                </form>
              </td>
              <td class="text-end">$<?= number_format((float)$it['subtotal'], 2) ?></td>
              <td class="text-end">
                <form method="POST" class="m-0 d-inline">
                  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                  <input type="hidden" name="action" value="set">
                  <input type="hidden" name="id_producto" value="<?= (int)$it['id_producto'] ?>">
                  <input type="hidden" name="cantidad" value="0">
                  <button class="btn btn-sm btn-outline-danger" type="submit">Quitar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="card p-3 shadow-sm">
      <div class="d-flex justify-content-between align-items-center">
        <div class="text-muted">Total</div>
        <div class="fs-5 fw-semibold">$<?= number_format((float)$cart['total'], 2) ?></div>
      </div>
      <div class="mt-3 d-flex justify-content-end gap-2">
        <a class="btn btn-outline-dark" href="productoshtml.php">Seguir comprando</a>
        <a class="btn btn-dark" href="checkout.php">Continuar a pago</a>
      </div>
    </div>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

