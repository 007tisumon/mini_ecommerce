<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/database.php';

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

$pdo = database();
$statement = $pdo->prepare('SELECT id, role FROM users WHERE email = :email LIMIT 1');
$statement->execute(['email' => $email]);
$admin = $statement->fetch();

if (!is_array($admin)) {
    fwrite(STDERR, "No user has that email. Import the database and check the admin email first.\n");
    exit(1);
}

if ($admin['role'] !== 'admin') {
    fwrite(STDERR, "That account is not an administrator; its role was not changed.\n");
    exit(1);
}

$update = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id AND role = \'admin\'');
$update->execute([
    'password' => password_hash($password, PASSWORD_DEFAULT),
    'id' => (int) $admin['id'],
]);

fwrite(STDOUT, "Admin password updated for {$email}.\n");
