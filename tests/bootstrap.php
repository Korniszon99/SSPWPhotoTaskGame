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
    echo "Clean bootstrap without default credentials: OK\n";
} finally {
    foreach ([$dbPath, $dbPath.'-wal', $dbPath.'-shm'] as $file) if (file_exists($file)) unlink($file);
}
