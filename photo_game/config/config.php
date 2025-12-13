<?php
// Konfiguracja sesji
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Konfiguracja błędów (0 W PRODUKCJI!)
$display_errors = 1;
ini_set('display_errors', $display_errors);
ini_set('display_startup_errors', $display_errors);
error_reporting(E_ALL);
ini_set('log_errors',1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

require_once __DIR__ . '/../database/database.php';
require_once __DIR__ . '/rate_limiter.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../lang/localization.php';

// Ustawienia języka
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$currentLang = $_SESSION['lang'] ?? 'pl';
Localization::getInstance($currentLang);

// Inicjalizuj połączenie z bazą
try {
    $db = Database::getInstance();
    $rateLimiter = new RateLimiter();

} catch (Exception $e) {
    die("Błąd inicjalizacji aplikacji. Spróbuj ponownie później.");
}

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
    regenerateCSRFToken();
}
