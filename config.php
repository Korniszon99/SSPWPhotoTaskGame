<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$con = mysqli_init();
mysqli_ssl_set($con, NULL, NULL, '/Users/korniszon99/PhpstormProjects/SzkoleniePhotoTaskGame/DigiCertGlobalRootG2.crt.pem', NULL, NULL);
mysqli_real_connect($con, 'szkoleniesql.mysql.database.azure.com', 'adminPW', 'piktu6-buFduk-dizcyg', 'fut_gdansk_app', 3306, MYSQLI_CLIENT_SSL);

if (mysqli_connect_errno($con)) {
    die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
}
?>