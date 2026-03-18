<?php
// Helpers de autenticación (usuario/admin)
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function is_logged_in(): bool
{
    return !empty($_SESSION['auth']) && is_array($_SESSION['auth']);
}

function auth_user(): ?array
{
    return is_logged_in() ? $_SESSION['auth'] : null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login_usuario.php');
        exit;
    }
}

function require_admin(): void
{
    if (!is_logged_in() || ($_SESSION['auth']['rol'] ?? null) !== 'admin') {
        header('Location: login_admin.php');
        exit;
    }
}

