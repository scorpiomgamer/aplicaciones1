<?php
// Cerrar sesión
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

$_SESSION = [];
session_destroy();

header('Location: index.php');
exit;

