<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/cart.php';

// CSRF básico (para "Agregar al carrito")
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = (string)$_SESSION['csrf'];

$idCategoria = (int)($_GET['cat'] ?? 0);
$q = trim((string)($_GET['q'] ?? ''));

// Cargar categorías
try {
    $categorias = db()->query("SELECT id_categoria, nombre FROM categorias WHERE activo = 1 ORDER BY nombre")->fetchAll();
} catch (Throwable $e) {
    $categorias = [];
}

$where = "p.activo = 1";
$params = [];
if ($idCategoria > 0) {
    $where .= " AND p.id_categoria = :cat";
    $params[':cat'] = $idCategoria;
}
if ($q !== '') {
    $where .= " AND (p.nombre LIKE :q OR p.descripcion LIKE :q)";
    $params[':q'] = '%' . $q . '%';
}

$sql = "SELECT p.id_producto, p.nombre, p.descripcion, p.precio, p.stock, p.imagen_url, c.nombre AS categoria
        FROM productos p
        JOIN categorias c ON c.id_categoria = p.id_categoria
        WHERE $where
        ORDER BY p.id_producto DESC";

try {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $productos = $stmt->fetchAll();
} catch (Throwable $e) {
    $productos = [];
}

$title = 'Productos';
$cssFile = ASSETS_BASE . '/css/productoscss.css';
require __DIR__ . '/includes/header.php';
?>

<main class="container py-4">
  <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
    <div>
      <h1 class="mb-1">Nuestros Productos</h1>
      <p class="text-muted mb-0">Bolsos y accesorios para dama. Elige tus favoritos y agrégalos al carrito.</p>
    </div>
    <a class="btn btn-outline-dark" href="cart.php" title="Carrito"><span aria-hidden="true">🛒</span> Carrito</a>
  </div>

  <div class="card p-3 shadow-sm mb-4">
    <form class="row g-2 align-items-end" method="GET">
      <div class="col-md-5">
        <label class="form-label">Buscar</label>
        <input class="form-control" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Ej: bolso, tote, accesorio...">
      </div>
      <div class="col-md-5">
        <label class="form-label">Categoría</label>
        <select class="form-select" name="cat">
          <option value="0">Todas</option>
          <?php foreach ($categorias as $c): ?>
            <option value="<?= (int)$c['id_categoria'] ?>" <?= ((int)$c['id_categoria'] === $idCategoria) ? 'selected' : '' ?>>
              <?= htmlspecialchars((string)$c['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-grid">
        <button class="btn btn-dark" type="submit">Filtrar</button>
      </div>
    </form>
  </div>

  <div class="row g-3">
    <?php if (!$productos): ?>
      <div class="col-12">
        <div class="alert alert-info">No hay productos para mostrar.</div>
      </div>
    <?php endif; ?>

    <?php foreach ($productos as $p): ?>
      <div class="col-sm-6 col-lg-4">
        <div class="card h-100">
          <?php if (!empty($p['imagen_url'])): ?>
            <?php
              $img = (string)$p['imagen_url'];
              if (str_starts_with($img, '../assets/')) {
                  $img = 'frontend/assets/' . substr($img, strlen('../assets/'));
              }
            ?>
            <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" alt="<?= htmlspecialchars((string)$p['nombre']) ?>" style="height:220px;object-fit:cover;">
          <?php else: ?>
            <div style="height:220px;background:#eee;"></div>
          <?php endif; ?>
          <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start gap-2">
              <h5 class="card-title mb-1"><?= htmlspecialchars((string)$p['nombre']) ?></h5>
              <span class="badge text-bg-light"><?= htmlspecialchars((string)$p['categoria']) ?></span>
            </div>
            <p class="card-text text-muted" style="min-height: 48px;">
              <?= htmlspecialchars(mb_strimwidth((string)($p['descripcion'] ?? ''), 0, 90, '...')) ?>
            </p>
            <div class="mt-auto">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-semibold">$<?= number_format((float)$p['precio'], 2) ?></div>
                <small class="text-muted">Stock: <?= (int)$p['stock'] ?></small>
              </div>
              <div class="d-flex gap-2">
                <a class="btn btn-outline-dark w-50" href="producto.php?id=<?= (int)$p['id_producto'] ?>">Ver</a>
                <form class="w-50" method="POST" action="cart.php?return=productoshtml.php">
                  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                  <input type="hidden" name="action" value="add">
                  <input type="hidden" name="id_producto" value="<?= (int)$p['id_producto'] ?>">
                  <input type="hidden" name="cantidad" value="1">
                  <button class="btn btn-dark w-100" type="submit" <?= ((int)$p['stock'] <= 0) ? 'disabled' : '' ?>>Agregar</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
