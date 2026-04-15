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

function normalize_login_email(string $email): string
{
    return strtolower(trim($email));
}

/** Dominio obligatorio para el panel de administración. */
function is_panthera_admin_email(string $email): bool
{
    return str_ends_with(strtolower(trim($email)), '@panthera.com');
}

/**
 * Redirección interna tras login (evita open redirect).
 */
function safe_post_login_redirect(?string $target): string
{
    $t = trim((string)$target);
    if ($t === '') {
        return 'index.php';
    }
    if (preg_match('#^https?://#i', $t)) {
        return 'index.php';
    }
    if (str_contains($t, '..')) {
        return 'index.php';
    }
    $allowed = ['index.php', 'checkout.php', 'cart.php', 'productoshtml.php', 'producto.php', 'pedidos.php', 'admin.php'];
    $file = basename(explode('?', $t, 2)[0]);
    if (!in_array($file, $allowed, true)) {
        return 'index.php';
    }

    return $t;
}

function require_login(): void
{
    if (!is_logged_in()) {
        $here = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
        $qs = $_SERVER['QUERY_STRING'] ?? '';
        $ret = $here . ($qs !== '' ? '?' . $qs : '');
        header('Location: login_usuario.php?redirect=' . rawurlencode($ret));
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
