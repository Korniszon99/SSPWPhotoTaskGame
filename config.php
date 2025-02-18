<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Pobierz dane z Azure App Service – zmienne środowiskowe
$db_host = getenv('DB_HOST');
$db_user = getenv('DB_USER');
$db_password = getenv('DB_PASSWORD');
$db_name = getenv('DB_NAME');

// Inicjalizacja połączenia
$con = mysqli_init();

// Ścieżka do certyfikatu SSL – Azure zaleca użycie DigiCertGlobalRootG2.crt.pem
mysqli_ssl_set($con, NULL, NULL, '/site/wwwroot/DigiCertGlobalRootG2.crt.pem', NULL, NULL);


// Połączenie
mysqli_real_connect($con, $db_host, $db_user, $db_password, $db_name, 3306, NULL, MYSQLI_CLIENT_SSL);

// Sprawdzenie połączenia
if (mysqli_connect_errno($con)) {
    die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
}

echo "Połączono z bazą danych!";
?>
