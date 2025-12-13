<?php
// UWAGA: Usuń ten plik po weryfikacji!
// Dostęp tylko z parametrem ?secret=debug2024
if (!isset($_GET['secret']) || $_GET['secret'] !== 'debug2024') {
    http_response_code(403);
    die('Access denied');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Diagnostyka Azure - Photo Game</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        h1 { color: #4ec9b0; }
        h2 { color: #569cd6; margin-top: 30px; }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .warning { color: #dcdcaa; }
        pre { background: #252526; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .box { background: #252526; padding: 15px; margin: 10px 0; border-left: 3px solid #569cd6; }
    </style>
</head>
<body>
    <h1>🔍 Diagnostyka zmiennych środowiskowych - Azure App Service</h1>
    <p><strong>⚠️ USUŃ TEN PLIK PO WERYFIKACJI!</strong></p>

<?php

echo "<h2>📋 1. Zmienne środowiskowe DATABASE</h2>";
echo "<div class='box'><pre>";

$dbVars = [
    'DATABASE_URL',
    'DB_DRIVER',
    'DB_HOST',
    'DB_PORT',
    'DB_NAME',
    'DB_USER',
    'DB_PASSWORD',
    'DB_CHARSET'
];

foreach ($dbVars as $var) {
    $value = getenv($var);
    if ($value !== false) {
        if ($var === 'DB_PASSWORD') {
            echo "✓ <span class='success'>$var</span>: " . (strlen($value) > 0 ? "[SET - " . strlen($value) . " chars]" : "[EMPTY]") . "\n";
        } else {
            echo "✓ <span class='success'>$var</span>: $value\n";
        }
    } else {
        echo "✗ <span class='error'>$var</span>: NOT SET\n";
    }
}

echo "</pre></div>";

echo "<h2>🔐 2. Zmienne ADMIN</h2>";
echo "<div class='box'><pre>";

$adminUsername = getenv('ADMIN_USERNAME');
$adminPassword = getenv('ADMIN_PASSWORD');

echo "ADMIN_USERNAME: " . ($adminUsername !== false ? "<span class='success'>$adminUsername</span>" : "<span class='warning'>NOT SET (default: admin)</span>") . "\n";
echo "ADMIN_PASSWORD: " . ($adminPassword !== false ? "<span class='success'>[SET - " . strlen($adminPassword) . " chars]</span>" : "<span class='warning'>NOT SET (default: admin)</span>") . "\n";

echo "</pre></div>";

echo "<h2>🎟️ 3. Zmienna ACCESS_CODE</h2>";
echo "<div class='box'><pre>";

$accessCode = getenv('ACCESS_CODE');
echo "ACCESS_CODE: " . ($accessCode !== false ? "<span class='success'>$accessCode</span>" : "<span class='warning'>NOT SET (default: demo)</span>") . "\n";

echo "</pre></div>";

echo "<h2>🔗 4. Azure Connection Strings (MYSQLCONNSTR_*, SQLCONNSTR_*, etc.)</h2>";
echo "<div class='box'><pre>";

$foundConnStr = false;
foreach ($_SERVER as $key => $value) {
    if (preg_match('/^(MYSQLCONNSTR_|SQLCONNSTR_|POSTGRESQLCONNSTR_|CUSTOMCONNSTR_)/i', $key)) {
        $foundConnStr = true;
        // Ukryj hasło w connection string
        $safeValue = preg_replace('/(password|pwd)=([^;]+)/i', '$1=***', $value);
        echo "✓ <span class='success'>$key</span>:\n  $safeValue\n\n";
    }
}

if (!$foundConnStr) {
    echo "<span class='warning'>Brak Azure Connection Strings</span>\n";
}

echo "</pre></div>";

echo "<h2>💾 5. Test połączenia z bazą danych</h2>";
echo "<div class='box'><pre>";

try {
    echo "⏳ Próba połączenia z bazą...\n\n";

    // Sprawdź czy plik Database.php istnieje
    $dbFile = __DIR__ . '/database/Database.php';
    if (!file_exists($dbFile)) {
        throw new Exception("Brak pliku Database.php");
    }

    require_once $dbFile;

    $db = Database::getInstance();
    echo "✓ <span class='success'>Połączenie z bazą udane!</span>\n\n";

    // Sprawdź tabele
    echo "📊 Statystyki bazy danych:\n";
    try {
        $stats = $db->getStats();
        echo "  - Użytkownicy: <span class='success'>" . $stats['users'] . "</span>\n";
        echo "  - Zadania: <span class='success'>" . $stats['tasks'] . "</span>\n";
        echo "  - Ukończone zadania: <span class='success'>" . $stats['completed_tasks'] . "</span>\n";
        echo "  - Oceny zdjęć: <span class='success'>" . $stats['photo_ratings'] . "</span>\n";
        echo "  - Kody dostępu: <span class='success'>" . $stats['access_codes'] . "</span>\n";
    } catch (Exception $e) {
        echo "  <span class='warning'>Nie można pobrać statystyk: " . htmlspecialchars($e->getMessage()) . "</span>\n";
    }

    // Sprawdź tabele
    echo "\n📋 Lista tabel w bazie:\n";
    try {
        // Próbuj najpierw MySQL
        try {
            $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e1) {
            // Jeśli nie zadziałało, spróbuj SQLite
            try {
                $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
            } catch (Exception $e2) {
                // Jeśli ani jedno ani drugie, spróbuj PostgreSQL
                try {
                    $tables = $db->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public'")->fetchAll(PDO::FETCH_COLUMN);
                } catch (Exception $e3) {
                    $tables = [];
                }
            }
        }

        if (count($tables) > 0) {
            foreach ($tables as $table) {
                echo "  - $table\n";
            }
        } else {
            echo "  <span class='warning'>Brak tabel lub nieobsługiwany typ bazy</span>\n";
        }
    } catch (Exception $e) {
        echo "  <span class='warning'>Nie można pobrać listy tabel: " . htmlspecialchars($e->getMessage()) . "</span>\n";
    }

} catch (Throwable $e) {
    echo "✗ <span class='error'>Błąd połączenia z bazą danych:</span>\n\n";
    echo "<span class='error'>" . htmlspecialchars($e->getMessage()) . "</span>\n\n";
    echo "Stack trace:\n";
    echo "<span class='error'>" . htmlspecialchars($e->getTraceAsString()) . "</span>\n";
}

echo "</pre></div>";

echo "<h2>🌍 6. Informacje o środowisku</h2>";
echo "<div class='box'><pre>";

echo "PHP Version: <span class='success'>" . phpversion() . "</span>\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "Script Filename: " . __FILE__ . "\n";
echo "Current Working Dir: " . getcwd() . "\n";
echo "Temp Dir: " . sys_get_temp_dir() . "\n";

echo "\nPDO Drivers:\n";
$drivers = PDO::getAvailableDrivers();
foreach ($drivers as $driver) {
    echo "  - $driver\n";
}

echo "</pre></div>";

echo "<h2>🔧 7. Config.php - wartości</h2>";
echo "<div class='box'><pre>";

try {
    $configFile = __DIR__ . '/config/config.php';
    if (!file_exists($configFile)) {
        throw new Exception("Brak pliku config.php");
    }

    require_once $configFile;

    if (defined('ADMIN_USERNAME')) {
        echo "ADMIN_USERNAME: <span class='success'>" . ADMIN_USERNAME . "</span>\n";
    } else {
        echo "ADMIN_USERNAME: <span class='warning'>NOT DEFINED</span>\n";
    }

    if (defined('ADMIN_PASSWORD')) {
        echo "ADMIN_PASSWORD: <span class='success'>[" . strlen(ADMIN_PASSWORD) . " chars]</span>\n";
    } else {
        echo "ADMIN_PASSWORD: <span class='warning'>NOT DEFINED</span>\n";
    }

    if (defined('ACCESS_CODE')) {
        echo "ACCESS_CODE: <span class='success'>" . ACCESS_CODE . "</span>\n";
    } else {
        echo "ACCESS_CODE: <span class='warning'>NOT DEFINED</span>\n";
    }

} catch (Throwable $e) {
    echo "<span class='error'>Błąd ładowania config.php: " . htmlspecialchars($e->getMessage()) . "</span>\n";
    echo "\nStack trace:\n";
    echo "<span class='error'>" . htmlspecialchars($e->getTraceAsString()) . "</span>\n";
}

echo "</pre></div>";

?>

<hr>
<p style="color: #f48771; font-weight: bold;">⚠️ PAMIĘTAJ: Usuń ten plik po zakończeniu diagnostyki!</p>
<p>Wygenerowano: <?php echo date('Y-m-d H:i:s'); ?></p>

</body>
</html>

