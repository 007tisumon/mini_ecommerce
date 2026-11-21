<?php

require_once  'session.php';

function h(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function admin_is_authenticated(): bool
{
    start_app_session();

    return isset($_SESSION['admin_user'])
        && is_array($_SESSION['admin_user'])
        && ($_SESSION['admin_user']['role'] ?? null) === 'admin';
}

function require_admin(): void
{
    if (!admin_is_authenticated()) {
        header('Location: signin.php');
        exit;
    }
}
