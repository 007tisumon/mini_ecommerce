<?php
declare(strict_types=1);

require_once __DIR__ . '/session.php';

function h(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function app_url(string $path = ''): string
{
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    if (str_ends_with($basePath, '/admin')) {
        $basePath = substr($basePath, 0, -6);
    }

    return ($basePath === '' || $basePath === '.' ? '' : $basePath)
        . '/'
        . ltrim($path, '/');
}

function csrf_token(): string
{
    start_app_session();

    if (!isset($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function csrf_is_valid(mixed $submittedToken): bool
{
    start_app_session();

    return is_string($submittedToken)
        && isset($_SESSION['_csrf_token'])
        && is_string($_SESSION['_csrf_token'])
        && hash_equals($_SESSION['_csrf_token'], $submittedToken);
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
        header('Location: ' . app_url('admin/login.php'));
        exit;
    }
}
