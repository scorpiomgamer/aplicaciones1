<?php
declare(strict_types=1);
$title = 'Inicio';
$cssFile = 'frontend/css/style.css';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
  <h1 class="mb-4">Gestión de Pedidos</h1>
  <p>Aquí puedes gestionar los pedidos realizados por los clientes.</p>
  <!-- Aquí iría la tabla o interfaz para gestionar pedidos -->
</div>
<table class="table table-striped">
<!-- Aquí se cargarían los pedidos desde la base de datos -->
<div id="loading-message" class="text-center mt-3">
    <p>Cargando pedidos...</p>
    </div>
<!-- añadir productos -->
 <div id="añadir producto" class="text-center mt-3">
    <p><a href="añadir_pedido.php" class="btn btn-success">Añadir Pedido</a></p>
    </div>
    

  <thead>
    <tr>
      <th>ID Pedido</th>
      <th>Cliente</th>
      <th>Fecha</th>
      <th>Total</th>
      <th>Estado</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <!-- Aquí se cargarían los pedidos desde la base de datos -->
    <tr>
      <td>1</td>
      <td>Juan Pérez</td>
      <td>2024-06-01</td>
      <td>$100.00</td>
      <td>Pendiente</td>
      <td><button class="btn btn-sm btn-primary">Ver Detalles</button></td>
    </tr>
    <!-- Más filas de pedidos -->
  </tbody>
</table>

<?php require __DIR__ . '/includes/footer.php'; ?>
