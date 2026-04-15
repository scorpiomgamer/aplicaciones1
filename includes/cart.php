<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * Carrito en sesión:
 * $_SESSION['cart'] = [ id_producto => cantidad ]
 */

function cart_init(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function cart_count_items(): int
{
    cart_init();
    $total = 0;
    foreach ($_SESSION['cart'] as $qty) {
        $total += max(0, (int)$qty);
    }
    return $total;
}

function cart_add(int $idProducto, int $cantidad = 1): void
{
    cart_init();
    if ($idProducto <= 0) {
        return;
    }
    $cantidad = max(1, $cantidad);
    $_SESSION['cart'][$idProducto] = (int)($_SESSION['cart'][$idProducto] ?? 0) + $cantidad;
}

function cart_set(int $idProducto, int $cantidad): void
{
    cart_init();
    if ($idProducto <= 0) {
        return;
    }
    if ($cantidad <= 0) {
        unset($_SESSION['cart'][$idProducto]);
        return;
    }
    $_SESSION['cart'][$idProducto] = $cantidad;
}

function cart_clear(): void
{
    cart_init();
    $_SESSION['cart'] = [];
}

/**
 * Devuelve items enriquecidos con datos de productos desde BD.
 * @return array{items: array<int,array>, total: float}
 */
function cart_get_detailed(): array
{
    cart_init();
    $ids = array_keys($_SESSION['cart']);
    $ids = array_values(array_filter(array_map('intval', $ids), fn ($v) => $v > 0));

    if (!$ids) {
        return ['items' => [], 'total' => 0.0];
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare(
        "SELECT id_producto, nombre, descripcion, precio, stock, imagen_url, activo
         FROM productos
         WHERE id_producto IN ($placeholders)"
    );
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();

    $byId = [];
    foreach ($rows as $r) {
        $byId[(int)$r['id_producto']] = $r;
    }

    $items = [];
    $total = 0.0;
    foreach ($ids as $id) {
        $p = $byId[$id] ?? null;
        if (!$p || !(bool)$p['activo']) {
            continue;
        }
        $qty = max(1, (int)($_SESSION['cart'][$id] ?? 1));
        $precio = (float)$p['precio'];
        $subtotal = $precio * $qty;
        $total += $subtotal;
        $items[] = [
            'id_producto' => $id,
            'nombre' => (string)$p['nombre'],
            'descripcion' => (string)($p['descripcion'] ?? ''),
            'precio' => $precio,
            'stock' => (int)$p['stock'],
            'imagen_url' => (string)($p['imagen_url'] ?? ''),
            'cantidad' => $qty,
            'subtotal' => $subtotal,
        ];
    }

    return ['items' => $items, 'total' => $total];
}
