<?php
declare(strict_types=1);
$title = 'Añadir Producto';
$cssFile = 'frontend/css/productoscss.css';
require __DIR__ . '/includes/header.php';
?>
<div class="container mt-5">
  <h2 class="text-center mb-4">Añadir Nuevo Producto</h2>
  <form id="add-product-form" class="mx-auto" style="max-width: 500px;">
    <div class="mb-3">
      <label for="product-name" class="form-label 
fw-bold">Nombre del Producto</label>
      <input type="text" class="form-control" id="product-name" required>
    </div>
    <div class="mb-3">
      <label for="product-description" class="form-label fw-bold">Descripción</label>
      <textarea class="form-control" id="product-description" rows="3" required></textarea>
    </div>
    <div class="mb-3">
      <label for="product-price" class="form-label fw-bold">Precio</label>
      <input type="number" class="form-control" id="product-price" step="0.01" required>
    </div>
    <button type="submit" class="btn btn-success">Añadir Producto</button>
  </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
