<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/cart.php';

// CSRF básico
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = (string)$_SESSION['csrf'];

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    $title = 'Producto';
    require __DIR__ . '/includes/header.php';
    echo "<main class='container py-4'><div class='alert alert-danger'>Producto no encontrado.</div></main>";
    require __DIR__ . '/includes/footer.php';
    exit;
}

// Incrementar vistas si existe columna (si no, no rompemos)
try {
    db()->exec("UPDATE productos SET vistas = COALESCE(vistas, 0) + 1 WHERE id_producto = " . (int)$id);
} catch (Throwable $e) {
    // ignore
}

$stmt = db()->prepare(
    "SELECT p.id_producto, p.nombre, p.descripcion, p.precio, p.stock, p.imagen_url, p.activo, c.nombre AS categoria
     FROM productos p
     JOIN categorias c ON c.id_categoria = p.id_categoria
     WHERE p.id_producto = :id
     LIMIT 1"
);
$stmt->execute([':id' => $id]);
$p = $stmt->fetch();

if (!$p || !(bool)$p['activo']) {
    http_response_code(404);
    $title = 'Producto';
    require __DIR__ . '/includes/header.php';
    echo "<main class='container py-4'><div class='alert alert-danger'>Producto no disponible.</div></main>";
    require __DIR__ . '/includes/footer.php';
    exit;
}

$mensajeOk = null;
$mensajeError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_to_cart') {
    if (!hash_equals($csrf, (string)($_POST['csrf'] ?? ''))) {
        $mensajeError = 'Token inválido, recarga la página.';
    } else {
        $qty = (int)($_POST['cantidad'] ?? 1);
        $qty = max(1, $qty);
        if ($qty > (int)$p['stock']) {
            $mensajeError = 'No hay stock suficiente.';
        } else {
            cart_add((int)$p['id_producto'], $qty);
            $mensajeOk = 'Agregado al carrito.';
        }
    }
}

$title = (string)$p['nombre'];
$cssFile = ASSETS_BASE . '/css/productoscss.css';
require __DIR__ . '/includes/header.php';
?>

<main class="container py-4">
  <div class="row g-4 align-items-start">
    <div class="col-lg-6">
      <div class="card p-3 shadow-sm">
        <?php if (!empty($p['imagen_url'])): ?>
          <?php
            $img = (string)$p['imagen_url'];
            if (str_starts_with($img, '../assets/')) {
                $img = 'frontend/assets/' . substr($img, strlen('../assets/'));
            }
          ?>
          <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars((string)$p['nombre']) ?>" style="width:100%;max-height:520px;object-fit:cover;border-radius:14px;">
        <?php else: ?>
          <div style="width:100%;height:420px;background:#eee;border-radius:14px;"></div>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-lg-6">
      <h1 class="mb-2"><?= htmlspecialchars((string)$p['nombre']) ?></h1>
      <div class="text-muted mb-3"><?= htmlspecialchars((string)$p['categoria']) ?> · Stock: <?= (int)$p['stock'] ?></div>
      <div class="fs-3 fw-semibold mb-3">$<?= number_format((float)$p['precio'], 2) ?></div>

      <?php if ($mensajeOk): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensajeOk) ?></div>
      <?php endif; ?>
      <?php if ($mensajeError): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($mensajeError) ?></div>
      <?php endif; ?>

      <p><?= nl2br(htmlspecialchars((string)($p['descripcion'] ?? ''))) ?></p>

      <div class="card p-3 shadow-sm mt-3">
        <form method="POST" class="d-flex gap-2 align-items-end flex-wrap">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
          <input type="hidden" name="action" value="add_to_cart">
          <div>
            <label class="form-label">Cantidad</label>
            <input class="form-control" type="number" name="cantidad" min="1" max="<?= max(1, (int)$p['stock']) ?>" value="1" <?= ((int)$p['stock'] <= 0) ? 'disabled' : '' ?>>
          </div>
          <button class="btn btn-dark" type="submit" <?= ((int)$p['stock'] <= 0) ? 'disabled' : '' ?>>Agregar al carrito</button>
          <a class="btn btn-outline-secondary" href="cart.php" title="Carrito"><span aria-hidden="true">🛒</span> Carrito</a>
        </form>
      </div>
    </div>
  </div>

  <div class="mt-4">
    <a class="btn btn-outline-dark" href="productoshtml.php">← Volver al catálogo</a>
  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

