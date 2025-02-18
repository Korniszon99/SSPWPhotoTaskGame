<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Pobierz dane z Azure App Service
$db_host = getenv('DB_HOST');
$db_user = getenv('DB_USER');
$db_password = getenv('DB_PASSWORD');
$db_name = getenv('DB_NAME');

try {
    $con = mysqli_connect($db_host, $db_user, $db_password, $db_name);

    if (!$con) {
        throw new Exception("Błąd połączenia MySQL: " . mysqli_connect_error());
    }

    echo "Połączono z bazą danych!";

} catch (Exception $e) {
    die("Wystąpił błąd: " . $e->getMessage());
}
?>