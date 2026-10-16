<?php
$dbPath = sys_get_temp_dir() . '/photo-game-' . bin2hex(random_bytes(8)) . '.sqlite';
putenv('DATABASE_URL=sqlite:' . $dbPath);
putenv('ADMIN_PASSWORD');
putenv('ACCESS_CODE');
require __DIR__ . '/../photo_game/database/database.php';
try {
    $db = Database::getInstance();
    $pdo = new PDO('sqlite:' . $dbPath);
    if ((int)$pdo->query('SELECT COUNT(*) FROM users WHERE is_admin=1')->fetchColumn() !== 0) throw new Exception('Default admin created');
    if ((int)$pdo->query('SELECT COUNT(*) FROM access_codes')->fetchColumn() !== 0) throw new Exception('Default access code created');
    foreach (['users','tasks','user_tasks','completed_tasks','photo_ratings','app_settings','access_codes'] as $table) $pdo->query('SELECT COUNT(*) FROM ' . $table);
    $username = 'integration-admin';
    $password = bin2hex(random_bytes(24));
    $code = bin2hex(random_bytes(16));
    putenv('ADMIN_USERNAME=' . $username);
    putenv('ADMIN_PASSWORD=' . $password);
    putenv('ACCESS_CODE=' . $code);
    $instance = new ReflectionProperty(Database::class, 'instance');
    $instance->setAccessible(true);
    $instance->setValue(null, null);
    $db = Database::getInstance();
    $hash = $pdo->query("SELECT password FROM users WHERE username='integration-admin'")->fetchColumn();
    if (!password_verify($password, $hash)) throw new Exception('Explicit admin password not installed');
    if ((int)$pdo->query('SELECT COUNT(*) FROM access_codes')->fetchColumn() !== 1) throw new Exception('Explicit access code not installed');
    putenv('ADMIN_PASSWORD=' . bin2hex(random_bytes(24)));
    $instance->setValue(null, null);
    $db = Database::getInstance();
    $unchangedHash = $pdo->query("SELECT password FROM users WHERE username='integration-admin'")->fetchColumn();
    if ($unchangedHash !== $hash) throw new Exception('Existing admin password reset unexpectedly');
    echo "Clean bootstrap and explicit credentials without recurring reset: OK\n";
} finally {
    foreach ([$dbPath, $dbPath.'-wal', $dbPath.'-shm'] as $file) if (file_exists($file)) unlink($file);
}
