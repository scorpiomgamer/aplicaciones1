<?php
declare(strict_types=1);
$title = 'Productos';
$cssFile = 'frontend/css/productoscss.css';
require __DIR__ . '/includes/header.php';
?>

<main class="container mt-5">
  <h2 class="text-center mb-4">Nuestros Productos</h2>
  <div id="productos-container" class="row"></div>

  <div id="loading-message" class="text-center mt-3">
    <p>Cargando productos...</p>
  </div>
</main>

<script src="frontend/js/productos.js"></script>
<?php require __DIR__ . '/includes/footer.php'; ?>

