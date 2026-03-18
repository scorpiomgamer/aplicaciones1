<?php
// Header reutilizable (navbar)
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$user = auth_user();
$cssFile = $cssFile ?? (ASSETS_BASE . '/css/style.css');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($cssFile) ?>" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <title><?= htmlspecialchars($title ?? 'Panthera') ?></title>
</head>
<body>
  <header>
    <nav class="navbar navbar-expand-sm">
      <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
          <img class="imagen_logo" src="<?= ASSETS_BASE ?>/assets/PANTHERA%20LOGO_Mesa%20de%20trabajo%201.jpg" alt="Panthera logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mynavbar">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mynavbar">
          <ul class="navbar-nav me-auto">
            <li class="nav-item">
              <a class="nav-link" href="productos.php">Artículos</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="productos.php">Accesorios</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="https://www.instagram.com/p4nther4__/" target="_blank" rel="noreferrer">Contáctenos</a>
            </li>
          </ul>

          <div class="d-flex gap-2 align-items-center">
            <?php if ($user): ?>
              <span class="mini-badge">Hola, <?= htmlspecialchars($user['email'] ?? 'usuario') ?></span>
              <?php if (($user['rol'] ?? null) === 'admin'): ?>
                <a class="btn btn-sm btn-outline-light" href="admin.php">Admin</a>
              <?php endif; ?>
              <a class="btn btn-sm btn-light" href="logout.php">Salir</a>
            <?php else: ?>
              <a class="btn btn-sm btn-light" href="login_usuario.php">Login usuario</a>
              <a class="btn btn-sm btn-outline-light" href="login_admin.php">Login admin</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <a class="boton2" href="registro.php">Regístrate</a>
    </nav>
  </header>

