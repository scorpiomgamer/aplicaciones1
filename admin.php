<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_admin(); // protege la página solo para admin

$title = 'Administrador';
$cssFile = ASSETS_BASE . '/css/style.css';
require __DIR__ . '/includes/header.php';
?>
<main class="container py-4">
  <div class="card p-4 shadow-sm">
    <h1 class="mb-2">Hola administrador</h1>
  </div>
  <div class="card p-4 shadow-sm mt-4">
    <h2 class="mb-3">Panel de administración</h2>
    <p class="text-muted">Aquí puedes gestionar productos, pedidos y usuarios.</p>
    <div class="list-group">
      <a href="productos.php" class="list-group-item list-group-item-action">Gestionar Productos</a>
      <a href="pedidos.php" class="list-group-item list-group-item-action">Gestionar Pedidos</a>
      <a href="usuarios.php" class="list-group-item list-group-item-action">Gestionar Usuarios</a>
    </div>
  </div>

</main>
<?php require __DIR__ . '/includes/footer.php'; ?>

