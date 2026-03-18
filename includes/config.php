<?php
// Configuración base de la app (DB + sesión)
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start(); // sesión para login
}

// Ajusta estas credenciales a tu XAMPP/MySQL
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'pantera');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Ruta base para assets dentro del proyecto
define('ASSETS_BASE', 'frontend');

