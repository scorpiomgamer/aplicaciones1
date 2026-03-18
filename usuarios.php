<?php
declare(strict_types=1);
$title = 'Inicio';
$cssFile = 'frontend/css/style.css';
require __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
  <h1 class="mb-4">Gestión de Usuarios</h1>
  <p>Aquí puedes gestionar los usuarios registrados en el sistema.</p>
  <!-- Aquí iría la tabla o interfaz para gestionar usuarios -->
</div>
<table class="table table-striped">
  <thead>
    <tr>
      <th>ID Usuario</th>
      <th>Nombre</th>
      <th>Email</th>
      <th>Rol</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <!-- Aquí se cargarían los usuarios desde la base de datos -->
    <tr>
      <td>1</td>
      <td>Juan Pérez</td>
      <td>
        juan.perez@example.com
      </td>
      <td>Usuario</td>
      <td>
        <button class="btn btn-sm btn-primary">Ver Detalles</button>
      </td>
    </tr>
    <!-- Más filas de usuarios -->
  </tbody>
</table>

<?php require __DIR__ . '/includes/footer.php'; ?>
