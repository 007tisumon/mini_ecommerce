<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('PROJECT_ROOT', 'C:/xampp_lite_8_5/www/mini_ecommerce');
require_once PROJECT_ROOT . '/config/database.php';

$email = trim((string) ($argv[1] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage: php scripts/set_admin_password.php admin@example.com\n");
    exit(1);
}

fwrite(STDOUT, "New password (at least 12 characters): ");
$password = rtrim((string) fgets(STDIN), "\r\n");

if (strlen($password) < 12) {
    fwrite(STDERR, "Password must be at least 12 characters long.\n");
    exit(1);
}

$connection = database();
$emailSql = $connection->real_escape_string($email);
$result = $connection->query("SELECT id, role FROM users WHERE email = '$emailSql' LIMIT 1");
$admin = $result->fetch_assoc();

if (!is_array($admin)) {
    fwrite(STDERR, "No user has that email. Import the database and check the admin email first.\n");
    exit(1);
}

if ($admin['role'] !== 'admin') {
    fwrite(STDERR, "That account is not an administrator; its role was not changed.\n");
    exit(1);
}

$passwordSql = $connection->real_escape_string(password_hash($password, PASSWORD_DEFAULT));
$adminId = (int) $admin['id'];
$connection->query("UPDATE users SET password = '$passwordSql' WHERE id = $adminId AND role = 'admin'");

fwrite(STDOUT, "Admin password updated for {$email}.\n");
